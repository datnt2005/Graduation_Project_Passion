<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const orderId = route.params.id
const order = ref(null)
const loading = ref(true)
const error = ref(null)
const config = useRuntimeConfig()
const api = config.public.apiBaseUrl
const formattedDate = computed(() => {
  if (!order.value?.created_at) return ''
  return new Date(order.value.created_at).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

const totalItems = computed(() => {
  if (!order.value?.items) return 0
  return order.value.items.reduce((sum, item) => sum + item.quantity, 0)
})

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${api}/user/orders/${orderId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    order.value = res.data
  } catch (err) {
    error.value = 'Không thể tải thông tin đơn hàng'
    console.error(err)
  } finally {
    loading.value = false
    setTimeout(() => window.print(), 500)
  }
})

function formatPrice(val) {
  if (!val) return '0 ₫'
  return Number(val).toLocaleString('vi-VN') + ' ₫'
}

  definePageMeta({
  layout: 'print'
})
</script>

<template>
  <div v-if="order" class="max-w-4xl mx-auto p-6 text-sm font-sans print:text-black">
    <!-- Header -->
    <div class="text-center mb-4">
      <h1 class="text-2xl font-bold uppercase">Sàn thương mại điện tử Passion</h1>
      <h2 class="text-xl font-bold uppercase mt-2">HÓA ĐƠN MUA HÀNG</h2>
    </div>

    <!-- Order Info -->
    <div class="grid md:grid-cols-2 gap-8 mb-6">
      <div>
        <h3 class="font-semibold text-gray-900 mb-2">Thông tin khách hàng</h3>
        <p><span class="text-gray-600">Họ tên:</span> {{ order.user.name }}</p>
        <p><span class="text-gray-600">Email:</span> {{ order.user.email }}</p>
        <p><span class="text-gray-600">Địa chỉ:</span> {{ order.address.detail }}</p>
        <p><span class="text-gray-600">SĐT:</span> {{ order.address.phone }}</p>
      </div>
      <div>
        <h3 class="font-semibold text-gray-900 mb-2">Thông tin đơn hàng</h3>
        <p><span class="text-gray-600">Mã đơn hàng:</span> #ORD{{ order.id.toString().padStart(3, '0') }}</p>
        <p><span class="text-gray-600">Ngày đặt:</span> {{ formattedDate }}</p>
        <p><span class="text-gray-600">Tổng sản phẩm:</span> {{ totalItems }} sản phẩm</p>
        <p><span class="text-gray-600">Trạng thái:</span> <span class="text-green-600 font-semibold">Đã thanh toán</span></p>
      </div>
    </div>

    <!-- Items Table -->
    <table class="w-full border border-gray-300 text-left mb-6 text-xs">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2 border">Sản phẩm</th>
          <th class="p-2 border">Biến thể</th>
          <th class="p-2 border">SL</th>
          <th class="p-2 border">Đơn giá</th>
          <th class="p-2 border">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in order.items" :key="item.slug">
          <td class="p-2 border">{{ item.product_name }}</td>
          <td class="p-2 border">{{ item.variant || '-' }}</td>
          <td class="p-2 border text-center">{{ item.quantity }}</td>
          <td class="p-2 border text-right">{{ formatPrice(item.price) }}</td>
          <td class="p-2 border text-right">{{ formatPrice(item.total) }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Total -->
    <div class="text-right text-base">
      <p><strong>Tổng cộng:</strong> <span class="text-xl font-bold text-green-700">{{ formatPrice(order.final_price) }}</span></p>
    </div>

    <!-- Footer -->
    <div class="text-center text-sm text-gray-600 mt-8 border-t pt-4">
      <p>Cảm ơn bạn đã mua sắm tại Passion Store!</p>
      <p>Mọi thắc mắc xin liên hệ: support@passion.com | 1900-xxxx</p>
    </div>
  </div>
</template>
 
<style scoped>
@media print {
  header,
  footer,
  .no-print,
  .main-layout,
  .top-bar,
  .bottom-bar,
  .category-bar,
  .menu,
  .global-navbar {
    display: none !important;
  }

  body {
    margin: 0;
    background: white !important;
  }

  #app {
    padding: 0 !important;
  }
}

@media print {
  body {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .no-print {
    display: none !important;
  }

  * {
    color: black !important;
    background: white !important;
  }

  table {
    break-inside: avoid;
  }

  thead {
    display: table-header-group;
  }

  tr {
    break-inside: avoid;
  }
}
</style>
