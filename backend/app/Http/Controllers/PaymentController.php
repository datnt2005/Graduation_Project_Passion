<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Inventory;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSuccessMail;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createVNPayPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ], [
            'order_id.required' => 'ID đơn hàng là bắt buộc',
            'order_id.exists' => 'ID đơn hàng không tồn tại'
        ]);

        $order = Order::findOrFail($request->order_id);
        
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost:3000/payment/vnpay-return";
        $vnp_TmnCode = "0RS8EKIA"; // Mã website tại VNPAY
        $vnp_HashSecret = "83L7TQH357797GEBWDRUIDM0OV686MME"; // Chuỗi bí mật

        $vnp_TxnRef = $order->id . '_' . time(); // Mã đơn hàng
        $vnp_OrderInfo = 'Thanh toan don hang ' . $order->id;
        $vnp_OrderType = 'billpayment';
        // Round to nearest integer after multiplying by 100 to avoid floating point issues
        $vnp_Amount = (int)round($order->final_price * 100);
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = request()->ip();
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json([
            'data' => [
                'payment_url' => $vnp_Url
            ]
        ]);
    }

    public function verifyVNPayPayment(Request $request)
{
    \Log::info('VNPAY Verify Request:', $request->all());
    
    $vnp_HashSecret = "83L7TQH357797GEBWDRUIDM0OV686MME";
    $inputData = array();
    
    $returnData = $request->input('query_params', []);
    
    foreach ($returnData as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    if (empty($inputData)) {
        return response()->json([
            'message' => 'Không nhận được dữ liệu từ VNPAY',
        ], 400);
    }

    $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $hashData = "";
    $i = 0;
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    $vnpTranId = $returnData['vnp_TransactionNo'] ?? '';
    $vnp_BankCode = $returnData['vnp_BankCode'] ?? '';
    $vnp_Amount = intval($returnData['vnp_Amount'] ?? 0);
    $vnp_ResponseCode = $returnData['vnp_ResponseCode'] ?? '';
    
    try {
        $vnp_TxnRef = $returnData['vnp_TxnRef'] ?? '';
        if (empty($vnp_TxnRef)) {
            throw new \Exception('Mã đơn hàng không hợp lệ');
        }

        $orderId = explode('_', $vnp_TxnRef)[0];
        
        if ($vnp_SecureHash !== $secureHash) {
            \Log::error('VNPAY Hash Mismatch', [
                'received' => $vnp_SecureHash,
                'calculated' => $secureHash
            ]);
            throw new \Exception('Chữ ký không hợp lệ');
        }

        if ($vnp_ResponseCode === '00') {
            $order = Order::findOrFail($orderId);
            $order->update(['status' => 'processing']);
            
            // Trừ tồn kho
            $inventoryController = new InventoryController();
            if (!$inventoryController->deductInventoryForOrder($order)) {
                \Log::error('Thất bại khi trừ tồn kho cho đơn hàng', ['order_id' => $orderId]);
                throw new \Exception('Lỗi khi cập nhật tồn kho');
            }
            
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new OrderSuccessMail($order));
            }
            if (!empty($order->email)) {
                Mail::to($order->email)->send(new OrderSuccessMail($order));
            }
            
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['name' => 'VNPAY'],
                ['status' => 'active']
            );

            Payment::create([
                'order_id' => $orderId,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $vnp_Amount / 100,
                'transaction_id' => $vnpTranId,
                'status' => 'completed'
            ]);

            \Log::info('VNPAY Payment Success', [
                'order_id' => $orderId,
                'amount' => $vnp_Amount / 100,
                'transaction_id' => $vnpTranId
            ]);

            return response()->json([
                'message' => 'Thanh toán thành công',
                'order_id' => $orderId,
                'transaction_id' => $vnpTranId,
                'amount' => $vnp_Amount / 100,
                'bank_code' => $vnp_BankCode
            ]);
        }

        \Log::warning('VNPAY Payment Failed', [
            'order_id' => $orderId,
            'response_code' => $vnp_ResponseCode
        ]);

        return response()->json([
            'message' => 'Thanh toán thất bại',
            'order_id' => $orderId,
            'response_code' => $vnp_ResponseCode
        ], 400);

    } catch (\Exception $e) {
        \Log::error('VNPAY Payment Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            'order_id' => $orderId ?? null
        ], 500);
    }
}

    public function vnpayReturn(Request $request)
{
    \Log::info('VNPAY Return Request:', $request->all());
    
    $vnp_HashSecret = "83L7TQH357797GEBWDRUIDM0OV686MME";

    $inputData = array();
    $returnData = $request->all();
    
    foreach ($returnData as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    if (empty($inputData)) {
        return response()->json([
            'message' => 'Không nhận được dữ liệu từ VNPAY',
        ], 400);
    }

    $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $hashData = "";
    $i = 0;
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    $vnpTranId = $request->input('vnp_TransactionNo', '');
    $vnp_BankCode = $request->input('vnp_BankCode', '');
    $vnp_Amount = intval($request->input('vnp_Amount', 0));
    $vnp_ResponseCode = $request->input('vnp_ResponseCode', '');
    
    try {
        $vnp_TxnRef = $request->input('vnp_TxnRef', '');
        if (empty($vnp_TxnRef)) {
            throw new \Exception('Mã đơn hàng không hợp lệ');
        }

        $orderId = explode('_', $vnp_TxnRef)[0];
        
        if ($vnp_SecureHash !== $secureHash) {
            \Log::error('VNPAY Hash Mismatch', [
                'received' => $vnp_SecureHash,
                'calculated' => $secureHash
            ]);
            throw new \Exception('Chữ ký không hợp lệ');
        }

        if ($vnp_ResponseCode === '00') {
            $order = Order::findOrFail($orderId);
            $order->update(['status' => 'processing']);
            
            // Trừ tồn kho
            $inventoryController = new InventoryController();
            if (!$inventoryController->deductInventoryForOrder($order)) {
                throw new \Exception('Lỗi khi cập nhật tồn kho');
            }
            
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new OrderSuccessMail($order));
            }

            $paymentMethod = PaymentMethod::firstOrCreate(
                ['name' => 'VNPAY'],
                ['status' => 'active']
            );

            Payment::create([
                'order_id' => $orderId,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $vnp_Amount / 100,
                'transaction_id' => $vnpTranId,
                'status' => 'completed'
            ]);

            if (!empty($order->email)) {
                Mail::to($order->email)->send(new OrderSuccessMail($order));
            }

            \Log::info('VNPAY Payment Success', [
                'order_id' => $orderId,
                'amount' => $vnp_Amount / 100,
                'transaction_id' => $vnpTranId
            ]);

            return response()->json([
                'message' => 'Thanh toán thành công',
                'success' => true,
                'order_id' => $orderId,
                'transaction_id' => $vnpTranId,
                'amount' => $vnp_Amount / 100,
                'bank_code' => $vnp_BankCode
            ]);
        }

        \Log::warning('VNPAY Payment Failed', [
            'order_id' => $orderId,
            'response_code' => $vnp_ResponseCode
        ]);

        return response()->json([
            'message' => 'Thanh toán thất bại',
            'success' => false,
            'order_id' => $orderId,
            'response_code' => $vnp_ResponseCode
        ], 400);

    } catch (\Exception $e) {
        \Log::error('VNPAY Payment Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            'order_id' => $orderId ?? null
        ], 500);
    }
}

    public function createMoMoPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ], [
            'order_id.required' => 'ID đơn hàng là bắt buộc',
            'order_id.exists' => 'ID đơn hàng không tồn tại'
        ]);

        $order = Order::findOrFail($request->order_id);

        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = "MOMO";
        $accessKey = "F8BBA842ECF85";
        $secretKey = "K951B6PE1waDMi640xX08PD3vg6EkVlz";
        $orderInfo = "Thanh toán đơn hàng " . $order->id;
        $amount = (int)($order->final_price); // Convert to integer
        $orderId = time() . ""; // Mã đơn hàng, unique
        $redirectUrl = "http://localhost:3000/payment/momo-return";
        $ipnUrl = "http://localhost:8000/api/payments/momo/ipn";
        $extraData = base64_encode(json_encode(["order_id" => $order->id])); // Mã hóa thông tin đơn hàng
        $requestId = time() . ""; // Request id, unique
        $requestType = "payWithATM";

        // Tạo chữ ký
        $rawSignature = "accessKey=" . $accessKey . 
            "&amount=" . $amount . 
            "&extraData=" . $extraData . 
            "&ipnUrl=" . $ipnUrl . 
            "&orderId=" . $orderId . 
            "&orderInfo=" . $orderInfo . 
            "&partnerCode=" . $partnerCode . 
            "&redirectUrl=" . $redirectUrl . 
            "&requestId=" . $requestId . 
            "&requestType=" . $requestType;

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $data = array(
            'partnerCode' => $partnerCode,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'requestType' => $requestType,
            'extraData' => $extraData,
            'lang' => 'vi',
            'signature' => $signature
        );

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        if (isset($jsonResult['payUrl'])) {
            // Lưu thông tin đơn hàng MoMo vào payment
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['name' => 'MOMO'],
                ['status' => 'active']
            );

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $amount,
                'transaction_id' => $orderId, // Lưu orderId của MoMo
                'status' => 'pending'
            ]);

            return response()->json([
                'data' => [
                    'payment_url' => $jsonResult['payUrl']
                ]
            ]);
        }

        return response()->json([
            'message' => 'Có lỗi xảy ra khi tạo thanh toán MoMo',
            'error' => $jsonResult
        ], 400);
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function momoReturn(Request $request)
{
    $accessKey = "F8BBA842ECF85";
    $secretKey = "K951B6PE1waDMi640xX08PD3vg6EkVlz";
    
    $partnerCode = $request->input('partnerCode');
    $orderId = $request->input('orderId');
    $requestId = $request->input('requestId');
    $amount = $request->input('amount');
    $orderInfo = $request->input('orderInfo');
    $orderType = $request->input('orderType');
    $transId = $request->input('transId');
    $resultCode = $request->input('resultCode');
    $message = $request->input('message');
    $payType = $request->input('payType');
    $responseTime = $request->input('responseTime');
    $extraData = $request->input('extraData');
    $signature = $request->input('signature');

    $decodedExtraData = json_decode(base64_decode($extraData), true);
    $originalOrderId = $decodedExtraData['order_id'] ?? null;

    $rawHash = "accessKey=" . $accessKey .
        "&amount=" . $amount .
        "&extraData=" . $extraData .
        "&message=" . $message .
        "&orderId=" . $orderId .
        "&orderInfo=" . $orderInfo .
        "&orderType=" . $orderType .
        "&partnerCode=" . $partnerCode .
        "&payType=" . $payType .
        "&requestId=" . $requestId .
        "&responseTime=" . $responseTime .
        "&resultCode=" . $resultCode .
        "&transId=" . $transId;

    \Log::info('MoMo Return Data:', [
        'rawHash' => $rawHash,
        'signature' => $signature,
        'request_data' => $request->all()
    ]);

    $calculatedSignature = hash_hmac('sha256', $rawHash, $secretKey);

    try {
        if ($signature !== $calculatedSignature) {
            \Log::error('MoMo Signature Mismatch', [
                'received' => $signature,
                'calculated' => $calculatedSignature
            ]);
            // Vẫn tiếp tục xử lý dù signature không khớp
        }

        if (!$originalOrderId) {
            throw new \Exception('Không tìm thấy ID đơn hàng gốc');
        }

        if ($resultCode == '0') {
            $order = Order::findOrFail($originalOrderId);
            $order->update(['status' => 'processing']);
            
            // Trừ tồn kho
            $inventoryController = new InventoryController();
            if (!$inventoryController->deductInventoryForOrder($order)) {
                \Log::error('Thất bại khi trừ tồn kho cho đơn hàng', ['order_id' => $originalOrderId]);
                throw new \Exception('Lỗi khi cập nhật tồn kho');
            }
            
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new OrderSuccessMail($order));
            }
            $payment = Payment::where('order_id', $originalOrderId)
                ->where('transaction_id', $orderId)
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $transId
                ]);
            }

            \Log::info('MoMo Payment Success', [
                'order_id' => $originalOrderId,
                'amount' => $amount,
                'transaction_id' => $transId
            ]);

            return response()->json([
                'message' => 'Thanh toán thành công',
                'order_id' => $originalOrderId
            ]);
        }

        $payment = Payment::where('order_id', $originalOrderId)
            ->where('transaction_id', $orderId)
            ->first();

        if ($payment) {
            $payment->update([
                'status' => 'failed'
            ]);
        }

        \Log::warning('MoMo Payment Failed', [
            'order_id' => $originalOrderId,
            'result_code' => $resultCode
        ]);

        return response()->json([
            'message' => 'Thanh toán thất bại: ' . $message,
            'order_id' => $originalOrderId
        ], 400);

    } catch (\Exception $e) {
        \Log::error('MoMo Payment Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            'order_id' => $originalOrderId ?? null
        ], 500);
    }
}

    public function momoIPN(Request $request)
    {
        // Xử lý IPN tương tự như momoReturn
        // Nhưng không cần trả về response cho user
        // Chỉ cần trả về response cho MoMo
        return response()->json([
            'message' => 'Received IPN successfully',
            'status' => 'ok'
        ]);
    }
}
