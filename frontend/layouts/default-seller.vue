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
import SideBar from '~/components/seller/SideBar.vue'
import Header from '~/components/seller/Header.vue'
</script>
