<template>
  <main class="bg-[#F5F5FA] py-2">
    <div class="container bg-white p-4 min-h-screen shadow w-full mx-auto mt-4" v-if="seller">
      <div v-if="loading" class="text-center py-4 text-gray-500">Đang tải thông tin cửa hàng...</div>
      <div v-else>
        <!-- Header: Thông tin shop -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center text-2xl">
              <img v-if="seller.avatar"
                :src="seller.avatar.startsWith('http') ? seller.avatar : `${mediaBase}${seller.avatar}`" alt="Avatar"
                class="w-full h-full rounded-full object-cover" />
              <span v-else>📘</span>
            </div>
            <div>
              <h2 class="font-semibold text-lg">{{ seller.store_name }}</h2>
              <div class="flex items-center text-sm text-gray-500 space-x-2">
                <span class="text-yellow-500 flex items-center gap-1">
                  ★ {{ seller.rating || 'Chưa có đánh giá' }}
                </span>
                <span class="text-blue-700 flex items-center gap-1">
                  | {{ followerCount }} người theo dõi
                </span>
              </div>
            </div>
          </div>

          <div class="flex space-x-2">
            <button v-if="isLoggedIn && isNotOwner"
              class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm">
              Chat
            </button>
            <button v-if="isLoggedIn && isNotOwner"
              class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
              @click="toggleFollow" :disabled="isFollowLoading">
              <font-awesome-icon v-if="isFollowLoading" icon="spinner" spin class="text-gray-500" />
              <font-awesome-icon v-else :icon="['fas', isFollowing ? 'check' : 'user-plus']" />
              <span>
                {{ isFollowing ? 'Đã theo dõi' : 'Theo dõi' }}
              </span>
            </button>
            <button v-else-if="!isLoggedIn" class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm"
              @click="openLoginModal">
              <font-awesome-icon :icon="['fas', 'user']" />
              Đăng nhập để theo dõi
            </button>
            <div v-if="isLoggedIn && !isNotOwner" class="flex flex-wrap space-x-2">
              <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
                @click="goToDashboard">
                <font-awesome-icon :icon="['fas', 'cog']" />
                Cài đặt cửa hàng
              </button>
            </div>
          </div>
        </div>

        <!-- Menu điều hướng + Tìm kiếm -->
        <div class="mt-6 border-t pt-6 flex flex-col lg:flex-row justify-between gap-4 items-start lg:items-center">
          <nav class="flex flex-wrap gap-3 text-sm font-medium text-gray-700">
            <a v-for="tab in tabs" :key="tab" href="#" class="px-3 py-1.5 rounded-md transition"
              :class="{ 'bg-blue-100 text-blue-600': activeTab === tab, 'hover:text-blue-600 hover:bg-blue-50': activeTab !== tab }"
              @click.prevent="setActiveTab(tab)">
              {{ tab }}
            </a>
          </nav>
          <div class="w-full lg:w-1/4" v-if="activeTab === 'Cửa hàng'">
            <div class="flex border rounded overflow-hidden max-w-full">
              <input type="text" placeholder="Tìm kiếm sản phẩm tại cửa hàng"
                class="flex-1 px-3 py-2 text-sm outline-none" v-model="searchQuery" @input="filterProducts" />
              <button class="bg-gray-100 px-4 text-sm hover:bg-gray-200 transition">Tìm</button>
            </div>
          </div>
        </div>

        <!-- Nội dung chính -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
          <aside class="bg-white p-5 border-r min-h-screen col-span-1" v-if="activeTab === 'Cửa hàng'">
            <h3 class="font-semibold text-base mb-4 text-gray-800 border-b pb-2">Tất cả danh mục</h3>
            <ul class="space-y-2 text-gray-700 text-sm">
              <li v-for="category in uniqueCategories" :key="category">
                <a href="#" @click.prevent="filterByCategory(category)" class="block px-3 py-2 rounded transition" :class="[
                  selectedCategory === category
                    ? 'bg-blue-100 text-blue-600 font-semibold'
                    : 'hover:bg-blue-50 hover:text-blue-600 text-gray-700'
                ]">
                  {{ category }}
                </a>
              </li>
            </ul>
          </aside>

          <section :class="activeTab === 'Cửa hàng' ? 'col-span-1 md:col-span-4' : 'col-span-1 md:col-span-5'">
            <!-- Tab Cửa hàng -->
            <div v-if="activeTab === 'Cửa hàng'">
              <div class="bg-white p-3 shadow rounded mb-4 flex flex-wrap justify-between items-center text-sm">
                <h3 class="font-semibold mb-2 md:mb-0">Tất cả sản phẩm: {{ filteredProducts.length }}</h3>
                <div class="flex flex-wrap gap-3 font-medium text-sm">
                  <button v-for="(label, sortKey) in {
                    popular: 'Phổ biến',
                    sold: 'Bán chạy',
                    new: 'Hàng mới',
                    'price-asc': 'Giá thấp - cao',
                    'price-desc': 'Giá cao - thấp'
                  }" :key="sortKey" @click="sortProducts(sortKey)" class="px-3 py-1 rounded transition" :class="activeSort === sortKey
                    ? 'bg-blue-100 text-blue-600 font-semibold'
                    : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50'">
                    {{ label }}
                  </button>
                </div>
              </div>
              <div v-if="loading" class="text-center py-4 text-gray-500">Đang tải sản phẩm...</div>
              <div v-else-if="error" class="text-center py-4 text-red-500">Lỗi: {{ error }}</div>
              <div v-else-if="filteredProducts.length === 0" class="text-center py-8 text-gray-400 text-base">
                Không có sản phẩm nào phù hợp.
              </div>
              <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
                <ProductCard v-for="product in filteredProducts" :key="product.id" :item="product" />
              </div>
              <!-- Pagination -->
              <div class="mt-8 flex justify-center items-center gap-1 text-sm flex-wrap" v-if="pagination.last_page > 1">
                <button
                  class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
                  <i class="fas fa-chevron-left mr-1"></i>
                </button>
                <template v-for="(page, i) in visiblePages" :key="i">
                  <span v-if="page === '...'" class="px-3 py-1 text-gray-400 font-semibold select-none">...</span>
                  <button v-else class="px-3 py-1 rounded-full border transition font-semibold shadow-sm" :class="page === pagination.current_page
                    ? 'bg-[#1BA0E2] text-white border-[#1BA0E2] shadow-md sm:scale-100 md:scale-105'
                    : 'bg-white border-gray-300 hover:bg-blue-50 hover:border-blue-400 text-gray-700'"
                    @click="changePage(page)">
                    {{ page }}
                  </button>
                </template>
                <button
                  class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="pagination.current_page === pagination.last_page"
                  @click="changePage(pagination.current_page + 1)">
                  <i class="fas fa-chevron-right ml-1"></i>
                </button>
              </div>
            </div>

            <!-- Tab Giá sốc hôm nay -->
            <div v-if="activeTab === 'Giá sốc hôm nay'">
              <div class="bg-white p-3 shadow rounded mb-4">
                <h3 class="font-semibold text-base mb-4 text-gray-800">Giá sốc hôm nay</h3>
                <div v-if="loadingDeals" class="text-center py-4 text-gray-500">Đang tải ưu đãi...</div>
                <div v-else-if="errorDeals" class="text-center py-4 text-red-500">Lỗi: {{ errorDeals }}</div>
                <div v-else-if="dealProducts.length === 0" class="text-center py-8 text-gray-400 text-base">
                  Không có ưu đãi nào hôm nay.
                </div>
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
                  <ProductCard v-for="product in dealProducts" :key="product.id" :item="product" />
                </div>
              </div>
            </div>

            <!-- Tab Hồ sơ cửa hàng -->
            <div v-if="activeTab === 'Hồ sơ cửa hàng'" class="py-6">
              <div
                class="bg-white p-6 shadow-lg rounded-xl border border-gray-100 transform transition-all duration-300 hover:shadow-xl">
                <h3 class="font-bold text-xl mb-6 text-gray-900 border-b-2 border-blue-100 pb-3">Hồ sơ cửa hàng</h3>
                <div v-if="isLoggedIn && !isNotOwner" class="mb-4 bg-green-50 p-4 rounded-lg">
                  <p class="text-sm text-green-700">Quản lý cửa hàng của bạn: chỉnh sửa thông tin, xem đơn hàng, hoặc theo dõi người theo dõi.</p>
                  <div class="mt-2 flex flex-wrap space-x-2">
                    <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
                      @click="editStoreProfile">
                      <font-awesome-icon :icon="['fas', 'edit']" />
                      Chỉnh sửa hồ sơ
                    </button>
                    <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
                      @click="manageProducts">
                      <font-awesome-icon :icon="['fas', 'box']" />
                      Quản lý sản phẩm
                    </button>
                    <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
                      @click="manageOrders">
                      <font-awesome-icon :icon="['fas', 'shopping-cart']" />
                      Quản lý đơn hàng
                    </button>
                    <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
                      @click="viewFollowers">
                      <font-awesome-icon :icon="['fas', 'users']" />
                      Xem người theo dõi
                    </button>
                  </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-sm">
                  <div class="space-y-6">
                    <div
                      class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                      <div class="flex items-center gap-3">
                        <span class="text-blue-700 font-semibold">Tỉ lệ hủy</span>
                      </div>
                      <span class="text-green-600 font-bold text-xl">{{ seller.cancellation_rate || '0%' }}</span>
                    </div>
                    <div
                      class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                      <div class="flex items-center gap-3">
                        <span class="text-blue-700 font-semibold">Tỉ lệ đổi trả</span>
                      </div>
                      <span class="text-green-600 font-bold text-xl">{{ seller.return_rate || '0%' }}</span>
                    </div>
                    <div
                      class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                      <div class="flex items-center gap-3">
                        <span class="text-blue-700 font-semibold">Thành viên từ năm</span>
                      </div>
                      <span class="text-gray-900 font-medium">{{ seller.member_since || 'N/A' }}</span>
                    </div>
                    <div
                      class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                      <div class="flex items-center gap-3">
                        <span class="text-blue-700 font-semibold">Sản phẩm</span>
                      </div>
                      <span class="text-gray-900 font-medium">{{ seller.total_products || '0' }}+</span>
                    </div>
                    
                  </div>
                  <div class="space-y-6">
                    <div
                      class="p-6 bg-gradient-to-br from-blue-50 via-white to-gray-100 rounded-2xl shadow-lg border border-blue-100 hover:shadow-xl transition-all duration-300">
                      <div class="flex items-center gap-3 mb-2">
                        <font-awesome-icon icon="info-circle" class="text-blue-500 text-lg" />
                        <h4 class="text-blue-700 font-semibold text-lg">Mô tả cửa hàng</h4>
                      </div>
                      <p class="text-gray-700 leading-relaxed text-base min-h-[40px]">
                        {{ seller.description || 'Chưa có mô tả.' }}
                      </p>
                    </div>
                    <div
                      class="p-6 bg-white rounded-2xl shadow-lg border-l-8 border-yellow-300 hover:shadow-xl transition-all duration-300">
                      <div class="flex items-center gap-3 mb-2">
                        <font-awesome-icon icon="star" class="text-yellow-400 text-lg" />
                        <h4 class="text-blue-700 font-semibold text-lg">Đánh giá</h4>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Điểm đánh giá</span>
                        <span class="flex items-center gap-1 text-yellow-500 font-bold text-xl">
                          {{ seller.stars ? '★'.repeat(seller.stars) + '☆'.repeat(5 - seller.stars) : '☆☆☆☆☆' }}
                          <span class="text-gray-700 ml-2 text-base">({{ seller.rating || '0' }}/5)</span>
                        </span>
                      </div>
                    </div>
                    <div
                      class="p-6 bg-gradient-to-br from-blue-100 via-white to-blue-50 rounded-2xl shadow-lg border-l-8 border-blue-400 hover:shadow-xl transition-all duration-300">
                      <div class="flex items-center gap-3 mb-2">
                        <font-awesome-icon icon="users" class="text-blue-500 text-lg" />
                        <h4 class="text-blue-700 font-semibold text-lg">Người theo dõi</h4>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Tổng số</span>
                        <span class="text-blue-700 font-bold text-2xl">{{ followerCount || '0' }}+</span>
                      </div>
                    </div>
                    <div
                      class="p-6 bg-gradient-to-br from-gray-100 via-white to-gray-50 rounded-2xl shadow-lg border-l-8 border-gray-300 hover:shadow-xl transition-all duration-300">
                      <div class="flex items-center gap-3 mb-2">
                        <font-awesome-icon icon="comments" class="text-gray-500 text-lg" />
                        <h4 class="text-blue-700 font-semibold text-lg">Phản hồi Chat</h4>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Trạng thái</span>
                        <span class="text-gray-700 font-medium">Chưa có</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab Voucher của shop -->
            <div v-if="activeTab === 'Voucher của shop'" class="py-6">
              <div class="bg-white p-6 shadow-lg rounded-xl border border-gray-100">
                <h3 class="font-bold text-xl mb-6 text-gray-900 border-b-2 border-blue-100 pb-3">Voucher của shop</h3>
                <div v-if="loadingVouchers" class="text-center py-4 text-gray-500">Đang tải voucher...</div>
                <div v-else-if="errorVouchers" class="text-center py-4 text-red-500">Lỗi: {{ errorVouchers }}</div>
                <div v-else-if="vouchers.length === 0" class="text-center py-8 text-gray-400 text-base">
                  Không có voucher nào hiện tại.
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div v-for="voucher in vouchers" :key="voucher.id"
                    class="relative bg-white border border-blue-200 shadow-sm rounded-xl flex items-center justify-between w-full max-w-md mb-4 overflow-hidden hover:shadow-md transition">
                    <!-- Left Content -->
                    <div class="p-4 flex-1">
                      <div class="text-blue-700 font-bold text-base mb-1">
                        Giảm
                        <span v-if="voucher.discount_type === 'percentage'">
                          {{ formatDiscountValue(voucher.discount_value) }}%
                          <span v-if="voucher.max_discount">tối đa {{ formatCurrency(voucher.max_discount) }}</span>
                        </span>
                        <span v-else>
                          {{ formatCurrency(voucher.discount_value) }}
                        </span>
                      </div>
                      <div class="text-sm text-gray-600 mb-1">
                        Đơn tối thiểu {{ formatCurrency(voucher.min_order_value) }}
                      </div>
                      <div class="mt-2 text-xs text-gray-700 space-y-1">
                        <div v-if="voucher.products && voucher.products.length">
                          Áp dụng cho sản phẩm:
                          <span v-for="(product, idx) in voucher.products" :key="idx"
                            class="inline-block bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs mr-1">
                            {{ product.name }}
                          </span>
                        </div>
                        <div v-if="voucher.categories && voucher.categories.length">
                          Áp dụng cho danh mục:
                          <span v-for="(category, idx) in voucher.categories" :key="idx"
                            class="inline-block bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs mr-1">
                            {{ category.name }}
                          </span>
                        </div>
                        <div v-if="voucher.users && voucher.users.length">
                          Chỉ dành cho người dùng:
                          <span v-for="(user, idx) in voucher.users" :key="idx"
                            class="inline-block bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs mr-1">
                            {{ user.name }}
                          </span>
                        </div>
                      </div>
                      <div class="text-xs mt-2" :class="isVoucherExpired(voucher) ? 'text-red-500' : 'text-gray-500'">
                        {{ isVoucherExpired(voucher) ? 'Đã hết hạn' : 'HSD: ' + formatDate(voucher.end_date) }}
                      </div>
                      <div v-if="isVoucherUsedUp(voucher)" class="text-xs text-red-500 mt-1">
                        Hết lượt sử dụng
                      </div>
                    </div>
                    <div class="border-l border-blue-100 h-full flex items-center justify-center px-4 bg-white">
                      <button
                        @click="!isVoucherUnavailable(voucher) && (voucher.is_saved ? goToUseVoucher(voucher) : saveVoucher(voucher.code))"
                        :disabled="isVoucherUnavailable(voucher)"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed">
                        {{ isVoucherUnavailable(voucher) ? 'Không khả dụng' : (voucher.is_saved ? 'Dùng sau' : 'Lưu') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
    <div v-else-if="error" class="text-center py-4 text-red-500">Lỗi: {{ error }}</div>
    <div v-else class="text-center py-4 text-gray-500">Đang tải...</div>
  </main>
  <AuthModal :show="showModal" :initial-mode="modalMode" @close="showModal = false"
    @login-success="handleLoginSuccess" />
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import ProductCard from '~/components/shared/products/ProductCard.vue';
import { useToast } from '~/composables/useToast';
import { debounce } from 'lodash';
import { useRuntimeConfig } from '#imports';
import { useDiscount } from '~/composables/useDiscount';
import AuthModal from "~/components/shared/AuthModal.vue";


const { toast } = useToast();
const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const config = useRuntimeConfig();
const { saveVoucherByCode } = useDiscount();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;
const modalMode = ref("login");
const showModal = ref(false);

const seller = ref(null);
const products = ref([]);
const filteredProducts = ref([]);
const dealProducts = ref([]);
const vouchers = ref([]);
const searchQuery = ref('');
const selectedCategory = ref('');
const isFollowing = ref(false);
const followerCount = ref(0);
const pendingOrders = ref(0);
const isFollowLoading = ref(false);
const loading = ref(false);
const loadingDeals = ref(false);
const loadingVouchers = ref(false);
const error = ref(null);
const errorDeals = ref(null);
const errorVouchers = ref(null);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});
const activeTab = ref('Cửa hàng');
const activeSort = ref('popular');
const tabs = ['Cửa hàng', 'Giá sốc hôm nay', 'Hồ sơ cửa hàng', 'Voucher của shop'];

const isLoggedIn = computed(() => auth.isLoggedIn);
const currentUser = computed(() => auth.currentUser);
const isNotOwner = computed(() => {
  if (!seller.value || !currentUser.value) return true;
  return !seller.value.is_owner && currentUser.value.id !== seller.value.user_id;
});

const uniqueCategories = computed(() => {
  const categories = new Set();
  products.value.forEach(product => {
    if (product.categories && product.categories.length > 0) {
      product.categories.forEach(category => categories.add(category));
    } else {
      categories.add('Tất cả');
    }
  });
  return Array.from(categories);
});

const visiblePages = computed(() => {
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;
  const range = [];

  if (total <= 7) {
    for (let i = 1; i <= total; i++) range.push(i);
  } else {
    if (current <= 4) {
      range.push(1, 2, 3, 4, 5, '...', total);
    } else if (current >= total - 3) {
      range.push(1, '...', total - 4, total - 3, total - 2, total - 1, total);
    } else {
      range.push(1, '...', current - 1, current, current + 1, '...', total);
    }
  }
  return range;
});

const handleApiError = async (err, callback) => {
  if (err.response?.status === 401) {
    try {
      await auth.refreshToken();
      return await callback();
    } catch (refreshErr) {
      toast('error', 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.');
      openLoginModal();
      return false;
    }
  }
  toast('error', err.response?.data?.message || 'Có lỗi xảy ra.');
  return false;
};

function openLoginModal() {
  console.log("openLoginModal called");
  modalMode.value = "login";
  showModal.value = true;
}


const goToDashboard = () => { router.push('/seller/dashboard'); };
const editStoreProfile = () => { router.push('/seller/profile/edit'); };
const manageProducts = () => { router.push('/seller/products'); };
const manageOrders = () => { router.push('/seller/orders'); };
const viewFollowers = () => { router.push(`/sellers/${seller.value.id}/followers`); };

const fetchSeller = async (page = 1) => {
  try {
    loading.value = true;
    error.value = null;
    if (!currentUser.value && isLoggedIn.value) {
      await auth.fetchUser();
    }
    const res = await axios.get(`${apiBase}/sellers/store/${route.params.slug}`, {
      params: { page, per_page: 24, search: searchQuery.value || undefined, category: selectedCategory.value || undefined },
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` },
    });
    const { seller: sellerData, products: productData = [], pagination: paginationData = {} } = res.data.data;
    seller.value = {
      ...sellerData,
      is_owner: sellerData.is_owner ?? (currentUser.value?.id === sellerData.user_id),
    };
    followerCount.value = sellerData.followers_count || 0;
    isFollowing.value = sellerData.is_following || false;
    pendingOrders.value = sellerData.pending_orders || 0;
    products.value = productData.map(product => ({
      ...product,
      image: product.image ? `${mediaBase}${product.image}` : 'https://via.placeholder.com/150',
      categories: Array.isArray(product.categories) ? product.categories : [],
      tags: Array.isArray(product.tags) ? product.tags : [],
      price: parseFloat(product.price) || 0,
      discount: product.discount ? parseFloat(product.discount) : null,
      percent: parseInt(product.percent) || 0,
      rating: product.rating || '☆☆☆☆☆',
      sold: parseInt(product.sold) || 0,
    }));
    filteredProducts.value = products.value;
    pagination.value = {
      current_page: parseInt(paginationData.current_page) || 1,
      last_page: parseInt(paginationData.last_page) || 1,
      total: parseInt(paginationData.total) || 0,
    };
  } catch (err) {
    console.error('Error fetching seller:', err);
    const success = await handleApiError(err, () => fetchSeller(page));
    if (!success) error.value = err.response?.data?.message || 'Không thể tải dữ liệu cửa hàng.';
  } finally {
    loading.value = false;
  }
};

const fetchDeals = async () => {
  try {
    loadingDeals.value = true;
    errorDeals.value = null;
    const res = await axios.get(`${apiBase}/sellers/store/${route.params.slug}/deals`, {
      params: { per_page: 24 },
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` },
    });
    dealProducts.value = res.data.data.deals.map(product => ({
      ...product,
      image: product.image ? `${mediaBase}${product.image}` : 'https://via.placeholder.com/150',
      categories: Array.isArray(product.categories) ? product.categories : [],
      tags: Array.isArray(product.tags) ? product.tags : [],
      price: parseFloat(product.price) || 0,
      discount: product.discount ? parseFloat(product.discount) : null,
      percent: parseInt(product.percent) || 0,
      rating: product.rating || '☆☆☆☆☆',
      sold: parseInt(product.sold) || 0,
    }));
  } catch (err) {
    console.error('Error fetching deals:', err);
    const success = await handleApiError(err, () => fetchDeals());
    if (!success) errorDeals.value = err.response?.data?.message || 'Không thể tải ưu đãi hôm nay.';
  } finally {
    loadingDeals.value = false;
  }
};

const fetchVouchers = async () => {
  if (!seller.value) return;
  try {
    loadingVouchers.value = true;
    errorVouchers.value = null;
    const res = await axios.get(`${apiBase}/sellers/store/${route.params.slug}/discounts`, {
      headers: isLoggedIn.value ? { Authorization: `Bearer ${localStorage.getItem('access_token')}` } : {},
    });
    vouchers.value = res.data.data.map(voucher => ({
      ...voucher,
      discount_type: voucher.discount_type || 'fixed',
      discount_value: parseFloat(voucher.discount_value) || 0,
      max_discount: parseFloat(voucher.max_discount) || 0,
      min_order_value: parseFloat(voucher.min_order_value) || 0,
      end_date: voucher.end_date || 'N/A',
      is_saved: voucher.is_saved || false,
      products: Array.isArray(voucher.products) ? voucher.products : [],
      categories: Array.isArray(voucher.categories) ? voucher.categories : [],
      users: Array.isArray(voucher.users) ? voucher.users : [],
      usage_limit: voucher.usage_limit !== null ? parseInt(voucher.usage_limit) : null,
      used_count: parseInt(voucher.used_count) || 0,
    }));
  } catch (err) {
    console.error('Error fetching vouchers:', err);
    const success = await handleApiError(err, () => fetchVouchers());
    if (!success) errorVouchers.value = err.response?.data?.message || 'Không thể tải danh sách voucher.';
  } finally {
    loadingVouchers.value = false;
  }
};

const saveVoucher = async (code) => {
  if (!isLoggedIn.value) {
    openLoginModal();
    return;
  }
  try {
    const res = await saveVoucherByCode(code);
    if (res.success) {
      vouchers.value = vouchers.value.map(voucher =>
        voucher.code === code ? { ...voucher, is_saved: true } : voucher
      );
      toast('success', res.message || 'Đã lưu voucher thành công!');
      await fetchVouchers();
    } else {
      toast('error', res.message || 'Không thể lưu voucher.');
    }
  } catch (err) {
    console.error('Error saving voucher:', err);
    toast('error', err.response?.data?.message || 'Không thể lưu voucher.');
  }
};

const goToUseVoucher = (voucher) => {
  if (voucher.products && voucher.products.length > 0) {
    router.push(`/products/${voucher.products[0].id}?voucher=${voucher.code}`);
  } else {
    router.push(`/checkout?voucher=${voucher.code}`);
  }
};

const isVoucherExpired = (voucher) => {
  return voucher.end_date && new Date(voucher.end_date) < new Date();
};

const isVoucherUsedUp = (voucher) => {
  return voucher.usage_limit !== null && voucher.used_count >= voucher.usage_limit;
};

const isVoucherUnavailable = (voucher) => {
  return isVoucherExpired(voucher) || isVoucherUsedUp(voucher);
};

const toggleFollow = async () => {
  if (!isLoggedIn.value) {
    openLoginModal();
    return;
  }
  if (!seller.value) {
    toast('error', 'Không thể tải thông tin cửa hàng.');
    return;
  }
  if (!isNotOwner.value) {
    toast('error', 'Bạn không thể theo dõi cửa hàng của chính mình.');
    return;
  }
  isFollowLoading.value = true;
  try {
    const url = `${apiBase}/sellers/${seller.value.id}/${isFollowing.value ? 'unfollow' : 'follow'}`;
    await axios.post(url, {}, { headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` } });
    isFollowing.value = !isFollowing.value;
    followerCount.value += isFollowing.value ? 1 : -1;
    toast('success', isFollowing.value ? 'Đã theo dõi cửa hàng!' : 'Đã bỏ theo dõi cửa hàng.');
  } catch (err) {
    console.error('Error toggling follow:', err);
    const success = await handleApiError(err, () => toggleFollow());
    if (!success) toast('error', err.response?.data?.message || 'Lỗi khi thao tác theo dõi.');
  } finally {
    isFollowLoading.value = false;
  }
};

const formatPrice = (price) => price !== null && price !== undefined
  ? new Intl.NumberFormat('vi-VN').format(price) + ' ₫'
  : 'Liên hệ';

const formatCurrency = (value) => formatPrice(value);

const formatDiscountValue = (value) => Math.round(value);

const formatDate = (dateString) => {
  if (!dateString || dateString === 'N/A') return 'Chưa xác định';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const filterProducts = debounce(() => {
  fetchSeller(1);
  updateQueryParams();
}, 500);

const filterByCategory = (category) => {
  selectedCategory.value = category;
  if (category === 'Tất cả') filteredProducts.value = products.value;
  else filteredProducts.value = products.value.filter(product => product.categories.includes(category));
  updateQueryParams();
};

const sortProducts = (sortType) => {
  activeSort.value = sortType;
  let sorted = [...filteredProducts.value];
  const originalOrder = filteredProducts.value.map(p => p.id);
  const getRatingValue = (rating) => !rating ? 0 : (rating.match(/★/g) || []).length;
  switch (sortType) {
    case 'popular': sorted.sort((a, b) => getRatingValue(b.rating) - getRatingValue(a.rating)); break;
    case 'sold': sorted.sort((a, b) => (b.sold || 0) - (a.sold || 0)); break;
    case 'new': sorted.sort((a, b) => (b.id || 0) - (a.id || 0)); break;
    case 'price-asc': sorted.sort((a, b) => (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0)); break;
    case 'price-desc': sorted.sort((a, b) => (parseFloat(b.price) || 0) - (parseFloat(a.price) || 0)); break;
  }
  filteredProducts.value = sorted;
  if (originalOrder.join() === sorted.map(p => p.id).join()) toast('info', 'Không có sự thay đổi trong sắp xếp.');
};

const changePage = (page) => {
  if (page !== pagination.value.current_page && page >= 1 && page <= pagination.value.last_page) {
    fetchSeller(page);
    updateQueryParams();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const setActiveTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'Giá sốc hôm nay') fetchDeals();
  else if (tab === 'Voucher của shop') fetchVouchers();
  if (tab !== 'Cửa hàng') {
    searchQuery.value = '';
    selectedCategory.value = '';
    updateQueryParams();
  }
};

const updateQueryParams = () => {
  const query = {};
  if (activeTab.value === 'Cửa hàng') {
    if (searchQuery.value) query.search = searchQuery.value;
    if (selectedCategory.value) query.category = selectedCategory.value;
    if (pagination.value.current_page > 1) query.page = pagination.value.current_page.toString();
  }
  if (activeTab.value !== 'Cửa hàng') query.tab = activeTab.value;
  router.push({ path: route.path, query });
};

onMounted(async () => {
  try {
    await auth.fetchUser();
    if (!currentUser.value && isLoggedIn.value) {
      console.warn('No user data after fetchUser');
      toast('warning', 'Không thể lấy thông tin người dùng. Vui lòng đăng nhập lại.');
      openLoginModal();
      return;
    }
    const page = parseInt(route.query.page) || 1;
    await fetchSeller(page);
    if (route.query.tab) {
      activeTab.value = tabs.includes(route.query.tab) ? route.query.tab : 'Cửa hàng';
      if (activeTab.value === 'Giá sốc hôm nay') await fetchDeals();
      else if (activeTab.value === 'Voucher của shop') await fetchVouchers();
    }
  } catch (err) {
    console.error('Error in onMounted:', err);
    toast('error', 'Có lỗi xảy ra khi tải trang.');
  }
});

onBeforeUnmount(() => {
  filterProducts.cancel();
});
</script>