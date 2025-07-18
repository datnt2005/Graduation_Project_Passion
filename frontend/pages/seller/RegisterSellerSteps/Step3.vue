<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-white relative">
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="2" />
    </div>

    <!-- Cột trái: Ảnh -->
    <div class="hidden lg:flex items-center justify-center bg-gray-50">
      <img
        src="/images/SellerCenter2.png"
        alt="Thông tin doanh nghiệp"
        class="max-h-[500px] rounded-xl shadow-md"
      />
    </div>

    <!-- Cột phải: Form -->
    <div class="flex flex-col justify-center p-10 max-w-lg w-full mx-auto">
      <h2 class="text-2xl font-semibold text-gray-800 mb-6">Thông tin thuế và doanh nghiệp</h2>

      <form @submit.prevent="submitStep3" class="space-y-5">
        <!-- Loại người bán -->
        <div>
          <label class="block mb-1 font-medium">Loại hình người bán *</label>
          <select v-model="form.seller_type" class="input" required>
            <option value="">-- Chọn --</option>
            <option value="personal">Cá nhân</option>
            <option value="business">Doanh nghiệp</option>
          </select>
        </div>

        <!-- Mã số thuế -->
        <div>
          <label class="block mb-1 font-medium">Mã số thuế *</label>
          <input v-model="form.tax_code" type="text" class="input" required />
        </div>

        <!-- Nếu là doanh nghiệp -->
        <div v-if="form.seller_type === 'business'" class="space-y-4">
          <div>
            <label class="block mb-1 font-medium">Tên doanh nghiệp</label>
            <input v-model="form.business_name" type="text" class="input" />
          </div>
          <div>
            <label class="block mb-1 font-medium">Email doanh nghiệp</label>
            <input v-model="form.business_email" type="email" class="input" />
          </div>
         <div>
  <label class="block mb-2 font-medium">Tài liệu xác minh doanh nghiệp (PDF/JPG/PNG)</label>

  <div
    v-if="form.identity_card_file_name"
    class="w-full max-w-md border border-gray-200 bg-gray-50 rounded-2xl p-4 flex items-center justify-between shadow-sm"
  >
    <div class="flex items-center gap-3 text-gray-700">
      <i class="fas fa-file-alt text-xl text-blue-500"></i>
      <div class="flex flex-col text-sm">
        <span class="font-medium truncate">{{ form.identity_card_file_name }}</span>
        <span class="text-xs text-gray-500 italic">Tệp đã chọn</span>
      </div>
    </div>
    <button @click="removeIdentityCardFile" class="text-gray-500 hover:text-red-500 transition">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div
    v-else
    class="w-full max-w-md h-40 flex flex-col items-center justify-center bg-gray-100 text-gray-400 rounded-2xl border border-dashed border-gray-300 cursor-pointer hover:shadow-md transition"
    @click="$refs.identityCardFile.click()"
  >
    <i class="fas fa-file-upload text-3xl mb-2"></i>
    <p class="text-sm">Kéo thả tài liệu vào đây hoặc <span class="text-blue-600 underline">chọn file</span></p>
   <input
  type="file"
  ref="identityCardFile"
  @change="handleFileUpload"
  accept=".pdf,.jpg,.jpeg,.png"
  class="hidden"
/>
  </div>
</div>

        </div>

        <!-- Nút -->
        <div class="flex justify-between gap-3 pt-2">
          <button type="button" @click="goBackStep" class="btn-outline w-1/2 h-11">
            <i class="fas fa-arrow-left mr-1"></i> Quay lại
          </button>
          <button type="submit" class="btn btn-primary w-1/2 h-11">Tiếp theo</button>
        </div>
      </form>
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

const form = ref({
  seller_type: '',
  tax_code: '',
  business_name: '',
  business_email: '',
  identity_card_file_base64: '',
  identity_card_file_name: '',
})

const identityCardFile = ref(null)

onMounted(() => {
  const saved = localStorage.getItem('register_step3')
  if (saved) Object.assign(form.value, JSON.parse(saved))
})

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = () => {
    form.value.identity_card_file_base64 = reader.result
    form.value.identity_card_file_name = file.name
  }
  reader.readAsDataURL(file)
}

const removeIdentityCardFile = () => {
  form.value.identity_card_file_name = ''
  form.value.identity_card_file_base64 = ''
  if (identityCardFile.value) identityCardFile.value.value = null
}

const submitStep3 = () => {
  if (form.value.seller_type === 'personal') {
    form.value.business_name = ''
    form.value.business_email = ''
    form.value.identity_card_file_base64 = ''
    form.value.identity_card_file_name = ''
  }

  localStorage.setItem('register_step3', JSON.stringify(form.value))
  router.push('/seller/RegisterSellerSteps/step4')
}

const goBackStep = () => {
  router.push('/seller/RegisterSellerSteps/step2')
}
</script>


<style scoped>
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}
.btn-primary {
  @apply bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition;
}
.btn-outline {
  @apply border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition text-center;
}
</style>
