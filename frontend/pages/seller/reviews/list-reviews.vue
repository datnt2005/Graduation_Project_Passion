<template>
    <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
        <div class="max-w-full overflow-x-auto">
            <!-- Header -->
            <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
                <h1 class="text-xl font-semibold text-gray-800">Quản lý đánh giá</h1>
            </div>

            <!-- Bộ lọc nâng cao -->
            <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
                <div class="flex items-center gap-2 flex-wrap">
                    <button v-for="n in [null, 5, 4, 3, 2, 1]" :key="'star-' + n"
                        @click="filterRating = n; applyFilters()" :class="[
                            'px-3 py-1 rounded border',
                            filterRating === n ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'
                        ]">
                        {{ n ? `${n} sao` : 'Tất cả' }} ({{ countByRating[n ?? 'all'] || 0 }})
                    </button>
                </div>

                <button @click="filterHasMedia = !filterHasMedia; applyFilters()" :class="[
                    'px-3 py-1 rounded border',
                    filterHasMedia ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'
                ]">
                    Có ảnh / video ({{ countWithMedia }})
                </button>

                <div class="flex items-center gap-2 flex-wrap">
                    <button v-for="status in ['approved', 'pending', 'rejected']" :key="'status-' + status"
                        @click="filterStatus = status; applyFilters()" :class="[
                            'px-3 py-1 rounded border',
                            filterStatus === status ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'
                        ]">
                        {{ statusText(status) }} ({{ countByStatus[status] || 0 }})
                    </button>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
                    <select v-model="sortOrder" @change="applyFilters"
                        class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="desc">Mới nhất</option>
                        <option value="asc">Cũ nhất</option>
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
                                <button @click="reportReview(activeDropdown); closeDropdown()"
                                    class="flex items-center w-full px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg> Báo cáo
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

            <!-- Report Dialog -->
            <ReportDialog 
                v-if="showReportDialog" 
                :target-id="selectedReviewId" 
                type="review" 
                @close="showReportDialog = false" 
                @submitted="handleReportSubmitted" 
            />
        </div>
    </div>
</template>
```


```vue
<script setup>
import { Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRuntimeConfig } from '#app'
import { useNotification } from '~/composables/useNotification'
import { secureAxios } from '@/utils/secureAxios'
import ReportDialog from '~/components/shared/ReportDialog.vue'

definePageMeta({ layout: 'default-seller' })

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const reviews = ref([])
const allReviews = ref([])
const loading = ref(true)

const { showNotification } = useNotification()

const sortOrder = ref('desc')
const filterRating = ref(null)
const filterStatus = ref('all')
const filterHasMedia = ref(false)

const countByRating = ref({})
const countByStatus = ref({})
const countWithMedia = ref(0)

const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px' })
const showReportDialog = ref(false)
const selectedReviewId = ref(null)

// -------------------------
// 1. Fetch reviews của seller
onMounted(async () => {
    try {
        const res = await secureAxios(`${apiBase}/seller/reviews`, {}, ['seller'])
        allReviews.value = res.data.data
        countFilters()
        applyFilters()
    } catch (e) {
        console.error('Lỗi khi tải đánh giá của seller:', e)
        showNotification('Lỗi khi tải đánh giá', 'error')
    } finally {
        loading.value = false
    }
})

// -------------------------
// 2. Lọc theo trạng thái, sao, ảnh
function countFilters() {
    const ratingCount = { all: allReviews.value.length }
    const statusCount = { all: allReviews.value.length }
    let withMedia = 0

    allReviews.value.forEach(r => {
        // Rating
        if (ratingCount[r.rating]) ratingCount[r.rating]++
        else ratingCount[r.rating] = 1

        // Status
        if (statusCount[r.status]) statusCount[r.status]++
        else statusCount[r.status] = 1

        // Media
        if (r.images?.length) withMedia++
    })

    countByRating.value = ratingCount
    countByStatus.value = statusCount
    countWithMedia.value = withMedia
}

// -------------------------
// 3. Áp dụng lọc
function applyFilters() {
    let filtered = [...allReviews.value]

    if (filterRating.value) {
        filtered = filtered.filter(r => r.rating === filterRating.value)
    }

    if (filterStatus.value !== 'all') {
        filtered = filtered.filter(r => r.status === filterStatus.value)
    }

    if (filterHasMedia.value) {
        filtered = filtered.filter(r => r.images?.length > 0)
    }

    filtered.sort((a, b) => {
        const dateA = new Date(a.created_at)
        const dateB = new Date(b.created_at)
        return sortOrder.value === 'asc' ? dateA - dateB : dateB - dateA
    })

    reviews.value = filtered
}

// -------------------------
// 4. Dropdown thao tác
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

// -------------------------
// 5. Chuyển trang view/sửa
function viewReview(id) {
    navigateTo(`/seller/reviews/view/${id}`)
}

function editReview(id) {
    navigateTo(`/seller/reviews/edit-reviews/${id}`)
}

// -------------------------
// 6. Báo cáo đánh giá
function reportReview(id) {
    selectedReviewId.value = id
    showReportDialog.value = true
}

function handleReportSubmitted() {
    showReportDialog.value = false
    showNotification('Báo cáo đã được gửi thành công', 'success')
}

// -------------------------
// 7. Xóa đánh giá
async function confirmDelete(id) {
    await deleteReview(id)
}

async function deleteReview(id) {
    try {
        const token = localStorage.getItem('access_token')
        await axios.delete(`${apiBase}/seller/reviews/${id}`, {
            headers: { Authorization: `Bearer ${token}` }
        })

        reviews.value = reviews.value.filter(r => r.id !== id)
        allReviews.value = allReviews.value.filter(r => r.id !== id)
        countFilters()
        applyFilters()
        showNotification('Đã xóa đánh giá thành công', 'success')
    } catch (e) {
        console.error('Lỗi khi xóa đánh giá:', e)
        showNotification('Lỗi khi xóa đánh giá', 'error')
    }
}

// -------------------------
// 8. Format
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

// -------------------------
// 9. Lấy review đang chọn trong dropdown
function getReviewById(id) {
    return reviews.value.find(r => r.id === id)
}
</script>