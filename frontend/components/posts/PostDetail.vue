<template>
  <main class="flex-1 p-6 sm:p-10 bg-white max-w-6xl mx-auto rounded-lg shadow flex flex-col md:flex-row gap-8">
    <!-- Nội dung chính -->
    <div class="flex-1 min-w-0">
      <!-- Loading & Error -->
      <div v-if="loading" class="text-center text-gray-400 py-16 text-lg flex flex-col items-center gap-2">
        <span class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mb-2"></span>
        Đang tải bài viết...
      </div>
      <div v-else-if="error" class="text-center text-red-500 py-16 text-lg">{{ error }}</div>
      <div v-else-if="!post" class="text-center text-gray-500 py-16 text-lg">Không tìm thấy bài viết.</div>

      <div v-else>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">{{ post.title }}</h1>
        <div class="flex flex-wrap gap-4 text-xs text-gray-500 mb-6 items-center">
          <span class="flex items-center gap-1">
            <i class="i-mdi-account-circle-outline text-base"></i>
            {{ post.user?.name || '---' }}
          </span>
          <span class="flex items-center gap-1">
            <i class="i-mdi-calendar-month-outline text-base"></i>
            {{ formatDate(post.created_at) }}
          </span>
          <span class="flex items-center gap-1">
            <i class="i-mdi-eye-outline text-base"></i>
            {{ post.views || 0 }} lượt xem
          </span>
          <span v-if="post.category?.name" class="flex items-center gap-1">
            <i class="i-mdi-tag-outline text-base"></i>
            {{ post.category.name }}
          </span>
        </div>

        <img v-if="post.thumbnail_url" :src="post.thumbnail_url" alt="Thumbnail" class="w-full max-h-96 object-cover rounded-lg mb-8 shadow" />
        <div class="prose prose-blue max-w-none mb-8 text-base leading-relaxed" v-html="post.content"></div>

        <!-- Tags -->
        <div v-if="post.tags?.length" class="mt-6 flex flex-wrap items-center gap-2">
          <span class="font-semibold text-gray-700">Tags:</span>
          <span v-for="tag in post.tags" :key="tag" class="inline-block bg-blue-100 text-blue-700 rounded px-2 py-1 text-xs">
            #{{ tag }}
          </span>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
   
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useRuntimeConfig } from '#app'
import PostSidebar from '~/components/posts/PostSidebar.vue'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const route = useRoute()

const post = ref(null)
const loading = ref(true)
const error = ref(null)
const postSlug = ref(route.params.slug)

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleString('vi-VN', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const fetchPost = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await $fetch(`${apiBase}/posts/slug/${route.params.slug}`, {
      headers: { 'Accept': 'application/json' }
    })
    if (res.success && res.data) {
      post.value = res.data
    } else {
      throw new Error('Dữ liệu không đúng định dạng từ API')
    }
  } catch (e) {
    error.value = `Không tìm thấy bài viết hoặc có lỗi từ server: ${e.message}`
    post.value = null
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchPost()
})

watch(() => route.params.slug, async (newSlug) => {
  postSlug.value = newSlug
  await fetchPost()
})
</script>

<style scoped>
.prose {
  max-width: 100%;
}
</style>