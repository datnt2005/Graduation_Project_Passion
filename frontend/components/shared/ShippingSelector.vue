<template>
  <section id="shipping-methods" class="bg-white rounded-[4px] p-6 shadow-sm space-y-6">
    <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức giao hàng</h3>

    <div v-if="loadingShipping" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-blue-600"></div>
    </div>

    <div v-if="errorMessage" class="text-red-500 text-xs mt-2">
      {{ errorMessage }}
      <button @click="retryCalculateFees" class="text-blue-500 underline ml-2">Thử lại</button>
      <span v-if="errorMessage.includes('2000g')" class="ml-2 text-gray-600 text-xs">
        (Hoặc liên hệ hỗ trợ để kích hoạt dịch vụ Hàng nhẹ.)
      </span>
    </div>

    <div class="space-y-8">
      <div v-for="shop in localCartItems" :key="shop.seller_id"
        class="border border-gray-300 rounded p-4 bg-white shadow">
        <div class="flex justify-between items-center mb-4">
          <NuxtLink :to="`${shop.store_url}`" class="text-sm font-semibold text-blue-600">
            <i class="fa-solid fa-shop"></i> {{ shop.store_name || 'Cửa hàng' }}
          </NuxtLink>
          <button @click="selectShopDiscount(shop.seller_id)"
            class="text-sm text-blue-500 hover:underline hover:text-blue-700">
            + Chọn mã giảm giá
          </button>
        </div>

        <!-- Danh sách phương thức giao hàng cho từng cửa hàng -->
        <div v-if="shippingMethods[shop.seller_id]?.length" class="mb-4">
          <label class="block text-xs text-gray-600 mb-1">Phương thức giao hàng</label>
          <form class="space-y-2">
            <label v-for="method in shippingMethods[shop.seller_id]" :key="method.service_id"
              class="relative block p-3 border rounded-[4px] cursor-pointer transition hover:border-blue-400"
              :class="{
                'bg-blue-50 border-blue-200': method.service_id === shopServiceIds[shop.seller_id],
                'bg-white border-blue-300': method.service_id !== shopServiceIds[shop.seller_id]
              }">
              <div class="flex items-center gap-3">
                <input class="w-4 h-4 text-blue-600 border-gray-300 accent-blue-600 focus:ring-blue-500"
                  type="radio"
                  :name="'shipping_method_' + shop.seller_id"
                  :value="method.service_id"
                  v-model="shopServiceIds[shop.seller_id]"
                  @change="handleMethodChange(shop.seller_id, method)" />
                <span :class="method.service_id === shopServiceIds[shop.seller_id] ? 'text-[14px] font-semibold' : 'text-[14px]'">
                  {{ method.short_name || 'Dịch vụ GHN' }}
                </span>
              </div>
            </label>
          </form>
        </div>
        <div v-else class="text-red-500 text-xs">
          Không có phương thức giao hàng khả dụng cho cửa hàng này.
        </div>

        <div class="space-y-4">
          <div v-for="item in shop.items" :key="item.id"
            class="flex gap-4 items-center border border-gray-100 rounded-md p-3 bg-gray-50">
            <img
              :src="item.productVariant?.thumbnail ? mediaBaseUrl + item.productVariant.thumbnail : '/images/default-product.jpg'"
              :alt="item.product?.name" class="w-16 h-16 object-cover rounded border" />
            <div class="flex-1">
              <div class="font-medium text-sm">{{ item.product?.name || 'Sản phẩm' }}</div>
              <div class="text-gray-500 text-xs">
                {{ item.productVariant?.attributes?.map(attr => attr.value).join(' - ') || 'Không có thuộc tính' }}
              </div>
              <div class="text-gray-500 text-xs">Số lượng: x{{ item.quantity }}</div>
            </div>
            <div class="text-sm font-semibold text-gray-900 whitespace-nowrap">
              {{ formatPrice(item.sale_price) }}
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between gap-4 mt-4">
          <div class="flex-1">
            <label class="block text-xs text-gray-600 mb-1">Ghi chú cho cửa hàng</label>
            <textarea v-model="shop.note" rows="1"
              class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
              placeholder="Nhập ghi chú cho cửa hàng này..."></textarea>
          </div>
          <div class="text-right text-sm font-semibold text-gray-700 mt-4 space-y-0.5">
            <template v-if="shop.discount > 0">
              <div>Tiền ban đầu: <span class="font-normal">{{ formatPrice(shop.store_total) }}</span></div>
              <div>
                Tiền giảm: <span class="text-green-600 font-normal">-{{ formatPrice(shop.discount) }}</span>
                <button @click="removeDiscount(shop)" class="ml-2 text-red-500 underline text-xs">Huỷ mã</button>
              </div>
              <div>
                Tổng tiền: <span class="text-blue-600 text-base font-bold">{{ formatPrice(shop.store_total -
                  (shop.discount || 0)) }}</span>
              </div>
              <div class="text-xs text-green-600 mt-1">
                <i class="fas fa-ticket-alt mr-1"></i>Đã áp dụng mã giảm giá
              </div>
            </template>
            <template v-else>
              <div>
                Tổng tiền: <span class="text-blue-600 text-base font-bold">{{ formatPrice(shop.store_total) }}</span>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle mr-1"></i>Chưa áp dụng mã giảm giá
              </div>
            </template>
            <div class="text-xs text-gray-700 mt-1">
              Phí vận chuyển: <span class="font-semibold">{{ getShippingFee(shop.seller_id) || 'Đang tính...' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-right text-sm font-semibold text-gray-900 mt-4">
      Tổng tiền vận chuyển: <span id="shipping-fee-display">
        {{ formatPrice(Object.values(fees).filter(f => f !== 'Lỗi').reduce((sum, f) => sum + parsePrice(f), 0)) }}
      </span>
    </div>
    <div v-if="Object.values(fees).includes('Lỗi')" class="text-red-500 text-xs mt-2">
      Có lỗi khi tính phí vận chuyển. Vui lòng thử lại sau.
      <button @click="retryCalculateFees" class="text-blue-500 underline ml-2">Thử lại</button>
    </div>
  </section>

  <Teleport to="body">
    <div v-if="showDiscountPopup" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
      <div class="bg-white rounded-md p-6 w-[90%] max-w-2xl shadow-lg relative">
        <h3 class="text-base font-semibold mb-4 text-gray-800">Chọn mã giảm giá</h3>
        <div class="mb-4 flex items-center gap-2">
          <input v-model="searchCoupon" type="text" placeholder="Tìm kiếm tên hoặc mã..."
            class="border border-gray-300 rounded px-3 py-2 text-sm w-full focus:outline-none focus:ring-1 focus:ring-blue-500" />
          <i class="fas fa-search text-gray-400"></i>
        </div>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <li v-if="loadingCoupons" class="col-span-2">Đang tải mã giảm giá...</li>
          <li v-else-if="filteredVouchersSearched.length === 0" class="col-span-2">Không có mã giảm giá nào</li>
          <li v-for="discount in filteredVouchersSearched" :key="discount.id">
            <div
              class="flex items-center bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-2 gap-3 min-h-[72px]">
              <div class="flex flex-col items-center justify-center min-w-[48px]">
                <div class="bg-blue-100 rounded-lg w-10 h-10 flex items-center justify-center mb-1">
                  <i class="fas fa-ticket-alt text-blue-500 text-lg"></i>
                </div>
                <span v-if="discount.level"
                  class="text-[10px] text-blue-600 bg-blue-50 rounded px-1 py-0.5 font-medium">{{ discount.level
                  }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                  <span class="text-xs font-semibold text-gray-800 truncate">{{ discount.name }}</span>
                  <span v-if="discount.code" class="text-[10px] bg-blue-50 text-blue-700 rounded px-1 ml-1 uppercase">{{
                    discount.code }}</span>
                </div>
                <div class="text-xs font-bold text-blue-600 mb-0.5">{{ getDiscountLabel(discount) }}</div>
                <div class="text-xs text-gray-600 truncate">{{ discount.description }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                  <span v-if="discount.min_order_value">Đơn từ {{ formatPrice(discount.min_order_value) }}</span>
                  <span v-if="discount.end_date" class="ml-2">HSD: {{ (new
                    Date(discount.end_date)).toLocaleDateString('vi-VN') }}</span>
                </div>
              </div>
              <button @click="applyDiscount(discount)"
                class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded px-3 py-1 shadow-sm transition">
                Chọn
              </button>
            </div>
          </li>
        </ul>
        <button @click="showDiscountPopup = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { onMounted, watch, ref, computed } from 'vue';
import { useRuntimeConfig } from '#app';
import { NuxtLink } from '#components';
import Swal from 'sweetalert2';
import { useCheckout } from '~/composables/useCheckout';

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl;
const fees = ref({});
const loadingShipping = ref(false);
const shippingMethods = ref({});
const loadingFees = ref({});
const errorMessage = ref('');

const props = defineProps({
  address: Object,
  cartItems: Array
});

const emit = defineEmits(['update:shippingFee', 'update:shopDiscount', 'update:totalShippingFee']);

const {
  sellerAddresses,
  fetchSellerAddress,
  fetchGHNServiceId,
  fetchDefaultAddress,
  cartItems,
  defaultShippingMethod,
  shopServiceIds,
  calculateShippingFee, // Sử dụng từ useCheckout.js
  shippingFeeCache // Sử dụng cache từ useCheckout.js
} = useCheckout(null, null, props.address, ref({}));

defineExpose({
  fees,
  shopServiceIds
});

const parsePrice = (price) => {
  if (price == null) return 0;
  let clean = String(price).replace(/[^\d.,]/g, '').trim();
  if (clean.includes('.') && clean.includes(',')) {
    clean = clean.split(',')[0].replace(/\./g, '');
  } else if (clean.includes('.')) {
    clean = clean.replace(/\./g, '');
  } else if (clean.includes(',')) {
    clean = clean.replace(/,/g, '');
  }
  const num = parseInt(clean, 10);
  return isNaN(num) ? 0 : num;
};

const formatPrice = (price) => {
  const parsed = parsePrice(price);
  return parsed.toLocaleString('vi-VN', { maximumFractionDigits: 0 }) + ' đ';
};

const calculateTotalWeight = (shop) => {
  const totalWeight = shop.items.reduce((sum, item) => {
    const itemWeight = item.productVariant?.weight || 1000; // Đồng bộ với useCheckout.js
    return sum + itemWeight * item.quantity;
  }, 0);
  console.log(`Total weight for shop ${shop.seller_id}: ${totalWeight}g`);
  return totalWeight;
};

const getShippingFee = (sellerId) => {
  const feeKeys = Object.keys(fees.value);
  const matchingKey = feeKeys.find(key => key.startsWith(`${sellerId}_`) && fees.value[key] !== 'Lỗi');
  return matchingKey ? fees.value[matchingKey] : 'Đang tính...';
};

const calculateAllShippingFees = async () => {
  if (!props.cartItems || !Array.isArray(props.cartItems) || props.cartItems.length === 0) {
    console.error('Không có sản phẩm trong giỏ hàng');
    toast('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm để tính phí vận chuyển.');
    return;
  }

  console.time('calculateAllShippingFees');
  loadingShipping.value = true;
  errorMessage.value = '';
  fees.value = {};

  const sellerAddressPromises = props.cartItems.map(async (shop) => {
    if (!shop.seller_id) {
      console.error('Thiếu seller_id cho shop:', shop);
      fees.value[`${shop.seller_id || 'unknown'}_unknown`] = 'Lỗi';
      errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại sau.';
      return { shop, districtId: null, wardCode: null };
    }

    let districtId = shop.district_id;
    let wardCode = shop.ward_code;

    if (!districtId || !wardCode) {
      console.log(`Thiếu district_id hoặc ward_code cho shop ${shop.seller_id}, kiểm tra cache hoặc lấy từ API...`);
      const cachedAddress = sellerAddresses.value[shop.seller_id];
      if (cachedAddress && cachedAddress.district_id && cachedAddress.ward_code) {
        districtId = cachedAddress.district_id;
        wardCode = cachedAddress.ward_code;
      } else {
        try {
          const sellerAddress = await fetchSellerAddress(shop.seller_id);
          if (!sellerAddress || !sellerAddress.district_id || !sellerAddress.ward_code) {
            fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
            errorMessage.value = `Không thể lấy địa chỉ cho shop ${shop.seller_id}. Vui lòng thử lại sau.`;
            return { shop, districtId: null, wardCode: null };
          }
          districtId = sellerAddress.district_id;
          wardCode = sellerAddress.ward_code;
        } catch (error) {
          console.error(`Lỗi khi lấy địa chỉ cho shop ${shop.seller_id}:`, error);
          fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
          errorMessage.value = `Không thể lấy địa chỉ cho shop ${shop.seller_id}: ${error.message}`;
          return { shop, districtId: null, wardCode: null };
        }
      }

      const shopIndex = localCartItems.value.findIndex(s => s.seller_id === shop.seller_id);
      if (shopIndex !== -1) {
        localCartItems.value[shopIndex] = {
          ...localCartItems.value[shopIndex],
          district_id: districtId,
          ward_code: wardCode,
        };
      } else {
        console.warn(`Không tìm thấy shop ${shop.seller_id} trong localCartItems`);
      }
    }

    return { shop, districtId, wardCode };
  });

  const shopAddresses = await Promise.all(sellerAddressPromises);

  const shippingPromises = shopAddresses.map(async ({ shop, districtId, wardCode }) => {
    if (!districtId || !wardCode) {
      loadingFees.value[shop.seller_id] = false;
      return;
    }

    if (!props.address?.district_id) {
      console.warn(`Thiếu district_id trong địa chỉ người nhận cho shop ${shop.seller_id}`);
      fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
      errorMessage.value = 'Vui lòng chọn địa chỉ giao hàng hợp lệ.';
      loadingFees.value[shop.seller_id] = false;
      return;
    }

    loadingFees.value[shop.seller_id] = true;

    try {
      const totalWeight = calculateTotalWeight(shop);
      const services = await fetchGHNServiceId(shop.seller_id, districtId, props.address.district_id);
      console.log(`Dịch vụ vận chuyển cho shop ${shop.seller_id}:`, JSON.stringify(services, null, 2));

      if (!services || !Array.isArray(services) || services.length === 0) {
        fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
        errorMessage.value = 'Không có dịch vụ vận chuyển khả dụng. Vui lòng liên hệ hỗ trợ.';
        loadingFees.value[shop.seller_id] = false;
        return;
      }

      shippingMethods.value[shop.seller_id] = services;

      let effectiveMethod = services.find(m => m.service_id === shopServiceIds.value[shop.seller_id]);
      if (!effectiveMethod) {
        effectiveMethod = services.find(m => [53321, 53322].includes(m.service_id)) || services[0];
        shopServiceIds.value[shop.seller_id] = effectiveMethod.service_id;
      }

      if (effectiveMethod.service_id === 100039 && totalWeight < 2000) {
        console.warn(`Dịch vụ Hàng nặng không hợp lệ cho trọng lượng ${totalWeight}g`);
        effectiveMethod = services.find(m => m.service_id !== 100039) || services[0];
        shopServiceIds.value[shop.seller_id] = effectiveMethod.service_id;
      }

      // Sử dụng calculateShippingFee từ useCheckout.js
      const { fee } = await calculateShippingFee(shop.seller_id, {
        district_id: districtId,
        ward_code: wardCode
      }, props.address);

      if (fee !== null && !isNaN(fee)) {
        fees.value[`${shop.seller_id}_${effectiveMethod.service_id}`] = formatPrice(fee);
        emit('update:shippingFee', { sellerId: shop.seller_id, fee });
      } else {
        fees.value[`${shop.seller_id}_${effectiveMethod.service_id}`] = 'Lỗi';
        errorMessage.value = `Không thể tính phí vận chuyển cho shop ${shop.seller_id} với dịch vụ ${effectiveMethod.service_id}.`;
      }
    } catch (error) {
      console.error(`Lỗi tính phí vận chuyển cho shop ${shop.seller_id}:`, error);
      fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
      errorMessage.value = `Lỗi tính phí cho shop ${shop.seller_id}: ${error.message || 'Không xác định'}.`;
    } finally {
      loadingFees.value[shop.seller_id] = false;
    }
  });

  await Promise.all(shippingPromises);

  const totalShippingFee = Object.values(fees.value)
    .filter(f => f !== 'Lỗi' && !isNaN(parsePrice(f)))
    .reduce((sum, f) => sum + parsePrice(f), 0);
  console.log(`Tổng phí vận chuyển: ${totalShippingFee}`);
  emit('update:totalShippingFee', totalShippingFee);

  if (Object.values(fees.value).some(f => f === 'Lỗi')) {
    toast('error', errorMessage.value || 'Có lỗi xảy ra khi tính phí vận chuyển.');
  }

  loadingShipping.value = false;
  console.timeEnd('calculateAllShippingFees');
};

const userVouchers = ref([]);
const loadingCoupons = ref(false);
const showDiscountPopup = ref(false);
const selectedSellerId = ref(null);
const searchCoupon = ref('');

const fetchUserVouchers = async () => {
  loadingCoupons.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBase}/discounts/my-vouchers`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    const data = await res.json();
    userVouchers.value = data.data || [];
  } catch (e) {
    userVouchers.value = [];
    toast('error', 'Không thể tải danh sách mã giảm giá.');
  }
  loadingCoupons.value = false;
};

const filteredVouchers = computed(() => {
  if (!selectedSellerId.value) return [];
  return userVouchers.value.filter(v => String(v.seller_id) === String(selectedSellerId.value));
});

const filteredVouchersSearched = computed(() => {
  let arr = filteredVouchers.value;
  if (searchCoupon.value) {
    const keyword = searchCoupon.value.toLowerCase();
    arr = arr.filter(v =>
      (v.name && v.name.toLowerCase().includes(keyword)) ||
      (v.code && v.code.toLowerCase().includes(keyword))
    );
  }
  const seen = new Set();
  return arr.filter(v => {
    if (seen.has(v.id)) return false;
    seen.add(v.id);
    return true;
  });
});

const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    width: '350px',
    padding: '10px 20px',
    customClass: { popup: 'text-sm rounded-md shadow-md' },
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toastEl) => {
      toastEl.addEventListener('mouseenter', () => Swal.stopTimer());
      toastEl.addEventListener('mouseleave', () => Swal.resumeTimer());
    },
  });
};

const applyDiscount = (discount) => {
  const shop = localCartItems.value.find(shop => String(shop.seller_id) === String(selectedSellerId.value));
  if (shop) {
    shop.selectedDiscountId = discount.id;
    let discountAmount = 0;
    if (typeof discount.discount_value === 'number' || !isNaN(Number(discount.discount_value))) {
      discountAmount = Number(discount.discount_value);
      shop.discount = discountAmount;
    } else if (typeof discount.discount_value === 'string' && discount.discount_value.endsWith('%')) {
      const percent = parseFloat(discount.discount_value);
      const total = shop.items.reduce((sum, item) => sum + item.sale_price * item.quantity, 0);
      discountAmount = Math.floor((percent / 100) * total);
      shop.discount = discountAmount;
    }

    emit('update:shopDiscount', {
      sellerId: selectedSellerId.value,
      discount: discountAmount,
      discountId: discount.id
    });
    toast('success', `Đã áp dụng mã giảm giá cho ${shop.store_name || 'cửa hàng'}`);
  }
  showDiscountPopup.value = false;
  selectedSellerId.value = null;
};

const selectShopDiscount = (sellerId) => {
  selectedSellerId.value = sellerId;
  showDiscountPopup.value = true;
};

const handleMethodChange = async (sellerId, method) => {
  const shop = localCartItems.value.find(s => s.seller_id === sellerId);
  if (!shop) {
    toast('error', `Không tìm thấy cửa hàng ${sellerId}.`);
    return;
  }

  if (!method) {
    toast('error', `Phương thức vận chuyển không hợp lệ cho cửa hàng ${sellerId}.`);
    return;
  }

  shopServiceIds.value[sellerId] = method.service_id;
  shop.service_id = method.service_id; // Đồng bộ service_id vào shop
  loadingFees.value[sellerId] = true;

  const fee = await calculateShippingFee(shop, method);
  if (fee !== null && !isNaN(fee)) {
    fees.value[`${sellerId}_${method.service_id}`] = formatPrice(fee);
    shop.shipping_fee = fee; // Đồng bộ phí vận chuyển vào shop
    emit('update:shippingFee', { sellerId, fee });
  } else {
    fees.value[`${sellerId}_${method.service_id}`] = 'Lỗi';
    errorMessage.value = `Không thể tính phí cho cửa hàng ${sellerId}.`;
    shop.shipping_fee = 0;
  }

  loadingFees.value[sellerId] = false;

  const totalShippingFee = Object.values(fees.value)
    .filter(f => f !== 'Lỗi')
    .reduce((sum, f) => sum + parsePrice(f), 0);
  console.log(`Cập nhật tổng phí vận chuyển: ${totalShippingFee}`);
  emit('update:totalShippingFee', totalShippingFee);
};

const getDiscountLabel = (discount) => {
  if (discount.discount_type === 'percentage') {
    let label = `Giảm ${discount.discount_value}%`;
    if (discount.max_discount_value) {
      label += ` tối đa ${formatPrice(discount.max_discount_value)}`;
    }
    return label;
  }
  if (discount.discount_type === 'fixed') {
    return `Giảm ${formatPrice(discount.discount_value)}`;
  }
  if (discount.discount_type === 'shipping_fee') {
    return `Giảm phí ship ${formatPrice(discount.discount_value)}`;
  }
  return '';
};

const removeDiscount = (shop) => {
  shop.discount = 0;
  shop.selectedDiscountId = null;
  emit('update:shopDiscount', {
    sellerId: shop.seller_id,
    discount: 0,
    discountId: null
  });
  toast('success', `Đã hủy mã giảm giá cho ${shop.store_name || 'cửa hàng'}`);
};

const localCartItems = ref([]);
watch(() => props.cartItems, (val) => {
  localCartItems.value = val.map(shop => ({
    ...shop,
    discount: shop.discount || 0,
    selectedDiscountId: shop.selectedDiscountId || null,
    store_name: shop.store_name || 'Cửa hàng',
    store_url: shop.store_url || '#',
    items: shop.items || [],
    district_id: shop.district_id || null,
    ward_code: shop.ward_code || null,
    note: shop.note || '',
    shipping_fee: shop.shipping_fee || 0,
    service_id: shop.service_id || null
  }));
}, { immediate: true, deep: true });

watch(() => props.address, async (newVal) => {
  if (newVal && newVal.district_id && newVal.ward_code) {
    await calculateAllShippingFees();
  }
}, { immediate: false });

onMounted(async () => {
  console.log('Địa chỉ ban đầu:', props.address);
  if (props.address && props.address.district_id && props.address.ward_code) {
    await calculateAllShippingFees();
  }
  await fetchUserVouchers();
});
</script>