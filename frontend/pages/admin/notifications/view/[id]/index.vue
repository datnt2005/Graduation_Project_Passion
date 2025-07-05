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
          <!-- Loading -->
          <div v-if="loading" class="text-sm text-gray-500">Đang tải thông tin...</div>

          <!-- Thông báo -->
          <div v-else-if="notification">
            <!-- Tiêu đề + ảnh -->
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
              <div><strong>Loại:</strong> {{ typeText(notification.type) }}</div>
              <div><strong>Vai trò nhận:</strong> {{ roleText(notification.to_role) }}</div>
              <div>
                <strong>Liên kết:</strong>
                <a :href="notification.link" target="_blank" class="text-blue-600 hover:underline break-all">
                  {{ notification.link }}
                </a>
              </div>
              <div>
                <strong>Tình trạng:</strong>
                <span :class="statusClass(notification.status)">
                  {{ notification.status === 'sent' ? 'Đã gửi' : 'Lưu nháp' }}
                </span>
              </div>
              <div>
                <strong>Trạng thái đọc:</strong>
                <span
                  :class="notification.is_read == 1 ? 'text-green-700 font-semibold' : 'text-yellow-700 font-semibold'">
                  {{ notification.is_read == 1 ? 'Đã đọc' : 'Chưa đọc' }}
                </span>
              </div>
              <div>
                <strong>Trạng thái xóa:</strong>
                <span
                  :class="notification.is_hidden == 1 ? 'text-yellow-700 font-semibold' : 'text-green-700 font-semibold'">
                  {{ notification.is_hidden == 1 ? 'Đã ẩn' : 'Đang hiển thị' }}
                </span>
              </div>
            </div>
            <!-- Nội dung -->
            <div>
              <p class="font-semibold mb-1">Nội dung:</p>
              <div class="bg-gray-50 p-4 rounded border text-gray-800 whitespace-pre-line">
                {{ stripHTML(notification.content) || 'Không có nội dung' }}
              </div>
            </div>

            <!-- Nút quay lại -->
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
