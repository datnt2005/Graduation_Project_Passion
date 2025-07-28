<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerApprovedMail;
use App\Mail\SellerRejectedMail;

class AdminSellerController extends Controller
{
    // Xem danh sách seller (cả chờ duyệt, đã duyệt, từ chối)
    public function index()
    {
        $sellers = Seller::with(['user'])->latest()->get();
        return response()->json($sellers);
    }

    // Xem chi tiết 1 seller
    public function show($id)
    {
        $seller = Seller::with(['user'])->findOrFail($id);
        return response()->json($seller);
    }

    // Duyệt seller
    public function verify($id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        $seller->verification_status = 'verified';
        $seller->save();

        // Gán role cho user
        $user = $seller->user;
        $user->role = 'seller';
        $user->save();

        // Gửi email
        Mail::to($user->email)->send(new SellerApprovedMail($seller));

        return response()->json(['message' => 'Đã duyệt seller thành công.']);
    }

    // Từ chối seller
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $seller = Seller::with('user')->findOrFail($id);
        $seller->verification_status = 'rejected';
        $seller->save();

        // Gửi email từ chối
        Mail::to($seller->user->email)->send(new SellerRejectedMail($seller, $request->reason));

        return response()->json(['message' => 'Đã từ chối seller và gửi email thông báo.']);
    }

 public function ban($id)
{
    $seller = Seller::with('user')->where('id', $id)->first();

    if (!$seller) {
        return response()->json(['message' => 'Seller không tồn tại.'], 404);
    }

    $seller->verification_status = 'banned';
    $seller->save();

    $user = $seller->user;
    $user->role = 'user';
    $user->save();

    return response()->json(['message' => 'Đã cấm seller thành công.']);
}

}
