<!-- app.vue -->
<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <ClientOnly>
      <Notification />
    </ClientOnly>
  </div>
</template>

<script setup>
// The Notification component will be auto-imported by Nuxt
</script>
