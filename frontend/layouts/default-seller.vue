
<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <SideBar :show="isSidebarOpen" @close="isSidebarOpen = false" />

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <Header @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

      <!-- Page content -->
      <main class="p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import SideBar from '~/components/seller/SideBar.vue'
import Header from '~/components/seller/Header.vue'

const isSidebarOpen = ref(false)
</script>

