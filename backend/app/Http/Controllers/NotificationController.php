<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;


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
            Storage::disk('r2')->put($filename, file_get_contents($file));
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
            'user_id' => auth()->id(), // ✅ Lấy từ người dùng đăng nhập
            'is_read' => 0,
            'is_hidden' => 0,
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
                'is_hidden' => 0,
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

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        try {
            Notification::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Đã xóa các thông báo đã chọn']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa nhiều thông báo'], 500);
        }
    }


    public function destroyAll()
    {
        try {
            Notification::truncate();
            return response()->json(['message' => 'Đã xóa tất cả thông báo']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa tất cả thông báo'], 500);
        }
    }



    public function sendMultiple(Request $request)
    {
        $notificationIds = $request->input('ids');
        $toRole = $request->input('to_role');
        $toUserIds = $request->input('to_user_ids');

        if (is_string($toUserIds)) {
            $toUserIds = json_decode($toUserIds, true);
        }

        // Nếu là mảng đối tượng như [{id: 3, name: 'Phattran'}] thì chỉ lấy id
        if (is_array($toUserIds) && isset($toUserIds[0]['id'])) {
            $toUserIds = array_map(fn($u) => $u['id'], $toUserIds);
        }

        $notifications = Notification::whereIn('id', $notificationIds)
            ->where('user_id', auth()->id())
            ->get();


        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy thông báo để cập nhật.'], 400);
        }

        foreach ($notifications as $notification) {
            $notification->update([
                'to_role'     => $toRole,
                'to_user_id'  => $toUserIds[0] ?? null, // nếu chọn 1 người
                'status'      => 'sent',
            ]);
        }

        return response()->json(['message' => 'Đã gửi thông báo thành công.']);
    }


    public function getMyNotifications(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Chưa đăng nhập.'], 401);
            }

            $baseImageUrl = env('R2_URL');

            $notifications = Notification::where('status', 'sent')
                ->where('is_hidden', 0)
                ->where(function ($query) use ($user) {
                    $query->where('to_user_id', $user->id)
                        ->orWhere(function ($q) use ($user) {
                            $q->whereNull('to_user_id')->where('to_role', $user->role);
                        });
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($item) use ($baseImageUrl) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'content' => (string) $item->content,
                        'link' => $item->link,
                        'image_url' => $item->image_url && !str_starts_with($item->image_url, 'http')
                            ? rtrim($baseImageUrl, '/') . '/' . ltrim($item->image_url, '/')
                            : $item->image_url,
                        'is_read' => $item->is_read,
                        'read_at' => $item->read_at,
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'time_ago' => \Carbon\Carbon::parse($item->created_at)->diffForHumans(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách thông báo theo người dùng thành công.',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông báo người dùng: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông báo.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }


    // Controller
    public function markAsRead($id)
    {
        $user = auth()->user();

        $notification = Notification::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('to_user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereNull('to_user_id')->where('to_role', $user->role);
                    });
            })
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Không tìm thấy thông báo hoặc không có quyền.'], 404);
        }

        $notification->update([
            'is_read' => 1,
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Đã đánh dấu là đã đọc.']);
    }

    public function markMultipleAsRead(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);
        Notification::whereIn('id', $ids)
            ->where(function ($q) use ($user) {
                $q->where('to_user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereNull('to_user_id')->where('to_role', $user->role);
                    });
            })->update(['is_read' => 1, 'read_at' => now()]);

        return response()->json(['message' => 'Đã đánh dấu đã đọc.']);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        Notification::where(function ($q) use ($user) {
            $q->where('to_user_id', $user->id)
                ->orWhere(function ($q2) use ($user) {
                    $q2->whereNull('to_user_id')->where('to_role', $user->role);
                });
        })->update(['is_read' => 1, 'read_at' => now()]);

        return response()->json(['message' => 'Đã đánh dấu tất cả đã đọc.']);
    }

    public function deleteMultiple(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);

        Notification::whereIn('id', $ids)
            ->where(function ($q) use ($user) {
                $q->where('to_user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereNull('to_user_id')->where('to_role', $user->role);
                    });
            })->update(['is_hidden' => 1]);

        return response()->json(['message' => 'Đã ẩn thông báo.']);
    }


    public function deleteAll()
    {
        $user = auth()->user();

        Notification::where(function ($q) use ($user) {
            $q->where('to_user_id', $user->id)
                ->orWhere(function ($q2) use ($user) {
                    $q2->whereNull('to_user_id')->where('to_role', $user->role);
                });
        })->update(['is_hidden' => 1]);

        return response()->json(['message' => 'Đã ẩn tất cả thông báo.']);
    }
}
