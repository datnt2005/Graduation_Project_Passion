<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#eaf1fd] to-[#f4f7fd] px-2 py-6">
    <div
      class="max-w-9xl w-full flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-[0_8px_40px_0_rgba(22,61,124,.10)] border border-gray-200 bg-white mx-auto">
      <!-- Bên trái: Branding -->
      <div class="hidden md:flex flex-col justify-center items-center w-1/2 p-12 bg-transparent">
        <div class="mb-10 flex flex-col items-center">
          <img src="/images/SellerCenter2.png" alt="">
        </div>
        <div class="text-2xl font-bold text-gray-900 mb-2 text-center">
          Đăng ký bán hàng cùng <strong class="text-blue-600 font-extrabold">Passion</strong>
        </div>
        <div class="text-gray-500 text-base text-center max-w-[300px]">
          Tham gia cộng đồng bán hàng lớn nhất Việt Nam.<br>
          Tiếp cận hàng triệu khách hàng tiềm năng.
        </div>
      </div>
      <!-- Bên phải: Form -->
      <div class="w-full md:w-1/2 flex flex-col justify-center p-8 sm:p-16 bg-white">
        <div class="mx-auto w-full md:w-[80%]">
          <div class="mb-6">
            <div class="text-3xl font-bold text-gray-900 mb-1">Đăng ký ngay</div>
            <div class="text-gray-500 text-base">Tạo tài khoản bán hàng của bạn</div>
          </div>
          <div class="grid grid-cols-2 gap-2 mb-6 rounded-[12px] bg-[#f3f6fa] p-1.5">
            <button type="button"
              class="py-2 rounded-[10px] font-semibold text-base transition-all duration-200 shadow-sm" :class="sellerType === 'personal'
                ? 'bg-white text-blue-600 shadow border border-blue-500'
                : 'bg-transparent text-gray-700 border border-transparent hover:bg-white'"
              @click="sellerType = 'personal'">
              <span class="inline-flex items-center gap-2">Cá nhân/Nhỏ lẻ</span>
            </button>
            <button type="button"
              class="py-2 rounded-[10px] font-semibold text-base transition-all duration-200 shadow-sm" :class="sellerType === 'business'
                ? 'bg-white text-blue-600 shadow border border-blue-500'
                : 'bg-transparent text-gray-700 border border-transparent hover:bg-white'"
              @click="sellerType = 'business'">
              <span class="inline-flex items-center gap-2">Doanh nghiệp</span>
            </button>
          </div>

          <!-- FORM ĐĂNG KÝ -->
          <form @submit.prevent="handleSubmit" class="space-y-3">

            <!-- Email -->
            <div>
              <label class="block font-semibold text-sm mb-1.5 text-gray-700">Địa chỉ email</label>
              <input type="email"
                class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                v-model="form.email" :class="{ 'border-red-500': emailError }" placeholder="Nhập địa chỉ email" required>
              <p v-if="emailError" class="text-sm text-red-600 mt-1.5 flex items-center">
                Email chưa đúng định dạng.
              </p>
            </div>

            <div>
              <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tên cửa hàng</label>
              <input type="text"
                class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                v-model="form.store_name" placeholder="Nhập tên shop hoặc tên hiển thị" required>
            </div>

            <div>
              <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số điện thoại</label>
              <input type="text"
                class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                v-model="form.phone_number" placeholder="Nhập số điện thoại" required>
            </div>

            <!-- Cá nhân -->
            <template v-if="sellerType === 'personal'">
              <div class="mb-2">
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Ảnh CCCD/CMND <span
                    class="text-gray-500 font-normal">(2 mặt)</span></label>
                <label
                  class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 bg-[#f8fafc] cursor-pointer transition hover:border-[#1BA0E2] hover:bg-[#f1f8fd]">
                  <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 10-2.828-2.828z" />
                  </svg>
                  <span class="flex-1 text-sm text-gray-400">Chọn 2 ảnh CCCD/CMND (mặt trước & mặt sau)</span>
                  <span
                    class="ml-3 text-[#fff] bg-[#1BA0E2] px-3 py-1 rounded font-semibold text-sm hover:bg-[#1780B6] transition">Chọn
                    file</span>
                  <input type="file" class="hidden" accept="image/*" multiple @change="onCccdFiles">
                </label>
                <div class="mt-2 grid grid-cols-2 gap-3">
                  <div v-if="cccdPreviews[0]" class="flex flex-col items-center">
                    <img :src="cccdPreviews[0]" class="max-h-32 rounded-lg border border-gray-200 shadow"
                      alt="CCCD mặt trước" />
                    <span class="mt-1 text-xs text-gray-500">Mặt trước</span>
                  </div>
                  <div v-if="cccdPreviews[1]" class="flex flex-col items-center">
                    <img :src="cccdPreviews[1]" class="max-h-32 rounded-lg border border-gray-200 shadow"
                      alt="CCCD mặt sau" />
                    <span class="mt-1 text-xs text-gray-500">Mặt sau</span>
                  </div>
                </div>
              </div>

              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Ngày sinh</label>
                <input type="date"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.date_of_birth" required>
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Địa chỉ cá nhân</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.personal_address" placeholder="Nhập địa chỉ cá nhân">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Mô tả ngắn về shop (tuỳ chọn)</label>
                <textarea
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.bio" placeholder="Mô tả ngắn về cửa hàng..."></textarea>
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tài liệu xác thực bổ sung (tuỳ
                  chọn)</label>
                <label
                  class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 bg-[#f8fafc] cursor-pointer transition hover:border-[#1BA0E2] hover:bg-[#f1f8fd]">
                  <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 10-2.828-2.828z" />
                  </svg>
                  <span class="flex-1 text-sm text-gray-400">Chọn file ảnh/PDF bổ sung (nếu có)</span>
                  <span
                    class="ml-3 text-[#fff] bg-[#1BA0E2] px-3 py-1 rounded font-semibold text-sm hover:bg-[#1780B6] transition">Chọn
                    file</span>
                  <input type="file" class="hidden" accept="image/*,application/pdf" @change="onDocumentFile">
                </label>
                <div v-if="documentFile" class="mt-2">
                  <img v-if="isImage(documentPreview)" :src="documentPreview"
                    class="max-h-32 rounded-lg border border-gray-200 shadow" alt="Tài liệu" />
                  <div v-else class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                    <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ documentPreview }}</span>
                  </div>
                </div>
              </div>

            </template>

            <!-- Doanh nghiệp -->
            <template v-if="sellerType === 'business'">
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Mã số thuế</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.tax_code" placeholder="Nhập mã số thuế" required>
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Địa chỉ công ty</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.company_address" placeholder="Nhập địa chỉ công ty">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tỉnh/Thành phố</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.province" placeholder="Nhập tỉnh/thành phố">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Quận/Huyện</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.district" placeholder="Nhập quận/huyện">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tên người đại diện pháp lý</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.representative_name" placeholder="Nhập tên người đại diện pháp lý">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số điện thoại đại diện</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.representative_phone" placeholder="Nhập SĐT người đại diện">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số giấy phép kinh doanh</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.business_license" placeholder="Nhập số giấy phép kinh doanh">
              </div>
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">{{ label }}</label>
                <label
                  class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 bg-[#f8fafc] cursor-pointer transition hover:border-[#1BA0E2] hover:bg-[#f1f8fd]">
                  <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 10-2.828-2.828z" />
                  </svg>
                  <span class="flex-1 text-sm truncate text-gray-700" v-if="fileName">{{ fileName }}</span>
                  <span class="flex-1 text-sm text-gray-400" v-else>{{ placeholder }}</span>
                  <span
                    class="ml-3 text-[#fff] bg-[#1BA0E2] px-3 py-1 rounded font-semibold text-sm hover:bg-[#1780B6] transition">Chọn
                    file</span>
                  <input type="file" class="hidden" :accept="accept" @change="onChange">
                </label>
                <!-- Hiển thị preview nếu là ảnh -->
                <div v-if="previewUrl" class="mt-2">
                  <img :src="previewUrl" class="max-h-32 rounded-lg border border-gray-200 shadow" alt="Preview" />
                </div>
              </div>

            </template>

            <button type="submit" :disabled="loading"
              class="w-full text-white font-bold py-3 rounded-[11px] mt-2 text-lg transition-all duration-300 shadow disabled:opacity-60 disabled:cursor-not-allowed"
              :style="{ background: loading ? '#1BA0E2CC' : (isHover ? '#1780B6' : '#1BA0E2') }"
              @mouseenter="isHover = true" @mouseleave="isHover = false">
              <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 inline-block" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              {{ loading ? 'Đang đăng ký...' : 'Đăng ký ngay' }}
            </button>
          </form>
          <div class="text-center mt-5 text-base">
            Đã có tài khoản bán hàng?
            <NuxtLink href="/seller/login" class="text-blue-600 hover:underline font-semibold">Đăng nhập</NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const sellerType = ref('personal')
const loading = ref(false)
const emailError = ref(false)
const isHover = ref(false)

// preview cho file tài liệu
const cccdFiles = ref([])
const cccdPreviews = ref([])
const documentFile = ref(null)
const documentPreview = ref(null)
const documentName = ref('')
const fileName = ref('')
const previewUrl = ref(null)
const label = 'Tải lên file giấy phép kinh doanh'
const placeholder = 'Chọn file ảnh hoặc PDF'
const accept = 'image/*,application/pdf'
// cccd
function onCccdFiles(e) {
  const files = Array.from(e.target.files).slice(0, 2)
  cccdFiles.value = files
  cccdPreviews.value = files.map(f => URL.createObjectURL(f))
}

// bussiness
function onChange(e) {
  const file = e.target.files[0]
  if (!file) return
  fileName.value = file.name
  if (file.type.startsWith('image/')) {
    previewUrl.value = URL.createObjectURL(file)
  } else {
    previewUrl.value = null
  }
}

function onDocumentFile(e) {
  const file = e.target.files[0]
  if (!file) return
  documentFile.value = file
  documentName.value = file.name
  if (file.type.startsWith('image/')) {
    documentPreview.value = URL.createObjectURL(file)
  } else {
    documentPreview.value = null
  }
}


function isImage(url) {
  // Rất đơn giản, chỉ check url có blob: hoặc .jpg/png/gif/webp
  return url && (url.startsWith('blob:') || url.match(/\.(jpg|jpeg|png|gif|webp)$/i))
}
const form = ref({
  // Chung
  email: '',
  store_name: '',
  phone_number: '',
  // Personal
  identity_card_number: '',
  date_of_birth: '',
  personal_address: '',
  bio: '',
  identity_card_file: null,
  document: null,
  // Business
  tax_code: '',
  company_address: '',
  province: '',
  district: '',
  representative_name: '',
  representative_phone: '',
  business_license: '',
  business_license_file: null,
})


function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

async function handleSubmit() {
  emailError.value = false

  if (!validateEmail(form.value.email)) {
    emailError.value = true
    return
  }

  loading.value = true

  try {
    const formData = new FormData()

    // Chung
    formData.append('email', form.value.email)
    formData.append('store_name', form.value.store_name)
    formData.append('phone_number', form.value.phone_number)

    // Personal
    formData.append('identity_card_number', form.value.identity_card_number)
    formData.append('date_of_birth', form.value.date_of_birth)
    formData.append('personal_address', form.value.personal_address)
    formData.append('bio', form.value.bio)

    // File CCCD
    if (cccdFiles.value.length > 0) {
      cccdFiles.value.forEach((file, index) => {
        formData.append(`identity_card_file[${index}]`, file)
      })
    }

    // File giấy phép
    if (documentFile.value) {
      formData.append('document', documentFile.value)
    }

    // Business (nếu là doanh nghiệp)
    if (sellerType.value === 'business') {
      formData.append('tax_code', form.value.tax_code)
      formData.append('company_address', form.value.company_address)
      formData.append('province', form.value.province)
      formData.append('district', form.value.district)
      formData.append('representative_name', form.value.representative_name)
      formData.append('representative_phone', form.value.representative_phone)
      formData.append('business_license', form.value.business_license)

      if (form.value.business_license_file) {
        formData.append('business_license_file', form.value.business_license_file)
      }
    }

    const res = await axios.post('http://localhost:8000/api/sellers/resgister', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    alert('Đăng ký thành công!')
<<<<<<< HEAD
    console.log('Response:', res.data)
  } catch (error) {
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors

      if (errors.user_id && errors.user_id[0]) {
        alert(errors.user_id[0]) // => "Người dùng này đã đăng ký seller rồi."
      } else {
        alert('Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin.')
      }
    } else {
      alert('Lỗi không xác định. Vui lòng thử lại sau.')
    }
  } finally {
    loading.value = false
  }
=======
    // chuyển đến trang chờ
    window.location.href = '/seller/SellerRegisterSuccess'
  }, 800)
>>>>>>> 537c724c34ae742b86897289086712a3b14c6266
}

</script>
