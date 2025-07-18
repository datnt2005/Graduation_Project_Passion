<template>
  <main class="flex-1 p-6 sm:p-10 bg-white max-w-6xl mx-auto rounded-lg shadow flex flex-col md:flex-row gap-8">
    <!-- Nội dung chính -->
    <div class="flex-1 min-w-0">
      <PostDetail :post-slug="route.params.slug" />
      <CommentSection :post-slug="route.params.slug" :current-user-id="currentUserId" />
    </div>

    <!-- Sidebar -->
    <PostSidebar :post-slug="route.params.slug" />
  </main>
</template>

<script setup>
import { useRoute } from 'vue-router'
import PostDetail from '~/components/posts/PostDetail.vue'
import CommentSection from '~/components/posts/CommentSection.vue'
import PostSidebar from '~/components/posts/PostSidebar.vue'
import { useAuthStore } from '~/stores/auth'

const route = useRoute()
const authStore = useAuthStore()
const currentUserId = authStore.user?.id || null
</script>