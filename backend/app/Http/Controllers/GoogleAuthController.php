<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser->getEmail() || !$googleUser->getId()) {
                throw new \Exception('Dữ liệu người dùng Google không hợp lệ.');
            }

            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            $avatarPath = $googleUser->getAvatar();

            if ($user) {
                if ($user->google_id !== $googleUser->getId() || $user->avatar !== $avatarPath) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $avatarPath ?? $user->avatar
                    ]);
                }
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $avatarPath ?? 'default-avatar.png',
                    'google_id' => $googleUser->getId(),
                    'phone' => null,
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                    'is_verified' => true,
                    'status' => 'active',
                    'role' => 'user',
                ]);
            }

            // Kiểm tra trạng thái user
            if (!$user->is_verified) {
                return response()->view('auth.google-error', [
                    'message' => 'Tài khoản chưa được xác minh, vui lòng xác minh trước khi đăng nhập.',
                ], 403);
            }

            if ($user->status !== 'active') {
                return response()->view('auth.google-error', [
                    'message' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ hỗ trợ.',
                ], 403);
            }

            // Token thành công
            $token = $user->createToken('api_token')->plainTextToken;

            // luu vao redis
            $redisKey = 'user:session:' . $user->id;
                    $redisData = [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                        'role' => $user->role,
                        'status' => $user->status,
                        'avatar' => $user->avatar,
                        'token' => $token,
                        'logged_in_at' => now()->toDateTimeString(),
                    ];
                    Redis::setex($redisKey, 7200, json_encode($redisData));


            return response()->view('auth.google-callback', [
                'token' => $token,
                'user' => (new UserResource($user))->toArray($request),
            ]);

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Lỗi Google OAuth (Invalid State): ' . $e->getMessage());
            $errorMessage = config('app.debug') ? $e->getMessage() : 'Trạng thái xác thực không hợp lệ. Vui lòng thử lại.';
            return response()->view('auth.google-error', ['message' => $errorMessage], 500);
        } catch (\Exception $e) {
            Log::error('Lỗi Google OAuth: ' . $e->getMessage());
            $errorMessage = config('app.debug') ? $e->getMessage() : 'Đăng nhập bằng Google thất bại. Vui lòng thử lại sau.';
            return response()->view('auth.google-error', ['message' => $errorMessage], 500);
        }
    }
}