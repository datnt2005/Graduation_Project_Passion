<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\GHNService;
use Illuminate\Support\Facades\Log;
use App\Models\Seller;
use App\Models\Discount;

class GHNController extends Controller
{
    protected $ghnService;
    protected $token;
    protected $baseUrl;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
        $this->token = env('GHN_TOKEN');
        $this->baseUrl = env('GHN_API_URL');
    }

    public function getProvinces()
    {
        return response()->json($this->ghnService->getProvinces());
    }

    public function getDistricts(Request $request)
    {
        $provinceId = (int) $request->input('province_id');
        return response()->json($this->ghnService->getDistricts($provinceId));
    }

    public function getWards(Request $request)
    {
        $districtId = (int) $request->input('district_id');
        return response()->json($this->ghnService->getWards($districtId));
    }

    public function getServices(Request $request)
    {
        try {
            $data = $request->validate([
                'seller_id' => 'required|integer',
                'from_district_id' => 'required|integer',
                'to_district_id' => 'required|integer',
            ]);

            $services = $this->ghnService->getServices($data['seller_id'], $data['to_district_id']);
            return response()->json($services, 200);
        } catch (\Exception $e) {
            Log::error('GHNController getServices Exception:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function calculateFee(Request $request)
    {
        try {
            $data = $request->validate([
                'seller_id' => 'required|integer',
                'from_district_id' => 'required|integer',
                'from_ward_code' => 'required|string',
                'to_district_id' => 'required|integer',
                'to_ward_code' => 'required|string',
                'service_id' => 'required|integer',
                'weight' => 'required|integer|min:50',
                'height' => 'required|integer|min:1',
                'length' => 'required|integer|min:1',
                'width' => 'required|integer|min:1',
            ]);

            $result = $this->ghnService->calculateFee($data);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            Log::error('GHNController calculateFee Exception:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Phương thức mới để tính toán phí vận chuyển với shipping discount
    public function calculateFeeWithDiscount(Request $request)
    {
        try {
            $data = $request->validate([
                'seller_id' => 'required|integer',
                'from_district_id' => 'required|integer',
                'from_ward_code' => 'required|string',
                'to_district_id' => 'required|integer',
                'to_ward_code' => 'required|string',
                'service_id' => 'required|integer',
                'weight' => 'required|integer|min:50',
                'height' => 'required|integer|min:1',
                'length' => 'required|integer|min:1',
                'width' => 'required|integer|min:1',
                'shipping_discount_id' => 'nullable|exists:discounts,id',
            ]);

            // Tính phí vận chuyển gốc
            $result = $this->ghnService->calculateFee($data);
            
            if (!isset($result['data']['total'])) {
                throw new \Exception('Không thể tính phí vận chuyển');
            }

            $originalFee = $result['data']['total'] / 100; // Chuyển từ cent sang VND
            $finalFee = $originalFee;
            $discountAmount = 0;
            $shippingDiscount = null;

            // Áp dụng shipping discount nếu có
            if (!empty($data['shipping_discount_id'])) {
                $shippingDiscount = Discount::where('id', $data['shipping_discount_id'])
                    ->where('discount_type', 'shipping_fee')
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    })
                    ->first();

                if ($shippingDiscount) {
                    // Kiểm tra giới hạn sử dụng
                    if ($shippingDiscount->usage_limit && $shippingDiscount->used_count >= $shippingDiscount->usage_limit) {
                        throw new \Exception('Mã giảm giá vận chuyển đã hết lượt sử dụng');
                    }

                    // Tính toán discount
                    if ($shippingDiscount->discount_type === 'percentage') {
                        $discountAmount = $originalFee * ($shippingDiscount->discount_value / 100);
                    } else {
                        $discountAmount = $shippingDiscount->discount_value;
                    }

                    // Đảm bảo discount không vượt quá phí vận chuyển
                    $discountAmount = min($discountAmount, $originalFee);
                    $finalFee = max(0, $originalFee - $discountAmount);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'original_fee' => $originalFee,
                    'discount_amount' => $discountAmount,
                    'final_fee' => $finalFee,
                    'shipping_discount' => $shippingDiscount ? [
                        'id' => $shippingDiscount->id,
                        'code' => $shippingDiscount->code,
                        'discount_type' => $shippingDiscount->discount_type,
                        'discount_value' => $shippingDiscount->discount_value,
                    ] : null,
                    'ghn_data' => $result['data']
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('GHNController calculateFeeWithDiscount Exception:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}