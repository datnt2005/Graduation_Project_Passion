<template>
  <div class="w-full max-w-4xl mx-auto px-4">
    <div class="flex items-center justify-between">
      <div
        v-for="(step, index) in steps"
        :key="index"
        class="flex items-center flex-1"
      >
        <!-- Step Circle and Content -->
        <div class="flex flex-col items-center relative">
          <!-- Step Circle -->
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border-2 relative z-10"
            :class="{
              'bg-[#1B9FE1] border-[#1B9FE1] text-white shadow-md': index < currentStep || index === currentStep,
              'bg-white border-gray-300 text-gray-400': index > currentStep,
              'ring-2 ring-[#1B9FE1]/30': index === currentStep
            }"
          >
            <i
              v-if="index < currentStep"
              class="fas fa-check w-5 h-5"
            ></i>
            <i
              v-else
              :class="[
                step.icon,
                'w-5 h-5',
                index === currentStep ? 'text-white' : 'text-gray-400'
              ]"
            ></i>
          </div>

          <!-- Step Label -->
          <div class="mt-2 text-center">
            <p
              class="text-xs font-medium transition-colors duration-200"
              :class="{
                'text-[#1B9FE1]': index === currentStep,
                'text-gray-900': index < currentStep,
                'text-gray-500': index > currentStep
              }"
            >
              {{ step.label }}
            </p>
            <div
              class="mt-0.5 text-[11px] transition-colors duration-200"
              :class="{
                'text-[#1B9FE1]': index === currentStep,
                'text-green-600': index < currentStep,
                'text-gray-400': index > currentStep
              }"
            >
              {{ index < currentStep ? 'Hoàn thành' : index === currentStep ? 'Đang thực hiện' : 'Chờ thực hiện' }}
            </div>
          </div>
        </div>

        <!-- Connecting Line -->
        <div
          v-if="index < steps.length - 1"
          class="flex-1 h-px mx-2 relative top-[-20px]"
        >
          <div
            class="h-full transition-all duration-500 ease-in-out"
            :class="index < currentStep ? 'bg-[#1B9FE1]' : 'bg-gray-300'"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const { currentStep } = defineProps({
  currentStep: {
    type: Number,
    required: true
  }
})

const steps = [
  { label: 'Thông tin Shop', icon: 'fas fa-store' },
  { label: 'Cài đặt vận chuyển', icon: 'fas fa-shipping-fast' },
  { label: 'Thông tin thuế', icon: 'fas fa-file-invoice-dollar' },
  { label: 'Thông tin định danh', icon: 'fas fa-id-card' },
  { label: 'Hoàn tất', icon: 'fas fa-check-circle' }
]
</script>
