<template>
  <div class="bg-white p-2 rounded shadow-sm">
    <!-- Heading -->
    <h2 class="text-lg font-semibold mb-4">Tất cả sản phẩm</h2>

    <!-- Filters -->
    <Filters />

    <!-- Products -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div
          v-for="(item, index) in products"
          :key="index"
          class="overflow-hidden p-2 bg-white shadow transition transform hover:scale-105 hover:-translate-y-1 hover:shadow-lg duration-300 text-left"
        >
          <img
            :src="item.image"
            alt="Hình sản phẩm"
            class="w-full h-40 object-cover rounded"
          />
          <p class="text-sm mt-2 font-medium text-gray-700 line-clamp-2">
            {{ item.name }}
          </p>
          <div class="text-red-500 font-semibold mt-1">{{ item.price }}</div>
          <div class="line-through text-gray-400 text-sm">{{ item.discount }}</div>
          <div class="flex items-center text-sm text-gray-400 space-x-2">
            <div class="text-yellow-400">{{ item.rating }}</div>
            <div>| {{ item.sold }} đã bán</div>
          </div>
        </div>
    </div>

  </div>
</template>





<script setup>
import Filters from '~/components/shared/Filters.vue' 

const products = [
    {
        name: 'Điện thoại việt nam, tại việt nam. ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/b5/6e/93/026f3f64e6718eb644b5911bca06583f.jpg.webp',
        price: '$199.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '1.000'
    },
     {
        name: 'Gậy Bẻ Lò Xo Ti Tan Lực Bẻ Từ 20KG Tập Tay,Vai,Xô,Ngực Body Tại Nhà. ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/af/3a/ca/2d5455c9afdcff3b22b0c5ef8fcae22d.jpg.webp',
        price: '$129.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '2.000'
    },
     {
        name: 'MacBook Air M2. ',
        image: 'https://salt.tikicdn.com/cache/750x750/media/catalog/producttmp/1a/7f/72/5f3c9a7499bc976b932ef8cbd58c0282.jpg.webp',
        price: '$1999.000',
        discount: '$2999.000',
        rating: '★★★★★',
        sold: '3.000'
    },
     {
        name: 'Apple Watch Series 9 GPS Sport Loop (Viền Nhôm, Dây vải). ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/49/c9/84/cfae5ab522d63988d89f07603c6e874d.jpg.webp',
        price: '$239.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '4.000'
    },
     {
        name: 'Ấm Điện Thủy Tinh Siêu Tốc Có Điều Chỉnh Nhiệt Độ Lock&Lock EJK341 (1.8L) - Hàng chính hãng. ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/0e/a9/c7/f859ea0995229bcc5cd8cf134648373e.jpg.webp',
        price: '$99.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '5.000'
    },
     {
        name: 'Máy Đánh Trứng, Đánh Sữa Và Tạo Bọt Cafe Cầm Tay Di Động 3 Tốc Độ Sử Dụng Pin Sạc Cao Cấp - Hàng chính hãng. ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/35/a0/49/59f5efea2f4d2bbdf0872e9ce382ec71.jpg.webp',
        price: '$39.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '6.000'
    },
     {
        name: '[Mẫu mới] Combo 2 Tã quần SunMate siêu mềm mại G1 mới size M-18+2 miếng. ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/14/fa/2e/e93d258f32bf97f8f7ed21aff391eeb7.png.webp',
        price: '$99.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '2.300'
    },
     {
        name: 'Xe Scooter Umoo vận động ngoài trời, tăng cường phát triển thể chất cho Bé. ',
        image: 'https://salt.tikicdn.com/cache/750x750/ts/product/e7/f5/18/36829c04449633eab8e3c6fb2af1ddca.jpg.webp',
        price: '$199.000',
        discount: '$299.000',
        rating: '★★★★★',
        sold: '1.000'
    }
    

]
</script>


<style scoped>
/* Để giới hạn 2 dòng tên sản phẩm */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


