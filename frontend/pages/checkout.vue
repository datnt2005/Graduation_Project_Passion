<template>
    <div class="flex min-h-screen bg-gray-100 justify-center py-8">
        <div class="flex bg-white rounded-lg shadow-xl max-w-6xl w-full overflow-hidden">
            <main class="flex-1 p-8 overflow-y-auto">
                <div class="min-h-full max-w-6xl mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-8">
                            <section id="shipping-methods"
                                class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <h3 class="text-xl font-bold text-gray-800 mb-4">Chọn hình thức giao hàng</h3>
                                <div class="space-y-4">
                                    <label
                                        class="flex items-center space-x-3 cursor-pointer p-4 rounded-lg border border-gray-300 hover:border-blue-500 transition-colors duration-200">
                                        <input type="radio" name="shipping_method" value="100039" checked
                                            class="form-radio text-blue-600 h-5 w-5 shipping-method-radio">
                                        <span class="text-gray-900 font-medium">GHN Tiết kiệm</span>
                                        <span class="text-green-600 ml-auto font-semibold" id="fee-100039">Đang
                                            tính...</span>
                                    </label>

                                    <label
                                        class="flex items-center space-x-3 cursor-pointer p-4 rounded-lg border border-gray-300 hover:border-blue-500 transition-colors duration-200">
                                        <input type="radio" name="shipping_method" value="53321"
                                            class="form-radio text-blue-600 h-5 w-5 shipping-method-radio">
                                        <span class="text-gray-900 font-medium">GHN Nhanh</span>
                                        <span class="text-green-600 ml-auto font-semibold" id="fee-53321">Đang
                                            tính...</span>
                                    </label>
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
                                <h3 class="text-xl font-bold text-gray-800 mb-4">Ưu đãi thanh toán khác</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Freeship</p>
                                        <p class="text-xs text-gray-600">Thẻ starcard Platinum</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Freeship</p>
                                        <p class="text-xs text-gray-600">Thẻ starcard Standard</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 30K</p>
                                        <p class="text-xs text-gray-600">từ 500K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 20K</p>
                                        <p class="text-xs text-gray-600">từ 200K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 50K</p>
                                        <p class="text-xs text-gray-600">từ 300K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 70K</p>
                                        <p class="text-xs text-gray-600">từ 500K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                </div>

                                <div class="mt-6">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-bank"
                                            class="form-radio text-blue-600 h-5 w-5">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/ed/Bank_of_America_Logo.svg"
                                            alt="The ATM" class="h-6 w-auto">
                                        <span class="text-gray-900">The ATM</span>
                                    </label>
                                    <p class="text-sm text-gray-600 ml-8">Hỗ trợ Internet Banking</p>
                                </div>
                            </section>
                        </div>

                        <div class="lg:col-span-1 space-y-8">
                            <section v-if="selectedAddress"
                                class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Giao tới</h3>
                                    <NuxtLink href="/address" class="text-blue-600 text-sm font-medium">Thay đổi
                                    </NuxtLink>
                                </div>
                                <div class="space-y-1 text-sm text-gray-700">
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
                            </section>
                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Khuyến mãi</h3>
                                    <a href="#" class="text-blue-600 text-sm font-medium">Có thể chọn 2</a>
                                    <svg class="w-4 h-4 text-gray-500 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center p-3 border border-gray-300 rounded-lg">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6sF5z5q0V0f7G6J0F4Z0F7G6J0F4Z0F7G6J0F4Z0F7G6J0F4Z0F7G6J0F4Z0F&s"
                                            alt="XTRA Voucher" class="w-8 h-8 rounded-full mr-3">
                                        <span class="text-gray-900 font-medium">Giảm 70K</span>
                                        <button
                                            class="ml-auto px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-md hover:bg-blue-200">Bỏ
                                            chọn</button>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="apply-promo"
                                            class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                        <label for="apply-promo" class="ml-2 text-blue-600 font-medium">Chọn hoặc nhập
                                            mã khác</label>
                                        <svg class="w-4 h-4 text-blue-600 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500">Thứ ngày: Giảm giá cho đơn hàng bằng ASA</p>

                                    <div class="space-y-3 mt-4">
                                        <div class="flex items-center text-gray-400">
                                            <input type="checkbox" disabled
                                                class="form-checkbox h-5 w-5 rounded cursor-not-allowed">
                                            <span class="ml-2">Chưa áp dụng giảm giá</span>
                                            <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 ml-7">Ví dụ: Chưa đủ số lượng kho</p>

                                        <div class="flex items-center text-gray-400">
                                            <input type="checkbox" disabled
                                                class="form-checkbox h-5 w-5 rounded cursor-not-allowed">
                                            <span class="ml-2">Chưa áp dụng giảm giá</span>
                                            <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 ml-7">Ví dụ: Chỉ đủ điều kiện tối thiểu 1000 xu
                                        </p>
                                    </div>
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
                                    <a href="#" class="text-blue-600 text-sm font-medium">Thay đổi</a>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <span class="text-gray-900 font-medium mr-2">1 x</span>
                                        <img src="https://didongmoi.com.vn/upload/images/product/iphone-15-chinh-hang-cu-2.jpg"
                                            alt="Product Image" class="w-10 h-10 rounded mr-3 flex-shrink-0">
                                        <div>
                                            <p class="text-gray-900">Hộp quà gồm giữ nhiệt Lebentang LBL3038, cà men
                                                dung tích 700ml, c...</p>
                                        </div>
                                        <span class="ml-auto text-gray-900 font-semibold">299.000 đ</span>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2 text-sm">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Tổng tiền hàng:</span>
                                        <span>429.000đ</span>
                                    </div>
                                    <div class="flex justify-between text-gray-700 mt-4">
                                        <span>Phí vận chuyển:</span>
                                        <span id="shipping-fee-display">Đang tính...</span>
                                    </div>
                                    <div class="flex justify-between text-red-600 font-medium">
                                        <span>Giảm giá trực tiếp:</span>
                                        <span>-130.000đ</span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span>Mã khuyến mại từ nhà bán:</span>
                                        <span>-20.000đ</span>
                                    </div>
                                    <div class="flex justify-between text-red-600 font-medium">
                                        <span>Giảm vận chuyển:</span>
                                        <span>-36.500đ</span>
                                    </div>
                                    <div
                                        class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-300 pt-3">
                                        <span>Tổng tiền thanh toán:</span>
                                        <span class="text-red-600">299.000 đ</span>
                                    </div>
                                    <p class="text-xs text-gray-500 italic text-right">
                                        (Giá này đã bao gồm thuế GTGT, phí đóng gói, phí vận chuyển và các chi phí phát
                                        sinh khác)
                                    </p>
                                </div>

                                <div class="mt-6">
                                    <button
                                        class="w-full bg-red-500 text-white py-3 rounded-lg font-bold text-lg hover:bg-red-700 transition duration-200">
                                        Đặt hàng
                                    </button>
                                </div>
                            </section>

                            <div class="flex flex-col space-y-4">
                                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="request-invoice"
                                            class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                        <label for="request-invoice" class="ml-2 text-gray-900 font-medium">Yêu cầu
                                            hoá
                                            đơn</label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2 ml-7">Ví dụ: Trading chỉ xuất hoá đơn điện tử
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useRuntimeConfig } from '#app';

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const route = useRoute();

const selectedAddress = ref(null);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const baseUrl = `http://localhost:8000/api/shipping/calculate-fee`;

const weight = 1000;
const address_id = route.query.address_id;

// Load tỉnh thành
const loadProvinces = async () => {
    try {
        const res = await axios.get(`${apiBase}/ghn/provinces`);
        provinces.value = res.data.data;
    } catch (err) {
        console.error('Không tải được provinces:', err);
    }
};

// Load quận huyện
const loadDistricts = async (province_id) => {
    try {
        const res = await axios.post(`${apiBase}/ghn/districts`, {
            province_id
        });
        districts.value = Array.isArray(res.data.data) ? res.data.data : [];
    } catch (err) {
        console.error(`Lỗi tải districts với province_id=${province_id}:`, err);
    }
};

// Load xã phường
const loadWards = async (district_id) => {
    try {
        const res = await axios.post(`${apiBase}/ghn/wards`, {
            district_id
        });
        wards.value = Array.isArray(res.data.data) ? res.data.data : [];
    } catch (err) {
        console.error(`Lỗi tải wards với district_id=${district_id}:`, err);
    }
};

// Load địa chỉ đã chọn
const loadSelectedAddress = async () => {
    if (!address_id) return;

    try {
        const res = await axios.get(`${apiBase}/address/${address_id}`);
        selectedAddress.value = res.data.data;

        if (selectedAddress.value) {
            await loadProvinces();
            await loadDistricts(selectedAddress.value.province_id);
            await loadWards(selectedAddress.value.district_id);
        }
    } catch (err) {
        console.error('Lỗi lấy địa chỉ đã chọn:', err);
    }
};

// Tính phí vận chuyển cho tất cả phương thức
const calculateAllShippingFees = async () => {
    if (!selectedAddress.value) return;

    const { district_id: to_district_id, ward_code: to_ward_code } = selectedAddress.value;
    const methods = document.querySelectorAll('.shipping-method-radio');

    for (const radio of methods) {
        const service_id = parseInt(radio.value);
        const feeElement = document.getElementById('fee-' + service_id);

        try {
            const res = await fetch(baseUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    from_district_id: 1552,
                    to_district_id,
                    to_ward_code,
                    service_id,
                    weight,
                    height: 20,
                    length: 20,
                    width: 20
                })
            });

            const data = await res.json();
            const fee = data?.data?.total ?? 0;

            radio.dataset.fee = fee;
            if (feeElement) {
                feeElement.textContent = fee.toLocaleString('vi-VN') + 'đ';
            }

            if (radio.checked) {
                const display = document.getElementById('shipping-fee-display');
                if (display) {
                    display.textContent = fee.toLocaleString('vi-VN') + 'đ';
                }
            }
        } catch (err) {
            console.error(`Lỗi tính phí ship cho service_id=${service_id}:`, err);
            if (feeElement) {
                feeElement.textContent = "Lỗi";
            }
        }
    }

    document.querySelectorAll('.shipping-method-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            const fee = parseInt(radio.dataset.fee || '0');
            const display = document.getElementById('shipping-fee-display');
            if (display) {
                display.textContent = fee.toLocaleString('vi-VN') + 'đ';
            }
        });
    });
};

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

onMounted(async () => {
    await loadSelectedAddress();
    await nextTick();
    calculateAllShippingFees();
});

</script>

<style scoped></style>