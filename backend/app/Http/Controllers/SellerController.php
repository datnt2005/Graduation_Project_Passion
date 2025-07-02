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

    public function index()
    {
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
            'store_name',
            'store_slug',
            'seller_type',
            'bio',
            'identity_card_number',
            'date_of_birth',
            'personal_address',
            'phone_number'
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


    public function getVerifiedSellers()
    {
        $sellers = Seller::where('verification_status', 'verified')->get();
        return response()->json([
            'message' => 'Lấy danh sách người bán.',
            'data' => $sellers
        ], 200);
    }


    public function registerFull(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Chưa xác thực người dùng.'], 401);
        }

       $validator = Validator::make($request->all(), [
    // Bước 1
    'store_name' => 'required|string|max:255|unique:sellers,store_name',
    'phone_number' => 'required|regex:/^[0-9\-\+\s\(\)]+$/|min:10|max:11|unique:sellers,phone_number',
    'pickup_address' => 'required|string',

    // Bước 2
    'shipping_options' => 'required|array',

    // Bước 3
    'seller_type' => 'required|in:personal,business',
    'tax_code' => 'required|string|max:20|unique:sellers,tax_code',
    'business_name' => 'nullable|string|max:255',
    'business_email' => 'nullable|email',

    // Bước 4
    'identity_card_number' => 'required|string|max:20|unique:sellers,identity_card_number',
    'date_of_birth' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
    'personal_address' => 'required|string',
    'id_card_front_url' => 'required|file|mimes:jpg,jpeg,png|max:4096',
    'id_card_back_url' => 'required|file|mimes:jpg,jpeg,png|max:4096',
], [
    // Thông báo lỗi tùy chỉnh
    'store_name.required' => 'Vui lòng nhập tên cửa hàng.',
    'store_name.unique' => 'Tên cửa hàng đã được sử dụng.',
    'store_name.max' => 'Tên cửa hàng không được vượt quá 255 ký tự.',

    'phone_number.required' => 'Vui lòng nhập số điện thoại.',
    'phone_number.regex' => 'Số điện thoại không hợp lệ.',
    'phone_number.min' => 'Số điện thoại phải có ít nhất 10 số.',
    'phone_number.max' => 'Số điện thoại không được vượt quá 11 số.',
    'phone_number.unique' => 'Số điện thoại đã tồn tại.',

    'pickup_address.required' => 'Vui lòng nhập địa chỉ lấy hàng.',

    'shipping_options.required' => 'Vui lòng chọn ít nhất một phương thức vận chuyển.',
    'shipping_options.array' => 'Dữ liệu vận chuyển không hợp lệ.',

    'seller_type.required' => 'Vui lòng chọn loại hình người bán.',
    'seller_type.in' => 'Loại hình người bán không hợp lệ.',

    'tax_code.required' => 'Vui lòng nhập mã số thuế.',
    'tax_code.unique' => 'Mã số thuế đã tồn tại.',
    'tax_code.max' => 'Mã số thuế không được vượt quá 20 ký tự.',

    'business_name.max' => 'Tên doanh nghiệp không được vượt quá 255 ký tự.',
    'business_email.email' => 'Email doanh nghiệp không hợp lệ.',

    'identity_card_number.required' => 'Vui lòng nhập số CCCD.',
    'identity_card_number.max' => 'Số CCCD không được vượt quá 20 ký tự.',
    'identity_card_number.unique' => 'Số CCCD đã được đăng ký.',

    'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
    'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
    'date_of_birth.before_or_equal' => 'Bạn phải đủ 18 tuổi trở lên.',

    'personal_address.required' => 'Vui lòng nhập địa chỉ cá nhân.',

    'id_card_front_url.required' => 'Vui lòng tải lên ảnh mặt trước CCCD.',
    'id_card_front_url.file' => 'Ảnh mặt trước CCCD không hợp lệ.',
    'id_card_front_url.mimes' => 'Ảnh mặt trước phải là file JPG, JPEG hoặc PNG.',
    'id_card_front_url.max' => 'Ảnh mặt trước CCCD không được vượt quá 4MB.',

    'id_card_back_url.required' => 'Vui lòng tải lên ảnh mặt sau CCCD.',
    'id_card_back_url.file' => 'Ảnh mặt sau CCCD không hợp lệ.',
    'id_card_back_url.mimes' => 'Ảnh mặt sau phải là file JPG, JPEG hoặc PNG.',
    'id_card_back_url.max' => 'Ảnh mặt sau CCCD không được vượt quá 4MB.',
]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tạo slug
        $slug = Str::slug($request->store_name) . '-' . uniqid();

        // Tạo hoặc cập nhật seller
        $seller = Seller::updateOrCreate(
            ['user_id' => $user->id],
            [
                'store_name' => $request->store_name,
                'store_slug' => $slug,
                'phone_number' => $request->phone_number,
                'pickup_address' => $request->pickup_address,
                'shipping_options' => $request->shipping_options,
                'seller_type' => $request->seller_type,
                'tax_code' => $request->tax_code,
                'business_name' => $request->business_name,
                'business_email' => $request->business_email,
                'identity_card_number' => $request->identity_card_number,
                'date_of_birth' => $request->date_of_birth,
                'personal_address' => $request->personal_address,
                'verification_status' => 'pending',
            ]
        );

        // Upload ảnh CCCD
        if ($request->hasFile('id_card_front_url')) {
            $frontPath = $request->file('id_card_front_url')->store('seller-documents/cccd-front', 'r2');
            $seller->id_card_front_url = $frontPath;
        }

        if ($request->hasFile('id_card_back_url')) {
            $backPath = $request->file('id_card_back_url')->store('seller-documents/cccd-back', 'r2');
            $seller->id_card_back_url = $backPath;
        }

        $seller->save();

        return response()->json([
            'message' => 'Đã hoàn tất đăng ký người bán.'
        ]);
    }
}


