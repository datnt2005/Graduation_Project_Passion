<script setup lang="ts">
import LoadingSpinner from "~/components/shared/LoadingSpinner.vue";
import ChatWidget from "~/components/chat/ChatWidget.vue";
import Notification from "~/components/Notification.vue";
import { useLoadingStore } from "~/stores/loading";
import { useSettings } from "~/composables/useSettings";

const loading = useLoadingStore();
const { settings, fetchSettings } = useSettings();

onMounted(() => {
  fetchSettings();
});
</script>

<template>
  <div>
    <LoadingSpinner v-if="loading.isLoading" />
    <NuxtLayout>
      <NuxtPage />
      <Notification />
    </NuxtLayout>
    <ChatWidget />
  </div>
</template>
