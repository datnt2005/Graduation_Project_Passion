<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
            <div v-if="loading" class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                <p class="mt-4 text-gray-600">Đang xử lý kết quả thanh toán...</p>
            </div>

            <div v-else-if="error" class="text-center">
                <div class="text-red-600 text-xl mb-4">
                    <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-xl font-semibold">Thanh toán thất bại</h2>
                </div>
                <p class="text-gray-600 mb-6">{{ error }}</p>
                <button @click="$router.push('/checkout')" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                    Thử lại
                </button>
            </div>

            <div v-else-if="success" class="text-center">
                <div class="text-green-600 text-xl mb-4">
                    <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <h2 class="text-xl font-semibold">Thanh toán thành công</h2>
                </div>
                <p class="text-gray-600 mb-6">Cảm ơn bạn đã mua hàng!</p>
                <div class="space-y-4">
                    <button @click="$router.push('/order-history')" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        Xem đơn hàng
                    </button>
                    <button @click="$router.push('/')" class="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-50">
                        Tiếp tục mua sắm
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'VNPayReturn',
    data() {
        return {
            loading: true,
            error: null,
            success: false
        };
    },
    async created() {
        try {
            // Lấy tất cả query parameters
            const queryParams = new URLSearchParams(window.location.search);
            
            // Gọi API xử lý kết quả thanh toán
            const response = await axios.get(`http://localhost:8000/api/payments/vnpay/return${window.location.search}`);
            
            if (response.data && response.data.message === 'Thanh toán thành công') {
                this.success = true;
                // Có thể lưu order_id vào store hoặc localStorage nếu cần
            } else {
                this.error = 'Thanh toán không thành công. Vui lòng thử lại.';
            }
        } catch (error) {
            console.error('Error processing VNPAY return:', error);
            this.error = error.response?.data?.message || 'Có lỗi xảy ra khi xử lý thanh toán';
        } finally {
            this.loading = false;
        }
    }
};
</script> 