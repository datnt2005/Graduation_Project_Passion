<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Seller;
use Carbon\Carbon;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.ghn.token');
        $this->baseUrl = config('services.ghn.base_url');
        if (empty($this->token) || empty($this->baseUrl)) {
            Log::error('GHN configuration missing', [
                'token' => $this->token,
                'base_url' => $this->baseUrl,
            ]);
            throw new \Exception('Cấu hình GHN không hợp lệ (token hoặc base_url)');
        }
    }

    protected function headers($shopId)
    {
        return [
            'Token' => $this->token,
            'ShopId' => (int) $shopId,
            'Content-Type' => 'application/json',
        ];
    }

    public function getProvinces()
    {
        try {
            $shopId = (int) config('services.ghn.shop_id');
            $response = Http::withHeaders($this->headers($shopId))
                ->get("{$this->baseUrl}/master-data/province");

            if (!$response->successful()) {
                Log::error('GHN API error for provinces:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new \Exception($response->json()['message'] ?? 'Không thể lấy danh sách tỉnh/thành');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('GHN getProvinces Exception:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getDistricts($provinceId)
    {
        try {
            $shopId = (int) config('services.ghn.shop_id');
            $response = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/master-data/district", [
                    'province_id' => (int) $provinceId,
                ]);

            if (!$response->successful()) {
                Log::error('GHN API error for districts:', [
                    'province_id' => $provinceId,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new \Exception($response->json()['message'] ?? 'Không thể lấy danh sách quận/huyện');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('GHN getDistricts Exception:', [
                'error' => $e->getMessage(),
                'province_id' => $provinceId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getWards($districtId)
    {
        try {
            $shopId = (int) config('services.ghn.shop_id');
            $response = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/master-data/ward", [
                    'district_id' => (int) $districtId,
                ]);

            if (!$response->successful()) {
                Log::error('GHN API error for wards:', [
                    'district_id' => $districtId,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new \Exception($response->json()['message'] ?? 'Không thể lấy danh sách phường/xã');
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('GHN getWards Exception:', [
                'error' => $e->getMessage(),
                'district_id' => $districtId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getServices($sellerId, $toDistrictId)
    {
        try {
            $seller = Seller::find($sellerId);
            if (!$seller) {
                Log::error("Seller not found: {$sellerId}");
                throw new \Exception("Không tìm thấy thông tin cửa hàng với seller_id: {$sellerId}");
            }

            $shopId = (int) config('services.ghn.shop_id');
            if (empty($shopId)) {
                Log::error('GHN shop_id is not configured in config/services.php');
                throw new \Exception('Cấu hình shop_id GHN không hợp lệ');
            }

            $fromDistrictId = (int) $seller->district_id;
            if (empty($fromDistrictId)) {
                Log::error("Invalid district_id for seller: {$sellerId}", ['district_id' => $seller->district_id]);
                throw new \Exception("Quận/huyện của cửa hàng {$sellerId} không hợp lệ");
            }

            // Kiểm tra ward_code của seller
            $wardResponse = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/master-data/ward", [
                    'district_id' => $fromDistrictId,
                ]);

            if (!$wardResponse->successful()) {
                Log::error('GHN API error for wards:', [
                    'district_id' => $fromDistrictId,
                    'status' => $wardResponse->status(),
                    'response' => $wardResponse->json(),
                ]);
                throw new \Exception('Không thể lấy danh sách phường/xã từ GHN: ' . ($wardResponse->json()['message'] ?? 'Lỗi không xác định'));
            }

            $wards = $wardResponse->json()['data'] ?? [];
            if (!in_array($seller->ward_id, array_column($wards, 'WardCode'))) {
                Log::error("Invalid ward_id: {$seller->ward_id} for district_id: {$fromDistrictId}", [
                    'seller_id' => $sellerId,
                    'available_wards' => $wards,
                ]);
                throw new \Exception("Mã phường/xã {$seller->ward_id} không hợp lệ cho quận/huyện {$fromDistrictId}");
            }

            Log::info("GHN getServices Payload: seller_id={$sellerId}, shop_id={$shopId}, from_district={$fromDistrictId}, to_district={$toDistrictId}");

            $response = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/v2/shipping-order/available-services", [
                    'shop_id' => $shopId,
                    'from_district' => $fromDistrictId,
                    'to_district' => (int) $toDistrictId,
                ]);

            if (!$response->successful()) {
                Log::error('GHN API error for services:', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'payload' => [
                        'shop_id' => $shopId,
                        'from_district' => $fromDistrictId,
                        'to_district' => $toDistrictId,
                    ],
                ]);
                throw new \Exception($response->json()['message'] ?? 'Không thể lấy danh sách dịch vụ từ GHN');
            }

            $result = $response->json();
            if (empty($result['data'])) {
                Log::error('GHN API returned empty services list', [
                    'seller_id' => $sellerId,
                    'from_district_id' => $fromDistrictId,
                    'to_district_id' => $toDistrictId,
                    'response' => $result,
                ]);
                throw new \Exception('Không có dịch vụ vận chuyển nào khả dụng');
            }

            Log::info('GHN getServices Response:', [
                'data' => $result['data'],
                'payload' => [
                    'seller_id' => $sellerId,
                    'from_district_id' => $fromDistrictId,
                    'to_district_id' => $toDistrictId,
                ],
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('GHN getServices Exception:', [
                'error' => $e->getMessage(),
                'seller_id' => $sellerId,
                'to_district_id' => $toDistrictId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

   public function calculateFee($data)
{
    try {
        $requiredFields = ['seller_id', 'from_district_id', 'from_ward_code', 'to_district_id', 'to_ward_code', 'service_id', 'weight', 'length', 'width', 'height'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                Log::error("Missing or empty required field in GHN calculateFee: {$field}", ['data' => $data]);
                throw new \Exception("Thiếu hoặc không hợp lệ tham số {$field} khi tính phí ship");
            }
        }

        if ($data['weight'] < 50) {
            Log::warning("Invalid weight: {$data['weight']}g. Minimum is 50g.", ['data' => $data]);
            throw new \Exception("Cân nặng không hợp lệ: tối thiểu 50g");
        }

        if ($data['service_id'] == 100039 && $data['weight'] < 2000) {
            Log::warning("Invalid weight for service_id 100039: {$data['weight']}g. Minimum is 2000g.", ['data' => $data]);
            // Lấy danh sách dịch vụ thay thế
            $services = $this->getServices($data['seller_id'], (int) $data['to_district_id']);
            $alternativeServices = array_filter($services['data'] ?? [], fn($s) => in_array($s['service_id'], [53321, 53322]));
            throw new \Exception("Cân nặng không hợp lệ cho dịch vụ Hàng nặng: tối thiểu 2000g", 0, null, [
                'alternative_services' => $alternativeServices
            ]);
        }

        $seller = Seller::find($data['seller_id']);
        if (!$seller) {
            Log::error("Seller not found: {$data['seller_id']}");
            throw new \Exception("Không tìm thấy thông tin cửa hàng với seller_id: {$data['seller_id']}");
        }

        $shopId = (int) config('services.ghn.shop_id');
        if (empty($shopId)) {
            Log::error('GHN shop_id is not configured in config/services.php');
            throw new \Exception('Cấu hình shop_id GHN không hợp lệ');
        }

        $wardResponse = Http::withHeaders($this->headers($shopId))
            ->post("{$this->baseUrl}/master-data/ward", [
                'district_id' => (int) $data['to_district_id'],
            ]);

        if (!$wardResponse->successful()) {
            Log::error('GHN API error for to_ward:', [
                'district_id' => $data['to_district_id'],
                'status' => $wardResponse->status(),
                'response' => $wardResponse->json(),
            ]);
            throw new \Exception("Không thể lấy danh sách phường/xã điểm đến: " . ($wardResponse->json()['message'] ?? 'Lỗi không xác định'));
        }

        $wards = $wardResponse->json()['data'] ?? [];
        if (!in_array($data['to_ward_code'], array_column($wards, 'WardCode'))) {
            Log::error("Invalid to_ward_code: {$data['to_ward_code']} for district_id: {$data['to_district_id']}", [
                'available_wards' => $wards,
                'data' => $data,
            ]);
            throw new \Exception("Mã phường/xã điểm đến {$data['to_ward_code']} không hợp lệ cho quận/huyện {$data['to_district_id']}");
        }

        $services = $this->getServices($data['seller_id'], (int) $data['to_district_id']);
        $validServiceIds = array_column($services['data'] ?? [], 'service_id');
        if (!in_array((int) $data['service_id'], $validServiceIds)) {
            Log::error("Invalid service_id: {$data['service_id']} for seller {$data['seller_id']}", [
                'available_services' => $validServiceIds,
                'data' => $data,
            ]);
            throw new \Exception("Dịch vụ vận chuyển service_id {$data['service_id']} không được hỗ trợ", 0, null, [
                'available_services' => $services['data']
            ]);
        }

        Log::info('GHN calculateFee Payload:', $data);

        $payload = [
            'from_district_id' => (int) $data['from_district_id'],
            'from_ward_code' => (string) $data['from_ward_code'],
            'to_district_id' => (int) $data['to_district_id'],
            'to_ward_code' => (string) $data['to_ward_code'],
            'service_id' => (int) $data['service_id'],
            'weight' => (int) $data['weight'],
            'length' => (int) $data['length'],
            'width' => (int) $data['width'],
            'height' => (int) $data['height'],
            'shop_id' => $shopId,
        ];

        $response = Http::withHeaders($this->headers($shopId))
            ->post("{$this->baseUrl}/v2/shipping-order/fee", $payload);

        if (!$response->successful()) {
            $errorResponse = $response->json();
            Log::error('GHN calculateFee Error:', [
                'status' => $response->status(),
                'response' => $errorResponse,
                'data' => $data,
            ]);
            throw new \Exception($errorResponse['message'] ?? 'Không thể tính phí ship');
        }

        $result = $response->json();
        Log::info('GHN calculateFee Response:', [
            'status' => $response->status(),
            'data' => $result['data'] ?? null,
            'message' => $result['message'] ?? null,
        ]);

        // Đồng bộ với shipping_methods
        $fee = $result['data']['total'] ?? 0;
        if ($fee > 0) {
            ShippingMethod::updateOrCreate(
                ['id' => $data['service_id']],
                [
                    'name' => $data['service_id'] == 53321 ? 'GHN Tiêu chuẩn' : ($data['service_id'] == 53322 ? 'GHN Nhanh' : 'GHN Dịch vụ #' . $data['service_id']),
                    'carrier' => 'GHN',
                    'estimated_days' => $data['service_id'] == 53321 ? 3 : ($data['service_id'] == 53322 ? 2 : 3),
                    'cost' => $fee / 100, // Chuyển sang VND
                    'status' => 'active',
                ]
            );
            Log::info("Đồng bộ shipping_methods với service_id: {$data['service_id']}, cost: {$fee}");
        }

        return array_merge($result, [
            'alternative_services' => $services['data'] // Trả về danh sách dịch vụ khả dụng
        ]);
    } catch (\Exception $e) {
        Log::error('GHN calculateFee Exception:', [
            'error' => $e->getMessage(),
            'data' => $data,
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
    }
}

    public function createShippingOrder($order, $address, $serviceId, $paymentMethod)
    {
        try {
            $orderItems = OrderItem::where('order_id', $order->id)
                ->with('product', 'productVariant')
                ->get();

            if ($orderItems->isEmpty()) {
                Log::error("Order has no items", ['order_id' => $order->id]);
                throw new \Exception("Đơn hàng không có sản phẩm");
            }

            $sellerId = $orderItems->first()->product->seller_id;
            $seller = Seller::find($sellerId);
            if (!$seller) {
                Log::error("Seller not found: {$sellerId}", ['order_id' => $order->id]);
                throw new \Exception("Không tìm thấy thông tin cửa hàng với seller_id: {$sellerId}");
            }

            $shopId = $seller->ghn_shop_id ?? (int) config('services.ghn.shop_id');
            if (empty($shopId)) {
                Log::error('GHN shop_id is not configured', ['seller_id' => $sellerId]);
                throw new \Exception('Cấu hình shop_id GHN không hợp lệ');
            }

            $fromDistrictId = (int) $seller->district_id;
            $fromWardCode = (string) $seller->ward_id;

            // Kiểm tra ward_code của seller
            $wardResponse = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/master-data/ward", [
                    'district_id' => $fromDistrictId,
                ]);

            if (!$wardResponse->successful()) {
                Log::error('GHN API error for wards:', [
                    'district_id' => $fromDistrictId,
                    'status' => $wardResponse->status(),
                    'response' => $wardResponse->json(),
                ]);
                throw new \Exception('Không thể lấy danh sách phường/xã từ GHN: ' . ($wardResponse->json()['message'] ?? 'Lỗi không xác định'));
            }

            $wards = $wardResponse->json()['data'] ?? [];
            if (!in_array($fromWardCode, array_column($wards, 'WardCode'))) {
                Log::error("Invalid ward_id: {$fromWardCode} for district_id: {$fromDistrictId}", [
                    'seller_id' => $sellerId,
                    'available_wards' => $wards,
                ]);
                throw new \Exception("Mã phường/xã {$fromWardCode} không hợp lệ cho quận/huyện {$fromDistrictId}");
            }

            // Kiểm tra to_ward_code
            $toWardResponse = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/master-data/ward", [
                    'district_id' => (int) $address->district_id,
                ]);

            if (!$toWardResponse->successful()) {
                Log::error('GHN API error for to_ward:', [
                    'district_id' => $address->district_id,
                    'status' => $toWardResponse->status(),
                    'response' => $toWardResponse->json(),
                ]);
                throw new \Exception('Không thể lấy danh sách phường/xã điểm đến từ GHN');
            }

            $toWards = $toWardResponse->json()['data'] ?? [];
            if (!in_array($address->ward_code, array_column($toWards, 'WardCode'))) {
                Log::error("Invalid to_ward_code: {$address->ward_code} for district_id: {$address->district_id}", [
                    'order_id' => $order->id,
                    'available_wards' => $toWards,
                ]);
                throw new \Exception("Mã phường/xã điểm đến {$address->ward_code} không hợp lệ");
            }

            // Kiểm tra service_id
            $services = $this->getServices($sellerId, (int) $address->district_id);
            $validServiceIds = array_column($services['data'] ?? [], 'service_id');
            if (!in_array((int) $serviceId, $validServiceIds)) {
                Log::error("Invalid service_id: {$serviceId} for seller {$sellerId}", [
                    'available_services' => $validServiceIds,
                    'order_id' => $order->id,
                ]);
                throw new \Exception("Dịch vụ vận chuyển service_id {$serviceId} không được hỗ trợ");
            }

            $items = [];
            $totalWeight = 0;
            $maxLength = 0;
            $maxWidth = 0;
            $maxHeight = 0;

            foreach ($orderItems as $orderItem) {
                $product = $orderItem->product;
                $variant = $orderItem->productVariant;

                $productName = $product->name ?? "Sản phẩm #" . $orderItem->product_id;
                $productCode = $variant && $variant->sku ? $variant->sku : ($product->sku ?? ("SP" . $orderItem->product_id));
                $quantity = (int) $orderItem->quantity;
                $weight = max(50, (int) ($product->weight ?? 100));
                $length = (int) ($product->length ?? 20);
                $width = (int) ($product->width ?? 15);
                $height = (int) ($product->height ?? 10);

                $items[] = [
                    'name' => $productName,
                    'code' => $productCode,
                    'quantity' => $quantity,
                    'weight' => $weight,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                ];

                $totalWeight += $weight * $quantity;
                $maxLength = max($maxLength, $length);
                $maxWidth = max($maxWidth, $width);
                $maxHeight = max($maxHeight, $height);
            }

            if (empty($items)) {
                Log::warning("No valid items for order, using default", ['order_id' => $order->id]);
                $items[] = [
                    'name' => "Đơn hàng #" . $order->id,
                    'code' => "ORDER" . $order->id,
                    'quantity' => 1,
                    'weight' => 100,
                    'length' => 20,
                    'width' => 15,
                    'height' => 10,
                ];
                $totalWeight = 100;
                $maxLength = 20;
                $maxWidth = 15;
                $maxHeight = 10;
            }

            if ($totalWeight < 50) {
                Log::warning("Total weight too low: {$totalWeight}g. Using 50g.", ['order_id' => $order->id]);
                $totalWeight = 50;
            }

            $codAmount = $paymentMethod === 'COD' ? (int) $order->final_price : 0;

            $payload = [
                'shop_id' => $shopId,
                'from_district_id' => $fromDistrictId,
                'from_ward_code' => (string) $fromWardCode,
                'to_name' => $address->name,
                'to_phone' => $address->phone,
                'to_address' => $address->detail,
                'to_ward_code' => (string) $address->ward_code,
                'to_district_id' => (int) $address->district_id,
                'service_id' => (int) $serviceId,
                'weight' => (int) $totalWeight,
                'length' => (int) $maxLength,
                'width' => (int) $maxWidth,
                'height' => (int) $maxHeight,
                'cod_amount' => $codAmount,
                'payment_type_id' => 2,
                'required_note' => 'CHOXEMHANGKHONGTHU',
                'items' => $items,
            ];

            Log::info('GHN createShippingOrder Payload:', [
                'order_id' => $order->id,
                'payload' => $payload,
            ]);

            $response = Http::withHeaders($this->headers($shopId))
                ->post("{$this->baseUrl}/v2/shipping-order/create", $payload);

            if (!$response->successful()) {
                Log::error('GHN createShippingOrder Error:', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'payload' => $payload,
                ]);
                throw new \Exception($response->json()['message'] ?? 'Tạo đơn GHN thất bại');
            }

            $result = $response->json()['data'];
            if (empty($result['order_code'])) {
                Log::error('GHN response missing order_code', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                ]);
                throw new \Exception('GHN không trả về order_code');
            }

            Log::info('GHN createShippingOrder Success:', [
                'order_id' => $order->id,
                'order_code' => $result['order_code'],
                'expected_delivery_time' => $result['expected_delivery_time'],
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('GHN createShippingOrder Exception:', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}