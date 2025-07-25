<template>
  <div class="w-full mx-auto bg-gray-50">
    <component :is="currentStepComponent" @next="goToNextStep" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Step1 from './RegisterSellerSteps/Step1.vue'
import Step2 from './RegisterSellerSteps/Step2.vue'
import Step3 from './RegisterSellerSteps/Step3.vue'
import Step4 from './RegisterSellerSteps/Step4.vue'
import Submit from './RegisterSellerSteps/Submit.vue'

const step = ref(1)

const components = {
  1: Step1,
  2: Step2,
  3: Step3,
  4: Step4,
  5: Submit,
}

const currentStepComponent = computed(() => components[step.value])

const goToNextStep = () => {
  if (step.value < 5) step.value++
}
</script>