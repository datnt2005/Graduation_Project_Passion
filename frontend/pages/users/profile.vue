<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a] min-h-screen">
    <div class="flex flex-col md:flex-row max-w-[1200px] mx-auto p-4 md:p-6">
      <!-- Sidebar -->
      <SidebarProfile />

      <!-- Main Content -->
      <main class="flex-grow mt-6 md:mt-0 md:ml-6">
        <h1 class="text-xl font-bold mb-6">Thông tin tài khoản</h1>
        <div class="bg-white rounded-lg p-4 md:p-8 flex flex-col md:flex-row md:space-x-8 shadow border border-gray-100">
          <!-- Thông tin cá nhân -->
          <section class="flex-grow border-b md:border-b-0 md:border-r border-[#e2e7f7] pr-0 md:pr-8 mb-8 md:mb-0">
            <h2 class="text-[#7f8fa4] mb-5 text-base font-semibold">Thông tin cá nhân</h2>
            <div class="flex flex-col sm:flex-row items-center mb-7 gap-6">
              <div class="relative flex justify-center items-center w-24 h-24 rounded-full border-4 border-[#c7defa] bg-white">
                <img
                  id="avatarPreview"
                  :src="avatarUrl"
                  alt="Avatar"
                  class="w-16 h-16 rounded-full object-cover"
                />
                <label
                  for="avatarInput"
                  class="absolute bottom-0 right-0 bg-[#2a6adf] rounded-full w-7 h-7 flex justify-center items-center text-white text-xs border-2 border-white cursor-pointer hover:bg-[#095cd9] transition-colors"
                  aria-label="Change avatar"
                  title="Đổi ảnh đại diện"
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
              <div class="flex flex-col space-y-3 w-full max-w-md">
                <label class="text-sm font-medium mb-1" for="fullname">Họ & Tên</label>
                <input
                  class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                  id="fullname"
                  type="text"
                  v-model="fullname"
                />
              </div>
            </div>
            <form class="space-y-5 max-w-md" @submit.prevent="savePersonalInfo">
              <div>
                <label class="text-sm font-medium mb-1" for="email">Địa chỉ Email</label>
                <input
                  class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                  id="email"
                  type="email"
                  v-model="email"
                  required
                />
              </div>
              <div>
                <label class="text-sm font-medium mb-1" for="phone">Số điện thoại</label>
                <input
                  class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                  id="phone"
                  type="tel"
                  v-model="phone"
                  required
                />
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

          <!-- Thông tin phụ + Đổi mật khẩu -->
          <section class="flex-shrink-0 w-full md:w-80 pl-0 md:pl-8 pt-8 md:pt-0">
            <div class="space-y-6 text-base">
              <div class="p-4 border rounded-lg shadow bg-gray-50 mb-8">
                <h3 class="text-[#7f8fa4] font-semibold text-lg mb-2">Thông tin hệ thống</h3>
                <div class="flex items-center mb-2">
                  <span class="w-28 text-[#7f8fa4] font-normal">Vai trò:</span>
                  <span class="font-medium capitalize">{{ role }}</span>
                </div>
                <div class="flex items-center mb-2">
                  <span class="w-28 text-[#7f8fa4] font-normal">Trạng thái:</span>
                  <span class="font-medium capitalize">{{ status }}</span>
                </div>
                <div class="flex items-center">
                  <span class="w-28 text-[#7f8fa4] font-normal">Đăng nhập lúc:</span>
                  <span class="font-medium">{{ loggedInAt }}</span>
                </div>
              </div>
              <!-- Form đổi mật khẩu -->
              <form class="space-y-4 p-4 border rounded-lg shadow bg-gray-50" @submit.prevent="changePassword">
                <h3 class="text-[#7f8fa4] font-semibold text-lg mb-2">Đổi mật khẩu</h3>
                <div>
                  <label class="block text-sm font-medium mb-1" for="newPassword">Mật khẩu mới</label>
                  <input
                    class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                    id="newPassword"
                    type="password"
                    v-model="newPassword"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1" for="confirmPassword">Xác nhận mật khẩu</label>
                  <input
                    class="border border-[#d1d5db] rounded-md px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[#2a6adf]"
                    id="confirmPassword"
                    type="password"
                    v-model="confirmPassword"
                    required
                  />
                </div>
                <div class="flex justify-center">
                  <button
                    class="bg-[#2a6adf] text-white rounded-md px-6 py-2 text-sm font-semibold hover:bg-[#095cd9] focus:outline-none focus:ring-2 focus:ring-[#2a6adf] transition-colors"
                    type="submit"
                  >
                    Đổi mật khẩu
                  </button>
                </div>
              </form>
            </div>
          </section>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SidebarProfile from '~/components/shared/Sidebar-profile.vue'

// Thông tin cá nhân
const avatarUrl = ref('https://www.pngmart.com/files/22/User-Avatar-Profile-PNG.png')
const fullname = ref('')
const email = ref('')
const phone = ref('')
const role = ref('')
const status = ref('')
const loggedInAt = ref('')

// Đổi mật khẩu
const newPassword = ref('')
const confirmPassword = ref('')

// Tải thông tin user khi vào trang
onMounted(async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return
  const res = await fetch('http://localhost:8000/api/me', {
    headers: { Authorization: `Bearer ${token}` }
  })
  const json = await res.json()
  if (json && json.data) {
    avatarUrl.value = json.data.avatar || 'https://www.pngmart.com/files/22/User-Avatar-Profile-PNG.png'
    fullname.value = json.data.name || ''
    email.value = json.data.email || ''
    phone.value = json.data.phone || ''
    role.value = json.data.role || ''
    status.value = json.data.status || ''
    loggedInAt.value = json.data.logged_in_at || ''
  }
})

// Preview avatar (tuỳ logic của Daddy)
const previewAvatar = (e) => {
  const file = e.target.files[0]
  if (file) {
    avatarUrl.value = URL.createObjectURL(file)
    // Gọi hàm uploadAvatar nếu cần
  }
}

// Lưu thông tin cá nhân
const savePersonalInfo = () => {
  // Gọi API cập nhật user, truyền token
  // Xử lý logic cập nhật tên, email, phone ở đây
}

// Đổi mật khẩu
const changePassword = async () => {
  if (newPassword.value !== confirmPassword.value) {
    alert('Mật khẩu xác nhận không khớp!')
    return
  }
 
}
</script>
