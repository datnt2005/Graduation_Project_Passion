<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\User;
use App\Models\BusinessSeller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Exception;

class SellerController extends Controller
{
    public function index()
    {
       $sellers = User::whereHas('seller')
                   ->with('seller.business') 
                   ->get();
        return response()->json($sellers);
    }

   public function showStore($slug)
    {
        $seller = Seller::with([
            'user',
            'business',
            'products' => function ($query) {
                $query->with(['productVariants', 'productPic', 'categories', 'tags']);
            }
        ])->where('store_slug', $slug)->firstOrFail();

        return response()->json([
            'seller' => $seller,
        ]);
    }


    public function register(Request $request){
       $validator = validator::make($request->all(), [
        'store_name' => 'required|string|max:255',
            'seller_type' => 'required|in:personal,business',
            'identity_card_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'personal_address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'document' => 'nullable|file',
            'bio' => 'nullable|string',
            // For business type
            'tax_code' => 'required_if:seller_type,business',
            'company_name' => 'required_if:seller_type,business',
            'company_address' => 'required_if:seller_type,business',
            'business_license' => 'required_if:seller_type,business|file',
            'representative_name' => 'required_if:seller_type,business',
            'representative_phone' => 'required_if:seller_type,business'
       ], [
           // Cá nhân
            'store_name.required' => 'Tên cửa hàng là bắt buộc.',
            'seller_type.required' => 'Vui lòng chọn loại người bán.',
            'identity_card_number.required' => 'Vui lòng nhập số CMND/CCCD.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'personal_address.required' => 'Vui lòng nhập địa chỉ cá nhân.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',

            // Doanh nghiệp
            'tax_code.required_if' => 'Vui lòng nhập mã số thuế.',
            'company_name.required_if' => 'Vui lòng nhập tên công ty.',
            'company_address.required_if' => 'Vui lòng nhập địa chỉ công ty.',
            'business_license.required_if' => 'Vui lòng tải lên giấy phép kinh doanh.',
            'representative_name.required_if' => 'Vui lòng nhập tên người đại diện.',
            'representative_phone.required_if' => 'Vui lòng nhập số điện thoại người đại diện.',

            // Chung
            'store_name.max' => 'Tên cửa hàng không được vượt quá 255 ký tự.',
            'identity_card_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự.',
            'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'document.file' => 'Tệp đính kèm không hợp lệ.',
            'business_license.file' => 'Tệp giấy phép kinh doanh phải là tệp hợp lệ.',
       ]);

       if($validator->fails()){
           return response()->json($validator->errors(), 422);
       }

        $userId = $request->input('user_id', auth()->id());

        // 👉 Check trùng
        if (Seller::where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'Bạn đã đăng ký cửa hàng rồi. Không thể tạo thêm.'
            ], 409);
        }

       $storeSlug = Str::slug($request->store_name) . '-' . uniqid();

        $documentPath = $request->hasFile('document')
            ? $request->file('document')->store('documents', 'public')
            : null;
        // $userId = $request->input('user_id', auth()->id());
        $seller = Seller::create([
            'user_id' => auth()->id(),
            // 'user_id' => $userId,
            'store_name' => $request->store_name,
            'store_slug' => $storeSlug,
            'seller_type' => $request->seller_type,
            'identity_card_number' => $request->identity_card_number,
            'date_of_birth' => $request->date_of_birth,
            'personal_address' => $request->personal_address,
            'phone_number' => $request->phone_number,
            'document' => $documentPath,
            'bio' => $request->bio,
            'verification_status' => 'pending',
        ]);

        if ($request->seller_type === 'business') {
            $licensePath = $request->hasFile('business_license')
                ? $request->file('business_license')->store('licenses', 'public')
                : null;

            $seller->business()->create([
                'tax_code' => $request->tax_code,
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'business_license' => $licensePath,
                'representative_name' => $request->representative_name,
                'representative_phone' => $request->representative_phone,
            ]);
        }

        return response()->json([
            'message' => 'Đăng ký bán hàng thành công! Vui lòng chờ xét duyệt.',
            'seller' => $seller->load('business')
        ]);
    }

 public function login(Request $request)
    {
        try {
            // B1: Validate dữ liệu đầu vào
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Email không hợp lệ.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // B2: Thử đăng nhập
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
            }

            $user = Auth::user();

            // B3: Kiểm tra xác minh tài khoản
            $seller = null;
            $store_slug = null;

            if ($user->role === 'seller') {
                $seller = \App\Models\Seller::where('user_id', $user->id)->first();

                if (!$seller) {
                    Auth::logout(); // Đăng xuất để tránh trạng thái không hợp lệ
                    return response()->json([
                        'message' => 'Bạn chưa đăng ký cửa hàng. Vui lòng hoàn tất hồ sơ.',
                    ], 403);
                }

                if ($seller->verification_status !== 'verified') {
                    Auth::logout(); // Đăng xuất để tránh trạng thái không hợp lệ
                    return response()->json([
                        'message' => 'Tài khoản của bạn đang chờ xác nhận.',
                    ], 403);
                }

                $store_slug = $seller->store_slug;
            }
            // B4: Tạo token cho user
            $token = $user->createToken('api_token')->plainTextToken;

            // B5: Lưu Redis
            $redisKey = 'user:session:' . $user->id;
            $redisData = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'status' => $user->status,
                'avatar' => $user->avatar,
                'token' => $token,
                'store_slug' => $store_slug,
                'logged_in_at' => now()->toDateTimeString(),
            ];
            Redis::setex($redisKey, 7200, json_encode($redisData)); // TTL 2 giờ

            // B6: Ghi session tạm
            session([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'store_slug' => $store_slug,
            ]);

            // B7: Trả response thành công
            return response()->json([
                'message' => 'Đăng nhập thành công!',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                ],
                'store_slug' => $store_slug,
            ], 200);
        } catch (Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'], 500);
        }
    }

// đăng xuất

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
