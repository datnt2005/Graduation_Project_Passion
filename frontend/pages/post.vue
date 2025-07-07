<template>
    <main class="flex-1 p-8 bg-white max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Bài viết</h1>

        <!-- Featured Post -->
        <div
            v-if="posts.length"
            class="flex flex-col md:flex-row items-start border-b border-gray-200 pb-8 mb-8 md:gap-x-8"
        >
            <div class="w-full md:w-1/2 md:pr-8 mb-6 md:mb-0 flex-shrink">
                <NuxtLink :to="`/posts/${posts[0].id}`">
                    <img
                        :src="posts[0].thumbnail_url"
                        alt="Featured Post"
                        class="w-full h-auto rounded-lg shadow-md object-cover"
                    />
                </NuxtLink>
            </div>
            <div class="w-full md:w-1/2 md:ml-8">
                <NuxtLink :to="`/posts/${posts[0].id}`">
                    <h2 class="text-2xl font-bold text-gray-800 mb-3 hover:text-blue-600 transition">
                        {{ posts[0].title }}
                    </h2>
                </NuxtLink>
                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                    {{ posts[0].description || posts[0].excerpt || '' }}
                </p>
                <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
                    <span>Người đăng:</span>
                    <span>{{ posts[0].user?.name || '---' }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
                    <span>{{ formatDate(posts[0].created_at) }}</span>
                    <span>{{ posts[0].views || 0 }} lượt xem</span>
                </div>
                <ul v-if="posts[0].tags && posts[0].tags.length" class="list-disc list-inside text-gray-600 text-sm space-y-1">
                    <li v-for="tag in posts[0].tags" :key="tag">{{ tag }}</li>
                </ul>
            </div>
        </div>

        <!-- List Posts -->
        <div class="grid grid-cols-1 gap-x-8 gap-y-16">
            <div
                v-for="post in posts.slice(1)"
                :key="post.id"
                class="flex items-start pb-6 border-b border-gray-200"
            >
                <NuxtLink :to="`/posts/${post.id}`">
                    <img
                        :src="post.thumbnail_url"
                        alt="Article Image"
                        class="w-32 h-20 object-cover rounded-md flex-shrink-0 mr-4"
                    />
                </NuxtLink>
                <div class="flex-1">
                    <NuxtLink :to="`/posts/${post.id}`">
                        <h3 class="text-lg font-semibold text-gray-800 mb-1 hover:text-blue-600 transition">
                            {{ post.title }}
                        </h3>
                    </NuxtLink>
                    <p class="text-gray-600 text-sm line-clamp-2 mb-2">
                        {{ post.description || post.excerpt || '' }}
                    </p>
                    <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                        <span>Người đăng: {{ post.user?.name || '---' }}</span>
                        <span>{{ post.category?.name || '' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>{{ formatDate(post.created_at) }}</span>
                        <span>{{ post.views || 0 }} lượt xem</span>
                    </div>
                </div>
            </div>
            <div v-if="!posts.length" class="text-center text-gray-500 py-10">Chưa có bài viết nào.</div>
        </div>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const posts = ref([])

const formatDate = (dateStr) => {
    if (!dateStr) return ''
    return new Date(dateStr).toLocaleDateString('vi-VN')
}

const fetchPosts = async () => {
    try {
        const res = await $fetch('http://localhost:8000/api/posts?status=published')
        posts.value = res.data || []
    } catch (e) {
        posts.value = []
    }
}

onMounted(fetchPosts)
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>