<template>
  <div class="flex h-screen">
    <Sidebar :show="showSidebar" @close="showSidebar = false" />

    <div class="flex-1 flex flex-col overflow-hidden relative"> <!-- ✅ Sửa ở đây -->
      <Header @toggle-sidebar="showSidebar = !showSidebar" />
      <main class="p-4 overflow-y-auto">
        <slot />
      </main>
    </div>

    <!-- ✅ Notification đã được Teleport ra body -->
    <Notification />
  </div>
</template>


<script setup>
import Sidebar from '~/components/admin-ui/SideBar.vue'
import Header from '~/components/admin-ui/Header.vue'
import Notification from '~/components/Notification.vue'
import { ref } from 'vue'

const showSidebar = ref(false)
</script>
