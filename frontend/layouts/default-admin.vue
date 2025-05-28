<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <SideBar />

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="bg-white shadow-md px-4 py-2 flex items-center justify-between">
        <h1 class="text-lg font-semibold hidden md:block">Trang quản trị</h1>
        
        <!-- User Info -->
        <div class="flex items-center gap-2">
          <img src="https://i.pravatar.cc/40" alt="avatar" class="w-8 h-8 rounded-full border">
          <span class="text-sm hidden sm:inline">Xin chào, người dùng</span>
        </div>
      </header>

      <!-- Slot for page content -->
      <main class="p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import SideBar from '~/components/admin-ui/SideBar.vue'
</script>
