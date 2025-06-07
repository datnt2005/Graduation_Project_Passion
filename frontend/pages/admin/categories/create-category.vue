<template>
 <div class="max-w-2xl mx-auto bg-white p-6 rounded-md shadow-lg border border-gray-200">
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Thêm danh mục</h2>

  <!-- Input Tên -->
  <label class="block text-sm font-semibold text-gray-700 mb-1">Tên danh mục</label>
  <input type="text" class="w-full mb-4 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Nhập tên danh mục" />

  <!-- Textarea -->
  <label class="block text-sm font-semibold text-gray-700 mb-1">Mô tả</label>
  <textarea rows="3" class="w-full mb-4 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Nhập mô tả"></textarea>

  <!-- Select -->
  <label class="block text-sm font-semibold text-gray-700 mb-1">Danh mục cha</label>
  <select class="w-full mb-4 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
    <option>-- Chọn danh mục cha --</option>
    <option>-- Chọn danh mục cha 1--</option>
    <option>-- Chọn danh mục cha 2--</option>
    <option>-- Chọn danh mục cha 3--</option>
  </select>

  <!-- Checkbox -->
  <div class="mb-4 flex items-center">
    <input type="checkbox" id="active" class="mr-2">
    <label for="active" class="text-sm text-gray-700">Hoạt động</label>
  </div>

  <!-- File input -->
  <label class="block text-sm font-semibold text-gray-700 mb-1">Ảnh</label>
  <input type="file" class="mb-6">

  <!-- Submit button -->
<button class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded transition duration-200">
  Lưu danh mục
</button>

</div>
</template>
<script setup>

definePageMeta({
  layout: 'default-admin'
});
</script>
