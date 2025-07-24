<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-gradient-to-br from-white to-blue-50 relative">
    <!-- Header với stepper -->
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white shadow-sm">
      <RegisterSteps :currentStep="2" />
    </div>

    <!-- Cột trái: Ảnh -->
    <div class="hidden lg:flex flex-col items-center justify-center p-8">
      <img
        src="/images/SellerCenter2.png"
        alt="Thông tin doanh nghiệp"
        class="max-h-[500px] rounded-xl shadow-lg transition-all duration-500 hover:scale-105"
      />
      <div class="mt-8 text-center max-w-md">
        <h2 class="text-xl font-bold text-blue-800 mb-2">Thông tin pháp lý</h2>
        <p class="text-blue-700 opacity-80">Cung cấp thông tin thuế và doanh nghiệp để đảm bảo tính minh bạch và tuân thủ pháp luật</p>
      </div>
    </div>

    <!-- Cột phải: Form -->
<div class="flex flex-col justify-center p-8 lg:p-10 max-w-2xl w-full mx-auto pt-[200px] lg:pt-[270px]">
<div class="bg-white rounded-2xl shadow-lg p-10 sm:p-8">
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-4">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full p-3">
              <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Thông tin thuế và doanh nghiệp</h2>
              <p class="text-gray-600 text-sm">Cung cấp thông tin pháp lý để xác minh tài khoản</p>
            </div>
          </div>
        </div>

        <form @submit.prevent="submitStep3" class="space-y-6">
          <!-- Loại người bán -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-user-tag mr-3 text-blue-600"></i>
              Loại hình người bán <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div 
                class="seller-type-option"
                :class="{ 'selected': form.seller_type === 'personal' }"
                @click="form.seller_type = 'personal'"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="radio"
                    v-model="form.seller_type"
                    value="personal"
                    class="h-4 w-4 text-blue-600"
                    @click.stop
                  />
                  <div class="flex items-center gap-2">
                    <i class="fas fa-user text-blue-600"></i>
                    <span class="font-medium">Cá nhân</span>
                  </div>
                </div>
                <p class="text-xs text-gray-600 mt-2 ml-7">Bán hàng với tư cách cá nhân</p>
              </div>
              
              <div 
                class="seller-type-option"
                :class="{ 'selected': form.seller_type === 'business' }"
                @click="form.seller_type = 'business'"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="radio"
                    v-model="form.seller_type"
                    value="business"
                    class="h-4 w-4 text-blue-600"
                    @click.stop
                  />
                  <div class="flex items-center gap-2">
                    <i class="fas fa-building text-blue-600"></i>
                    <span class="font-medium">Doanh nghiệp</span>
                  </div>
                </div>
                <p class="text-xs text-gray-600 mt-2 ml-7">Công ty, doanh nghiệp</p>
              </div>
            </div>
          </div>

          <!-- Mã số thuế -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-hashtag mr-3 text-green-600"></i>
              Mã số thuế <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                <i class="fas fa-receipt"></i>
              </span>
              <input 
                v-model="form.tax_code" 
                type="text" 
                class="input pl-10" 
                placeholder="Nhập mã số thuế"
                required 
              />
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-1">
              <i class="fas fa-info-circle mr-1"></i>
              Mã số thuế cá nhân hoặc doanh nghiệp
            </p>
          </div>

          <!-- Thông tin doanh nghiệp -->
          <transition name="slide-fade">
            <div v-if="form.seller_type === 'business'" class="bg-blue-50 rounded-xl p-6 space-y-5">
              <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-4">
                <i class="fas fa-building"></i>
                Thông tin doanh nghiệp
              </h3>
              
              <!-- Tên doanh nghiệp -->
              <div class="form-group">
                <label class="form-label">Tên doanh nghiệp</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                    <i class="fas fa-building"></i>
                  </span>
                  <input 
                    v-model="form.business_name" 
                    type="text" 
                    class="input pl-10" 
                    placeholder="Nhập tên công ty/doanh nghiệp"
                  />
                </div>
              </div>

              <!-- Email doanh nghiệp -->
              <div class="form-group">
                <label class="form-label">Email doanh nghiệp</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                    <i class="fas fa-envelope"></i>
                  </span>
                  <input 
                    v-model="form.business_email" 
                    type="email" 
                    class="input pl-10" 
                    placeholder="email@company.com"
                  />
                </div>
              </div>

              <!-- Upload tài liệu -->
              <div class="form-group">
                <label class="form-label">
                  <i class="fas fa-file-upload mr-3 text-purple-600"></i>
                  Tài liệu xác minh doanh nghiệp
                </label>
                <p class="text-xs text-gray-600 mb-3">Chấp nhận file PDF, JPG, PNG (tối đa 5MB)</p>
                
                <!-- File đã chọn -->
                <div
                  v-if="form.identity_card_file_name"
                  class="file-preview"
                >
                  <div class="flex items-center gap-3">
                    <div class="bg-green-100 text-green-600 rounded-lg p-2">
                      <i class="fas fa-file-check text-lg"></i>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium text-gray-800 truncate">{{ form.identity_card_file_name }}</p>
                      <p class="text-xs text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>
                        Tệp đã được chọn
                      </p>
                    </div>
                    <button 
                      @click="removeIdentityCardFile" 
                      class="text-gray-400 hover:text-red-500 transition-colors p-2"
                      type="button"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>

                <!-- Upload area -->
                <div
                  v-else
                  class="upload-area"
                  @click="$refs.identityCardFile.click()"
                  @dragover.prevent
                  @drop.prevent="handleFileDrop"
                >
                  <div class="text-center">
                    <div class="bg-blue-100 text-blue-600 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                      <i class="fas fa-cloud-upload-alt text-2xl"></i>
                    </div>
                    <p class="text-gray-700 font-medium mb-1">Kéo thả tài liệu vào đây</p>
                    <p class="text-sm text-gray-500">
                      hoặc <span class="text-blue-600 underline cursor-pointer">chọn file từ máy tính</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                      Giấy phép kinh doanh, đăng ký doanh nghiệp
                    </p>
                  </div>
                </div>

                <input
                  type="file"
                  ref="identityCardFile"
                  @change="handleFileUpload"
                  accept=".pdf,.jpg,.jpeg,.png"
                  class="hidden"
                />
              </div>
            </div>
          </transition>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row justify-center gap-4 pt-6">
            <button 
              type="button" 
              @click="goBackStep" 
              class="btn-outline flex items-center justify-center gap-2 px-8 py-3 text-base"
            >
              <i class="fas fa-arrow-left"></i>
              <span>Quay lại</span>
            </button>
            <button 
              type="submit" 
              class="btn-primary flex items-center justify-center gap-2 px-8 py-3 text-base"
              :disabled="loading"
            >
              <span v-if="!loading">Tiếp tục</span>
              <span v-else class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu
              </span>
              <i v-if="!loading" class="fas fa-arrow-right"></i>
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
const { toast } = useToast()
const loading = ref(false)

const form = ref({
  seller_type: '',
  tax_code: '',
  business_name: '',
  business_email: '',
  identity_card_file_base64: '',
  identity_card_file_name: '',
})

const identityCardFile = ref(null)

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return
  
  // Validate file size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    toast('error', 'File quá lớn. Vui lòng chọn file nhỏ hơn 5MB.')
    return
  }
  
  const reader = new FileReader()
  reader.onload = () => {
    form.value.identity_card_file_base64 = reader.result
    form.value.identity_card_file_name = file.name
    toast('success', 'Đã tải file thành công!')
  }
  reader.readAsDataURL(file)
}

const handleFileDrop = (e) => {
  const files = e.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    if (file.type.match(/^(image\/(jpeg|jpg|png)|application\/pdf)$/)) {
      handleFileUpload({ target: { files: [file] } })
    } else {
      toast('error', 'Chỉ chấp nhận file PDF, JPG, PNG.')
    }
  }
}

const removeIdentityCardFile = () => {
  form.value.identity_card_file_name = ''
  form.value.identity_card_file_base64 = ''
  if (identityCardFile.value) identityCardFile.value.value = null
  toast('info', 'Đã xóa file.')
}

const submitStep3 = async () => {
  // Validation
  if (!form.value.seller_type) {
    toast('error', 'Vui lòng chọn loại hình người bán.')
    return
  }
  
  if (!form.value.tax_code) {
    toast('error', 'Vui lòng nhập mã số thuế.')
    return
  }
  
  loading.value = true
  
  try {
    if (form.value.seller_type === 'personal') {
      form.value.business_name = ''
      form.value.business_email = ''
      form.value.identity_card_file_base64 = ''
      form.value.identity_card_file_name = ''
    }
    
    localStorage.setItem('register_step3', JSON.stringify(form.value))
    toast('success', 'Đã lưu thông tin thuế và doanh nghiệp thành công!')
    
    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 500))
    
    router.push('/seller/RegisterSellerSteps/step4')
  } catch (error) {
    toast('error', 'Có lỗi xảy ra khi lưu thông tin. Vui lòng thử lại.')
    console.error('Error saving step 3:', error)
  } finally {
    loading.value = false
  }
}

const goBackStep = () => {
  router.push('/seller/RegisterSellerSteps/step2')
}

onMounted(() => {
  const saved = localStorage.getItem('register_step3')
  if (saved) {
    try {
      Object.assign(form.value, JSON.parse(saved))
    } catch (error) {
      console.error('Error loading saved data:', error)
    }
  }
})
</script>

<style scoped>
.form-group {
  @apply mb-4;
}

.form-label {
  @apply block mb-2 font-medium text-gray-700 text-sm;
}

.input {
  @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200;
}

.input.pl-10 {
  @apply pl-12;
}

.input:hover {
  @apply border-gray-400;
}

.seller-type-option {
  @apply border-2 border-gray-200 rounded-xl p-4 cursor-pointer transition-all duration-300 hover:border-blue-300 hover:shadow-md;
}

.seller-type-option.selected {
  @apply border-blue-500 bg-blue-50 shadow-md;
}

.upload-area {
  @apply w-full h-40 flex flex-col items-center justify-center bg-gray-50 text-gray-500 rounded-xl border-2 border-dashed border-gray-300 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-300;
}

.file-preview {
  @apply w-full border border-gray-200 bg-gray-50 rounded-xl p-4 shadow-sm;
}

.btn-primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed;
}

.btn-outline {
  @apply border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2;
}

/* Animations */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.form-group {
  animation: fadeIn 0.4s ease-out forwards;
}

.form-group:nth-child(2) { animation-delay: 0.1s; }
.form-group:nth-child(3) { animation-delay: 0.2s; }
.form-group:nth-child(4) { animation-delay: 0.3s; }

/* Responsive */
@media (max-width: 640px) {
  .btn-primary, .btn-outline {
    @apply w-full;
  }
  
  .grid-cols-2 {
    @apply grid-cols-1;
  }

   .mobile-pt-200 {
    padding-top: 320px !important;
  }
  
}
</style>
