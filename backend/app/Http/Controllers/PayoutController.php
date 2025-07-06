<?php
namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    // Lấy danh sách payout của seller hiện tại
    public function index(Request $request)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->firstOrFail();
        $payouts = Payout::where('seller_id', $seller->id)->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $payouts]);
    }

    // Tạo payout mới (admin hoặc hệ thống gọi)
    public function store(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'amount' => 'required|numeric|min:0',
        ]);
        $payout = Payout::create([
            'seller_id' => $request->seller_id,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);
        return response()->json(['data' => $payout], 201);
    }

    // Xem chi tiết payout
    public function show($id)
    {
        $payout = Payout::findOrFail($id);
        return response()->json(['data' => $payout]);
    }

    // Cập nhật trạng thái payout (admin hoặc hệ thống gọi)
    public function update(Request $request, $id)
    {
        $payout = Payout::findOrFail($id);
        $request->validate([
            'status' => 'required|in:pending,completed,failed',
            'transferred_at' => 'nullable|date',
        ]);
        $payout->status = $request->status;
        if ($request->status === 'completed' && $request->transferred_at) {
            // Convert ISO 8601 to Y-m-d H:i:s
            $payout->transferred_at = date('Y-m-d H:i:s', strtotime($request->transferred_at));
        }
        $payout->save();
        return response()->json(['data' => $payout]);
    }

    public function listApproved(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            // Admin: trả về tất cả payout đã completed
            $payouts = \App\Models\Payout::where('status', 'completed')
                ->orderByDesc('transferred_at')
                ->get();
            return response()->json($payouts);
        } elseif ($user->role === 'seller' && $user->seller) {
            // Seller: chỉ trả về payout của seller đó
            $payouts = \App\Models\Payout::where('seller_id', $user->seller->id)
                ->where('status', 'completed')
                ->orderByDesc('transferred_at')
                ->get();
            return response()->json($payouts);
        }
        // User thường: không trả về gì
        return response()->json([], 200);
    }
} 