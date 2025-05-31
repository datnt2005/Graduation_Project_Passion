<template>
  <aside class="hidden md:block w-1/6 bg-white p-2 rounded shadow-sm">
    <ul class="space-y-2">
      <li
        v-for="(item, index) in categories "
        :key="index"
        class="flex items-center gap-4 bg-white p-2 rounded-lg hover:bg-gray-200 transition-all cursor-pointer"
        >
        <img
         :src="item.image"
          alt="item"
          class="w-8 h-8 object-contain rounded-full"
        />
        <span class="text-sm font-medium">{{ item.name }}</span>
      </li>
    </ul>
  </aside>
</template>

<script setup>
const categories = [
  {
    name: 'Thời trang nam',
    image: 'https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp',
  },
  {
    name: 'Thời trang nữ',
    image: 'https://media.canifa.com/mega_menu/item/Nu-1-menu-05Mar.webp',
  },
  {
    name: 'Trẻ em',
    image: 'https://media.canifa.com/mega_menu/item/Be-trai-2-menu-05Mar.webp',
  },
  {
    name: 'Phụ kiện',
    image: 'https://media.canifa.com/mega_menu/item/Be-gai-2-menu-05Mar.webp',
  },
]
</script>

