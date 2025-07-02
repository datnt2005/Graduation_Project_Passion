<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-white">
    <!-- Cột trái -->
    <div class="hidden lg:flex items-center justify-center bg-gray-50">
      <img src="/images/SellerCenter2.png" alt="Đăng ký bán hàng" class="max-h-[500px] rounded-xl shadow-md" />
    </div>

    <!-- Cột phải -->
    <div class="flex flex-col justify-center p-8 max-w-md w-full mx-auto">
      <h2 class="text-2xl font-bold text-blue-600 mb-2">Chọn dịch vụ vận chuyển</h2>
      <p class="text-gray-600 mb-6">Chọn các dịch vụ vận chuyển phù hợp với cửa hàng của bạn</p>

      <form @submit.prevent="submitStep2" class="space-y-6">
        <!-- GHN -->
        <div
          class="relative border rounded-xl p-5 space-y-2 cursor-pointer transition"
          :class="selected ? 'border-blue-500 bg-blue-50 shadow' : 'border-gray-300 hover:border-blue-400'"
          @click="toggleOption"
        >
          <div class="absolute -top-2 left-4 bg-blue-500 text-white text-xs px-2 py-[2px] rounded-md">
            Phổ biến
          </div>

          <div class="flex items-start gap-3">
            <input type="checkbox" class="mt-1 accent-blue-600" :checked="selected" />
            <div>
              <div class="flex items-center gap-2 font-semibold text-gray-800">
                <i class="fas fa-bolt text-blue-600"></i>
                Giao hàng nhanh (GHN)
              </div>
              <div class="text-sm text-gray-500">Giao hàng trong 1-2 ngày làm việc</div>
              <div class="flex gap-2 mt-2 flex-wrap">
                <span class="tag">Giao hàng 1-2 ngày</span>
                <span class="tag">Theo dõi đơn hàng</span>
                <span class="tag">Hỗ trợ 24/7</span>
              </div>
            </div>
          </div>
        </div>

        

        <!-- Nút -->
        <div class="flex justify-between items-center mt-6">
          <button type="button" @click="goBack" class="text-blue-600 underline">← Quay lại</button>
          <button type="submit" class="btn btn-primary w-32 h-11">Tiếp theo</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const selected = ref(true)
const router = useRouter()

onMounted(() => {
  const saved = localStorage.getItem('register_step2')
  if (saved) {
    const parsed = JSON.parse(saved)
    selected.value = !!parsed.express
  }
})


const toggleOption = () => {
  selected.value = !selected.value
}

const submitStep2 = () => {
  localStorage.setItem('register_step2', JSON.stringify({ express: selected.value }))
  router.push('/seller/RegisterSellerSteps/step3')
}


const goBack = () => {
  router.push('/seller/RegisterSellerSteps/step1')
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white rounded-lg py-2 hover:bg-blue-700 transition;
}
.tag {
  @apply text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full;
}
</style>
