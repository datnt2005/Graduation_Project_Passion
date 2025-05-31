<template>
  <!-- Tiêu đề -->
  <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
    <h1 class="text-2xl font-bold">Sản phẩm</h1>
    <div class="space-x-2">
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Thêm sản phẩm mới</button>
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
    <input type="text" placeholder="Tìm kiếm sản phẩm..." class="border p-2 rounded w-full md:w-1/3" />
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
      <tbody>
        <!-- Dòng sản phẩm mẫu -->
        <tr class="border-t hover:bg-gray-50">
          <td class="p-2"><input type="checkbox" /></td>
          <td class="p-2"><img src="https://via.placeholder.com/40" alt="Product" class="h-10 w-10 object-cover" /></td>
          <td class="p-2 text-blue-600 font-medium">Quần nỉ bé gái dáng jogger có túi ốp</td>
          <td class="p-2">–</td>
          <td class="p-2 text-green-600 font-semibold">Còn hàng</td>
          <td class="p-2">550.000₫</td>
          <td class="p-2">Quần áo trẻ em</td>
          <td class="p-2">2025/02/18 12:58</td>
          <td class="p-2">–</td>
          <td class="p-2">
            <!-- Nút hành động có thể thêm tại đây -->
            <button class="text-blue-600 hover:underline">Sửa</button>
          </td>
        </tr>
        <!-- Thêm các dòng khác nếu cần -->
      </tbody>
    </table>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default-admin'
})
</script>
