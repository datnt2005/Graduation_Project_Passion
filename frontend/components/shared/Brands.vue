<template>
  <div class="bg-white p-6 rounded-xl shadow-md relative overflow-hidden">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Thương hiệu nổi bật</h2>

    <!-- Responsive grid -->
   <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-6">
  <div
    v-for="(item, index) in paginatedBrands"
    :key="index"
    class="flex flex-col items-center bg-gray-50 p-4 rounded-lg hover:shadow-lg transition duration-300 ease-in-out hover:scale-105"
  >
    <img
      :src="item.image"
      alt="Logo thương hiệu"
      class="w-30 h-30 sm:w-20 sm:h-20 object-contain mb-2"
    />
    <p class="text-sm font-medium text-gray-700 text-center truncate w-full">
      {{ item.name }}
    </p>
  </div>
</div>


    <!-- Controls -->
    <button
      @click="prevBrand"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-100 z-10"
      v-if="canNavigate"
    >
      ❮
    </button>
    <button
      @click="nextBrand"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-100 z-10"
      v-if="canNavigate"
    >
      ❯
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const brand = ref([
  { name: 'Guicci', image: 'https://nguoinoitieng.tv/images/nnt/107/1/bjvx.jpg' },
  { name: 'OWEN', image: 'https://mcdn.coolmate.me/image/November2024/cac_brand_thoi_trang_viet_nam2.jpg' },
  { name: 'Philips', image: 'https://topbrands.vn/wp-content/uploads/2021/08/Thuong-hieu-do-gia-dung-Philips.jpg' },
  { name: 'Nutifood', image: 'https://toplist.vn/images/800px/nutifood-31428.jpg' },
  { name: 'Maggi', image: 'https://hoasenfoods.vn/wp-content/uploads/2023/11/536d047a-d94f-4cba-86d2-5dd74d6a7f24-8-maggi.jpg' },
  { name: 'Heineken', image: 'https://toplist.vn/images/800px/heineken-835434.jpg' },
  { name: 'Sony', image: 'https://th.bing.com/th/id/OIP.efxRHxjVeX8oXklDDWBqSAHaEK?cb=iwc2&rs=1&pid=ImgDetMain' },
  { name: 'Samsung', image: 'https://1000logos.net/wp-content/uploads/2017/06/Samsung-Logo.png' },
])

const currentPage = ref(0)
const itemsPerPage = 6

const totalPages = computed(() => Math.ceil(brand.value.length / itemsPerPage))

const paginatedBrands = computed(() =>
  brand.value.slice(currentPage.value * itemsPerPage, (currentPage.value + 1) * itemsPerPage)
)

const nextBrand = () => {
  if (currentPage.value < totalPages.value - 1) {
    currentPage.value++
  }
}

const prevBrand = () => {
  if (currentPage.value > 0) {
    currentPage.value--
  }
}

const canNavigate = computed(() => totalPages.value > 1)
</script>

<style scoped>
@media (max-width: 640px) {
  .grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
