<template>
  <NotificationListPage :title="'Thông báo từ người bán'" :filterRole="'seller'" />
</template>

<script setup>
import NotificationListPage from '@/components/notifications/NotificationListPage.vue'
definePageMeta({
  layout: 'default-admin'
})

</script>