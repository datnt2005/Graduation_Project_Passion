<?php

namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    // USER gửi yêu cầu trả/đổi hànguse Illuminate\Support\Facades\Storage;

public function store(Request $request)
{
    $user = $request->user();

    $validator = Validator::make($request->all(), [
        'order_item_id' => 'required|exists:order_items,id',
        'reason' => 'required|string',
        'type' => 'required|in:return,exchange',
        'images' => 'nullable|array',
        'images.*' => 'file|max:10240', // cho phép ảnh hoặc video
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $item = OrderItem::with('order')->find($request->order_item_id);

    if (!$item || $item->order->user_id !== $user->id || $item->order->status !== 'delivered') {
        return response()->json(['message' => 'Không hợp lệ hoặc không đủ điều kiện đổi trả'], 403);
    }

    if (ReturnRequest::where('order_item_id', $item->id)->exists()) {
        return response()->json(['message' => 'Đã gửi yêu cầu đổi/trả cho sản phẩm này'], 409);
    }

    // Lưu đúng 1 ảnh/video duy nhất dưới dạng chuỗi
    $path = null;
    if ($request->hasFile('images') && count($request->file('images')) > 0) {
     $path = $request->file('images')[0]->store('return-requests', 'r2'); // ra: return-requests/abc.webp
    }

    $return = ReturnRequest::create([
        'order_item_id' => $item->id,
        'user_id' => $user->id,
        'reason' => $request->reason,
        'type' => $request->type,
            'status' => 'pending',
             'images' => $path,
        ]);

    return response()->json(['message' => 'Đã gửi yêu cầu trả hàng', 'data' => $return]);
}


    // ADMIN duyệt hoặc từ chối
    public function update(Request $request, ReturnRequest $returnRequest)
    {
        // Giả sử bạn có middleware hoặc field is_admin
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền duyệt yêu cầu này'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
            'refund_amount' => 'nullable|numeric'
        ]);

        $returnRequest->status = $request->status;
        $returnRequest->admin_note = $request->admin_note;

        if ($request->has('refund_amount')) {
            $returnRequest->refund_amount = $request->refund_amount;
        }

        $returnRequest->save();

        return response()->json(['message' => 'Cập nhật thành công']);
    }

    // ADMIN: danh sách yêu cầu
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $returns = ReturnRequest::with(['orderItem.product', 'user'])
            ->latest()
            ->paginate(20);

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

}
