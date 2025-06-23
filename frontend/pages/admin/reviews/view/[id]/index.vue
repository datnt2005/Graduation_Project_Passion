<template>
  <div class="p-6 max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold">Chi tiết đánh giá</h1>
    <NuxtLink to="/admin/reviews/list-reviews" class="text-blue-600 hover:underline">
      ← Quay lại danh sách
    </NuxtLink>

    <div v-if="loading">Đang tải...</div>

    <div v-else-if="review" class="bg-white rounded shadow p-6 space-y-4">
      <!-- Thông tin sản phẩm -->
      <div>
        <strong>Sản phẩm:</strong> {{ review.product_name }}
      </div>

      <!-- Người đánh giá -->
      <div>
        <strong>Người đánh giá:</strong> {{ review.user_name }}
      </div>

      <!-- Nội dung đánh giá -->
      <div>
        <strong>Nội dung:</strong>
        <p class="mt-1 text-gray-800 whitespace-pre-line">{{ review.content }}</p>
      </div>

      <!-- Rating -->
      <div>
        <strong>Đánh giá:</strong> {{ review.rating }} ★
      </div>

      <!-- Trạng thái -->
      <div>
        <strong>Trạng thái:</strong> {{ statusText(review.status) }}
      </div>

      <!-- Ngày tạo -->
      <div>
        <strong>Ngày gửi:</strong> {{ formatDate(review.created_at) }}
      </div>

      <!-- Ảnh đính kèm -->
      <div v-if="Array.isArray(review.images) && review.images.length">
        <p class="font-medium text-gray-700">Ảnh đính kèm:</p>
        <div class="flex gap-3 flex-wrap">
          <img v-for="(img, i) in review.images" :key="i" :src="img.url"
            class="w-24 h-24 object-cover rounded border hover:scale-105 transition-transform cursor-pointer" />
        </div>
      </div>

      <!-- Phản hồi từ admin -->
      <div v-if="review.reply && review.reply.content" class="pt-4 border-t">
        <p class="font-semibold text-gray-800">Phản hồi từ quản trị viên:</p>
        <p class="mt-1 text-gray-700 whitespace-pre-line">{{ review.reply.content }}</p>
        <p class="text-sm text-gray-500 mt-1">Gửi lúc: {{ formatDate(review.reply.created_at) }}</p>
      </div>
    </div>

    <!-- Modal xem ảnh lớn -->
    <div v-if="showImage" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
      @click.self="showImage = null">
      <img :src="showImage" class="max-h-[90vh] max-w-[90vw] rounded shadow-lg" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRuntimeConfig } from '#app'
import axios from 'axios'

definePageMeta({ layout: 'default-admin' })

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
    const res = await axios.get(`${apiBase}/admin/reviews/${id}`, {
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
  switch (status) {
    case 'approved': return 'Đã duyệt'
    case 'pending': return 'Chờ duyệt'
    case 'rejected': return 'Bị từ chối'
    default: return 'Không rõ'
  }
}

function openImage(src) {
  showImage.value = src
}

function getImageUrl(img) {
  if (typeof img === 'string') return img; // phòng trường hợp là URL sẵn
  return img?.media_url?.startsWith('http') ? img.media_url : `${config.public.mediaBaseUrl}/${img.media_url}`
}

</script>
