<template>
  <div class="bg-gray-100 text-gray-800 font-sans min-h-screen px-6 py-6">
    <!-- Heading -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Chi tiết thông báo</h1>
      <NuxtLink to="/admin/notifications/list-notifications"
        class="inline-block mt-2 text-sm text-blue-600 hover:underline">
        ← Quay lại danh sách
      </NuxtLink>
    </div>

    <!-- Loading / Error / Content -->
    <div v-if="loading" class="text-gray-500">Đang tải thông tin...</div>

    <div v-else-if="notification" class="bg-white rounded-lg shadow-md p-6 space-y-6">
      <!-- Title -->
      <h2 class="text-xl font-semibold text-gray-900">{{ notification.title }}</h2>

      <!-- Image -->
      <div v-if="notification.image_url" class="border rounded overflow-hidden w-48 h-48">
        <img :src="notification.image_url" alt="Ảnh thông báo" class="w-full h-full object-cover" />
      </div>

      <!-- Content -->
      <div>
        <p class="text-gray-700 whitespace-pre-line">{{ notification.content }}</p>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
        <div>
          <span class="font-medium text-gray-800">Loại:</span> {{ notification.type }}
        </div>
        <div>
          <span class="font-medium text-gray-800">Vai trò nhận:</span> {{ notification.to_role }}
        </div>
        <div>
          <span class="font-medium text-gray-800">Liên kết:</span>
          <a :href="notification.link" target="_blank" class="text-blue-600 hover:underline break-all">
            {{ notification.link }}
          </a>
        </div>
        <div>
          <span class="font-medium text-gray-800">Ngày gửi:</span> {{ formatDate(notification.created_at) }}
        </div>
        <div>
          <span class="font-medium text-gray-800">Tình trạng:</span>
          <span
            :class="notification.status === 'sent' ? 'text-green-700 bg-green-100' : 'text-yellow-700 bg-yellow-100'"
            class="px-2 py-1 rounded text-xs font-semibold">
            {{ notification.status === 'sent' ? 'Đã gửi' : 'Lưu nháp' }}
          </span>
        </div>
        <div>
          <span class="font-medium text-gray-800">Trạng thái đọc:</span>
          <span
            :class="notification.is_read == 1 ? 'text-green-700 bg-green-100' : 'text-yellow-700 bg-yellow-100'"
            class="px-2 py-1 rounded text-xs font-semibold">
            {{ notification.is_read == 1 ? 'Đã đọc' : 'Chưa đọc' }}
          </span>
        </div>
      </div>
    </div>

    <div v-else class="text-red-600">Không tìm thấy thông báo.</div>
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
  return d.toLocaleString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
