<template>
  <div class="p-4 md:p-6">
    <h1 class="text-xl md:text-2xl font-bold mb-4">Người dùng</h1>

    <!-- Nút thêm người dùng -->
    <NuxtLink 
      to="/admin/users/create-user"
      class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded mb-4"
    >
      Thêm người dùng mới
    </NuxtLink>
    <!-- Bộ lọc + hành động -->
    <div class="flex flex-col md:flex-row flex-wrap items-center gap-2 mb-4">
      <select class="border p-2 rounded text-sm">
        <option value="">Thao tác hàng loạt</option>
        <option value="delete">Xoá</option>
      </select>
      <button class="bg-gray-200 hover:bg-gray-300 text-sm px-4 py-2 rounded">Áp dụng</button>

      <select class="border p-2 rounded text-sm">
        <option value="">Thêm vai trò...</option>
        <option value="administrator">Quản trị viên</option>
        <option value="editor">Biên tập viên</option>
        <option value="shop_manager">Quản lý cửa hàng</option>
      </select>
      <button class="bg-gray-200 hover:bg-gray-300 text-sm px-4 py-2 rounded">Thêm</button>

      <select class="border p-2 rounded text-sm">
        <option value="">Xóa vai trò...</option>
        <option value="administrator">Quản trị viên</option>
        <option value="editor">Biên tập viên</option>
        <option value="shop_manager">Quản lý cửa hàng</option>
      </select>
      <button class="bg-gray-200 hover:bg-gray-300 text-sm px-4 py-2 rounded">Xóa</button>
    </div>

    <!-- Bảng người dùng -->
    <div class="hidden md:block overflow-auto">
  <table class="min-w-[600px] w-full border text-sm">
    <thead class="bg-gray-100 border">
      <tr>
        <th class="p-2 border text-left"><input type="checkbox" /></th>
        <th class="p-2 border">Tên đăng nhập</th>
        <th class="p-2 border">Họ tên</th>
        <th class="p-2 border">Email</th>
        <th class="p-2 border">Vai trò</th>
        <th class="p-2 border">Bài viết</th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="(user, index) in users"
        :key="index"
        class="hover:bg-gray-50 border-b"
      >
        <td class="p-2 border"><input type="checkbox" /></td>
        <td class="p-2 border font-semibold text-blue-600 cursor-pointer">{{ user.username }}</td>
        <td class="p-2 border">{{ user.name || '—' }}</td>
        <td class="p-2 border">{{ user.email }}</td>
        <td class="p-2 border">{{ convertRole(user.role) }}</td>
        <td class="p-2 border text-blue-600 hover:underline cursor-pointer">{{ user.posts }}</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Card layout cho mobile -->
<div class="md:hidden space-y-4">
  <div
    v-for="(user, index) in users"
    :key="'mobile-' + index"
    class="border rounded-lg p-4 shadow-sm bg-white"
  >
    <div class="flex justify-between items-center mb-2">
      <div>
        <p class="text-blue-600 font-semibold">{{ user.username }}</p>
        <p class="text-sm text-gray-500">{{ user.email }}</p>
      </div>
      <input type="checkbox" />
    </div>
    <p class="text-sm"><span class="font-medium">Họ tên:</span> {{ user.name || '—' }}</p>
    <p class="text-sm"><span class="font-medium">Vai trò:</span> {{ convertRole(user.role) }}</p>
    <p class="text-sm text-blue-600 mt-1"><span class="font-medium">Bài viết:</span> {{ user.posts }}</p>
  </div>
</div>
    <p class="text-sm text-gray-600 mt-2">{{ users.length }} người dùng</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const users = ref([
  { username: 'admin', name: null, email: 'admin@tbchuong.id.vn', role: 'Administrator', posts: 5 },
  { username: 'zeisc2', name: 'Trieu Bao Chuong', email: 'zeisc2@gmail.com', role: 'Shop manager', posts: 0 },
  { username: 'zeisc3', name: 'Trieu Bao Chuong', email: 'zeisc3@gmail.com', role: 'Editor', posts: 0 },
])

const convertRole = (role) => {
  switch(role.toLowerCase()) {
    case 'administrator':
      return 'Quản trị viên'
    case 'editor':
      return 'Biên tập viên'
    case 'shop manager':
    case 'shop_manager':
      return 'Quản lý cửa hàng'
    default:
      return 'Người dùng'
  }
}

definePageMeta({
  layout: 'default-admin'
})
</script>
