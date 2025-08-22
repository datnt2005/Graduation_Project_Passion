<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Models\Seller;
use App\Models\Payout;
use Illuminate\Support\Facades\Auth;

class WithdrawRequestController extends Controller
{
    // Seller gửi yêu cầu rút tiền
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền muốn rút',
            'amount.numeric' => 'Số tiền không hợp lệ',
            'amount.min' => 'Số tiền phải lớn hơn 0',
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng',
            'bank_account.required' => 'Vui lòng nhập số tài khoản',
            'bank_account_name.required' => 'Vui lòng nhập tên chủ tài khoản',
        ]);

        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->firstOrFail();
        $amount = $request->input('amount');

        // Giới hạn rút tối đa 25 triệu/ngày
        $today = now()->startOfDay();
        $totalToday = \App\Models\WithdrawRequest::where('seller_id', $seller->id)
            ->where('created_at', '>=', $today)
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');
        if ($totalToday + $amount > 25000000) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ được rút tối đa 25.000.000đ mỗi ngày. Số tiền còn lại có thể rút hôm nay: ' . number_format(25000000 - $totalToday) . 'đ'
            ], 400);
        }

        // Tính số dư khả dụng
        $totalPayout = Payout::where('seller_id', $seller->id)->where('status', 'completed')->sum('amount');
        $failedPayouts = Payout::where('seller_id', $seller->id)->where('status', 'failed')->sum('amount');
        $totalWithdrawCompleted = WithdrawRequest::where('seller_id', $seller->id)->where('status', 'completed')->sum('amount');
        $totalWithdrawPending = WithdrawRequest::where('seller_id', $seller->id)->where('status', 'pending')->sum('amount');
        $available = $totalPayout - $failedPayouts - $totalWithdrawCompleted - $totalWithdrawPending;

        if ($amount > $available) {
            return response()->json([
                'success' => false,
                'message' => 'Số dư không đủ',
                'available' => $available
            ], 400);
        }

        $withdraw = WithdrawRequest::create([
            'seller_id' => $seller->id,
            'amount' => $amount,
            'status' => 'pending',
            'note' => $request->input('note'),
            'bank_name' => $request->input('bank_name'),
            'bank_account' => $request->input('bank_account'),
            'bank_account_name' => $request->input('bank_account_name'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gửi yêu cầu rút tiền thành công, chờ admin duyệt!',
            'data' => $withdraw,
            'available' => $available - $amount
        ]);
    }

    // Seller xem lịch sử rút tiền
    public function index(Request $request)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->firstOrFail();
        $withdraws = WithdrawRequest::where('seller_id', $seller->id)->orderByDesc('created_at')->get();
        return response()->json(['data' => $withdraws]);
    }

    // Admin xem tất cả yêu cầu rút tiền
    public function adminIndex(Request $request)
    {
        $withdraws = \App\Models\WithdrawRequest::with(['seller.user'])
            ->orderByDesc('created_at')
            ->get();
        $data = $withdraws->map(function ($item) {
            return [
                'id' => $item->id,
                'seller_id' => $item->seller_id,
                'amount' => $item->amount,
                'status' => $item->status,
                'note' => $item->note,
                'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : null,
                'approved_at' => $item->approved_at ? $item->approved_at->format('d/m/Y H:i:s') : null,
                'bank_name' => $item->bank_name,
                'bank_account' => $item->bank_account,
                'bank_account_name' => $item->bank_account_name,
                // Thông tin seller và user
                'seller' => $item->seller ? [
                    // Ưu tiên shop_name; nếu không có, fallback về store_name để đảm bảo hiển thị đúng tên cửa hàng
                    'shop_name' => $item->seller->shop_name ?? $item->seller->store_name ?? null,
                    'store_name' => $item->seller->store_name ?? null,
                    'name' => optional($item->seller->user)->name,
                    'email' => optional($item->seller->user)->email,
                    'phone' => optional($item->seller->user)->phone,
                ] : null,
            ];
        });
        return response()->json(['data' => $data]);
    }

    // Admin duyệt yêu cầu rút tiền
    public function approve($id)
    {
        $withdraw = \App\Models\WithdrawRequest::findOrFail($id);
        if ($withdraw->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Yêu cầu đã được xử lý!'], 400);
        }
        $withdraw->status = 'completed';
        $withdraw->approved_at = now();
        $withdraw->save();
        return response()->json(['success' => true, 'message' => 'Duyệt rút tiền thành công!']);
    }

    // Admin từ chối yêu cầu rút tiền
    public function reject(Request $request, $id)
    {
        $withdraw = \App\Models\WithdrawRequest::findOrFail($id);
        if ($withdraw->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Yêu cầu đã được xử lý!'], 400);
        }
        $withdraw->status = 'rejected';
        $withdraw->approved_at = now();
        $withdraw->note = $request->input('note', $withdraw->note);
        $withdraw->save();
        return response()->json(['success' => true, 'message' => 'Đã từ chối yêu cầu rút tiền!']);
    }

    // API lấy số dư khả dụng cho seller
    public function getAvailableBalance(Request $request)
    {
        $user = Auth::user();
        $seller = Seller::where('user_id', $user->id)->firstOrFail();
        
        // Tính tổng payout đã hoàn thành
        $totalPayout = Payout::where('seller_id', $seller->id)
            ->where('status', 'completed')
            ->sum('amount');
        
        // Tính tổng payout đã bị thất bại (status = 'failed')
        $failedPayouts = Payout::where('seller_id', $seller->id)
            ->where('status', 'failed')
            ->sum('amount');
        
        // Tính tổng rút tiền đã hoàn thành
        $totalWithdrawCompleted = WithdrawRequest::where('seller_id', $seller->id)
            ->where('status', 'completed')
            ->sum('amount');
        
        // Tính tổng rút tiền đang chờ
        $totalWithdrawPending = WithdrawRequest::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        // Số dư khả dụng = Tổng payout - Payout đơn hàng bị thất bại - Tổng rút tiền
        $available = $totalPayout - $failedPayouts - $totalWithdrawCompleted - $totalWithdrawPending;
        
        return response()->json([
            'success' => true,
            'available' => max(0, $available), // Đảm bảo không âm
            'total_payout' => $totalPayout,
            'failed_payouts' => $failedPayouts,
            'total_withdraw_completed' => $totalWithdrawCompleted,
            'total_withdraw_pending' => $totalWithdrawPending
        ]);
    }
}
