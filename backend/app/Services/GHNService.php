<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\OrderItem;
use App\Models\Product;

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
        // Lấy danh sách OrderItem của đơn hàng
        $orderItems = OrderItem::where('order_id', $order->id)
            ->with('product', 'productVariant') // Nạp quan hệ với model Product
            ->get();

        // Tạo mảng items cho payload
        $items = [];
        $totalWeight = 0; // Tổng khối lượng (gram)
        $maxLength = 0; // Chiều dài tối đa (cm)
        $maxWidth = 0; // Chiều rộng tối đa (cm)
        $maxHeight = 0; // Chiều cao tối đa (cm)

        foreach ($orderItems as $orderItem) {
            $product = $orderItem->product;
            $variant = $orderItem->productVariant;

            $productName = $product->name ?? "Sản phẩm #" . $orderItem->product_id;
            $productCode = $variant && $variant->sku ? $variant->sku : ($product->sku ?? ("SP" . $orderItem->product_id));
            $quantity = $orderItem->quantity;
            $weight = $product->weight ?? 500; // Mặc định 500g nếu không có khối lượng
            $length = $product->length ?? 20; // Mặc định 20cm
            $width = $product->width ?? 15; // Mặc định 15cm
            $height = $product->height ?? 10; // Mặc định 10cm

            // Thêm sản phẩm vào mảng items
            $items[] = [
                'name' => $productName,
                'code' => $productCode,
                'quantity' => $quantity,
                'weight' => $weight, // Khối lượng của từng sản phẩm (gram)
                'length' => $length, // Chiều dài (cm)
                'width' => $width, // Chiều rộng (cm)
                'height' => $height, // Chiều cao (cm)
            ];

            // Cập nhật tổng khối lượng và kích thước
            $totalWeight += $weight * $quantity;
            $maxLength = max($maxLength, $length);
            $maxWidth = max($maxWidth, $width);
            $maxHeight = max($maxHeight, $height);
        }

        // Nếu không có sản phẩm, thêm một mục mặc định để tránh lỗi
        if (empty($items)) {
            $items[] = [
                'name' => "Đơn hàng #" . $order->id,
                'code' => "ORDER" . $order->id,
                'quantity' => 1,
                'weight' => 500,
                'length' => 20,
                'width' => 15,
                'height' => 10,
            ];
            $totalWeight = 500;
            $maxLength = 20;
            $maxWidth = 15;
            $maxHeight = 10;
        }

        $codAmount = $paymentMethod === 'COD' ? (int) $order->final_price : 0;

        $payload = [
            'shop_id' => $this->shopId,
            'from_district_id' => 3152,
            'service_id' => $serviceId,
            'to_name' => $address->name,
            'to_phone' => $address->phone,
            'to_address' => $address->detail,
            'to_ward_code' => $address->ward_code,
            'to_district_id' => $address->district_id,
            'weight' => $totalWeight,
            'length' => $maxLength,
            'width' => $maxWidth,
            'height' => $maxHeight,
            'cod_amount' => $codAmount,
            'payment_type_id' => 2, // 2: shop trả phí ship
            'required_note' => 'CHOXEMHANGKHONGTHU',
            'items' => $items,
        ];

        // Ghi log payload gửi đi
        Log::info('GHN Payload:', $payload);

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/v2/shipping-order/create", $payload);

        // Ghi log status code và response body từ GHN
        Log::info('GHN URL:', ["{$this->baseUrl}/v2/shipping-order/create"]);
        Log::info('GHN Response:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new \Exception("Tạo đơn GHN thất bại: " . $response->body());
        }

        return $response->json()['data'];
    }
}
