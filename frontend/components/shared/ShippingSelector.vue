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

        <div class="flex items-start justify-between gap-4 mt-4">
          <div class="flex-1">
            <label class="block text-xs text-gray-600 mb-1">Ghi chú cho cửa hàng</label>
            <textarea v-model="shop.note" rows="1"
              class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
              placeholder="Nhập ghi chú cho cửa hàng này..."></textarea>
          </div>
          <div class="text-right text-sm text-gray-700 space-y-2">
            <div class="flex justify-between gap-4">
              <span>Tổng tiền hàng:</span>
              <span class="font-semibold">{{ formatPrice(calculateStoreTotal(shop)) }} đ</span>
            </div>
            <div class="flex justify-between gap-4">
              <span>Phí vận chuyển:</span>
              <span class="font-semibold">
                <span v-if="shop.shipping_discount > 0" class="line-through text-gray-400 mr-1">
                  {{ formatPrice(shop.original_shipping_fee || shop.shipping_fee || 0) }} đ
                </span>
                <span v-if="shop.shipping_fee > 0 || shop.original_shipping_fee > 0">
                  {{ formatPrice(Math.max(0, (shop.original_shipping_fee || shop.shipping_fee || 0) - (shop.shipping_discount || 0))) }} đ
                </span>
                <span v-else class="text-gray-500">
                  {{ loadingFees[shop.seller_id] ? 'Đang tính...' : 'Chưa tính' }}
                </span>
              </span>
            </div>
            <div v-if="shop.discount > 0" class="flex justify-between gap-4">
              <span>Giảm giá sản phẩm:</span>
              <div class="flex items-center gap-2">
                <span class="text-green-600 font-semibold">-{{ formatPrice(shop.discount) }} đ</span>
                <button @click="removeDiscount(shop)" class="text-red-500 text-xs hover:underline">Huỷ</button>
              </div>
            </div>
            <div v-if="shop.admin_product_discount > 0" class="text-xs text-blue-600">
              <i class="fas fa-info-circle mr-1"></i> Bao gồm {{ formatPrice(shop.admin_product_discount) }} đ từ mã admin
            </div>
            <div v-if="shop.shipping_discount > 0" class="flex justify-between gap-4">
              <span>Giảm giá phí ship:</span>
              <div class="flex items-center gap-2">
                <span class="text-green-600 font-semibold">-{{ formatPrice(shop.shipping_discount) }} đ</span>
                <button @click="removeShippingDiscount(shop)" class="text-red-500 text-xs hover:underline">Huỷ</button>
              </div>
            </div>
            <div class="flex justify-between gap-4 pt-2 border-t border-gray-200">
              <span class="font-semibold">Tổng thanh toán:</span>
              <span class="font-bold text-blue-600">{{ formatPrice(calculateStoreTotal(shop) - (shop.discount || 0) + Math.max(0, (shop.original_shipping_fee || shop.shipping_fee) - (shop.shipping_discount || 0))) }} đ</span>
            </div>
            <div class="text-xs text-gray-500 mt-1">
              <i class="fas fa-ticket-alt mr-1"></i>
              {{ shop.discount > 0 || shop.shipping_discount > 0 ? 'Đã áp dụng mã giảm giá' : 'Chưa áp dụng mã giảm giá' }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-right text-sm font-semibold text-gray-900 mt-4">
      Tổng phí vận chuyển:
      <span id="shipping-fee-display">
        <span v-if="totalShippingDiscount > 0" class="line-through text-gray-400 mr-1">{{ formatPrice(totalOriginalShippingFee) }} đ</span>
        <span class="font-bold text-blue-600">{{ formatPrice(totalShippingFee) }} đ</span>
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
                  class="text-[10px] text-blue-600 bg-blue-50 rounded px-1 py-0.5 font-medium">{{ discount.level }}</span>
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
import { useDiscount } from '~/composables/useDiscount';
import { useToast } from '~/composables/useToast';

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl;
const { toast } = useToast();

const fees = ref({});
const loadingShipping = ref(false);
const shippingMethods = ref({});
const loadingFees = ref({});
const errorMessage = ref('');
const isCalculatingShipping = ref(false);

const props = defineProps({
  address: Object,
  cartItems: Array
});

const emit = defineEmits(['update:shippingFee', 'update:shopDiscount', 'update:totalShippingFee', 'update:shippingDiscount']);

const {
  sellerAddresses,
  fetchSellerAddress,
  fetchGHNServiceId,
  fetchDefaultAddress,
  cartItems,
  defaultShippingMethod,
  shopServiceIds,
  calculateShippingFee,
  shippingFeeCache
} = useCheckout(null, null, props.address, ref({}));

defineExpose({
  fees,
  shopServiceIds
});

const parsePrice = (price) => {
  if (price == null) return 0;
  if (typeof price === 'number') return Math.floor(price);
  if (typeof price === 'string' && price.includes('.')) {
    const num = parseFloat(price);
    return isNaN(num) ? 0 : Math.floor(num);
  }
  let clean = String(price).replace(/[^\d.,]/g, '').trim();
  if (clean.includes('.') && clean.includes(',')) {
    const lastCommaIndex = clean.lastIndexOf(',');
    const beforeComma = clean.substring(0, lastCommaIndex).replace(/\./g, '');
    const afterComma = clean.substring(lastCommaIndex + 1);
    clean = beforeComma + afterComma;
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
  return parsed.toLocaleString('vi-VN');
};

const calculateTotalWeight = (shop) => {
  const totalWeight = shop.items.reduce((sum, item) => {
    const itemWeight = parseFloat(item.productVariant?.weight || 1000);
    return sum + itemWeight * item.quantity;
  }, 0);
  return totalWeight;
};

const getShippingFee = (sellerId) => {
  const shop = localCartItems.value.find(s => s.seller_id === sellerId);
  return shop && shop.shipping_fee >= 0 ? formatPrice(shop.shipping_fee) : 'Đang tính...';
};

const totalOriginalShippingFee = computed(() => {
  return localCartItems.value.reduce((sum, shop) => sum + (shop.original_shipping_fee || shop.shipping_fee || 0), 0);
});

const totalShippingDiscount = computed(() => {
  return localCartItems.value.reduce((sum, shop) => sum + (shop.shipping_discount || 0), 0);
});

const totalShippingFee = computed(() => {
  return localCartItems.value.reduce((sum, shop) => {
    const shippingFee = shop.shipping_fee || 0;
    const shippingDiscount = shop.shipping_discount || 0;
    return sum + Math.max(0, shippingFee - shippingDiscount);
  }, 0);
});

const calculateAllShippingFees = async () => {
  console.log('=== calculateAllShippingFees START ===');
  console.log('isCalculatingShipping:', isCalculatingShipping.value);
  console.log('props.cartItems:', props.cartItems);
  console.log('props.address:', props.address);
  
  if (isCalculatingShipping.value) {
    console.log('Already calculating, skipping...');
    return;
  }
  isCalculatingShipping.value = true;

  if (!props.cartItems || !Array.isArray(props.cartItems) || props.cartItems.length === 0) {
    console.error('Không có sản phẩm trong giỏ hàng');
    toast('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm để tính phí vận chuyển.');
    isCalculatingShipping.value = false;
    return;
  }

  if (!props.address || !props.address?.district_id || !props.address?.ward_code) {
    console.error('Địa chỉ giao hàng không hợp lệ:', props.address);
    toast('error', 'Vui lòng chọn địa chỉ giao hàng hợp lệ.');
    isCalculatingShipping.value = false;
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
      errorMessage.value = 'Không thể tính phí vận chuyển. Thiếu thông tin cửa hàng.';
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
            errorMessage.value = `Không thể lấy địa chỉ cho shop ${shop.seller_id}.`;
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

    try {
      const totalWeight = calculateTotalWeight(shop);
      const services = await fetchGHNServiceId(shop.seller_id, districtId, props.address.district_id);
      console.log(`Dịch vụ vận chuyển cho shop ${shop.seller_id}:`, JSON.stringify(services, null, 2));

      if (!services || !Array.isArray(services) || services.length === 0) {
        fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
        errorMessage.value = `Không có dịch vụ vận chuyển khả dụng cho shop ${shop.seller_id}.`;
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

      const shopIndex = localCartItems.value.findIndex(s => s.seller_id === shop.seller_id);
      if (shopIndex !== -1) {
        localCartItems.value[shopIndex].service_id = effectiveMethod.service_id;
      }

      const { fee } = await calculateShippingFee(shop.seller_id, {
        district_id: districtId,
        ward_code: wardCode,
        service_id: effectiveMethod.service_id,
        total_weight: totalWeight
      }, props.address);

      if (fee !== null && !isNaN(fee) && fee >= 0) {
        console.log(`✅ Shop ${shop.seller_id}: Calculated shipping fee = ${fee}`);
        fees.value[`${shop.seller_id}_${effectiveMethod.service_id}`] = fee;
        const shopIndex = localCartItems.value.findIndex(s => s.seller_id === shop.seller_id);
        if (shopIndex !== -1) {
          localCartItems.value[shopIndex].shipping_fee = fee;
          localCartItems.value[shopIndex].original_shipping_fee = fee; // Lưu phí gốc
          console.log(`✅ Updated shop ${shop.seller_id} shipping_fee to ${fee}`);
          console.log('Updated localCartItems:', localCartItems.value.map(s => ({
            seller_id: s.seller_id,
            shipping_fee: s.shipping_fee,
            original_shipping_fee: s.original_shipping_fee
          })));
        } else {
          console.warn(`❌ Shop ${shop.seller_id} not found in localCartItems`);
        }
        emit('update:shippingFee', { sellerId: shop.seller_id, fee });
      } else {
        fees.value[`${shop.seller_id}_${effectiveMethod.service_id}`] = 'Lỗi';
        errorMessage.value = `Không thể tính phí vận chuyển cho shop ${shop.seller_id} với dịch vụ ${effectiveMethod.service_id}.`;
        if (shopIndex !== -1) {
          localCartItems.value[shopIndex].shipping_fee = 0;
        }
      }
    } catch (error) {
      console.error(`Lỗi tính phí vận chuyển cho shop ${shop.seller_id}:`, error);
      fees.value[`${shop.seller_id}_unknown`] = 'Lỗi';
      errorMessage.value = `Lỗi tính phí cho shop ${shop.seller_id}: ${error.message || 'Không xác định'}.`;
      const shopIndex = localCartItems.value.findIndex(s => s.seller_id === shop.seller_id);
      if (shopIndex !== -1) {
        localCartItems.value[shopIndex].shipping_fee = 0;
      }
    } finally {
      loadingFees.value[shop.seller_id] = false;
    }
  });

  await Promise.all(shippingPromises);

  const totalShippingFeeValue = localCartItems.value.reduce((sum, shop) => {
    const shippingFee = shop.shipping_fee || 0;
    const shippingDiscount = shop.shipping_discount || 0;
    return sum + Math.max(0, shippingFee - shippingDiscount);
  }, 0);
  console.log(`Tổng phí vận chuyển sau chiết khấu: ${totalShippingFeeValue}`);
  console.log('localCartItems after calculation:', localCartItems.value.map(s => ({
    seller_id: s.seller_id,
    shipping_fee: s.shipping_fee,
    original_shipping_fee: s.original_shipping_fee,
    shipping_discount: s.shipping_discount
  })));
  emit('update:totalShippingFee', totalShippingFeeValue);

  if (Object.values(fees.value).some(f => f === 'Lỗi')) {
    toast('error', errorMessage.value || 'Có lỗi xảy ra khi tính phí vận chuyển.');
  }

  loadingShipping.value = false;
  isCalculatingShipping.value = false;
  console.log('=== calculateAllShippingFees END ===');
  console.timeEnd('calculateAllShippingFees');
};

const retryCalculateFees = async () => {
  errorMessage.value = '';
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
  
  const shop = localCartItems.value.find(shop => String(shop.seller_id) === String(selectedSellerId.value));
  if (!shop || !shop.items || shop.items.length === 0) return [];
  
  // Lấy danh sách product IDs của shop này
  const shopProductIds = shop.items.map(item => {
    const productId = item.product_id || item.product?.id || item.id;
    return productId;
  }).filter(id => id);
  
  console.log('=== DEBUG filteredVouchers ===');
  console.log('Shop product IDs:', shopProductIds);
  console.log('All user vouchers:', userVouchers.value);
  
  // Lọc voucher có thể áp dụng cho tất cả sản phẩm trong shop
  const applicableVouchers = userVouchers.value.filter(voucher => {
    // Voucher toàn sàn (seller_id === null) luôn có thể áp dụng
    if (voucher.seller_id === null) {
      console.log('Voucher toàn sàn:', voucher.name, 'có thể áp dụng');
      return true;
    }
    
    // Voucher của seller cụ thể
    if (String(voucher.seller_id) === String(selectedSellerId.value)) {
      // Nếu voucher có products được gán cụ thể
      if (voucher.products && voucher.products.length > 0) {
        // Kiểm tra xem tất cả sản phẩm trong shop có được áp dụng voucher này không
        const applicableProducts = voucher.products.filter(product => 
          shopProductIds.includes(product.id)
        );
        
        console.log('Voucher có products:', voucher.name, 'applicable products:', applicableProducts.length, 'shop products:', shopProductIds.length);
        
        // Chỉ áp dụng nếu voucher có thể dùng cho tất cả sản phẩm trong shop
        if (applicableProducts.length === shopProductIds.length) {
          console.log('Voucher có thể áp dụng cho tất cả sản phẩm:', voucher.name);
          return true;
        } else {
          console.log('Voucher không thể áp dụng cho tất cả sản phẩm:', voucher.name);
          return false;
        }
      }
      
      // Nếu voucher có categories được gán cụ thể
      if (voucher.categories && voucher.categories.length > 0) {
        // Kiểm tra xem tất cả sản phẩm trong shop có thuộc categories được áp dụng không
        // Logic này cần kiểm tra từng sản phẩm có thuộc categories không
        // Tạm thời return true, có thể cần cải thiện sau
        console.log('Voucher có categories:', voucher.name, 'có thể áp dụng');
        return true;
      }
      
      // Voucher không có products/categories cụ thể -> có thể áp dụng cho tất cả sản phẩm của seller
      console.log('Voucher không có products/categories cụ thể:', voucher.name, 'có thể áp dụng');
      return true;
    }
    
    return false;
  });
  
  console.log('Applicable vouchers:', applicableVouchers.map(v => v.name));
  console.log('=== END DEBUG ===');
  
  return applicableVouchers;
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

// Toast function đã được import từ useToast

const applyDiscount = async (discount) => {
  const shop = localCartItems.value.find(shop => String(shop.seller_id) === String(selectedSellerId.value));
  if (!shop) {
    toast('error', 'Không tìm thấy cửa hàng.');
    return;
  }

  // Lấy danh sách product IDs của shop này
  const productIds = shop.items.map(item => {
    // Thử các cách khác nhau để lấy product_id
    const productId = item.product_id || item.product?.id || item.id;
    console.log('Item:', item, 'Product ID:', productId);
    return productId;
  }).filter(id => id);
  const orderValue = calculateStoreTotal(shop);

  console.log('=== DEBUG applyDiscount ===');
  console.log('Shop:', shop);
  console.log('Product IDs:', productIds);
  console.log('Order value:', orderValue);
  console.log('Discount:', discount);
  console.log('Shop items:', shop.items);

  // Kiểm tra xem discount có áp dụng được cho các sản phẩm này không
  if (discount.seller_id && discount.seller_id === shop.seller_id) {
    // Đây là shop discount, cần kiểm tra sản phẩm
    
    // Kiểm tra trước ở frontend
    if (discount.products && discount.products.length > 0) {
      // Kiểm tra xem tất cả sản phẩm trong shop có được áp dụng voucher này không
      const applicableProducts = discount.products.filter(product => 
        productIds.includes(product.id)
      );
      
      console.log('Frontend check - Voucher products:', discount.products.length, 'Applicable products:', applicableProducts.length, 'Shop products:', productIds.length);
      
      // Chỉ áp dụng nếu voucher có thể dùng cho tất cả sản phẩm trong shop
      if (applicableProducts.length !== productIds.length) {
        toast('error', 'Mã giảm giá này chỉ áp dụng cho một số sản phẩm, không thể áp dụng cho toàn bộ đơn hàng');
        console.log('Frontend check failed - voucher cannot be applied to all products');
        return;
      }
    }
    
    // Nếu frontend check passed, gọi API để kiểm tra chi tiết
    const { checkShopDiscount } = useDiscount();
    console.log('Calling checkShopDiscount with:', {
      discountId: discount.id,
      sellerId: shop.seller_id,
      productIds: productIds,
      orderValue: orderValue
    });
    
    const checkResult = await checkShopDiscount(discount.id, shop.seller_id, productIds, orderValue);
    
    if (!checkResult.success) {
      toast('error', checkResult.message);
      console.log('Discount check failed:', checkResult);
      return;
    }
    
    console.log('Discount check passed:', checkResult);
  }

  shop.selectedDiscountId = discount.id;
  let discountAmount = 0;

  if (discount.discount_type === 'shipping_fee') {
    discountAmount = Number(discount.discount_value);
    shop.shipping_discount = discountAmount;
    emit('update:shippingDiscount', {
      sellerId: selectedSellerId.value,
      shippingDiscount: discountAmount,
      discountId: discount.id
    });
  } else {
    shop.discount = 0; // Reset discount sản phẩm
    if (discount.discount_type === 'fixed') {
      discountAmount = Number(discount.discount_value);
      shop.discount = discountAmount;
    } else if (discount.discount_type === 'percentage') {
      const percent = parseFloat(discount.discount_value);
      const total = calculateStoreTotal(shop);
      discountAmount = Math.floor((percent / 100) * total);
      shop.discount = discountAmount;
    }
    emit('update:shopDiscount', {
      sellerId: selectedSellerId.value,
      discount: discountAmount,
      discountId: discount.id
    });
  }

  console.log('=== END DEBUG ===');
  toast('success', `Đã áp dụng mã giảm giá cho ${shop.store_name || 'cửa hàng'}`);
  showDiscountPopup.value = false;
  selectedSellerId.value = null;
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

const removeShippingDiscount = (shop) => {
  shop.shipping_discount = 0;
  shop.selectedDiscountId = null;
  emit('update:shippingDiscount', {
    sellerId: shop.seller_id,
    shippingDiscount: 0,
    discountId: null
  });
  toast('success', `Đã hủy mã giảm giá phí vận chuyển cho ${shop.store_name || 'cửa hàng'}`);
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
  shop.service_id = method.service_id;
  loadingFees.value[sellerId] = true;

  try {
    const totalWeight = calculateTotalWeight(shop);
    const { fee } = await calculateShippingFee(sellerId, {
      district_id: shop.district_id,
      ward_code: shop.ward_code,
      service_id: method.service_id,
      total_weight: totalWeight
    }, props.address);

    if (fee !== null && !isNaN(fee) && fee >= 0) {
      fees.value[`${sellerId}_${method.service_id}`] = fee;
      shop.shipping_fee = fee;
      shop.original_shipping_fee = fee; // Lưu phí gốc
      emit('update:shippingFee', { sellerId, fee });
    } else {
      fees.value[`${sellerId}_${method.service_id}`] = 'Lỗi';
      errorMessage.value = `Không thể tính phí cho cửa hàng ${sellerId}.`;
      shop.shipping_fee = 0;
    }
  } catch (error) {
    console.error(`Lỗi tính phí vận chuyển khi thay đổi phương thức cho shop ${sellerId}:`, error);
    fees.value[`${sellerId}_${method.service_id}`] = 'Lỗi';
    errorMessage.value = `Lỗi tính phí cho cửa hàng ${sellerId}: ${error.message}`;
    shop.shipping_fee = 0;
  } finally {
    loadingFees.value[sellerId] = false;
  }

  const totalShippingFeeValue = localCartItems.value.reduce((sum, shop) => {
    const shippingFee = shop.shipping_fee || 0;
    const shippingDiscount = shop.shipping_discount || 0;
    return sum + Math.max(0, shippingFee - shippingDiscount);
  }, 0);
  console.log(`Cập nhật tổng phí vận chuyển: ${totalShippingFeeValue}`);
  emit('update:totalShippingFee', totalShippingFeeValue);
};

const calculateStoreTotal = (shop) => {
  if (!shop.items || !Array.isArray(shop.items)) return 0;
  const total = shop.items.reduce((total, item) => {
    const price = parsePrice(item.sale_price || item.price || 0);
    const quantity = parseInt(item.quantity || 1);
    const itemTotal = price * quantity;
    return total + itemTotal;
  }, 0);
  return total;
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
    return `Giảm ${formatPrice(Number(discount.discount_value))}`;
  }
  if (discount.discount_type === 'shipping_fee') {
    return `Giảm phí ship ${formatPrice(Number(discount.discount_value))}`;
  }
  return '';
};

const localCartItems = ref([]);
watch(() => props.cartItems, (val) => {
  localCartItems.value = val.map(shop => ({
    ...shop,
    discount: shop.discount || 0,
    shipping_discount: shop.shipping_discount || 0,
    selectedDiscountId: shop.selectedDiscountId || null,
    store_name: shop.store_name || 'Cửa hàng',
    store_url: shop.store_url || '#',
    items: shop.items || [],
    district_id: shop.district_id || null,
    ward_code: shop.ward_code || null,
    note: shop.note || '',
    shipping_fee: shop.shipping_fee || 0,
    original_shipping_fee: shop.original_shipping_fee || shop.shipping_fee || 0,
    service_id: shop.service_id || null,
    admin_product_discount: shop.admin_product_discount || 0
  }));
}, { immediate: true, deep: true });

// Emit tổng phí vận chuyển thực tế khi có thay đổi
watch(totalShippingFee, (newValue) => {
  emit('update:totalShippingFee', newValue);
}, { immediate: true });

// Emit tổng discount phí ship khi có thay đổi
watch(totalShippingDiscount, (newValue) => {
  emit('update:shippingDiscount', {
    totalDiscount: newValue,
    totalOriginalFee: totalOriginalShippingFee.value,
    totalRealFee: totalShippingFee.value
  });
}, { immediate: true });

let lastCartItems = null;
watch(() => props.cartItems, async (val) => {
  if (JSON.stringify(val) !== JSON.stringify(lastCartItems)) {
    lastCartItems = JSON.parse(JSON.stringify(val));
    await calculateAllShippingFees();
  }
}, { immediate: false, deep: true });

let lastAddress = null;
watch(() => props.address, async (newVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(lastAddress)) {
    lastAddress = JSON.parse(JSON.stringify(newVal));
    if (newVal && newVal.district_id && newVal.ward_code) {
      await calculateAllShippingFees();
    }
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

<style scoped>
.text-right .flex {
  align-items: center;
}
.text-right .flex span {
  white-space: nowrap;
}
.text-right .border-t {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
}
</style>