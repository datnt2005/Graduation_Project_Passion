<script setup lang="ts">
import LoadingSpinner from '~/components/shared/LoadingSpinner.vue'
import { useLoadingStore } from '~/stores/loading'
import { useRouter } from 'vue-router'
import ChatWidget from '~/components/chat/ChatWidget.vue'
import Notification from '~/components/Notification.vue'

const loading = useLoadingStore()
const router = useRouter()

router.beforeEach((to, from, next) => {
  loading.start()
  next()
})

router.afterEach(() => {
  setTimeout(() => loading.stop(), 600)
})
</script>

<template>
  <div>
        <LoadingSpinner v-if="loading.isLoading" />
  </div>
  <NuxtLayout>
    <NuxtPage />
    <Notification />
  </NuxtLayout>
      <ChatWidget />

</template>
