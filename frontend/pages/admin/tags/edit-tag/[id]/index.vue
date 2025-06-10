<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Sửa thẻ sản phẩm</h1>
    <div class="px-6 pb-4">
      <nuxt-link to="/admin/tags/list-tag" class="text-gray-600 hover:underline text-sm">
        Thẻ
      </nuxt-link>
      <span class="text-gray-600 text-sm"> / Sửa thẻ</span>
    </div>

    <div class="flex min-h-screen bg-gray-100">
      <!-- Sidebar -->
      <nav class="w-64 bg-white border-r border-gray-200">
        <ul class="py-2">
          <li>
            <button @click="activeTab = 'overview'" :class="[
              'flex items-center w-full px-4 py-2 text-sm transition-colors',
              activeTab === 'overview' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              Tổng quan
            </button>
          </li>
        </ul>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 p-6 bg-gray-100">
        <div class="max-w-[1200px] mx-auto">
          <form @submit.prevent="updateTag">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <section class="space-y-4">
                <!-- Form Content -->
                <div class="space-y-2">
                  <!-- Name input -->
                  <div>
                    <label for="tag-name" class="block text-sm text-gray-700 mb-1">Tên thẻ</label>
                    <input id="tag-name" v-model="formData.name" type="text"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập tên thẻ" />
                    <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>
                  </div>

                  <!-- Slug input -->
                  <div class="mt-4">
                    <label for="tag-slug" class="block text-sm text-gray-700 mb-1">Đường dẫn (Slug)</label>
                    <input id="tag-slug" v-model="formData.slug" type="text"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập đường dẫn (tùy chọn)" />
                    <span v-if="errors.slug" class="text-red-500 text-xs mt-1">{{ errors.slug }}</span>
                  </div>
                </div>
              </section>

              <!-- Status and Image Aside -->
              <aside
                class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3 max-w-[320px]">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                  <h2 class="font-semibold">Hình ảnh thẻ</h2>
                </header>
                <div v-if="activeTab === 'overview'" class="space-y-3">
                  <!-- Drag & Drop + Click Upload Box -->
                  <div
                    class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                    @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
                    <input ref="fileInput" id="tag-image" type="file" accept="image/*" class="hidden"
                      @change="handleImageUpload" />
                    <div class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span>
                      </p>
                    </div>
                  </div>
                  <!-- Error -->
                  <span v-if="errors.image" class="text-red-500 text-xs mt-1 block">{{ errors.image }}</span>
                  <!-- Preview -->
                  <div v-if="imagePreview" class="mt-3">
                    <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded border" />
                  </div>
                </div>

                <!-- <header class="flex items-center justify-between border-b border-gray-300 pb-1 mt-4">
                  <h2 class="font-semibold">Trạng thái</h2>
                </header>
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <label class="flex items-center space-x-2">
                      <input type="radio" v-model="formData.status" value="active" class="form-radio text-blue-600" />
                      <span>Kích hoạt</span>
                    </label>
                  </div>
                  <div class="flex items-center gap-2">
                    <label class="flex items-center space-x-2">
                      <input type="radio" v-model="formData.status" value="inactive" class="form-radio text-blue-600" />
                      <span>Không kích hoạt</span>
                    </label>
                  </div>
                </div> -->

                <!-- Submit Button -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <button type="submit"
                    class="w-full bg-blue-600 text-white text-sm font-semibold rounded px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="loading">
                    {{ loading ? 'Đang xử lý...' : 'Cập nhật thẻ' }}
                  </button>
                </div>
              </aside>
            </div>
          </form>
        </div>
      </main>
    </div>

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
                class="h-5 w-5"
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
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute, useRuntimeConfig } from '#app';

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const route = useRoute();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const activeTab = ref('overview');
const loading = ref(false);
const errors = reactive({});
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success'); // 'success' or 'error'
const tags = ref([]);
const imagePreview = ref(null);
const fileInput = ref(null);

const formData = reactive({
  name: '',
  slug: '',
  parent_id: null,
  image: null,
  status: 'active'
});



// Fetch tag details
const fetchTag = async () => {
  if (!route.params.id) {
    console.error('No tag ID provided in route params');
    showNotificationMessage('Không tìm thấy ID thẻ' , 'error');
    router.push('/admin/tags/list-tag');
    return;
  }

  try {
    console.log('Fetching tag with ID:', route.params.id);
    const response = await fetch(`${apiBase}/tags/${route.params.id}`);
    const data = await response.json();
    console.log('tag API response:', data);

    if (!response.ok) {
      throw new Error(`Có lỗi xảy ra khi lấy thông tin thẻ`, 'error' );
    }

    // Handle different response formats
    const tag = data.tag || data.data || data;
    if (tag && tag.name) {
      formData.name = tag.name;
      formData.slug = tag.slug;
      formData.status = tag.status || 'active';
      if (tag.image) {
        imagePreview.value = `${mediaBase}${tag.image}`;
      }
    } else {
      console.error('tag data not found in response:', data);
      showNotificationMessage('Không tìm thấy thẻ' , 'error');
      router.push('/admin/tags/list-tag');
    }
  } catch (error) {
    console.error('Error fetching tag:', error.message);
    showNotificationMessage(`Có lỗi xảy ra khi lấy thông tin thẻ` , 'error');

  }
};

// Filter out current tag from parent dropdown
const filteredtags = computed(() => {
  return tags.value.filter(tag => tag.id !== parseInt(route.params.id));
});

// Trigger file input click
const triggerFileInput = () => {
  fileInput.value.click();
};

// Handle drag-and-drop
const handleDrop = (event) => {
  const file = event.dataTransfer.files[0];
  if (file) {
    handleImageUpload({ target: { files: [file] } });
  }
};

// Handle image upload
const handleImageUpload = (event) => {
  const file = event.target.files[0] || event.dataTransfer?.files?.[0];
  if (file) {
    formData.image = file;
    errors.image = null;
    // Generate image preview
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    formData.image = null;
    errors.image = '';
  }
};

// Show success notification
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Update tag
const updateTag = async () => {
  const form = new FormData();
  form.append('name', formData.name);
  form.append('slug', formData.slug );
  if (formData.image) form.append('image', formData.image);
  form.append('_method', 'PUT');

  try {
    loading.value = true;
    console.log('Updating tag with ID:', route.params.id);
    const response = await fetch(`${apiBase}/tags/${route.params.id}`, {
      method: 'POST', // Use POST with _method=PATCH for method spoofing
      body: form
    });
    const data = await response.json();
    console.log('Update API response:', data);

    if (data.success) {
      showNotificationMessage('Cập nhật thẻ thành công!' , 'success');
      setTimeout(() => {
        router.push('/admin/tags/list-tag');
      }, 1000);
    } else {
      if (data.errors) {
        Object.keys(data.errors).forEach(key => {
          errors[key] = data.errors[key][0];
        });
      } else {
        showNotificationMessage(data.message || 'Có lỗi xảy ra khi cập nhật thẻ' , 'error');
      }
    }
  } catch (error) {
    console.error('Error updating tag:', error);
    showNotificationMessage('Có lỗi xảy ra khi cập nhật thẻ' + error.message, 'error');
  } finally {
    loading.value = false;
  }
};

// Fetch data on mount
onMounted(() => {
  console.log('API Base URL:', apiBase);
  fetchTag();
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

button {
  position: relative;
  overflow: hidden;
}

button::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  pointer-events: none;
  background-image: radial-gradient(circle, #000 10%, transparent 10.01%);
  background-repeat: no-repeat;
  background-position: 50%;
  transform: scale(10, 10);
  opacity: 0;
  transition: transform .5s, opacity 1s;
}

button:active::after {
  transform: scale(0, 0);
  opacity: .2;
  transition: 0s;
}
</style>