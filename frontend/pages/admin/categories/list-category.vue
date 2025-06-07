<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold">Quản lý danh mục</h1>
        <p class="text-sm text-gray-500">Quản lý danh mục sản phẩm của bạn</p>
      </div>
      <NuxtLink to="/admin/categories/create-category"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium"
      >
        + Thêm danh mục
      </NuxtLink>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm border border-gray-200 bg-white shadow rounded-md">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-4 py-3 border">ID</th>
            <th class="px-4 py-3 border">Ảnh</th>
            <th class="px-4 py-3 border">Tên danh mục</th>
            <th class="px-4 py-3 border">Mô tả</th>
            <th class="px-4 py-3 border">Danh mục cha</th>
            <th class="px-4 py-3 border">Trạng thái</th>
            <th class="px-4 py-3 border">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="cat in categories"
            :key="cat.id"
            class="hover:bg-gray-50 border-t"
          >
            <td class="px-4 py-3 border text-gray-700 font-medium">#{{ cat.id }}</td>
            <td class="px-4 py-3 border">
              <img :src="cat.image" alt="img" class="w-12 h-12 object-cover rounded" />
            </td>
            <td class="px-4 py-3 border">{{ cat.name }}</td>
            <td class="px-4 py-3 border">{{ cat.description || '—' }}</td>
            <td class="px-4 py-3 border">
              {{ getParentName(cat.parent_id) }}
            </td>
            <td class="px-4 py-3 border">
              <span
                :class="[
                  'px-2 py-1 text-xs rounded',
                  cat.active
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-500'
                ]"
              >
                {{ cat.active ? 'Hoạt động' : 'Ẩn' }}
              </span>
            </td>
            <td class="px-4 py-3 border space-x-2">
              <button
                class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 text-xs rounded"
              >
                ✏️
              </button>
              <button
                class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 text-xs rounded"
              >
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
const categories = ref([
  {
    id: 1,
    name: 'Sữa tươi',
    slug: 'sua-tuoi',
    description: 'Sản phẩm sữa tươi nguyên chất từ trang trại',
    image: 'https://example.com/images/sua-tuoi.jpg',
    parent_id: null,
    active: true
  },
  {
    id: 2,
    name: 'Sữa chua',
    slug: 'sua-chua',
    description: 'Sữa chua lên men tự nhiên',
    image: 'https://example.com/images/sua-chua.jpg',
    parent_id: 1,
    active: true
  },
  {
    id: 3,
    name: 'Sữa đặc',
    slug: 'sua-dac',
    description: 'Sữa đặc có đường dùng pha chế',
    image: 'https://example.com/images/sua-dac.jpg',
    parent_id: null,
    active: false
  },
  {
    id: 4,
    name: 'Sữa chua uống',
    slug: 'sua-chua-uong',
    description: '',
    image: 'https://example.com/images/sua-chua-uong.jpg',
    parent_id: 2,
    active: true
  }
]);

// Lấy tên danh mục cha từ ID
const getParentName = (parentId) => {
  if (!parentId) return 'Không có';
  const parent = categories.value.find((cat) => cat.id === parentId);
  return parent ? parent.name : 'Không rõ';
};

definePageMeta({
  layout: 'default-admin'
});
</script>
