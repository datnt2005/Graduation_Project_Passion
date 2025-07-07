<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $userId = Auth::id();
        $addresses = Address::where('user_id', $userId)->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    public function show($id)
    {
        $address = Address::findOrFail($id);

        // Kiểm tra quyền xem
        if ($address->user_id != Auth::id()) {
            return response()->json(['message' => 'Không có quyền xem địa chỉ này.'], 403);
        }

        return response()->json(['data' => $address]);
    }

    public function store(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['message' => 'Bạn cần đăng nhập để thêm địa chỉ.'], 401);
    }

    $messages = [
        'name.required' => 'Tên người nhận là bắt buộc.',
        'name.max' => 'Tên người nhận không được vượt quá 255 ký tự.',
        'phone.required' => 'Số điện thoại là bắt buộc.',
        'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
        'phone.regex' => 'Số điện thoại không hợp lệ.',
        'province_id.required' => 'Tỉnh/Thành phố là bắt buộc.',
        'district_id.required' => 'Quận/Huyện là bắt buộc.',
        'ward_code.required' => 'Phường/Xã là bắt buộc.',
        'detail.required' => 'Địa chỉ chi tiết là bắt buộc.',
        'detail.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',
        'address_type.in' => 'Loại địa chỉ không hợp lệ.',
        'is_default.boolean' => 'Giá trị mặc định không hợp lệ.',
    ];

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => ['required', 'string', 'max:20', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
        'province_id' => 'required|integer',
        'district_id' => 'required|integer',
        'ward_code' => 'required|string|max:20',
        'detail' => 'required|string|max:255',
        'address_type' => 'nullable|string|in:home,company',
        'is_default' => 'nullable|boolean',
    ], $messages);

    $validated['user_id'] = Auth::id();

    if (!empty($validated['is_default']) && $validated['is_default']) {
        Address::where('user_id', $validated['user_id'])->update(['is_default' => 0]);
    } else {
        $validated['is_default'] = 0;
    }

    $address = Address::create($validated);

    return response()->json(['message' => 'Địa chỉ đã được thêm.', 'address' => $address], 201);
}


public function update(Request $request, $id)
{
    if (!Auth::check()) {
        return response()->json(['message' => 'Bạn cần đăng nhập để cập nhật địa chỉ.'], 401);
    }

    $address = Address::findOrFail($id);

    if ($address->user_id != Auth::id()) {
        return response()->json(['message' => 'Không có quyền sửa địa chỉ này.'], 403);
    }

    $messages = [
        'name.required' => 'Tên người nhận là bắt buộc.',
        'name.max' => 'Tên người nhận không được vượt quá 255 ký tự.',
        'phone.required' => 'Số điện thoại là bắt buộc.',
        'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
        'phone.regex' => 'Số điện thoại không hợp lệ.',
        'province_id.required' => 'Tỉnh/Thành phố là bắt buộc.',
        'district_id.required' => 'Quận/Huyện là bắt buộc.',
        'ward_code.required' => 'Phường/Xã là bắt buộc.',
        'detail.required' => 'Địa chỉ chi tiết là bắt buộc.',
        'detail.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',
        'address_type.in' => 'Loại địa chỉ không hợp lệ.',
        'is_default.boolean' => 'Giá trị mặc định không hợp lệ.',
    ];

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => ['required', 'string', 'max:20', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
        'province_id' => 'required|integer',
        'district_id' => 'required|integer',
        'ward_code' => 'required|string|max:20',
        'detail' => 'required|string|max:255',
        'address_type' => 'nullable|string|in:home,company',
        'is_default' => 'nullable|boolean',
    ], $messages);

    if (!empty($validated['is_default']) && $validated['is_default']) {
        Address::where('user_id', Auth::id())->update(['is_default' => 0]);
    } else {
        $validated['is_default'] = 0;
    }

    $address->update($validated);

    return response()->json(['message' => 'Cập nhật địa chỉ thành công.', 'address' => $address]);
}

    public function destroy($id)
    {
        $address = Address::findOrFail($id);

        if ($address->user_id != Auth::id()) {
            return response()->json(['message' => 'Không có quyền xóa địa chỉ này.'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Địa chỉ đã được xóa.']);
    }
}