<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">{{ title }}</h1>
      </div>

      <!-- Table & content giống list-notifications.vue -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm mt-4 bg-white">
        <thead>
          <tr>
            <th class="border px-3 py-2 text-left font-semibold">ID</th>
            <th class="border px-3 py-2 text-left font-semibold">Tiêu đề</th>
            <th class="border px-3 py-2 text-left font-semibold">Người gửi</th>
            <th class="border px-3 py-2 text-left font-semibold">Ngày tạo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in notifications" :key="item.id">
            <td class="px-3 py-2">{{ item.id }}</td>
            <td class="px-3 py-2">{{ item.title }}</td>
            <td class="px-3 py-2">{{ item.user?.name || '---' }}</td>
            <td class="px-3 py-2">{{ formatDate(item.created_at) }}</td>
          </tr>
          <tr v-if="notifications.length === 0">
            <td colspan="4" class="text-center py-4 text-gray-500">Không có thông báo</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRuntimeConfig } from '#app'
definePageMeta({ layout: 'default-admin' })
const props = defineProps({
  title: String,
  filterRole: String
})
const notifications = ref([])
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN')
}

const fetchData = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/notifications?from_role=${props.filterRole}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notifications.value = res.data.data || res.data
  } catch (err) {
    console.error('Lỗi khi tải thông báo:', err)
  }
}

onMounted(() => {
  fetchData()
})
</script>
