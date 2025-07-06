<template>
  <aside class="w-full md:w-80 flex-shrink-0">
    <SidebarBlock title="Bài viết mới nhất" :posts="latestPosts" />
    <SidebarBlock title="Bài viết theo chủ đề" :posts="topicPosts" />
    <SidebarBlock title="Bài viết liên quan" :posts="relatedPosts" :loading="relatedLoading" />
    
  </aside>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'
import { useRoute } from 'vue-router'
import SidebarBlock from '~/components/posts/SidebarBlock.vue'

const props = defineProps({ post: Object })
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const route = useRoute()

const relatedPosts = ref([])
const relatedLoading = ref(true)
const latestPosts = ref([])
const topicPosts = ref([])

const fetchLatest = async () => {
  try {
    const res = await $fetch(`${apiBase}/posts?limit=5`)
    latestPosts.value = res.data?.filter(p => p.id !== props.post?.id) || []
  } catch {
    latestPosts.value = []
  }
}

const fetchTopic = async () => {
  const catId = props.post?.category?.id
  if (!catId) return (topicPosts.value = [])
  try {
    const res = await $fetch(`${apiBase}/posts?category_id=${catId}&limit=5`)
    topicPosts.value = res.data?.filter(p => p.id !== props.post?.id) || []
  } catch {
    topicPosts.value = []
  }
}

const fetchRelated = async () => {
  relatedLoading.value = true
  try {
    let url = `${apiBase}/posts?status=published`
    if (props.post?.category?.id) {
      url += `&category_id=${props.post.category.id}`
    }
    const res = await $fetch(url)
    relatedPosts.value = res.data?.filter(p => p.id !== props.post.id) || []
  } catch {
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
