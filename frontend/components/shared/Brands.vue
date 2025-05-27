<template>
  <div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Thương hiệu nổi bật</h2>
    
    <!-- Responsive grid -->
    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-6">
      <div
        v-for="(item, index) in brand"
        :key="index"
        class="flex flex-col items-center bg-gray-50 p-4 rounded-lg hover:shadow-lg transition duration-300 ease-in-out hover:scale-105"
      >
        <img
          :src="item.image"
          alt="Logo thương hiệu"
          class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-2"
        />
        <p class="text-sm font-medium text-gray-700 text-center truncate w-full">
          {{ item.name }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
     const brand = [
        {
            name: 'Guicci',
            image: 'https://nguoinoitieng.tv/images/nnt/107/1/bjvx.jpg',
        },
         {
            name: 'OWEN',
            image: 'https://mcdn.coolmate.me/image/November2024/cac_brand_thoi_trang_viet_nam2.jpg',
        },
         {
            name: 'Philips',
            image: 'https://topbrands.vn/wp-content/uploads/2021/08/Thuong-hieu-do-gia-dung-Philips.jpg',
        },
         {
            name: 'Nutifood',
            image: 'https://toplist.vn/images/800px/nutifood-31428.jpg',
        },
         {
            name: 'Maggi',
            image: 'https://hoasenfoods.vn/wp-content/uploads/2023/11/536d047a-d94f-4cba-86d2-5dd74d6a7f24-8-maggi.jpg',
        },
         {
            name: 'Heineken',
            image: 'https://toplist.vn/images/800px/heineken-835434.jpg',
        },
        
     ]
</script>
