<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Đăng ký
public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                    'regex:/^[\w.+-]+@((gmail\.com)|(yahoo\.com)|(hotmail\.com)|([\w-]+\.edu))$/i',
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->numbers()
                        ->uncompromised(),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:11',
                    'unique:users,phone',
                    'regex:/^(\+84|0)(3[2-9]|5[6-9]|7[0-9]|8[1-9]|9[0-9])[0-9]{7}$/',
                ],
            ], [
                'name.required' => 'Trường tên là bắt buộc.',
                'email.required' => 'Trường email là bắt buộc.',
                'email.email' => 'Email không đúng định dạng.',
                'email.regex' => 'Email phải có định dạng gmail.com, yahoo.com, hotmail.com hoặc .edu.',
                'email.unique' => 'Email đã được đăng ký.',
                'password.required' => 'Trường mật khẩu là bắt buộc.',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
                'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
                'password.numbers' => 'Mật khẩu phải chứa ít nhất một số.',
                'password.uncompromised' => 'Mật khẩu quá yếu, vui lòng chọn mật khẩu khác.',
                'phone.required' => 'Trường số điện thoại là bắt buộc.',
                'phone.unique' => 'Số điện thoại đã được đăng ký.',
                'phone.regex' => 'Số điện thoại không hợp lệ.',
                'phone.max' => 'Số điện thoại không được vượt quá 11 ký tự.',
            ]);

            $validated['phone'] = preg_replace('/\D/', '', $validated['phone']);
            $validated['email'] = strtolower($validated['email']);

            $otp = rand(100000, 999999);
            $otpExpiredAt = Carbon::now()->addMinutes(5);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'role' => 'user',
                'status' => 'active',
                'otp' => $otp,
                'otp_expired_at' => $otpExpiredAt,
                'is_verified' => false,
            ]);

            try {
                Mail::to($user->email)->send(new OtpMail($otp));
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Đăng ký thành công nhưng không thể gửi OTP. Vui lòng thử lại.',
                    'user_id' => $user->id,
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để nhận mã OTP.',
                'user_id' => $user->id,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error during registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đăng ký người dùng.',
            ], 500);
        }
    }

    // Xác minh OTP
public function verifyOtp(Request $request)
    {
        try {
           $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'digits:6'],
        ], [
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
            'otp.required' => 'Mã OTP là bắt buộc.',
            'otp.digits' => 'Mã OTP phải gồm 6 chữ số.',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

            if ($user->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã được xác minh.',
                ], 400);
            }

            if ($user->otp !== $validated['otp']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã OTP không chính xác.',
                ], 400);
            }

            if (Carbon::now()->greaterThan($user->otp_expired_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã OTP đã hết hạn.',
                ], 400);
            }

            $user->update([
                'is_verified' => true,
                'otp' => null,
                'otp_expired_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Xác minh OTP thành công.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error during OTP verification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xác minh OTP.',
            ], 500);
        }
    }

    // Đăng nhập

public function login(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Trường email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Trường mật khẩu là bắt buộc.',
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng.',
            ], 401);
        }

        if (!$user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được xác minh. Vui lòng kiểm tra email để xác minh OTP.',
            ], 403);
        }

        if ($user->status !== 'active') {
            $messages = "";
                 if( $user->status === 'inactive') {
                   $messages = 'Hiện tại tài khoản của bạn đang không hoạt động. Vui lòng liên hệ quản trị viên để biết thêm chi tiết.';
                } elseif ($user->status === 'banned') {
                    $messages = 'Tài khoản đã bị cấm. Vui lòng liên hệ quản trị viên.';
                } else {
                    $messages = 'Tài khoản hiện đang ' . $user->status . '. Vui lòng liên hệ quản trị viên.';
                }
            return response()->json([
                'success' => false,
                'message' => $messages,
            ], 403);
        }

        $token = $user->createToken('api_token')->plainTextToken;

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

        session([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'token' => $token,
            'user' => $user,
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Error during login: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi đăng nhập.',
        ], 500);
    }
}

public function me(Request $request)
{
    $user = $request->user();

    return response()->json(['data' => new UserResource($user)]);
}

// Gửi lại mã OTP
public function resendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'numeric', 'exists:users,id'],
            ], [
                'user_id.required' => 'User ID là bắt buộc.',
                'user_id.numeric' => 'User ID phải là số.',
                'user_id.exists' => 'Người dùng không tồn tại.',
            ]);

            $user = User::findOrFail($validated['user_id']);

            if ($user->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã được xác minh. Không cần gửi lại OTP.',
                ], 400);
            }

            $otp = rand(100000, 999999);
            $otpExpiredAt = Carbon::now()->addMinutes(5);

            $user->update([
                'otp' => $otp,
                'otp_expired_at' => $otpExpiredAt,
            ]);

            // Send OTP email
            try {
                Mail::to($user->email)->send(new OtpMail($otp));
            } catch (\Exception $e) {
                \Log::error('Failed to resend OTP email: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể gửi OTP. Vui lòng thử lại.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được gửi lại.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error during OTP resend: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi lại OTP.',
            ], 500);
        }
    }

public function resendOtpByEmail(Request $request)
{
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user->is_verified) {
        return response()->json(['message' => 'Tài khoản đã được xác minh.'], 400);
    }

    $otp = rand(100000, 999999);
    $user->otp = $otp;
    $user->otp_expired_at = now()->addMinutes(10);
    $user->save();

    Mail::to($user->email)->send(new OtpMail($otp));

    return response()->json(['message' => 'Mã xác minh đã được gửi lại.']);
}

public function sendForgotPassword(Request $request)
{
     $request->validate([
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Vui lòng nhập email.',
        'email.email' => 'Email không đúng định dạng.',
        'email.exists' => 'Email không tồn tại trong hệ thống.',
    ]);


    $user = User::where('email', $request->email)->first();

    // Tạo mã OTP
    $otp = rand(100000, 999999);
    $user->otp = $otp;
    $user->otp_expired_at = now()->addMinutes(10);
    $user->save();

    // Gửi email
    Mail::to($user->email)->send(new OtpMail($otp));

    return response()->json(['message' => 'OTP đã được gửi đến email.'], 200);
}

// POST /api/reset-password
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|digits:6',
        'password' => [
            'required',
            'string',
            'min:6',
            'confirmed'
        ],
    ], [
        'email.required' => 'Vui lòng nhập email.',
        'email.email' => 'Email không đúng định dạng.',
        'email.exists' => 'Email không tồn tại trong hệ thống.',
        'otp.required' => 'Vui lòng nhập mã OTP.',
        'otp.digits' => 'Mã OTP phải gồm 6 chữ số.',
        'password.required' => 'Vui lòng nhập mật khẩu mới.',
        'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || $user->otp != $request->otp) {
        return response()->json(['message' => 'Mã OTP không hợp lệ.'], 422);
    }

    if (now()->gt($user->otp_expired_at)) {
        return response()->json(['message' => 'Mã OTP đã hết hạn.'], 422);
    }

    $user->password = Hash::make($request->password);
    $user->otp = null;
    $user->otp_expired_at = null;
    $user->save();

    return response()->json(['message' => 'Đặt lại mật khẩu thành công.'], 200);
}


// logout
public function logout(Request $request)
{
    try {
        $token = $request->bearerToken();
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            Log::warning('Logout failed: Invalid token.', [
                'ip' => $request->ip(),
                'authorization' => $token,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng đang đăng nhập.',
                'token' => $token
            ], 401);
        }

        $user = $accessToken->tokenable;

        Log::info('Attempting logout for user', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $redisKey = 'user:session:' . $user->id;
        if (Redis::exists($redisKey)) {
            Redis::del($redisKey);
            Log::info('Deleted Redis key', ['key' => $redisKey]);
        } else {
            Log::info('Redis key not found', ['key' => $redisKey]);
        }

        $accessToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.',
        ]);
    } catch (\Exception $e) {
        Log::error('Error during logout: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : 'Có lỗi xảy ra khi đăng xuất. Vui lòng thử lại!',
            'trace'   => config('app.debug') ? $e->getTrace() : null, // Chỉ show khi debug, bình thường để null
        ], 500);
    }
}


}