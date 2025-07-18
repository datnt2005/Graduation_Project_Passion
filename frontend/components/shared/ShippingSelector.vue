<template>
  <section id="shipping-methods" class="bg-white rounded-[4px] p-6 shadow-sm space-y-6">
    <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức giao hàng</h3>

    <div v-if="loadingShipping" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-blue-600"></div>
    </div>

    <form v-else-if="filteredShippingMethods.length > 0" class="relative space-y-4 w-2/3">
      <label v-for="method in filteredShippingMethods" :key="method.service_id"
        class="relative block p-4 border rounded-[4px] cursor-pointer transition hover:border-blue-400 accent-blue-60"
        :class="{
          'bg-blue-50 border-blue-200': method.service_id === selectedMethod,
          'bg-white border-blue-300': method.service_id !== selectedMethod
        }">
        <div class="flex items-center gap-3">
          <input class="w-4 h-4 text-[14px] text-blue-600 border-gray-300 accent-blue-600 focus:ring-blue-500"
            type="radio" name="shipping_method" :value="method.service_id" :checked="method.service_id === selectedMethod"
            @change="handleMethodChange(method.service_id)" />
          <span :class="method.service_id === selectedMethod ? 'text-[14px] font-semibold' : 'text-[14px]'">
            {{ method.short_name || 'Dịch vụ GHN' }}
          </span>
          <span class="text-green-600 ml-auto font-semibold" :id="'fee-' + method.service_id">
            {{ fees[`${props.cartItems[0]?.seller_id}_${method.service_id}`] || 'Đang tính...' }}
          </span>
        </div>
      </label>
    </form>
    <div v-else class="text-red-500 text-sm">
      {{ errorMessage || 'Không có dịch vụ vận chuyển khả dụng. Vui lòng kiểm tra địa chỉ hoặc liên hệ hỗ trợ.' }}
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
              Phí vận chuyển: <span class="font-semibold">{{ fees[`${shop.seller_id}_${selectedMethod}`] || 'Đang tính...' }}</span>
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
      Có lỗi khi tính phí vận chuyển. Vui lòng kiểm tra địa chỉ hoặc thử lại sau.
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
  selectedMethod: Number,
  cartItems: Array
});

const emit = defineEmits(['update:selectedMethod', 'update:shippingFee', 'update:shopDiscount']);

// Nhập sellerAddresses và fetchSellerAddress từ useCheckout, sử dụng fetchGHNServiceId thay vì fetchShippingServices
const { sellerAddresses, fetchSellerAddress, fetchGHNServiceId, fetchDefaultAddress } = useCheckout();

const selectedMethod = ref(props.selectedMethod || null);

watch(selectedMethod, (newVal) => {
  emit('update:selectedMethod', newVal);
});

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
    const itemWeight = item.productVariant?.weight || 100;
    console.log(`Item ${item.product?.name || 'Unknown'}: weight=${itemWeight}, quantity=${item.quantity}`);
    return sum + itemWeight * item.quantity;
  }, 0);
  console.log(`Total weight for shop ${shop.seller_id}: ${totalWeight}g`);
  return totalWeight;
};

const filteredShippingMethods = computed(() => {
  const sellerId = props.cartItems[0]?.seller_id;
  if (!sellerId || !shippingMethods.value[sellerId]) return [];
  
  const totalWeight = props.cartItems.reduce((sum, shop) => sum + calculateTotalWeight(shop), 0);
  return shippingMethods.value[sellerId].filter(method => {
    if (method.service_id === 100039 && totalWeight < 2000) {
      return false; // Loại bỏ Hàng nặng nếu cân nặng dưới 2000g
    }
    return true;
  });
});

const calculateShippingFee = async (shop, method) => {
  try {
    const sellerId = shop.seller_id;
    const totalWeight = calculateTotalWeight(shop);

    if (totalWeight < 50) {
      throw new Error('Cân nặng đơn hàng quá thấp. Tối thiểu 50g.');
    }
    if (method.service_id === 100039 && totalWeight < 2000) {
      throw new Error('Cân nặng không hợp lệ cho dịch vụ Hàng nặng: tối thiểu 2000g.');
    }

    console.log(`Kiểm tra shippingMethods cho seller ${sellerId}:`, JSON.stringify(shippingMethods.value[sellerId], null, 2));
    if (!shippingMethods.value[sellerId]?.some(m => m.service_id === method.service_id)) {
      throw new Error(`Dịch vụ vận chuyển ${method.service_id} không được hỗ trợ cho shop ${sellerId}`);
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
      to_district_id: props.address.district_id,
      to_ward_code: props.address.ward_code,
      service_id: method.service_id,
      weight: Math.max(totalWeight, 50),
      height: shop.items.reduce((max, item) => Math.max(max, item.productVariant?.height || 10), 10),
      length: shop.items.reduce((max, item) => Math.max(max, item.productVariant?.length || 30), 30),
      width: shop.items.reduce((max, item) => Math.max(max, item.productVariant?.width || 20), 20),
    };

    console.log('Payload gửi tới /ghn/shipping-fee:', JSON.stringify(payload, null, 2));

    const token = localStorage.getItem('access_token');
    if (!token) {
      throw new Error('Thiếu access token');
    }

    const res = await fetch(`${apiBase}/ghn/shipping-fee`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      const errorData = await res.json().catch(() => ({ message: `Lỗi máy chủ: ${res.status}` }));
      console.error('Phản hồi lỗi từ /ghn/shipping-fee:', JSON.stringify(errorData, null, 2));
      throw new Error(errorData.message || 'Không thể tính phí vận chuyển');
    }

    const data = await res.json();
    const fee = (data?.data?.total ?? 0) / 100;
    fees.value[`${sellerId}_${method.service_id}`] = formatPrice(fee);

    if (method.service_id === selectedMethod.value) {
      emit('update:shippingFee', { sellerId, fee });
    }

    return fee;
  } catch (err) {
    console.error(`Lỗi tính phí vận chuyển cho seller ${shop.seller_id}, method ${method.service_id}:`, err);
    fees.value[`${shop.seller_id}_${method.service_id}`] = 'Lỗi';
    errorMessage.value = err.message.includes('Cân nặng')
      ? err.message
      : `Không thể tính phí vận chuyển cho ${shop.store_name || 'cửa hàng'}. Vui lòng kiểm tra địa chỉ hoặc thử lại.`;
    toast('error', errorMessage.value);
    return null;
  }
};

const calculateAllShippingFees = async () => {
  if (!props.address || !props.address.district_id || !props.address.ward_code) {
    console.error('Thiếu thông tin địa chỉ:', props.address);
    toast('error', 'Vui lòng chọn đầy đủ địa chỉ (quận/huyện và phường/xã) để tính phí vận chuyển.');
    return;
  }

  if (!props.cartItems || props.cartItems.length === 0) {
    console.error('Không có sản phẩm trong giỏ hàng');
    toast('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm để tính phí vận chuyển.');
    return;
  }

  loadingShipping.value = true;
  errorMessage.value = '';

  for (const shop of props.cartItems) {
    if (!shop.seller_id) {
      console.error('Thiếu seller_id cho shop:', shop);
      fees.value[`${shop.seller_id}_${selectedMethod.value}`] = 'Lỗi';
      errorMessage.value = `Không thể tính phí vận chuyển cho ${shop.store_name || 'cửa hàng'}: Thiếu seller_id.`;
      toast('error', errorMessage.value);
      continue;
    }

    let districtId = shop.district_id;
    let wardCode = shop.ward_code;

    if (!districtId || !wardCode) {
      console.log(`Thiếu district_id hoặc ward_code cho shop ${shop.seller_id}, đang lấy từ API...`);
      const sellerAddress = await fetchSellerAddress(shop.seller_id);
      if (!sellerAddress || !sellerAddress.district_id || !sellerAddress.ward_code) {
        fees.value[`${shop.seller_id}_${selectedMethod.value}`] = 'Lỗi';
        errorMessage.value = `Không thể lấy thông tin địa chỉ cho ${shop.store_name || 'cửa hàng'}. Vui lòng kiểm tra thông tin cửa hàng.`;
        toast('error', errorMessage.value);
        loadingFees.value[shop.seller_id] = false;
        continue;
      }
      districtId = sellerAddress.district_id;
      wardCode = sellerAddress.ward_code;
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

    loadingFees.value[shop.seller_id] = true;

    const totalWeight = calculateTotalWeight(shop);
    const services = await fetchGHNServiceId(shop.seller_id, districtId, props.address.district_id);
    if (!services || services.length === 0) {
      fees.value[`${shop.seller_id}_${selectedMethod.value}`] = 'Lỗi';
      errorMessage.value = `Không có dịch vụ vận chuyển khả dụng cho ${shop.store_name || 'cửa hàng'}.`;
      toast('error', errorMessage.value);
      loadingFees.value[shop.seller_id] = false;
      continue;
    }

    shippingMethods.value[shop.seller_id] = services.filter(method => {
      if (method.service_id === 100039 && totalWeight < 2000) {
        return false;
      }
      return true;
    });

    console.log(`Cập nhật shippingMethods cho seller ${shop.seller_id}:`, JSON.stringify(shippingMethods.value[shop.seller_id], null, 2));

    if (shippingMethods.value[shop.seller_id].length === 0) {
      fees.value[`${shop.seller_id}_${selectedMethod.value}`] = 'Lỗi';
      errorMessage.value = `Không có dịch vụ vận chuyển phù hợp cho ${shop.store_name || 'cửa hàng'}.`;
      toast('error', errorMessage.value);
      loadingFees.value[shop.seller_id] = false;
      continue;
    }

    let success = false;
    for (const method of shippingMethods.value[shop.seller_id]) {
      console.log(`Thử dịch vụ ${method.service_id} cho seller ${shop.seller_id}`);
      const fee = await calculateShippingFee({
        ...shop,
        district_id: districtId,
        ward_code: wardCode,
      }, method);
      if (fee !== null) {
        success = true;
        if (!selectedMethod.value) {
          selectedMethod.value = method.service_id;
          emit('update:selectedMethod', method.service_id);
        }
        break;
      } else if (method.service_id === 53321) {
        // Thử dịch vụ khác nếu 53321 thất bại
        console.log(`Dịch vụ 53321 thất bại cho seller ${shop.seller_id}, thử dịch vụ khác...`);
        const alternativeMethod = shippingMethods.value[shop.seller_id].find(m => m.service_id !== 53321);
        if (alternativeMethod) {
          console.log(`Thử dịch vụ thay thế ${alternativeMethod.service_id} cho seller ${shop.seller_id}`);
          const altFee = await calculateShippingFee({
            ...shop,
            district_id: districtId,
            ward_code: wardCode,
          }, alternativeMethod);
          if (altFee !== null) {
            success = true;
            if (!selectedMethod.value) {
              selectedMethod.value = alternativeMethod.service_id;
              emit('update:selectedMethod', alternativeMethod.service_id);
            }
          }
        }
      }
    }

    if (!success) {
      fees.value[`${shop.seller_id}_${selectedMethod.value}`] = 'Lỗi';
      errorMessage.value = `Không thể tính phí vận chuyển cho ${shop.store_name || 'cửa hàng'}. Vui lòng kiểm tra địa chỉ hoặc thử lại.`;
      toast('error', errorMessage.value);
    }

    loadingFees.value[shop.seller_id] = false;
  }

  const display = document.getElementById('shipping-fee-display');
  if (display) {
    const totalShippingFee = Object.values(fees.value)
      .filter(f => f !== 'Lỗi')
      .reduce((sum, f) => sum + parsePrice(f), 0);
    display.textContent = formatPrice(totalShippingFee);
  }

  loadingShipping.value = false;
};

// Coupon logic
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
    position: 'top',
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

const handleMethodChange = (methodId) => {
  if (!filteredShippingMethods.value.some(m => m.service_id === methodId)) {
    toast('error', `Dịch vụ vận chuyển ${methodId} không hợp lệ. Vui lòng chọn dịch vụ khác.`);
    return;
  }
  selectedMethod.value = methodId;
  emit('update:selectedMethod', methodId);
  calculateAllShippingFees();
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
  emit('update:selectedMethod', selectedMethod.value);
  await fetchUserVouchers();
});
</script>