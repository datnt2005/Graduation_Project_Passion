<template>
  <div class="container mx-auto max-w-3xl px-4 py-8 md:py-12">
    <h1 class="text-2xl font-bold mb-6">Thanh toán</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Bên trái: Form và phương thức -->
      <div class="md:col-span-2 space-y-6">
        <!-- Thông tin giao hàng -->
        <div class="bg-white p-6 rounded shadow">
          <h2 class="text-xl font-semibold mb-4">Thông tin giao hàng</h2>
          <form class="space-y-4">
            <input v-model="form.name" placeholder="Họ tên" class="input" />
            <input v-model="form.phone" placeholder="Số điện thoại" class="input" />
            <input v-model="form.email" placeholder="Email" class="input" />
            <input v-model="form.address" placeholder="Địa chỉ" class="input" />
            <textarea v-model="form.note" placeholder="Ghi chú" class="input"></textarea>
          </form>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="bg-white p-6 rounded shadow">
          <h2 class="text-xl font-semibold mb-4">Phương thức thanh toán</h2>
          <div class="space-y-3">
            <label class="flex items-center gap-2">
              <input type="radio" value="cod" v-model="paymentMethod" />
              <span>Thanh toán khi nhận hàng (COD)</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" value="vnpay" v-model="paymentMethod" />
              <span>Thanh toán qua VNPAY</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" value="momo" v-model="paymentMethod" />
              <span>Thanh toán qua Momo</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Bên phải: Tóm tắt đơn hàng -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Tóm tắt đơn hàng</h2>
        <div v-for="item in cartItems" :key="item.id" class="flex justify-between mb-2">
          <div>
            <p class="font-medium">{{ item.name }}</p>
            <p class="text-sm text-gray-500">x{{ item.quantity }}</p>
          </div>
          <div>{{ formatCurrency(item.price * item.quantity) }}</div>
        </div>
        <hr class="my-4" />
        <div class="flex justify-between font-semibold text-lg">
          <span>Tổng cộng:</span>
          <span>{{ formatCurrency(total) }}</span>
        </div>
        <button
          @click="placeOrder"
          class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded font-semibold transition"
        >
          Đặt hàng
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const form = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
  note: ''
})

const paymentMethod = ref('cod')

const cartItems = ref([
  { id: 1, name: 'Hạt óc chó Mỹ', price: 120000, quantity: 2 },
  { id: 2, name: 'Hạt điều rang muối', price: 90000, quantity: 1 }
])

const total = computed(() =>
  cartItems.value.reduce((acc, item) => acc + item.price * item.quantity, 0)
)

function formatCurrency(value) {
  return value.toLocaleString('vi-VN') + '₫'
}

function placeOrder() {
  if (!form.name || !form.phone || !form.address) {
    alert('Vui lòng nhập đầy đủ thông tin giao hàng.')
    return
  }

  alert(`Đặt hàng thành công bằng phương thức: ${paymentMethod.value.toUpperCase()}`)

  // TODO: Gửi API đặt hàng & redirect nếu dùng VNPAY / MOMO
}
</script>

<style scoped>
.input {
  @apply border rounded px-4 py-2 w-full outline-none focus:ring-2 focus:ring-blue-500;
}
</style>
