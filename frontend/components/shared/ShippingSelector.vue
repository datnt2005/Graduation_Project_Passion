<template>
<section id="shipping-methods" class="bg-white rounded-[4px] p-6 shadow-sm space-y-6">
    <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức giao hàng</h3>

    <div v-if="loadingShipping" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-blue-600"></div>
    </div>

    <!-- Danh sách phương thức giao hàng -->
    <form v-else class="relative space-y-4 w-2/3">
      <label
        v-for="method in shippingMethods"
        :key="method.id"
        class="relative block p-4 border rounded-[4px] cursor-pointer transition hover:border-blue-400 accent-blue-60"
        :class="{
          'bg-blue-50 border-blue-200': method.id === selectedMethod,
          'bg-white border-blue-300': method.id !== selectedMethod
        }"
      >
        <div class="flex items-center gap-3">
          <input
            class="w-4 h-4 text-[14px] text-blue-600 border-gray-300 accent-blue-600 focus:ring-blue-500"
            type="radio"
            name="shipping_method"
            :value="method.id"
            :checked="method.id === selectedMethod"
            @change="handleMethodChange(method.id)"
          />
          <span
            :class="method.id === selectedMethod ? 'text-[14px] ' : 'text-[14px] '"
          >
            {{ method.name }}
          </span>
          <span class="text-green-600 ml-auto font-semibold" :id="'fee-' + method.id">
          {{ fees[method.id] !== undefined ? fees[method.id] : 'Đang tính...' }}
        </span>
        </div>

      </label>
    </form>

    <!-- Danh sách sản phẩm trong giỏ hàng -->
    <div class="space-y-6">
      <div
        v-for="item in cartItems"
        :key="item.id"
        class="border border-gray-200 rounded-[4px] p-4 bg-gray-50 shadow-sm"
      >
        <!-- Phí giao hàng -->
        <div class="flex justify-between items-center text-[14px] text-[14px] uppercase  border-b pb-2">
          <span class="text-[14px]">{{ item.shippingMethod || 'Giao tiết kiệm' }}</span>
          <span class="text-sm font-bold text-gray-900">
            {{ formatPrice(item.shippingFee || 37700) }}
          </span>
        </div>

        <!-- Thông tin sản phẩm -->
        <div class="flex gap-4 mt-4 items-center">
          <img
            :src="item.productVariant?.thumbnail ? mediaBaseUrl + item.productVariant.thumbnail : '/images/default-product.jpg'"
            :alt="item.productVariant?.product?.name"
            class="w-16 h-16 object-cover rounded-md border"
          />
           <div class="flex-1">
          <div class="font-semibold text-sm mb-0.5">
            {{ item.productVariant?.product?.name }}
          </div>
          <div class="text-gray-500 text-xs">SL: x{{ item.quantity }}</div>

          <!-- Vòng lặp hiển thị từng thuộc tính -->
         <div
          v-for="(attr, index) in item.productVariant?.attributes" :key="index" class="text-gray-500 text-xs">
          {{ attr.name }}: {{ attr.value }}
        </div>
        </div>
          <div class="text-sm font-semibold text-gray-900">
            {{ formatPrice(item.price) }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>


<script setup>
import { onMounted, watch, ref } from 'vue'
import { useRuntimeConfig } from '#app'

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



const emit = defineEmits(['update:selectedMethod'])

const selectedMethod = ref(props.selectedMethod ?? 100039)

watch(selectedMethod, (newVal) => {
  emit('update:selectedMethod', newVal);
})

defineExpose({
  selectedMethod,
  fees  
})

const formatPrice = (value) => {
  if (!value) return '0 đ'
  return Number(value).toLocaleString('vi-VN') + ' đ'
}

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
      fees.value[method.id] = fee.toLocaleString('vi-VN') + ' đ'
    } catch (err) {
      fees.value[method.id] = 'Lỗi'
    }
  }
  loadingShipping.value = false
}


const handleMethodChange = (methodId) => {
  selectedMethod.value = methodId
  const fee = fees.value[methodId] || '0đ'
  const display = document.getElementById('shipping-fee-display')
  if (display) display.textContent = fee
}

onMounted(() => {
  calculateAllShippingFees()
  emit('update:selectedMethod', selectedMethod.value) 
})

watch(() => props.address, (newVal) => {
  if (newVal) calculateAllShippingFees()
}, { immediate: true })
</script>
