<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    /**
     * Tạo đơn hàng GHN.
     *
     * @param \App\Models\Order $order
     * @param \App\Models\Address $address
     * @param int $serviceId - ID dịch vụ GHN do frontend gửi về
     * @return array
     * @throws \Exception
     */
    public function createShippingOrder($order, $address, $serviceId, $paymentMethod)
{
    $codAmount = $paymentMethod === 'COD' ? (int) $order->final_price : 0;
    $payload = [
        'shop_id' => $this->shopId,
        'from_district_id' => 3152,
        'service_id' => $serviceId, // từ frontend
        'to_name' => $address->name,
        'to_phone' => $address->phone,
        'to_address' => $address->detail,
        'to_ward_code' => $address->ward_code,
        'to_district_id' => $address->district_id,
        'weight' => 500,
        'length' => 20,
        'width' => 15,
        'height' => 10,
        'cod_amount' => $codAmount,
        'payment_type_id' => 2, // 2: shop trả phí ship
        'required_note' => 'CHOXEMHANGKHONGTHU',
        'items' => [
            [
                'name' => "Đơn hàng #" . $order->id,
                'quantity' => 1
            ]
        ]
    ];

    // Ghi log payload gửi đi
    Log::error('GHN Payload:', $payload);

    $response = Http::withHeaders($this->headers())
    ->post("{$this->baseUrl}/v2/shipping-order/create", $payload);


    // Ghi log status code và response body từ GHN
    Log::error('GHN URL:', ["{$this->baseUrl}/v2/shipping-order/create"]);

    Log::error('GHN Response:', [
        'status' => $response->status(),
        'body' => $response->body(),
    ]);

    if (!$response->successful()) {
        throw new \Exception("Tạo đơn GHN thất bại: " . $response->body());
    }

    return $response->json()['data'];
}

}
