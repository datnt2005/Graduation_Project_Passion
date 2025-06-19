<template>
  <div class="container bg-white p-4 shadow w-full mx-auto mt-4" v-if="seller">
    <div class="mb-4">
      <h1 class="text-xl font-semibold text-gray-800">Chào mừng đến gian hàng</h1>
    </div>

    <!-- Header: Thông tin shop -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center text-2xl">📘</div>
        <div>
          <h2 class="font-semibold text-lg">{{ seller.store_name }}</h2>
          <div class="flex items-center text-sm text-gray-500 space-x-2">
            <span class="text-yellow-500">★ 4.8</span>
            <span class="text-blue-700 flex items-center gap-1">
|               {{ followerCount }} người theo dõi
            </span>
          </div>
        </div>
      </div>

      <div class="flex space-x-2">
        <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm">Chat</button>

        <!-- Nút Theo dõi / Hủy -->
        <button
          v-if="isLoggedIn && currentUser?.id !== seller.user_id"
          class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm flex items-center gap-2"
          @click="toggleFollow"
          :disabled="isFollowLoading"
        >
          <font-awesome-icon
            v-if="isFollowLoading"
            icon="spinner"
            spin
            class="text-gray-500"
          />
          <font-awesome-icon
            v-else
            :icon="['fas', isFollowing ? 'check' : 'user-plus']"
          />
          {{ isFollowing ? 'Đã theo dõi' : 'Theo dõi' }}
        </button>

        <!-- Nút yêu cầu đăng nhập -->
        <button
          v-else-if="!isLoggedIn"
          class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm"
          @click="router.push('/login')"
        >
          <font-awesome-icon :icon="['fas', 'user']" />
          Đăng nhập để theo dõi
        </button>
      </div>
    </div>

    <!-- Menu điều hướng + Tìm kiếm -->
    <div class="mt-6 border-t pt-4 flex flex-col lg:flex-row justify-between gap-4">
      <nav class="flex flex-wrap gap-4 text-sm font-medium text-gray-700">
        <a href="#" class="hover:text-blue-600">Cửa hàng</a>
        <a href="#" class="hover:text-blue-600">Tất cả sản phẩm</a>
        <a href="#" class="hover:text-blue-600">Bộ sưu tập</a>
        <a href="#" class="hover:text-blue-600">Giá sốc hôm nay</a>
        <a href="#" class="hover:text-blue-600">Hồ sơ cửa hàng</a>
      </nav>
      <div class="w-full lg:w-auto">
        <div class="flex border rounded overflow-hidden max-w-full">
          <input type="text" placeholder="Tìm kiếm sản phẩm tại cửa hàng"
            class="flex-1 px-3 py-2 text-sm outline-none" />
          <button class="bg-gray-100 px-4 text-sm hover:bg-gray-200 transition">Tìm</button>
        </div>
      </div>
    </div>

    <!-- Nội dung chính -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
      <!-- Sidebar danh mục -->
      <aside class="bg-white p-5 shadow-md rounded-lg col-span-1">
        <h3 class="font-semibold text-base mb-4 text-gray-800 border-b pb-2">📂 Tất cả danh mục</h3>
        <ul class="space-y-2 text-gray-700 text-sm">
          <li><a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">📚 Sách chuyện</a></li>
          <li><a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">🔥 Sách passion</a></li>
          <li><a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">🎭 Giải trí</a></li>
          <li><a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">👗 Thời trang</a></li>
          <li><a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">🧒 Trẻ em</a></li>
          <li><a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">👩‍👧 Mẹ & Bé</a></li>
        </ul>
      </aside>

      <!-- Danh sách sản phẩm -->
      <section class="col-span-1 md:col-span-4">
        <div class="bg-white p-3 shadow rounded mb-4 flex flex-wrap justify-between items-center text-sm">
          <h3 class="font-semibold mb-2 md:mb-0">Tất cả sản phẩm:</h3>
          <div class="flex flex-wrap gap-3 font-medium text-gray-600">
            <button class="hover:text-blue-600">Phổ biến</button>
            <button class="hover:text-blue-600">Bán chạy</button>
            <button class="hover:text-blue-600">Hàng mới</button>
            <button class="hover:text-blue-600">Giá thấp - cao</button>
            <button class="hover:text-blue-600">Giá cao - thấp</button>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
          <!-- Demo sản phẩm -->
          <div class="bg-white rounded shadow p-3 text-left">
            <img
              src="https://salt.tikicdn.com/cache/750x750/ts/product/a9/83/7c/6664136e604227071213793e7a2091d9.jpg.webp"
              alt="book" class="w-full h-35 object-cover mb-2 rounded-md">
            <p class="text-sm leading-snug font-medium mb-1">PHẠM XUÂN ẨN - Tên Người Như Cuộc Đời</p>
            <p class="text-gray-500 text-xs mb-1">Tác giả</p>
            <div class="flex items-center text-xs text-gray-600 mb-1">
              <span class="text-yellow-500 mr-1">★</span>
              <span class="mr-2">4.8</span>
              <span class="text-gray-400">| Đã bán 1.2k</span>
            </div>
            <p class="font-bold text-red-500 text-sm">120.000₫</p>
            <p class="text-xs text-gray-400 mt-1">Giao từ 3 - 5 ngày</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const seller = ref(null)
const isFollowing = ref(false)
const followerCount = ref(0)
const isFollowLoading = ref(false)

const isLoggedIn = computed(() => auth.isLoggedIn)
const currentUser = computed(() => auth.currentUser)

const fetchSeller = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/sellers/store/${route.params.slug}`)
    seller.value = res.data.seller
    followerCount.value = res.data.followers_count || 0
    isFollowing.value = res.data.is_following || false
  } catch (err) {
    console.error('Lỗi khi tải seller:', err)
    alert('Không thể tải dữ liệu cửa hàng.')
  }
}

const toggleFollow = async () => {
  if (!isLoggedIn.value) return router.push('/login')
  if (!seller.value || isFollowLoading.value) return

  isFollowLoading.value = true

  try {
    const url = `http://localhost:8000/api/sellers/${seller.value.id}/${isFollowing.value ? 'unfollow' : 'follow'}`
    await axios.post(url)
    isFollowing.value = !isFollowing.value
    followerCount.value += isFollowing.value ? 1 : -1
  } catch (err) {
    console.error('Lỗi khi toggle follow:', err)
    alert(err.response?.data?.message || 'Lỗi khi thao tác theo dõi.')
  } finally {
    isFollowLoading.value = false
  }
}

onMounted(async () => {
  await auth.fetchUser()
  await fetchSeller()
})
</script>
