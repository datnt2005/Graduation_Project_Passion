<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $userId = Auth::id();
        $cacheKey = 'addresses_user_' . $userId;
        $ttl = 3600; // 1 tiếng

        $addresses = Cache::store('redis')->tags(['addresses'])->remember($cacheKey, $ttl, function () use ($userId) {
            return Address::where('user_id', $userId)->get();
        });

        return response()->json(['data' => $addresses]);
    }

    public function show($id)
    {
        $address = Address::findOrFail($id);

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
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên phải là một chuỗi ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.string' => 'Số điện thoại phải là một chuỗi ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng.',

            'province_id.required' => 'Vui lòng chọn tỉnh/thành.',
            'province_id.integer' => 'ID tỉnh/thành phải là số nguyên.',

            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'ID quận/huyện phải là số nguyên.',

            'ward_code.required' => 'Vui lòng chọn phường/xã.',
            'ward_code.string' => 'Mã phường/xã phải là chuỗi.',
            'ward_code.max' => 'Mã phường/xã không được vượt quá 20 ký tự.',

            'detail.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'detail.string' => 'Địa chỉ chi tiết phải là một chuỗi ký tự.',
            'detail.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',

            'address_type.string' => 'Loại địa chỉ phải là chuỗi.',
            'address_type.in' => 'Loại địa chỉ không hợp lệ. Chỉ chấp nhận home hoặc company.',

            'is_default.boolean' => 'Giá trị mặc định phải là true hoặc false.',
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

        Cache::store('redis')->tags(['addresses'])->forget('addresses_user_' . Auth::id());

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

        $messages = [ // giống như bên store
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên phải là một chuỗi ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.string' => 'Số điện thoại phải là một chuỗi ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng.',

            'province_id.required' => 'Vui lòng chọn tỉnh/thành.',
            'province_id.integer' => 'ID tỉnh/thành phải là số nguyên.',

            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'ID quận/huyện phải là số nguyên.',

            'ward_code.required' => 'Vui lòng chọn phường/xã.',
            'ward_code.string' => 'Mã phường/xã phải là chuỗi.',
            'ward_code.max' => 'Mã phường/xã không được vượt quá 20 ký tự.',

            'detail.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'detail.string' => 'Địa chỉ chi tiết phải là một chuỗi ký tự.',
            'detail.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',

            'address_type.string' => 'Loại địa chỉ phải là chuỗi.',
            'address_type.in' => 'Loại địa chỉ không hợp lệ. Chỉ chấp nhận home hoặc company.',

            'is_default.boolean' => 'Giá trị mặc định phải là true hoặc false.',
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

        Cache::store('redis')->tags(['addresses'])->forget('addresses_user_' . Auth::id());

        return response()->json(['message' => 'Cập nhật địa chỉ thành công.', 'address' => $address]);
    }


    public function destroy($id)
    {
        $address = Address::findOrFail($id);

        if ($address->user_id != Auth::id()) {
            return response()->json(['message' => 'Không có quyền xóa địa chỉ này.'], 403);
        }

        $address->delete();

        Cache::store('redis')->tags(['addresses'])->forget('addresses_user_' . Auth::id());

        return response()->json(['message' => 'Địa chỉ đã được xóa.']);
    }
}
