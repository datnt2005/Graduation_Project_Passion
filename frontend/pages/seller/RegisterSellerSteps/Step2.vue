```vue
<template>
  <div class="min-h-screen bg-gradient-to-b from-white to-blue-50 px-4 py-10 flex items-start justify-center relative">
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="2" />
    </div>
    <div class="w-full max-w-2xl text-center space-y-6 pt-24">
      <div class="flex flex-col items-center gap-2">
        <div class="bg-blue-100 text-blue-600 rounded-full p-3">
          <i class="fas fa-truck fa-lg"></i>
        </div>
        <h2 class="text-2xl font-bold text-blue-700">Phương thức vận chuyển</h2>
        <p class="text-gray-600 text-sm">
          Chọn các phương thức vận chuyển mà cửa hàng của bạn hỗ trợ.
        </p>
      </div>

      <div class="bg-white rounded-xl shadow p-5 space-y-4">
        <div class="space-y-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.shipping_options.express"
              :value="true"
              class="h-5 w-5 text-blue-600 rounded border-gray-300"
            />
            <span class="text-gray-700">Giao hàng nhanh (Express)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.shipping_options.standard"
              :value="true"
              class="h-5 w-5 text-blue-600 rounded border-gray-300"
            />
            <span class="text-gray-700">Giao hàng tiêu chuẩn (Standard)</span>
          </label>
        </div>
      </div>

      <div class="flex justify-center gap-4 pt-2">
        <button
          @click="router.push('/seller/RegisterSellerSteps/step1')"
          class="px-6 py-2 rounded-lg border border-blue-500 text-blue-600 font-medium hover:bg-blue-50"
        >
          <i class="fas fa-arrow-left mr-1"></i> Quay lại
        </button>
        <button
          @click="saveAndNext"
          class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700"
        >
          <i class="fas fa-arrow-right mr-1"></i> Tiếp tục
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '~/composables/useToast';
import RegisterSteps from '@/components/RegisterSteps.vue';

const router = useRouter();
const { toast } = useToast();
const form = ref({
  shipping_options: {
    express: false,
    standard: false,
  },
});

onMounted(() => {
  const savedData = localStorage.getItem('register_step2');
  if (savedData) {
    form.value = JSON.parse(savedData);
  }
});

const saveAndNext = () => {
  if (!form.value.shipping_options.express && !form.value.shipping_options.standard) {
    toast('error', 'Vui lòng chọn ít nhất một phương thức vận chuyển.');
    return;
  }
  localStorage.setItem('register_step2', JSON.stringify(form.value));
  router.push('/seller/RegisterSellerSteps/step3');
};
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700;
}

.btn-outline {
  @apply border border-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-100;
}
</style>
```