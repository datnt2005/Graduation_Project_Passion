<template>
  <div class="bg-white p-2 border-sm shadow relative overflow-hidden">

    <!-- Scrollable horizontal list -->
    <div
      class="flex gap-3 sm:gap-4 overflow-x-auto no-scrollbar px-1 py-2"
      ref="scrollContainer"
    >
      <div
        v-for="(item, index) in brand"
        :key="item.id || index"
        class="flex-none w-1/3 sm:w-1/4 md:w-1/6 p-3 rounded-md  transition duration-300 ease-in-out  flex flex-col items-center"
      >
        <img
          :src="item.image"
          alt="Logo thương hiệu"
          class="w-16 h-16 sm:w-13 sm:h-13 object-contain"
        />
        <p class="text-xs sm:text-sm font-medium text-gray-700 text-center truncate w-full">
          {{ item.name }}
        </p>
      </div>
    </div>

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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none; /* IE và Edge */
  scrollbar-width: none; /* Firefox */
}

</style>

