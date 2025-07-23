<template>
  <div class="w-full max-w-6xl mx-auto px-4 py-6 pb-16">
    <!-- Desktop Stepper -->
    <div class="hidden md:flex items-center justify-between relative">
      <!-- Background line -->
      <div class="absolute top-6 left-0 right-0 h-1 bg-gray-200 rounded-full -z-10"></div>
      <div 
        class="absolute top-6 left-0 h-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-700 ease-out -z-10"
        :style="{ width: `${(currentStep / (steps.length - 1)) * 100}%` }"
      ></div>

      <div 
        v-for="(step, index) in steps" 
        :key="index" 
        class="flex flex-col items-center relative z-10"
        :class="{ 'flex-1': index < steps.length - 1 }"
      >
        <!-- Step Circle -->
        <div
          class="step-circle"
          :class="{
            'completed': index < currentStep,
            'current': index === currentStep,
            'pending': index > currentStep
          }"
        >
          <div class="step-inner">
            <transition name="icon-fade" mode="out-in">
              <i 
                v-if="index < currentStep" 
                class="fas fa-check text-lg"
                key="check"
              ></i>
              <i
                v-else
                :class="[step.icon, 'text-lg']"
                key="icon"
              ></i>
            </transition>
          </div>
          
          <!-- Pulse effect for current step -->
          <div 
            v-if="index === currentStep"
            class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 animate-ping opacity-20"
          ></div>
        </div>

        <!-- Step Content -->
        <div class="mt-4 text-center max-w-[120px]">
          <h4 
            class="text-sm font-semibold transition-all duration-300 mb-1"
            :class="{
              'text-transparent bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text': index === currentStep,
              'text-gray-900': index < currentStep,
              'text-gray-500': index > currentStep
            }"
          >
            {{ step.label }}
          </h4>
          
          <!-- Status Badge -->
          <div class="status-badge" :class="getStatusClass(index)">
            <i :class="getStatusIcon(index)" class="mr-1"></i>
            <span>{{ getStatusText(index) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Stepper -->
    <div class="md:hidden mb-8">
      <!-- Current Step Display -->
      <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
          <div class="step-circle current">
            <div class="step-inner">
              <i :class="[steps[currentStep].icon, 'text-lg']"></i>
            </div>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-transparent bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text">
              {{ steps[currentStep].label }}
            </h3>
            <p class="text-sm text-gray-600">Bước {{ currentStep + 1 }} / {{ steps.length }}</p>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
          <div 
            class="bg-gradient-to-r from-blue-500 to-purple-600 h-full transition-all duration-700 ease-out rounded-full"
            :style="{ width: `${((currentStep + 1) / steps.length) * 100}%` }"
          ></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-2">
          <span>Tiến độ</span>
          <span>{{ Math.round(((currentStep + 1) / steps.length) * 100) }}%</span>
        </div>
      </div>

      <!-- All Steps Overview -->
      <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-4">
        <h4 class="font-semibold text-gray-800 mb-3 text-center">Tổng quan các bước</h4>
        <div class="space-y-3">
          <div 
            v-for="(step, index) in steps" 
            :key="index"
            class="flex items-center gap-3 p-2 rounded-xl transition-all duration-300"
            :class="{
              'bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200': index === currentStep,
              'bg-green-50': index < currentStep,
              'bg-gray-50': index > currentStep
            }"
          >
            <div class="mobile-step-circle" :class="getMobileStepClass(index)">
              <i 
                v-if="index < currentStep" 
                class="fas fa-check text-xs"
              ></i>
              <i
                v-else
                :class="[step.icon, 'text-xs']"
              ></i>
            </div>
            <div class="flex-1">
              <p 
                class="text-sm font-medium"
                :class="{
                  'text-transparent bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text': index === currentStep,
                  'text-gray-900': index < currentStep,
                  'text-gray-500': index > currentStep
                }"
              >
                {{ step.label }}
              </p>
            </div>
            <div class="mobile-status-badge" :class="getStatusClass(index)">
              <i :class="getStatusIcon(index)" class="text-xs"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
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

const getStatusClass = (index) => {
  if (index < props.currentStep) return 'completed'
  if (index === props.currentStep) return 'current'
  return 'pending'
}

const getStatusIcon = (index) => {
  if (index < props.currentStep) return 'fas fa-check-circle'
  if (index === props.currentStep) return 'fas fa-clock'
  return 'fas fa-circle'
}

const getStatusText = (index) => {
  if (index < props.currentStep) return 'Hoàn thành'
  if (index === props.currentStep) return 'Đang thực hiện'
  return 'Chờ thực hiện'
}

const getMobileStepClass = (index) => {
  if (index < props.currentStep) return 'completed'
  if (index === props.currentStep) return 'current'
  return 'pending'
}
</script>

<style scoped>
/* Desktop Step Circle */
.step-circle {
  @apply relative w-12 h-12 rounded-full transition-all duration-500 ease-out;
}

.step-circle.completed {
  @apply bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg;
}

.step-circle.completed .step-inner {
  @apply text-white;
}

.step-circle.current {
  @apply bg-gradient-to-br from-blue-500 to-purple-600 shadow-xl;
  box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4), 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.step-circle.current .step-inner {
  @apply text-white;
}

.step-circle.pending {
  @apply bg-white border-2 border-gray-300 shadow-md;
}

.step-circle.pending .step-inner {
  @apply text-gray-400;
}

.step-inner {
  @apply w-full h-full rounded-full flex items-center justify-center transition-all duration-300;
}

/* Mobile Step Circle */
.mobile-step-circle {
  @apply w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300;
}

.mobile-step-circle.completed {
  @apply bg-emerald-500 text-white;
}

.mobile-step-circle.current {
  @apply bg-gradient-to-br from-blue-500 to-purple-600 text-white;
}

.mobile-step-circle.pending {
  @apply bg-gray-300 text-gray-500;
}

/* Status Badges */
.status-badge {
  @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-all duration-300;
}

.status-badge.completed {
  @apply bg-emerald-100 text-emerald-700;
}

.status-badge.current {
  @apply bg-gradient-to-r from-blue-100 to-purple-100 text-blue-700;
}

.status-badge.pending {
  @apply bg-gray-100 text-gray-600;
}

.mobile-status-badge {
  @apply w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300;
}

.mobile-status-badge.completed {
  @apply bg-emerald-500 text-white;
}

.mobile-status-badge.current {
  @apply bg-gradient-to-br from-blue-500 to-purple-600 text-white;
}

.mobile-status-badge.pending {
  @apply bg-gray-300 text-gray-500;
}

/* Animations */
.icon-fade-enter-active,
.icon-fade-leave-active {
  transition: all 0.3s ease;
}

.icon-fade-enter-from {
  opacity: 0;
  transform: scale(0.8) rotate(-10deg);
}

.icon-fade-leave-to {
  opacity: 0;
  transform: scale(1.2) rotate(10deg);
}

/* Glass morphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Gradient text */
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}

/* Hover effects */
.step-circle:hover {
  @apply transform scale-105;
}

.step-circle.pending:hover {
  @apply border-gray-400 shadow-lg;
}

.step-circle.pending:hover .step-inner {
  @apply text-gray-500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .step-circle {
    @apply w-10 h-10;
  }
  
  .step-inner i {
    @apply text-base;
  }
}

@media (max-width: 640px) {
  .step-circle {
    @apply w-8 h-8;
  }
  
  .step-inner i {
    @apply text-sm;
  }
}

/* Custom animations */
@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.animate-ping {
  animation: pulse-ring 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Smooth transitions for all elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>
