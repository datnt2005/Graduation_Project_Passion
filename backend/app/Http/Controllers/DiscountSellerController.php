<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountProduct;
use App\Models\DiscountCategory;
use App\Models\DiscountUser;
use App\Models\FlashSale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

use App\Models\NotificationRecipient;
class DiscountSellerController extends Controller
{
    // List all discounts for current seller
    public function index()
    {
        try {
            $seller = Auth::user()->seller;
            $discounts = Discount::with(['products', 'categories', 'users', 'flashSales'])
                ->where('seller_id', $seller->id)
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách mã giảm giá thành công',
                'data' => $discounts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách mã giảm giá: ' . $e->getMessage()
            ], 500);
        }
    }

    // Create a new discount for seller
    public function store(Request $request)
    {
        $seller = Auth::user()->seller;
        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ seller mới được phép tạo mã giảm giá'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:discounts',
            'description' => 'nullable|string',
            'discount_type' => 'in:percentage,fixed,shipping_fee',
            'discount_value' => 'numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,expired',
        ], [
            'name.required' => 'Tên mã giảm giá không được để trống',
            'code.required' => 'Mã giảm giá không được để trống',
            'code.unique' => 'Mã giảm giá đã tồn tại',
            'discount_type.required' => 'Loại giảm giá không được để trống',
            'discount_type.in' => 'Loại giảm giá không hợp lệ',
            'discount_value.required' => 'Giá trị giảm giá không được để trống',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn 0',
            'usage_limit.min' => 'Giới hạn sử dụng phải lớn hơn 0',
            'min_order_value.min' => 'Giá trị đơn hàng tối thiểu phải lớn hơn 0',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng',
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ ngày hôm nay trở đi',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'status.required' => 'Trạng thái không được để trống',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['seller_id'] = $seller ? $seller->id : null;
            $discount = Discount::create($data);

           $followers = $seller->followers; // Lấy danh sách người theo dõi seller

         foreach ($followers as $follower) {
    $notification = Notification::create([
        'title' => 'Mã giảm giá mới từ người bán bạn đang theo dõi',
        'content' => "Người bán {$seller->name} vừa tạo mã giảm giá mới: {$discount->code}",
        'type' => 'promotion',
        'link' => "/seller/{$seller->slug}",
        'user_id' => $seller->user_id,
        'from_role' => 'seller',
        'channels' => json_encode(['dashboard']),
        'status' => 'sent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);


    NotificationRecipient::create([
        'notification_id' => $notification->id,
        'user_id' => $follower->id,
        'is_read' => false,
        'is_hidden' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}


            return response()->json([
                'success' => true,
                'message' => 'Tạo mã giảm giá thành công',
                'data' => $discount
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo mã giảm giá: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show a specific discount for seller
    public function show($id)
    {
        try {
            \Log::info('show method called with id:', ['id' => $id]);
            $user = Auth::user();
            \Log::info('User in show:', ['user' => $user]);
            
            $seller = $user->seller;
            \Log::info('Seller in show:', ['seller' => $seller]);
            
            if (!$seller) {
                \Log::error('No seller found in show method');
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy seller'
                ], 403);
            }
            
            $discount = Discount::with(['products', 'categories', 'users', 'flashSales'])
                ->where('seller_id', $seller->id)
                ->findOrFail($id);
            
            \Log::info('Discount found:', ['discount' => $discount]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin mã giảm giá thành công',
                'data' => $discount
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in show method:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy mã giảm giá'
            ], 404);
        }
    }

    // Update a discount for seller
    public function update(Request $request, $id)
    {
        try {
            $seller = Auth::user()->seller;
            $discount = Discount::where('seller_id', $seller->id)->findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy mã giảm giá'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'code' => 'string|max:50|unique:discounts,code,' . $id,
            'description' => 'nullable|string',
            'discount_type' => 'in:percentage,fixed,shipping_fee',
            'discount_value' => 'numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'status' => 'in:active,inactive,expired',
        ], [
            'code.unique' => 'Mã giảm giá đã tồn tại',
            'discount_type.in' => 'Loại giảm giá không hợp lệ',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn 0',
            'usage_limit.min' => 'Giới hạn sử dụng phải lớn hơn 0',
            'min_order_value.min' => 'Giá trị đơn hàng tối thiểu phải lớn hơn 0',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $discount->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công',
                'data' => $discount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật mã giảm giá: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete a discount for seller
    public function destroy($id)
    {
        try {
            $seller = Auth::user()->seller;
            $discount = Discount::where('seller_id', $seller->id)->findOrFail($id);
            $discount->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa mã giảm giá thành công'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa mã giảm giá: ' . $e->getMessage()
            ], 500);
        }
    }

    // Assign discount to products for seller
    public function assignProducts(Request $request, $discountId)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $seller = Auth::user()->seller;
            $discount = Discount::where('seller_id', $seller->id)->findOrFail($discountId);
            $discount->products()->sync($request->product_ids);
            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã giảm giá cho sản phẩm thành công'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi áp dụng mã giảm giá cho sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    // Assign discount to categories for seller
    public function assignCategories(Request $request, $discountId)
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $seller = Auth::user()->seller;
            $discount = Discount::where('seller_id', $seller->id)->findOrFail($discountId);
            $discount->categories()->sync($request->category_ids);
            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã giảm giá cho danh mục thành công'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi áp dụng mã giảm giá cho danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    // Assign discount to users for seller
    public function assignUsers(Request $request, $discountId)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $seller = Auth::user()->seller;
            $discount = Discount::where('seller_id', $seller->id)->findOrFail($discountId);
            $discount->users()->sync($request->user_ids);
            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã giảm giá cho người dùng thành công'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi áp dụng mã giảm giá cho người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    // Create a flash sale for seller
    public function storeFlashSale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'discount_id' => 'required|exists:discounts,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'video' => 'nullable|string',
            'link' => 'nullable|string',
            'status' => 'required|in:active,inactive,expired',
        ], [
            'product_id.required' => 'Sản phẩm không được để trống',
            'product_id.exists' => 'Sản phẩm không tồn tại',
            'discount_id.required' => 'Mã giảm giá không được để trống',
            'discount_id.exists' => 'Mã giảm giá không tồn tại',
            'start_time.required' => 'Thời gian bắt đầu không được để trống',
            'end_time.required' => 'Thời gian kết thúc không được để trống',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'quantity.required' => 'Số lượng không được để trống',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'price.required' => 'Giá không được để trống',
            'price.min' => 'Giá phải lớn hơn 0',
            'discounted_price.required' => 'Giá giảm không được để trống',
            'discounted_price.min' => 'Giá giảm phải lớn hơn 0',
            'status.required' => 'Trạng thái không được để trống',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $seller = Auth::user()->seller;
            // Kiểm tra discount_id có thuộc seller không
            $discount = Discount::where('id', $request->discount_id)
                ->where('seller_id', $seller->id)
                ->firstOrFail();
            $flashSale = FlashSale::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Tạo flash sale thành công',
                'data' => $flashSale
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo flash sale: ' . $e->getMessage()
            ], 500);
        }
    }

    // List all flash sales for seller
    public function indexFlashSales()
    {
        try {
            $seller = Auth::user()->seller;
            $flashSales = FlashSale::with(['product', 'discount'])
                ->whereHas('discount', function($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                })
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách flash sale thành công',
                'data' => $flashSales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách flash sale: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show a specific flash sale for seller
    public function showFlashSale($id)
    {
        try {
            $seller = Auth::user()->seller;
            $flashSale = FlashSale::with(['product', 'discount'])
                ->where('id', $id)
                ->whereHas('discount', function($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                })
                ->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin flash sale thành công',
                'data' => $flashSale
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy flash sale'
            ], 404);
        }
    }

    // Update a flash sale for seller
    public function updateFlashSale(Request $request, $id)
    {
        try {
            $seller = Auth::user()->seller;
            $flashSale = FlashSale::where('id', $id)
                ->whereHas('discount', function($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                })
                ->firstOrFail();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy flash sale'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'exists:products,id',
            'discount_id' => 'exists:discounts,id',
            'start_time' => 'date',
            'end_time' => 'date|after:start_time',
            'quantity' => 'integer|min:1',
            'price' => 'numeric|min:0',
            'discounted_price' => 'numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'video' => 'nullable|string',
            'link' => 'nullable|string',
            'status' => 'in:active,inactive,expired',
        ], [
            'product_id.exists' => 'Sản phẩm không tồn tại',
            'discount_id.exists' => 'Mã giảm giá không tồn tại',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'price.min' => 'Giá phải lớn hơn 0',
            'discounted_price.min' => 'Giá giảm phải lớn hơn 0',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi dữ liệu nhập vào',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $flashSale->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật flash sale thành công',
                'data' => $flashSale
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật flash sale: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete a flash sale for seller
    public function destroyFlashSale($id)
    {
        try {
            $seller = Auth::user()->seller;
            $flashSale = FlashSale::where('id', $id)
                ->whereHas('discount', function($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                })
                ->firstOrFail();
            $flashSale->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa flash sale thành công'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa flash sale: ' . $e->getMessage()
            ], 500);
        }
    }
     // Lấy danh sách sản phẩm của seller
    public function sellerProducts(Request $request)
    {
        try {
            \Log::info('sellerProducts method called');
            $user = Auth::user();
            \Log::info('User:', ['user' => $user]);
            
            $seller = $user->seller;
            \Log::info('Seller:', ['seller' => $seller]);
            
            if (!$seller) {
                \Log::error('No seller found for user');
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy seller'
                ], 403);
            }
            
            $perPage = $request->get('per_page', 1000);
            $discountId = $request->get('discount_id'); // Thêm parameter để lấy discount_id
            \Log::info('Per page:', ['per_page' => $perPage, 'discount_id' => $discountId]);
            
            // Base query cho tất cả sản phẩm của seller
            $query = \App\Models\Product::where('seller_id', $seller->id);
            
            // Nếu có discount_id, bao gồm cả sản phẩm đã được gán cho discount đó
            if ($discountId) {
                $discount = Discount::find($discountId);
                if ($discount && $discount->seller_id == $seller->id) {
                    // Lấy tất cả sản phẩm của seller (active và approved) HOẶC sản phẩm đã được gán cho discount
                    $query->where(function($q) use ($discount) {
                        $q->where(function($subQ) {
                            $subQ->where('status', 'active')
                                 ->where('admin_status', 'approved');
                        })->orWhereHas('discounts', function($discountQ) use ($discount) {
                            $discountQ->where('discount_id', $discount->id);
                        });
                    });
                }
            } else {
                // Nếu không có discount_id, chỉ lấy sản phẩm active và approved
                $query->where('status', 'active')
                      ->where('admin_status', 'approved');
            }
            
            \Log::info('Query built for seller_id:', ['seller_id' => $seller->id, 'discount_id' => $discountId]);
            
            // Luôn trả về array để frontend dễ xử lý
            $products = $query->paginate($perPage);
            \Log::info('Products found:', ['count' => $products->count()]);
            \Log::info('Products details:', ['products' => $products->items()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công',
                'data' => $products->items()
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in sellerProducts:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
    // Các hàm saveVoucherByCode, myVouchers, deleteUserVoucher, indexPublic giữ nguyên như DiscountController nếu seller cần dùng
}
