<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GHNController extends Controller
{
    protected $token;
    protected $shopId;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = env('GHN_TOKEN');
        $this->shopId = env('GHN_SHOP_ID');
        $this->baseUrl = env('GHN_API_URL');
    }

    protected function headers()
    {
        return [
            'Token' => $this->token,
            'ShopId' => $this->shopId,
            'Content-Type' => 'application/json'
        ];
    }

    public function getProvinces()
    {
        $cacheKey = 'ghn_provinces';
        $ttl = 86400; // cache 1 ngày

        $data = Cache::remember($cacheKey, $ttl, function () {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/master-data/province");

            return $response->json();
        });

        return response()->json($data);
    }

    public function getDistricts(Request $request)
    {
        $provinceId = (int) $request->input('province_id');
        $cacheKey = 'ghn_districts_' . $provinceId;
        $ttl = 86400;

        $data = Cache::remember($cacheKey, $ttl, function () use ($provinceId) {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/master-data/district", [
                    'province_id' => $provinceId
                ]);

            return $response->json();
        });

        return response()->json($data);
    }

    public function getWards(Request $request)
    {
        $districtId = (int) $request->input('district_id');
        $cacheKey = 'ghn_wards_' . $districtId;
        $ttl = 86400;

        $data = Cache::remember($cacheKey, $ttl, function () use ($districtId) {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/master-data/ward", [
                    'district_id' => $districtId
                ]);

            return $response->json();
        });

        return response()->json($data);
    }

    public function calculateFee(Request $request)
    {
        $data = $request->all();

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/v2/shipping-order/fee', $data);

        return response()->json($response->json());
    }

    public function getServices(Request $request)
    {
        $fromDistrict = (int) $request->input('from_district_id');
        $toDistrict = (int) $request->input('to_district_id');

        $response = Http::withHeaders([
            'Token' => $this->token,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/v2/shipping-order/available-services', [
            "shop_id" => (int)$this->shopId,
            "from_district" => $fromDistrict,
            "to_district" => $toDistrict
        ]);

        return response()->json($response->json());
    }
}
