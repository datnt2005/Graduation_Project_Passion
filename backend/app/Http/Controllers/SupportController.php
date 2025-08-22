<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Support;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Notification;

class SupportController extends Controller
{
      public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
        ]);
        $support = Support::create($data);

        // Gửi email xác nhận cho user
        try {
            \Mail::send('emails.support_user_created', ['support' => $support], function ($message) use ($support) {
                $message->to($support->email)
                    ->subject('Xác nhận yêu cầu hỗ trợ từ Passion');
            });
        } catch (\Exception $e) {
            \Log::error('Mail send error (support created): ' . $e->getMessage());
            // Không trả lỗi cho user, chỉ log lại
        }
        // Gửi thông báo đến admin\

        $user = auth()->user();
        $admins = User::where('role', 'admin')->get();

        $checkUser = User::find(2);
$userId = $checkUser ? 2 : 1;

        $notification = Notification::create([
            'title' => 'Yêu cầu hỗ trợ mới',
            'content' => "Người dùng {$support->name} ({$support->email}) đã gửi yêu cầu hỗ trợ vào " . now()->format('d/m/Y H:i'),
            'type' => 'system',
            'to_roles' => json_encode(['admin']),
            'link' => "/admin/supports",
            'user_id' => $userId,
            'from_role' => 'system',
            'status' => 'sent',
            'channels' => json_encode(['dashboard']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Gửi hỗ trợ thành công!']);
    }

    // Admin lấy danh sách yêu cầu hỗ trợ
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        $supports = Support::orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $supports
        ]);
    }

    // Admin lấy chi tiết yêu cầu hỗ trợ
    public function show($id)
    {
        $support = Support::findOrFail($id);
        return response()->json(['success' => true, 'data' => $support]);
    }
    // Admin đánh dấu đã xử lý
    public function markAsHandled($id)
    {
        $support = Support::findOrFail($id);
        $support->handled = true;
        $support->handled_at = Carbon::now();
        $support->save();

        return response()->json(['success' => true, 'message' => 'Đã đánh dấu là đã xử lý']);
    }
    // Admin xóa yêu cầu hỗ trợ
    public function destroy($id)
    {
        $support = Support::findOrFail($id);
        $support->delete();

        return response()->json(['success' => true, 'message' => 'Yêu cầu hỗ trợ đã được xóa']);
    }
    // Admin gửi phản hồi
    public function reply(Request $request, $id)
    {
        $support = Support::findOrFail($id);
        $data = $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);
        $support->admin_reply = $data['admin_reply'];
        $support->replied_at = Carbon::now();
        $support->save();

        // Gửi mail phản hồi
        try {
            Mail::send('emails.support_user', ['support' => $support, 'admin_reply' => $data['admin_reply']], function ($message) use ($support) {
                $message->to($support->email)
                    ->subject('Phản hồi hỗ trợ từ Pasion');
            });
        } catch (\Exception $e) {
            \Log::error('Mail send error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không gửi được email: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Đã gửi phản hồi']);
    }
}

