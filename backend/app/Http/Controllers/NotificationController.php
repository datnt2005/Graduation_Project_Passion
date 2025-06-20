<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $baseImageUrl = env('R2_URL'); // Sử dụng R2_URL bạn đã cấu hình

            $notifications = Notification::latest()->get()->map(function ($item) use ($baseImageUrl) {
                // Nếu ảnh đã là full URL rồi thì không nối thêm
                $item->image_url = $item->image_url && !str_starts_with($item->image_url, 'http')
                    ? rtrim($baseImageUrl, '/') . '/' . ltrim($item->image_url, '/')
                    : $item->image_url;

                return $item;
            });


            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách thông báo thành công.',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách thông báo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách thông báo.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }




    public function store(Request $request)
    {
        $messages = [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'content.required' => 'Nội dung là bắt buộc.',
            'type.required' => 'Loại thông báo là bắt buộc.',
            'type.in' => 'Loại thông báo không hợp lệ.',
            'to_role.required' => 'Vai trò người nhận là bắt buộc.',
            'to_role.in' => 'Vai trò người nhận không hợp lệ.',
            'link.url' => 'Đường dẫn không hợp lệ.',
            'link.max' => 'Đường dẫn không được vượt quá 255 ký tự.',
            'image.image' => 'Tệp phải là hình ảnh.',
            'image.max' => 'Ảnh không được vượt quá 2MB.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:order,promotion,message,system',
            'to_role' => 'required|in:admin,user,seller',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,sent',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Upload ảnh nếu có
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'notifications/' . time() . '_' . $file->getClientOriginalName();

            // Lưu vào Cloudflare R2
            Storage::disk('r2')->put($filename, file_get_contents($file));

            // Tạo URL đầy đủ
            $baseUrl = rtrim(env('R2_URL'), '/');
            $imageUrl = $baseUrl . '/' . ltrim($filename, '/');
        }

        // Lưu DB
        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'to_role' => $request->to_role,
            'link' => $request->link,
            'user_id' => 3, // Nếu có auth thì sửa thành auth()->id()
            'is_read' => 0,
            'status' => $request->status,
            'image_url' => $imageUrl,
        ]);

        return response()->json([
            'message' => 'Tạo thông báo thành công!',
            'data' => $notification
        ]);
    }

    public function show($id)
    {
        try {
            $notification = Notification::findOrFail($id);

            // Gán URL ảnh nếu chưa đầy đủ
            $baseImageUrl = env('R2_URL');
            if ($notification->image_url && !str_starts_with($notification->image_url, 'http')) {
                $notification->image_url = rtrim($baseImageUrl, '/') . '/' . ltrim($notification->image_url, '/');
            }

            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông báo.',
            ], 404);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|in:order,promotion,message,system',
                'to_role' => 'required|in:admin,user,seller',
                'link' => 'nullable|url|max:255',
                'image' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Upload lại ảnh nếu có
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'notifications/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $baseUrl = rtrim(env('R2_URL'), '/');
                $notification->image_url = $baseUrl . '/' . ltrim($filename, '/');
            }

            $notification->update([
                'title' => $request->title,
                'content' => $request->content,
                'type' => $request->type,
                'to_role' => $request->to_role,
                'link' => $request->link,
            ]);

            return response()->json(['message' => 'Cập nhật thông báo thành công!', 'data' => $notification]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json(['message' => 'Xóa thông báo thành công!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không thể xóa thông báo.'], 500);
        }
    }

    // POST /notifications/send-multiple
    public function sendMultiple(Request $request)
    {
        $notificationIds = $request->input('ids');
        $toRole = $request->input('to_role');
        $toUserIds = $request->input('to_user_ids'); // array or null

        $notifications = Notification::whereIn('id', $notificationIds)->get();

        if ($toUserIds && is_array($toUserIds)) {
            // Gửi cho danh sách user cụ thể
            foreach ($toUserIds as $userId) {
                foreach ($notifications as $notification) {
                    Notification::create([
                        'title' => $notification->title,
                        'content' => $notification->content,
                        'image_url' => $notification->image_url,
                        'type' => $notification->type,
                        'to_user_id' => $userId,
                        'to_role' => null,
                        'status' => 'sent',
                    ]);
                }
            }
        } elseif ($toRole) {
            // Gửi cho toàn bộ người dùng theo role
            $users = User::where('role', $toRole)->pluck('id');
            foreach ($users as $userId) {
                foreach ($notifications as $notification) {
                    Notification::create([
                        'title' => $notification->title,
                        'content' => $notification->content,
                        'image_url' => $notification->image_url,
                        'type' => $notification->type,
                        'to_user_id' => $userId,
                        'to_role' => $toRole,
                        'status' => 'sent',
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Đã gửi thông báo!']);
    }
}
