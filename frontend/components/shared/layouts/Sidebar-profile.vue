<template>
  <aside
    aria-label="Sidebar"
    class="w-full md:w-[260px] rounded-xl bg-white p-4 md:p-6 shadow-sm border border-[#e0e6ed]"
  >
    <!-- Mobile toggle -->
    <button
      class="md:hidden flex items-center justify-between w-full p-2 bg-[#e2e7f7] rounded-md mb-4"
      @click="toggleSidebar"
      aria-label="Toggle sidebar menu"
    >
      <span class="text-[#1a1a1a] font-semibold">Menu</span>
      <font-awesome-icon :icon="['fas', sidebarOpen ? 'times' : 'bars']" class="w-5 h-5" />
    </button>

    <!-- Menu -->
    <nav :class="{ hidden: !sidebarOpen }" class="md:block">
      <!-- Breadcrumb -->
      <nav aria-label="Breadcrumb" class="text-sm text-gray-500 mb-4">
        <ol class="flex items-center space-x-1">
          <li><router-link to="/" class="hover:underline">Trang chủ</router-link></li>
          <li><span>›</span></li>
          <li class="font-medium text-[#212b36]">Thông tin tài khoản</li>
        </ol>
      </nav>

      <!-- Profile -->
      <div class="flex items-center gap-3 mb-6">
        <img
          :src="avatarUrl"
          alt="Avatar"
          class="w-10 h-10 rounded-full object-cover border border-gray-200"
        />
        <div>
          <p class="text-sm text-gray-500">Tài khoản của</p>
          <p class="text-base font-semibold text-[#212b36] truncate">
            {{ displayName }}
          </p>
        </div>
      </div>

      <!-- Menu list -->
      <ul class="space-y-2 text-sm">
        <li v-for="item in sidebarItems" :key="item.to">
          <router-link
            :to="item.to"
            class="flex items-center gap-2 px-3 py-2 rounded transition-all"
            :class="[ 
              isActive(item.to)
                ? 'bg-[#e2e7f7] text-[#1a1a1a] font-semibold'
                : 'text-gray-500 hover:bg-[#f2f6fc] hover:text-[#212b36]'
            ]"
          >
            <template v-if="item.icon">
              <font-awesome-icon
                :icon="['fas', item.icon]"
                class="w-5 h-5 min-w-[20px] min-h-[20px]"
              />
            </template>
            <template v-else-if="item.img">
              <img :src="item.img" class="w-5 h-5 object-contain" />
            </template>
            <span>{{ item.label }}</span>
          </router-link>
        </li>
      </ul>
    </nav>
  </aside>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const defaultAvatar = config.public.mediaBaseUrl + 'avatars/default.jpg'

// State
const user = ref({})
const loading = ref(true)

// Fetch user
const fetchUser = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) return
    const res = await fetch(`${apiBase}/me`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    const data = await res.json()
    if (data && data.data) {
      user.value = {
        name: data.data.name || '',
        avatar_url: data.data.avatar
          ? config.public.mediaBaseUrl + data.data.avatar
          : defaultAvatar
      }
    }
  } catch (e) {
    user.value = {}
  } finally {
    loading.value = false
  }
}

onMounted(fetchUser)

const avatarUrl = computed(() =>
  user.value?.avatar_url || defaultAvatar
)

const displayName = computed(() =>
  user.value?.name || 'Chưa cập nhật'
)

// UI logic
const sidebarOpen = ref(true)
const toggleSidebar = () => (sidebarOpen.value = !sidebarOpen.value)

const route = useRoute()
const isActive = (to) => route.path.startsWith(to)

const sidebarItems = [
  { to: '/users/profile', icon: 'user-circle', label: 'Thông tin tài khoản' },
  { to: '/account/notifications', icon: 'bell', label: 'Thông báo của tôi' },
  { to: '/users/orders', icon: 'file-alt', label: 'Quản lý đơn hàng' },
  { to: '/account/address', icon: 'map-marker-alt', label: 'Sổ địa chỉ' },
  { to: '/account/payment', icon: 'credit-card', label: 'Thông tin thanh toán' },
  { to: '/account/seen', icon: 'eye', label: 'Sản phẩm bạn đã xem' },
  { to: '/account/favorite', icon: 'heart', label: 'Sản phẩm yêu thích' },
  { to: '/account/comments', icon: 'star', label: 'Nhận xét của tôi' },
  {
    to: '/account/vouchers',
    img: 'https://storage.googleapis.com/a1aa/image/e0af1418-a92a-43bf-ade5-4d49087465f2.jpg',
    label: 'Mã giảm giá'
  }
]
</script>

<style scoped>
svg.fa-icon {
  width: 1.25rem;
  height: 1.25rem;
  min-width: 1.25rem;
  min-height: 1.25rem;
}
</style>