<template>
    <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
        <!-- Breadcrumb -->
        <div class="px-6 pt-6">
            <h1 class="text-xl font-semibold text-gray-800">Chi tiết báo cáo đánh giá</h1>
        </div>
        <div class="px-6 pb-4">
            <NuxtLink to="/seller/reports/reviews" class="text-gray-600 hover:underline text-sm">
                Danh sách báo cáo
            </NuxtLink>
            <span class="text-gray-600 text-sm"> / Chi tiết báo cáo</span>
        </div>

        <div class="flex">
            <!-- Nội dung chính -->
            <main class="flex-1 p-6 bg-gray-100">
                <div class="max-w-5xl mx-auto bg-white rounded shadow border border-gray-200 p-6 space-y-6">
                    <div v-if="loading" class="text-sm text-gray-500">Đang tải dữ liệu...</div>

                    <div v-else-if="report">
                        <!-- Thông tin sản phẩm và người dùng -->
                        <div class="flex items-start gap-4">
                            <img v-if="report.review?.product_image"
                                :src="`${baseImageUrl}/${report.review.product_image}`"
                                class="w-24 h-24 object-cover border rounded" alt="Ảnh SP" />
                            <div>
                                <p class="text-lg font-semibold text-gray-800">{{ report.review.product_name }}</p>
                                <p class="text-sm"><strong>Người đánh giá:</strong> {{ report.review.user_name }}</p>
                                <p class="text-sm"><strong>Người báo cáo:</strong> {{ report.reporter }}</p>
                                <p class="text-sm"><strong>Ngày báo cáo:</strong> {{ formatDate(report.reported_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Chi tiết báo cáo -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div><strong>Lý do báo cáo:</strong> {{ reasonLabel(report.reason) }}</div>
                            <div>
                                <strong>Trạng thái:</strong>
                                <span :class="badgeClass(report.status)">{{ statusText(report.status) }}</span>
                            </div>
                            <div><strong>Số sao:</strong> <span class="text-yellow-500">{{ report.review.rating }} ★</span></div>
                            <div><strong>Lượt thích:</strong> {{ report.review.likes_count }}</div>
                        </div>

                        <!-- Nội dung đánh giá -->
                        <div>
                            <p class="font-semibold mb-1">Nội dung đánh giá:</p>
                            <div class="bg-gray-50 p-4 rounded border text-gray-800 whitespace-pre-line">
                                {{ report.review.content }}
                            </div>
                        </div>

                        <!-- Media -->
                        <div class="flex flex-wrap gap-2 mt-4">
                            <template v-for="(item, i) in report.review.media" :key="i">
                                <img v-if="item.type === 'image'" :src="item.url" @click="showImage = item.url"
                                    class="w-20 h-20 object-cover rounded border hover:scale-105 transition-transform cursor-pointer"
                                    :alt="'Ảnh ' + (i + 1)" />
                                <video v-else-if="item.type === 'video'" controls class="w-20 h-20 object-cover rounded border">
                                    <source :src="item.url" type="video/mp4" />
                                    Trình duyệt không hỗ trợ video.
                                </video>
                            </template>
                        </div>

                        <!-- Phản hồi admin -->
                        <div v-if="report.review.reply?.content">
                            <p class="font-semibold mt-4">Phản hồi từ quản trị viên:</p>
                            <div class="bg-gray-50 p-4 rounded border text-gray-700 whitespace-pre-line">
                                {{ report.review.reply.content }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Gửi lúc: {{ formatDate(report.review.reply.created_at) }}</p>
                        </div>

                        <!-- Hành động -->
                        <div class="flex gap-4 mt-6 border-t pt-6">
                            <button @click="updateStatus('resolved')"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                :disabled="report.status !== 'pending'">
                                Ẩn đánh giá
                            </button>
                            <button @click="updateStatus('rejected')"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                :disabled="report.status !== 'pending'">
                                Bỏ qua
                            </button>
                            <button class="ml-auto bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded"
                                @click="router.push('/seller/reports/reviews/list-reports')">
                                ← Quay lại danh sách
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal xem ảnh -->
                <div v-if="showImage" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
                    @click.self="showImage = null">
                    <img :src="showImage" class="max-h-[90vh] max-w-[90vw] rounded shadow-lg" />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig } from '#imports'
import axios from 'axios'

definePageMeta({ layout: 'default-seller' }) // Layout cho seller

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const baseImageUrl = config.public.r2BaseUrl || ''

const report = ref(null)
const loading = ref(true)
const showImage = ref(null)

const reasons = [
    { value: 'offensive', label: 'Đánh giá thô tục phản cảm' },
    { value: 'image', label: 'Chứa hình ảnh phản cảm, khỏa thân, khiêu dâm' },
    { value: 'duplicate', label: 'Đánh giá trùng lặp (thông tin rác)' },
    { value: 'personal', label: 'Chứa thông tin cá nhân' },
    { value: 'ads', label: 'Quảng cáo trái phép' },
    { value: 'wrong', label: 'Đánh giá không chính xác / gây hiểu lầm' },
    { value: 'other', label: 'Vi phạm khác' }
]

const reasonLabel = (code) => reasons.find(r => r.value === code)?.label || code

const statusText = (status) => {
    switch (status) {
        case 'pending': return 'Chờ xử lý'
        case 'resolved': return 'Đã ẩn'
        case 'rejected': return 'Đã bỏ qua'
        default: return 'Không rõ'
    }
}
const badgeClass = (status) => {
    switch (status) {
        case 'pending': return 'inline-block px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
        case 'resolved': return 'inline-block px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
        case 'rejected': return 'inline-block px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded'
        default: return 'inline-block px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded'
    }
}
function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('vi-VN')
}

async function fetchDetail() {
    loading.value = true
    try {
        const id = route.params.id
        const token = localStorage.getItem('access_token')
        const res = await axios.get(`${apiBase}/seller/reports/reviews/${id}`, {
            headers: { Authorization: `Bearer ${token}` }
        })
        report.value = res.data.data
    } catch (error) {
        console.error('Lỗi khi tải chi tiết báo cáo:', error)
    } finally {
        loading.value = false
    }
}

async function updateStatus(status) {
    try {
        const id = route.params.id
        const token = localStorage.getItem('access_token')
        await axios.put(`${apiBase}/seller/reports/reviews/${id}/status`, { status }, {
            headers: { Authorization: `Bearer ${token}` }
        })
        await fetchDetail()
    } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái:', error)
    }
}

onMounted(fetchDetail)
</script>
