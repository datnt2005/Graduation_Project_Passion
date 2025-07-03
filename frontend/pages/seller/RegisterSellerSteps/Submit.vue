<template>
  <div class="min-h-screen bg-gradient-to-b from-white to-blue-50 px-4 py-10 flex items-start justify-center relative">
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
            <RegisterSteps :currentStep="4" />
        </div>
    <div class="w-full max-w-2xl text-center space-y-6 pt-24">
            <!-- Icon + tiêu đề -->
            <div class="flex flex-col items-center gap-2">
                <div class="bg-blue-100 text-blue-600 rounded-full p-3">
                    <i class="fas fa-file-alt fa-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-blue-700">Xác nhận gửi đăng ký</h2>
                <p class="text-gray-600 text-sm">
                    Hệ thống sẽ xem xét và duyệt hồ sơ trong vòng
                    <span class="font-medium text-blue-600">1–3 ngày làm việc</span>.
                </p>
            </div>

            <!-- Thông tin đăng ký -->
            <div class="bg-white rounded-xl shadow p-5 text-left space-y-4" v-if="step1">
                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i>
                    Thông tin đăng ký
                </h3>
                <div class="bg-gray-50 rounded px-4 py-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tên cửa hàng:</span>
                        <span class="font-medium text-gray-700">{{ step1.store_name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Số điện thoại:</span>
                        <span class="font-medium text-gray-700">{{ step1.phone_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Địa chỉ lấy hàng:</span>
                        <span class="font-medium text-gray-700">{{ step1.pickup_address }}</span>
                    </div>
                </div>
            </div>

            <!-- Quy trình xử lý -->
            <div class="bg-white rounded-xl shadow p-5 text-left space-y-4">
                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-stream text-blue-500"></i>
                    Quy trình xử lý
                </h3>
                <div class="space-y-4 pl-6">
                    <div class="flex items-start gap-3">
                        <div class="text-green-500"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="font-medium text-gray-800">Gửi đăng ký</div>
                            <div class="text-sm text-gray-500">Hoàn thành thông tin cơ bản</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div
                            class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold">
                            2</div>
                        <div>
                            <div class="font-medium text-gray-800">Xem xét hồ sơ</div>
                            <div class="text-sm text-gray-500">1–2 ngày làm việc</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div
                            class="bg-gray-300 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold">
                            3</div>
                        <div>
                            <div class="font-medium text-gray-800">Thông báo kết quả</div>
                            <div class="text-sm text-gray-500">Qua email hoặc hệ thống</div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Nút hành động -->
            <div class="flex justify-center gap-4 pt-2">
                <button @click="goBackStep"
                    class="px-6 py-2 rounded-lg border border-blue-500 text-blue-600 font-medium hover:bg-blue-50">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                </button>
               <button
  @click="submit"
  :disabled="loading"
  class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 flex items-center justify-center min-w-[150px]"
>
  <svg
    v-if="loading"
    class="animate-spin h-4 w-4 mr-2 text-white"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
  >
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
    <path
      class="opacity-75"
      fill="currentColor"
      d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"
    />
  </svg>
  <i v-else class="fas fa-paper-plane mr-1"></i>
  <span>{{ loading ? 'Đang gửi...' : 'Gửi đăng ký' }}</span>
</button>
            </div>
            <p class="text-xs text-gray-500 text-center max-w-sm mx-auto">
                Khi nhấn <span class="font-medium">Gửi đăng ký</span>, bạn đồng ý với
                <a href="/seller/terms-of-service" target="_blank"
                    class="text-blue-600 underline hover:text-blue-800">Điều
                    khoản</a>
                và
                <a href="/seller/privacy-policy" target="_blank"
                    class="text-blue-600 underline hover:text-blue-800">Chính
                    sách</a>
                của chúng tôi.
            </p>
            <!-- Lỗi nếu có -->
            <div v-if="errorMessage" class="text-red-600 font-medium text-sm pt-2">
                {{ errorMessage }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from '~/composables/useToast'
const loading = ref(false)

const config = useRuntimeConfig()
const api = config.public.apiBaseUrl
const router = useRouter()
const { toast } = useToast()

const errorMessage = ref('')
const lastStepWithError = ref('step1')
const step1 = ref(null)
const step2 = ref(null)
const step3 = ref(null)
const step4 = ref(null)

const dataURLtoFile = (dataUrl, filename) => {
    const arr = dataUrl.split(',')
    const mime = arr[0].match(/:(.*?);/)[1]
    const bstr = atob(arr[1])
    let n = bstr.length
    const u8arr = new Uint8Array(n)
    while (n--) u8arr[n] = bstr.charCodeAt(n)
    return new File([u8arr], filename, { type: mime })
}

onMounted(() => {
    step1.value = JSON.parse(localStorage.getItem('register_step1') || '{}')
    step2.value = JSON.parse(localStorage.getItem('register_step2') || '{}')
    step3.value = JSON.parse(localStorage.getItem('register_step3') || '{}')
    step4.value = JSON.parse(localStorage.getItem('register_step4') || '{}')
})

const submit = async () => {
    errorMessage.value = ''
     loading.value = true 
    const token = localStorage.getItem('access_token')

    try {
        const formData = new FormData()
        formData.append('store_name', step1.value.store_name || '')
        formData.append('phone_number', step1.value.phone_number || '')
        formData.append('pickup_address', step1.value.pickup_address || '')
        formData.append('shipping_options[express]', step2.value.express ? 'true' : '')

        formData.append('seller_type', step3.value.seller_type || '')
        formData.append('tax_code', step3.value.tax_code || '')
        formData.append('business_name', step3.value.business_name || '')
        formData.append('business_email', step3.value.business_email || '')

        formData.append('identity_card_number', step4.value.identity_card_number || '')
        formData.append('date_of_birth', step4.value.date_of_birth || '')
        formData.append('personal_address', step4.value.personal_address || '')

        if (step4.value.frontImageBase64) {
            formData.append('id_card_front_url', dataURLtoFile(step4.value.frontImageBase64, 'cccd-front.png'))
        }
        if (step4.value.backImageBase64) {
            formData.append('id_card_back_url', dataURLtoFile(step4.value.backImageBase64, 'cccd-back.png'))
        }

        await axios.post(`${api}/sellers/register/full`, formData, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'multipart/form-data',
            },
        })

        localStorage.removeItem('register_step1')
        localStorage.removeItem('register_step2')
        localStorage.removeItem('register_step3')
        localStorage.removeItem('register_step4')

        toast('success', 'Gửi đăng ký thành công! Vui lòng chờ xác minh.')
        router.push('/seller/RegisterSellerSteps/Success')
    } catch (err) {
        console.error(err)
        if (err.response?.status === 422) {
            const errors = err.response.data.errors || {}

            if (errors.store_name || errors.phone_number || errors.pickup_address) {
                lastStepWithError.value = 'step1'
            } else if (errors.shipping_options) {
                lastStepWithError.value = 'step2'
            } else if (errors.tax_code || errors.business_email || errors.seller_type) {
                lastStepWithError.value = 'step3'
            } else if (errors.identity_card_number || errors.id_card_front_url || errors.date_of_birth) {
                lastStepWithError.value = 'step4'
            }

            errorMessage.value = 'Có lỗi trong hồ sơ đăng ký. Vui lòng quay lại sửa.'
            for (const field in errors) {
                const messages = errors[field]
                Array.isArray(messages)
                    ? messages.forEach(msg => toast('error', msg))
                    : toast('error', messages)
            }
        } else {
            toast('error', 'Đã xảy ra lỗi khi gửi đăng ký.')
        } 
    }finally {
    loading.value = false  
  }
}

const goBackStep = () => {
    router.push(`/seller/RegisterSellerSteps/${lastStepWithError.value}`)
}
</script>



<style scoped>
.btn-primary {
    @apply bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700;
}

.btn-outline {
    @apply border border-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-100;
}
</style>
