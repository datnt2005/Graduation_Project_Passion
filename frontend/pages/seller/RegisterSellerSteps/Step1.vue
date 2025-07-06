<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-white relative">

    <!-- 🟦 Thanh bước ở trên cùng toàn trang -->
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="0" />
    </div>
    <!-- Cột trái: Ảnh minh họa -->
    <div class="hidden lg:flex items-center justify-center">
      <img src="/images/SellerCenter2.png" alt="Đăng ký bán hàng" class="max-h-[500px] rounded-xl shadow-md" />
    </div>

    <!-- Cột phải: Form -->
    <div class="flex items-center justify-center px-8 py-20">
      <div class="w-full max-w-xl">
        <div class="mb-8 mt-6">
          <h1 class="text-2xl font-bold">Tạo tài khoản bán hàng</h1>
          <p class="text-gray-600">Tham gia để tiếp cận hàng triệu khách hàng</p>
        </div>

        <form @submit.prevent="submitStep1" class="space-y-5">
          <div>
            <label class="block mb-1 font-medium">Tên cửa hàng *</label>
            <input v-model="form.store_name" type="text" class="input" required />
            <p v-if="errors.store_name" class="text-red-500 text-sm mt-1">{{ errors.store_name[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Số điện thoại *</label>
            <input v-model="form.phone_number" type="text" class="input" required />
            <p v-if="errors.phone_number" class="text-red-500 text-sm mt-1">{{ errors.phone_number[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Địa chỉ lấy hàng *</label>
            <textarea v-model="form.pickup_address" class="input" rows="3" required></textarea>
            <p v-if="errors.pickup_address" class="text-red-500 text-sm mt-1">{{ errors.pickup_address[0] }}</p>
          </div>

          <button type="submit" class="btn btn-primary w-full h-11" :disabled="loading">
            <span v-if="!loading">Tiếp theo</span>
            <span v-else>Đang xử lý...</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from '~/composables/useToast'
import RegisterSteps from '@/components/RegisterSteps.vue'

const { toast } = useToast()
const router = useRouter()
const config = useRuntimeConfig()
const api = config.public.apiBaseUrl

// Form data
const form = reactive({
  store_name: '',
  phone_number: '',
  pickup_address: ''
})

// Load từ localStorage nếu có
onMounted(() => {
  const saved = localStorage.getItem('register_step1')
  if (saved) Object.assign(form, JSON.parse(saved))
})

const loading = ref(false)
const errors = ref({})

const submitStep1 = async () => {
  loading.value = true
  errors.value = {}

  try {
    // Lưu vào localStorage
    localStorage.setItem('register_step1', JSON.stringify(form))

    router.push('/seller/RegisterSellerSteps/step2')
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      for (const field in errors.value) {
        const messages = errors.value[field]
        Array.isArray(messages)
          ? messages.forEach(msg => toast('error', msg))
          : toast('error', messages)
      }
    } else {
      toast('error', 'Có lỗi xảy ra khi tạo tài khoản bán hàng')
      console.error(error)
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}
.btn-primary {
  @apply bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition;
}
</style>
