<template>
  <!-- Tiêu đề -->
  <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
    <h1 class="text-2xl font-bold">Sản phẩm</h1>
    <div class="space-x-2">
      <NuxtLink to="/admin/products/create-product" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Thêm sản phẩm mới</NuxtLink>
      <button class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Nhập dữ liệu</button>
      <button class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Xuất dữ liệu</button>
    </div>
  </div>

  <!-- Bộ lọc -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 mb-4">
    <select class="p-2 border rounded w-full">
      <option>Tất cả điểm SEO</option>
    </select>
    <select class="p-2 border rounded w-full">
      <option>Tất cả điểm dễ đọc</option>
    </select>
    <select class="p-2 border rounded w-full">
      <option>Chọn danh mục</option>
    </select>
    <select class="p-2 border rounded w-full">
      <option>Lọc theo loại sản phẩm</option>
    </select>
    <select class="p-2 border rounded w-full">
      <option>Lọc theo tình trạng kho</option>
    </select>
    <select class="p-2 border rounded w-full">
      <option>Lọc theo thương hiệu</option>
    </select>
  </div>

  <!-- Thanh tìm kiếm -->
  <div class="flex justify-end mb-4">
    <input type="text" v-model="searchKeyword" placeholder="Tìm kiếm sản phẩm..." class="border p-2 rounded w-full md:w-1/3" />
  </div>

  <!-- Bảng sản phẩm -->
  <div class="overflow-auto bg-white shadow rounded">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-200 text-left">
        <tr>
          <th class="p-2"><input type="checkbox" /></th>
          <th class="p-2">Ảnh</th>
          <th class="p-2">Tên sản phẩm</th>
          <th class="p-2">Mã SKU</th>
          <th class="p-2">Tình trạng</th>
          <th class="p-2">Giá</th>
          <th class="p-2">Danh mục</th>
          <th class="p-2">Ngày tạo</th>
          <th class="p-2">Thương hiệu</th>
          <th class="p-2">Hành động</th>
        </tr>
      </thead>
      <tbody v-for="product in filteredProducts " :key="product.id">
        <!-- Dòng sản phẩm mẫu -->
        <tr class="border-t hover:bg-gray-50">
          <td class="p-2"><input type="checkbox" /></td>
          <td class="p-2"><img :src="product.image" class="w-10 h-10 object-cover rounded" alt="Product Image"></td>
          <td class="p-2 text-blue-600 font-medium">{{ product.name }}</td>
          <td class="p-2">{{ product.sku }}</td>
          <td class="p-2 text-green-600 font-semibold">{{ product.status }}</td>
          <td class="p-2">{{ product.price }}</td>
          <td class="p-2">{{ product.category }}</td>
          <td class="p-2">{{ product.createdAt }}</td>
          <td class="p-2">{{ product.brand }}</td>
          <td class="p-2">
            <!-- Nút hành động có thể thêm tại đây -->
            <NuxtLink to="/admin/products/edit-product" class="text-blue-600 hover:underline">Sửa</NuxtLink>
          </td>
        </tr>
        <!-- Thêm các dòng khác nếu cần -->
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const products = ref([
  {
    id: 1,
    image: "https://salt.tikicdn.com/cache/750x750/ts/product/30/0e/15/0c485f1e70d016dc256c49980ffc5d70.jpg.webp",
    name: "Quần nỉ bé gái dáng jogger có túi ốp",
    brand: "–",
    status: "Còn hàng",
    price: "550.000₫",
    category: "Quần áo trẻ em",
    createdAt: "2025/02/18 12:58",
    updatedAt: "–"
  },
  {
    id: 2,
    image: "https://salt.tikicdn.com/cache/750x750/ts/product/37/65/3d/a2bc998b48e1443841ba81ef6d72e2a6.jpg.webp",
    name: "Áo khoác gió nam mùa thu kiểu Hàn",
    brand: "CoolMate",
    status: "Hết hàng",
    price: "720.000₫",
    category: "Áo nam",
    createdAt: "2025/03/10 09:24",
    updatedAt: "2025/03/12 15:30"
  },
  {
    id: 3,
    image: "https://salt.tikicdn.com/cache/750x750/ts/product/0c/5c/58/54a155b38e29c261fd4dee249ba092d0.jpg.webp",
    name: "Giày sneaker thể thao nữ siêu nhẹ",
    brand: "Nike",
    status: "Còn hàng",
    price: "1.250.000₫",
    category: "Giày dép",
    createdAt: "2025/01/05 17:20",
    updatedAt: "–"
  }
]);

const searchKeyword = ref('');
const filteredProducts  = computed(()=>{
  if(!searchKeyword.value) return products.value
  return products.value.filter(p=>p.name.toLocaleLowerCase().includes(searchKeyword.value.toLocaleLowerCase()));
});


definePageMeta({
  layout: 'default-admin'
})
</script>
