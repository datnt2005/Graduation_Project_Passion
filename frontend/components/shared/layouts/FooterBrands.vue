<template>
  <div class="bg-white border-t border-gray-200 py-3 mt-6">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-sm font-semibold text-gray-800 mb-2">
        Thương hiệu nổi bật
      </h2>
      <div class="flex flex-wrap text-xs text-center gap-x-2 gap-y-1">
        <NuxtLink
          v-for="(brand, index) in brands"
          :key="index"
          :to="`/brand/${brand.slug}`"
          class="text-gray-700 hover:text-blue-600 transition-colors duration-200 hover:underline relative after:content-['|'] after:ml-1 after:text-gray-300"
          :class="{ 'after:content-none': index === brands.length - 1 }"
        >
          {{ brand.name }}
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
const brands = [
  { name: "Apple", slug: "apple" },
  { name: "Samsung", slug: "samsung" },
  { name: "Sony", slug: "sony" },
  { name: "LG", slug: "lg" },
  { name: "Xiaomi", slug: "xiaomi" },
  { name: "Oppo", slug: "oppo" },
  { name: "Dell", slug: "dell" },
  { name: "HP", slug: "hp" },
  { name: "Asus", slug: "asus" },
  { name: "Lenovo", slug: "lenovo" },
  { name: "Nike", slug: "nike" },
  { name: "Adidas", slug: "adidas" },
  { name: "Puma", slug: "puma" },
  { name: "Zara", slug: "zara" },
  { name: "H&M", slug: "h-m" },
  { name: "Gucci", slug: "gucci" },
  { name: "Chanel", slug: "chanel" },
  { name: "Panasonic", slug: "panasonic" },
  { name: "Philips", slug: "philips" },
  { name: "Toshiba", slug: "toshiba" },
  { name: "Canon", slug: "canon" },
  { name: "Nikon", slug: "nikon" },
  { name: "Logitech", slug: "logitech" },
  { name: "MSI", slug: "msi" },
  { name: "Acer", slug: "acer" },
];
</script>
