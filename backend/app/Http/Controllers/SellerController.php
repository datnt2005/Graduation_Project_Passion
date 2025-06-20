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
use Illuminate\Support\Facades\Storage;


class SellerController extends Controller
{

  public function index(){
     $sellers = User::whereHas('seller')
                    ->with('seller.business')
                    ->get();
            return response()->json($sellers);
  }



public function getMySellerInfo()
{
    $user = auth()->user();

    // Kiểm tra user có phải seller không
    $seller = Seller::with(['business', 'user:id,name,email,avatar'])
    ->where('user_id', auth()->id())
    ->first();

    if (!$seller) {
        return response()->json([
            'message' => 'Bạn không phải là người bán (seller).'
        ], 403); // Hoặc 404 nếu muốn ẩn thông tin
    }
     $avatarFile = $seller->user->avatar;
    $avatarUrl = $avatarFile
        ? env('R2_AVATAR_URL') . $avatarFile
        : env('R2_AVATAR_URL') . 'default.jpg';

    // Gắn vào response
    $seller->user->avatar_url = $avatarUrl;

    return response()->json([
        'seller' => $seller
    ]);
}
public function update(Request $request)
{
    $user = auth()->user();
    $seller = Seller::where('user_id', $user->id)->firstOrFail();

    $data = $request->only([
        'store_name', 'store_slug', 'seller_type', 'bio',
        'identity_card_number', 'date_of_birth',
        'personal_address', 'phone_number'
    ]);

    // ======= Upload giấy tờ cá nhân lên R2 =======
    if ($request->hasFile('document') && $request->file('document')->isValid()) {
        // Xóa file cũ nếu có
        if ($seller->document) {
            Storage::disk('r2')->delete($seller->document);
        }

        $file = $request->file('document');
        $filename = 'seller-documents/personal/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        $uploadResult = Storage::disk('r2')->put($filename, file_get_contents($file));

        if ($uploadResult) {
            $data['document'] = $filename;
        } else {
            throw new \Exception('Không thể upload file giấy tờ cá nhân lên R2.');
        }
    }

     // ======= Upload CCCD mặt trước =======
    if ($request->hasFile('cccd_front') && $request->file('cccd_front')->isValid()) {
        if ($seller->cccd_front) {
            Storage::disk('r2')->delete($seller->cccd_front);
        }

        $cccdFrontPath = $request->file('cccd_front')->store('seller-documents', 'r2');
        $data['cccd_front'] = $cccdFrontPath;
    }

    // ======= Upload CCCD mặt sau =======
    if ($request->hasFile('cccd_back') && $request->file('cccd_back')->isValid()) {
        if ($seller->cccd_back) {
            Storage::disk('r2')->delete($seller->cccd_back);
        }

        $cccdBackPath = $request->file('cccd_back')->store('seller-documents', 'r2');
        $data['cccd_back'] = $cccdBackPath;
    }


    $seller->update($data);

    // ======= Upload giấy phép kinh doanh =======
    if ($request->seller_type === 'business' && $request->has('business')) {
        $businessData = $request->input('business');

        if ($request->hasFile('business.business_license') && $request->file('business.business_license')->isValid()) {
            // Xóa file cũ nếu có
            if ($seller->business?->business_license) {
                Storage::disk('r2')->delete($seller->business->business_license);
            }

            $file = $request->file('business.business_license');
            $filename = 'seller-documents/business/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadResult = Storage::disk('r2')->put($filename, file_get_contents($file));

            if ($uploadResult) {
                $businessData['business_license'] = $filename;
            } else {
                throw new \Exception('Không thể upload file giấy phép kinh doanh lên R2.');
            }
        }

        BusinessSeller::updateOrCreate(
            ['seller_id' => $seller->id],
            $businessData
        );
    }

    // Lấy lại seller sau khi update
    $seller = Seller::with(['user:id,name,email', 'business'])->findOrFail($seller->id);

    return response()->json([
        'message' => 'Cập nhật thành công',
        'seller' => $seller
    ]);
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

    $isFollowing = false;
    $user = auth('sanctum')->user(); // dùng sanctum thay vì auth()->check()

    if ($user && $user->id !== $seller->user_id) {
        $isFollowing = $seller->followers()->where('user_id', $user->id)->exists();
    }

    return response()->json([
        'seller' => $seller,
        'followers_count' => $seller->followers()->count(),
        'is_following' => $isFollowing,
    ]);
}



   public function register(Request $request)
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'message' => 'Bạn cần đăng nhập để đăng ký cửa hàng.'
                ], 401);
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'store_name' => 'required_if:seller_type,personal|string|max:255',
                'seller_type' => 'required|in:personal,business',
                'identity_card_number' => 'required_if:seller_type,personal|string|max:20',
                'date_of_birth' => 'required_if:seller_type,personal|date',
                'personal_address' => 'required_if:seller_type,personal|string',
                'phone_number' => 'required_if:seller_type,personal|string|max:20',
                'document' => 'nullable|file|mimes:jpg,png,pdf|max:4048',
                'bio' => 'nullable|string',
                'tax_code' => 'required_if:seller_type,business|string',
                'company_name' => 'required_if:seller_type,business|string',
                'company_address' => 'required_if:seller_type,business|string',
                'business_license' => 'required_if:seller_type,business|file|mimes:jpg,png,pdf|max:4048',
                'representative_name' => 'required_if:seller_type,business|string',
                'representative_phone' => 'required_if:seller_type,business|string|max:20',
                'cccd_front' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
                'cccd_back' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            ], [

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra đã có seller chưa
            $seller = Seller::where('user_id', $userId)->first();

            // ====== TRƯỜNG HỢP NÂNG CẤP PERSONAL → BUSINESS ======
            if ($seller && $seller->seller_type === 'personal' && $request->seller_type === 'business' && !$seller->business) {
                // Upload business license
                $licensePath = null;
                if ($request->hasFile('business_license') && $request->file('business_license')->isValid()) {
                    $file = $request->file('business_license');
                    $filename = 'seller-documents/business/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    Storage::disk('r2')->put($filename, file_get_contents($file));
                    $licensePath = $filename;
                }

                $seller->business()->create([
                    'tax_code' => $request->tax_code,
                    'company_name' => $request->company_name,
                    'company_address' => $request->company_address,
                    'business_license' => $licensePath,
                    'representative_name' => $request->representative_name,
                    'representative_phone' => $request->representative_phone,
                ]);

                $seller->update([
                    'seller_type' => 'business',
                    'verification_status' => 'pending'
                ]);

                return response()->json([
                    'message' => 'Nâng cấp thành công lên doanh nghiệp! Vui lòng chờ xác minh.',
                    'data' => $seller->load('business')
                ], 200);
            }

            // Nếu seller đã tồn tại không được tạo mới
            if ($seller) {
                return response()->json([
                    'message' => 'Tài khoản này đã đăng ký cửa hàng hoặc không thể nâng cấp.'
                ], 409);
            }

            // ====== ĐĂNG KÝ MỚI ======
            $storeSlug = Str::slug($request->store_name) . '-' . uniqid();

            // Upload giấy tờ cá nhân nếu có
            $documentPath = null;
            $cccdFront = null;
            $cccdBack = null;
            if ($request->hasFile('document') && $request->file('document')->isValid()) {
                $file = $request->file('document');
                $filename = 'seller-documents/personal/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                $documentPath = $filename;

                // Upload CCCD mặt trước
                if ($request->hasFile('cccd_front') && $request->file('cccd_front')->isValid()) {
                    $cccdFront = $request->file('cccd_front')->store('seller-documents', 'r2');
                }

                // Upload CCCD mặt sau
                if ($request->hasFile('cccd_back') && $request->file('cccd_back')->isValid()) {
                    $cccdBack = $request->file('cccd_back')->store('seller-documents', 'r2');
                }
            }

            // Tạo seller mới
            $seller = Seller::create([
                'user_id' => $userId,
                'store_name' => $request->store_name,
                'store_slug' => $storeSlug,
                'seller_type' => $request->seller_type,
                'identity_card_number' => $request->seller_type === 'personal' ? $request->identity_card_number : null,
                'date_of_birth' => $request->seller_type === 'personal' ? $request->date_of_birth : null,
                'personal_address' => $request->seller_type === 'personal' ? $request->personal_address : null,
                'phone_number' => $request->seller_type === 'personal' ? $request->phone_number : null,
                'document' => $documentPath,
                'cccd_front' => $cccdFront,
                'cccd_back' => $cccdBack,
                'bio' => $request->bio,
                'verification_status' => 'pending'
            ]);

            // Nếu là doanh nghiệp thì tạo thêm bảng phụ
            if ($request->seller_type === 'business') {
                $licensePath = null;
                if ($request->hasFile('business_license') && $request->file('business_license')->isValid()) {
                    $file = $request->file('business_license');
                    $filename = 'seller-documents/business/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    Storage::disk('r2')->put($filename, file_get_contents($file));
                    $licensePath = $filename;
                }

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
                'message' => 'Đăng ký thành công! Vui lòng chờ xác minh.',
                'data' => $seller->load('business')
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Đã xảy ra lỗi server.',
                'error' => $e->getMessage()
            ], 500);
        }
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

            if ($user->role !== 'seller') {
             return response()->json(['message' => 'Chỉ seller mới được đăng nhập hệ thống này.'], 403);
                   }

            // B3:  Kiểm tra vai trò và trạng thái của người dùng
                if ($user->role === 'seller') {
                    $seller = \App\Models\Seller::where('user_id', $user->id)->first();

                    if (!$seller) {
                        return response()->json([
                            'message' => 'Bạn chưa đăng ký cửa hàng. Vui lòng hoàn tất hồ sơ để được xét duyệt.',
                        ], 403);
                    }

                    // Nếu seller đã có mà status hoặc verification_status chưa đúng thì báo lỗi
                        if (
                        $seller->verification_status !== 'verified'
                    ) {
                        return response()->json([
                            'message' => 'Tài khoản của bạn đang chờ admin xác nhận cửa hàng.',
                        ], 403);
                    }
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
                'store_slug' => $user->store_slug,
                'token' => $token,
                'logged_in_at' => now()->toDateTimeString(),
            ];
            Redis::setex($redisKey, 7200, json_encode($redisData)); // TTL 2 giờ

            // B6: Ghi session tạm
            session([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
            ]);

            // B7: Trả response thành công
            return response()->json([
                'message' => 'Đăng nhập thành công!',
                'token' => $token,
                'user' => $user,
                'store_slug' => $seller ? $seller->store_slug : null,

            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }



 }
