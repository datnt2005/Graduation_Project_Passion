<template>
  <div class="bg-gray-100 text-gray-800 font-sans min-h-screen px-6 py-6">
    <h1 class="text-2xl font-bold mb-4">Chi tiết thông báo</h1>

    <nuxt-link to="/admin/notifications/list-notifications" class="text-blue-600 text-sm hover:underline mb-4 block">
      ← Quay lại danh sách
    </nuxt-link>

    <div v-if="loading">Đang tải...</div>
    <div v-else-if="notification" class="bg-white shadow rounded p-6 space-y-4">
      <h2 class="text-xl font-semibold">{{ notification.title }}</h2>
      <p class="text-gray-600">{{ notification.content }}</p>

      <div class="text-sm text-gray-500">
        <p><strong>Loại:</strong> {{ notification.type }}</p>
        <p><strong>Vai trò nhận:</strong> {{ notification.to_role }}</p>
        <p><strong>Liên kết:</strong> <a :href="notification.link" target="_blank" class="text-blue-600 underline">{{ notification.link }}</a></p>
        <p><strong>Ngày gửi:</strong> {{ formatDate(notification.created_at) }}</p>
      </div>

      <div v-if="notification.image_url">
        <p class="font-medium text-gray-700">Ảnh thông báo:</p>
        <img :src="notification.image_url" class="w-40 h-40 object-cover border rounded" />
      </div>
    </div>

    <div v-else class="text-red-600">Không tìm thấy thông báo</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRuntimeConfig } from '#app'
import axios from 'axios'
definePageMeta({
  layout: 'default-admin'
})
const route = useRoute()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const id = route.params.id

const notification = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notification.value = res.data
  } catch (err) {
    console.error('Lỗi khi tải chi tiết:', err)
  } finally {
    loading.value = false
  }
})

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleString('vi-VN')
}
</script>
