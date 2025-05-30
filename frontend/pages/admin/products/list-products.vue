<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold mb-1">Quản lí sản phẩm</h1>
    <p class="text-gray-600 mb-4">Danh sách sản phẩm hiện có</p>

    <div class="flex flex-wrap items-center gap-2 mb-4">
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium">Hiển thị</label>
        <select class="border rounded px-2 py-1 text-sm">
          <option>5</option>
          <option>10</option>
          <option>20</option>
        </select>
      </div>

      <input type="text" placeholder="Tìm kiếm" class="border px-3 py-2 rounded w-48 text-sm" />

      <select class="border rounded px-2 py-1 text-sm">
        <option>Danh mục</option>
      </select>

      <select class="border rounded px-2 py-1 text-sm">
        <option>Thương hiệu</option>
      </select>

      <button class="bg-gray-100 px-3 py-1 rounded text-sm">Lọc</button>

      <div class="ml-auto flex gap-2">
        <button class="border px-3 py-1 rounded text-sm">Xuất csv</button>
        <button class="border px-3 py-1 rounded text-sm">Xuất Excel</button>
        <button class="border px-3 py-1 rounded text-sm text-gray-400" disabled>Upload File</button>
        <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Thêm sản phẩm</button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left border rounded bg-white shadow-sm">
        <thead class="bg-gray-100 font-semibold text-gray-700">
          <tr>
            <th class="px-3 py-2"><input type="checkbox" /></th>
            <th class="px-3 py-2">#</th>
            <th class="px-3 py-2">Tên sản phẩm</th>
            <th class="px-3 py-2">Giá nhập</th>
            <th class="px-3 py-2">Giá bán</th>
            <th class="px-3 py-2">Danh mục</th>
            <th class="px-3 py-2">Khuyến mãi</th>
            <th class="px-3 py-2">Hình ảnh</th>
            <th class="px-3 py-2">Số lượng</th>
            <th class="px-3 py-2">Biến thể</th>
            <th class="px-3 py-2">Slug</th>
            <th class="px-3 py-2">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(product, index) in products" :key="product.id" class="border-t hover:bg-gray-50">
            <td class="px-3 py-2"><input type="checkbox" /></td>
            <td class="px-3 py-2">{{ index + 1 }}</td>
            <td class="px-3 py-2 font-medium">{{ product.name }}</td>
            <td class="px-3 py-2">${{ product.importPrice }}</td>
            <td class="px-3 py-2">${{ product.sellPrice }}</td>
            <td class="px-3 py-2">{{ product.category }}</td>
            <td class="px-3 py-2">${{ product.promotion }}</td>
            <td class="px-3 py-2">
              <img :src="product.image" alt="Product Image" class="w-12 h-12 object-cover rounded" />
            </td>
            <td class="px-3 py-2">{{ product.quantity }}</td>
            <td class="px-3 py-2 whitespace-nowrap">
              <div v-for="variant in product.variants" :key="variant" class="text-xs">{{ variant }}</div>
            </td>
            <td class="px-3 py-2">{{ product.slug }}</td>
            
          
            <td class="px-3 py-2">Xoá | Sửa</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex justify-center mt-4">
      <ul class="inline-flex space-x-1 text-sm">
        <li v-for="page in 5" :key="page">
          <button class="px-3 py-1 border rounded hover:bg-gray-100" :class="{ 'bg-blue-600 text-white': page === 2 }">{{ page }}</button>
        </li>
      </ul>
    </div>
  </div>
</template>


<script setup>

const products = ref([
  {
    id: 1,
    name: 'Sản phẩm A',
    importPrice: 10,
    sellPrice: 109,
    category: 'Kính nữ',
    promotion: 0,
    image: 'https://via.placeholder.com/50x50?text=🕶️',
    quantity: 200,
    variants: ['biến thể 1', 'biến thể 2'],
    slug: 'slug',
  },
  {
    id: 2,
    name: 'Sản phẩm B',
    importPrice: 10,
    sellPrice: 109,
    category: 'Kính nữ',
    promotion: 0,
    image: 'https://via.placeholder.com/50x50?text=🕶️',
    quantity: 200,
    variants: ['biến thể 1', 'biến thể 2'],
    slug: 'slug',
  },
  // Thêm các sản phẩm khác nếu muốn
])


definePageMeta({
  layout: 'default-admin' // Dùng layout riêng cho admin
})
</script>