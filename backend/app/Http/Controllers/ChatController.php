<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\ChatAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // 📩 Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        Log::info('💬 Gửi tin nhắn với dữ liệu:', $request->all());

        $request->validate([
            'session_id'    => 'nullable|exists:chat_sessions,id',
            'receiver_id'   => 'nullable|integer',
            'sender_type'   => 'required|in:user,seller',
            'sender_id'     => 'required|integer',
            'message_type'  => 'required|in:text,image,product',
            'message'       => 'nullable|string',
            'file'          => 'nullable|array',
            'file.*'        => 'file|mimes:jpg,jpeg,png,webp|max:2048',
            'meta_data'     => 'nullable|array'
        ]);

        // Nếu chưa có session, tạo mới
        if ($request->filled('session_id')) {
            $session = ChatSession::findOrFail($request->session_id);
        } else {
            if (!$request->filled('receiver_id')) {
                return response()->json(['error' => 'receiver_id is required if session_id is not provided.'], 422);
            }

            $session = ChatSession::create([
                'user_id'         => $request->sender_type === 'user' ? $request->sender_id : $request->receiver_id,
                'seller_id'       => $request->sender_type === 'seller' ? $request->sender_id : $request->receiver_id,
                'status'          => 'open',
                'last_message_at' => now()
            ]);
        }

        // 📝 Tạo tin nhắn
        $message = ChatMessage::create([
            'session_id'    => $session->id,
            'sender_type'   => $request->sender_type,
            'sender_id'     => $request->sender_id,
            'message'       => $request->message,
            'message_type'  => $request->message_type,
            'is_read'       => false
        ]);

        // 📎 Nếu có file gửi kèm
        if ($request->hasFile('file') && is_array($request->file('file'))) {
            foreach ($request->file('file') as $file) {
                if ($file->isValid()) {
                    $filename = 'chat_uploads/' . time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

                    logger()->info('📤 Upload file chat lên R2', [
                        'filename' => $filename,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ]);

                    try {
                        $uploadResult = Storage::disk('r2')->put($filename, file_get_contents($file));

                        if ($uploadResult) {
                            $fileUrl = Storage::disk('r2')->url($filename);

                            ChatAttachment::create([
                                'message_id' => $message->id,
                                'file_type'  => 'image',
                                'file_url'   => $fileUrl
                            ]);
                        } else {
                            logger()->error('❌ Upload ảnh thất bại', ['filename' => $filename]);
                        }
                    } catch (\Exception $e) {
                        logger()->error('❌ Lỗi upload ảnh lên R2', ['error' => $e->getMessage()]);
                    }
                }
            }
        }

        // 📦 Nếu là sản phẩm
        if ($request->message_type === 'product' && $request->meta_data) {
            ChatAttachment::create([
                'message_id' => $message->id,
                'file_type'  => 'product',
                'file_url'   => '#',
                'meta_data'  => $request->meta_data
            ]);
        }

        // ⏰ Cập nhật thời gian cuối cùng
        $session->update(['last_message_at' => now()]);

        // 🧹 Xoá cache liên quan
        Cache::store('redis')->forget("chat_messages_session_{$session->id}");
        Cache::store('redis')->forget("chat_sessions_seller_{$session->seller_id}");
        Cache::store('redis')->forget("chat_sessions_user_{$session->user_id}");

        return response()->json([
            'chat_message' => $message->load('attachments')
        ]);
    }

    // 📥 Lấy tin nhắn theo session (có cache)
    public function getMessages($sessionId)
    {
        try {
            $cacheKey = "chat_messages_session_{$sessionId}";

            $messages = Cache::store('redis')->remember($cacheKey, 60, function () use ($sessionId) {
                return ChatMessage::with('attachments')
                    ->where('session_id', $sessionId)
                    ->orderBy('created_at')
                    ->get();
            });

            return response()->json($messages);
        } catch (\Exception $e) {
            Log::error("❌ Lỗi getMessages: {$e->getMessage()}");
            return response()->json([], 500);
        }
    }

    // 📚 Lấy danh sách cuộc trò chuyện (có cache)
   public function getSessions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'type'    => 'required|in:user,seller'
        ]);

        $cacheKey = "chat_sessions_{$request->type}_{$request->user_id}";

        Cache::store('redis')->forget($cacheKey);
        $sessions = Cache::store('redis')->remember($cacheKey, 60, function () use ($request) {
            $relations = [
                'messages' => function ($q) {
                    $q->latest('created_at')->limit(1);
                }
            ];

            if ($request->type === 'seller') {
                // Nếu là seller → load user (khách) gắn với session
                $relations[] = 'user:id,name,avatar';
            } else {
                // Nếu là user → load seller + seller.user (để lấy avatar)
                $relations[] = 'seller:id,store_name,user_id';
                $relations[] = 'seller.user:id,avatar';
            }

            return ChatSession::with($relations)
                ->where($request->type . '_id', $request->user_id)
                ->orderByDesc('last_message_at')
                ->get();
        });

        return response()->json($sessions);
    }

}
