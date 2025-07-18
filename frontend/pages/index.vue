<template>
  <main class="bg-[#f5f5f5] text-[#222222] mb-6">
    <div class="max-w-[1200px] mx-auto px-2">
      <!-- Sidebar -->

      <!-- Main content -->
      <section class="flex-1 space-y-6">
        <!-- Banner -->
        <Banner />
        <!-- Brands -->
        <categories />
        <!-- // giảm giá -->
        <Sale />

        <ProductSearch />
        <!-- Products -->
        <Products />
      </section>
    </div>
  </main>
</template>

<script setup>
import Sidebar from "~/components/shared/layouts/Sidebar.vue";
import categories from "~/components/shared/layouts/categories.vue";
import Sale from "~/components/shared/Sale.vue";
import ProductSearch from "~/components/shared/ProductSearch.vue";
import Banner from "~/components/shared/Banner.vue";
import Brands from "~/components/shared/Tags.vue";
import Products from "~/components/shared/Products.vue";
</script>
