import { ref } from 'vue'
import { useRuntimeConfig } from '#app'

export function usePost() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBaseUrl

  const post = ref(null)
  const loading = ref(true)
  const error = ref(null)

  const fetchPost = async (id) => {
    loading.value = true
    error.value = null
    try {
      const res = await $fetch(`${apiBase}/posts/${id}`)
      post.value = res.data
    } catch (err) {
      post.value = null
      error.value = err
    } finally {
      loading.value = false
    }
  }

  return { post, loading, error, fetchPost }
}