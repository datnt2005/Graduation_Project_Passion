<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <SideBar />

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
       <Header />
      <!-- Slot for page content -->
      <main class="p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import SideBar from '~/components/admin-ui/SideBar.vue'
import Header from '~/components/admin-ui/Header.vue'
</script>
