<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Thêm danh mục bài viết</h1>
  <div class="px-6 pb-4">
    <NuxtLink to="/admin/post-categories/list-post-category" class="text-gray-600 hover:underline text-sm">
      Danh sách danh mục
    </NuxtLink>
    <span class="text-gray-600 text-sm"> / Thêm danh mục</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-[800px] mx-auto">
        <form @submit.prevent="submitCategory">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
            <!-- Main Info -->
            <section>
              <div class="bg-white rounded border border-gray-300 shadow-sm p-6 space-y-5">
                <div>
                  <label class="block text-sm font-medium mb-1">Tên danh mục</label>
                  <input
                    v-model="name"
                    type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-green-500"
                    placeholder="Nhập tên danh mục"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Slug</label>
                  <input
                    v-model="slug"
                    type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-green-500"
                    placeholder="slug (không dấu, viết liền)"
                    required
                  />
                </div>
              </div>
            </section>
            <!-- Sidebar -->
            <aside class="w-full lg:w-80 space-y-4 text-xs text-gray-700 font-normal">
              <!-- Upload Ảnh -->
              <section class="border border-gray-300 rounded-md shadow-sm bg-white">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1 px-4 py-3">
                  <h2 class="font-semibold">Hình ảnh</h2>
                </header>
                <div class="p-4 space-y-3">
                  <div
                    class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                    @click="triggerFileInput"
                  >
                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
                    <div v-if="!preview" class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span></p>
                    </div>
                    <img
                      v-if="preview"
                      :src="preview"
                      alt="Preview"
                      class="absolute inset-0 w-full h-full object-cover rounded"
                    />
                    <button v-if="preview"
                      class="absolute top-2 right-2 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-blue-700"
                      @click.stop="removeImage"
                      type="button">×</button>
                  </div>
                  <div class="text-xs text-gray-500">Định dạng: jpg, png, jpeg, webp. Kích thước tối đa 4MB.</div>
                </div>
              </section>
              <!-- Button + Thông báo -->
              <div class="bg-white border border-gray-300 rounded-md shadow-sm p-4">
                <div class="flex justify-end gap-2">
                  <NuxtLink
                    to="/admin/post-categories/list-post-category"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200 hover:text-blue-700 transition-colors duration-150"
                  >
                    Hủy
                  </NuxtLink>
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-150"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Lưu
                  </button>
                </div>
                <div v-if="error" class="mt-4 text-red-600">{{ error }}</div>
                <div v-if="success" class="mt-4 text-green-600">{{ success }}</div>
              </div>
            </aside>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
definePageMeta({
  layout: 'default-admin',
})

const name = ref('')
const slug = ref('')
const image = ref(null)
const preview = ref(null)
const error = ref('')
const success = ref('')
const router = useRouter()
const fileInput = ref(null)

const onFileChange = (e) => {
  const file = e.target.files[0]
  validateAndPreviewImage(file)
}
const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  validateAndPreviewImage(file)
}
const triggerFileInput = () => {
  fileInput.value.click()
}
const validateAndPreviewImage = (file) => {
  if (!file) return
  if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type) || file.size > 4 * 1024 * 1024) {
    error.value = 'Chỉ nhận ảnh jpg, png, jpeg, webp, nhỏ hơn 4MB.'
    return
  }
  image.value = file
  preview.value = URL.createObjectURL(file)
}
const removeImage = () => {
  preview.value = null
  image.value = null
}

const submitCategory = async () => {
  error.value = ''
  success.value = ''
  if (!name.value || !slug.value) {
    error.value = 'Vui lòng nhập đầy đủ tên và slug.'
    return
  }
  try {
    const formData = new FormData()
    formData.append('name', name.value)
    formData.append('slug', slug.value)
    if (image.value) formData.append('image', image.value)
    const token = localStorage.getItem('access_token')
    await $fetch(`${apiBase}/post-categories`, {
      method: 'POST',
      body: formData,
      headers: { Authorization: `Bearer ${token}` },
    })
    success.value = 'Thêm danh mục thành công!'
    setTimeout(() => router.push('/admin/post-categories/list-post-category'), 1200)
  } catch (err) {
    if (err?.data?.errors) {
      error.value = Object.values(err.data.errors).flat().join(', ')
    } else {
      error.value = 'Có lỗi xảy ra khi thêm danh mục.'
    }
    console.error(err)
  }
}
</script>
