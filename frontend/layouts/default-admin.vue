<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <Sidebar :show="showSidebar" @close="showSidebar = false" />

    <!-- Nội dung chính -->
    <div class="flex-1 flex flex-col">
      <Header @toggle-sidebar="showSidebar = !showSidebar" />
      <main class="p-4 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '~/components/admin-ui/SideBar.vue';
import Header from '~/components/admin-ui/Header.vue';
import { ref } from 'vue'

const showSidebar = ref(false)
</script>
