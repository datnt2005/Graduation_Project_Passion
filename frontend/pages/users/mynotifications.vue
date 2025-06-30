<template>
    <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
            <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

            <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
                <div class="min-h-full">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4">Thông báo của tôi</h2>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="flex border-b border-gray-200 relative">
                            <button
                                class="flex-1 py-3 px-4 text-center text-sm font-medium text-blue-600 border-b-2 border-blue-600">Thông
                                báo của tôi</button>
                            <button
                                class="flex-1 py-3 px-4 text-center text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300">Khuyến
                                mãi</button>
                            <button
                                class="flex-1 py-3 px-4 text-center text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300">Cập
                                nhật đơn hàng</button>
                            <button
                                class="flex-1 py-3 px-4 text-center text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300">Lịch
                                sử</button>

                            <!-- Menu dropdown -->
                            <div class="relative inline-block text-left py-3 px-4 z-10" @click.stop="toggleDropdown">
                                <button type="button"
                                    class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>
                                <div v-if="showDropdown"
                                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            @click.prevent="markAllAsRead">Đánh dấu đọc tất cả</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            @click.prevent="deleteAllNotifications">Xóa tất cả thông báo</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox chọn tất cả -->
                        <div v-if="notifications.length > 0"
                            class="flex items-center gap-2 px-6 py-3 bg-gray-50 border-b border-gray-200">
                            <input type="checkbox"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                v-model="selectAll" @change="toggleSelectAll" />
                            <label class="text-sm text-gray-700">Chọn tất cả</label>
                        </div>


                        <!-- Khi có thông báo đã chọn -->
                        <div v-if="selectedNotificationIds.length > 0"
                            class="flex justify-between items-center px-6 py-3 bg-gray-50 border-b">
                            <p class="text-sm text-gray-700">Đã chọn {{ selectedNotificationIds.length }} thông báo</p>
                            <div class="flex gap-2">
                                <button @click="markSelectedAsRead"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 text-sm rounded">Đánh dấu
                                    đã đọc</button>
                                <button @click="deleteSelectedNotifications"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 text-sm rounded">Xóa</button>
                            </div>
                        </div>

                        <!-- Danh sách thông báo -->
                        <div class="p-6">
                            <div v-if="notifications.length === 0"
                                class="flex flex-col items-center justify-center py-10">
                                <img src="https://salt.tikicdn.com/ts/upload/e9/89/3b/b7ad854f386927361a49852f1e2f75a6.png"
                                    alt="No Notifications" class="w-40 h-40 mb-4" />
                                <p class="text-gray-600 text-base mb-6">Bạn chưa có thông báo nào</p>
                                <NuxtLink to="/"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                                    Tiếp tục mua sắm</NuxtLink>
                            </div>

                            <ul v-else class="space-y-4">
                                <li v-for="item in notifications" :key="item.id"
                                    class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 shadow-sm transition cursor-pointer"
                                    :class="item.is_read === 1 ? 'bg-gray-50 text-gray-500' : 'bg-white text-gray-900 hover:bg-gray-50'">
                                    <input type="checkbox" v-model="selectedNotificationIds" :value="item.id"
                                        class="mt-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />

                                    <img v-if="item.image_url" :src="item.image_url" alt="Ảnh thông báo"
                                        class="w-16 h-16 rounded-lg object-cover border" />

                                    <div class="flex-1 min-w-0" @click="handleNotificationClick(item)">
                                        <div class="flex justify-between items-start">
                                            <h3 class="text-base font-semibold text-gray-900 mb-1"
                                                :class="{ 'font-bold': item.is_read === 0 }">{{ item.title }}</h3>
                                            <span v-if="item.is_read === 0"
                                                class="mt-1 w-2 h-2 rounded-full bg-blue-500 shrink-0"
                                                title="Chưa đọc"></span>
                                        </div>

                                        <p class="text-gray-600 text-sm mb-1 line-clamp-2">{{ stripHTML(item.content) ||
                                            'Không có nội dung' }}</p>
                                        <div class="text-xs text-gray-400 mt-1">
                                            <span>{{ item.time_ago }}</span>
                                            <span v-if="item.link" class="ml-2 text-blue-500 hover:underline">Xem chi
                                                tiết</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'

const api = useRuntimeConfig().public.apiBaseUrl
const notifications = ref([])
const unreadCount = ref(0)
const showDropdown = ref(false)
const selectedNotificationIds = ref([])

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value
}

const handleClickOutside = (e) => {
    if (showDropdown.value && !e.target.closest('.relative.inline-block')) {
        showDropdown.value = false
    }
}

const selectAll = ref(false)

// Khi checkbox "Chọn tất cả" thay đổi
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotificationIds.value = notifications.value.map(item => item.id)
  } else {
    selectedNotificationIds.value = []
  }
}

// Đồng bộ lại checkbox chọn tất cả nếu user chọn thủ công từng item
watch(selectedNotificationIds, (newVal) => {
  if (newVal.length === notifications.value.length) {
    selectAll.value = true
  } else {
    selectAll.value = false
  }
})


onMounted(() => {
    fetchNotifications()
    document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

const fetchNotifications = async () => {
    try {
        const token = localStorage.getItem('access_token')
        if (!token) return

        const res = await fetch(`${api}/my-notifications`, {
            headers: { Authorization: `Bearer ${token}` }
        })

        const data = await res.json()
        if (data?.data) {
            notifications.value = data.data
            unreadCount.value = data.data.filter(n => !n.is_read).length
        }
    } catch (e) {
        console.error('Lỗi khi lấy thông báo:', e)
    }
}

const handleNotificationClick = async (item) => {
    try {
        const token = localStorage.getItem('access_token')
        if (!token) return

        if (item.is_read === 0) {
            await fetch(`${api}/notifications/${item.id}/read`, {
                method: 'POST',
                headers: { Authorization: `Bearer ${token}` }
            })
            item.is_read = 1
            unreadCount.value = notifications.value.filter(n => !n.is_read).length
        }

        if (item.link) {
            window.location.href = item.link
        }
    } catch (err) {
        console.error('Lỗi khi xử lý thông báo:', err)
    }
}

const markAllAsRead = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) return

    await Promise.all(notifications.value.map(item =>
        fetch(`${api}/notifications/${item.id}/read`, {
            method: 'POST', headers: { Authorization: `Bearer ${token}` }
        })
    ))

    notifications.value.forEach(item => item.is_read = 1)
    unreadCount.value = 0
}

const deleteAllNotifications = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) return

    await Promise.all(notifications.value.map(item =>
        fetch(`${api}/notifications/${item.id}`, {
            method: 'DELETE', headers: { Authorization: `Bearer ${token}` }
        })
    ))

    notifications.value = []
    selectedNotificationIds.value = []
    unreadCount.value = 0
}

const markSelectedAsRead = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) return

    await Promise.all(selectedNotificationIds.value.map(id =>
        fetch(`${api}/notifications/${id}/read`, {
            method: 'POST', headers: { Authorization: `Bearer ${token}` }
        })
    ))

    notifications.value.forEach(item => {
        if (selectedNotificationIds.value.includes(item.id)) {
            item.is_read = 1
        }
    })
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
    selectedNotificationIds.value = []
}

const deleteSelectedNotifications = async () => {
    const token = localStorage.getItem('access_token')
    if (!token || selectedNotificationIds.value.length === 0) return

    await fetch(`${api}/notifications/delete-multiple`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ ids: selectedNotificationIds.value })
    })

    notifications.value = notifications.value.filter(
        item => !selectedNotificationIds.value.includes(item.id)
    )
    selectedNotificationIds.value = []
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
}


const stripHTML = (html) => {
    const div = document.createElement('div')
    div.innerHTML = html
    return div.textContent || div.innerText || ''
}
</script>

<style scoped></style>
