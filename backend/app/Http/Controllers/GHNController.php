<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/master-data/province");

        return response()->json($response->json());
    }

    public function getDistricts(Request $request)
    {
        $provinceId = $request->input('province_id');

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/master-data/district", [
                'province_id' => (int) $provinceId
            ]);

        return response()->json($response->json());
    }

    public function getWards(Request $request)
    {
        $districtId = $request->input('district_id');

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/master-data/ward", [
                'district_id' => (int) $districtId
            ]);

        return response()->json($response->json());
    }

    public function calculateFee(Request $request)
    {
        $data = $request->all();

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/shipping-order/fee", $data);

        return response()->json($response->json());
    }
}
