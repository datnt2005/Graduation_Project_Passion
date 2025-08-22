<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\ChatAttachment;
use App\Models\Notification;
use App\Models\User;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    // 1️⃣ Tạo phiên chat giữa user và seller
    public function createSession(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'seller_id' => 'required|exists:sellers,id',
        ]);

        $session = ChatSession::firstOrCreate([
            'user_id'   => $request->user_id,
            'seller_id' => $request->seller_id,
        ]);

        return response()->json($session);
    }

    // 2️⃣ Lấy danh sách các phiên chat của user hoặc seller
    public function getSessions(Request $request)
    {
        $userId = $request->query('user_id');
        $type   = $request->query('type');

        try {
            if (!$userId || !in_array($type, ['user', 'seller'])) {
                Log::warning('Invalid parameters for chat sessions', [
                    'user_id' => $userId,
                    'type'    => $type
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tham số không hợp lệ'
                ], 400);
            }

            $query = ChatSession::query();

            if ($type === 'user') {
                $query->where('user_id', $userId);
            } elseif ($type === 'seller') {
                $query->where('seller_id', $userId);
            }

            $sessions = $query
                ->with([
                    'user:id,name,avatar', // Chỉ lấy id, name, avatar của user
                    'seller:id,user_id,store_name,store_slug', // Chỉ lấy id, user_id, store_name, store_slug của seller
                    'seller.user:id,name,avatar' // Lấy thông tin user của seller
                ])
                ->withCount(['messages as unread_count' => function ($q) use ($type) {
                    $q->where('is_read', false)
                        ->where('sender_type', $type === 'user' ? 'seller' : 'user');
                }])
                ->orderByDesc(function ($q) {
                    $q->select('created_at')
                        ->from('chat_messages')
                        ->whereColumn('chat_messages.session_id', 'chat_sessions.id')
                        ->orderByDesc('created_at')
                        ->limit(1);
                })
                ->orWhereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('chat_messages')
                        ->whereColumn('chat_messages.session_id', 'chat_sessions.id');
                })
                ->orderByDesc('created_at')
                ->get([
                    'id',
                    'user_id',
                    'seller_id',
                    'last_message',
                    'last_message_at',
                    'created_at',
                    'updated_at',
                ]);

            return response()->json([
                'success' => true,
                'data'    => $sessions
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chat sessions', [
                'user_id' => $userId,
                'type'    => $type,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy chat sessions',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 3️⃣ Gửi tin nhắn mới (text, image, product)
    public function sendMessage(Request $request)
    {
        Log::info('📩 sendMessage', ['session_id' => $request->session_id]);

        $request->validate([
            'session_id'   => 'required|exists:chat_sessions,id',
            'sender_id'    => 'required|integer',
            'sender_type'  => 'required|in:user,seller',
            'message'      => 'nullable|string',
            'message_type' => 'required|in:text,image,product',
            'file.*'       => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'meta_data'    => 'nullable|array',
        ]);

        $session = ChatSession::findOrFail($request->session_id);
        $attachments = [];

        // Ảnh đính kèm
        if ($request->message_type === 'image' && $request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                if (!$file->isValid()) {
                    return response()->json(['error' => 'File ảnh không hợp lệ: ' . $file->getClientOriginalName()], 400);
                }

                $filename = 'chat_uploads/' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                try {
                    $content = file_get_contents($file->getRealPath());
                    if (strlen($content) === 0) continue;

                    $put = Storage::disk('r2')->put($filename, $content);
                    if ($put) {
                        $attachments[] = [
                            'file_type' => 'image',
                            'file_url'  => Storage::disk('r2')->url($filename),
                            'meta_data' => [
                                'original_name' => $file->getClientOriginalName(),
                                'mime_type'     => $file->getMimeType(),
                                'size'          => $file->getSize(),
                            ]
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('❌ Lỗi upload ảnh', ['error' => $e->getMessage()]);
                    return response()->json(['error' => 'Lỗi server khi upload ảnh: ' . $e->getMessage()], 500);
                }
            }
        }

        // Gửi sản phẩm
        if ($request->message_type === 'product' && $request->meta_data) {
            $attachments[] = [
                'file_type' => 'product',
                'file_url'  => '#',
                'meta_data' => $request->meta_data,
            ];
        }

        // Tạo tin nhắn
        $messageContent = $request->message ?? '';
        $message = ChatMessage::create([
            'session_id'   => $session->id,
            'sender_id'    => $request->sender_id,
            'sender_type'  => $request->sender_type,
            'message'      => $messageContent,
            'message_type' => $request->message_type,
            'is_read'      => false,
            'status'       => 'normal',
        ]);

        if (!empty($attachments)) {
            ChatAttachment::create([
                'message_id'  => $message->id,
                'attachments' => $attachments,
            ]);
        }

        // Preview last_message
        $previewText = match ($request->message_type) {
            'text'    => $messageContent,
            'image'   => '[Ảnh]',
            'product' => '[Sản phẩm]',
            default   => '[Đính kèm]',
        };

        $session->update([
            'last_message'    => $previewText,
            'last_message_at' => now(),
        ]);

        $this->flushSessionCaches($session->id, $session->seller_id, $session->user_id);

        return response()->json([
            'message'     => $message,
            'attachments' => $attachments,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // 4️⃣ Lấy tin nhắn của 1 phiên (phân trang, ẩn tin đã thu hồi)
    public function getMessages(Request $request, $sessionId)
    {
        try {
            $limit = (int) $request->input('limit', 20);
            $limit = $limit > 0 ? $limit : 20;
            $page  = (int) $request->input('page', 1);
            $page  = $page > 0 ? $page : 1;

            $messages = ChatMessage::with('attachments')
                ->where('session_id', $sessionId)
                ->where('status', '!=', 'deleted')                // ẩn tin đã thu hồi
                ->whereIn('message_type', ['text', 'image', 'product'])
                ->with('userSender')
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get()
                ->sortBy('created_at')                            // hiển thị tăng dần
                ->values();

            $formatted = $messages->map(function (ChatMessage $m) {
                $attachments = $m->attachments->pluck('attachments')->flatten(1)->all() ?? [];
                return [
                    'id'           => $m->id,
                    'session_id'   => $m->session_id,
                    'sender_id'    => $m->sender_id,
                    'sender_type'  => $m->sender_type,
                    'message'      => $m->message ?? '',
                    'message_type' => $m->message_type,
                    'status'       => $m->status,
                    'created_at'   => $m->created_at,
                    'updated_at'   => $m->updated_at,
                    'attachments'  => $attachments,
                    'avatar'       => $m->userSender?->avatar ?? '',
                ];
            });

            return response()->json(['data' => $formatted]);
        } catch (\Exception $e) {
            Log::error('❌ Lỗi trong getMessages: ' . $e->getMessage(), ['session_id' => $sessionId]);
            return response()->json([
                'error' => 'Lỗi server khi lấy tin nhắn: ' . $e->getMessage()
            ], 500);
        }
    }

    // 5️⃣ Đánh dấu tin nhắn là đã đọc
    public function markAsRead(Request $request, $sessionId)
    {
        try {
            $senderType = $request->input('sender_type');
            if (!in_array($senderType, ['user', 'seller'])) {
                Log::warning('Invalid sender_type for markAsRead', [
                    'session_id'  => $sessionId,
                    'sender_type' => $senderType
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'sender_type không hợp lệ'
                ], 400);
            }

            if (!ChatSession::where('id', $sessionId)->exists()) {
                Log::warning('Session not found for markAsRead', ['session_id' => $sessionId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Phiên chat không tồn tại'
                ], 404);
            }

            $updated = ChatMessage::where('session_id', $sessionId)
                ->where('sender_type', '!=', $senderType)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            Log::info('Messages marked as read', [
                'session_id'     => $sessionId,
                'sender_type'    => $senderType,
                'updated_count'  => $updated
            ]);

            return response()->json([
                'success' => true,
                'updated' => $updated,
                'message' => 'Tin nhắn đã được đánh dấu là đã đọc.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in markAsRead', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đánh dấu tin nhắn đã đọc',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 6️⃣ Sửa / Thu hồi (xoá) tin nhắn
    public function messageAction(Request $request, $messageId)
    {
        $request->validate([
            'action'  => 'required|in:edit,revoke',
            'message' => 'nullable|string|required_if:action,edit',
        ]);

        $message = ChatMessage::with('session')->findOrFail($messageId);
        $session = $message->session;

        // TODO: nếu có auth, kiểm tra chủ sở hữu tin nhắn ở đây

        if ($request->action === 'edit') {
            // ✅ Chỉ cho sửa trong 5 phút đầu
            $diffSec = now()->diffInSeconds($message->created_at);
            if ($diffSec > 5 * 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tin nhắn chỉ có thể chỉnh sửa trong 5 phút đầu.',
                ], 403);
            }

            if ($message->message_type !== 'text') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể sửa tin dạng văn bản.',
                ], 422);
            }

            if ($message->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tin nhắn đã bị thu hồi.',
                ], 409);
            }

            $message->message = (string) $request->message;
            $message->status  = 'edited';
            $message->save();

            $this->syncSessionLastMessage($session->id);
            $this->flushSessionCaches($session->id, $session->seller_id, $session->user_id);

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // action = revoke
        if ($message->status === 'deleted') {
            // Đã thu hồi trước đó -> idempotent
            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // ✅ Chỉ cho thu hồi trong 1 ngày (24h) kể từ khi tạo
        $diffSec = now()->diffInSeconds($message->created_at);
        if ($diffSec > 24 * 60 * 60) {
            return response()->json([
                'success' => false,
                'message' => 'Tin nhắn chỉ có thể thu hồi trong vòng 1 ngày.',
            ], 403);
        }

        DB::transaction(function () use ($message) {
            // Xoá attachments để UI không còn gì hiển thị
            ChatAttachment::where('message_id', $message->id)->delete();

            // KHÔNG đổi message_type (tránh lỗi ENUM); chỉ đổi status + xoá nội dung
            $message->status  = 'deleted';
            $message->message = '';
            $message->save();
        });

        $this->syncSessionLastMessage($session->id);
        $this->flushSessionCaches($session->id, $session->seller_id, $session->user_id);

        return response()->json([
            'success' => true,
            'message' => $message,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    private function previewFromMessage(?ChatMessage $m): ?string
    {
        if (!$m) return null;
        return match ($m->message_type) {
            'text'    => $m->message,
            'image'   => '[Ảnh]',
            'product' => '[Sản phẩm]',
            default   => null,
        };
    }

    private function syncSessionLastMessage(int $sessionId): void
    {
        $session = ChatSession::find($sessionId);
        if (!$session) return;

        $last = ChatMessage::where('session_id', $sessionId)
            ->where('status', '!=', 'deleted')                 // bỏ tin đã thu hồi
            ->whereIn('message_type', ['text', 'image', 'product'])
            ->orderByDesc('created_at')
            ->first();

        $session->last_message    = $this->previewFromMessage($last);
        $session->last_message_at = $last?->created_at;
        $session->save();
    }

    private function flushSessionCaches(int $sessionId, int $sellerId, int $userId): void
    {
        Cache::store('redis')->forget("chat_messages_session_{$sessionId}");
        Cache::store('redis')->forget("chat_sessions_seller_{$sellerId}");
        Cache::store('redis')->forget("chat_sessions_user_{$userId}");
    }
}
