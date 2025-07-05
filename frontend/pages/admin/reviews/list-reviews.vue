<template>
    <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
        <div class="max-w-full overflow-x-auto">
            <!-- Header -->
            <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
                <h1 class="text-xl font-semibold text-gray-800">Quản lý đánh giá</h1>
            </div>

            <!-- Bộ lọc nâng cao -->
            <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
                    <select v-model="sortOrder" @change="applyFilters"
                        class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="desc">Mới nhất</option>
                        <option value="asc">Cũ nhất</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-600">Số sao:</label>
                    <select v-model="filterRating" @change="applyFilters"
                        class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tất cả</option>
                        <option v-for="n in 5" :key="n" :value="n">{{ n }} sao</option>
                    </select>
                </div>
            </div>

            <!-- Bảng đánh giá -->
            <table class="min-w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-white border-b border-gray-300">
                    <tr>
                        <th class="border px-3 py-2 font-semibold text-left">ID</th>
                        <th class="border px-3 py-2 font-semibold text-left">Sản phẩm</th>
                        <th class="border px-3 py-2 font-semibold text-left">Ảnh SP</th>
                        <th class="border px-3 py-2 font-semibold text-left">Ảnh đánh giá</th>
                        <th class="border px-3 py-2 font-semibold text-left">Thích</th>
                        <th class="border px-3 py-2 font-semibold text-left">Nội dung</th>
                        <th class="border px-3 py-2 font-semibold text-left">Sao</th>
                        <th class="border px-3 py-2 font-semibold text-left">Phản hồi</th>
                        <th class="border px-3 py-2 font-semibold text-left">Trạng thái</th>
                        <th class="border px-3 py-2 font-semibold text-left">Ngày</th>
                        <th class="border px-3 py-2 font-semibold text-left">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="review in reviews" :key="review.id" :class="{ 'bg-gray-50': review.id % 2 === 0 }"
                        class="border-b border-gray-300">
                        <td class="border px-3 py-2">#{{ review.id }}</td>
                        <td class="border px-3 py-2">{{ review.product_name }}</td>
                        <td class="border px-3 py-2">
                            <img v-if="review.product_image" :src="review.product_image"
                                class="w-12 h-12 object-cover rounded" />
                        </td>
                        <td class="border px-3 py-2">
                            <div class="flex gap-1">
                                <img v-for="img in review.images" :key="img" :src="img"
                                    class="w-10 h-10 object-cover rounded" />
                            </div>
                        </td>
                        <td class="border px-3 py-2">{{ review.likes_count }}</td>
                        <td class="border px-3 py-2 truncate max-w-[200px]">{{ review.content }}</td>
                        <td class="border px-3 py-2">{{ review.rating }} ★</td>
                        <td class="border px-3 py-2 text-sm italic text-gray-600">
                            <template v-if="review.reply">
                                {{ review.reply.content }}
                            </template>
                            <template v-else>
                                <span class="text-gray-400">Chưa phản hồi</span>
                            </template>
                        </td>
                        <td class="border px-3 py-2">
                            <span :class="statusClass(review.status)" class="px-2 py-1 text-xs font-semibold rounded">
                                {{ statusText(review.status) }}
                            </span>
                        </td>
                        <td class="border px-3 py-2">{{ formatDate(review.created_at) }}</td>
                        <td class="border px-3 py-2 relative">
                            <button @click.stop="toggleDropdown($event, review.id)"
                                class="p-1 text-gray-600 hover:text-black focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="reviews.length === 0">
                        <td colspan="11" class="text-center py-4 text-gray-500">Không có đánh giá nào</td>
                    </tr>
                </tbody>
            </table>
            <Teleport to="body">
                <Transition enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                    <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
                        <div class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right w-40"
                            :style="dropdownPosition" @click.stop>
                            <div class="py-1">
                                <button @click="viewReview(activeDropdown); closeDropdown()"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <Eye class="w-4 h-4 mr-2" /> Xem
                                </button>
                                <button @click="editReview(activeDropdown); closeDropdown()"
                                    class="flex items-center w-full px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                                    <Pencil class="w-4 h-4 mr-2" /> Sửa
                                </button>
                                <button @click="confirmDelete(activeDropdown); closeDropdown()"
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <Trash2 class="w-4 h-4 mr-2" /> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
        
    </div>
    
</template>

<script setup>
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRuntimeConfig } from '#app'
import { useNotification } from '~/composables/useNotification'
definePageMeta({ layout: 'default-admin' })
import { secureAxios } from '@/utils/secureAxios'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const reviews = ref([])
const loading = ref(true)
const { showMessage } = useNotification()
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px' })
const allReviews = ref([])


const sortOrder = ref('desc')       // mặc định: mới nhất
const filterRating = ref('')        // mặc định: tất cả


function toggleDropdown(event, id) {
  if (activeDropdown.value === id) {
    activeDropdown.value = null
    return
  }

  const rect = event.currentTarget.getBoundingClientRect()
  dropdownPosition.value = {
    top: `${rect.bottom + window.scrollY}px`,
    left: `${rect.left + window.scrollX - 160}px`
  }
  activeDropdown.value = id
}

function closeDropdown() {
  activeDropdown.value = null
}

function viewReview(id) {
    navigateTo(`/admin/reviews/view/${id}`)
}

function editReview(id) {
    navigateTo(`/admin/reviews/edit-reviews/${id}`)
}

async function confirmDelete(id) {
    if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
        await deleteReview(id)
    }
}


async function deleteReview(id) {
    try {
        const token = localStorage.getItem('access_token')
        await axios.delete(`${apiBase}/admin/reviews/${id}`, {
            headers: { Authorization: `Bearer ${token}` }
        })

        reviews.value = reviews.value.filter(r => r.id !== id)

        showMessage('Đã xóa đánh giá thành công', 'success')
    } catch (e) {
        console.error('Lỗi khi xóa đánh giá:', e)
        showMessage('Lỗi khi xóa đánh giá', 'error')
    }
}




onMounted(async () => {
  try {
    const res = await secureAxios(`${apiBase}/admin/reviews`, {}, ['admin'])
    allReviews.value = res.data.data
    applyFilters()  
  } catch (e) {
    console.error('Lỗi khi tải danh sách đánh giá:', e)
  } finally {
    loading.value = false
  }
})


function applyFilters() {
    let filtered = [...allReviews.value]

    // Lọc theo số sao nếu có
    if (filterRating.value) {
        filtered = filtered.filter(r => r.rating === Number(filterRating.value))
    }

    // Sắp xếp theo ngày
    filtered.sort((a, b) => {
        const dateA = new Date(a.created_at)
        const dateB = new Date(b.created_at)
        return sortOrder.value === 'asc'
            ? dateA - dateB
            : dateB - dateA
    })

    reviews.value = filtered
}


function formatDate(dateStr) {
    const d = new Date(dateStr)
    return d.toLocaleString('vi-VN')
}

function statusText(status) {
    switch (status) {
        case 'approved': return 'Đã duyệt'
        case 'pending': return 'Chờ duyệt'
        case 'rejected': return 'Từ chối'
        default: return 'Không rõ'
    }
}

function statusClass(status) {
    switch (status) {
        case 'approved': return 'bg-green-100 text-green-800'
        case 'pending': return 'bg-yellow-100 text-yellow-800'
        case 'rejected': return 'bg-red-100 text-red-800'
        default: return 'bg-gray-100 text-gray-800'
    }
}
</script>
