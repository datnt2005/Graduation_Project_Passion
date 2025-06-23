<template>
  <section id="shipping-methods"
    class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Chọn hình thức giao hàng</h3>
    <div class="space-y-4">
      <label
        v-for="method in shippingMethods"
        :key="method.id"
        class="flex items-center space-x-3 cursor-pointer p-4 rounded-lg border border-gray-300 hover:border-blue-500 transition-colors duration-200">
        <input
          type="radio"
          name="shipping_method"
          :value="method.id"
          :checked="method.id === selectedMethod"
          class="form-radio text-blue-600 h-5 w-5 shipping-method-radio"
          @change="handleMethodChange(method.id)"
        />
        <span class="text-gray-900 font-medium">{{ method.name }}</span>
        <span class="text-green-600 ml-auto font-semibold" :id="'fee-' + method.id">
          {{ fees[method.id] !== undefined ? fees[method.id] : 'Đang tính...' }}
        </span>
      </label>
    </div>
  </section>
</template>

<script setup>
import { onMounted, watch, ref } from 'vue'
import { useRuntimeConfig } from '#app'



const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const weight = 1000
const fees = ref({})

  
const shippingMethods = [
  { id: 100039, name: 'GHN Tiết kiệm' },
  { id: 53321, name: 'GHN Nhanh' }
]
const props = defineProps({
  address: Object,
  selectedMethod: Number
})

const emit = defineEmits(['update:selectedMethod'])

const selectedMethod = ref(props.selectedMethod ?? 100039)

watch(selectedMethod, (newVal) => {
  emit('update:selectedMethod', newVal)
})
 
defineExpose({
  selectedMethod
})

const calculateAllShippingFees = async () => {
  if (
    !props.address ||
    !props.address.district_id ||
    !props.address.ward_code
  ) return

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
        const display = document.getElementById('shipping-fee-display')
        if (display) display.textContent = fee.toLocaleString('vi-VN') + 'đ'
      }
    } catch (err) {
      fees.value[method.id] = 'Lỗi'
    }
  }
}

const handleMethodChange = (methodId) => {
  selectedMethod.value = methodId
  const fee = fees.value[methodId] || '0đ'
  const display = document.getElementById('shipping-fee-display')
  if (display) display.textContent = fee
}

onMounted(() => {
  calculateAllShippingFees()
})

watch(() => props.address, (newVal) => {
  if (newVal) calculateAllShippingFees()
}, { immediate: true })
</script>
