<?php

namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Mail\ReturnRequestStatusUpdatedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    // USER gửi yêu cầu trả/đổi hànguse Illuminate\Support\Facades\Storage;

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|exists:order_items,id',
            'reason' => 'required|string',
            'additional_reason' => 'nullable|string',
            'type' => 'required|in:return,exchange',
            'images' => 'nullable|array',
            'images.*' => 'file|max:10240', // tối đa 10MB mỗi file
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = OrderItem::with('order')->find($request->order_item_id);

        // Kiểm tra quyền đổi trả
        if (
            !$item ||
            $item->order->user_id !== $user->id ||
            $item->order->status !== 'delivered'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Không hợp lệ hoặc không đủ điều kiện đổi trả'], 403);
        }

        // Giới hạn 14 ngày kể từ ngày đặt hàng
        $orderCreatedAt = \Carbon\Carbon::parse($item->order->created_at);
        if (now()->diffInDays($orderCreatedAt) > 3) {
            return response()->json([
                'success' => false,
                'message' => 'Đã quá hạn đổi trả (quá 3 ngày)'], 410);
        }

        // Không cho gửi trùng
        if (ReturnRequest::where('order_item_id', $item->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Đã gửi yêu cầu đổi/trả trước đó'], 409);
        }

        // Tạo yêu cầu đổi trả
        $return = ReturnRequest::create([
            'user_id' => $user->id,
            'order_item_id' => $item->id,
            'reason' => $request->reason,
            'additional_reason' => $request->additional_reason,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        // Upload ảnh lên R2
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Tạo tên file ngẫu nhiên để tránh trùng
                $filename = uniqid('return_') . '.' . $file->getClientOriginalExtension();

                // Upload lên R2
                $path = $file->storeAs('return_images', $filename, 'r2');


                $return->images()->create([
                    'path' => $path,
                ]);
            }
        }

        $seller = optional($item->product)->seller;
 
        if ($seller && $seller->user_id) {
            try {
                $notification = \App\Models\Notification::create([
                    'title' => 'Yêu cầu đổi/trả mới',
                    'content' => "Người dùng đã gửi yêu cầu {$return->type} cho sản phẩm trong đơn hàng #{$item->order_id}.",
                    'type' => 'system',
                    'link' => 'seller/return/list-return',
                    'user_id' => $seller->user_id,
                    'from_role' => 'system',
                    'to_roles' => json_encode(['seller']),
                    'channels' => json_encode(['dashboard']),
                    'status' => 'sent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \App\Models\NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'user_id' => $seller->user_id,
                    'is_read' => false,
                    'is_hidden' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Lỗi tạo thông báo yêu cầu đổi/trả cho seller', [
                    'seller_user_id' => $seller->user_id ?? null,
                    'return_request_id' => $return->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu đổi/trả đã được gửi thành công!'
        ]);
    }

    // ADMIN duyệt hoặc từ chối
    public function update(Request $request, ReturnRequest $returnRequest)
    {
        $user = $request->user();

        // Nếu không phải admin thì kiểm tra seller quyền xử lý yêu cầu
        if (!$user->is_admin) {
            if ($user->role !== 'seller') {
                return response()->json(['message' => 'Không có quyền duyệt yêu cầu này'], 403);
            }

            // Lấy seller_id thực tế từ bảng sellers
            $sellerId = $user->seller->id ?? null;

            if (
                !$sellerId ||
                $returnRequest->orderItem->product->seller_id !== $sellerId
            ) {
                return response()->json(['message' => 'Không có quyền duyệt yêu cầu này'], 403);
            }
        }

        // Validate dữ liệu cập nhật
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
            'refund_amount' => 'nullable|numeric'
        ]);

        // Cập nhật yêu cầu
        $returnRequest->status = $request->status;
        $returnRequest->admin_note = $request->admin_note;
        if ($request->has('refund_amount')) {
            $returnRequest->refund_amount = $request->refund_amount;
        }

        $returnRequest->save();

        // Soạn nội dung thông báo
        $statusText = $request->status === 'approved' ? 'được duyệt' : 'bị từ chối';

        $notification = Notification::create([
            'title' => 'Cập nhật yêu cầu đổi/trả',
            'content' => "Yêu cầu đổi/trả sản phẩm \"{$returnRequest->orderItem->product->name}\" của bạn đã {$statusText}.",
>>>>>>> cfc0cdf00e223ed8ad70af4576ee0d18cb0e2c2f
            'type' => 'system',
            'link' => 'users/orders',
            'user_id' => $returnRequest->user_id,
            'from_role' => 'system',
            'channels' => json_encode(['dashboard']),
            'status' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id' => $returnRequest->user_id,
            'is_read' => false,
            'is_hidden' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Gửi email
        Mail::to($returnRequest->user->email)->send(new ReturnRequestStatusUpdatedMail($returnRequest));

        return response()->json(['message' => 'Cập nhật thành công và đã gửi email thông báo']);
    }


    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = ReturnRequest::with(['orderItem.product', 'user', 'images']);

        // Nếu không phải admin thì kiểm tra seller
        if (!$user->is_admin) {
            if ($user->role !== 'seller') {
                return response()->json(['message' => 'Không có quyền truy cập'], 403);
            }

            // Lấy seller_id từ quan hệ user → seller
            $sellerId = $user->seller->id ?? null;

            if (!$sellerId) {
                return response()->json(['message' => 'Không tìm thấy seller tương ứng với user'], 403);
            }

            // Lọc theo seller_id trong bảng products
            $query->whereHas('orderItem.product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            });
        }

        // Lọc theo status nếu có
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $returns = $query->latest()->paginate(20);

        return response()->json($returns);
    }





    public function myRequests(Request $request)
    {
        $returns = ReturnRequest::with(['orderItem.product'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($returns);
    }
    public function check(Request $request, $orderItemId)
    {
        $user = $request->user();

        $item = OrderItem::with('order')->find($orderItemId);
        if (!$item || $item->order->user_id !== $user->id) {
            return response()->json(['message' => 'Không hợp lệ'], 403);
        }

        $requestData = ReturnRequest::where('order_item_id', $orderItemId)->first();

        return response()->json([
            'exists' => !!$requestData,
            'status' => $requestData?->status,
        ]);
    }
}
