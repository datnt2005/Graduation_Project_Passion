<?php

namespace App\Http\Controllers;

use App\Models\GhnSyncLog;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    public function ghnSyncLogs(Request $request)
{
    try {
        $user = Auth::user();
        $query = GhnSyncLog::with('order')->orderBy('created_at', 'desc');

        if ($user->role === 'seller') {
            $seller = Seller::where('user_id', $user->id)->firstOrFail();
            $query->whereHas('order', function ($q) use ($seller) {
                $q->where('shop_id', $seller->id);
            });
        }

        $logs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'order_id' => $log->order_id,
                    'tracking_code' => $log->tracking_code,
                    'ghn_status' => $log->ghn_status,
                    'success' => $log->success,
                    'message' => $log->message,
                    'created_at' => $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : null,
                ];
            })
        ], 200);
    } catch (\Exception $e) {
        Log::error('GHN sync logs error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy nhật ký đồng bộ GHN',
            'error' => $e->getMessage()
        ], 500);
    }
}
}