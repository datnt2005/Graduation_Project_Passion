<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $addresses = Address::where('user_id', $userId)->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }


    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_code' => 'required|string|max:20',
            'detail' => 'required|string|max:255',
            'address_type' => 'nullable|string|in:home,company',
            'is_default' => 'nullable|boolean',
        ], [
            'user_id.required' => 'Trường user_id là bắt buộc.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'name.required' => 'Tên không được để trống.',
            'name.max' => 'Tên không được dài quá 255 ký tự.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.max' => 'Số điện thoại không được dài quá 20 ký tự.',
            'province_id.required' => 'Tỉnh/thành phố là bắt buộc.',
            'district_id.required' => 'Quận/huyện là bắt buộc.',
            'ward_code.required' => 'Phường/xã là bắt buộc.',
            'ward_code.max' => 'Phường/xã không được dài quá 20 ký tự.',
            'detail.required' => 'Chi tiết địa chỉ là bắt buộc.',
            'detail.max' => 'Chi tiết địa chỉ không được dài quá 255 ký tự.',
            'address_type.boolean' => 'Trường address_type phải là giá trị có hoặc không.',
            'is_default.boolean' => 'Trường is_default phải là giá trị có hoặc không.',
        ]);

        // Nếu là địa chỉ mặc định, bỏ mặc định các địa chỉ khác của user này
        if (!empty($validated['is_default']) && $validated['is_default']) {
            Address::where('user_id', $validated['user_id'])->update(['is_default' => 0]);
        } else {
            // Nếu không có is_default truyền vào, mặc định nó là false
            $validated['is_default'] = 0;
        }

        // Tạo mới địa chỉ
        $address = Address::create($validated);

        return response()->json(['message' => 'Địa chỉ đã được thêm.', 'address' => $address], 201);
    }

    /**
     * Cập nhật địa chỉ theo ID
     */
    public function update(Request $request, $id)
{
    $address = Address::findOrFail($id);

    // Validate dữ liệu đầu vào
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'province_id' => 'required|integer',
        'district_id' => 'required|integer',
        'ward_code' => 'required|string|max:20',
        'detail' => 'required|string|max:255',
        'address_type' => 'nullable|string|in:home,company',
        'is_default' => 'nullable|boolean',
    ], [
        'user_id.required' => 'Trường user_id là bắt buộc.',
        'user_id.exists' => 'Người dùng không tồn tại.',
        'name.required' => 'Tên không được để trống.',
        'name.max' => 'Tên không được dài quá 255 ký tự.',
        'phone.required' => 'Số điện thoại là bắt buộc.',
        'phone.max' => 'Số điện thoại không được dài quá 20 ký tự.',
        'province_id.required' => 'Tỉnh/thành phố là bắt buộc.',
        'district_id.required' => 'Quận/huyện là bắt buộc.',
        'ward_code.required' => 'Phường/xã là bắt buộc.',
        'ward_code.max' => 'Phường/xã không được dài quá 20 ký tự.',
        'detail.required' => 'Chi tiết địa chỉ là bắt buộc.',
        'detail.max' => 'Chi tiết địa chỉ không được dài quá 255 ký tự.',
        'address_type.in' => 'Loại địa chỉ không hợp lệ.',
        'is_default.boolean' => 'Trường is_default phải là giá trị có hoặc không.',
    ]);

    // Kiểm tra quyền sửa: chỉ sửa được địa chỉ của chính user
    if ($address->user_id != $validated['user_id']) {
        return response()->json(['message' => 'Không có quyền sửa địa chỉ này.'], 403);
    }

    // Nếu cập nhật is_default = true thì set các địa chỉ khác về 0
    if (!empty($validated['is_default']) && $validated['is_default']) {
        Address::where('user_id', $validated['user_id'])->update(['is_default' => 0]);
    } else {
        $validated['is_default'] = 0;
    }

    // Cập nhật dữ liệu địa chỉ
    $address->update($validated);

    return response()->json(['message' => 'Cập nhật địa chỉ thành công.', 'address' => $address], 200);
}


    /**
     * Xóa địa chỉ theo ID
     */
    public function destroy(Request $request, $id)
{
    $address = Address::findOrFail($id);

    // Kiểm tra quyền xóa: chỉ xóa được địa chỉ của chính user
    if ($address->user_id != $request->user_id) {
        return response()->json(['message' => 'Không có quyền xóa địa chỉ này.'], 403);
    }

    $address->delete();

    return response()->json(['message' => 'Địa chỉ đã được xóa.'], 200);
}

}
