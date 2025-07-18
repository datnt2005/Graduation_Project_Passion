<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <!-- Breadcrumb -->
    <div class="px-6 pt-6">
      <h1 class="text-xl font-semibold text-gray-800">Chi tiết đánh giá</h1>
    </div>
    <div class="px-6 pb-4">
      <NuxtLink to="/seller/reviews/list-reviews" class="text-gray-600 hover:underline text-sm">
        Danh sách đánh giá
      </NuxtLink>
      <span class="text-gray-600 text-sm"> / Chi tiết đánh giá</span>
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
              Quản lý đánh giá
            </button>
          </li>
        </ul>
      </aside>

      <!-- Nội dung chính -->
      <main class="flex-1 p-6 bg-gray-100">
        <div class="max-w-5xl mx-auto bg-white rounded shadow border border-gray-200 p-6 space-y-6">
          <div v-if="loading" class="text-sm text-gray-500">Đang tải đánh giá...</div>

          <div v-else-if="review">
            <!-- Thông tin sản phẩm -->
            <div class="flex items-start gap-4">
              <img
                v-if="review.images?.[0]?.url"
                :src="review.images[0].url"
                class="w-24 h-24 object-cover border rounded"
                alt="Ảnh SP"
              />
              <div>
                <p class="text-lg font-semibold text-gray-800">{{ review.product_name }}</p>
                <p class="text-sm text-gray-500">ID SP: {{ review.product_id }}</p>
                <p class="text-sm"><strong>Người đánh giá:</strong> {{ review.user_name }}</p>
                <p class="text-sm"><strong>Ngày gửi:</strong> {{ formatDate(review.created_at) }}</p>
              </div>
            </div>

            <!-- Thông tin phụ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
              <div><strong>Số sao:</strong> <span class="text-yellow-500">{{ review.rating }} ★</span></div>
              <div>
                <strong>Trạng thái:</strong>
                <span :class="statusClass(review.status)">{{ statusText(review.status) }}</span>
              </div>
              <div><strong>Lượt thích:</strong> {{ review.likes_count }}</div>
            </div>

            <!-- Nội dung đánh giá -->
            <div>
              <p class="font-semibold mb-1">Nội dung đánh giá:</p>
              <div class="bg-gray-50 p-4 rounded border text-gray-800 whitespace-pre-line">
                {{ review.content }}
              </div>
            </div>

            <!-- Ảnh đính kèm -->
            <div v-if="Array.isArray(review.images) && review.images.length">
              <p class="font-semibold mb-1">Ảnh đính kèm:</p>
              <div class="flex flex-wrap gap-3">
                <img
                  v-for="(img, i) in review.images"
                  :key="i"
                  :src="img.url"
                  @click="openImage(img.url)"
                  class="w-20 h-20 object-cover rounded border hover:scale-105 transition-transform cursor-pointer"
                  :alt="'Ảnh ' + (i + 1)"
                />
              </div>
            </div>

            <!-- Phản hồi từ admin -->
            <div v-if="review.reply?.content">
              <p class="font-semibold mt-4">Phản hồi của bạn:</p>
              <div class="bg-gray-50 p-4 rounded border text-gray-700 whitespace-pre-line">
                {{ review.reply.content }}
              </div>
              <p class="text-xs text-gray-500 mt-1">Gửi lúc: {{ formatDate(review.reply.created_at) }}</p>
            </div>

            <!-- Nút quay lại -->
            <div class="pt-6 border-t">
              <button
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded"
                @click="goBack"
              >
                ← Quay lại danh sách
              </button>
            </div>
          </div>
        </div>

        <!-- Modal ảnh -->
        <div v-if="showImage" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
          @click.self="showImage = null">
          <img :src="showImage" class="max-h-[90vh] max-w-[90vw] rounded shadow-lg" />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRuntimeConfig } from '#app'
import axios from 'axios'

definePageMeta({ layout: 'default-seller' })

const route = useRoute()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const review = ref(null)
const loading = ref(true)
const showImage = ref(null)

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token')
    const id = route.params.id
    const res = await axios.get(`${apiBase}/seller/reviews/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    review.value = res.data
  } catch (e) {
    console.error('Lỗi khi tải review:', e)
  } finally {
    loading.value = false
  }
})

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleString('vi-VN')
}

function statusText(status) {
  return {
    approved: 'Đã duyệt',
    pending: 'Chờ duyệt',
    rejected: 'Bị từ chối'
  }[status] || 'Không rõ'
}

function statusClass(status) {
  return {
    approved: 'text-green-600 font-semibold',
    pending: 'text-yellow-600 font-semibold',
    rejected: 'text-red-600 font-semibold'
  }[status] || 'text-gray-500'
}

function goBack() {
  window.history.back()
}

function openImage(src) {
  showImage.value = src
}
</script>



