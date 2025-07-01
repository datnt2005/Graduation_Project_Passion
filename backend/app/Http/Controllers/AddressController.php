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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_code' => 'required|string|max:20',
            'detail' => 'required|string|max:255',
            'address_type' => 'nullable|string|in:home,company',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // Nếu là địa chỉ mặc định thì reset các địa chỉ cũ
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
        $address = Address::findOrFail($id);

        if ($address->user_id != Auth::id()) {
            return response()->json(['message' => 'Không có quyền sửa địa chỉ này.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_code' => 'required|string|max:20',
            'detail' => 'required|string|max:255',
            'address_type' => 'nullable|string|in:home,company',
            'is_default' => 'nullable|boolean',
        ]);

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

