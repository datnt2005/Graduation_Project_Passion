<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Thêm thuộc tính</h1>
    <div class="px-6 pb-4">
      <nuxt-link to="/admin/attributes/list-attribute" class="text-gray-600 hover:underline text-sm">
        Danh sách thuộc tính
      </nuxt-link>
      <span class="text-gray-600 text-sm"> / Thêm thuộc tính</span>
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
          <form @submit.prevent="createAttribute">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <section class="space-y-4">
                <!-- Form Content -->
                <div class="space-y-2">
                  <!-- Name input -->
                  <div>
                    <label for="attribute-name" class="block text-sm text-gray-700 mb-1">Tên thuộc tính</label>
                    <input id="attribute-name" v-model="formData.name" type="text"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập tên thuộc tính" />
                    <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>
                  </div>

                  <!-- Slug input -->
                  <div class="mt-4">
                    <label for="attribute-slug" class="block text-sm text-gray-700 mb-1">Đường dẫn (Slug)</label>
                    <input id="attribute-slug" v-model="formData.slug" type="text"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập đường dẫn (tùy chọn)" />
                    <span v-if="errors.slug" class="text-red-500 text-xs mt-1">{{ errors.slug }}</span>
                  </div>

                  <!-- Values input -->
                  <div class="mt-4">
                    <label for="attribute-value" class="block text-sm text-gray-700 mb-1">Giá trị thuộc tính</label>
                    <div class="flex gap-2">
                      <input id="attribute-value" v-model="currentValue" type="text"
                        class="flex-1 rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập giá trị (ví dụ: đỏ)" @keyup.enter="addValue" />
                      <button type="button" @click="addValue"
                        class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="!currentValue.trim()">
                        Thêm
                      </button>
                    </div>
                    <span v-if="errors.values" class="text-red-500 text-xs mt-1">{{ errors.values }}</span>
                    <!-- Display added values -->
                    <div v-if="formData.values.length" class="mt-2 space-y-1">
                      <div v-for="(value, index) in formData.values" :key="index"
                        class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200">
                        <span class="text-sm text-gray-700">{{ value }}</span>
                        <button type="button" @click="removeValue(index)"
                          class="text-red-500 hover:text-red-700 focus:outline-none">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Aside -->
              <aside
                class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3 max-w-[320px]">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                  <h2 class="font-semibold">Thông tin thuộc tính</h2>
                </header>
                <div v-if="activeTab === 'overview'" class="space-y-3">
                  <p class="text-sm text-gray-500">
                    Nhập tên thuộc tính và các giá trị tương ứng. Slug là tùy chọn và sẽ được tự động tạo nếu để trống.
                  </p>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <button type="submit"
                    class="w-full bg-blue-600 text-white text-sm font-semibold rounded px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :disabled="loading">
                    {{ loading ? 'Đang xử lý...' : 'Tạo thuộc tính' }}
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
import { ref, reactive } from 'vue';
import { useRouter, useRuntimeConfig } from '#app';
import { secureFetch } from '@/utils/secureFetch' 

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const activeTab = ref('overview');
const loading = ref(false);
const errors = reactive({});
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const currentValue = ref('');

const formData = reactive({
  name: '',
  slug: '',
  values: [],
});

// Add value to the list
const addValue = () => {
  const value = currentValue.value.trim();
  if (value && !formData.values.includes(value)) {
    formData.values.push(value);
    currentValue.value = '';
    errors.values = '';
  } else if (formData.values.includes(value)) {
    errors.values = 'Giá trị này đã tồn tại.';
  }
};

// Remove value from the list
const removeValue = (index) => {
  formData.values.splice(index, 1);
};

// Show notification
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Create attribute
const createAttribute = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key]);

  // Basic client-side validation
  if (!formData.name.trim()) {
    errors.name = 'Tên thuộc tính là bắt buộc.';
    showNotificationMessage('Vui lòng kiểm tra lại dữ liệu.', 'error');
    return;
  }

  const form = new FormData();
  form.append('name', formData.name.trim());
  if (formData.slug) form.append('slug', formData.slug.trim());
  formData.values.forEach((value, index) => {
    form.append(`values[${index}][value]`, value);
  });

  try {
    loading.value = true;
    const response = await secureFetch(`${apiBase}/attributes`, {
      method: 'POST',
      body: form,
      headers: {
        Accept: 'application/json'
      }
    } , ['admin']);

    const data = await response.json();
    console.log('API response:', data); // Debug log

    if (data.success) {
      showNotificationMessage('Tạo thuộc tính thành công!', 'success');
      setTimeout(() => {
        router.push('/admin/attributes/list-attribute');
      }, 1000);
    } else {
      if (data.errors) {
        Object.keys(data.errors).forEach(key => {
          errors[key] = data.errors[key][0];
        });
      }
      showNotificationMessage(data.message || 'Có lỗi xảy ra khi tạo thuộc tính', 'error');
    }
  } catch (error) {
    console.error('Error creating attribute:', error);
    showNotificationMessage('Có lỗi xảy ra khi tạo thuộc tính' , 'error');
  } finally {
    loading.value = false;
  }
};
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