<template>
  <div class="max-w-[1200px] mx-auto">
    <!-- HERO + SEARCH -->
    <div class="bg-[#1BA0E2] text-white py-4 px-4 text-center mt-4 rounded-t-md">
      <h4 class="text-2xl md:text-3xl font-semibold mb-4">CHÚNG TÔI CÓ THỂ GIÚP GÌ CHO BẠN</h4>
    </div>

   <div class="grid grid-cols-2 gap-4 my-6 px-4">
      <div class="bg-white p-4 rounded shadow">
        <h5 class="text-lg font-semibold mb-2">Hỗ trợ kỹ thuật</h5>
        <p class="text-sm text-gray-600">Cần giúp đỡ về sản phẩm hoặc dịch vụ? Chúng tôi sẵn sàng hỗ trợ bạn.</p>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <h5 class="text-lg font-semibold mb-2">Hỗ trợ đơn hàng</h5>
        <p class="text-sm text-gray-600">Theo dõi, thay đổi hoặc hủy đơn hàng của bạn một cách dễ dàng.</p>
      </div>
    </div>
    <!-- Wrapper cho FAQ và Form -->
  <div class="flex flex-col md:flex-row gap-4 mb-4">

    <!-- FAQ -->
    <div class="bg-[#1BA0E2] text-white py-6 px-4 rounded w-full md:w-1/2">
      <h2 class="text-xl font-bold mb-4">CÂU HỎI THƯỜNG GẶP</h2>
      <ul class="space-y-2 text-sm">
        <li>
          <a href="/faq" class="block hover:underline hover:text-gray-200 transition">
            › Tôi cần làm gì để thay đổi thông tin cá nhân (SĐT, Email, ...)?
          </a>
        </li>
        <li>
          <a href="/faq" class="block hover:underline hover:text-gray-200 transition">
            › Tôi có thể theo dõi đơn hàng ở đâu?
          </a>
        </li>
        <li>
          <a href="/faq" class="block hover:underline hover:text-gray-200 transition">
            › Làm sao để đổi mật khẩu tài khoản?
          </a>
        </li>
        <li>
          <a href="/faq" class="block hover:underline hover:text-gray-200 transition">
            › Pasion có chính sách đổi trả không?
          </a>
        </li>
        <li>
          <a href="/faq" class="block hover:underline hover:text-gray-200 transition">
            › Cách liên hệ với bộ phận hỗ trợ?
          </a>
        </li>
      </ul>
    </div>

    <!-- FORM LIÊN HỆ -->
    <div class="bg-gray-100 py-6 px-4 rounded w-full md:w-1/2">
      <div class="text-center mb-4">
        <h2 class="text-2xl font-bold mb-2">Trợ giúp</h2>
        <p class="text-gray-600 text-sm">Gửi thông tin liên hệ hoặc trợ giúp tại đây.</p>
      </div>
      <form class="space-y-3 text-left" @submit.prevent="submitSupport">
        <input v-model="form.name" type="text" placeholder="Tên của bạn" class="w-full px-3 py-2 rounded border text-sm" required />
        <input v-model="form.email" type="email" placeholder="Email" class="w-full px-3 py-2 rounded border text-sm" required />
        <input v-model="form.phone" type="text" placeholder="Số điện thoại" class="w-full px-3 py-2 rounded border text-sm" />
        <select v-model="form.subject" class="w-full px-3 py-2 rounded border text-sm" required>
          <option value="" disabled>Chọn chủ đề</option>
          <option>Hỗ trợ kỹ thuật</option>
          <option>Hỗ trợ đơn hàng</option>
          <option>Đổi/trả hàng</option>
          <option>Phản hồi dịch vụ</option>
          <option>Khác</option>
        </select>
        <textarea v-model="form.content" rows="4" placeholder="Nội dung" class="w-full px-3 py-2 rounded border text-sm" required></textarea>
        <button
          type="submit"
          class="bg-[#1BA0E2] text-white px-6 py-2 rounded hover:bg-blue-600 transition w-full text-sm"
          :disabled="submitting"
        >
          Gửi
        </button>
      </form>
    </div>
  </div>
  </div>
</template>

<script setup>
import '@fortawesome/fontawesome-svg-core/styles.css'
import { ref } from 'vue'
import { useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const { toast } = useToast()
const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  content: ''
})
const submitting = ref(false)

const submitSupport = async () => {
  submitting.value = true
  try {
    await $fetch(`${apiBase}/supports`, {
      method: 'POST',
      body: form.value
    })
    toast('success', 'Gửi hỗ trợ thành công!')
    form.value = { name: '', email: '', phone: '', subject: '', content: '' }
  } catch (e) {
    toast('error', 'Gửi thất bại, vui lòng thử lại.')
  }
  submitting.value = false
}
</script>

<style scoped>
/* Thêm CSS tùy chỉnh tại đây nếu cần */
</style>
