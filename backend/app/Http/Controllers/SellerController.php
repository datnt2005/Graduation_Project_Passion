<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerController extends Controller
{

    public function index()
    {
        $sellers = User::whereHas('seller')
            ->get();
        return response()->json($sellers);
    }



    public function getMySellerInfo()
    {
        $user = auth()->user();

        // Kiểm tra user có phải seller không
        $seller = Seller::with(['user:id,name,email,avatar'])
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
        'store_name' => 'nullable|string|max:255',
        'bio' => 'nullable|string',
        'phone_number' => 'nullable|string|max:20',
        'pickup_address' => 'nullable|string',
        'document' => 'nullable|file|mimes:jpg,png,pdf|max:4048',
    ], [
        'store_name.max' => 'Tên cửa hàng không được vượt quá 255 ký tự.',
        'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
        'document.file' => 'Tài liệu phải là tệp hợp lệ.',
        'document.mimes' => 'Tài liệu phải có định dạng: jpg, png, hoặc pdf.',
        'document.max' => 'Tài liệu không được vượt quá 4MB.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'seller' => $seller
        ]);
    }

    $data = $request->only([
        'store_name',
        'bio',
        'phone_number',
        'pickup_address',
    ]);

    // Nếu có file document mới thì xử lý upload
    if ($request->hasFile('document') && $request->file('document')->isValid()) {
        if ($seller->document) {
            Storage::disk('r2')->delete($seller->document);
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

        if (Storage::disk('r2')->put($filename, file_get_contents($file))) {
            $data['document'] = $filename;
        } else {
            throw new \Exception('Không thể upload file giấy tờ lên R2.');
        }
    }
            $cccdBackPath = $request->file('cccd_back')->store('seller-documents', 'r2');
            $data['cccd_back'] = $cccdBackPath;
        }

    $seller = Seller::with('user:id,name,email')->findOrFail($seller->id);

    return response()->json([
        'message' => 'Cập nhật thông tin thành công.',
        'seller' => $seller
    ]);
}

    public function showStore($slug)
    {
        $seller = Seller::with([
            'user',
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
            'identity_card_file' => 'required_if:seller_type,business|file|mimes:jpg,jpeg,png,pdf|max:4096',

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

            'identity_card_file.required_if' => 'Vui lòng tải lên giấy tờ xác minh cho doanh nghiệp.',
            'identity_card_file.file' => 'File xác minh không hợp lệ.',
            'identity_card_file.mimes' => 'File xác minh phải là JPG, JPEG, PNG hoặc PDF.',
            'identity_card_file.max' => 'File xác minh không được vượt quá 4MB.',

            'id_card_back_url.required' => 'Vui lòng tải lên ảnh mặt sau CCCD.',
            'id_card_back_url.file' => 'Ảnh mặt sau CCCD không hợp lệ.',
            'id_card_back_url.mimes' => 'Ảnh mặt sau phải là file JPG, JPEG hoặc PNG.',
            'id_card_back_url.max' => 'Ảnh mặt sau CCCD không được vượt quá 4MB.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tạo slug
        $slug = Str::slug($request->store_name) . '-' . Str::random(4) . '-' . uniqid();

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

        if ($request->hasFile('identity_card_file')) {
            $identityFilePath = $request->file('identity_card_file')->store('seller-documents/business-proof', 'r2');
            $seller->identity_card_file = $identityFilePath;
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
