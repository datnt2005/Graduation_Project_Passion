<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Thêm bài viết</h1>
  <div class="px-6 pb-4">
    <NuxtLink to="/admin/posts/list-post" class="text-gray-600 hover:underline text-sm">
      Danh sách bài viết
    </NuxtLink>
    <span class="text-gray-600 text-sm"> / Thêm bài viết</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Main Content -->
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-[1200px] mx-auto">
        <form @submit.prevent="submitPost">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
            <!-- Form Main (Left) -->
            <section>
              <div class="bg-white rounded border border-gray-300 shadow-sm p-6 space-y-5">
                <!-- Tiêu đề -->
                <div>
                  <label class="block text-sm font-medium mb-1">Tiêu đề bài viết</label>
                  <input v-model="title" type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Nhập tiêu đề bài viết" required />
                </div>
                <!-- Danh mục -->
                <div>
                  <label class="block text-sm font-medium mb-1">Danh mục bài viết</label>
                  <select v-model="category_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    <option value="" disabled>Chọn danh mục</option>
                    <option :value="cat.id" v-for="cat in categories" :key="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
                <!-- Nội dung -->
                <div>
                  <label class="block text-sm font-medium mb-1">Nội dung</label>
                  <Editor
                    v-model="content"
                    api-key="aa4zr8h53q6wemx77swuav7tsmfd5njtlvgik26k4byi1e9z"
                    :init="{
                      height: 300,
                      menubar: false,
                      plugins: 'lists link image preview',
                      toolbar: 'undo redo | formatselect | bold italic underline |alignjustify alignleft aligncenter alignright | bullist numlist | removeformat | preview | link image | code | h1 h2 h3',
                    }"
                  />
                </div>
              </div>
            </section>

            <!-- Sidebar (Right) -->
            <aside class="w-full lg:w-80 space-y-4 text-xs text-gray-700 font-normal">
              <!-- Upload Ảnh -->
              <section class="border border-gray-300 rounded-md shadow-sm bg-white">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1 px-4 py-3">
                  <h2 class="font-semibold">Ảnh đại diện</h2>
                </header>
                <div class="p-4 space-y-3">
                  <div
                    class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                    @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                    <div class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span></p>
                    </div>
                    <img v-if="preview" :src="preview" alt="Ảnh đại diện" class="absolute inset-0 w-full h-full object-cover rounded" />
                    <button v-if="preview"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
                      @click.stop="removeImage" type="button">×</button>
                  </div>
                  <div class="text-xs text-gray-500">Định dạng: jpg, png, jpeg, webp. Kích thước tối đa 4MB.</div>
                  <div v-if="errors.image" class="text-red-500 text-xs mt-1">{{ errors.image }}</div>
                </div>
              </section>
              <!-- Button Lưu + Thông báo -->
              <div class="bg-white border border-gray-300 rounded-md shadow-sm p-4">
                <div class="flex justify-end gap-2">
                  <NuxtLink
                    to="/admin/posts/list-post"
                    class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                  >
                    Hủy
                  </NuxtLink>
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none"
                    :disabled="loading"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ loading ? 'Đang lưu...' : 'Thêm bài viết' }}
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
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import Editor from '@tinymce/tinymce-vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

definePageMeta({ layout: 'default-admin' })

const router = useRouter()
const title = ref('')
const content = ref('')
const category_id = ref('')
const image = ref(null)
const preview = ref(null)
const categories = ref([])
const loading = ref(false)
const error = ref('')
const success = ref('')
const errors = reactive({})

const fileInput = ref(null)

const handleImageUpload = (e) => {
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
  errors.image = ''
  if (!file) return
  if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type) || file.size > 4 * 1024 * 1024) {
    errors.image = 'Chỉ nhận ảnh jpg, png, jpeg, webp, nhỏ hơn 4MB.'
    return
  }
  image.value = file
  preview.value = URL.createObjectURL(file)
}
const removeImage = () => {
  preview.value = null
  image.value = null
  errors.image = ''
}

onMounted(async () => {
  const token = localStorage.getItem('access_token')
  try {
    const res = await $fetch(`${apiBase}/post-categories`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    categories.value = res.data || []
  } catch (err) {
    error.value = 'Không thể tải danh mục bài viết'
  }
})

const submitPost = async () => {
  error.value = ''
  success.value = ''
  loading.value = true

  if (!title.value || !category_id.value) {
    error.value = 'Vui lòng nhập tiêu đề và chọn danh mục.'
    loading.value = false
    return
  }

  try {
    const formData = new FormData()
    formData.append('title', title.value)
    formData.append('content', content.value)
    formData.append('category_id', category_id.value)
    if (image.value) formData.append('thumbnail', image.value)

    const token = localStorage.getItem('access_token')
    await $fetch(`${apiBase}/posts`, {
      method: 'POST',
      body: formData,
      headers: { Authorization: `Bearer ${token}` },
    })

    success.value = 'Tạo bài viết thành công'
    setTimeout(() => router.push('/admin/posts/list-post'), 1200)
  } catch (err) {
    if (err?.data?.errors) {
      error.value = Object.values(err.data.errors).flat().join(', ')
    } else {
      error.value = 'Tạo bài viết thất bại'
    }
  } finally {
    loading.value = false
  }
}
</script>
