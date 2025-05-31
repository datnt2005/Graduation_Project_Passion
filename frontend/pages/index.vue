<template>
  <main class="bg-[#F5F5FA] py-2">
    <div class="container mx-auto flex gap-4 px-4">
      <!-- Sidebar -->
      <Sidebar />
      <!-- Main content -->
      <section class="flex-1 space-y-6">
        <!-- Banner -->
        <Banner />      
      
        <!-- Brands -->
        <Brands />
        <!-- Products -->
        <Products />
      </section>
    </div>
  </main>
</template>

<script setup>
import Sidebar from '~/components/shared/Sidebar.vue'
import Banner from '~/components/shared/Banner.vue'
import Brands from '~/components/shared/Brands.vue'
import Products from '~/components/shared/Products.vue'
</script>
