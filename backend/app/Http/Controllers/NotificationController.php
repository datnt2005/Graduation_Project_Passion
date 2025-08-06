<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\User;
use App\Models\NotificationRecipient;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

Carbon::setLocale('vi');

class NotificationController extends Controller
{
    public function index(Request $request)
{
    $userId = $request->query('user_id');
    $toRoles = $request->query('to_roles');
    $query = Notification::whereJsonContains('to_roles', $toRoles)
        ->whereHas('recipients', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_hidden', 0);
        })
        ->with(['recipients' => function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_hidden', 0);
        }]);

    if ($request->has('type')) {
        $query->where('type', $request->type);
    }
    if ($request->has('search')) {
        $query->where('title', 'LIKE', '%' . $request->search . '%');
    }
    $query->orderBy('sent_at', $request->sort_order ?? 'desc');

    $notifications = $query->paginate($request->per_page ?? 10);

    return response()->json([
        'success' => true,
        'data' => $notifications->items(),
        'last_page' => $notifications->lastPage(),
        'total' => $notifications->total(),
        'current_page' => $notifications->currentPage(),
    ]);
}


    public function show($id)
    {
        try {
            $notification = Notification::with([
                'users:id,name,email,role', // người dùng cụ thể
                'recipients.user:id,name,email,role' // người nhận + user liên quan
            ])->findOrFail($id);

            // Giải mã các trường JSON
            $notification->to_roles = json_decode($notification->to_roles, true);
            $notification->channels = json_decode($notification->channels, true);

            // Xử lý ảnh nếu là ảnh cục bộ
            $baseImageUrl = env('R2_URL');
            if ($notification->image_url && !str_starts_with($notification->image_url, 'http')) {
                $notification->image_url = rtrim($baseImageUrl, '/') . '/' . ltrim($notification->image_url, '/');
            }

            // Gắn thêm danh sách người nhận
            $notification->recipients = $notification->recipients->map(function ($recipient) {
                return [
                    'id' => $recipient->id,
                    'is_read' => $recipient->is_read,
                    'read_at' => $recipient->read_at,
                    'is_hidden' => $recipient->is_hidden,
                    'user' => $recipient->user ? [
                        'id' => $recipient->user->id,
                        'name' => $recipient->user->name,
                        'email' => $recipient->user->email,
                        'role' => $recipient->user->role,
                    ] : null
                ];
            });

            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông báo',
                'error' => $e->getMessage()
            ], 404);
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
            'link.url' => 'Đường dẫn không hợp lệ.',
            'link.max' => 'Đường dẫn không được vượt quá 255 ký tự.',
            'image.image' => 'Tệp phải là hình ảnh.',
            'image.max' => 'Ảnh không được vượt quá 2MB.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'roles.required' => 'Vai trò người nhận là bắt buộc.',
            'roles.array' => 'Vai trò người nhận phải là mảng.',
            'channels.required' => 'Kênh gửi là bắt buộc.',
            'channels.array' => 'Kênh gửi phải là mảng.',
            'user_ids.array' => 'Danh sách người dùng phải là mảng.',
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:order,promotion,message,system',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,sent',
            'roles' => 'required|array|min:1',
            'channels' => 'required|nullable|array',
            'user_ids' => 'nullable|array',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Xử lý ảnh
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'notifications/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('r2')->put($filename, file_get_contents($file));
            $baseUrl = rtrim(env('R2_URL'), '/');
            $imageUrl = $baseUrl . '/' . ltrim($filename, '/');
        }

        // Lấy danh sách người nhận
        $users = collect();

        if (!empty($request->user_ids)) {
            Log::info('Truy vấn người dùng cụ thể:', [
                'query_user_ids' => (array) $request->user_ids,
                'query_roles' => (array) $request->roles,
            ]);


            $users = User::whereIn('id', $request->user_ids)
                ->whereIn('role', $request->roles)
                ->get();

            Log::info('Người dùng tìm được:', ['user_ids' => $users->pluck('id')->toArray()]);
        } else {
            // Nếu không chọn user cụ thể → gửi toàn bộ user theo role
            $users = User::whereIn('role', $request->roles)->get();
        }

        if ($users->isEmpty()) {
            return response()->json(['errors' => ['roles' => ['Không có người nhận hợp lệ.']]], 422);
        }

        // Tạo bản ghi notification duy nhất
        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'to_roles' => json_encode($request->roles),
            'link' => $request->link,
            'user_id' => auth()->id(),
            'from_role' => auth()->user()->role ?? 'admin',
            'status' => $request->status,
            'image_url' => $imageUrl,
            'channels' => json_encode($request->channels ?? []),
        ]);

        // Gửi cho từng user
        foreach ($users as $user) {
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'is_read' => 0,
                'read_at' => null,
                'is_hidden' => 0,
            ]);

            if (
                $request->status === 'sent' &&
                in_array('email', $request->channels ?? [])
            ) {
                Mail::to($user->email)->queue(new \App\Mail\NotificationMail($notification));
            }
        }

        return response()->json(['message' => 'Đã gửi thông báo thành công.']);
    }


    public function update(Request $request, $id)
    {
        try {
            $messages = [
                'title.required' => 'Tiêu đề là bắt buộc.',
                'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
                'content.required' => 'Nội dung là bắt buộc.',
                'type.required' => 'Loại thông báo là bắt buộc.',
                'type.in' => 'Loại thông báo không hợp lệ.',
                'link.url' => 'Đường dẫn không hợp lệ.',
                'link.max' => 'Đường dẫn không được vượt quá 255 ký tự.',
                'image.image' => 'Tệp phải là hình ảnh.',
                'image.max' => 'Ảnh không được vượt quá 2MB.',
                'status.required' => 'Trạng thái là bắt buộc.',
                'status.in' => 'Trạng thái không hợp lệ.',
                'roles.required' => 'Vai trò người nhận là bắt buộc.',
                'roles.array' => 'Vai trò người nhận phải là mảng.',
                'channels.required' => 'Kênh gửi là bắt buộc.',
                'channels.array' => 'Kênh gửi phải là mảng.',
                'user_ids.array' => 'Danh sách người dùng phải là mảng.',
            ];

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|in:order,promotion,message,system',
                'link' => 'nullable|url|max:255',
                'image' => 'nullable|image|max:2048',
                'status' => 'required|in:draft,sent',
                'roles' => 'required|array|min:1',
                'channels' => 'required|nullable|array',
                'user_ids' => 'nullable|array',
            ], $messages);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $notification = Notification::findOrFail($id);

            // Xóa ảnh cũ nếu có yêu cầu
            if ($request->boolean('remove_image') && !$request->hasFile('image')) {
                $notification->image_url = null;
            }

            // Cập nhật ảnh mới nếu có
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'notifications/' . time() . '_' . $file->getClientOriginalName();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $baseUrl = rtrim(env('R2_URL'), '/');
                $notification->image_url = $baseUrl . '/' . ltrim($filename, '/');
            }

            // Cập nhật thông tin cơ bản
            $notification->title = $request->title;
            $notification->content = $request->content;
            $notification->type = $request->type;
            $notification->link = $request->link;
            $notification->status = $request->status;
            $notification->to_roles = json_encode($request->roles);
            $notification->channels = json_encode($request->channels ?? []);

            // Reset trạng thái ẩn
            NotificationRecipient::where('notification_id', $notification->id)
                ->update(['is_hidden' => 0]);

            $notification->save();

            // Gán người nhận:
            if (!empty($request->user_ids)) {
                // Nếu người dùng có chọn cụ thể
                $notification->users()->sync($request->user_ids);
            } else {
                // Nếu không có user_ids → lấy tất cả user theo roles
                $users = \App\Models\User::whereIn('role', $request->roles)->pluck('id')->toArray();
                $notification->users()->sync($users);
            }

            // Tải lại danh sách người nhận
            $notification->load(['users:id,name,email,role']);

            return response()->json([
                'message' => 'Cập nhật thông báo thành công!',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
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
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Người dùng chưa được xác thực'], 401);
        }

        $user->notifications()->delete(); // Xóa hàng loạt

        return response()->json(['message' => 'Đã xóa tất cả thông báo'], 200);
    } catch (\Exception $e) {
        \Log::error('Lỗi xóa tất cả thông báo: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json(['error' => 'Lỗi khi xóa tất cả thông báo', 'message' => $e->getMessage()], 500);
    }
}


    public function sendMultiple(Request $request)
    {
        $notificationIds = $request->input('ids');

        if (!is_array($notificationIds) || empty($notificationIds)) {
            return response()->json(['message' => 'Không có ID thông báo nào được chọn.'], 400);
        }

        $notifications = Notification::with(['users:id,email', 'recipients'])
            ->whereIn('id', $notificationIds)
            ->where('user_id', auth()->id())
            ->get();

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy thông báo để gửi.'], 400);
        }

        foreach ($notifications as $notification) {
            $channels = json_decode($notification->channels, true) ?? [];
            $shouldSendEmail = in_array('email', $channels);

            $hasRole = !empty(json_decode($notification->to_roles, true));
            $hasUsers = $notification->users && $notification->users->count() > 0;

            if (!$hasRole && !$hasUsers) {
                return response()->json([
                    'message' => "Thông báo #{$notification->id} chưa có thông tin người nhận. Vui lòng chỉnh sửa."
                ], 422);
            }

            if ($notification->status === 'sent') continue;

            $recipientUsers = $notification->users;

            foreach ($recipientUsers as $user) {
                $alreadySent = $notification->recipients->contains(fn($r) => $r->user_id === $user->id);

                if (!$alreadySent) {
                    $notification->recipients()->create([
                        'user_id' => $user->id,
                        'is_read' => false,
                        'is_hidden' => false,
                        'read_at' => null,
                    ]);
                }

                if ($shouldSendEmail && $user->email) {
                    Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
                }
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Đã gửi các thông báo thành công.']);
    }


    public function sendAll(Request $request)
    {
        $notifications = Notification::with(['users:id,email', 'recipients'])
            ->where('user_id', auth()->id())
            ->where('status', 'draft')
            ->get();

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'Không có thông báo nào ở trạng thái "Lưu nháp" để gửi.'], 400);
        }

        foreach ($notifications as $notification) {
            $channels = json_decode($notification->channels, true) ?? [];
            $shouldSendEmail = in_array('email', $channels);

            $hasRole = !empty(json_decode($notification->to_roles, true));
            $hasUsers = $notification->users && $notification->users->count() > 0;

            if (!$hasRole && !$hasUsers) {
                return response()->json([
                    'message' => "Thông báo #{$notification->id} chưa có thông tin người nhận. Vui lòng chỉnh sửa trước khi gửi."
                ], 422);
            }

            $recipientUsers = $notification->users;

            foreach ($recipientUsers as $user) {
                $alreadySent = $notification->recipients->contains(fn($r) => $r->user_id === $user->id);

                if (!$alreadySent) {
                    $notification->recipients()->create([
                        'user_id' => $user->id,
                        'is_read' => false,
                        'is_hidden' => false,
                        'read_at' => null,
                    ]);
                }

                if ($shouldSendEmail && $user->email) {
                    Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
                }
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Đã gửi tất cả thông báo chưa gửi thành công.']);
    }



    public function getMyNotifications(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Chưa đăng nhập.'], 401);
            }

            $baseImageUrl = rtrim(env('R2_URL'), '/');

            $notifications = NotificationRecipient::with(['notification' => function ($query) {
                $query->where('status', 'sent'); // Chỉ lấy thông báo đã gửi
            }])
                ->where('user_id', $user->id)
                ->where('is_hidden', 0)
                ->orderByDesc('created_at')
                ->get()
                ->filter(fn($recipient) => $recipient->notification) // Loại bỏ bản ghi không hợp lệ
                ->map(function ($recipient) use ($baseImageUrl) {
                    $n = $recipient->notification;

                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'content' => (string) $n->content,
                        'link' => $n->link,
                        'image_url' => $n->image_url && !str_starts_with($n->image_url, 'http')
                            ? $baseImageUrl . '/' . ltrim($n->image_url, '/')
                            : $n->image_url,
                        'type' => $n->type,
                        'status' => $n->status,
                        'is_read' => $recipient->is_read,
                        'read_at' => $recipient->read_at,
                        'sent_at' => $n->sent_at ? Carbon::parse($n->sent_at)->format('Y-m-d H:i:s') : null,
                        'time_ago' => $n->sent_at
                            ? Carbon::parse($n->sent_at)->timezone('Asia/Ho_Chi_Minh')->diffForHumans()
                            : null,
                    ];
                });

            // Thêm log để debug
            Log::info('Notifications fetched', ['count' => $notifications->count(), 'unread' => $notifications->where('is_read', 0)->count()]);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách thông báo thành công.',
                'data' => $notifications->values()->all(),
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông báo người dùng: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông báo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }



    public function markAsRead($id)
    {
        $user = auth()->user();

        $recipient = NotificationRecipient::where('notification_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$recipient) {
            return response()->json(['message' => 'Không tìm thấy hoặc không có quyền truy cập.'], 404);
        }

        $recipient->update([
            'is_read' => 1,
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Đã đánh dấu là đã đọc.']);
    }


    public function markMultipleAsRead(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['message' => 'Danh sách ID trống.'], 400);
        }

        NotificationRecipient::whereIn('notification_id', $ids)
            ->where('user_id', $user->id)
            ->update([
                'is_read' => 1,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'Đã đánh dấu là đã đọc.']);
    }


    public function markAllAsRead()
    {
        $user = auth()->user();

        NotificationRecipient::where('user_id', $user->id)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'Đã đánh dấu tất cả là đã đọc.']);
    }


    public function deleteMultiple(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['message' => 'Danh sách ID trống.'], 400);
        }

        NotificationRecipient::whereIn('notification_id', $ids)
            ->where('user_id', $user->id)
            ->update([
                'is_hidden' => 1,
            ]);

        return response()->json(['message' => 'Đã ẩn các thông báo.']);
    }



    public function deleteAll()
    {
        $user = auth()->user();

        NotificationRecipient::where('user_id', $user->id)
            ->update(['is_hidden' => 1]);

        return response()->json(['message' => 'Đã ẩn tất cả thông báo.']);
    }


    public function adminIndex()
{
    try {
        $baseImageUrl = env('R2_URL');
        $notifications = Notification::where('from_role', 'system')
            ->latest()
            ->get()
            ->map(function ($item) use ($baseImageUrl) {
                $item->image_url = $item->image_url && !str_starts_with($item->image_url, 'http')
                    ? rtrim($baseImageUrl, '/') . '/' . ltrim($item->image_url, '/')
                    : $item->image_url;
                $item->to_roles = json_decode($item->to_roles, true);
                $item->channels = json_decode($item->channels, true);

                // Thêm read_status từ bảng notification_recipient
                $item->read_status = NotificationRecipient::where('notification_id', $item->id)
                    ->where('is_hidden', 0) // Chỉ lấy bản ghi không bị ẩn
                    ->select('user_id', 'is_read', 'read_at')
                    ->get()
                    ->map(function ($recipient) {
                        return [
                            'user_id' => $recipient->user_id,
                            'is_read' => (bool) $recipient->is_read,
                            'read_at' => $recipient->read_at,
                        ];
                    })->toArray();

                return $item;
            });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thông báo hệ thống thành công.',
            'data' => $notifications,
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi khi lấy danh sách thông báo hệ thống: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi lấy danh sách thông báo.',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

    public function adminShow($id)
    {
        try {
            $notification = Notification::with([
                'users:id,name,email,role',
                'recipients.user:id,name,email,role'
            ])->where('from_role', 'system')->findOrFail($id);

            $notification->to_roles = json_decode($notification->to_roles, true);
            $notification->channels = json_decode($notification->channels, true);

            $baseImageUrl = env('R2_URL');
            if ($notification->image_url && !str_starts_with($notification->image_url, 'http')) {
                $notification->image_url = rtrim($baseImageUrl, '/') . '/' . ltrim($notification->image_url, '/');
            }

            $notification->recipients = $notification->recipients->map(function ($recipient) {
                return [
                    'id' => $recipient->id,
                    'is_read' => $recipient->is_read,
                    'read_at' => $recipient->read_at,
                    'is_hidden' => $recipient->is_hidden,
                    'user' => $recipient->user ? [
                        'id' => $recipient->user->id,
                        'name' => $recipient->user->name,
                        'email' => $recipient->user->email,
                        'role' => $recipient->user->role,
                    ] : null
                ];
            });

            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông báo hệ thống.',
                'error' => $e->getMessage()
            ], 404);
        }
    }


  public function sellerIndex(Request $request)
{
    try {
        $user = auth()->user();
        $baseImageUrl = env('R2_URL');
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $type = $request->query('type');
        $search = $request->query('search');
        $sortOrder = $request->query('sort_order', 'desc');

        $query = Notification::where(function ($query) use ($user) {
            $query->whereJsonContains('to_roles', $user->role)
                  ->orWhereHas('recipients', function ($sub) use ($user) {
                      $sub->where('user_id', $user->id)
                          ->where('is_hidden', 0); // Chỉ lấy thông báo chưa ẩn
                  });
        });

        // Lọc theo type nếu có
        if ($type) {
            $query->where('type', $type);
        }

        // Tìm kiếm theo title hoặc content
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Sắp xếp
        $query->orderBy('sent_at', $sortOrder);

        // Phân trang
        $notifications = $query->paginate($perPage, ['*'], 'page', $page);

        // Định dạng dữ liệu
        $notifications->getCollection()->transform(function ($item) use ($baseImageUrl) {
            $item->image_url = $item->image_url && !str_starts_with($item->image_url, 'http')
                ? rtrim($baseImageUrl, '/') . '/' . ltrim($item->image_url, '/')
                : $item->image_url;
            $item->to_roles = json_decode($item->to_roles, true);
            $item->channels = json_decode($item->channels, true);
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => $notifications->isEmpty() ? 'Không có thông báo nào.' : 'Lấy danh sách thông báo dành cho seller thành công.',
            'data' => $notifications->items(),
            'last_page' => $notifications->lastPage(),
            'total' => $notifications->total(),
            'current_page' => $notifications->currentPage(),
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi khi lấy danh sách thông báo cho seller: ' . $e->getMessage(), [
            'user_id' => auth()->id(),
            'params' => $request->query()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi lấy danh sách thông báo.',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}


    public function sellerShow($id)
    {
        try {
            $user = auth()->user();

            $notification = Notification::with([
                'users:id,name,email,role',
                'recipients.user:id,name,email,role'
            ])
                ->where(function ($query) use ($user) {
                    $query->whereJsonContains('to_roles', $user->role)
                        ->orWhereHas('recipients', function ($sub) use ($user) {
                            $sub->where('user_id', $user->id);
                        });
                })
                ->findOrFail($id);

            $notification->to_roles = json_decode($notification->to_roles, true);
            $notification->channels = json_decode($notification->channels, true);

            $baseImageUrl = env('R2_URL');
            if ($notification->image_url && !str_starts_with($notification->image_url, 'http')) {
                $notification->image_url = rtrim($baseImageUrl, '/') . '/' . ltrim($notification->image_url, '/');
            }

            $notification->recipients = $notification->recipients->map(function ($recipient) {
                return [
                    'id' => $recipient->id,
                    'is_read' => $recipient->is_read,
                    'read_at' => $recipient->read_at,
                    'is_hidden' => $recipient->is_hidden,
                    'user' => $recipient->user ? [
                        'id' => $recipient->user->id,
                        'name' => $recipient->user->name,
                        'email' => $recipient->user->email,
                        'role' => $recipient->user->role,
                    ] : null
                ];
            });

            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông báo.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 404);
        }
    }
}
