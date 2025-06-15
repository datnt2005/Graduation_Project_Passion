<template>
    <div class="flex min-h-screen bg-gray-100 justify-center py-8">
        <div class="flex bg-white rounded-lg shadow-xl max-w-6xl w-full overflow-hidden">
            <main class="flex-1 p-8 overflow-y-hidden">
                <div class="min-h-full max-w-6xl mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-8">
                            <!-- Error message -->
                            <div v-if="error"
                                class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
                                {{ error }}
                            </div>

                            <!-- Loading state -->
                            <div v-if="loading" class="flex justify-center items-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>

                            <section v-else class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center justify-between">
                                    Chọn hình thức giao hàng
                                    <span class="text-sm font-normal text-red-500">-35K</span>
                                </h3>
                                <div class="space-y-4">
                                    <label
                                        class="flex items-center space-x-3 cursor-pointer p-4 rounded-lg border border-gray-300 hover:border-blue-500 transition-colors duration-200">
                                        <input type="radio" name="shipping-method"
                                            class="form-radio text-blue-600 h-5 w-5" checked>
                                        <span class="text-gray-900 font-medium">Giao tiết kiệm</span>
                                        <span class="text-red-500 ml-auto">-35K</span>
                                    </label>
                                    <div class="p-4 rounded-lg border border-blue-500 bg-blue-50">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <input type="radio" name="shipping-method"
                                                class="form-radio text-blue-600 h-5 w-5" disabled>
                                            <span class="text-blue-800 font-medium">Giao tốc độ 2, trước 19h,
                                                02/06</span>
                                            <span class="ml-auto text-green-600 font-semibold">MIỄN PHÍ</span>
                                        </div>
                                        <div v-for="item in cartItems" :key="item.id"
                                            class="flex items-start text-sm text-gray-700 mb-2">
                                            <img :src="item.productVariant?.thumbnail ? `${mediaBaseUrl}${item.productVariant.thumbnail}` : '/images/default-product.jpg'"
                                                :alt="item.productVariant?.product?.name"
                                                class="w-16 h-16 rounded mr-3 flex-shrink-0">
                                            <div>
                                                <p class="font-semibold mb-1">{{ item.productVariant?.product?.name }}
                                                </p>
                                                <p class="text-gray-600">SL: {{ item.quantity }} <span
                                                        class="ml-2 font-bold text-red-500">{{
                                                        formatPrice(item.price) }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center mb-4">
                                    <input type="checkbox" id="promo-20k"
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <label for="promo-20k" class="ml-2 text-blue-600 font-medium">Đã giảm 20k</label>
                                    <svg class="w-4 h-4 text-blue-600 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <h3 class="text-xl font-bold text-gray-800 mb-4">Chọn hình thức thanh toán</h3>

                                <!-- Loading state -->
                                <div v-if="paymentLoading" class="flex justify-center items-center py-4">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                </div>

                                <!-- Error state -->
                                <div v-else-if="paymentError" class="text-red-600 mb-4">
                                    {{ paymentError }}
                                </div>

                                <!-- Payment methods -->
                                <div v-else class="space-y-4">
                                    <label v-for="method in paymentMethods" :key="method.id"
                                        class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-method" v-model="selectedPaymentMethod"
                                            :value="method.name" class="form-radio text-blue-600 h-5 w-5">
                                        <div class="flex items-center space-x-2">
                                            <img v-if="method.name === 'VNPAY'"
                                                src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Icon-VNPAY-QR.png"
                                                :alt="method.name" class="h-6 w-auto">
                                            <img v-else-if="method.name === 'MOMO'"
                                                src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                                                :alt="method.name" class="h-6 w-auto">
                                            <i v-else-if="method.name === 'COD'"
                                                class="fas fa-money-bill-wave text-green-600"></i>
                                            <span class="text-gray-900">{{ getPaymentMethodLabel(method.name) }}</span>
                                        </div>
                                    </label>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xl font-bold text-gray-800">Ưu đãi thanh toán</h3>
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-600">Chọn để áp dụng</span>
                                        <i class="fas fa-info-circle text-gray-400 ml-2"></i>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template v-for="promotion in promotions" :key="promotion.id">
                                        <div
                                            class="bg-gradient-to-br from-white to-gray-50 p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-300 group">
                                            <div class="flex items-start space-x-4">
                                                <!-- Icon and badge -->
                                                <div class="relative">
                                                    <div
                                                        class="w-12 h-12 flex items-center justify-center bg-blue-50 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                                        <img :src="promotion.icon" :alt="promotion.name"
                                                            class="w-8 h-8">
                                                    </div>
                                                    <div v-if="promotion.badge" :class="getBadgeClass(promotion.badge)"
                                                        class="absolute -top-2 -right-2 px-2 py-1 text-xs text-white rounded-full">
                                                        {{ promotion.badge }}
                                                    </div>
                                                </div>

                                                <!-- Content -->
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between">
                                                        <div>
                                                            <h4 class="font-bold text-gray-800">{{ promotion.name }}
                                                            </h4>
                                                            <p class="text-sm text-gray-600">{{ promotion.description }}
                                                            </p>
                                                        </div>
                                                        <button v-if="!promotion.selected"
                                                            @click="selectPromotion(promotion)"
                                                            class="px-3 py-1 text-sm bg-blue-50 text-blue-600 font-medium rounded hover:bg-blue-100 transition-colors">
                                                            Chọn
                                                        </button>
                                                        <span v-else class="text-green-500">
                                                            <i class="fas fa-check-circle"></i>
                                                        </span>
                                                    </div>

                                                    <!-- Details -->
                                                    <div class="mt-2 flex items-center text-xs text-gray-500 space-x-4">
                                                        <span v-if="promotion.limit">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            {{ promotion.limit }}
                                                        </span>
                                                        <span v-if="promotion.expiry">
                                                            <i class="fas fa-calendar-alt mr-1"></i>
                                                            HSD: {{ formatDate(promotion.expiry) }}
                                                        </span>
                                                    </div>

                                                    <!-- Bank info -->
                                                    <div class="mt-2 flex items-center">
                                                        <img :src="promotion.bankIcon" :alt="promotion.bank"
                                                            class="w-4 h-4 mr-1">
                                                        <span class="text-xs text-gray-600">{{ promotion.bank }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        </div>

                        <div class="lg:col-span-1 space-y-8">
                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Giao tới</h3>
                                    <NuxtLink href="/address" class="text-blue-600 text-sm font-medium">Thay đổi
                                    </NuxtLink>
                                </div>
                                <div v-if="selectedAddress" class="space-y-1 text-sm text-gray-700">
                                    <p class="font-semibold">
                                        {{ selectedAddress.name }} - {{ selectedAddress.phone }}
                                    </p>
                                    <p>
                                        {{ selectedAddress.detail }},
                                        {{ getWardName(selectedAddress.ward_code, selectedAddress.district_id) }},
                                        {{ getDistrictName(selectedAddress.district_id) }},
                                        {{ getProvinceName(selectedAddress.province_id) }}
                                    </p>
                                </div>
                                <div v-else class="text-sm text-gray-700">
                                    <p>Chưa chọn địa chỉ giao hàng</p>
                                    <NuxtLink href="/address" class="text-blue-600">Chọn địa chỉ</NuxtLink>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Khuyến mãi</h3>
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-600 mr-2">Có thể chọn 2</span>

                                        <i class="fas fa-info-circle text-gray-500"></i>
                                    </div>
                                </div>

                                <!-- Loading state -->
                                <div v-if="discountLoading" class="flex justify-center items-center py-4">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                </div>

                                <!-- Error state -->
                                <div v-if="discountError" class="text-red-600 mb-4">
                                    {{ discountError }}
                                </div>

                                <!-- Selected discounts -->
                                <div v-if="selectedDiscounts.length > 0" class="space-y-3 mb-4">
                                    <div v-for="discount in selectedDiscounts" :key="discount.id"
                                        class="flex items-start p-3 border border-blue-200 rounded-lg bg-blue-50">
                                        <div class="relative mr-3">
                                            <div
                                                class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg">
                                                <img src="https://img.icons8.com/color/48/000000/discount.png"
                                                    alt="Discount Icon" class="w-6 h-6">
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="font-medium text-blue-800">{{ discount.name }}</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ discount.discount_type === 'percentage' ?
                                                            `Giảm ${discount.discount_value}%` :
                                                            `Giảm ${formatPrice(discount.discount_value)}đ` }}
                                                        <span v-if="discount.min_order_value" class="ml-1">
                                                            | Đơn tối thiểu {{
                                                            formatPrice(discount.min_order_value) }}đ
                                                        </span>
                                                    </p>
                                                </div>
                                                <button @click="removeDiscount(discount.id)"
                                                    class="text-red-600 hover:text-red-800 p-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Available discounts -->
                                <div v-if="discounts.length > 0" class="space-y-3">
                                    <div v-for="discount in discounts" :key="discount.id"
                                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-sm transition-all duration-300"
                                        :class="{ 'opacity-50': cartTotal < discount.min_order_value }">
                                        <div class="relative mr-3">
                                            <div
                                                class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-lg group-hover:bg-blue-50 transition-colors">
                                                <img src="https://img.icons8.com/color/48/000000/discount.png"
                                                    alt="Discount Icon" class="w-6 h-6">
                                            </div>
                                            <div v-if="discount.usage_limit"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
                                                {{ discount.usage_limit }}
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ discount.name }}</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ discount.discount_type === 'percentage' ?
                                                            `Giảm ${discount.discount_value}%` :
                                                            `Giảm ${formatPrice(discount.discount_value)}đ` }}
                                                        <span v-if="discount.min_order_value" class="ml-1">
                                                            | Đơn tối thiểu {{
                                                            formatPrice(discount.min_order_value) }}đ
                                                        </span>
                                                    </p>
                                                    <div class="mt-1 flex items-center text-xs text-gray-500">
                                                        <i class="fas fa-calendar-alt mr-1"></i>
                                                        HSD: {{ formatDate(discount.end_date) }}
                                                    </div>
                                                </div>
                                                <button @click="applyDiscount(discount)"
                                                    class="px-3 py-1.5 bg-blue-50 text-blue-600 font-medium rounded hover:bg-blue-100 transition-colors"
                                                    :disabled="cartTotal < discount.min_order_value">
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- No discounts available -->
                                <div v-else-if="!discountLoading" class="text-center py-6">
                                    <img src="https://img.icons8.com/color/48/000000/discount.png" alt="No Discounts"
                                        class="w-12 h-12 mx-auto mb-3 opacity-50">
                                    <p class="text-gray-500">Không có mã giảm giá nào khả dụng</p>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center">
                                    <input type="checkbox" id="request-invoice"
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <label for="request-invoice" class="ml-2 text-gray-900 font-medium">Yêu cầu hoá
                                        đơn</label>
                                </div>
                                <p class="text-sm text-gray-500 mt-2 ml-7">Ví dụ: Trading chỉ xuất hoá đơn điện tử</p>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Đơn hàng</h3>
                                    <NuxtLink to="/cart" class="text-blue-600 text-sm font-medium">Thay đổi</NuxtLink>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="item in cartItems" :key="item.id" class="flex items-center">
                                        <span class="text-gray-900 font-medium mr-2">{{ item.quantity }} x</span>
                                        <img :src="item.productVariant?.thumbnail ? `${mediaBaseUrl}${item.productVariant.thumbnail}` : '/images/default-product.jpg'"
                                            :alt="item.productVariant?.product?.name"
                                            class="w-10 h-10 rounded mr-3 flex-shrink-0">
                                        <div>
                                            <p class="text-gray-900">{{ item.productVariant?.product?.name }}</p>
                                        </div>
                                        <span class="ml-auto text-gray-900 font-semibold">{{
                                            formatPrice(item.price) }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2 text-sm">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Tổng tiền hàng:</span>
                                        <span>{{ formattedCartTotal }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-300 pt-3">
                                        <span>Tổng tiền thanh toán:</span>
                                        <span class="text-red-600">{{ formattedFinalTotal }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 italic text-right">
                                        (Giá đã bao gồm VAT)
                                    </p>
                                </div>

                                <div class="mt-6">
                                    <button @click="placeOrder"
                                        class="w-full bg-red-500 text-white py-3 rounded-lg font-bold text-lg hover:bg-red-700 transition duration-200"
                                        :disabled="!cartItems.length || loading">
                                        Đặt hàng
                                    </button>
                                </div>
                            </section>

                            
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Thêm Notification Popup -->
    <ClientOnly>
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="showNotification"
                    class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
                >
                    <div class="flex-shrink-0">
                        <svg
                            class="h-6 w-6 text-green-400"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ notificationMessage }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <button
                            @click="showNotification = false"
                            class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
                        >
                            <svg
                                class="h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useCart } from '~/composables/useCart'
import { usePayment } from '~/composables/usePayment'
import { useDiscount } from '~/composables/useDiscount'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const { cartItems, cartTotal, loading, error, fetchCart } = useCart()
const { paymentMethods, loading: paymentLoading, error: paymentError, fetchPaymentMethods, processPayment } = usePayment()
const { discounts, selectedDiscounts, loading: discountLoading, error: discountError, fetchDiscounts, applyDiscount, removeDiscount, calculateDiscount } = useDiscount()

const selectedAddress = ref(null)
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const selectedPaymentMethod = ref('')
const address_id = route.query.address_id

const config = useRuntimeConfig()
const mediaBaseUrl = config.public.mediaBaseUrl

// Hàm lấy tên tỉnh, huyện, xã
const getProvinceName = (province_id) => {
    const p = provinces.value.find(item => item.ProvinceID == province_id);
    return p ? p.ProvinceName : '';
};

const getDistrictName = (district_id) => {
    const d = districts.value.find(item => item.DistrictID == district_id);
    return d ? d.DistrictName : '';
};

const getWardName = (ward_code, district_id) => {
    const w = wards.value.find(item => item.WardCode == ward_code && item.DistrictID == district_id);
    return w ? w.WardName : '';
};
// Load address đã chọn
const loadSelectedAddress = async () => {
    if (!address_id) return;

    try {
        const res = await axios.get(`http://127.0.0.1:8000/api/address/${address_id}`);
        selectedAddress.value = res.data.data;

        if (selectedAddress.value) {
            await loadProvinces(); // nếu có
            await loadDistricts(selectedAddress.value.province_id);
            await loadWards(selectedAddress.value.district_id);
        }
    } catch (err) {
        console.error('Lỗi lấy địa chỉ đã chọn:', err);
    }
};

// Load tỉnh thành (nếu bạn dùng file JSON hoặc API riêng)
const loadProvinces = async () => {
    try {
        const res = await axios.get('http://127.0.0.1:8000/api/ghn/provinces');
        provinces.value = res.data.data;
    } catch (err) {
        console.error('Không tải được provinces:', err);
    }
};

// Load quận huyện từ GHN API
const loadDistricts = async (province_id) => {
    try {
        const res = await axios.post('http://127.0.0.1:8000/api/ghn/districts', {
            province_id: province_id
        });
        if (Array.isArray(res.data.data)) {
            districts.value = res.data.data;
        } else {
            console.warn(`Không tải được districts cho province_id=${province_id}`);
        }
    } catch (err) {
        console.error(`Lỗi tải districts với province_id=${province_id}:`, err);
    }
};

// Load xã phường từ GHN API
const loadWards = async (district_id) => {
    try {
        const res = await axios.post('http://127.0.0.1:8000/api/ghn/wards', {
            district_id: district_id
        });
        if (Array.isArray(res.data.data)) {
            wards.value = res.data.data;
        } else {
            console.warn(`Không tải được wards cho district_id=${district_id}`);
        }
    } catch (err) {
        console.error(`Lỗi tải wards với district_id=${district_id}:`, err);
    }
};

onMounted(() => {
    loadSelectedAddress();
});

// Fetch data on mount
onMounted(async () => {
    console.log('onMounted called vnpay-return')
    console.log('Checkout page mounted')
    console.log('Access Token:', localStorage.getItem('access_token'))
    console.log('API Base URL:', config.public.apiBaseUrl)
    
    try {
        console.log('Fetching data...')
        await Promise.all([
            fetchCart(),
            fetchPaymentMethods(),
            fetchDiscounts(),
            loadSelectedAddress()
        ])
        console.log('Data fetched successfully')
        console.log('Cart Items:', cartItems.value)
        console.log('Payment Methods:', paymentMethods.value)
        console.log('Discounts:', discounts.value)
        console.log('Selected Address:', selectedAddress.value)
    } catch (err) {
        console.error('Error fetching data:', err)
    }
})

const promotions = ref([
    {
    }
]);

const getBadgeClass = (badge) => {
    const classes = {
        'Hot': 'bg-red-500',
        'New': 'bg-green-500',
        'Best': 'bg-yellow-500',
        'VIP': 'bg-purple-500'
    }
    return classes[badge] || 'bg-gray-500'
}

const selectPromotion = (promotion) => {
    const selectedCount = promotions.value.filter(p => p.selected).length
    if (selectedCount >= 2 && !promotion.selected) {
        alert('Chỉ được chọn tối đa 2 ưu đãi')
        return
    }
    promotion.selected = !promotion.selected
}

const formatPrice = (price) => {
    if (!price) return '0'
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

// Calculate final total with discounts
const finalTotal = computed(() => {
    const couponDiscount = calculateDiscount(cartTotal.value || 0)
    const total = Math.max(0, (cartTotal.value || 0) - couponDiscount)
    return total
})

// Format price for display
const formattedFinalTotal = computed(() => {
    return formatPrice(finalTotal.value) + ' đ'
})

// Format price for display
const formattedCartTotal = computed(() => {
    return formatPrice(cartTotal.value) + ' đ'
})

// Get payment method display label
const getPaymentMethodLabel = (methodName) => {
    switch (methodName) {
        case 'COD':
            return 'Thanh toán khi nhận hàng'
        case 'VNPAY':
            return 'Thanh toán qua VNPAY'
        case 'MOMO':
            return 'Thanh toán qua Ví MoMo'
        default:
            return methodName
    }
}

const showNotification = ref(false);
const notificationMessage = ref('');

// Hàm hiển thị notification
const showSuccessNotification = (message) => {
  notificationMessage.value = message;
  showNotification.value = true;
  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

const placeOrder = async () => {
    if (!cartItems.value?.length) {
        showSuccessNotification('Giỏ hàng trống');
        return;
    }

    if (!selectedPaymentMethod.value) {
        showSuccessNotification('Vui lòng chọn phương thức thanh toán');
        return;
    }

    try {
        loading.value = true;
        error.value = null;

        const token = localStorage.getItem('access_token')
        if (!token) {
            navigateTo('/auth/login')
            return
        }

        // Fetch user info first
        const userResponse = await fetch(`${config.public.apiBaseUrl}/me`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })

        if (!userResponse.ok) {
            throw new Error('Không thể lấy thông tin người dùng')
        }

        const { data: userData } = await userResponse.json()
        
        if (!userData?.id) {
            throw new Error('Không tìm thấy thông tin người dùng')
        }

        // Prepare order data with minimal object structure
        const orderData = {
            user_id: userData.id,
            address_id: 1,
            payment_method: selectedPaymentMethod.value,
            discount_id: selectedDiscounts.value?.[0]?.id || null,
            items: cartItems.value.map(item => ({
                product_id: item.productVariant?.product?.id,
                product_variant_id: item.product_variant_id,
                quantity: item.quantity,
                price: item.price
            }))
        }

        const orderResponse = await fetch(`${config.public.apiBaseUrl}/orders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(orderData)
        })

        if (!orderResponse.ok) {
            const errorData = await orderResponse.json();
            throw new Error(errorData.message || 'Lỗi khi tạo đơn hàng');
        }

        const { data: orderResult } = await orderResponse.json()

        // Process payment based on selected method
        if (selectedPaymentMethod.value === 'COD') {
            navigateTo(`/order-success/${orderResult.id}`);
        } else {
            const { url } = await processPayment(orderResult.id, selectedPaymentMethod.value);
            if (url) {
                window.location.href = url;
            } else {
                throw new Error('Không nhận được URL thanh toán');
            }
        }
    } catch (err) {
        console.error('Order placement error:', err);
        error.value = err.message || 'Có lỗi xảy ra khi đặt hàng';
        showSuccessNotification(error.value);
    } finally {
        loading.value = false;
    }
}

// Cleanup function
onUnmounted(() => {
    error.value = null
    loading.value = false
    selectedPaymentMethod.value = ''
})

const formatDate = (date) => {
    if (!date) return '';
    if (typeof date === 'string' && date.includes('/')) {
        // If date is already in DD/MM/YYYY format, convert to MM/DD/YYYY
        const [day, month, year] = date.split('/');
        return `${month}/${day}/${year}`;
    }
    // For dates from API, format as MM/DD/YYYY
    const d = new Date(date);
    return `${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}/${d.getFullYear()}`;
};
</script>

<style scoped>
.form-radio {
    @apply text-blue-600 focus:ring-blue-500;
}
</style>