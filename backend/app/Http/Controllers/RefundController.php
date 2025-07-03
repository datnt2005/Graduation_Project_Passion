<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Refund::with(['order', 'user'])->latest()->get()
        ]);
    }

    public function show($id)
    {
        $refund = Refund::with(['order', 'user'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $refund
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);
        $refund = Refund::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu hoàn tiền đã được gửi',
            'data' => $refund
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);
        $refund->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái hoàn tiền thành công',
            'data' => $refund
        ]);
    }

    public function destroy($id)
    {
        $refund = Refund::findOrFail($id);
        $refund->delete();
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa yêu cầu hoàn tiền'
        ]);
    }
}
