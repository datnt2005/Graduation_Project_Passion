<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Chỉnh sửa danh mục bài viết</h1>
  <div class="px-6 pb-4">
    <NuxtLink to="/admin/post-categories/list-post-category" class="text-gray-600 hover:underline text-sm">
      Danh sách danh mục
    </NuxtLink>
    <span class="text-gray-600 text-sm"> / Chỉnh sửa danh mục</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-[800px] mx-auto">
        <form @submit.prevent="submitEdit">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
            <!-- Main Info -->
            <section>
              <div class="bg-white rounded border border-gray-300 shadow-sm p-6 space-y-5">
                <div>
                  <label class="block text-sm font-medium mb-1">Tên danh mục</label>
                  <input
                    v-model="name"
                    type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Nhập tên danh mục"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Slug</label>
                  <input
                    v-model="slug"
                    type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="slug (không dấu, viết liền)"
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
                    <div v-if="!preview && !imageUrl" class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span></p>
                    </div>
                    <img
                      v-if="preview || imageUrl"
                      :src="preview || imageUrl"
                      alt="Preview"
                      class="absolute inset-0 w-full h-full object-cover rounded"
                    />
                    <button v-if="preview || imageUrl"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600"
                      @click.stop="removeImage"
                      type="button">×</button>
                  </div>
                  <div class="text-xs text-gray-500">Định dạng: jpg, png, jpeg, webp. Kích thước tối đa 4MB.</div>
                </div>
              </section>
              <!-- Button -->
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
                    :disabled="loading"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ loading ? 'Đang lưu...' : 'Lưu thay đổi' }}
                  </button>
                </div>
              </div>
            </aside>
          </div>
        </form>
      </div>
    </main>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
        >
          <div class="flex-shrink-0">
            <svg
              class="h-6 w-6"
              :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                v-if="notificationType === 'success'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
              <path
                v-if="notificationType === 'error'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button
              @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

definePageMeta({
  layout: 'default-admin',
})

const name = ref('')
const slug = ref('')
const image = ref(null)
const imageUrl = ref('')
const preview = ref(null)
const loading = ref(false)

const router = useRouter()
const route = useRoute()
const fileInput = ref(null)
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')

const generateSlug = (text) => {
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // Remove accents
    .replace(/[^a-z0-9-]/g, '-') // Replace non-alphanumeric characters with hyphen
    .replace(/-+/g, '-') // Replace multiple hyphens with a single hyphen
    .replace(/^-|-$/g, '') // Remove leading/trailing hyphens
}

const fetchCategory = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const data = await $fetch(`${apiBase}/post-categories/${route.params.id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })

    name.value = data.data.name
    slug.value = data.data.slug
    imageUrl.value = data.data.image_url
  } catch (err) {
    showNotificationMessage('Không thể tải thông tin danh mục.', 'error')
  }
}

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
    showNotificationMessage('Chỉ nhận ảnh jpg, png, jpeg, webp, nhỏ hơn 4MB.', 'error')
    return
  }
  image.value = file
  preview.value = URL.createObjectURL(file)
  imageUrl.value = ''
}
const removeImage = () => {
  preview.value = null
  image.value = null
  imageUrl.value = ''
}

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => (showNotification.value = false), 3000)
}

const submitEdit = async () => {
  if (!name.value) {
    showNotificationMessage('Vui lòng nhập tên danh mục.', 'error')
    return
  }
  // Generate slug if not provided
  if (!slug.value) {
    slug.value = generateSlug(name.value)
  }

  loading.value = true
  try {
    const formData = new FormData()
    formData.append('name', name.value)
    formData.append('slug', slug.value)
    if (image.value) formData.append('image', image.value)

    const token = localStorage.getItem('access_token')
    await $fetch(`${apiBase}/post-categories/${route.params.id}`, {
      method: 'POST', // PUT bị lỗi FormData -> dùng POST + method spoofing
      body: formData,
      headers: {
        Authorization: `Bearer ${token}`,
        'X-HTTP-Method-Override': 'PUT',
      },
    })

    showNotificationMessage('Cập nhật danh mục thành công!', 'success')
    setTimeout(() => router.push('/admin/post-categories/list-post-category'), 1200)
  } catch (err) {
    if (err?.data?.errors) {
      showNotificationMessage(Object.values(err.data.errors).flat().join(', '), 'error')
    } else {
      showNotificationMessage('Có lỗi xảy ra khi cập nhật danh mục.', 'error')
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchCategory)
</script>

<style scoped>
/* Add any component-specific styles here */
</style>