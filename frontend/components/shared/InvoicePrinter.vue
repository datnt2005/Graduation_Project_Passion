<template>
    <div v-if="selectedOrder"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl w-[600px] p-8 relative">
            <button @click="closeInvoiceModal"
                class="absolute top-2 right-4 text-gray-400 hover:text-black text-lg">✕</button>
            <!-- Khung hóa đơn -->
            <div class="border border-black p-4 text-sm">
                <!-- Header -->
                <div class="flex justify-between items-center border-b border-dashed border-black pb-2 mb-2">
                    <img :src="Logo" alt="" class="w-16 h-16 object-contain mb-2" />
                    <div class="text-right text-xs">
                        <p><b>Mã vận đơn:</b> {{ selectedOrder.tracking_code || '-' }}</p>
                    </div>
                </div>
                <!-- Người bán và Người nhận -->
                <div class="flex justify-between border-b border-dashed border-black pb-2 mb-2">
                    <div class="w-1/2 pr-2 text-xs border-r border-dashed border-black">
                        <p class="font-bold">Từ:</p>
                        <p>Cửa hàng {{ selectedOrder.items[0]?.store_name || '-' }}</p>
                        <p>Địa chỉ: {{ selectedOrder.items[0]?.store_address || '-' }}</p>
                        <p>SĐT: {{ selectedOrder.items[0]?.store_phone || '-' }}</p>
                    </div>

                    <div class="w-1/2 pl-2 text-xs text-xs">
                        <p class="font-bold">Đến:</p>
                        <p>{{ selectedOrder.customer?.name || '-' }}</p>
                        <p>Địa chỉ: {{ getCustomerAddress }}</p>
                        <p>SĐT: {{ selectedOrder.customer?.phone || '-' }}</p>
                    </div>
                </div>
                <div class="flex justify-between border-b border-dashed border-black pb-2 mb-2">
                    <div class="w-3/4 pr-2 text-xs border-r border-dashed border-black">
                        <p class="font-bold mb-1 text-xs">Nội dung hàng (Tổng SL sản phẩm: {{ selectedOrder.items.length
                            }})
                        </p>
                        <ul class="list-disc pl-4 text-xs ">
                            <ul>
                                <li v-for="item in selectedOrder.items" :key="item.id">
                                    {{ item.product_name }}
                                    <span v-if="item.variant?.attributes && item.variant.attributes.length > 0">
                                        -
                                        {{item.variant.attributes.map(attr => `${attr.name}:
                                        ${attr.value.value}`).join(', ')}}
                                    </span>
                                    - SL: {{ item.quantity }}
                                </li>
                            </ul>

                        </ul>
                    </div>

                    <div class="w-1/2 pl-2 text-xs text-xs">
                        <div class="text-center">
                            <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="QR Code" class="w-24 h-24 mx-auto border" />
                            <p class="mt-2"><b>Ngày đặt hàng:</b> {{ selectedOrder.created_at }}</p>

                        </div>
                    </div>
                </div>

                <!-- COD + QR + Ký nhận -->
                <div class="flex justify-between items-start mt-2 text-xs">
                    <div>
                        <p>Tiền thu Người nhận:</p>
                        <p class="text-2xl mt-3 font-bold text-black-600">{{ formatCurrency(selectedOrder.final_price)
                            }}</p>
                    </div>

                    <div class="text-center w-1/3">
                        <p><b>Chữ ký người nhận:</b></p>
                        <p class="italic">Xác nhận hàng nguyên vẹn, không móp/méo, bể/vỡ</p>
                        <div class="mt-8 border-t border-dashed border-black w-full"></div>
                    </div>
                </div>
            </div>

            <!-- Nút In -->
            <div class="text-right mt-4">
                <button @click="printInvoice" class="px-4 py-2 bg-transparent border border-blue-600 text-blue-600 rounded hover:bg-blue-50">In Hóa
                    Đơn</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRuntimeConfig } from '#app';
import { secureFetch } from '@/utils/secureFetch';
import Logo from '@/images/logo.png';

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const props = defineProps({
    orderId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['close']);
const selectedOrder = ref(null);
const qrCodeUrl = ref('');
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const closeInvoiceModal = () => {
    emit('close');
};

const formatCurrency = (value) =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(value || 0));

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    return d.toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getCustomerAddress = computed(() => {
    if (!selectedOrder.value?.customer) return '-';
    return [
        selectedOrder.value.customer.address_detail || '',
        selectedOrder.value.customer.ward_name || '',
        selectedOrder.value.customer.district_name || '',
        selectedOrder.value.customer.province_name || ''
    ].filter(Boolean).join(', ');
});

const loadProvinces = async () => {
    try {
        const res = await fetch(`${apiBase}/ghn/provinces`);
        const data = await res.json();
        provinces.value = Array.isArray(data.data) ? data.data : [];
    } catch (e) {
        console.error('Error loading provinces:', e);
    }
};

const loadDistricts = async (provinceId) => {
    try {
        if (!provinceId) return;
        const res = await fetch(`${apiBase}/ghn/districts`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ province_id: provinceId })
        });
        const data = await res.json();
        districts.value = Array.isArray(data.data) ? data.data : [];
    } catch (e) {
        console.error('Error loading districts:', e);
    }
};

const loadWards = async (districtId) => {
    try {
        if (!districtId) return;
        const res = await fetch(`${apiBase}/ghn/wards`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ district_id: districtId })
        });
        const data = await res.json();
        wards.value = Array.isArray(data.data) ? data.data : [];
    } catch (e) {
        console.error('Error loading wards:', e);
    }
};

const fetchInvoiceData = async () => {
    try {
        const response = await fetch(`${apiBase}/orders/${props.orderId}/print-invoice`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('access_token') || ''}`,
            },
        });
        const data = await response.json();
        if (!data || !data.data) {
            console.error('No data found for invoice');
            return;
        }
        selectedOrder.value = data.data;

        // Ánh xạ tên tỉnh, huyện, xã nếu thiếu
        if (
            selectedOrder.value.customer &&
            (!selectedOrder.value.customer.ward_name ||
                !selectedOrder.value.customer.district_name ||
                !selectedOrder.value.customer.province_name)
        ) {
            await loadProvinces();
            await loadDistricts(selectedOrder.value.customer.province_id);
            await loadWards(selectedOrder.value.customer.district_id);
            const province = provinces.value.find(p => p.ProvinceID == selectedOrder.value.customer.province_id);
            const district = districts.value.find(d => d.DistrictID == selectedOrder.value.customer.district_id);
            const ward = wards.value.find(w => w.WardCode == selectedOrder.value.customer.ward_code);
            selectedOrder.value.customer.province_name = province?.ProvinceName || '';
            selectedOrder.value.customer.district_name = district?.DistrictName || '';
            selectedOrder.value.customer.ward_name = ward?.WardName || '';
        }

        // QR Code
        const trackingCode = selectedOrder.value.tracking_code || `GHN-${Date.now()}`;
        qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(trackingCode)}`;
    } catch (err) {
        console.error('Error fetching invoice:', err);
    }
};

const printInvoice = () => {
    window.print();
};

onMounted(fetchInvoiceData);
</script>