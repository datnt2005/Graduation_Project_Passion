<template>
  <main class="bg-[#F5F5FA] py-2">
    <div class="max-w-7xl mx-auto">
      <div class="text-sm text-gray-500 px-4 py-2 rounded">
        <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
        <span class="mx-1">›</span>
        <span class="text-black font-medium">Bài viết</span>
      </div>
    </div>
    <div class="flex-1 p-6 sm:p-10 bg-white max-w-7xl mx-auto rounded-lg shadow flex flex-col md:flex-row gap-8">
      <!-- Nội dung chính -->
      <div class="flex-1 min-w-0">
        <PostDetail :post-slug="route.params.slug" />
        <CommentSection :slug="route.params.slug" :current-user-id="currentUserId" />
      </div>
      <!-- Sidebar -->
      <PostSidebar :post-slug="route.params.slug" />
    </div>
  </main>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { computed, onMounted } from 'vue'
import { useAuthStore } from '~/stores/auth'
import { useToast } from '~/composables/useToast'
import PostDetail from '~/components/posts/PostDetail.vue'
import CommentSection from '~/components/posts/CommentSection.vue'
import PostSidebar from '~/components/posts/PostSidebar.vue'

const route = useRoute()
const authStore = useAuthStore()
const { toast } = useToast()

// Gọi fetchUser khi component được mount để đảm bảo user được tải
onMounted(async () => {
  const token = localStorage.getItem('access_token')
  if (!authStore.currentUser && token) {
    try {
      await authStore.fetchUser()
    } catch (err) {
      console.error('Page: Failed to fetch user:', err.message, err)
      toast('error', 'Không thể tải thông tin người dùng. Vui lòng đăng nhập lại.')
      localStorage.removeItem('access_token')
      authStore.currentUser = null
      authStore.isLoggedIn = false
    }
  } else if (!token) {
    console.warn('Page: No access token found')
    toast('info', 'Vui lòng đăng nhập để sử dụng đầy đủ tính năng.')
  } else {
  }
})

const currentUserId = computed(() => {
  const id = authStore.currentUser?.id ? String(authStore.currentUser.id) : null
  return id
})

</script>