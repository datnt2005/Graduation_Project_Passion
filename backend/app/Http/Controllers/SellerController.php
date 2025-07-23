<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\User;
use App\Models\Discount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

    public function show($sellerId)
    {
        try {
            $seller = Seller::findOrFail($sellerId);
            return response()->json([
                'data' => [
                    'id' => $seller->id,
                    'district_id' => $seller->district_id,
                    'ward_id' => $seller->ward_id, // Sử dụng ward_id
                    'province_id' => $seller->province_id,
                    'address' => $seller->address,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy thông tin seller {$sellerId}: {$e->getMessage()}");
            return response()->json(['message' => 'Không thể lấy thông tin cửa hàng'], 500);
        }
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
            ], 403);
        }

        $avatarUrl = $seller->user->avatar
            ? env('R2_AVATAR_URL') . $seller->user->avatar
            : env('R2_AVATAR_URL') . 'default.jpg';

        return response()->json([
            'seller' => [
                'id' => $seller->id,
                'store_name' => $seller->store_name,
                'province_id' => $seller->province_id,
                'district_id' => $seller->district_id,
                'ward_id' => $seller->ward_id,
                'address' => $seller->address,
                'ghn_shop_id' => $seller->ghn_shop_id,
                'user' => [
                    'id' => $seller->user->id,
                    'name' => $seller->user->name,
                    'email' => $seller->user->email,
                    'avatar_url' => $avatarUrl,
                ],
            ],
        ], 200);
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
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
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

            $file = $request->file('document');
            $filename = 'seller-documents/personal/' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            if (Storage::disk('r2')->put($filename, file_get_contents($file))) {
                $data['document'] = $filename;
            } else {
                throw new \Exception('Không thể upload file giấy tờ lên R2.');
            }
        }

        $seller->update($data);

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
            $productsQuery->where('status', 'active')
                ->where('admin_status', 'approved');
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
            $productsCount = $seller->products()->where('status', 'active')->where('admin_status', 'approved')->count();

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

            // Check if user is following
            $isFollowing = false;
            $user = auth('sanctum')->user();
            if ($user && $user->id !== $seller->user_id) {
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
                'user_id' => $seller->user_id,
                'is_owner' => $user && $user->id === $seller->user_id,
                'bio' => $seller->bio ?? 'N/A',
                'avatar' => $seller->user->avatar ?? 'avatars/default.jpg',
                'phone' => $phone,
                'rating' => $sellerRating,
                'stars' => (int) round($sellerRating),
                'products_count' => $productsCount,
                'total_sold' => (string) $totalSold,
                'last_active' => $lastActive,
                'created_at' => $seller->created_at ? $seller->created_at->locale('vi')->isoFormat('LL') : 'N/A',
                'description' => $seller->bio ? $seller->bio ?? 'Chưa có mô tả.' : 'Chưa có mô tả.',
                'address' => $seller->personal_address ? $seller->personal_address ?? 'Chưa cung cấp địa chỉ.' : 'Chưa cung cấp địa chỉ.',
                'member_since' => $seller->created_at ? $seller->created_at->format('Y') : 'N/A',
                'cancellation_rate' => $cancellationRate,
                'return_rate' => $returnRate,
                'business' => $seller->seller_type === 'business' ? [
                    'name' => $seller->business_name ?? 'N/A',
                    'address' => $seller->pickup_address  ?? 'N/A',
                    'description' => $seller->bio ?? 'N/A',
                    'tax_code' => $seller->tax_code ?? 'N/A',
                ] : null,
                'followers_count' => $seller->followers()->count(),
                'is_following' => $isFollowing,
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


    public function getVerifiedSellers()
    {
        $sellers = Seller::where('verification_status', 'verified')
            ->select('id', 'store_name', 'store_slug')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách người bán thành công.',
            'data' => $sellers
        ]);
    }


    public function registerFull(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            Log::warning('Unauthorized access attempt in registerFull');
            return response()->json(['message' => 'Chưa xác thực người dùng.'], 401);
        }

        // Chuẩn hóa dữ liệu đầu vào
        $data = $request->all();
        $data['province_id'] = (int) $request->input('province_id'); // Chuyển thành số nguyên
        $data['district_id'] = (int) $request->input('district_id'); // Chuyển thành số nguyên

        // Log dữ liệu đầu vào để debug
        Log::info('FormData received in registerFull:', ['data' => $data]);

        // Validation rules
        $rules = [
            'store_name' => 'required|string|max:255|unique:sellers,store_name,' . $user->id . ',user_id',
            'phone_number' => 'required|string|max:20|regex:/^[0-9]{10,11}$/|unique:sellers,phone_number,' . $user->id . ',user_id',
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_id' => 'required|string',
            'address' => 'required|string|max:255',
            'shipping_options' => 'required|json',
            'seller_type' => 'required|in:personal,business',
            'tax_code' => 'required|string|max:20|unique:sellers,tax_code,' . $user->id . ',user_id',
            'business_name' => 'nullable|string|max:255',
            'business_email' => 'nullable|email|max:255',
            'identity_card_file' => 'required_if:seller_type,business|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'identity_card_number' => 'required|string|regex:/^[0-9]{12}$/|unique:sellers,identity_card_number,' . $user->id . ',user_id',
            'date_of_birth' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'personal_address' => 'required|string|max:255',
            'id_card_front_url' => 'required|file|mimes:jpg,jpeg,png|max:4096',
            'id_card_back_url' => 'required|file|mimes:jpg,jpeg,png|max:4096',
        ];

        // Custom error messages
        $messages = [
            'store_name.required' => 'Vui lòng nhập tên cửa hàng.',
            'store_name.unique' => 'Tên cửa hàng đã được sử dụng.',
            'store_name.max' => 'Tên cửa hàng không được vượt quá 255 ký tự.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại phải có 10 hoặc 11 chữ số.',
            'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'phone_number.unique' => 'Số điện thoại đã tồn tại.',
            'province_id.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_id.integer' => 'Tỉnh/thành phố không hợp lệ.',
            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'Quận/huyện không hợp lệ.',
            'ward_id.required' => 'Vui lòng chọn phường/xã.',
            'ward_id.string' => 'Phường/xã không hợp lệ.',
            'address.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'shipping_options.required' => 'Vui lòng chọn ít nhất một phương thức vận chuyển.',
            'shipping_options.json' => 'Dữ liệu vận chuyển không hợp lệ, phải là chuỗi JSON.',
            'seller_type.required' => 'Vui lòng chọn loại hình người bán.',
            'seller_type.in' => 'Loại hình người bán phải là cá nhân hoặc doanh nghiệp.',
            'tax_code.required' => 'Vui lòng nhập mã số thuế.',
            'tax_code.unique' => 'Mã số thuế đã tồn tại.',
            'tax_code.max' => 'Mã số thuế không được vượt quá 20 ký tự.',
            'business_name.max' => 'Tên doanh nghiệp không được vượt quá 255 ký tự.',
            'business_email.email' => 'Email doanh nghiệp không hợp lệ.',
            'identity_card_number.required' => 'Vui lòng nhập số CCCD.',
            'identity_card_number.regex' => 'Số CCCD phải có đúng 12 chữ số.',
            'identity_card_number.unique' => 'Số CCCD đã được đăng ký.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before_or_equal' => 'Bạn phải đủ 18 tuổi trở lên.',
            'personal_address.required' => 'Vui lòng nhập địa chỉ cá nhân.',
            'personal_address.max' => 'Địa chỉ cá nhân không được vượt quá 255 ký tự.',
            'id_card_front_url.required' => 'Vui lòng tải lên ảnh mặt trước CCCD.',
            'id_card_front_url.mimes' => 'Ảnh mặt trước phải là file JPG, JPEG hoặc PNG.',
            'id_card_front_url.max' => 'Ảnh mặt trước CCCD không được vượt quá 4MB.',
            'id_card_back_url.required' => 'Vui lòng tải lên ảnh mặt sau CCCD.',
            'id_card_back_url.mimes' => 'Ảnh mặt sau phải là file JPG, JPEG hoặc PNG.',
            'id_card_back_url.max' => 'Ảnh mặt sau CCCD không được vượt quá 4MB.',
            'identity_card_file.required_if' => 'Vui lòng tải lên giấy tờ xác minh cho doanh nghiệp.',
            'identity_card_file.mimes' => 'File xác minh phải là JPG, JPEG, PNG hoặc PDF.',
            'identity_card_file.max' => 'File xác minh không được vượt quá 4MB.',
        ];

        // Validate request
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            Log::warning('Validation failed in registerFull: ', ['errors' => $validator->errors(), 'request_data' => $data]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate shipping_options content
        $shippingOptions = json_decode($request->input('shipping_options'), true);
        if (!is_array($shippingOptions) || empty($shippingOptions)) {
            Log::warning('Invalid shipping_options: ', ['shipping_options' => $request->input('shipping_options')]);
            return response()->json([
                'errors' => ['shipping_options' => ['Dữ liệu vận chuyển không hợp lệ, phải chứa ít nhất một phương thức.']]
            ], 422);
        }

        // Validate allowed shipping options
        $allowedOptions = ['express', 'standard'];
        foreach ($shippingOptions as $key => $value) {
            if (!in_array($key, $allowedOptions) || $value !== true) {
                Log::warning('Invalid shipping option: ', ['key' => $key, 'value' => $value]);
                return response()->json([
                    'errors' => ['shipping_options' => ['Phương thức vận chuyển không hợp lệ: ' . $key]]
                ], 422);
            }
        }

        // Validate province_id, district_id, ward_id with GHN API
        try {
            $ghnToken = env('GHN_TOKEN');
            if (empty($ghnToken)) {
                Log::error('GHN_TOKEN is not configured in .env');
                return response()->json(['errors' => ['api' => ['Thiếu cấu hình GHN_TOKEN.']]], 500);
            }

            // Validate province_id
            $provinceResponse = Http::withHeaders(['Token' => $ghnToken])
                ->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province');
            if (!$provinceResponse->successful()) {
                Log::error('GHN API error for provinces: ', ['response' => $provinceResponse->json()]);
                return response()->json(['errors' => ['province_id' => ['Lỗi khi kiểm tra tỉnh/thành phố với GHN.']]], 500);
            }
            $provinces = $provinceResponse->json()['data'] ?? [];
            if (!collect($provinces)->contains('ProvinceID', $data['province_id'])) {
                Log::warning('Invalid province_id: ', ['province_id' => $data['province_id']]);
                return response()->json(['errors' => ['province_id' => ['Tỉnh/thành phố không hợp lệ.']]], 422);
            }

            // Validate district_id
            $districtResponse = Http::withHeaders(['Token' => $ghnToken])
                ->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district', [
                    'province_id' => $data['province_id'],
                ]);
            if (!$districtResponse->successful()) {
                Log::error('GHN API error for districts: ', ['response' => $districtResponse->json()]);
                return response()->json(['errors' => ['district_id' => ['Lỗi khi kiểm tra quận/huyện với GHN.']]], 500);
            }
            $districts = $districtResponse->json()['data'] ?? [];
            if (!collect($districts)->contains('DistrictID', $data['district_id'])) {
                Log::warning('Invalid district_id: ', ['district_id' => $data['district_id']]);
                return response()->json(['errors' => ['district_id' => ['Quận/huyện không hợp lệ.']]], 422);
            }

            // Validate ward_id
            $wardResponse = Http::withHeaders(['Token' => $ghnToken])
                ->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward', [
                    'district_id' => $data['district_id'],
                ]);
            if (!$wardResponse->successful()) {
                Log::error('GHN API error for wards: ', ['response' => $wardResponse->json()]);
                return response()->json(['errors' => ['ward_id' => ['Lỗi khi kiểm tra phường/xã với GHN.']]], 500);
            }
            $wards = $wardResponse->json()['data'] ?? [];
            if (!collect($wards)->contains('WardCode', $request->ward_id)) {
                Log::warning('Invalid ward_id: ', ['ward_id' => $request->ward_id]);
                return response()->json(['errors' => ['ward_id' => ['Phường/xã không hợp lệ.']]], 422);
            }
        } catch (\Exception $e) {
            Log::error('GHN API exception: ', [
                'error' => $e->getMessage(),
                'request_data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => ['api' => ['Lỗi khi kiểm tra địa chỉ với GHN: ' . $e->getMessage()]]], 500);
        }

        try {
            // Tạo slug
            $slug = Str::slug($request->store_name) . '-' . Str::random(4) . '-' . uniqid();

            // Lưu ảnh và file
            $idCardFrontPath = null;
            $idCardBackPath = null;
            $identityCardFilePath = null;

            if ($request->hasFile('id_card_front_url')) {
                try {
                    $idCardFrontPath = $request->file('id_card_front_url')->store('seller-documents/cccd-front', 'r2');
                    Log::info('Uploaded id_card_front_url: ', ['path' => $idCardFrontPath]);
                } catch (\Exception $e) {
                    Log::error('Error uploading id_card_front_url: ', ['error' => $e->getMessage()]);
                    return response()->json(['errors' => ['id_card_front_url' => ['Lỗi khi tải lên ảnh mặt trước CCCD: ' . $e->getMessage()]]], 500);
                }
            }

            if ($request->hasFile('id_card_back_url')) {
                try {
                    $idCardBackPath = $request->file('id_card_back_url')->store('seller-documents/cccd-back', 'r2');
                    Log::info('Uploaded id_card_back_url: ', ['path' => $idCardBackPath]);
                } catch (\Exception $e) {
                    Log::error('Error uploading id_card_back_url: ', ['error' => $e->getMessage()]);
                    return response()->json(['errors' => ['id_card_back_url' => ['Lỗi khi tải lên ảnh mặt sau CCCD: ' . $e->getMessage()]]], 500);
                }
            }

            if ($request->hasFile('identity_card_file')) {
                try {
                    $identityCardFilePath = $request->file('identity_card_file')->store('seller-documents/business-proof', 'r2');
                    Log::info('Uploaded identity_card_file: ', ['path' => $identityCardFilePath]);
                } catch (\Exception $e) {
                    Log::error('Error uploading identity_card_file: ', ['error' => $e->getMessage()]);
                    return response()->json(['errors' => ['identity_card_file' => ['Lỗi khi tải lên giấy tờ xác minh: ' . $e->getMessage()]]], 500);
                }
            }

            // Log dữ liệu trước khi lưu vào database
            $sellerData = [
                'user_id' => $user->id,
                'store_name' => $request->store_name,
                'store_slug' => $slug,
                'phone_number' => $request->phone_number,
                'province_id' => $data['province_id'], // Số nguyên
                'district_id' => $data['district_id'], // Số nguyên
                'ward_id' => $request->ward_id, // Chuỗi
                'address' => $request->address,
                'shipping_options' => $request->shipping_options,
                'seller_type' => $request->seller_type,
                'tax_code' => $request->tax_code,
                'business_name' => $request->business_name,
                'business_email' => $request->business_email,
                'identity_card_number' => $request->identity_card_number,
                'date_of_birth' => $request->date_of_birth,
                'personal_address' => $request->personal_address,
                'id_card_front_url' => $idCardFrontPath,
                'id_card_back_url' => $idCardBackPath,
                'identity_card_file' => $identityCardFilePath,
                'verification_status' => 'pending',
            ];
            Log::info('Data to save in sellers table:', ['sellerData' => $sellerData]);

            // Tạo hoặc cập nhật seller
            $seller = Seller::updateOrCreate(
                ['user_id' => $user->id],
                $sellerData
            );

            Log::info('Seller registered successfully: ', ['seller_id' => $seller->id, 'user_id' => $user->id]);

            return response()->json([
                'message' => 'Đã hoàn tất đăng ký người bán.',
                'data' => [
                    'seller_id' => $seller->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in registerFull: ', [
                'error' => $e->getMessage(),
                'request_data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Lỗi khi đăng ký người bán: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDiscounts(Request $request, $slug)
    {
        $seller = Seller::where('store_slug', $slug)->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy cửa hàng.'
            ], 404);
        }

        // Lấy các voucher/discounts của shop
        $discounts = Discount::with(['products:id,name', 'categories:id,name', 'users:id,name'])
            ->where('seller_id', $seller->id)
            ->orderByDesc('created_at')
            ->get();

        // Nếu có user đăng nhập, lấy danh sách voucher đã lưu
        $savedCodes = [];
        if ($request->user()) {
            $savedCodes = $request->user()->savedVouchers()->pluck('code')->toArray();
        }

        $data = $discounts->map(function ($voucher) use ($savedCodes) {
            return [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'discount_type' => $voucher->discount_type,
                'discount_value' => $voucher->discount_value,
                'max_discount' => $voucher->max_discount,
                'min_order_value' => $voucher->min_order_value,
                'end_date' => $voucher->end_date,
                'is_saved' => in_array($voucher->code, $savedCodes),
                'products' => $voucher->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                'categories' => $voucher->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                'users' => $voucher->users->map(fn($u) => ['id' => $u->id, 'name' => $u->name]),
                'usage_limit' => $voucher->usage_limit,
                'used_count' => $voucher->used_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getBrands()
    {
        $brands = Seller::where('verification_status', 'verified') // lọc trước
            ->get()
            ->map(function ($seller) {
                return [
                    'id' => $seller->id,
                    'name' => $seller->store_name,
                    'slug' => $seller->store_slug,
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách cửa hàng thành công.',
            'data' => $brands
        ]);
    }
}
