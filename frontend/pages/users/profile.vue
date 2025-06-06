<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="min-h-screen flex flex-col md:flex-row max-w-[1200px] mx-auto p-4 md:p-6">
      <!-- Sidebar -->
     <SidebarProfile />
 
      <!-- Main Content -->
      <main class="flex-grow mt-6 md:mt-0 md:ml-6">
        <h1 class="text-lg font-normal mb-6">Thông tin tài khoản</h1>
        <div class="bg-white rounded-lg p-4 md:p-6 flex flex-col md:flex-row md:space-x-6 max-w-full">
          <!-- Left form section -->
          <section class="flex-grow border-r border-[#e2e7f7] pr-0 md:pr-6 mb-6 md:mb-0">
            <h2 class="text-[#7f8fa4] mb-4 text-[15px] font-normal">Thông tin cá nhân</h2>
            <div class="flex flex-col sm:flex-row items-center mb-6 space-y-4 sm:space-y-0 sm:space-x-6">
              <div class="relative flex justify-center items-center w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-[#c7defa]">
                <img
                  id="avatarPreview"
                  :src="avatarUrl"
                  alt="Avatar"
                  class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover"
                />
                <label
                  for="avatarInput"
                  class="absolute bottom-0 right-0 bg-[#6b7280] rounded-full w-6 h-6 flex justify-center items-center text-white text-xs border border-white cursor-pointer hover:bg-[#5a626e] transition-colors"
                  aria-label="Change avatar"
                >
                  <font-awesome-icon :icon="['fas', 'pencil']" />
                </label>
                <input
                  type="file"
                  id="avatarInput"
                  accept="image/*"
                  class="hidden"
                  @change="previewAvatar"
                />
              </div>
              <div class="flex flex-col space-y-4 w-full max-w-md">
                <label class="text-sm text-[#4a4a4a] font-normal mb-1" for="fullname">
                  Họ & Tên
                </label>
                <input
                  class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm text-[#1a1a1a] focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                  id="fullname"
                  type="text"
                  v-model="fullname"
                />
               
              </div>
            </div>
            <form class="space-y-5 max-w-md" @submit.prevent="savePersonalInfo">
              <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <label class="text-sm text-[#4a4a4a] font-normal w-24" for="dob-day">
                  Ngày sinh
                </label>
                <div class="flex space-x-2 w-full">
                  <select
                    v-model="selectDay"
                    class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm text-[#1a1a1a] focus:outline-none focus:ring-2 focus:ring-[#2a6adf] flex-1"
                    id="dob-day"
                  >
                    <option disabled value="">Ngày</option>
                    <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
                  </select>
                  <select
                    v-model="selectMonth"
                    class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm text-[#1a1a1a] focus:outline-none focus:ring-2 focus:ring-[#2a6adf] flex-1"
                    id="dob-month"
                  >
                    <option disabled value="">Tháng</option>
                    <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
                  </select>
                  <select
                    v-model="selectYear"
                    class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm text-[#1a1a1a] focus:outline-none focus:ring-2 focus:ring-[#2a6adf] flex-1"
                    id="dob-year"
                  >
                    <option disabled value="">Năm</option>
                    <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                  </select>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <label class="text-sm text-[#4a4a4a] font-normal w-24">Giới tính</label>
                <div class="flex space-x-4">
                  <label class="inline-flex items-center space-x-1 text-sm text-[#4a4a4a] font-normal">
                    <input
                      class="form-radio text-[#2a6adf]"
                      name="gender"
                      type="radio"
                      value="nam"
                      v-model="gender"
                    />
                    <span>Nam</span>
                  </label>
                  <label class="inline-flex items-center space-x-1 text-sm text-[#4a4a4a] font-normal">
                    <input
                      class="form-radio text-[#2a6adf]"
                      name="gender"
                      type="radio"
                      value="nu"
                      v-model="gender"
                    />
                    <span>Nữ</span>
                  </label>
                  <label class="inline-flex items-center space-x-1 text-sm text-[#4a4a4a] font-normal">
                    <input
                      class="form-radio text-[#2a6adf]"
                      name="gender"
                      type="radio"
                      value="khac"
                      v-model="gender"
                    />
                    <span>Khác</span>
                  </label>
                </div>
              </div>
              <div class="flex justify-center">
                <button
                  class="bg-[#0a6efd] text-white rounded-md px-6 py-2 text-sm font-semibold hover:bg-[#095cd9] focus:outline-none focus:ring-2 focus:ring-[#0a6efd] transition-colors"
                  type="submit"
                >
                  Lưu thay đổi
                </button>
              </div>
            </form>
          </section>
          <!-- Right info section -->
          <section class="flex-shrink-0 w-full md:w-80 pl-0 md:pl-6 border-t md:border-t-0 border-[#e2e7f7] pt-6 md:pt-0">
            <div class="space-y-6 text-[16px] text-[#4a4a4a]">
              <form class="space-y-6 max-w-md mx-auto p-4 border rounded-lg shadow" @submit.prevent="saveContactInfo">
                <h3 class="text-[#7f8fa4] font-semibold text-lg">Thông tin liên hệ & bảo mật</h3>
                <div>
                  <label class="block text-sm text-[#7f8fa4] mb-1" for="phone">Số điện thoại</label>
                  <div class="flex items-center space-x-2">
                    <font-awesome-icon icon="phone-alt" class="text-[#7f8fa4]" />
                    <input
                      id="phone"
                      name="phone"
                      type="tel"
                      v-model="phone"
                      class="border rounded px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                      required
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-sm text-[#7f8fa4] mb-1" for="email">Địa chỉ email</label>
                  <div class="flex items-center space-x-2">
                    <font-awesome-icon icon="envelope" class="text-[#7f8fa4]" />
                    <input
                      id="email"
                      name="email"
                      type="email"
                      v-model="email"
                      class="border rounded px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                      required
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-sm text-[#7f8fa4] mb-1" for="password">Mật khẩu mới</label>
                  <div class="flex items-center space-x-2">
                    <font-awesome-icon icon="lock" class="text-[#7f8fa4]" />
                    <input
                      id="password"
                      name="password"
                      type="password"
                      v-model="password"
                      class="border rounded px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                      required
                    />
                  </div>
                </div>
                <div class="flex justify-center">
                  <button
                    class="bg-[#0a6efd] text-white rounded-md px-6 py-2 text-sm font-semibold hover:bg-[#095cd9] focus:outline-none focus:ring-2 focus:ring-[#0a6efd] transition-colors"
                    type="submit"
                  >
                    Lưu thay đổi
                  </button>
                </div>
              </form>
              <div>
                <h3 class="text-[#7f8fa4] mb-3 font-normal">Liên kết mạng xã hội</h3>
                <div class="flex justify-between items-center mb-3">
                  <div class="flex items-center space-x-2 max-w-[180px]">
                    <img
                      alt="Facebook icon"
                      class="w-4 h-4"
                      src="https://storage.googleapis.com/a1aa/image/b4f3133b-b81f-4a37-bc66-7d327973744e.jpg"
                    />
                    <div>Facebook</div>
                  </div>
                  <button
                    class="text-[#0a6efd] border border-[#0a6efd] rounded px-3 py-1 text-xs hover:bg-[#e6f0ff] transition-colors"
                    type="button"
                    @click="linkSocial('facebook')"
                    aria-label="Link Facebook account"
                  >
                    Liên kết
                  </button>
                </div>
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-2 max-w-[180px]">
                    <img
                      alt="Google icon"
                      class="w-4 h-4"
                      src="https://storage.googleapis.com/a1aa/image/18eb76f1-7aca-4192-4386-a4d663c49845.jpg"
                    />
                    <div>Google</div>
                  </div>
                  <button
                    class="bg-[#e2e7f7] text-[#7f8fa4] rounded px-3 py-1 text-xs cursor-default"
                    disabled
                    type="button"
                    aria-disabled="true"
                    aria-label="Google account already linked"
                  >
                    Đã liên kết
                  </button>
                </div>
              </div>
            </div>
          </section>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import SidebarProfile from '~/components/shared/Sidebar-profile.vue'

const route = useRoute()
const userId = route.params.id



// Personal info
const fullname = ref('Châu Bùi Văn')
const nickname = ref('')
const selectDay = ref('')
const selectMonth = ref('')
const selectYear = ref('')
const gender = ref('')

// Contact info
const phone = ref('0336841037')
const email = ref('chaubvpk03815@gmail.com')
const password = ref('')

// Avatar preview
const avatarUrl = ref('https://storage.googleapis.com/a1aa/image/f00d4e2b-1854-4d46-444c-e0476373e616.jpg')
const previewAvatar = (event) => {
  const file = event.target.files[0]
  if (file) {
    avatarUrl.value = URL.createObjectURL(file)
  }
}

// Form submission
const savePersonalInfo = () => {
  // Validate and save personal info
  if (!selectDay.value || !selectMonth.value || !selectYear.value) {
    alert('Vui lòng chọn đầy đủ ngày sinh')
    return
  }
  console.log('Saving personal info:', {
    fullname: fullname.value,
    nickname: nickname.value,
    dob: `${selectDay.value}/${selectMonth.value}/${selectYear.value}`,
    gender: gender.value
  })
  // Gửi API hoặc xử lý lưu dữ liệu tại đây
}

const saveContactInfo = () => {
  // Validate and save contact info
  if (!phone.value || !email.value) {
    alert('Vui lòng nhập số điện thoại và email')
    return
  }
  console.log('Saving contact info:', {
    phone: phone.value,
    email: email.value,
    password: password.value
  })
  // Gửi API hoặc xử lý lưu dữ liệu tại đây
}

const linkSocial = (platform) => {
  console.log(`Linking ${platform} account`)
  // Xử lý liên kết mạng xã hội tại đây
}

// Date options
const days = Array.from({ length: 31 }, (_, i) => i + 1)
const months = Array.from({ length: 12 }, (_, i) => i + 1)
const currentYear = new Date().getFullYear()
const years = Array.from({ length: 100 }, (_, i) => currentYear - i)
</script>

<style scoped>
/* Custom styles for additional responsiveness */
@media (max-width: 640px) {
  .flex-col.sm\:flex-row {
    flex-direction: column;
  }
  .w-24.sm\:w-24 {
    width: 5rem;
    height: 5rem;
  }
  .w-14.sm\:w-16 {
    width: 3.5rem;
    height: 3.5rem;
  }
  select {
    font-size: 0.875rem;
    padding: 0.5rem;
  }
  input {
    font-size: 0.875rem;
  }
  button {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
  }
}
</style>