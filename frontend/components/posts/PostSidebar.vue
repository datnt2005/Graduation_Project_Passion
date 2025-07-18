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
    latestPosts.value = res.data.data?.filter(p => p.id !== props.post?.id) || []
  } catch (e) {
    console.error('Error fetching latest posts:', e)
    latestPosts.value = []
  }
}

const fetchTopic = async () => {
  const catId = props.post?.category?.id
  if (!catId) {
    console.warn('No category ID found for post:', props.post)
    topicPosts.value = []
    return
  }
  try {
    const res = await $fetch(`${apiBase}/posts?category_id=${catId}&limit=5&status=published`, {
      headers: { 'Accept': 'application/json' }
    })
    if (res.data?.data?.length) {
      topicPosts.value = res.data.data.filter(p => p.id !== props.post?.id && p.status === 'published') || []
    } else {
      topicPosts.value = []
    }
  } catch (e) {
    console.error('Error fetching topic posts:', e)
    topicPosts.value = []
  }
}

const fetchRelated = async () => {
  relatedLoading.value = true
  try {
    let url = `${apiBase}/posts?status=published&limit=5`
    if (props.post?.category?.id) url += `&category_id=${props.post.category.id}`
    const res = await $fetch(url, {
      headers: { 'Accept': 'application/json' }
    })
    relatedPosts.value = res.data.data?.filter(p => p.id !== props.post?.id) || []
  } catch (e) {
    console.error('Error fetching related posts:', e)
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

watch(() => props.post?.id, () => {
  fetchLatest()
  fetchTopic()
  fetchRelated()
})
</script>