<template>
  <section id="shipping-methods" class="bg-white rounded-[4px] p-6 shadow-sm space-y-6">
    <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức giao hàng</h3>

    <div v-if="loadingShipping" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-blue-600"></div>
    </div>

    <div v-if="errorMessage" class="text-red-500 text-xs mt-2">
      {{ errorMessage }}
      <button @click="retryCalculateFees" class="text-blue-500 underline ml-2">Thử lại</button>
    </div>

    <div v-else class="space-y-6">
      <!-- Phần chọn hình thức giao hàng chung, chỉ hiển thị một phương thức mặc định -->
      <form v-if="defaultShippingMethod" class="relative space-y-4 w-2/3">
        <label
          class="relative block p-4 border rounded-[4px] cursor-pointer transition hover:border-blue-400 accent-blue-60"
          :class="{
            'bg-blue-50 border-blue-200': defaultShippingMethod.service_id === selectedMethod,
            'bg-white border-blue-300': defaultShippingMethod.service_id !== selectedMethod
          }">
          <div class="flex items-center gap-3">
            <input class="w-4 h-4 text-[14px] text-blue-600 border-gray-300 accent-blue-600 focus:ring-blue-500"
              type="radio" name="shipping_method" :value="defaultShippingMethod.service_id"
              :checked="defaultShippingMethod.service_id === selectedMethod"
              @change="handleMethodChange(defaultShippingMethod.service_id)" />
            <span :class="defaultShippingMethod.service_id === selectedMethod ? 'text-[14px] font-semibold' : 'text-[14px]'">
              {{ defaultShippingMethod.short_name || 'Dịch vụ GHN' }}
            </span>
          </div>
        </label>
      </form>
      <div v-if="!defaultShippingMethod" class="text-red-500 text-sm">
        {{ errorMessage || 'Không có dịch vụ vận chuyển khả dụng. Vui lòng liên hệ hỗ trợ.' }}
      </div>
    </div>

    <div class="space-y-8">
      <div v-for="shop in localCartItems" :key="shop.seller_id" class="border border-gray-300 rounded p-4 bg-white shadow">
        <div class="flex justify-between items-center mb-4">
          <NuxtLink :to="`${shop.store_url}`" class="text-sm font-semibold text-blue-600">
            <i class="fa-solid fa-shop"></i> {{ shop.store_name || 'Cửa hàng' }}
          </NuxtLink>
          <button @click="selectShopDiscount(shop.seller_id)"
            class="text-sm text-blue-500 hover:underline hover:text-blue-700">
            + Chọn mã giảm giá
          </button>
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
            <textarea
              v-model="shop.note"
              rows="1"
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
                Tổng tiền: <span class="text-blue-600 text-base font-bold">{{ formatPrice(shop.store_total - (shop.discount || 0)) }}</span>
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
            <div class="flex items-center bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-2 gap-3 min-h-[72px]">
              <div class="flex flex-col items-center justify-center min-w-[48px]">
                <div class="bg-blue-100 rounded-lg w-10 h-10 flex items-center justify-center mb-1">
                  <i class="fas fa-ticket-alt text-blue-500 text-lg"></i>
                </div>
                <span v-if="discount.level" class="text-[10px] text-blue-600 bg-blue-50 rounded px-1 py-0.5 font-medium">{{ discount.level }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                  <span class="text-xs font-semibold text-gray-800 truncate">{{ discount.name }}</span>
                  <span v-if="discount.code" class="text-[10px] bg-blue-50 text-blue-700 rounded px-1 ml-1 uppercase">{{ discount.code }}</span>
                </div>
                <div class="text-xs font-bold text-blue-600 mb-0.5">{{ getDiscountLabel(discount) }}</div>
                <div class="text-xs text-gray-600 truncate">{{ discount.description }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                  <span v-if="discount.min_order_value">Đơn từ {{ formatPrice(discount.min_order_value) }}</span>
                  <span v-if="discount.end_date" class="ml-2">HSD: {{ (new Date(discount.end_date)).toLocaleDateString('vi-VN') }}</span>
                </div>
              </div>
              <button
                @click="applyDiscount(discount)"
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
import { onMounted, watch, ref, computed, nextTick } from 'vue';
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

// Cache lưu trữ phí vận chuyển
const shippingFeeCache = ref(new Map());
const CACHE_TTL = 3600 * 1000; // 1 giờ

const props = defineProps({
  address: Object,
  selectedMethod: [Number, null], // Chỉ cần một giá trị chung hoặc null
  cartItems: Array
});

const emit = defineEmits(['update:selectedMethod', 'update:shippingFee', 'update:shopDiscount', 'update:totalShippingFee']);

const { sellerAddresses, fetchSellerAddress, fetchGHNServiceId, fetchDefaultAddress } = useCheckout();

// Sử dụng selectedMethod thay vì selectedMethods để quản lý một phương thức chung
const selectedMethod = ref(props.selectedMethod || null);

watch(selectedMethod, (newVal) => {
  emit('update:selectedMethod', newVal);
}, { immediate: true });

defineExpose({
  selectedMethod,
  fees
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
    const itemWeight = item.productVariant?.weight || 500; // Trọng lượng mặc định 500g
    return sum + itemWeight * item.quantity;
  }, 0);
  console.log(`Total weight for shop ${shop.seller_id}: ${totalWeight}g`);
  return totalWeight;
};

const getCacheKey = (payload) => {
  return `${payload.seller_id}_${payload.service_id}_${payload.to_district_id}_${payload.to_ward_code}_${payload.weight}_${payload.height}_${payload.length}_${payload.width}`;
};

const getCachedFee = (cacheKey) => {
  const cached = shippingFeeCache.value.get(cacheKey);
  if (cached && cached.timestamp + CACHE_TTL > Date.now()) {
    console.log(`Lấy phí vận chuyển từ cache cho key: ${cacheKey}`);
    return cached.fee;
  }
  shippingFeeCache.value.delete(cacheKey);
  return null;
};

const setCachedFee = (cacheKey, fee) => {
  shippingFeeCache.value.set(cacheKey, {
    fee,
    timestamp: Date.now()
  });
  console.log(`Lưu phí vận chuyển vào cache cho key: ${cacheKey}`);
};

const calculateShippingFee = async (shop, method, retryCount = 0) => {
  const maxRetries = 2;
  try {
    const sellerId = shop.seller_id;
    const totalWeight = calculateTotalWeight(shop);
    console.log(`Tính phí vận chuyển cho shop ${sellerId}, dịch vụ ${method.service_id}, trọng lượng ${totalWeight}g`);

    if (totalWeight < 50) {
      throw new Error('Cân nặng đơn hàng quá thấp. Tối thiểu 50g.');
    }
    if (method.service_id === 100039 && totalWeight < 2000) {
      throw new Error('Cân nặng không hợp lệ cho dịch vụ Hàng nặng: tối thiểu 2000g.');
    }

    if (!shippingMethods.value[sellerId]?.some(m => m.service_id === method.service_id)) {
      console.warn(`Dịch vụ ${method.service_id} không được hỗ trợ cho shop ${sellerId}, thử tìm phương thức khác...`);
      return null; // Trả về null để thử phương thức khác
    }

    const districtId = shop.district_id;
    const wardCode = shop.ward_code;

    if (!districtId || !wardCode) {
      throw new Error(`Thiếu district_id hoặc ward_code cho shop ${sellerId}`);
    }

    const payload = {
      seller_id: sellerId,
      from_district_id: districtId,
      from_ward_code: wardCode,
      to_district_id: props.address?.district_id || 0,
      to_ward_code: props.address?.ward_code || '',
      service_id: method.service_id,
      weight: Math.max(totalWeight, 40),
      height: shop.items.reduce((max, item) => Math.max(max, item.productVariant?.height || 40), 40),
      length: shop.items.reduce((max, item) => Math.max(max, item.productVariant?.length || 40), 40),
      width: shop.items.reduce((max, item) => Math.max(max, item.productVariant?.width || 30), 30),
    };

    const cacheKey = getCacheKey(payload);
    const cachedFee = getCachedFee(cacheKey);
    if (cachedFee !== null) {
      fees.value[`${sellerId}_${method.service_id}`] = formatPrice(cachedFee);
      if (method.service_id === selectedMethod.value) {
        emit('update:shippingFee', { sellerId, fee: cachedFee });
      }
      return cachedFee;
    }

    const token = localStorage.getItem('access_token');
    if (!token) {
      throw new Error('Thiếu access token');
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 20000);
    try {
      const res = await fetch(`${apiBase}/ghn/shipping-fee`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
        signal: controller.signal,
      });
      clearTimeout(timeoutId);

      if (!res.ok) {
        const errorData = await res.json().catch(() => ({ message: `Lỗi máy chủ: ${res.status}` }));
        throw new Error(errorData.message || 'Không thể tính phí vận chuyển');
      }

      const data = await res.json();
      console.log(`Phản hồi từ /ghn/shipping-fee:`, JSON.stringify(data, null, 2));
      const fee = (data?.data?.total ?? 0) / 100;

      if (fee < 1000) {
        throw new Error(`Phí vận chuyển ${fee} VNĐ quá thấp, có thể do lỗi dữ liệu từ API.`);
      }

      setCachedFee(cacheKey, fee);
      fees.value[`${sellerId}_${method.service_id}`] = formatPrice(fee);

      if (method.service_id === selectedMethod.value) {
        emit('update:shippingFee', { sellerId, fee });
      }

      return fee;
    } catch (err) {
      if (err.name === 'AbortError' && retryCount < maxRetries) {
        console.log(`Thử lại lần ${retryCount + 2} cho shop ${sellerId}, dịch vụ ${method.service_id}`);
        return calculateShippingFee(shop, method, retryCount + 1);
      }
      throw err;
    }
  } catch (err) {
    console.error(`Lỗi tính phí vận chuyển cho shop ${shop.seller_id}, dịch vụ ${method.service_id}:`, err.message);
    fees.value[`${shop.seller_id}_${method.service_id}`] = 'Lỗi';
    errorMessage.value = 'Không thể tính phí vận chuyển cho một số cửa hàng. Vui lòng thử lại hoặc chọn phương thức khác.';
    return null;
  }
};

const availableShippingMethods = computed(() => {
  // Lấy giao cắt của các phương thức từ tất cả các cửa hàng
  const methodsMap = new Map();
  localCartItems.value.forEach(shop => {
    if (shippingMethods.value[shop.seller_id]) {
      shippingMethods.value[shop.seller_id].forEach(method => {
        methodsMap.set(method.service_id, method);
      });
    }
  });
  return Array.from(methodsMap.values());
});

const defaultShippingMethod = computed(() => {
  // Lấy phương thức mặc định đầu tiên từ availableShippingMethods
  return availableShippingMethods.value.length > 0 ? availableShippingMethods.value[0] : null;
});

const getShippingFee = (sellerId) => {
  // Tìm key trong fees dựa trên sellerId và phương thức thực tế
  const feeKeys = Object.keys(fees.value);
  const matchingKey = feeKeys.find(key => key.startsWith(`${sellerId}_`) && fees.value[key] !== 'Lỗi');
  return matchingKey ? fees.value[matchingKey] : 'Đang tính...';
};

const calculateAllShippingFees = async () => {
  if (!props.cartItems || props.cartItems.length === 0) {
    console.error('Không có sản phẩm trong giỏ hàng');
    toast('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm để tính phí vận chuyển.');
    return;
  }

  console.time('calculateAllShippingFees');
  loadingShipping.value = true;
  errorMessage.value = '';

  const sellerAddressPromises = props.cartItems.map(async (shop) => {
    if (!shop.seller_id) {
      console.error('Thiếu seller_id cho shop:', shop);
      fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
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
        const sellerAddress = await fetchSellerAddress(shop.seller_id);
        if (!sellerAddress || !sellerAddress.district_id || !sellerAddress.ward_code) {
          fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
          errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại sau.';
          return { shop, districtId: null, wardCode: null };
        }
        districtId = sellerAddress.district_id;
        wardCode = sellerAddress.ward_code;
      }

      const shopIndex = localCartItems.value.findIndex(s => s.seller_id === shop.seller_id);
      if (shopIndex !== -1) {
        localCartItems.value[shopIndex] = {
          ...localCartItems.value[shopIndex],
          district_id: districtId,
          ward_code: wardCode,
        };
      } else {
        localCartItems.value.push({
          ...shop,
          district_id: districtId,
          ward_code: wardCode,
        });
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

    loadingFees.value[shop.seller_id] = true;

    const totalWeight = calculateTotalWeight(shop);
    const services = await fetchGHNServiceId(shop.seller_id, districtId, props.address?.district_id || 0);
    console.log(`Dịch vụ vận chuyển cho shop ${shop.seller_id}:`, JSON.stringify(services, null, 2));

    if (!services || services.length === 0) {
      fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
      errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại sau.';
      loadingFees.value[shop.seller_id] = false;
      return;
    }

    shippingMethods.value[shop.seller_id] = services.filter(method => {
      if (method.service_id === 100039 && totalWeight < 2000) {
        return false;
      }
      return true;
    });

    console.log(`Dịch vụ vận chuyển đã lọc cho shop ${shop.seller_id}:`, JSON.stringify(shippingMethods.value[shop.seller_id], null, 2));

    if (shippingMethods.value[shop.seller_id].length === 0) {
      fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
      errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại sau.';
      loadingFees.value[shop.seller_id] = false;
      return;
    }

    if (!selectedMethod.value && defaultShippingMethod.value) {
      selectedMethod.value = defaultShippingMethod.value.service_id;
      emit('update:selectedMethod', selectedMethod.value);
    }

    // Kiểm tra nếu selectedMethod không hỗ trợ cho shop này, chọn phương thức đầu tiên của shop
    let effectiveMethod = selectedMethod.value;
    if (selectedMethod.value && !shippingMethods.value[shop.seller_id].some(m => m.service_id === selectedMethod.value)) {
      console.warn(`Phương thức ${selectedMethod.value} không hỗ trợ cho shop ${shop.seller_id}, dùng phương thức mặc định ${shippingMethods.value[shop.seller_id][0].service_id}`);
      effectiveMethod = shippingMethods.value[shop.seller_id][0].service_id;
    }

    const fee = await calculateShippingFee({
      ...shop,
      district_id: districtId,
      ward_code: wardCode,
    }, { service_id: effectiveMethod });
    if (fee !== null) {
      fees.value[`${shop.seller_id}_${effectiveMethod}`] = formatPrice(fee);
      emit('update:shippingFee', { sellerId: shop.seller_id, fee });
    } else {
      fees.value[`${shop.seller_id}_${effectiveMethod}`] = 'Lỗi';
      errorMessage.value = 'Không thể tính phí vận chuyển cho một số cửa hàng. Vui lòng thử lại hoặc chọn phương thức khác.';
    }

    loadingFees.value[shop.seller_id] = false;
  });

  await Promise.all(shippingPromises);

  console.log(`Fees sau khi tính toán:`, JSON.stringify(fees.value, null, 2));
  const totalShippingFee = Object.values(fees.value)
    .filter(f => f !== 'Lỗi')
    .reduce((sum, f) => sum + parsePrice(f), 0);
  emit('update:totalShippingFee', totalShippingFee);

  await nextTick();
  const display = document.getElementById('shipping-fee-display');
  if (display) {
    display.textContent = formatPrice(totalShippingFee);
  }

  loadingShipping.value = false;
  console.timeEnd('calculateAllShippingFees');
};

const retryCalculateFees = async () => {
  fees.value = {};
  errorMessage.value = '';
  selectedMethod.value = null;
  shippingFeeCache.value.clear();
  emit('update:selectedMethod', null);
  await calculateAllShippingFees();
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

const handleMethodChange = async (methodId) => {
  if (!availableShippingMethods.some(m => m.service_id === methodId)) {
    toast('error', `Dịch vụ vận chuyển ${methodId} không hợp lệ. Vui lòng chọn dịch vụ khác.`);
    return;
  }
  selectedMethod.value = methodId;
  emit('update:selectedMethod', selectedMethod.value);

  // Tính lại phí vận chuyển cho tất cả các cửa hàng với phương thức mới
  for (const shop of props.cartItems) {
    loadingFees.value[shop.seller_id] = true;
    const fee = await calculateShippingFee(shop, { service_id: methodId });
    if (fee !== null) {
      fees.value[`${shop.seller_id}_${methodId}`] = formatPrice(fee);
      emit('update:shippingFee', { sellerId: shop.seller_id, fee });
    }
    loadingFees.value[shop.seller_id] = false;
  }
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
    note: shop.note || ''
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