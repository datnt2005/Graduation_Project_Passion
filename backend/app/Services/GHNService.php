<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GHNService
{
    protected $token;
    protected $shopId;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.ghn.token');
        $this->shopId = config('services.ghn.shop_id');
        $this->baseUrl = config('services.ghn.base_url');
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
        return Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/master-data/province")
            ->json();
    }

    public function getDistricts($provinceId)
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/master-data/district", [
                'province_id' => $provinceId
            ])->json();
    }

    public function getWards($districtId)
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/master-data/ward", [
                'district_id' => $districtId
            ])->json();
    }

    public function getServices($toDistrictId)
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/shipping-order/available-services", [
                'shop_id' => $this->shopId,
                'from_district' => 1542, // quận ship hàng (cố định của shop)
                'to_district' => $toDistrictId
            ])->json();
    }

    public function calculateFee($data)
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/shipping-order/fee", $data)
            ->json();
    }
}
