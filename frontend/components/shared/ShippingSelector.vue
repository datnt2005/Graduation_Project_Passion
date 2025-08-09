<template>
  <section id="shipping-methods" class="bg-white rounded-[4px] p-6 shadow-sm space-y-6">

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
      <div
        v-for="shop in localCartItems"
        :key="shop.seller_id + '-' + (shop.service_id || 'x')"
        class="border border-gray-300 rounded p-4 bg-white shadow"
      >
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
                {{ item.productVariant?.attributes?.map(attr => attr.value).join(' - ') || '' }}
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
            <textarea v-model="shop.note" rows="3"
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
                  {{ isCheckoutCalculatingShipping ? 'Đang tính...' : 'Chưa tính' }}
                </span>
              </span>
            </div>
            <div v-if="shop.discount > 0" class="flex justify-between gap-4">
              <span>Giảm giá sản phẩm:</span>
              <div class="flex items-center gap-2">
                <span class="text-green-600 font-semibold">-{{ formatPrice(shop.discount) }} đ</span>
                <button 
                  v-if="!isShopDiscountFromAdmin(shop)" 
                  @click="removeDiscount(shop)" 
                  class="text-red-500 text-xs hover:underline"
                >
                  Huỷ
                </button>
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

    <!-- Discount Popup -->
    <div v-if="showDiscountPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Chọn mã giảm giá</h3>
          <button @click="showDiscountPopup = false" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm mã giảm giá</label>
            <input v-model="searchCoupon" type="text" placeholder="Nhập mã giảm giá..."
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500" />
          </div>

          <div v-if="loadingCoupons" class="flex justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-blue-600"></div>
          </div>

          <div v-else-if="filteredVouchersSearched.length === 0" class="text-center py-4 text-gray-500">
            Không có mã giảm giá khả dụng
          </div>

          <div v-else class="space-y-2 max-h-60 overflow-y-auto">
            <div v-for="voucher in filteredVouchersSearched" :key="voucher.id"
              class="border border-gray-200 rounded p-3 hover:bg-gray-50 cursor-pointer"
              @click="applyDiscount(voucher)">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="font-medium text-sm">{{ voucher.code }}</div>
                  <div class="text-xs text-gray-600">{{ voucher.description }}</div>
                  <div class="text-xs text-gray-500">
                    Giảm {{ voucher.discount_type === 'percentage' ? voucher.discount_value + '%' : formatPrice(voucher.discount_value) }}
                  </div>
                </div>
                <div class="text-xs text-gray-500">
                  {{ voucher.min_order_value ? `Từ ${formatPrice(voucher.min_order_value)}` : 'Không giới hạn' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useCheckout } from '~/composables/useCheckout';
import { useToast } from '~/composables/useToast';
import { useRuntimeConfig } from '#imports';

const props = defineProps({
  cartItems: { type: Array, required: false, default: () => [] },
  address: { type: Object, required: true },
  totalShippingFee: { type: Number, default: 0 },
  isBuyNow: { type: Boolean, default: false },
});

const emit = defineEmits([
  'update:shippingFee',
  'update:totalShippingFee',
  'update:shopDiscount',
  'update:shippingDiscount',
]);

const { toast } = useToast();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl;

// ====== THAM SỐ CHO useCheckout() ======
const shippingRef = ref(null);
const selectedShippingMethod = ref(null);
const storeNotes = ref({});

// selectedAddress phải là ref/computed để composable dùng .value
const selectedAddress = computed(() => props.address || null);

// ====== LẤY LOGIC SHIP/DISCOUNT TỪ composable ======
const {
  cartItems: cartItemsComputed,
  buyNowItems: buyNowItemsComputed,
  selectedDiscounts,
  getShopDiscountId,
  updateShopDiscount,
  removeShopDiscount: removeShopDiscountCore,
  recalculateAllShopDiscounts,
  isCheckoutCalculatingShipping,
  loadShippingFees,
  formatPrice,
} = useCheckout(shippingRef, selectedShippingMethod, selectedAddress, storeNotes);

// ==================== LOCAL STATE ====================
const localCartItems = ref([]);
const loadingShipping = ref(false);
const errorMessage = ref('');
const userVouchers = ref([]);
const loadingCoupons = ref(false);
const showDiscountPopup = ref(false);
const selectedSellerId = ref(null);
const searchCoupon = ref('');

// Nguồn hiển thị: ưu tiên Buy Now nếu isBuyNow=true
const itemsSource = computed(() => {
  if (props.isBuyNow) {
    const fromComposable = buyNowItemsComputed?.value || [];
    return fromComposable.length ? fromComposable : (props.cartItems || []);
  } else {
    const fromComposable = cartItemsComputed?.value || [];
    return (props.cartItems?.length || 0) > 0 ? props.cartItems : fromComposable;
  }
});

// ====== HELPERS ======
const calculateStoreTotal = (shop) => {
  return (shop.items || []).reduce((total, item) => {
    const unit = Number(item.sale_price || 0);
    return total + unit * (item.quantity || 1);
  }, 0);
};

// clone lại local từ nguồn (để UI thấy shipping_fee mới)
const syncLocalFromSource = () => {
  localCartItems.value = JSON.parse(JSON.stringify(itemsSource.value || []));
};

// ====== VOUCHERS ======
const fetchUserVouchers = async () => {
  loadingCoupons.value = true;
  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      userVouchers.value = [];
      return;
    }
    const res = await fetch(`${apiBase}/discounts/my-vouchers`, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    });
    if (res.ok) {
      const data = await res.json();
      userVouchers.value = data.data || [];
    } else {
      userVouchers.value = [];
    }
  } catch (err) {
    console.error('Error fetching vouchers:', err);
    userVouchers.value = [];
  } finally {
    loadingCoupons.value = false;
  }
};

const filteredVouchers = computed(() => {
  if (!selectedSellerId.value) return [];
  return userVouchers.value.filter(
    (v) => String(v.seller_id) === String(selectedSellerId.value)
  );
});

const filteredVouchersSearched = computed(() => {
  let arr = filteredVouchers.value;
  if (searchCoupon.value) {
    const keyword = searchCoupon.value.toLowerCase();
    arr = arr.filter(
      (v) =>
        (v.code && v.code.toLowerCase().includes(keyword)) ||
        (v.description && v.description.toLowerCase().includes(keyword))
    );
  }
  const seen = new Set();
  return arr.filter((v) => {
    if (seen.has(v.id)) return false;
    seen.add(v.id);
    return true;
  });
});

// ====== DISCOUNT FLOW ======
const selectShopDiscount = async (sellerId) => {
  selectedSellerId.value = sellerId;
  showDiscountPopup.value = true;
  if (userVouchers.value.length === 0) await fetchUserVouchers();
};

const applyDiscount = async (voucher) => {
  if (!selectedSellerId.value) return;

  const shopIndex = localCartItems.value.findIndex(
    (s) => String(s.seller_id) === String(selectedSellerId.value)
  );
  if (shopIndex === -1) return;

  const shop = localCartItems.value[shopIndex];
  const storeTotal = calculateStoreTotal(shop);

  // Validate min order
  if (voucher.min_order_value && storeTotal < voucher.min_order_value) {
    toast('error', `Đơn tối thiểu ${formatPrice(voucher.min_order_value)} để dùng mã này`);
    return;
  }

  // Tính discount
  let discountAmount = 0;
  if (voucher.discount_type === 'percentage') {
    discountAmount = Math.floor(storeTotal * (Number(voucher.discount_value) / 100));
  } else {
    discountAmount = Math.min(Number(voucher.discount_value), storeTotal);
  }

  // Gọi core để validate + đồng bộ
  const ok = await updateShopDiscount(selectedSellerId.value, discountAmount, voucher.id);
  if (!ok) return;

  // Lưu local để UI phản hồi ngay
  localCartItems.value[shopIndex].discount = discountAmount;
  localCartItems.value[shopIndex].discount_code = voucher.code;
  localCartItems.value[shopIndex].discount_id = voucher.id;

  emit('update:shopDiscount', {
    sellerId: selectedSellerId.value,
    discount: discountAmount,
    discountId: voucher.id,
    action: 'apply',
  });

  showDiscountPopup.value = false;
  toast('success', `Đã áp dụng mã ${voucher.code}`);
};

const removeDiscount = async (shop) => {
  const idx = localCartItems.value.findIndex((s) => s.seller_id === shop.seller_id);
  if (idx !== -1) {
    await removeShopDiscountCore(shop.seller_id);
    localCartItems.value[idx].discount = 0;
    localCartItems.value[idx].discount_code = null;
    localCartItems.value[idx].discount_id = null;

    emit('update:shopDiscount', {
      sellerId: shop.seller_id,
      discount: 0,
      discountId: null,
      action: 'remove',
    });
  }
  toast('success', 'Đã huỷ mã giảm giá');
};

const removeShippingDiscount = (shop) => {
  const idx = localCartItems.value.findIndex((s) => s.seller_id === shop.seller_id);
  if (idx !== -1) {
    localCartItems.value[idx].shipping_discount = 0;
    localCartItems.value[idx].shipping_discount_code = null;
  }
  emit('update:shippingDiscount', { sellerId: shop.seller_id, shippingDiscount: 0 });
  toast('success', 'Đã huỷ giảm giá phí ship');
};

// discount của shop có phải từ admin không?
const isShopDiscountFromAdmin = (shop) => {
  if (!shop.discount || shop.discount <= 0) return false;
  const shopDiscountId = getShopDiscountId(shop.seller_id);
  const adminDiscount = selectedDiscounts.value?.find(
    (d) =>
      d.id === shopDiscountId &&
      !d.seller_id &&
      (d.discount_type === 'percentage' || d.discount_type === 'fixed')
  );
  return !!adminDiscount;
};

// ====== SHIPPING ======
const retryCalculateFees = async () => {
  errorMessage.value = '';
  try {
    if (!selectedAddress.value?.district_id || !selectedAddress.value?.ward_code) {
      errorMessage.value = 'Thiếu địa chỉ nhận (district/ward).';
      return;
    }
    loadingShipping.value = true;
    await loadShippingFees();
    // Đồng bộ lại sau khi tính xong
    syncLocalFromSource();
  } catch (e) {
    console.error(e);
    errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại.';
  } finally {
    loadingShipping.value = false;
  }
};

// ====== SYNC LOCAL & EMIT TOTAL SHIPPING ======
const recomputeAndEmitTotalShipping = () => {
  const totalShippingFeeValue = (localCartItems.value || []).reduce((sum, shop) => {
    const shippingFee = Number(shop.shipping_fee || 0);
    const shippingDiscount = Number(shop.shipping_discount || 0);
    return sum + Math.max(0, shippingFee - shippingDiscount);
  }, 0);
  emit('update:totalShippingFee', totalShippingFeeValue);
};

// Debounce nhẹ cho load ship khi thay đổi
let shipTimer;
const scheduleReloadShipping = () => {
  clearTimeout(shipTimer);
  shipTimer = setTimeout(async () => {
    try {
      if (!selectedAddress.value?.district_id || !selectedAddress.value?.ward_code) {
        // Chưa đủ địa chỉ → không gọi ship
        return;
      }
      loadingShipping.value = true;
      await loadShippingFees();
      // Đồng bộ lại dữ liệu có shipping_fee mới
      syncLocalFromSource();
    } catch (e) {
      console.error(e);
      errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại.';
    } finally {
      loadingShipping.value = false;
    }
  }, 300);
};

// ====== WATCHERS ======
watch(
  itemsSource,
  () => {
    syncLocalFromSource();
    scheduleReloadShipping();
  },
  { immediate: true, deep: true }
);

// (tuỳ chọn) nếu muốn sync từ props.cartItems, chỉ khi KHÔNG phải Buy Now
watch(
  () => props.cartItems,
  (newItems) => {
    if (!props.isBuyNow) {
      localCartItems.value = JSON.parse(JSON.stringify(newItems || []));
    }
  },
  { deep: true }
);

// Cập nhật tổng phí ship mỗi khi local thay đổi
watch(localCartItems, recomputeAndEmitTotalShipping, { deep: true });

// Khi state đang tính → xong, clone lại để UI thấy ship ngay
watch(isCheckoutCalculatingShipping, (now, prev) => {
  if (prev === true && now === false) {
    syncLocalFromSource();
  }
});

onMounted(async () => {
  syncLocalFromSource();
  try {
    if (!selectedAddress.value?.district_id || !selectedAddress.value?.ward_code) return;
    loadingShipping.value = true;
    await loadShippingFees();
    // clone lại sau khi tính ship
    syncLocalFromSource();
  } catch (e) {
    console.error(e);
    errorMessage.value = 'Không thể tính phí vận chuyển. Vui lòng thử lại.';
  } finally {
    loadingShipping.value = false;
  }
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
