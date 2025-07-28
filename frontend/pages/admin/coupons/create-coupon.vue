<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Thêm chiết khấu</h1>
  <div class="px-6 pb-4">
    <nuxt-link to="/admin/coupons/list-coupon" class="text-gray-600 hover:underline text-sm">
      Danh sách chiết khấu
    </nuxt-link>
    <span class="text-gray-600 text-sm"> / Thêm chiết khấu</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-700 font-medium">Đang tải dữ liệu...</p>
      </div>
    </div>

    <!-- Sidebar -->
    <nav class="w-64 bg-white border-r border-gray-200">
      <ul class="py-2">
        <li>
          <button
            @click="activeTab = 'overview'"
            :class="[
              'flex items-center w-full px-4 py-2 text-sm transition-colors',
              activeTab === 'overview' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Tổng quan
          </button>
        </li>
        <li>
          <button
            @click="activeTab = 'restrictions'"
            :class="[
              'flex items-center w-full px-4 py-2 text-sm transition-colors',
              activeTab === 'restrictions' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Hạn chế sử dụng
          </button>
        </li>
        <li>
          <button
            @click="activeTab = 'limits'"
            :class="[
              'flex items-center w-full px-4 py-2 text-sm transition-colors',
              activeTab === 'limits' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Giới hạn sử dụng
          </button>
        </li>
      </ul>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-[1200px] mx-auto">
        <form @submit.prevent="createCoupon">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
            <section class="space-y-4">
              <!-- Form Content -->
              <div class="space-y-2">
                <label for="discount-code" class="block text-sm text-gray-700 mb-1">Mã giảm giá</label>
                <input
                  id="discount-code"
                  v-model="formData.code"
                  type="text"
                  class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nhập mã giảm giá"
                />
                <span v-if="errors.code" class="text-red-500 text-xs mt-1">{{ errors.code }}</span>
                <button
                  type="button"
                  @click="generateCode"
                  class="inline-block rounded border border-blue-400 bg-blue-100 text-blue-700 text-xs px-2 py-0.5"
                >
                  Tạo mã phiếu giảm giá
                </button>

                <!-- Name input -->
                <div class="mt-4">
                  <label for="discount-name" class="block text-sm text-gray-700 mb-1">Tên mã giảm giá</label>
                  <input
                    id="discount-name"
                    v-model="formData.name"
                    type="text"
                    class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nhập tên mã giảm giá"
                  />
                  <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>
                </div>

                <!-- Description Textarea -->
                <div class="mt-4">
                  <label for="description" class="block text-sm text-gray-700 mb-1">Mô tả</label>
                  <textarea
                    id="description"
                    v-model="formData.description"
                    rows="3"
                    placeholder="Mô tả (tùy chọn)"
                    class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>

                <!-- Tabbed Content -->
                <div class="bg-white rounded border border-gray-300 shadow-sm mt-4">
                  <header class="flex items-center justify-between px-3 py-2 border-b border-gray-300 text-gray-700 font-semibold text-sm">
                    <span>Dữ liệu phiếu giảm giá</span>
                  </header>
                  <div class="flex-1 p-4 text-xs text-gray-700 space-y-3">
                    <!-- Tổng quan Tab -->
                    <div v-if="activeTab === 'overview'">
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="discount-type" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Loại giảm giá
                        </label>
                        <select
                          id="discount-type"
                          v-model="formData.discount_type"
                          class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                          <option value="fixed">Giảm giá cố định</option>
                          <option value="percentage">Giảm giá theo phần trăm</option>
                          <option value="shipping_fee">Giảm giá vận chuyển</option>

                        </select>
                        <span v-if="errors.discount_type" class="text-red-500 text-xs mt-1">{{ errors.discount_type }}</span>
                      </div>
                      <br>
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="discount-amount" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Giá trị giảm giá
                        </label>
                        <input
                          id="discount-amount"
                          v-model="formData.discount_value"
                          type="number"
                          min="0"
                          class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <span v-if="errors.discount_value" class="text-red-500 text-xs mt-1">{{ errors.discount_value }}</span>
                      </div>
                      <br>
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Thời gian hiệu lực
                        </label>
                        <div class="flex space-x-2">
                          <div class="flex flex-col flex-1">
                            <input
                              v-model="formData.start_date"
                              type="date"
                              :min="currentDate"
                              class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <span v-if="errors.start_date" class="text-red-500 text-xs mt-1">{{ errors.start_date }}</span>
                          </div>
                          <div class="flex flex-col flex-1">
                            <input
                              v-model="formData.end_date"
                              type="date"
                              :min="formData.start_date || currentDate"
                              class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <span v-if="errors.end_date" class="text-red-500 text-xs mt-1">{{ errors.end_date }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Hạn chế sử dụng Tab -->
                    <div v-if="activeTab === 'restrictions'">
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="min-spend" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Chi tiêu tối thiểu
                        </label>
                        <input
                          id="min-spend"
                          v-model="formData.min_order_value"
                          type="number"
                          min="0"
                          placeholder="Không có tối thiểu"
                          class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <span v-if="errors.min_order_value" class="text-red-500 text-xs mt-1">{{ errors.min_order_value }}</span>
                      <br>
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="min-spend" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Các sản phẩm được áp dụng
                        </label>
                        <div class="relative w-full md:w-60">
                          <div class="relative">
                            <div 
                              class="w-full rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 min-h-[28px] flex flex-wrap items-start cursor-text"
                              @click="$refs.productInput?.focus()"
                            >
                              <div v-if="selectedProducts.length > 0" class="flex flex-wrap gap-1.5 py-0.5" style="max-width: calc(100% - 20px);">
                                <div 
                                  v-for="product in selectedProducts" 
                                  :key="product.id"
                                  class="bg-gray-100 px-1.5 py-0.5 rounded flex items-center gap-1 mb-1"
                                  style="max-width: calc(33.33% - 4px); overflow: hidden;"
                                >
                                  <span class="text-xs truncate">{{ product.name }}</span>
                                  <button 
                                    @click.stop="toggleSelection('products', product)"
                                    class="text-gray-500 hover:text-gray-700 flex-shrink-0"
                                  >
                                    ×
                                  </button>
                                </div>
                              </div>
                              <input
                                ref="productInput"
                                v-model="productSearch"
                                type="text"
                                placeholder="Tìm sản phẩm..."
                                class="flex-1 outline-none text-xs min-w-[50px] bg-transparent py-0.5"
                                @focus="activeDropdown = 'products'"
                              />
                            </div>
                          </div>
                          <!-- Products Dropdown -->
                          <div
                            v-if="activeDropdown === 'products' && filteredProducts.length > 0"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg"
                          >
                            <div class="max-h-48 overflow-y-auto">
                              <div
                                v-for="product in filteredProducts"
                                :key="product.id"
                                class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer flex items-center"
                                @click.stop="toggleSelection('products', product); productSearch = ''"
                              >
                                <input
                                  type="checkbox"
                                  :checked="isSelected('products', product)"
                                  class="mr-2"
                                  @click.stop
                                />
                                <span class="text-xs">{{ product.name }}</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <br>
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="min-spend" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Các danh mục được áp dụng
                        </label>
                        <div class="relative w-full md:w-60">
                          <div class="relative">
                            <div 
                              class="w-full rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 min-h-[28px] flex flex-wrap items-start cursor-text"
                              @click="$refs.categoryInput?.focus()"
                            >
                              <div v-if="selectedCategories.length > 0" class="flex flex-wrap gap-1.5 py-0.5" style="max-width: calc(100% - 20px);">
                                <div 
                                  v-for="category in selectedCategories" 
                                  :key="category.id"
                                  class="bg-gray-100 px-1.5 py-0.5 rounded flex items-center gap-1 mb-1"
                                  style="max-width: calc(33.33% - 4px); overflow: hidden;"
                                >
                                  <span class="text-xs truncate">{{ category.name }}</span>
                                  <button 
                                    @click.stop="toggleSelection('categories', category)"
                                    class="text-gray-500 hover:text-gray-700 flex-shrink-0"
                                  >
                                    ×
                                  </button>
                                </div>
                              </div>
                              <input
                                ref="categoryInput"
                                v-model="categorySearch"
                                type="text"
                                placeholder="Tìm danh mục..."
                                class="flex-1 outline-none text-xs min-w-[50px] bg-transparent py-0.5"
                                @focus="activeDropdown = 'categories'"
                              />
                            </div>
                          </div>
                          <!-- Categories Dropdown -->
                          <div
                            v-if="activeDropdown === 'categories' && filteredCategories.length > 0"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg"
                          >
                            <div class="max-h-48 overflow-y-auto">
                              <div
                                v-for="category in filteredCategories"
                                :key="category.id"
                                class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer flex items-center"
                                @click.stop="toggleSelection('categories', category); categorySearch = ''"
                              >
                                <input
                                  type="checkbox"
                                  :checked="isSelected('categories', category)"
                                  class="mr-2"
                                  @click.stop
                                />
                                <span class="text-xs">{{ category.name }}</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Giới hạn sử dụng Tab -->
                    <div v-if="activeTab === 'limits'">
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="limit-per-coupon" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Giới hạn sử dụng
                        </label>
                        <input
                          id="limit-per-coupon"
                          v-model="formData.usage_limit"
                          type="number"
                          min="0"
                          placeholder="Không giới hạn"
                          class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <span v-if="errors.usage_limit" class="text-red-500 text-xs mt-1">{{ errors.usage_limit }}</span>
                    </div>
                    <div v-if="activeTab === 'limits'">
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="limit-per-coupon" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Giới hạn người dùng sử dụng
                        </label>
                        <div class="relative w-full md:w-60">
                          <div class="relative">
                            <div 
                              class="w-full rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 min-h-[28px] flex flex-wrap items-start cursor-text"
                              @click="$refs.userInput?.focus()"
                            >
                              <div v-if="selectedUsers.length > 0" class="flex flex-wrap gap-1.5 py-0.5" style="max-width: calc(100% - 20px);">
                                <div 
                                  v-for="user in selectedUsers" 
                                  :key="user.id"
                                  class="bg-gray-100 px-1.5 py-0.5 rounded flex items-center gap-1 mb-1"
                                  style="max-width: calc(33.33% - 4px); overflow: hidden;"
                                >
                                  <span class="text-xs truncate">{{ user.name }}</span>
                                  <button 
                                    @click.stop="toggleSelection('users', user)"
                                    class="text-gray-500 hover:text-gray-700 flex-shrink-0"
                                  >
                                    ×
                                  </button>
                                </div>
                              </div>
                              <input
                                ref="userInput"
                                v-model="userSearch"
                                type="text"
                                placeholder="Tìm người dùng..."
                                class="flex-1 outline-none text-xs min-w-[50px] bg-transparent py-0.5"
                                @focus="activeDropdown = 'users'"
                              />
                            </div>
                          </div>
                          <!-- Users Dropdown -->
                          <div
                            v-if="activeDropdown === 'users' && filteredUsers.length > 0"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg"
                          >
                            <div class="max-h-48 overflow-y-auto">
                              <div
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer flex items-center"
                                @click.stop="toggleSelection('users', user); userSearch = ''"
                              >
                                <input
                                  type="checkbox"
                                  :checked="isSelected('users', user)"
                                  class="mr-2"
                                  @click.stop
                                />
                                <span class="text-xs">{{ user.name }}</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Status Aside -->
            <aside class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3 max-w-[320px]">
              <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                <h2 class="font-semibold">Trạng thái</h2>
              </header>
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <label class="flex items-center space-x-2">
                    <input
                      type="radio"
                      v-model="formData.status"
                      value="active"
                      class="form-radio text-blue-600"
                    />
                    <span>Kích hoạt</span>
                  </label>
                </div>
                <div class="flex items-center gap-2">
                  <label class="flex items-center space-x-2">
                    <input
                      type="radio"
                      v-model="formData.status"
                      value="inactive"
                      class="form-radio text-blue-600"
                    />
                    <span>Không kích hoạt</span>
                  </label>
                </div>
                <span v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</span>
              </div>

              <!-- Submit Button -->
              <div class="pt-4 mt-4 border-t border-gray-200">
                <button
                  type="submit"
                  class="w-full bg-blue-600 text-white text-sm font-semibold rounded px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :disabled="loading"
                >
                  {{ loading ? 'Đang xử lý...' : 'Tạo mã giảm giá' }}
                </button>
              </div>
            </aside>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- Thêm Notification Popup -->
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
            class="h-6 w-6 text-green-400"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
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
</template>

<script setup>
definePageMeta({
  layout: 'default-admin'
});

import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useRuntimeConfig } from '#imports'
import { secureFetch } from '@/utils/secureFetch'

const products = ref([]);
const categories = ref([]);
const users = ref([]);

const router = useRouter();
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const activeTab = ref('overview');
const loading = ref(false);
const errors = reactive({});
const showNotification = ref(false);
const notificationMessage = ref('');

// Thêm computed property cho ngày hiện tại
const currentDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Hàm hiển thị notification
const showSuccessNotification = (message) => {
  notificationMessage.value = message;
  showNotification.value = true;
  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

const showErrorNotification = (message) => {
  notificationMessage.value = message;
  showNotification.value = true;
  // Có thể thêm style màu đỏ hoặc icon lỗi nếu muốn
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

const formData = reactive({
  name: '',
  code: '',
  description: '',
  discount_type: 'fixed',
  discount_value: 0,
  usage_limit: null,
  min_order_value: null,
  start_date: '',
  end_date: '',
  status: 'active'
});

const activeDropdown = ref(null);
const productSearch = ref('');
const categorySearch = ref('');
const userSearch = ref('');

const selectedProducts = ref([]);
const selectedCategories = ref([]);
const selectedUsers = ref([]);

// Computed properties cho filtered items với tìm kiếm không phân biệt dấu
const removeVietnameseTones = (str) => {
  str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
  str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
  str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
  str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
  str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
  str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
  str = str.replace(/đ/g, "d");
  str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
  str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
  str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
  str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
  str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
  str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
  str = str.replace(/Đ/g, "D");
  return str;
};

const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value
  const searchTerm = removeVietnameseTones(productSearch.value.toLowerCase())
  return products.value.filter(product =>
    removeVietnameseTones((product.name || '').toLowerCase()).includes(searchTerm)
  )
})

const filteredCategories = computed(() => {
  if (!categorySearch.value) return categories.value
  const searchTerm = removeVietnameseTones(categorySearch.value.toLowerCase())
  return categories.value.filter(category =>
    removeVietnameseTones((category.name || '').toLowerCase()).includes(searchTerm)
  )
})

const filteredUsers = computed(() => {
  if (!userSearch.value) return users.value
  const searchTerm = removeVietnameseTones(userSearch.value.toLowerCase())
  return users.value.filter(user =>
    removeVietnameseTones((user.name || '').toLowerCase()).includes(searchTerm)
  )
})

// Thêm refs cho input
const productInput = ref(null);
const categoryInput = ref(null);
const userInput = ref(null);

// Cập nhật toggleSelection để xóa search text sau khi chọn
const toggleSelection = (type, item) => {
  const selections = {
    products: selectedProducts,
    categories: selectedCategories,
    users: selectedUsers
  };
  
  const selected = selections[type];
  const index = selected.value.findIndex(i => i.id === item.id);
  
  if (index === -1) {
    selected.value.push(item);
  } else {
    selected.value.splice(index, 1);
  }
};

// Check if item is selected
const isSelected = (type, item) => {
  const selections = {
    products: selectedProducts,
    categories: selectedCategories,
    users: selectedUsers
  };
  
  return selections[type].value.some(i => i.id === item.id);
};

// Close dropdown when clicking outside
const closeDropdowns = (event) => {
  if (!event.target.closest('.relative')) {
    activeDropdown.value = null;
  }
};

// Add click event listener
onMounted(() => {
  document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
});

const validateForm = () => {
  errors.code = '';
  errors.name = '';
  errors.discount_type = '';
  errors.discount_value = '';
  errors.start_date = '';
  errors.end_date = '';
  errors.status = '';
  errors.usage_limit = '';
  errors.min_order_value = '';
  errors.discount_type = '';

  let isValid = true;

  if (!formData.code) {
    errors.code = 'Vui lòng nhập mã giảm giá';
    isValid = false;
  }

  if (!formData.name) {
    errors.name = 'Vui lòng nhập tên mã giảm giá';
    isValid = false;
  }

  if (!formData.discount_type) {
    errors.discount_type = 'Vui lòng chọn loại giảm giá';
    isValid = false;
  } else if (!['percentage', 'fixed', 'shipping_fee'].includes(formData.discount_type)) {
    errors.discount_type = 'Loại giảm giá không hợp lệ';
    isValid = false;
  }

  if (formData.discount_value === undefined || formData.discount_value === null) {
    errors.discount_value = 'Giá trị giảm giá không được để trống';
    isValid = false;
  } else if (formData.discount_value < 0) {
    errors.discount_value = 'Giá trị giảm giá phải lớn hơn hoặc bằng 0';
    isValid = false;
  }

  if (formData.discount_type === 'percentage' && formData.discount_value > 100) {
    errors.discount_value = 'Giảm giá theo phần trăm không được vượt quá 100%';
    isValid = false;
  }

   if (formData.discount_type === 'shipping_fee') {
    if (formData.discount_value < 5000 || formData.discount_value > 30000) {
      errors.discount_value = 'Giảm phí vận chuyển phải từ 5.000đ đến 30.000đ';
      isValid = false;
    }
  }

  // Validate start_date
  if (!formData.start_date) {
    errors.start_date = 'Vui lòng chọn ngày bắt đầu';
    isValid = false;
  } else {
    const startDate = new Date(formData.start_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (startDate < today) {
      errors.start_date = 'Ngày bắt đầu phải từ ngày hôm nay trở đi';
      isValid = false;
    }
  }

  // Validate end_date
  if (!formData.end_date) {
    errors.end_date = 'Vui lòng chọn ngày kết thúc';
    isValid = false;
  } else if (formData.start_date) {
    const startDate = new Date(formData.start_date);
    const endDate = new Date(formData.end_date);
    
    if (endDate <= startDate) {
      errors.end_date = 'Ngày kết thúc phải sau ngày bắt đầu';
      isValid = false;
    }
  }

  // Validate status
  if (!formData.status) {
    errors.status = 'Vui lòng chọn trạng thái';
    isValid = false;
  }

  // Validate usage_limit
  if (formData.usage_limit !== null && formData.usage_limit < 1) {
    errors.usage_limit = 'Giới hạn sử dụng phải lớn hơn 0';
    isValid = false;
  }

  // Validate min_order_value
  if (formData.min_order_value !== null && formData.min_order_value < 0) {
    errors.min_order_value = 'Giá trị đơn hàng tối thiểu phải lớn hơn hoặc bằng 0';
    isValid = false;
  }

  return isValid;
};

const generateCode = () => {
  const prefix = 'COUPON';
  const randomString = Math.random().toString(36).substring(2, 8).toUpperCase();
  formData.code = `${prefix}_${randomString}`;
};

const createCoupon = async () => {
  if (!validateForm()) return;

  try {
    loading.value = true;
    const data = await secureFetch(`${apiBase}/discounts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    }, ['admin']);
    if (data && data.id) {
      showSuccessNotification('Tạo mã giảm giá thành công!');
      // Gán sản phẩm, danh mục, user nếu có
      if (selectedProducts.value.length > 0) {
        await secureFetch(`${apiBase}/discounts/${data.id}/products`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_ids: selectedProducts.value.map(p => p.id) })
        }, ['admin']);
      }
      if (selectedCategories.value.length > 0) {
        await secureFetch(`${apiBase}/discounts/${data.id}/categories`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ category_ids: selectedCategories.value.map(c => c.id) })
        }, ['admin']);
      }
      if (selectedUsers.value.length > 0) {
        await secureFetch(`${apiBase}/discounts/${data.id}/users`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_ids: selectedUsers.value.map(u => u.id) })
        }, ['admin']);
      }
      setTimeout(() => {
        router.push('/admin/coupons/list-coupon');
      }, 1200);
    } else {
      showErrorNotification(data && data.message ? data.message : 'Có lỗi xảy ra khi tạo mã giảm giá');
    }
  } catch (error) {
    showErrorNotification('Lỗi tạo coupon: ' + error);
  } finally {
    loading.value = false;
  }
};

const fetchProducts = async () => {
  try {
    const data = await secureFetch(`${apiBase}/products?per_page=1000`, {}, ['admin']);
    if (data.data && Array.isArray(data.data.data)) {
      products.value = data.data.data;
    } else if (Array.isArray(data.data)) {
      products.value = data.data;
    } else if (Array.isArray(data)) {
      products.value = data;
    } else {
      products.value = [];
    }
  } catch (e) {
    products.value = [];
  }
}

const fetchCategories = async () => {
  try {
    const data = await secureFetch(`${apiBase}/categories`, {}, ['admin']);
    if (data.data && Array.isArray(data.data.data)) {
      categories.value = data.data.data;
    } else if (Array.isArray(data.data)) {
      categories.value = data.data;
    } else if (Array.isArray(data)) {
      categories.value = data;
    } else {
      categories.value = [];
    }
  } catch (e) {
    categories.value = [];
  }
}

const fetchUsers = async () => {
  try {
    const data = await secureFetch(`${apiBase}/users`, {}, ['admin']);
    if (data.data && Array.isArray(data.data.data)) {
      users.value = data.data.data;
    } else if (Array.isArray(data.data)) {
      users.value = data.data;
    } else if (Array.isArray(data)) {
      users.value = data;
    } else {
      users.value = [];
    }
  } catch (e) {
    users.value = [];
  }
}

// Fetch data on mount
onMounted(() => {
  fetchProducts();
  fetchCategories();
  fetchUsers();
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
</style>