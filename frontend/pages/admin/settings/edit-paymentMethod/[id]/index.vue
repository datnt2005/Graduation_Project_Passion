<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Cập nhật phương thức thanh toán</h1>
  <div class="px-6 pb-4">
    <nuxt-link to="/admin/settings/list-paymentMethod" class="text-gray-600 hover:underline text-sm">
      Danh sách phương thức thanh toán
    </nuxt-link>
    <span class="text-gray-600 text-sm"> / Cập nhật phương thức</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-lg mx-auto">
        <form @submit.prevent="updateMethod" class="space-y-6 bg-white p-6 rounded shadow border">
          <div>
            <label class="block text-sm text-gray-700 mb-1">Tên phương thức</label>
            <input v-model="form.name" type="text"
              class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Nhập tên phương thức" />
            <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Trạng thái</label>
            <select v-model="form.status"
              class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option value="active">Đang hoạt động</option>
              <option value="inactive">Ngừng hoạt động</option>
            </select>
            <span v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</span>
          </div>
          <div class="flex justify-between gap-2">
            <button type="button" @click="confirmDelete"
              class="bg-red-600 text-white rounded px-4 py-2 text-sm hover:bg-red-700 transition-colors">
              Xóa
            </button>
            <div class="flex gap-2">
              <nuxt-link to="/admin/settings/list-paymentMethod"
                class="bg-gray-200 text-gray-700 rounded px-4 py-2 text-sm hover:bg-gray-300">Hủy</nuxt-link>
              <button type="submit"
                class="bg-blue-700 text-white rounded px-4 py-2 text-sm font-semibold hover:bg-blue-800 transition-colors"
                :disabled="loading">
                {{ loading ? 'Đang lưu...' : 'Cập nhật' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- Notification Popup giống list-paymentMethod -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showNotification"
        class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
      >
        <div class="flex-shrink-0">
          <svg
            class="h-6 w-6"
            :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              v-if="notificationType === 'success'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
            <path
              v-if="notificationType === 'error'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">
            {{ notificationMessage }}
          </p>
        </div>
        <div class="flex-shrink-0">
          <button
            @click="showNotification = false"
            class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useRuntimeConfig } from '#imports'

const router = useRouter()
const route = useRoute()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const form = ref({ name: '', status: 'active' })
const errors = ref({})
const loading = ref(false)
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
definePageMeta({
  layout: 'default-admin'
});

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 3000)
}

const fetchMethod = async () => {
  loading.value = true
  try {
    const res = await fetch(`${apiBase}/payment-methods/${route.params.id}`)
    const data = await res.json()
    if (res.ok && data.data) {
      form.value = { name: data.data.name, status: data.data.status }
    } else {
      showNotificationMessage('Không tìm thấy phương thức.', 'error')
      setTimeout(() => router.push('/admin/settings/list-paymentMethod'), 1200)
    }
  } catch (e) {
    showNotificationMessage('Không thể kết nối máy chủ.', 'error')
    setTimeout(() => router.push('/admin/settings/list-paymentMethod'), 1200)
  } finally {
    loading.value = false
  }
}

const updateMethod = async () => {
  errors.value = {}
  if (!form.value.name.trim()) {
    errors.value.name = 'Tên phương thức là bắt buộc.'
    return
  }
  loading.value = true
  try {
    const res = await fetch(`${apiBase}/payment-methods/${route.params.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(form.value)
    })
    const data = await res.json()
    if (res.ok) {
      showNotificationMessage('Cập nhật thành công!', 'success')
      setTimeout(() => router.push('/admin/settings/list-paymentMethod'), 1200)
    } else {
      showNotificationMessage(data.message || 'Có lỗi xảy ra.', 'error')
      if (data.errors) errors.value = data.errors
    }
  } catch (e) {
    showNotificationMessage('Không thể kết nối máy chủ.', 'error')
  } finally {
    loading.value = false
  }
}

const confirmDelete = async () => {
  if (!confirm('Bạn có chắc chắn muốn xóa phương thức này?')) return
  loading.value = true
  try {
    const res = await fetch(`${apiBase}/payment-methods/${route.params.id}`, { method: 'DELETE' })
    if (res.ok) {
      showNotificationMessage('Đã xóa phương thức!', 'success')
      setTimeout(() => router.push('/admin/settings/list-paymentMethod'), 1200)
    } else {
      const data = await res.json()
      showNotificationMessage(data.message || 'Không thể xóa phương thức.', 'error')
    }
  } catch (e) {
    showNotificationMessage('Không thể kết nối máy chủ.', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(fetchMethod)
</script>