<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Thêm phương thức thanh toán</h1>
  <div class="px-6 pb-4">
    <nuxt-link to="/admin/settings/list-paymentMethod" class="text-gray-600 hover:underline text-sm">
      Danh sách phương thức thanh toán
    </nuxt-link>
    <span class="text-gray-600 text-sm"> / Thêm phương thức</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-lg mx-auto">
        <form @submit.prevent="createMethod" class="space-y-6 bg-white p-6 rounded shadow border">
          <div>
            <label class="block text-sm text-gray-700 mb-1">Tên phương thức</label>
            <input v-model="form.name" type="text"
              class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Nhập tên phương thức (VD: Chuyển khoản, COD, MoMo)" />
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
          <div class="flex justify-end gap-2">
            <nuxt-link to="/admin/settings/list-paymentMethod"
              class="bg-gray-200 text-gray-700 rounded px-4 py-2 text-sm hover:bg-gray-300">Hủy</nuxt-link>
            <button type="submit"
              class="bg-blue-700 text-white rounded px-4 py-2 text-sm font-semibold hover:bg-blue-800 transition-colors"
              :disabled="loading">
              {{ loading ? 'Đang lưu...' : 'Thêm phương thức' }}
            </button>
          </div>
        </form>
        <div v-if="showNotification" class="mt-4 text-center">
          <span :class="notificationType === 'success' ? 'text-green-600' : 'text-red-600'">{{ notificationMessage }}</span>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()
const form = ref({
  name: '',
  status: 'active'
})
const errors = ref({})
const loading = ref(false)
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')

const createMethod = async () => {
  errors.value = {}
  if (!form.value.name.trim()) {
    errors.value.name = 'Tên phương thức là bắt buộc.'
    return
  }
  loading.value = true
  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBaseUrl // sẽ là http://localhost:8000/api
    const res = await fetch(`${apiBase}/payment-methods`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(form.value)
    })
    const data = await res.json()
    if (res.ok) {
      notificationType.value = 'success'
      notificationMessage.value = 'Thêm phương thức thành công!'
      showNotification.value = true
      setTimeout(() => router.push('/admin/settings/list-paymentMethod'), 1200)
    } else {
      notificationType.value = 'error'
      notificationMessage.value = data.message || 'Có lỗi xảy ra.'
      if (data.errors) errors.value = data.errors
      showNotification.value = true
    }
  } catch (e) {
    notificationType.value = 'error'
    notificationMessage.value = 'Không thể kết nối máy chủ.'
    showNotification.value = true
  } finally {
    loading.value = false
  }
}
definePageMeta({
  layout: 'default-admin'
});

</script>