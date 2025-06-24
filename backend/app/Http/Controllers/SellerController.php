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
use Google\Cloud\Vision\V1\ImageAnnotatorClient;


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

    $validator = Validator::make($request->all(), [
        'store_name' => 'required_if:seller_type,personal|string|max:255',
        'store_slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
        'seller_type' => 'required|in:personal,business',
        'identity_card_number' => 'required_if:seller_type,personal|string|max:20',
        'date_of_birth' => 'required_if:seller_type,personal|date',
        'personal_address' => 'required_if:seller_type,personal|string',
        'phone_number' => 'required_if:seller_type,personal|string|max:20',
        'document' => 'nullable|file|mimes:jpg,png,pdf|max:4048',
        'bio' => 'nullable|string',
        'cccd_front' => 'required_if:seller_type,personal|file|mimes:jpg,jpeg,png|max:4096',
        'cccd_back' => 'required_if:seller_type,personal|file|mimes:jpg,jpeg,png|max:4096',

        // Business fields
        'business' => 'required_if:seller_type,business|array',
        'business.tax_code' => 'required_if:seller_type,business|string',
        'business.company_name' => 'required_if:seller_type,business|string',
        'business.company_address' => 'required_if:seller_type,business|string',
        'business.representative_name' => 'required_if:seller_type,business|string',
        'business.representative_phone' => 'required_if:seller_type,business|string|max:20',
        'business.business_license' => 'nullable|file|mimes:jpg,png,pdf|max:4048',
    ], [
        'store_name.required_if' => 'Vui lòng nhập tên cửa hàng (áp dụng cho người bán cá nhân).',
        'store_name.string' => 'Tên cửa hàng phải là chuỗi.',
        'store_name.max' => 'Tên cửa hàng không được vượt quá 255 ký tự.',

        'store_slug.regex' => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang.',

        'seller_type.required' => 'Loại người bán là bắt buộc.',
        'seller_type.in' => 'Loại người bán phải là "personal" hoặc "business".',

        'identity_card_number.required_if' => 'Vui lòng nhập số CMND/CCCD.',
        'identity_card_number.string' => 'Số CMND/CCCD phải là chuỗi.',
        'identity_card_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự.',

        'date_of_birth.required_if' => 'Vui lòng nhập ngày sinh.',
        'date_of_birth.date' => 'Ngày sinh không hợp lệ.',

        'personal_address.required_if' => 'Vui lòng nhập địa chỉ cá nhân.',
        'personal_address.string' => 'Địa chỉ phải là chuỗi.',

        'phone_number.required_if' => 'Vui lòng nhập số điện thoại cá nhân.',
        'phone_number.string' => 'Số điện thoại phải là chuỗi.',
        'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

        'document.file' => 'Tài liệu phải là tệp hợp lệ.',
        'document.mimes' => 'Tài liệu phải có định dạng: jpg, png, hoặc pdf.',
        'document.max' => 'Tài liệu không được vượt quá 4MB.',

        'bio.string' => 'Giới thiệu phải là chuỗi văn bản.',

        'cccd_front.required_if' => 'Vui lòng tải lên ảnh mặt trước CCCD.',
        'cccd_front.file' => 'Ảnh mặt trước CCCD phải là tệp hợp lệ.',
        'cccd_front.mimes' => 'Ảnh mặt trước CCCD phải có định dạng: jpg, jpeg hoặc png.',
        'cccd_front.max' => 'Ảnh mặt trước CCCD không được vượt quá 4MB.',

        'cccd_back.required_if' => 'Vui lòng tải lên ảnh mặt sau CCCD.',
        'cccd_back.file' => 'Ảnh mặt sau CCCD phải là tệp hợp lệ.',
        'cccd_back.mimes' => 'Ảnh mặt sau CCCD phải có định dạng: jpg, jpeg hoặc png.',
        'cccd_back.max' => 'Ảnh mặt sau CCCD không được vượt quá 4MB.',

        'business.tax_code.required_if' => 'Vui lòng nhập mã số thuế.',
        'business.company_name.required_if' => 'Vui lòng nhập tên công ty.',
        'business.company_address.required_if' => 'Vui lòng nhập địa chỉ công ty.',
        'business.representative_name.required_if' => 'Vui lòng nhập tên người đại diện.',
        'business.representative_phone.required_if' => 'Vui lòng nhập số điện thoại người đại diện.',
        'business.representative_phone.max' => 'Số điện thoại người đại diện không được vượt quá 20 ký tự.',
        'business.business_license.file' => 'Giấy phép kinh doanh phải là tệp hợp lệ.',
        'business.business_license.mimes' => 'Giấy phép kinh doanh phải có định dạng: jpg, png, hoặc pdf.',
        'business.business_license.max' => 'Giấy phép kinh doanh không được vượt quá 4MB.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $validator->errors()
        ], 422);
    }

    $data = $request->only([
        'store_name', 'store_slug', 'seller_type', 'bio',
        'identity_card_number', 'date_of_birth',
        'personal_address', 'phone_number'
    ]);

    // Upload document
    if ($request->hasFile('document') && $request->file('document')->isValid()) {
        if ($seller->document) {
            Storage::disk('r2')->delete($seller->document);
        }

        $file = $request->file('document');
        $filename = 'seller-documents/personal/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        if (Storage::disk('r2')->put($filename, file_get_contents($file))) {
            $data['document'] = $filename;
        } else {
            throw new \Exception('Không thể upload file giấy tờ cá nhân lên R2.');
        }
    }

    // CCCD front
    if ($request->hasFile('cccd_front') && $request->file('cccd_front')->isValid()) {
        if ($seller->cccd_front) {
            Storage::disk('r2')->delete($seller->cccd_front);
        }

        $cccdFrontPath = $request->file('cccd_front')->store('seller-documents', 'r2');
        $data['cccd_front'] = $cccdFrontPath;
    }

    // CCCD back
    if ($request->hasFile('cccd_back') && $request->file('cccd_back')->isValid()) {
        if ($seller->cccd_back) {
            Storage::disk('r2')->delete($seller->cccd_back);
        }

        $cccdBackPath = $request->file('cccd_back')->store('seller-documents', 'r2');
        $data['cccd_back'] = $cccdBackPath;
    }

    $seller->update($data);

    // Business info
    if ($request->seller_type === 'business' && $request->has('business')) {
        $businessData = $request->input('business');

        if ($request->hasFile('business.business_license') && $request->file('business.business_license')->isValid()) {
            if ($seller->business?->business_license) {
                Storage::disk('r2')->delete($seller->business->business_license);
            }

            $file = $request->file('business.business_license');
            $filename = 'seller-documents/business/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            if (Storage::disk('r2')->put($filename, file_get_contents($file))) {
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
                return response()->json(['message' => 'Bạn cần đăng nhập để đăng ký cửa hàng.'], 401);
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
                'cccd_front' => 'required_if:seller_type,personal|file|mimes:jpg,jpeg,png|max:4096',
                'cccd_back' => 'required_if:seller_type,personal|file|mimes:jpg,jpeg,png|max:4096',
            ], [
                'store_name.required_if' => 'Vui lòng nhập tên cửa hàng (áp dụng cho người bán cá nhân).',
                'store_name.string' => 'Tên cửa hàng phải là chuỗi.',
                'store_name.max' => 'Tên cửa hàng không được vượt quá 255 ký tự.',
                'seller_type.required' => 'Loại người bán là bắt buộc.',
                'seller_type.in' => 'Loại người bán phải là "personal" hoặc "business".',
                'identity_card_number.required_if' => 'Vui lòng nhập số CMND/CCCD.',
                'identity_card_number.string' => 'Số CMND/CCCD phải là chuỗi.',
                'identity_card_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự.',
                'date_of_birth.required_if' => 'Vui lòng nhập ngày sinh.',
                'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
                'personal_address.required_if' => 'Vui lòng nhập địa chỉ cá nhân.',
                'personal_address.string' => 'Địa chỉ phải là chuỗi.',
                'phone_number.required_if' => 'Vui lòng nhập số điện thoại cá nhân.',
                'phone_number.string' => 'Số điện thoại phải là chuỗi.',
                'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
                'document.file' => 'Tài liệu phải là tệp hợp lệ.',
                'document.mimes' => 'Tài liệu phải có định dạng: jpg, png, hoặc pdf.',
                'document.max' => 'Tài liệu không được vượt quá 4MB.',
                'bio.string' => 'Giới thiệu phải là chuỗi văn bản.',
                'tax_code.required_if' => 'Vui lòng nhập mã số thuế.',
                'tax_code.string' => 'Mã số thuế phải là chuỗi.',
                'company_name.required_if' => 'Vui lòng nhập tên công ty.',
                'company_name.string' => 'Tên công ty phải là chuỗi.',
                'company_address.required_if' => 'Vui lòng nhập địa chỉ công ty.',
                'company_address.string' => 'Địa chỉ công ty phải là chuỗi.',
                'business_license.required_if' => 'Vui lòng tải lên giấy phép kinh doanh.',
                'business_license.file' => 'Giấy phép kinh doanh phải là tệp hợp lệ.',
                'business_license.mimes' => 'Giấy phép kinh doanh phải có định dạng: jpg, png, hoặc pdf.',
                'business_license.max' => 'Giấy phép kinh doanh không được vượt quá 4MB.',
                'representative_name.required_if' => 'Vui lòng nhập tên người đại diện.',
                'representative_name.string' => 'Tên người đại diện phải là chuỗi.',
                'representative_phone.required_if' => 'Vui lòng nhập số điện thoại người đại diện.',
                'representative_phone.string' => 'Số điện thoại người đại diện phải là chuỗi.',
                'representative_phone.max' => 'Số điện thoại người đại diện không được vượt quá 20 ký tự.',
                'cccd_front.required_if' => 'Vui lòng tải lên ảnh mặt trước CCCD.',
                'cccd_front.file' => 'Ảnh mặt trước CCCD phải là tệp hợp lệ.',
                'cccd_front.mimes' => 'Ảnh mặt trước CCCD phải có định dạng: jpg, jpeg hoặc png.',
                'cccd_front.max' => 'Ảnh mặt trước CCCD không được vượt quá 4MB.',
                'cccd_back.required_if' => 'Vui lòng tải lên ảnh mặt sau CCCD.',
                'cccd_back.file' => 'Ảnh mặt sau CCCD phải là tệp hợp lệ.',
                'cccd_back.mimes' => 'Ảnh mặt sau CCCD phải có định dạng: jpg, jpeg hoặc png.',
                'cccd_back.max' => 'Ảnh mặt sau CCCD không được vượt quá 4MB.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra đã có seller chưa
            $seller = Seller::where('user_id', $userId)->first();

            // TRƯỜNG HỢP NÂNG CẤP PERSONAL → BUSINESS
            if ($seller && $seller->seller_type === 'personal' && $request->seller_type === 'business' && !$seller->business) {
                $licensePath = null;
                if ($request->hasFile('business_license') && $request->file('business_license')->isValid()) {
                    $file = $request->file('business_license');
                    $filename = 'seller-documents/business/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    Storage::disk('r2')->put($filename, file_get_contents($file));
                    \Log::info('Uploaded business_license to R2: ' . $filename);
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
                    'message' => 'Tài khoản này đã đăng ký cửa hàng không được đăng ký lại hoặc không thể nâng cấp.'
                ], 409);
            }

            // ĐĂNG KÝ MỚI
            $storeSlug = Str::slug($request->store_name) . '-' . uniqid();
            $documentPath = null;
            $cccdFront = null;
            $cccdBack = null;

            // Kiểm tra số CCCD đã được sử dụng
            if ($request->seller_type === 'personal' && $request->identity_card_number) {
                if (Seller::where('identity_card_number', $request->identity_card_number)->exists()) {
                    return response()->json(['message' => 'Số CCCD này đã được sử dụng để đăng ký tài khoản khác.'], 409);
                }
            }

            // Upload CCCD mặt trước
            if ($request->hasFile('cccd_front') && $request->file('cccd_front')->isValid()) {
                $cccdFrontFile = $request->file('cccd_front');
                $filename = 'seller-documents/cccd/' . time() . '_front_' . Str::slug(pathinfo($cccdFrontFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $cccdFrontFile->getClientOriginalExtension();
                Storage::disk('r2')->put($filename, file_get_contents($cccdFrontFile));
                \Log::info('Uploaded cccd_front to R2: ' . $filename);
                $cccdFront = $filename;
            }

            // Upload CCCD mặt sau
            if ($request->hasFile('cccd_back') && $request->file('cccd_back')->isValid()) {
                $cccdBackFile = $request->file('cccd_back');
                $filename = 'seller-documents/cccd/' . time() . '_back_' . Str::slug(pathinfo($cccdBackFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $cccdBackFile->getClientOriginalExtension();
                Storage::disk('r2')->put($filename, file_get_contents($cccdBackFile));
                \Log::info('Uploaded cccd_back to R2: ' . $filename);
                $cccdBack = $filename;
            }

            // Upload tài liệu bổ sung (nếu có)
            if ($request->hasFile('document') && $request->file('document')->isValid()) {
                $file = $request->file('document');
                $filename = 'seller-documents/personal/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                Storage::disk('r2')->put($filename, file_get_contents($file));
                \Log::info('Uploaded document to R2: ' . $filename);
                $documentPath = $filename;
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
                    \Log::info('Uploaded business_license to R2: ' . $filename);
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




 }
