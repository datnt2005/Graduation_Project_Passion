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
      <div v-for="shop in cartItems" :key="shop.seller_id" class="border border-gray-300 rounded p-4 bg-white shadow">

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

        <!-- Tổng tiền shop -->
        <div class="text-right text-sm font-semibold text-gray-700 mt-4">
          Tổng tiền: {{ formatPrice(shop.store_total - (shop.discount || 0)) }}
          <span v-if="shop.discount > 0" class="text-xs text-green-600 ml-2">(Giảm: {{ formatPrice(shop.discount)
            }})</span>
        </div>
      </div>
    </div>
    <div class="text-right text-sm font-semibold text-gray-900 mt-4">
      Tổng tiền vận chuyển: <span id="shipping-fee-display">{{ formatPrice(fees[selectedMethod]) }}</span>
    </div>

  </section>

  <Teleport to="body">
  <div v-if="showDiscountPopup" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-md p-6 w-[90%] max-w-md shadow-lg relative">
      <h3 class="text-base font-semibold mb-4 text-gray-800">Chọn mã giảm giá</h3>
      <ul class="space-y-3">
        <li v-for="discount in discountOptions" :key="discount.id">
          <button
            @click="applyDiscount(discount)"
            class="w-full text-left p-3 border rounded-md hover:bg-gray-50 text-sm"
          >
            {{ discount.name }}
          </button>
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
import { onMounted, watch, ref } from 'vue'
import { useRuntimeConfig } from '#app'
import { NuxtLink } from '#components'

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

const emit = defineEmits(['update:selectedMethod', 'update:shippingFee'])

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
  let clean = String(price).trim();

  // Nếu có cả dấu . và , → chuẩn châu Âu → đổi , thành . và xoá dấu .
  if (clean.includes(',') && clean.includes('.')) {
    clean = clean.replace(/\./g, '').replace(',', '.');
  } else {
    // Xoá tất cả dấu . hoặc , nếu chỉ có 1 loại
    clean = clean.replace(/[,.]/g, '');
  }

  const num = Number(clean.replace(/[^\d.-]/g, ''));
  return isNaN(num) ? 0 : num;
};

const formatPrice = (price) => {
  const parsed = parsePrice(price);
  return parsed.toLocaleString('vi-VN') + ' đ';
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


const showDiscountPopup = ref(false)
const selectedSellerId = ref(null)

const selectShopDiscount = (sellerId) => {
  selectedSellerId.value = sellerId
  showDiscountPopup.value = true
}

// Mã giảm giá hiển thị cứng
const discountOptions = ref([
  { id: 1, name: 'Giảm 20.000đ cho đơn từ 200k', amount: 20000 },
  { id: 2, name: 'Giảm 10%', amount: '10%' },
  { id: 3, name: 'Giảm 30.000đ vận chuyển', amount: 30000 }
])

const applyDiscount = (discount) => {
  const shop = props.cartItems.find(shop => shop.seller_id === selectedSellerId.value)
  if (shop) {
    // Áp dụng số tiền hoặc phần trăm
    if (typeof discount.amount === 'number') {
      shop.discount = discount.amount
    } else if (typeof discount.amount === 'string' && discount.amount.endsWith('%')) {
      const percent = parseFloat(discount.amount)
      const total = shop.items.reduce((sum, item) => sum + item.sale_price * item.quantity, 0)
      shop.discount = Math.floor((percent / 100) * total)
    }
  }

  showDiscountPopup.value = false
  selectedSellerId.value = null
}

const handleMethodChange = (methodId) => {
  selectedMethod.value = methodId
  calculateAllShippingFees()
}


onMounted(() => {
  calculateAllShippingFees()
  emit('update:selectedMethod', selectedMethod.value)
})

watch(() => props.address, (newVal) => {
  if (newVal) calculateAllShippingFees()
}, { immediate: true })
</script>
