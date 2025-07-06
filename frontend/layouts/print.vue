<template>
  <div class="bg-white text-gray-800">
    <Header v-if="!isPrintPage" />
    <NuxtPage />
    <Notification v-if="!isPrintPage" />
    <Footer v-if="!isPrintPage" />
  </div>
</template>

<script setup>
import Header from '~/components/shared/layouts/Header.vue'
import Footer from '~/components/shared/layouts/Footer.vue'
import Notification from '~/components/Notification.vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isPrintPage = computed(() => route.path.startsWith('/users/print-order/'))
</script>
