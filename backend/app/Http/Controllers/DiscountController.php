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

class DiscountController extends Controller
{
    // List all discounts
    public function index()
    {
        try {
            $discounts = Discount::with(['products', 'categories', 'users', 'flashSales'])->get();
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

    // Create a new discount
    public function store(Request $request)
    {
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
            $discount = Discount::create($request->all());
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

    // Show a specific discount
    public function show($id)
    {
        try {
            $discount = Discount::with(['products', 'categories', 'users', 'flashSales'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin mã giảm giá thành công',
                'data' => $discount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy mã giảm giá'
            ], 404);
        }
    }

    // Update a discount
    public function update(Request $request, $id)
    {
        try {
            $discount = Discount::findOrFail($id);
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
            'start_date' => 'sometimes|required|date|after_or_equal:today',
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
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ ngày hôm nay trở đi',
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

    // Delete a discount
    public function destroy($id)
    {
        try {
            $discount = Discount::findOrFail($id);
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

    // Assign discount to products
    public function assignProducts(Request $request, $discountId)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
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
            $discount = Discount::findOrFail($discountId);
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

    // Assign discount to categories
    public function assignCategories(Request $request, $discountId)
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
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
            $discount = Discount::findOrFail($discountId);
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

    // Assign discount to users
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
            $discount = Discount::findOrFail($discountId);
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

    // Create a flash sale
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

    // List all flash sales
    public function indexFlashSales()
    {
        try {
            $flashSales = FlashSale::with(['product', 'discount'])->get();
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

    // Show a specific flash sale
    public function showFlashSale($id)
    {
        try {
            $flashSale = FlashSale::with(['product', 'discount'])->findOrFail($id);
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

    // Update a flash sale
    public function updateFlashSale(Request $request, $id)
    {
        try {
            $flashSale = FlashSale::findOrFail($id);
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

    // Delete a flash sale
    public function destroyFlashSale($id)
    {
        try {
            $flashSale = FlashSale::findOrFail($id);
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
    public function saveVoucherByCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:discounts,code',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã voucher không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để lưu voucher'
            ], 401);
        }

        $discount = Discount::where('code', $request->code)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->first();

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Mã voucher không còn hiệu lực hoặc đã hết hạn'
            ], 404);
        }

        // Kiểm tra đã lưu chưa
        $exists = DiscountUser::where('user_id', $user->id)
            ->where('discount_id', $discount->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã lưu mã voucher này rồi'
            ], 409);
        }

        // Lưu vào bảng trung gian
        DiscountUser::create([
            'user_id' => $user->id,
            'discount_id' => $discount->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lưu voucher thành công',
            'data' => $discount
        ], 201);
    }

    public function myVouchers(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập'
            ], 401);
        }
        $vouchers = $user->discounts()->get();
        \Log::info('User ID: ' . $user->id);
        \Log::info('Vouchers: ' . $vouchers->toJson());
        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    public function deleteUserVoucher($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập'
            ], 401);
        }
        $deleted = \App\Models\DiscountUser::where('user_id', $user->id)
            ->where('discount_id', $id)
            ->delete();
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Xoá mã giảm giá thành công'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy mã giảm giá để xoá'
            ], 404);
        }
    }

    // Public: Lấy tất cả voucher đang hoạt động cho user (chỉ của admin)
    public function indexPublic()
    {
        $discounts = \App\Models\Discount::where('status', 'active')
            ->where('end_date', '>', now())
            ->whereNull('seller_id') // Chỉ lấy discount của admin
            ->get();
        return response()->json([
            'success' => true,
            'data' => $discounts
        ]);
    }

    // Public: Lấy discount của seller theo seller_id
    public function getSellerDiscounts($sellerId)
    {
        $discounts = \App\Models\Discount::where('status', 'active')
            ->where('end_date', '>', now())
            ->where('seller_id', $sellerId)
            ->get();
        return response()->json([
            'success' => true,
            'data' => $discounts
        ]);
    }
}