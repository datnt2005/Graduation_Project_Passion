<template>
  <aside class="w-full md:w-80 flex-shrink-0">
    <SidebarBlock title="Bài viết mới nhất" :posts="latestPosts" />
    <SidebarBlock title="Bài viết liên quan" :posts="relatedPosts" :loading="relatedLoading" />
  </aside>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'
import SidebarBlock from '~/components/posts/SidebarBlock.vue'

const props = defineProps({ post: Object })
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const relatedPosts = ref([])
const relatedLoading = ref(true)
const latestPosts = ref([])
const topicPosts = ref([])

const fetchLatest = async () => {
  try {
    const res = await $fetch(`${apiBase}/posts?limit=5&status=published&sort=created_at:desc`, {
      headers: { 'Accept': 'application/json' }
    })
    // Loại bỏ bài viết hiện tại khỏi danh sách bài viết mới nhất
    latestPosts.value = res.data.data?.filter(p => p.id !== props.post?.id) || []
  } catch (e) {
    console.error('Lỗi khi lấy bài viết mới nhất:', e)
    latestPosts.value = []
  }
}

const fetchTopic = async () => {
  const catId = props.post?.category?.id
  if (!catId) {
    console.warn('Không tìm thấy ID danh mục cho bài viết:', props.post)
    topicPosts.value = []
    return
  }
  try {
    const res = await $fetch(`${apiBase}/posts?category_id=${catId}&limit=5&status=published`, {
      headers: { 'Accept': 'application/json' }
    })
    topicPosts.value = res.data.data?.filter(p => p.id !== props.post?.id && p.status === 'published') || []
  } catch (e) {
    console.error('Lỗi khi lấy bài viết theo danh mục:', e)
    topicPosts.value = []
  }
}

const fetchRelated = async () => {
  relatedLoading.value = true
  try {
    let url = `${apiBase}/posts?status=published&limit=5`
    if (props.post?.category?.id) {
      url += `&category_id=${props.post.category.id}`
    }
    const res = await $fetch(url, {
      headers: { 'Accept': 'application/json' }
    })
    // Loại bỏ bài viết hiện tại khỏi danh sách bài viết liên quan
    if (props.post?.id) {
      relatedPosts.value = res.data.data?.filter(p => p.id !== props.post.id) || []
    } else {
      console.warn('Không có ID bài viết hiện tại, hiển thị tất cả bài viết liên quan:', props.post)
      relatedPosts.value = res.data.data || []
    }
  } catch (e) {
    console.error('Lỗi khi lấy bài viết liên quan:', e)
    relatedPosts.value = []
  } finally {
    relatedLoading.value = false
  }
}

onMounted(() => {
  fetchLatest()
  fetchTopic()
  fetchRelated()
})

watch(() => props.post?.id, (newId, oldId) => {
  if (newId !== oldId) {
    console.log('Bài viết thay đổi, cập nhật danh sách:', { newId, oldId })
    fetchLatest()
    fetchTopic()
    fetchRelated()
  }
})
</script>