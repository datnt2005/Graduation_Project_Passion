<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Discount;
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
        try {
            // Validate slug
            if (empty($slug) || !is_string($slug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug không hợp lệ.',
                ], 400);
            }

            // Get query parameters
            $page = request()->query('page', 1);
            $perPage = max(1, min(100, (int) request()->query('per_page', 24)));
            $search = request()->query('search');
            $category = request()->query('category');

            // Fetch seller with relationships and paginate products
            $seller = Seller::with([
                'user',
                'business',
                'products' => function ($query) use ($page, $perPage, $search, $category) {
                    $query->where('status', 'active')
                        ->with(['productVariants', 'productPic', 'categories', 'tags', 'reviews']);
                    if ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    }
                    if ($category) {
                        $query->whereHas('categories', function ($q) use ($category) {
                            $q->where('name', $category);
                        });
                    }
                    $query->orderByDesc('id');
                },
                'followers'
            ])->where('store_slug', $slug)->firstOrFail();

            // Get paginated products
            $productsQuery = $seller->products();
            $productsQuery->where('status', 'active');
            if ($search) {
                $productsQuery->where('name', 'like', '%' . $search . '%');
            }
            if ($category) {
                $productsQuery->whereHas('categories', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            }
            $productsPaginated = $productsQuery->with(['productVariants', 'productPic', 'categories', 'tags', 'reviews'])
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page);

            // Calculate seller rating
            $sellerRating = round(
                DB::table('reviews')
                    ->join('products', 'reviews.product_id', '=', 'products.id')
                    ->where('products.seller_id', $seller->id)
                    ->avg('reviews.rating') ?: 0,
                2
            );

            // Total active products
            $productsCount = $seller->products()->where('status', 'active')->count();

            // Total sold items
            $totalSold = $seller->products->flatMap(function ($product) {
                return $product->productVariants->flatMap(function ($variant) {
                    return $variant->orderItems ?? collect();
                });
            })->sum('quantity') ?? 0;

            // Mask phone number
            $phone = $seller->phone_number
                ? substr($seller->phone_number, 0, 4) . str_repeat('*', max(0, strlen($seller->phone_number) - 7)) . substr($seller->phone_number, -5)
                : 'N/A';

            // Format last active time (Vietnamese localization)
            $lastActive = $seller->last_active_at
                ? $seller->last_active_at->locale('vi')->diffForHumans()
                : 'Chưa hoạt động';

            // Check if user is authenticated and get user
            $user = auth('sanctum')->user();
            $isOwner = $user && $user->id === $seller->user_id; // Kiểm tra xem user có phải là chủ sở hữu không
            $isFollowing = false;

            // Check if user is following (only if not the owner)
            if ($user && !$isOwner) {
                $isFollowing = $seller->followers()->where('user_id', $user->id)->exists();
            }

            // Calculate cancellation rate
            $totalOrders = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('sellers', 'users.id', '=', 'sellers.user_id')
                ->where('sellers.id', $seller->id)
                ->count();
            $canceledOrders = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('sellers', 'users.id', '=', 'sellers.user_id')
                ->where('sellers.id', $seller->id)
                ->where('orders.status', 'canceled')
                ->count();
            $cancellationRate = $totalOrders > 0 ? round(($canceledOrders / $totalOrders) * 100, 2) . '%' : '0%';

            // Calculate return rate
            $deliveredOrders = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('sellers', 'users.id', '=', 'sellers.user_id')
                ->where('sellers.id', $seller->id)
                ->where('orders.status', 'delivered')
                ->count();
            $returnedOrders = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('sellers', 'users.id', '=', 'sellers.user_id')
                ->where('sellers.id', $seller->id)
                ->where('orders.status', 'returned')
                ->count();
            $returnRate = $deliveredOrders > 0 ? round(($returnedOrders / $deliveredOrders) * 100, 2) . '%' : '0%';

            // Format seller data
            $formattedSeller = [
                'id' => $seller->id,
                'store_name' => $seller->store_name ?? 'N/A',
                'store_slug' => $seller->store_slug,
                'bio' => $seller->bio ?? 'N/A',
                'avatar' => $seller->user->avatar ?? 'avatars/default.jpg',
                'phone' => $phone,
                'rating' => $sellerRating,
                'stars' => (int) round($sellerRating),
                'products_count' => $productsCount,
                'total_sold' => (string) $totalSold,
                'last_active' => $lastActive,
                'created_at' => $seller->created_at ? $seller->created_at->locale('vi')->isoFormat('LL') : 'N/A',
                'description' => $seller->bio ?? 'Chưa có mô tả.',
                'address' => $seller->personal_address ?? 'Chưa cung cấp địa chỉ.',
                'member_since' => $seller->created_at ? $seller->created_at->format('Y') : 'N/A',
                'cancellation_rate' => $cancellationRate,
                'return_rate' => $returnRate,
                'business' => $seller->business ? [
                    'name' => $seller->business->name ?? 'N/A',
                    'address' => $seller->business->company_address ?? 'N/A',
                    'description' => $seller->business->description ?? 'N/A',
                    'tax_code' => $seller->business->tax_code ?? 'N/A',
                ] : null,
                'followers_count' => $seller->followers()->count(),
                'is_following' => $isFollowing,
                'is_owner' => $isOwner, // Thêm cờ để frontend nhận diện
                'total_products' => $productsCount,
            ];

            // Format products to match ProductCard props
            $products = collect($productsPaginated->items())->map(function ($product) {
                $defaultVariant = $product->productVariants->first();
                $defaultPrice = $defaultVariant?->price ?? 0.0;
                $defaultSalePrice = $defaultVariant?->sale_price ?? null;
                $defaultPercent = ($defaultSalePrice && $defaultPrice > 0)
                    ? round((($defaultPrice - $defaultSalePrice) / $defaultPrice) * 100)
                    : 0;
                $rating = round($product->reviews->avg('rating') ?: 0, 2);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $defaultPrice,
                    'discount' => $defaultSalePrice,
                    'percent' => $defaultPercent,
                    'rating' => str_repeat('★', (int) round($rating)) . str_repeat('☆', 5 - (int) round($rating)),
                    'sold' => (int) ($product->productVariants->flatMap(function ($variant) {
                        return $variant->orderItems ?? collect();
                    })->sum('quantity') ?? 0),
                    'image' => $product->productPic->first()->imagePath ?? 'images/default-product.jpg',
                    'categories' => $product->categories->pluck('name')->toArray(),
                    'tags' => $product->tags->pluck('name')->toArray(),
                ];
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin cửa hàng thành công.',
                'data' => [
                    'seller' => $formattedSeller,
                    'products' => $products,
                    'pagination' => [
                        'current_page' => $productsPaginated->currentPage(),
                        'last_page' => $productsPaginated->lastPage(),
                        'per_page' => $productsPaginated->perPage(),
                        'total' => $productsPaginated->total(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông tin cửa hàng. Vui lòng thử lại sau.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
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
  public function getDeals($slug)
    {
        try {
            // Validate slug
            if (empty($slug) || !is_string($slug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug không hợp lệ.',
                ], 400);
            }

            // Validate query parameters
            $perPage = max(1, min(100, (int) request()->query('per_page', 24)));

            // Fetch seller
            $seller = Seller::where('store_slug', $slug)->firstOrFail();

            // Fetch deals (products with active discounts)
            $dealsQuery = $seller->products()->where('status', 'active')
                ->whereHas('productVariants', function ($query) {
                    $query->whereNotNull('sale_price')
                        ->whereColumn('sale_price', '<', 'price');
                })
                ->with([
                    'productVariants' => function ($q) {
                        $q->select('id', 'product_id', 'price', 'sale_price')
                            ->whereNotNull('sale_price')
                            ->whereColumn('sale_price', '<', 'price');
                    },
                    'productPic' => function ($q) {
                        $q->select('id', 'product_id', 'imagePath');
                    },
                    'categories' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'tags' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'reviews' => function ($q) {
                        $q->select('id', 'product_id', 'rating');
                    }
                ])
                ->orderByDesc('id');

            $dealsPaginated = $dealsQuery->paginate($perPage);

            // Format deals to match ProductCard props
            $deals = collect($dealsPaginated->items())->map(function ($product) {
                $defaultVariant = $product->productVariants->first();
                $defaultPrice = $defaultVariant?->price ?? 0.0;
                $defaultSalePrice = $defaultVariant?->sale_price ?? null;
                $defaultPercent = ($defaultSalePrice && $defaultPrice > 0)
                    ? round((($defaultPrice - $defaultSalePrice) / $defaultPrice) * 100)
                    : 0;
                $rating = round($product->reviews->avg('rating') ?: 0, 2);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $defaultPrice,
                    'discount' => $defaultSalePrice,
                    'percent' => $defaultPercent,
                    'rating' => str_repeat('★', (int) round($rating)) . str_repeat('☆', 5 - (int) round($rating)),
                    'sold' => (int) ($product->productVariants->flatMap(function ($variant) {
                        return $variant->orderItems ?? collect();
                    })->sum('quantity') ?? 0),
                    'image' => $product->productPic->first() ? $product->productPic->first()->imagePath : 'images/default-product.jpg',
                    'categories' => $product->categories->pluck('name')->toArray(),
                    'tags' => $product->tags->pluck('name')->toArray(),
                ];
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách ưu đãi thành công.',
                'data' => [
                    'deals' => $deals,
                    'pagination' => [
                        'current_page' => $dealsPaginated->currentPage(),
                        'last_page' => $dealsPaginated->lastPage(),
                        'per_page' => $dealsPaginated->perPage(),
                        'total' => $dealsPaginated->total(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách ưu đãi. Vui lòng thử lại sau.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function getDiscounts($slug)
    {
        try {
            // Validate slug
            if (empty($slug) || !is_string($slug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug không hợp lệ.',
                ], 400);
            }

            // Get per_page query parameter
            $perPage = max(1, min(100, (int) request()->query('per_page', 24)));

            // Tìm seller theo slug
            $seller = Seller::where('store_slug', $slug)->firstOrFail();

            // Lấy người dùng đang đăng nhập
            $user = auth('sanctum')->user();

            // Lấy danh sách voucher theo seller
            $vouchersPaginated = $seller->discounts()
                ->where('status', 'active')
                ->where(function ($query) {
                    $now = now();
                    $query->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })
                ->where(function ($query) {
                    $now = now();
                    $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
                })
                ->orderByDesc('id')
                ->paginate($perPage);

            // Format dữ liệu voucher
            $vouchers = collect($vouchersPaginated->items())->map(function ($voucher) use ($user) {
                return [
                    'id' => $voucher->id,
                    'name' => $voucher->name,
                    'code' => $voucher->code,
                    'description' => $voucher->description,
                    'discount_type' => $voucher->discount_type, // 'percent' hoặc 'fixed'
                    'discount_value' => $voucher->discount_value,
                    'max_discount' => $voucher->max_discount ?? null,
                    'min_order_value' => $voucher->min_order_value,
                    'start_date' => $voucher->start_date?->format('Y-m-d H:i:s'),
                    'end_date' => $voucher->end_date?->format('Y-m-d H:i:s'),
                    'usage_limit' => $voucher->usage_limit,
                    'used_count' => $voucher->used_count,
                    'status' => $voucher->status,
                    'is_saved' => $user ? $voucher->users()->where('user_id', $user->id)->exists() : false,

                    'applies_to_products' => $voucher->products->pluck('name'), // tên sản phẩm áp dụng
                    'applies_to_categories' => $voucher->categories->pluck('name'), // tên danh mục
                    'applies_to_users' => $voucher->users->pluck('name'), // người dùng được áp dụng (nếu có)
                ];
            })->values()->toArray();


            // Trả về response
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách voucher thành công.',
                'data' => $vouchers,
                'pagination' => [
                    'current_page' => $vouchersPaginated->currentPage(),
                    'last_page' => $vouchersPaginated->lastPage(),
                    'per_page' => $vouchersPaginated->perPage(),
                    'total' => $vouchersPaginated->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách voucher. Vui lòng thử lại sau.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
  public function getVerifiedSellers()
    {
        $sellers = Seller::where('verification_status', 'verified')->get();
        return response()->json([
            'message' => 'Lấy danh sách người bán.',
            'data' => $sellers
        ], 200);
    }
}
