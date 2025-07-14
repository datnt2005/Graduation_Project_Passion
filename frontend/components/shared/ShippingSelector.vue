<template>
  <section id="shipping-methods" class="bg-white rounded-[4px] p-6 shadow-sm space-y-6">
    <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức giao hàng</h3>

    <div v-if="loadingShipping" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-blue-600"></div>
    </div>

    <!-- Danh sách phương thức giao hàng -->
    <form v-else class="relative space-y-4 w-2/3">
      <label v-for="method in shippingMethods" :key="method.id"
        class="relative block p-4 border rounded-[4px] cursor-pointer transition hover:border-blue-400 accent-blue-60"
        :class="{
          'bg-blue-50 border-blue-200': method.id === selectedMethod,
          'bg-white border-blue-300': method.id !== selectedMethod
        }">
        <div class="flex items-center gap-3">
          <input class="w-4 h-4 text-[14px] text-blue-600 border-gray-300 accent-blue-600 focus:ring-blue-500"
            type="radio" name="shipping_method" :value="method.id" :checked="method.id === selectedMethod"
            @change="handleMethodChange(method.id)" />
          <span :class="method.id === selectedMethod ? 'text-[14px] ' : 'text-[14px] '">
            {{ method.name }}
          </span>
          <span class="text-green-600 ml-auto font-semibold" :id="'fee-' + method.id">
            {{ fees[method.id] !== undefined ? fees[method.id] : 'Đang tính...' }}
          </span>
        </div>
      </label>
    </form>

    <!-- Danh sách sản phẩm trong giỏ hàng -->
    <div class="space-y-8">
      <div v-for="shop in localCartItems" :key="shop.seller_id" class="border border-gray-300 rounded p-4 bg-white shadow">

        <!-- Tên cửa hàng + Nút chọn mã giảm giá -->
        <div class="flex justify-between items-center mb-4">
          <NuxtLink :to="`${shop.store_url}`" class=" text-sm font-semibold text-blue-600">
            <i class="fa-solid fa-shop"></i> {{ shop.store_name }}
          </NuxtLink>
          <button @click="selectShopDiscount(shop.seller_id)"
            class="text-sm text-blue-500 hover:underline hover:text-blue-700">
            + Chọn mã giảm giá
          </button>
        </div>
        <!-- KHÔNG render danh sách mã giảm giá inline nữa -->

        <!-- Danh sách sản phẩm trong shop -->
        <div class="space-y-4">
          <div v-for="item in shop.items" :key="item.id"
            class="flex gap-4 items-center border border-gray-100 rounded-md p-3 bg-gray-50">
            <!-- Ảnh sản phẩm -->
            <img
              :src="item.productVariant?.thumbnail ? mediaBaseUrl + item.productVariant.thumbnail : '/images/default-product.jpg'"
              :alt="item.product?.name" class="w-16 h-16 object-cover rounded border" />

            <!-- Thông tin sản phẩm -->
            <div class="flex-1">
              <div class="font-medium text-sm">
                {{ item.product?.name }}
              </div>
              <div class="text-gray-500 text-xs">
                {{item.productVariant?.attributes?.map(attr => attr.value).join(' - ')}}
              </div>
              <div class="text-gray-500 text-xs">
                Số lượng: x{{ item.quantity }}
              </div>
            </div>

            <!-- Giá -->
            <div class="text-sm font-semibold text-gray-900 whitespace-nowrap">
              {{ formatPrice(item.sale_price) }}
            </div>
          </div>
        </div>

        <!-- Tổng tiền shop + Ghi chú trên cùng 1 hàng -->
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
              <div>
                Tiền ban đầu: <span class="font-normal">{{ formatPrice(shop.store_total) }}</span>
              </div>
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
              Phí vận chuyển: <span class="font-semibold">{{ formatPrice(parsePrice(fees[selectedMethod])) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-right text-sm font-semibold text-gray-900 mt-4">
      Tổng tiền vận chuyển: <span id="shipping-fee-display">{{ formatPrice((Number(String(fees[selectedMethod]).replace(/[^\d]/g, '')) || 0) * localCartItems.length) }}</span>
    </div>

  </section>

  <Teleport to="body">
  <div v-if="showDiscountPopup" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-md p-6 w-[90%] max-w-2xl shadow-lg relative">
      <h3 class="text-base font-semibold mb-4 text-gray-800">Chọn mã giảm giá</h3>
      <!-- Ô tìm kiếm -->
      <div class="mb-4 flex items-center gap-2">
        <input v-model="searchCoupon" type="text" placeholder="Tìm kiếm tên hoặc mã..."
          class="border border-gray-300 rounded px-3 py-2 text-sm w-full focus:outline-none focus:ring-1 focus:ring-blue-500" />
        <i class="fas fa-search text-gray-400"></i>
      </div>
      <!-- Danh sách coupon dạng grid 2 cột -->
      <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <li v-if="loadingCoupons" class="col-span-2">Đang tải mã giảm giá...</li>
        <li v-else-if="filteredVouchersSearched.length === 0" class="col-span-2">Không có mã giảm giá nào</li>
        <li v-for="discount in filteredVouchersSearched" :key="discount.id">
          <div class="flex items-center bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-2 gap-3 min-h-[72px]">
            <!-- Logo/cấp độ bên trái -->
            <div class="flex flex-col items-center justify-center min-w-[48px]">
              <div class="bg-blue-100 rounded-lg w-10 h-10 flex items-center justify-center mb-1">
                <i class="fas fa-ticket-alt text-blue-500 text-lg"></i>
              </div>
              <span v-if="discount.level" class="text-[10px] text-blue-600 bg-blue-50 rounded px-1 py-0.5 font-medium">{{ discount.level }}</span>
            </div>
            <!-- Nội dung chính -->
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
            <!-- Nút chọn -->
            <div class="flex flex-col items-end ml-2">
              <button
                @click="applyDiscount(discount)"
                class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded px-3 py-1 shadow-sm transition">
                Chọn
              </button>
            </div>
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
import { onMounted, watch, ref, computed } from 'vue'
import { useRuntimeConfig } from '#app'
import { NuxtLink } from '#components'
import Swal from 'sweetalert2';

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBaseUrl = config.public.mediaBaseUrl
const weight = 1000
const fees = ref({})
const loadingShipping = ref(false)

const shippingMethods = [
  { id: 100039, name: 'GHN Tiết kiệm' },
  { id: 53321, name: 'GHN Nhanh' }
]

const props = defineProps({
  address: Object,
  selectedMethod: Number,
  cartItems: Array
})

const emit = defineEmits(['update:selectedMethod', 'update:shippingFee', 'update:shopDiscount'])

const selectedMethod = ref(props.selectedMethod ?? 100039)

watch(selectedMethod, (newVal) => {
  emit('update:selectedMethod', newVal);
})

defineExpose({
  selectedMethod,
  fees
})

const parsePrice = (price) => {
  if (price == null) return 0;
  let clean = String(price).replace(/[^\d.,]/g, '').trim();
  // Nếu có cả dấu chấm và dấu phẩy, xử lý kiểu Việt Nam: 20.500 hoặc 20.500,00
  if (clean.includes('.') && clean.includes(',')) {
    clean = clean.split(',')[0].replace(/\./g, '');
  } else if (clean.includes('.')) {
    // Nếu chỉ có dấu chấm, giả sử là phân tách nghìn
    clean = clean.replace(/\./g, '');
  } else if (clean.includes(',')) {
    // Nếu chỉ có dấu phẩy, giả sử là phân tách thập phân
    clean = clean.replace(/,/g, '');
  }
  const num = parseInt(clean, 10);
  return isNaN(num) ? 0 : num;
};

const formatPrice = (price) => {
  const parsed = parsePrice(price);
  // Hiển thị số nguyên, không có phần thập phân
  return parsed.toLocaleString('vi-VN', { maximumFractionDigits: 0 }) + ' đ';
};

const calculateAllShippingFees = async () => {
  if (!props.address || !props.address.district_id || !props.address.ward_code) return

  loadingShipping.value = true
  for (const method of shippingMethods) {
    try {
      const res = await fetch(`${apiBase}/shipping/calculate-fee`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          from_district_id: 1552,
          to_district_id: props.address.district_id,
          to_ward_code: props.address.ward_code,
          service_id: method.id,
          weight,
          height: 20,
          length: 20,
          width: 20
        })
      })
      const data = await res.json()
      const fee = data?.data?.total ?? 0
      fees.value[method.id] = fee.toLocaleString('vi-VN') + 'đ'

      if (method.id === selectedMethod.value) {
        emit('update:shippingFee', fee)  // Thêm dòng này
      }
      if (method.id === selectedMethod.value) {
        const display = document.getElementById('shipping-fee-display')
        if (display) display.textContent = fee.toLocaleString('vi-VN') + 'đ'
      }
    } catch (err) {
      fees.value[method.id] = 'Lỗi'
    }
  }
  loadingShipping.value = false
}

// --- Coupon logic ---
const userVouchers = ref([]); // Tất cả mã user đã lưu
const loadingCoupons = ref(false);

const showDiscountPopup = ref(false)
const selectedSellerId = ref(null)

// Lấy voucher đã lưu của user khi mount
onMounted(async () => {
  calculateAllShippingFees()
  emit('update:selectedMethod', selectedMethod.value)
  await fetchUserVouchers();
})

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
  }
  loadingCoupons.value = false;
}

const filteredVouchers = computed(() => {
  if (!selectedSellerId.value) return [];
  return userVouchers.value.filter(v => String(v.seller_id) === String(selectedSellerId.value));
});

const searchCoupon = ref('');
const filteredVouchersSearched = computed(() => {
  let arr = filteredVouchers.value;
  if (searchCoupon.value) {
    const keyword = searchCoupon.value.toLowerCase();
    arr = arr.filter(v =>
      (v.name && v.name.toLowerCase().includes(keyword)) ||
      (v.code && v.code.toLowerCase().includes(keyword))
    );
  }
  // Loại bỏ trùng id
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
    shop.selectedDiscountId = discount.id; // Đảm bảo selectedDiscountId được set
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
    
    // Cập nhật discount lên useCheckout
    emit('update:shopDiscount', {
      sellerId: selectedSellerId.value,
      discount: discountAmount,
      discountId: discount.id // Thêm discountId vào event
    });
    toast('success', `Đã áp dụng mã giảm giá cho ${shop.store_name}`);
  }
  showDiscountPopup.value = false;
  selectedSellerId.value = null;
};

// Hàm chọn shop để mở modal
const selectShopDiscount = (sellerId) => {
  selectedSellerId.value = sellerId;
  showDiscountPopup.value = true;
};

const handleMethodChange = (methodId) => {
  selectedMethod.value = methodId
  calculateAllShippingFees()
}

// Hàm lấy voucher cho từng shop (giữ nguyên)
const getVouchersForShop = (sellerId) => {
  return userVouchers.value.filter(v => String(v.seller_id) === String(sellerId));
};
// Hàm áp dụng mã giảm giá khi chọn inline
const applyDiscountInline = (shop, discount) => {
  shop.selectedDiscountId = discount.id;
  if (typeof discount.discount_value === 'number' || !isNaN(Number(discount.discount_value))) {
    shop.discount = Number(discount.discount_value);
  } else if (typeof discount.discount_value === 'string' && discount.discount_value.endsWith('%')) {
    const percent = parseFloat(discount.discount_value);
    const total = shop.items.reduce((sum, item) => sum + item.sale_price * item.quantity, 0);
    shop.discount = Math.floor((percent / 100) * total);
  }
  shop.showDiscountList = false; // Ẩn danh sách sau khi chọn
};

const onSelectDiscount = (shop) => {
  const discount = getVouchersForShop(shop.seller_id).find(d => d.id === shop.selectedDiscountId);
  if (discount) {
    if (typeof discount.discount_value === 'number' || !isNaN(Number(discount.discount_value))) {
      shop.discount = Number(discount.discount_value);
    } else if (typeof discount.discount_value === 'string' && discount.discount_value.endsWith('%')) {
      const percent = parseFloat(discount.discount_value);
      const total = shop.items.reduce((sum, item) => sum + item.sale_price * item.quantity, 0);
      shop.discount = Math.floor((percent / 100) * total);
    }
  } else {
    shop.discount = 0;
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

// Thêm hàm huỷ mã giảm giá cho shop
const removeDiscount = (shop) => {
  shop.discount = 0;
  shop.selectedDiscountId = null;
  
  // Cập nhật discount lên useCheckout
  emit('update:shopDiscount', {
    sellerId: shop.seller_id,
    discount: 0,
    discountId: null // Xóa discountId
  });
};


watch(() => props.address, (newVal) => {
  if (newVal) calculateAllShippingFees()
}, { immediate: true })

// Khi khởi tạo cartItems, đảm bảo mỗi shop có sẵn discount và selectedDiscountId
const localCartItems = ref([]);

watch(() => props.cartItems, (val) => {
  localCartItems.value = val.map(shop => ({
    ...shop,
    discount: shop.discount || 0,
    selectedDiscountId: shop.selectedDiscountId || null,
  }));
}, { immediate: true, deep: true });

</script>

