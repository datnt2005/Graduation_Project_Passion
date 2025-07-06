<template>
  <div class="w-full max-w-4xl mx-auto px-4">
    <div class="flex items-center justify-between">
      <div v-for="(step, index) in steps" :key="index" class="flex items-center flex-1">
        <div class="flex flex-col items-center relative">
          <div
            class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 border-2 relative z-10"
            :class="{
              'bg-[#1B9FE1] border-[#1B9FE1] text-white shadow-lg ring-4 ring-[#1B9FE1]/40': index === currentStep,
              'bg-[#1B9FE1] border-[#1B9FE1] text-white shadow-md': index < currentStep,
              'bg-white border-gray-300 text-gray-400': index > currentStep
            }"
          >
            <i v-if="index < currentStep" class="fas fa-check w-5 h-5"></i>
            <i
              v-else
              :class="[
                step.icon,
                'w-5 h-5 transition-colors duration-500 ease-in-out',
                index === currentStep ? 'text-white' : 'text-gray-400'
              ]"
            ></i>
          </div>

          <!-- Label -->
          <div class="mt-2 text-center">
            <p
              class="text-xs font-medium transition-colors duration-300"
              :class="{
                'text-[#1B9FE1]': index === currentStep,
                'text-gray-900': index < currentStep,
                'text-gray-500': index > currentStep
              }"
            >
              {{ step.label }}
            </p>
            <div
              class="mt-0.5 text-[11px] transition-colors duration-300"
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

        <div
          v-if="index < steps.length - 1"
          class="flex-1 h-2 mx-2 relative top-[-20px] overflow-hidden"
        >
          <div
            class="h-full w-full rounded-full origin-left"
            :class="[
              index < currentStep
                ? completedLines.includes(index)
                  ? 'bg-[#1B9FE1]'
                  : index === currentStep - 1
                    ? 'bg-[#1B9FE1] animate-fill-line'
                    : 'bg-[#1B9FE1]'
                : 'bg-gray-300'
            ]"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

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

const completedLines = ref([])

watch(() => currentStep, (newStep, oldStep) => {
  if (newStep > oldStep && newStep > 0) {
    completedLines.value.push(newStep - 1)
  }
})
</script>

<style scoped>
@keyframes fill-line {
  0% {
    transform: scaleX(0);
    opacity: 0;
  }
  100% {
    transform: scaleX(1);
    opacity: 1;
  }
}

.animate-fill-line {
  animation: fill-line 0.5s ease-in-out forwards;
}
</style>