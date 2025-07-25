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
}