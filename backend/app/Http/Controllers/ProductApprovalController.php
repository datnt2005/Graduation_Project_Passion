<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductPic;
use App\Models\VariantAttribute;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Tag;
use App\Models\Inventory;
use App\Models\Review;
use App\Models\Seller;
use App\Models\ProductApproval;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\ProductRequest;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProductApprovedMail;
use App\Mail\ProductRejectedMail;
use App\Models\Notification;
use App\Models\User;
use App\Models\NotificationRecipient;


class ProductApprovalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $search = trim($request->get('search', ''));
            $perPage = $request->get('per_page', 10);

            $cacheKey = 'products_approval_page_' . $page . '_perpage_' . $perPage . '_search_' . md5($search);
            $ttl = 3600;

            // Use tags for cache
            $products = Cache::store('redis')->tags(['products'])->remember($cacheKey, $ttl, function () use ($search, $perPage) {
                return Product::with([
                    'seller:id,store_name,store_slug,phone_number',
                    'categories',
                    'productVariants',
                    'productVariants.inventories',
                    'productVariants.attributes',
                    'productPic',
                    'tags'
                ])
                    ->whereIn('admin_status', ['pending'])
                    ->when($search, function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage)
                    ->toArray();
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getProductApprovalById($id)
    {
        try {
            $product = Product::with([
                'seller:id,store_name,store_slug,phone_number',
                'categories',
                'productVariants',
                'productVariants.inventories',
                'productVariants.attributes',
                'productPic',
                'tags'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin sản phẩm thành công.',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông tin sản phẩm.',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }
    protected function clearProductCache()
    {
        try {
            // Clear tagged caches
            Cache::store('redis')->tags(['products'])->flush();

            // Clear non-tagged cache keys with Laravel prefix
            $redis = Cache::store('redis')->getRedis();
            $prefix = config('cache.prefix', 'laravel_cache');
            $cursor = 0;
            do {
                $scan = $redis->scan($cursor, ['MATCH' => $prefix . 'products_approval_page_*', 'COUNT' => 100]);
                $cursor = $scan[0];
                foreach ($scan[1] as $key) {
                    // Remove the prefix from the key for Cache::forget
                    $cacheKey = str_replace($prefix, '', $key);
                    Cache::store('redis')->forget($cacheKey);
                }
            } while ($cursor != 0);
        } catch (\Exception $e) {
        }
    }
    public function approveProduct($id, Request $request)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.',
            ], 401);
        }

        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $validator = Validator::make($request->all(), [
            'admin_status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|max:1000',
        ], [
            'admin_status.required' => 'Trạng thái là bắt buộc.',
            'admin_status.in' => 'Trạng thái không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền duyệt sản phẩm.',
            ], 403);
        }

        // if ($product->admin_status !== 'pending') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Chỉ có thể duyệt sản phẩm ở trạng thái chờ duyệt.',
        //     ], 422);
        // }

        // Cập nhật trạng thái
        $product->admin_status = $request->admin_status;
        $product->save();

        // Lưu vào bảng product_approvals
        ProductApproval::create([
            'product_id' => $product->id,
            'admin_id' => $user->id,
            'status' => $request->admin_status,
            'reason' => $request->reason,
        ]);

        // Load lại dữ liệu đầy đủ
        $product->load([
            'seller:id,store_name,store_slug,phone_number,user_id',
            'seller.user:id,name,email',
            'categories',
            'productVariants',
            'productPic',
            'tags'
        ]);

        // Kiểm tra seller và email
        $seller = $product->seller;
        $sellerUser = $seller ? $seller->user : null;
        $sellerEmail = $sellerUser ? $sellerUser->email : null;

        if (!$seller || !$sellerUser || !$sellerEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xử lý: seller hoặc email của user không tồn tại'

            ], 422);

        }

        // Gửi email thông báo
      try {
    // Tạo thông báo
    $notification = Notification::create([
        'title' => $request->admin_status === 'approved'
            ? "Sản phẩm đã được duyệt"
            : "Sản phẩm đã bị từ chối",
        'content' => $request->admin_status === 'approved'
            ? "Sản phẩm '{$product->name}' đã được duyệt vào lúc " . now()->format('d/m/Y H:i')
            : "Sản phẩm '{$product->name}' đã bị từ chối lúc " . now()->format('d/m/Y H:i') . ". Lý do: {$request->reason}",
        'type' => 'system',
        'user_id' => auth()->id(), // Admin
        'from_role' => 'admin',
        'to_roles' => json_encode(['seller']),
        'link' => 'seller/products/list-product',
        'status' => 'sent',
        'channels' => json_encode(['dashboard']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 🎯 Gửi thông báo cụ thể cho seller
    NotificationRecipient::create([
        'notification_id' => $notification->id,
        'user_id' => $sellerUser->id,
    ]);

    // 📧 Gửi email
    if ($request->admin_status === 'approved') {
        Mail::to($sellerEmail)
            ->send(new ProductApprovedMail($product, $seller));
    } else {
        Mail::to($sellerEmail)
            ->send(new ProductRejectedMail($product, $seller, $request->reason));
    }
} catch (\Exception $mailException) {
    return response()->json([
        'success' => true,
        'message' => 'Duyệt sản phẩm thành công nhưng gửi email thất bại: ' . $mailException->getMessage(),
        'data' => $product,
    ], 200);
}


        // Xóa cache
        $this->clearProductCache();

        return response()->json([
            'success' => true,
            'message' => 'Đã duyệt sản phẩm thành công!',
            'data' => $product,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Thay đổi trạng thái sản phẩm thất bại.',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

    public function getRejectedProducts(Request $request)
    {
        $products = Product::with(['seller:id,store_name,store_slug,phone_number', 'categories', 'productVariants', 'productPic', 'tags', 'productVariants.inventories', 'productVariants.attributes'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->where('admin_status', 'rejected')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $this->clearProductCache();
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data' => $products
        ], 200);
    }

    public function getHistoryApproval(Request $request)
    {
        try {
            // Check if user is authenticated and has admin role
            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập vào lịch sử xét duyệt.',
                ], 403);
            }

            $perPage = $request->get('per_page', 10);
            $search = trim($request->get('search', ''));

            // Query with search and pagination
            $query = ProductApproval::with(['product', 'admin'])
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('admin', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $approvals = $query->paginate($perPage);

            // Transform data
            $data = $approvals->getCollection()->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'product_id' => $approval->product_id,
                    'product_name' => $approval->product ? $approval->product->name : '–',
                    'admin_id' => $approval->admin_id,
                    'admin_name' => $approval->admin ? ($approval->admin->name ?? $approval->admin->email) : '–',
                    'status' => $approval->status,
                    'reason' => $approval->reason ?? '–',
                    'created_at' => optional($approval->created_at)->toIso8601String(),
                    'updated_at' => optional($approval->updated_at)->toIso8601String(),
                ];
            });

            // Return paginated response
            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $approvals->currentPage(),
                    'last_page' => $approvals->lastPage(),
                    'per_page' => $approvals->perPage(),
                    'total' => $approvals->total(),
                ],
                'message' => 'Lấy lịch sử xét duyệt thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy lịch sử xét duyệt: ' . $e->getMessage(),
            ], 500);
        }
    }
}
