<template>
<div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-white relative">
      <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="3" />
    </div>        <!-- Cột trái: Ảnh -->
        <div class="hidden lg:flex items-center justify-center">
            <img src="/images/SellerCenter2.png" alt="CCCD" class="max-h-[500px] rounded-xl shadow-md" />
        </div>

        <!-- Cột phải: Form -->
        <div class="flex items-center justify-center px-8 py-12">
            <div class="w-full max-w-xl">
                <div class="mb-8 pt-20">
                    <h1 class="text-2xl font-bold text-blue-700">Xác minh danh tính</h1>
                    <p class="text-gray-600">Vui lòng cung cấp thông tin và ảnh CCCD của bạn</p>
                </div>

                <form @submit.prevent="submitStep4" class="space-y-5">
                    <div class="bg-blue-50 text-blue-600 px-4 py-3 rounded text-sm">
                        Thông tin cá nhân của bạn được bảo mật.
                    </div>

                    <!-- Số CCCD -->
                    <div>
                        <label class="block mb-1 font-medium">Số CCCD *</label>
                        <div class="relative">
                            <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
                            <input v-model="form.identity_card_number" type="text" class="input pl-10"
                                placeholder="Nhập số CCCD (12 chữ số)" />
                        </div>
                        <p v-if="errors.identity_card_number" class="text-red-500 text-sm mt-1">{{
                            errors.identity_card_number[0] }}</p>
                    </div>

                    <!-- Ngày sinh -->
                    <div>
                        <label class="block mb-1 font-medium">Ngày sinh *</label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
                            <input v-model="form.date_of_birth" type="date" class="input pl-10" />
                        </div>
                        <p v-if="errors.date_of_birth" class="text-red-500 text-sm mt-1">{{ errors.date_of_birth[0] }}
                        </p>
                    </div>

                    <!-- Địa chỉ -->
                    <div>
                        <label class="block mb-1 font-medium">Địa chỉ cá nhân *</label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
                            <input v-model="form.personal_address" type="text" class="input pl-10"
                                placeholder="Nhập địa chỉ thường trú" />
                        </div>
                        <p v-if="errors.personal_address" class="text-red-500 text-sm mt-1">{{
                            errors.personal_address[0] }}</p>
                    </div>



                    <!-- Ảnh mặt trước -->
                    <div>
                        <label class="block mb-1 font-medium">Ảnh mặt trước CCCD *</label>
                        <div v-if="frontPreview"
                            class="relative flex items-center gap-3 bg-gray-50 border rounded px-4 py-2">
                            <img :src="frontPreview" alt="Front" class="w-10 h-10 object-cover rounded" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ frontFileName }}</p>
                                <p class="text-xs text-gray-500">{{ frontFileSize }}</p>
                            </div>
                            <button @click="removeFront" class="text-gray-500 hover:text-red-500"><i
                                    class="fas fa-times"></i></button>
                        </div>
                        <div v-else
                            class="border border-dashed border-gray-300 rounded-md p-4 text-center text-sm text-gray-500">
                            <label class="cursor-pointer">
                                <i class="fas fa-upload text-blue-500 mr-2"></i>
                                Kéo thả ảnh vào đây hoặc <span class="text-blue-600 underline">chọn file</span>
                                <input type="file" ref="frontFile" class="hidden" accept="image/*"
                                    @change="handleFrontChange" />
                            </label>
                        </div>
                        <p v-if="errors.id_card_front_url" class="text-red-500 text-sm mt-1">{{
                            errors.id_card_front_url[0] }}</p>
                    </div>

                    <!-- Ảnh mặt sau -->
                    <div>
                        <label class="block mb-1 font-medium">Ảnh mặt sau CCCD *</label>
                        <div v-if="backPreview"
                            class="relative flex items-center gap-3 bg-gray-50 border rounded px-4 py-2">
                            <img :src="backPreview" alt="Back" class="w-10 h-10 object-cover rounded" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ backFileName }}</p>
                                <p class="text-xs text-gray-500">{{ backFileSize }}</p>
                            </div>
                            <button @click="removeBack" class="text-gray-500 hover:text-red-500"><i
                                    class="fas fa-times"></i></button>
                        </div>
                        <div v-else
                            class="border border-dashed border-gray-300 rounded-md p-4 text-center text-sm text-gray-500">
                            <label class="cursor-pointer">
                                <i class="fas fa-upload text-blue-500 mr-2"></i>
                                Kéo thả ảnh vào đây hoặc <span class="text-blue-600 underline">chọn file</span>
                                <input type="file" ref="backFile" class="hidden" accept="image/*"
                                    @change="handleBackChange" />
                            </label>
                        </div>
                        <p v-if="errors.id_card_back_url" class="text-red-500 text-sm mt-1">{{
                            errors.id_card_back_url[0] }}</p>
                    </div>


                    <button type="submit" class="btn btn-primary w-full h-11" :disabled="loading">
                        <span v-if="!loading">Gửi đăng ký</span>
                        <span v-else>Đang xử lý...</span>
                    </button>

                    <div class="text-center mt-3">
                        <button type="button" @click="goBackStep" class="text-blue-600 text-sm underline">
                            ← Quay lại bước trước
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '~/composables/useToast'
import RegisterSteps from '@/components/RegisterSteps.vue'

const router = useRouter()
const loading = ref(false)
const errors = ref({})
const { toast } = useToast()

const frontPreview = ref(null)
const backPreview = ref(null)
const frontFileName = ref('')
const backFileName = ref('')
const frontFileSize = ref('')
const backFileSize = ref('')
const frontFile = ref(null)
const backFile = ref(null)

const form = ref({
  identity_card_number: '',
  date_of_birth: '',
  personal_address: ''
})

// Load lại dữ liệu cũ nếu có
onMounted(() => {
  const saved = localStorage.getItem('register_step4')
  if (saved) {
    const data = JSON.parse(saved)
    Object.assign(form.value, data)
    if (data.frontImageBase64) frontPreview.value = data.frontImageBase64
    if (data.backImageBase64) backPreview.value = data.backImageBase64
  }
})

// Gửi form
const submitStep4 = async () => {
  loading.value = true
  errors.value = {}

  try {
    let frontBase64 = frontPreview.value
    let backBase64 = backPreview.value

    if (frontFile.value?.files[0]) {
      frontBase64 = await readFileAsBase64(frontFile.value.files[0])
    }

    if (backFile.value?.files[0]) {
      backBase64 = await readFileAsBase64(backFile.value.files[0])
    }

    localStorage.setItem('register_step4', JSON.stringify({
      ...form.value,
      frontImageBase64: frontBase64,
      backImageBase64: backBase64
    }))

    router.push('/seller/RegisterSellerSteps/Submit')
  } catch (err) {
    toast('error', 'Đã xảy ra lỗi khi xử lý ảnh.')
  } finally {
    loading.value = false
  }
}

// Hàm convert file -> base64
const readFileAsBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

// Preview và lưu ảnh mặt trước
const handleFrontChange = async (e) => {
  const file = e.target.files[0]
  if (file) {
    frontFileName.value = file.name
    frontFileSize.value = (file.size / 1024 / 1024).toFixed(2) + ' MB'
    const base64 = await readFileAsBase64(file)
    frontPreview.value = base64
  }
}

// Preview và lưu ảnh mặt sau
const handleBackChange = async (e) => {
  const file = e.target.files[0]
  if (file) {
    backFileName.value = file.name
    backFileSize.value = (file.size / 1024 / 1024).toFixed(2) + ' MB'
    const base64 = await readFileAsBase64(file)
    backPreview.value = base64
  }
}

// Xoá ảnh preview
const removeFront = () => {
  frontPreview.value = null
  frontFileName.value = ''
  frontFileSize.value = ''
  frontFile.value.value = ''
}
const removeBack = () => {
  backPreview.value = null
  backFileName.value = ''
  backFileSize.value = ''
  backFile.value.value = ''
}

const goBackStep = () => {
  router.push('/seller/RegisterSellerSteps/step3')
}
</script>


<style scoped>
.input {
    @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
    @apply bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition;
}

.input {
    @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
    padding-left: 2.5rem !important;
}
</style>
