<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#eaf1fd] to-[#f4f7fd] px-2 py-6">
    <div class="max-w-9xl w-full flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-[0_8px_40px_0_rgba(22,61,124,.10)] border border-gray-200 bg-white mx-auto">
      <!-- Bên trái: Branding -->
      <div class="hidden md:flex flex-col justify-center items-center w-1/2 p-12 bg-transparent">
        <div class="mb-10 flex flex-col items-center">
             <img src="/images/SellerCenter2.png" alt="">
        </div>
        <div class="text-2xl font-bold text-gray-900 mb-2 text-center">
          Đăng ký bán hàng cùng <strong class="text-blue-600 font-extrabold">Passion</strong>
        </div>
        <div class="text-gray-500 text-base text-center max-w-[300px]">Tham gia cộng đồng bán hàng lớn nhất Việt Nam.<br>Tiếp cận hàng triệu khách hàng tiềm năng.</div>
      </div>

      <!-- Bên phải: Form -->
      <div class="w-full md:w-1/2 flex flex-col justify-center p-8 sm:p-16 bg-white">
       <div class="mx-auto w-full md:w-[80%]">
          <div class="mb-6">
            <div class="text-3xl font-bold text-gray-900 mb-1">Đăng ký ngay</div>
            <div class="text-gray-500 text-base">Tạo tài khoản bán hàng của bạn</div>
          </div>
          <!-- Option cá nhân/doanh nghiệp -->
          <div class="grid grid-cols-2 gap-2 mb-6 rounded-[12px] bg-[#f3f6fa] p-1.5">
            <button
              type="button"
              class="py-2 rounded-[10px] font-semibold text-base transition-all duration-200 shadow-sm"
              :class="sellerType === 'personal' 
                ? 'bg-white text-blue-600 shadow border border-blue-500'
                : 'bg-transparent text-gray-700 border border-transparent hover:bg-white'"
              @click="sellerType = 'personal'">
              <span class="inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/><path d="M12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/></svg>
                Cá nhân/Nhỏ lẻ
              </span>
            </button>
            <button
              type="button"
              class="py-2 rounded-[10px] font-semibold text-base transition-all duration-200 shadow-sm"
              :class="sellerType === 'business' 
                ? 'bg-white text-blue-600 shadow border border-blue-500'
                : 'bg-transparent text-gray-700 border border-transparent hover:bg-white'"
              @click="sellerType = 'business'">
              <span class="inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14"/><path d="M9 21V9h6v12"/></svg>
                Doanh nghiệp
              </span>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-3">
            <!-- Email -->
            <div>
                
              <label class="block font-semibold text-sm mb-1.5 text-gray-700"><i class="fa-regular fa-envelope"></i> Địa chỉ email</label>
              <input type="email"
                class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                v-model="form.email"
                :class="{'border-red-500': emailError}"
                placeholder="Nhập địa chỉ email" required>
              <p v-if="emailError" class="text-sm text-red-600 mt-1.5 flex items-center">
                <svg class="w-4 h-4 mr-1 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Email chưa đúng định dạng.
              </p>
            </div>
            <!-- Grid input 2 cột -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Họ và tên</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.full_name"
                  placeholder="Nhập họ tên" required>
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700"><i class="fa-solid fa-phone"></i> Số điện thoại</label>
                <div class="flex">
                  <span class="inline-flex items-center px-3 rounded-l-[9px] border border-r-0 border-gray-200 bg-gray-100 text-gray-700 text-base">+84</span>
                  <input type="text"
                    class="w-full border border-gray-200 rounded-r-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.phone"
                    placeholder="Nhập số điện thoại" required>
                </div>
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Ngành hàng chủ lực</label>
                <select class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.industry" required>
                  <option value="">Chọn ngành hàng</option>
                  <option value="books">Sách & Văn phòng phẩm</option>
                  <option value="fashion">Thời trang</option>
                  <option value="electronics">Điện tử</option>
                  <option value="home">Nhà cửa & Đời sống</option>
                </select>
              </div>
              <template v-if="sellerType === 'personal'">
                <div>
                  <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số CCCD/CMND</label>
                  <input type="text"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.identity_card_number"
                    placeholder="Nhập số CCCD/CMND" required>
                </div>
              </template>
              <template v-else>
                <div>
                  <label class="block font-semibold text-sm mb-1.5 text-gray-700"><i class="fa-regular fa-building"></i> Tên công ty</label>
                  <input type="text"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.company_name"
                    placeholder="Nhập tên công ty" required>
                </div>
              </template>
              <template v-if="sellerType === 'personal'">
                <div>
                  <label class="block font-semibold text-sm mb-1.5 text-gray-700"><i class="fa-regular fa-calendar"></i> Ngày sinh</label>
                  <input type="date"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.date_of_birth" required>
                </div>
                <div>
                  <label class="block font-semibold text-sm mb-1.5 text-gray-700"> <i class="fa-solid fa-location-dot"></i> Địa chỉ cá nhân</label>
                  <input type="text"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.personal_address"
                    placeholder="Nhập địa chỉ cá nhân">
                </div>
              </template>
              <template v-else>
                <div>
                  <label class="block font-semibold text-sm mb-1.5 text-gray-700">Mã số thuế</label>
                  <input type="text"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.tax_code"
                    placeholder="Nhập mã số thuế" required>
                </div>
                <div>
                  <label class="block font-semibold text-sm mb-1.5 text-gray-700"><i class="fa-solid fa-location-dot"></i> Địa chỉ công ty</label>
                  <input type="text"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.company_address"
                    placeholder="Nhập địa chỉ công ty">
                </div>
              </template>
              <!-- Mật khẩu + xác nhận -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Mật khẩu</label>
                <div class="relative">
                  <input :type="showPassword ? 'text' : 'password'"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.password"
                    placeholder="Nhập mật khẩu" required>
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-3 text-gray-400 hover:text-gray-700 focus:outline-none">
                    <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Xác nhận mật khẩu</label>
                <div class="relative">
                  <input :type="showPassword2 ? 'text' : 'password'"
                    class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                    v-model="form.password_confirmation"
                    placeholder="Xác nhận mật khẩu" required>
                  <button type="button" @click="showPassword2 = !showPassword2"
                    class="absolute right-3 top-3 text-gray-400 hover:text-gray-700 focus:outline-none">
                    <svg v-if="showPassword2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
             <button
                type="submit"
                :disabled="loading"
                class="w-full text-white font-bold py-3 rounded-[11px] mt-2 text-lg transition-all duration-300 shadow disabled:opacity-60 disabled:cursor-not-allowed"
                :style="{ background: loading ? '#1BA0E2CC' : (isHover ? '#1780B6' : '#1BA0E2') }"
                @mouseenter="isHover = true"
                @mouseleave="isHover = false"
            >
                <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                {{ loading ? 'Đang đăng ký...' : 'Đăng ký ngay' }}
            </button>
          </form>
          <div class="text-center mt-5 text-base">
            Đã có tài khoản bán hàng?
            <NuxtLink href="/seller/login" class="text-blue-600 hover:underline font-semibold">Đăng nhập</NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const sellerType = ref('personal')
const form = ref({
  email: '',
  full_name: '',
  phone: '',
  industry: '',
  identity_card_number: '',
  date_of_birth: '',
  personal_address: '',
  company_name: '',
  tax_code: '',
  company_address: '',
  password: '',
  password_confirmation: '',
})
const showPassword = ref(false)
const showPassword2 = ref(false)
const loading = ref(false)
const emailError = ref(false)
const isHover = ref(false)

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

async function handleSubmit() {
  emailError.value = false
  if (!validateEmail(form.value.email)) {
    emailError.value = true
    return
  }
  loading.value = true
  setTimeout(() => {
    loading.value = false
    alert('Đăng ký thành công!')
  }, 800)
}
</script>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}
</style>

