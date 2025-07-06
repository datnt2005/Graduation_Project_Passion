<template>
  <div class="bg-gray-100 text-gray-800 font-sans min-h-screen">
    <!-- Breadcrumb -->
    <div class="px-6 pt-6">
      <h1 class="text-xl font-semibold text-gray-800">Chi tiết thông báo</h1>
    </div>
    <div class="px-6 pb-4">
      <NuxtLink to="/admin/notifications/list-notifications" class="text-gray-600 hover:underline text-sm">
        Danh sách thông báo
      </NuxtLink>
      <span class="text-gray-600 text-sm"> / Chi tiết thông báo</span>
    </div>

    <div class="flex">
      <!-- Sidebar trái -->
      <aside class="w-64 bg-white border-r border-gray-200 hidden lg:block">
        <ul class="py-2">
          <li>
            <button
              class="flex items-center w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              Quản lý thông báo
            </button>
          </li>
        </ul>
      </aside>

      <!-- Nội dung chính -->
      <main class="flex-1 p-6 bg-gray-100">
        <div class="max-w-5xl mx-auto bg-white rounded shadow border border-gray-200 p-6 space-y-6">
          <div v-if="loading" class="text-sm text-gray-500">Đang tải thông tin...</div>

          <!-- Thông báo -->
          <div v-else-if="notification">
            <!-- Thông tin chung -->
            <div class="flex items-start gap-4">
              <img v-if="notification.image_url" :src="notification.image_url"
                class="w-24 h-24 object-cover border rounded" alt="Ảnh thông báo" />
              <div>
                <p class="text-lg font-semibold text-gray-800">{{ notification.title }}</p>
                <p class="text-sm text-gray-500">ID: {{ notification.id }}</p>
                <p class="text-sm"><strong>Ngày gửi:</strong> {{ formatDate(notification.created_at) }}</p>
              </div>
            </div>

            <!-- Thông tin phụ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700 mt-4">
              <div><strong>Loại:</strong> {{ typeText(notification.type) }}</div>
              <div>
                <strong>Vai trò nhận:</strong>
                <span v-if="notification.to_roles && notification.to_roles.length">
                  <span v-for="(role, index) in notification.to_roles" :key="index"
                    class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs mr-1">
                    {{ roleText(role) }}
                  </span>
                </span>
                <span v-else class="text-gray-500 italic">Không có</span>
              </div>
              <div v-if="notification.link">
                <strong>Liên kết:</strong>
                <a :href="notification.link" target="_blank" class="text-blue-600 hover:underline break-all">
                  {{ notification.link }}
                </a>
              </div>
              <div>
                <strong>Trạng thái:</strong>
                <span :class="statusClass(notification.status)">
                  {{ notification.status === 'sent' ? 'Đã gửi' : 'Lưu nháp' }}
                </span>
              </div>
              <div>
                <strong>Kênh gửi:</strong>
                <span v-if="notification.channels && notification.channels.length">
                  <span v-for="(ch, index) in notification.channels" :key="index"
                    class="inline-block bg-gray-200 text-gray-800 px-2 py-1 rounded-full text-xs mr-1">
                    {{ channelText(ch) }}
                  </span>
                </span>
                <span v-else class="text-gray-500 italic">Không có</span>
              </div>
            </div>

            <!-- Nội dung -->
            <div class="mt-4">
              <p class="font-semibold mb-1">Nội dung:</p>
              <div class="bg-gray-50 p-4 rounded border text-gray-800 whitespace-pre-line">
                {{ stripHTML(notification.content) || 'Không có nội dung' }}
              </div>
            </div>

            <!-- Người nhận -->
            <div v-if="notification.recipients?.length > 0" class="mt-8">
              <p class="font-semibold mb-2 text-gray-800">Danh sách người nhận ({{ notification.recipients.length }}):
              </p>
              <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-300 bg-white">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="border px-3 py-2 text-left">Tên</th>
                      <th class="border px-3 py-2 text-left">Email</th>
                      <th class="border px-3 py-2 text-left">Trạng thái đọc</th>
                      <th class="border px-3 py-2 text-left">Ngày đọc</th>
                      <th class="border px-3 py-2 text-left">Trạng thái hiển thị</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="r in notification.recipients" :key="r.id" class="border-t">
                      <td class="px-3 py-2">{{ r.user?.name || 'Không xác định' }}</td>
                      <td class="px-3 py-2">{{ r.user?.email || '-' }}</td>
                      <td class="px-3 py-2">
                        <span :class="r.is_read ? 'text-green-600 font-medium' : 'text-yellow-600 font-medium'">
                          {{ r.is_read ? 'Đã đọc' : 'Chưa đọc' }}
                        </span>
                      </td>
                      <td class="px-3 py-2">
                        {{ r.read_at ? formatDate(r.read_at) : '-' }}
                      </td>
                      <td class="px-3 py-2">
                        <span :class="r.is_hidden ? 'text-red-600' : 'text-green-700'">
                          {{ r.is_hidden ? 'Đã ẩn' : 'Hiển thị' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Không có người nhận -->
            <div v-else class="mt-4 text-sm text-gray-500 italic">Không có người nhận nào.</div>

            <!-- Quay lại -->
            <div class="pt-6 border-t">
              <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded" @click="goBack">
                ← Quay lại danh sách
              </button>
            </div>
          </div>

          <!-- Không tìm thấy -->
          <div v-else class="text-red-600">Không tìm thấy thông báo.</div>
        </div>
      </main>

    </div>
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
function typeText(type) {
  return {
    system: 'Hệ thống',
    promotion: 'Khuyến mãi',
    general: 'Chung',
    message: 'Tin nhắn'
  }[type] || type
}

function channelText(channel) {
  return {
    web: 'Web',
    email: 'Email'
  }[channel] || channel
}

function roleText(role) {
  return {
    admin: 'Quản trị viên',
    seller: 'Người bán',
    user: 'Người dùng'
  }[role] || role
}


function statusClass(status) {
  return {
    sent: 'text-green-700 font-semibold',
    draft: 'text-yellow-700 font-semibold'
  }[status] || 'text-gray-600'
}

function stripHTML(html) {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}

function goBack() {
  window.history.back()
}
</script>
