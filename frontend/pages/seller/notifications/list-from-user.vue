<template>
  <NotificationListPage :title="'Thông báo từ người dùng'" :filterRole="'user'" />
</template>

<script setup>
import NotificationListPage from '@/components/notifications/NotificationListPage.vue'
definePageMeta({
  layout: 'default-seller'
})

</script>