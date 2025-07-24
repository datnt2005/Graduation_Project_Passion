<template>
  <div class="min-h-screen bg-gradient-to-br from-white to-blue-50 px-4 py-10 flex items-start justify-center relative">
    <!-- Header với stepper -->
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white shadow-sm">
      <RegisterSteps :currentStep="2" />
    </div>

<div class="w-full max-w-3xl mx-auto pt-24 mt-20 mobile-pt-180">
      <!-- Header Section -->
      <div class="text-center mb-8">
        <div style="margin-top: 100px;" class="flex flex-col items-center gap-4 mb-6">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full p-4 shadow-lg">
            <i class="fas fa-shipping-fast text-2xl"></i>
          </div>
          <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Phương thức vận chuyển</h2>
            <p class="text-gray-600 max-w-md mx-auto">
              Chọn các phương thức vận chuyển mà cửa hàng của bạn hỗ trợ để mang lại trải nghiệm tốt nhất cho khách hàng.
            </p>
          </div>
        </div>
      </div>

      <!-- Shipping Options -->
      <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-truck mr-3 text-blue-600"></i>
          Chọn phương thức vận chuyển
        </h3>
        
        <div class="space-y-4">
          <!-- Express Shipping -->
          <div 
            class="shipping-option"
            :class="{ 'selected': form.shipping_options.express }"
            @click="toggleShipping('express')"
          >
            <div class="flex items-start gap-4">
              <div class="flex items-center h-6">
                <input
                  type="checkbox"
                  v-model="form.shipping_options.express"
                  class="h-5 w-5 text-blue-600 rounded border-2 border-gray-300 focus:ring-2 focus:ring-blue-500"
                  @click.stop
                />
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <div class="bg-red-100 text-red-600 rounded-lg p-2">
                    <i class="fas fa-bolt"></i>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Giao hàng nhanh (Express)</h4>
                    <p class="text-sm text-gray-600">Giao hàng trong 1-2 ngày</p>
                  </div>
                </div>
                <div class="bg-red-50 rounded-lg p-3 ml-11">
                  <div class="flex items-center gap-2 text-sm text-red-700">
                    <i class="fas fa-clock"></i>
                    <span>Thời gian giao hàng: 1-2 ngày làm việc</span>
                  </div>
                  <div class="flex items-center gap-2 text-sm text-red-700 mt-1">
                    <i class="fas fa-shield-alt"></i>
                    <span>Bảo hiểm hàng hóa và theo dõi đơn hàng</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Standard Shipping -->
          <div 
            class="shipping-option"
            :class="{ 'selected': form.shipping_options.standard }"
            @click="toggleShipping('standard')"
          >
            <div class="flex items-start gap-4">
              <div class="flex items-center h-6">
                <input
                  type="checkbox"
                  v-model="form.shipping_options.standard"
                  class="h-5 w-5 text-blue-600 rounded border-2 border-gray-300 focus:ring-2 focus:ring-blue-500"
                  @click.stop
                />
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <div class="bg-green-100 text-green-600 rounded-lg p-2">
                    <i class="fas fa-truck"></i>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Giao hàng tiêu chuẩn (Standard)</h4>
                    <p class="text-sm text-gray-600">Giao hàng trong 3-5 ngày</p>
                  </div>
                </div>
                <div class="bg-green-50 rounded-lg p-3 ml-11">
                  <div class="flex items-center gap-2 text-sm text-green-700">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Thời gian giao hàng: 3-5 ngày làm việc</span>
                  </div>
                  <div class="flex items-center gap-2 text-sm text-green-700 mt-1">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Chi phí vận chuyển tiết kiệm</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <div class="text-blue-600 mt-0.5">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="text-sm text-blue-800">
              <p class="font-medium mb-1">Lưu ý quan trọng:</p>
              <ul class="space-y-1 text-blue-700">
                <li>• Bạn có thể chọn một hoặc cả hai phương thức vận chuyển</li>
                <li>• Khách hàng sẽ có thể lựa chọn phương thức phù hợp khi đặt hàng</li>
                <li>• Phí vận chuyển sẽ được tính tự động dựa trên khoảng cách và trọng lượng</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <button
          @click="router.push('/seller/RegisterSellerSteps/step1')"
          class="btn-outline flex items-center justify-center gap-2 px-8 py-3 text-base"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Quay lại</span>
        </button>
        <button
          @click="saveAndNext"
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
const loading = ref(false);

const form = ref({
  shipping_options: {
    express: false,
    standard: false,
  },
});

const toggleShipping = (type) => {
  form.value.shipping_options[type] = !form.value.shipping_options[type];
};

const saveAndNext = async () => {
  if (!form.value.shipping_options.express && !form.value.shipping_options.standard) {
    toast('error', 'Vui lòng chọn ít nhất một phương thức vận chuyển.');
    return;
  }
  
  loading.value = true;
  
  try {
    localStorage.setItem('register_step2', JSON.stringify(form.value));
    toast('success', 'Đã lưu thông tin vận chuyển thành công!');
    
    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 500));
    
    router.push('/seller/RegisterSellerSteps/step3');
  } catch (error) {
    toast('error', 'Có lỗi xảy ra khi lưu thông tin. Vui lòng thử lại.');
    console.error('Error saving step 2:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  const savedData = localStorage.getItem('register_step2');
  if (savedData) {
    try {
      form.value = JSON.parse(savedData);
    } catch (error) {
      console.error('Error loading saved data:', error);
    }
  }
});
</script>

<style scoped>
.shipping-option {
  @apply border-2 border-gray-200 rounded-xl p-4 cursor-pointer transition-all duration-300 hover:border-blue-300 hover:shadow-md;
}

.shipping-option.selected {
  @apply border-blue-500 bg-blue-50 shadow-md;
}

.btn-primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed;
}

.btn-outline {
  @apply border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.shipping-option {
  animation: fadeIn 0.4s ease-out forwards;
}

.shipping-option:nth-child(2) {
  animation-delay: 0.1s;
}

/* Responsive */
@media (max-width: 640px) {
  .btn-primary, .btn-outline {
    @apply w-full;
  }

    .mobile-pt-200 {
    padding-top: 200px !important;
  }
}
</style>
