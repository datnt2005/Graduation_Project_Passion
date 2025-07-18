<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\GHNService;
use Illuminate\Support\Facades\Log;
use App\Models\Seller;


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
    $data = $request->validate([
        'seller_id' => 'required|integer',
        'from_district_id' => 'required|integer',
        'to_district_id' => 'required|integer',
    ]);

    try {
        $seller = Seller::find($data['seller_id']);
        if (!$seller) {
            Log::error("Seller not found: {$data['seller_id']}");
            return response()->json(['message' => "Không tìm thấy thông tin cửa hàng với seller_id: {$data['seller_id']}"], 404);
        }

        // Sử dụng district_id từ thông tin seller thay vì ép cứng
        $data['from_district_id'] = $seller->district_id;

        $services = $this->ghnService->getServices($data['seller_id'], $data['from_district_id'], $data['to_district_id']);
        Log::info('GHN getServices Response: ' . json_encode($services), ['payload' => $data]);
        return response()->json(['data' => $services['data']], 200);
    } catch (\Exception $e) {
        Log::error('GHN getServices Error: ' . $e->getMessage(), ['data' => $data]);
        return response()->json(['message' => 'Không thể lấy danh sách dịch vụ'], 500);
    }
}

   public function calculateFee(Request $request)
    {
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

        try {
            // Kiểm tra service_id hợp lệ
            $services = $this->ghnService->getServices($data['seller_id'], $data['from_district_id'], $data['to_district_id']);
            $validServiceIds = array_column($services['data'], 'service_id');
            if (!in_array($data['service_id'], $validServiceIds)) {
                Log::error("Invalid service_id: {$data['service_id']} for seller {$data['seller_id']}", [
                    'available_services' => $validServiceIds,
                    'data' => $data
                ]);
                return response()->json(['message' => "Dịch vụ vận chuyển service_id {$data['service_id']} không được hỗ trợ"], 400);
            }

            $response = $this->ghnService->calculateFee($data);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('GHN calculateFee Exception: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}