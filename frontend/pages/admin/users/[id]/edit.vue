<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Cập nhật người dùng</h1>
    <div class="px-6 pb-4">
      <nuxt-link to="/admin/users/list-user" class="text-gray-600 hover:underline text-sm">
        Người dùng
      </nuxt-link>
      <span class="text-gray-600 text-sm"> / Cập nhật người dùng</span>
    </div>

    <div class="flex min-h-screen bg-gray-100">
      <!-- Sidebar -->
      <nav class="w-64 bg-white border-r border-gray-200">
        <ul class="py-2">
          <li>
            <button @click="activeTab = 'overview'" :class="[
              'flex items-center w-full px-4 py-2 text-sm transition-colors',
              activeTab === 'overview' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              Tổng quan
            </button>
          </li>
        </ul>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 p-6 bg-gray-100">
        <div class="max-w-[1200px] mx-auto">
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <!-- Info Section -->
              <section class="space-y-4">
                <div class="space-y-2">
                  <div>
                    <label for="user-name" class="block text-sm text-gray-700 mb-1">Họ và tên</label>
                    <input id="user-name" v-model="formData.name" type="text"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập họ và tên" />
                    <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-email" class="block text-sm text-gray-700 mb-1">Email</label>
                    <input id="user-email" v-model="formData.email" type="email"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập email" disabled />
                  </div>
               <div class="mt-4">
                  <label for="user-password" class="block text-sm text-gray-700 mb-1">Đổi mật khẩu (bỏ trống nếu không đổi)</label>
                  <input id="user-password" v-model="formData.password" type="password"
                    class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    autocomplete="new-password"
                    placeholder="Nhập mật khẩu mới" />
                  <span v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</span>
                </div>
                  <!-- Hiện trường old_password khi nhập password mới -->
                  <div class="mt-4">
                    <label for="user-phone" class="block text-sm text-gray-700 mb-1">Số điện thoại</label>
                    <input id="user-phone" v-model="formData.phone" type="text"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập số điện thoại" />
                    <span v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-role" class="block text-sm text-gray-700 mb-1">Phân quyền</label>
                    <select id="user-role" v-model="formData.role"
                      class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                      <option value="">Chọn quyền</option>
                      <option value="admin">Quản trị viên</option>
                      <option value="user">Người dùng</option>
                      <option value="seller">Seller</option>
                    </select>
                    <span v-if="errors.role" class="text-red-500 text-xs mt-1">{{ errors.role }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-status" class="block text-sm text-gray-700 mb-1">Trạng thái</label>
                    <select id="user-status" v-model="formData.status"
                      class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                      <option value="active">Kích hoạt</option>
                      <option value="inactive">Không kích hoạt</option>
                      <option value="banned">Khóa</option>
                    </select>
                  </div>
                </div>
              </section>

              <!-- Sidebar Avatar & Button -->
              <aside class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3 max-w-[320px]">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                  <h2 class="font-semibold">Ảnh đại diện (có thể để trống)</h2>
                </header>
                <div v-if="activeTab === 'overview'" class="space-y-3">
                  <div
                    class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                    @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
                    <input ref="fileInput" id="user-avatar" type="file" accept="image/*" class="hidden"
                      @change="handleImageUpload" />
                    <div class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span>
                      </p>
                    </div>
                  </div>
                  <span v-if="errors.avatar" class="text-red-500 text-xs mt-1 block">{{ errors.avatar }}</span>
                  <div v-if="imagePreview" class="mt-3">
                    <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-full border mx-auto" />
                  </div>
                  <div v-else-if="formData.avatar_url" class="mt-3">
                    <img :src="formData.avatar_url" alt="Avatar cũ" class="w-32 h-32 object-cover rounded-full border mx-auto" />
                  </div>
                </div>
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <button type="submit"
                    class="w-full bg-blue-600 text-white text-sm font-semibold rounded px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="loading">
                    {{ loading ? 'Đang xử lý...' : 'Lưu thay đổi' }}
                  </button>
                </div>
                <div v-if="success" class="text-green-600 text-xs text-center mt-2">Cập nhật thành công!</div>
                <div v-if="errorMsg" class="text-red-600 text-xs text-center mt-2">{{ errorMsg }}</div>
              </aside>
            </div>
          </form>
        </div>
      </main>
    </div>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

definePageMeta({ layout: 'default-admin' })

const router = useRouter()
const route = useRoute()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const userId = route.params.id
const activeTab = ref('overview')
const loading = ref(false)
const errors = reactive({})
const showNotification = ref(false)
const notificationMessage = ref('')
const success = ref(false)
const errorMsg = ref('')
const imagePreview = ref(null)
const fileInput = ref(null)

const formData = reactive({
  name: '',
  email: '',
  password: '',
  phone: '',
  role: '',
  status: 'active',
  avatar: null,
  avatar_url: null,
  _method: 'PUT',
})

const fetchUser = async () => {
  try {
    const res = await fetch(`${apiBase}/users/${userId}`)
    const data = await res.json()
    if (data && data.data) {
      Object.assign(formData, {
        ...data.data,
        password: '',
        avatar: null,
        avatar_url: data.data.avatar_url || null
      })
    }
  } catch (e) {
    errorMsg.value = 'Không lấy được thông tin user!'
  }
}
onMounted(fetchUser)

const triggerFileInput = () => {
  fileInput.value && fileInput.value.click()
}
const handleDrop = (event) => {
  const file = event.dataTransfer.files[0]
  if (file) handleImageUpload({ target: { files: [file] } })
}
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    formData.avatar = file
    imagePreview.value = URL.createObjectURL(file)
  } else {
    formData.avatar = null
    imagePreview.value = null
  }
}

const handleSubmit = async () => {
  Object.keys(errors).forEach((k) => errors[k] = '');
  errorMsg.value = '';
  success.value = false;
  loading.value = true;
  try {
    const payload = new FormData();
    const fieldsToSend = ['name', 'password', 'phone', 'role', 'status', 'avatar'];
    fieldsToSend.forEach((key) => {
      if (key === 'avatar' && !formData.avatar) return;
      if (key === 'password' && !formData.password) return;
      if (formData[key] === undefined || formData[key] === null) return;
      payload.append(key, formData[key]);
    });

    payload.append('_method', 'PATCH');

    for (const pair of payload.entries()) {
      console.log('FormData gửi lên:', pair[0], pair[1]);
    }

    const res = await fetch(`${apiBase}/users/${userId}`, {
      method: 'POST',  
      body: payload,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await res.json();
    if ((res.ok && data.data) || data.success) {
      success.value = true;
      showSuccessNotification('Cập nhật người dùng thành công!');
      setTimeout(() => router.push('/admin/users/list-user'), 1000);
    } else if (data.errors) {
      Object.assign(errors, data.errors);
      errorMsg.value = data.message || 'Dữ liệu không hợp lệ.';
    } else {
      errorMsg.value = data.error || 'Cập nhật thất bại!';
    }
  } catch (e) {
    errorMsg.value = 'Lỗi kết nối máy chủ!';
  } finally {
    loading.value = false;
  }
};



const showSuccessNotification = (message) => {
  notificationMessage.value = message
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 2000)
}
</script>

