import { ref, computed } from 'vue'
import { useRuntimeConfig } from '#app'

export function usePostComments(postId) {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBaseUrl

  const comments = ref([])
  const averageRating = ref(0)
  const loading = ref(false)
  const error = ref(null)

  const fetchComments = async () => {
    loading.value = true
    error.value = null
    try {
      const res = await $fetch(`${apiBase}/posts/${postId}/comments`)
      comments.value = res.data || []
      if (comments.value.length) {
        const sum = comments.value.reduce((acc, c) => acc + (c.rating || 0), 0)
        averageRating.value = sum / comments.value.length
      } else {
        averageRating.value = 0
      }
    } catch (err) {
      error.value = err
      comments.value = []
      averageRating.value = 0
    } finally {
      loading.value = false
    }
  }

  const getStarCount = (star) => comments.value.filter(c => c.rating === star).length

  return {
    comments,
    averageRating,
    loading,
    error,
    fetchComments,
    getStarCount,
  }
}