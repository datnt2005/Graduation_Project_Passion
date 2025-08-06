// plugins/router-loading.ts
import { defineNuxtPlugin } from '#app'
import { useLoadingStore } from '~/stores/loading'

export default defineNuxtPlugin((nuxtApp) => {
  // ✅ Gọi useLoadingStore bên trong plugin context
  const loading = useLoadingStore()

  nuxtApp.hook('page:start', () => {
    loading.start()
  })

  nuxtApp.hook('page:finish', () => {
    setTimeout(() => loading.stop(), 600)
  })
})
