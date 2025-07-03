<template>
  <div class="max-w-[1200px] mx-auto">
    <!-- HERO + SEARCH -->
    <div class="bg-[#1BA0E2] text-white py-4 px-4 text-center mt-4 rounded-t-md">
      <h4 class="text-2xl md:text-3xl font-semibold mb-4">CHÚNG TÔI CÓ THỂ GIÚP GÌ CHO BẠN</h4>
    </div>

    <!-- NGƯỜI BÁN / NGƯỜI MUA -->
   <div class="grid grid-cols-2 gap-4 my-6 px-4">
  <div class="border p-4 rounded text-center hover:shadow-md transition text-sm">
    <div class="text-2xl mb-1">🏠</div>
    <h3 class="text-base font-semibold mb-1">Tôi là người bán</h3>
    <p class="text-gray-600 text-xs">Mẹo vặt, hướng dẫn giúp bán hàng nhanh chóng và tiện lợi trên Pasion</p>
  </div>
  <div class="border p-4 rounded text-center hover:shadow-md transition text-sm">
    <div class="text-2xl mb-1">🛒</div>
    <h3 class="text-base font-semibold mb-1">Tôi là người mua</h3>
    <p class="text-gray-600 text-xs">Mẹo vặt, hướng dẫn giúp mua hàng nhanh chóng và tiện lợi trên Pasion</p>
  </div>
</div>
    <!-- Wrapper cho FAQ và Form -->
  <div class="flex flex-col md:flex-row gap-4 mb-4">

    <!-- FAQ -->
    <div class="bg-[#1BA0E2] text-white py-6 px-4 rounded w-full md:w-1/2">
      <h2 class="text-xl font-bold mb-4">CÂU HỎI THƯỜNG GẶP</h2>
      <ul class="space-y-2 text-sm">
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Tôi cần làm gì để thay đổi thông tin cá nhân (SĐT, Email, ...)?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Tôi có thể theo dõi đơn hàng ở đâu?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Làm sao để đổi mật khẩu tài khoản?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Pasion có chính sách đổi trả không?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
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
        <input v-model="form.subject" type="text" placeholder="Chủ đề" class="w-full px-3 py-2 rounded border text-sm" />
        <textarea v-model="form.content" rows="4" placeholder="Nội dung" class="w-full px-3 py-2 rounded border text-sm" required></textarea>
        <button
          type="submit"
          class="bg-[#1BA0E2] text-white px-6 py-2 rounded hover:bg-blue-600 transition w-full text-sm"
          :disabled="submitting"
        >
          Gửi
        </button>
        <div v-if="message" class="text-center text-green-600 mt-2">{{ message }}</div>
      </form>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  content: ''
})
const submitting = ref(false)
const message = ref('')

const submitSupport = async () => {
  submitting.value = true
  try {
    await $fetch('http://localhost:8000/api/supports', {
      method: 'POST',
      body: form.value
    })
    message.value = 'Gửi hỗ trợ thành công!'
    form.value = { name: '', email: '', phone: '', subject: '', content: '' }
  } catch (e) {
    message.value = 'Gửi thất bại, vui lòng thử lại.'
  }
  submitting.value = false
}
</script>

<style scoped>
/* Thêm CSS tùy chỉnh tại đây nếu cần */
</style>
