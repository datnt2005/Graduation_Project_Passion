<template>
  <aside class="w-64 bg-white h-screen shadow-md p-6 hidden md:block">
    <h2 class="text-2xl font-semibold text-gray-800 mb-8">Seller Dashboard</h2>

    <nav class="flex flex-col space-y-3 text-gray-700">
      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-home w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'house']" /> Bảng điều khiển</span>
      </a>

      <div class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 cursor-pointer transition">
        <div class="flex items-center gap-4">
          <i class="fas fa-box-open w-5 text-blue-500"></i>
          <span class="font-medium"><font-awesome-icon :icon="['fas', 'box']" /> Sản phẩm</span>
        </div>
        <i class="fas fa-chevron-down text-sm"></i>
      </div>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-shopping-bag w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'box-archive']" /> Đơn hàng</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-tags w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'tags']" /> Mã giảm giá</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-comments w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'comments']" /> Bình luận & đánh giá</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-file-alt w-5 text-blue-500"></i>
        <span class="font-medium">Bài viết</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-bell w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'bell']" /> Thông báo</span>
      </a>
    </nav>
  </aside>
</template>

<script setup>
</script>
