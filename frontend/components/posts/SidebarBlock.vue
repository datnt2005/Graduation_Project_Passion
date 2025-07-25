<template>
  <div class="bg-gray-50 rounded-lg shadow p-4 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">{{ title }}</h2>
    <div v-if="loading" class="text-gray-400 text-sm">Đang tải...</div>
    <div v-else-if="!posts.length" class="text-gray-400 text-sm">Không có bài viết.</div>
    <div v-else class="space-y-4">
      <NuxtLink
        v-for="item in posts"
        :key="item.id"
        :to="`/posts/${item.slug}`"
        class="flex gap-3 items-center hover:bg-blue-50 rounded transition p-2 -m-2"
      >
        <img v-if="item.thumbnail_url" :src="item.thumbnail_url" alt="thumb" class="w-16 h-12 object-cover rounded" />
        <div class="flex-1 min-w-0">
          <div class="font-semibold text-gray-800 text-sm truncate">{{ item.title }}</div>
          <div class="text-xs text-gray-500 truncate">{{ formatDate(item.created_at) }}</div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: String,
  posts: Array,
  loading: Boolean
})

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('vi-VN')
}
</script>
