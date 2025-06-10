<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminSellerController extends Controller
{
    // Xem danh sách seller chờ duyệt
    public function index(){

         $sellers = Seller::with('user')
            ->where('verification_status', 'pending')
            ->get();

        return response()->json($sellers);
    }

    // Xem chi tiết 1 seller
    public function show($id)
    {
        $seller = Seller::with(['user', 'business'])->findOrFail($id);
        return response()->json($seller);
    }

      // Duyệt seller
    public function verify($id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        $seller->verification_status = 'verified';
        $seller->save();

        // Gửi email thông báo
        Mail::to($seller->user->email)->send(new \App\Mail\SellerApprovedMail($seller));

        return response()->json(['message' => 'Đã duyệt seller thành công và gửi email thông báo.']);
    }

    // Từ chối seller
    public function reject(Request $request, $id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        $seller->verification_status = 'rejected';
        $seller->save();

        // Gửi email lý do từ chối
        Mail::to($seller->user->email)->send(new \App\Mail\SellerRejectedMail($seller, $request->reason));

        return response()->json(['message' => 'Đã từ chối seller và gửi email thông báo.']);
    }
}
