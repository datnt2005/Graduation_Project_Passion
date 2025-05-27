<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{

    // đăng kí
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[\w.+-]+@((gmail\.com)|(yahoo\.com)|(hotmail\.com)|([\w-]+\.edu))$/i',
            ],
            'password' => ['required', 'confirmed', Password::min(6)],
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:users,phone',
                'regex:/^(\+84|0)(3[2-9]|5[6-9]|7[0-9]|8[1-9]|9[0-9])[0-9]{7}$/',
            ],
        ], [
            'required' => 'Trường :attribute là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.regex' => 'Email phải có định dạng gmail.com, yahoo.com, hotmail.com hoặc .edu.',
            'email.unique' => 'Email đã được đăng ký.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'phone.unique' => 'Số điện thoại đã được đăng ký.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);
        if (!filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Email không hợp lệ.'], 422);
        }


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
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công.',
            'user_id' => $user->id,
        ], 201);
    }

    // xác minh OTP

    public function verifyOtp(Request $request)
{
    $validated = $request->validate([
        'user_id' => ['required', 'exists:users,id'],
        'otp' => ['required', 'digits:6'],
    ], [
        'user_id.required' => 'User ID là bắt buộc.',
        'user_id.exists' => 'User không tồn tại.',
        'otp.required' => 'Mã OTP là bắt buộc.',
        'otp.digits' => 'Mã OTP phải gồm 6 chữ số.',
    ]);

    $user = User::find($validated['user_id']);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Người dùng không tồn tại.',
        ], 404);
    }

    if ($user->otp !== $validated['otp']) {
        return response()->json([
            'success' => false,
            'message' => 'Mã OTP không chính xác.',
        ], 400);
    }

    if (now()->greaterThan($user->otp_expired_at)) {
        return response()->json([
            'success' => false,
            'message' => 'Mã OTP đã hết hạn.',
        ], 400);
    }

    $user->is_verified = true;
    $user->otp = null;
    $user->otp_expired_at = null;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Xác minh OTP thành công.',
    ]);
}

// đăng nhập
public function login(Request $request)
{
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ], [
        'email.required' => 'Email là bắt buộc.',
        'email.email' => 'Email không đúng định dạng.',
        'password.required' => 'Mật khẩu là bắt buộc.',
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (!$user || !\Hash::check($validated['password'], $user->password)) {
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

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'token' => $token,
        'user' => $user,
    ]);
}

// gửi lại mã OTP cho người dùng
public function resendOtp(Request $request)
{
    $validated = $request->validate([
        'user_id' => ['required', 'exists:users,id'],
    ], [
        'user_id.required' => 'User ID là bắt buộc.',
        'user_id.exists' => 'Người dùng không tồn tại.',
    ]);

    $user = User::find($validated['user_id']);

    if ($user->is_verified) {
        return response()->json([
            'success' => false,
            'message' => 'Người dùng đã xác minh. Không cần gửi lại OTP.',
        ], 400);
    }

    $otp = rand(100000, 999999);
    $otpExpiredAt = now()->addMinutes(5);

    $user->otp = $otp;
    $user->otp_expired_at = $otpExpiredAt;
    $user->save();

    // Gửi lại email
    Mail::to($user->email)->send(new OtpMail($otp));

    return response()->json([
        'success' => true,
        'message' => 'Mã OTP đã được gửi lại.',
    ]);
}

}
