<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\ChatAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        $request->validate([
            'user_id' => 'required|integer',
            'type'    => 'required|in:user,seller',
        ]);

        $relations = [
            'messages' => function ($q) {
                $q->latest('created_at')->limit(10);
            },
        ];

        if ($request->type === 'seller') {
            $relations['user'] = fn($q) => $q->select('id', 'name', 'avatar');
        } else {
            $relations['seller'] = fn($q) => $q->select('id', 'store_name', 'user_id');
            $relations['seller.user'] = fn($q) => $q->select('id', 'avatar');
        }

        // ✅ Truy vấn trực tiếp, bỏ qua cache để dễ debug
        $sessions = ChatSession::with($relations)
            ->where($request->type . '_id', $request->user_id)
            ->orderByDesc('last_message_at')
            ->get(['id', 'user_id', 'seller_id', 'last_message', 'last_message_at', 'created_at', 'updated_at']);

        return response()->json([
            'data' => $sessions
        ]);
    }


    // 3️⃣ Gửi tin nhắn mới (text, ảnh hoặc sản phẩm)
    public function sendMessage(Request $request)
    {
        Log::info('📩 Bắt đầu xử lý sendMessage', ['session_id' => $request->session_id]);

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

        // === Xử lý ảnh đính kèm ===
        if ($request->message_type === 'image' && $request->hasFile('file')) {
            foreach ($request->file('file') as $index => $file) {
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
                    Log::error('❌ Lỗi upload ảnh:', ['error' => $e->getMessage()]);
                    return response()->json(['error' => 'Lỗi server khi upload ảnh: ' . $e->getMessage()], 500);
                }
            }
        }

        // === Xử lý gửi sản phẩm ===
        if ($request->message_type === 'product' && $request->meta_data) {
            $attachments[] = [
                'file_type' => 'product',
                'file_url'  => '#',
                'meta_data' => $request->meta_data,
            ];
        }

        // === Tạo tin nhắn chính ===
        $messageContent = $request->message ?? '';
        $message = ChatMessage::create([
            'session_id'   => $session->id,
            'sender_id'    => $request->sender_id,
            'sender_type'  => $request->sender_type,
            'message'      => $messageContent,
            'message_type' => $request->message_type,
            'status'       => 'normal',
        ]);

        // === Lưu đính kèm nếu có ===
        if (!empty($attachments)) {
            ChatAttachment::create([
                'message_id'  => $message->id,
                'attachments' => $attachments,
            ]);
        }

        // === Cập nhật last_message rõ ràng theo loại ===
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

        // === Xóa cache liên quan ===
        Cache::store('redis')->forget("chat_messages_session_{$session->id}");
        Cache::store('redis')->forget("chat_sessions_seller_{$session->seller_id}");
        Cache::store('redis')->forget("chat_sessions_user_{$session->user_id}");

        return response()->json([
            'message'     => $message,
            'attachments' => $attachments,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // 4️⃣ Lấy tất cả tin nhắn của 1 phiên
    public function getMessages(Request $request ,$sessionId)
    {
        try {
            $limit = $request->input('limit', 20);
            $page = $request->input('page', 1);

            $messages = ChatMessage::with('attachments')
                ->where('session_id', $sessionId)
                ->whereIn('message_type', ['text', 'image', 'product'])
                ->orderBy('created_at', 'asc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

           $formattedMessages = $messages->map(function ($message) {
                $attachments = $message->attachments->pluck('attachments')->flatten(1)->all() ?? [];

                return [
                    'id'            => $message->id,
                    'session_id'    => $message->session_id,
                    'sender_id'     => $message->sender_id,
                    'sender_type'   => $message->sender_type,
                    'message'       => $message->message ?? '',
                    'message_type'  => $message->message_type,
                    'status'        => $message->status,
                    'created_at'    => $message->created_at,
                    'updated_at'    => $message->updated_at,
                    'attachments'   => $attachments,
                    'avatar'        => $message->senderUser?->avatar
                                        ? url($message->senderUser->avatar)
                                        : null,
                ];
            });


            return response()->json([
                'data' => $formattedMessages
            ]);
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
        $updated = ChatMessage::where('session_id', $sessionId)
            ->where('sender_type', '!=', $request->input('sender_type'))
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'updated' => $updated,
            'message' => 'Tin nhắn đã được đánh dấu là đã đọc.'
        ]);
    }
}
