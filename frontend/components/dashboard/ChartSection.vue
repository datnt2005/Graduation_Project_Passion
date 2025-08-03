<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
      <div>
        <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
          <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
            <span class="text-white text-xs">📈</span>
          </div>
          Biểu Đồ Doanh Thu Chiết Khấu
        </h2>
        <p class="text-gray-600 text-sm">Theo dõi doanh thu chiết khấu theo thời gian</p>
      </div>
      <div class="mt-4 lg:mt-0 flex flex-wrap items-center gap-3">
        <SelectControl
          :model-value="chartType"
          :options="chartTypeOptions"
          label="Lọc theo"
          class="w-32"
          @update:model-value="$emit('type-change', $event)"
        />
        <SelectControl
          :model-value="chartMode"
          :options="chartModeOptions"
          label="Kiểu biểu đồ"
          class="w-36"
          @update:model-value="$emit('mode-change', $event)"
        />
      </div>
    </div>

    <div class="h-[400px] bg-gray-50 rounded-lg p-4 border border-gray-100">
      <LoadingSpinner v-if="loading" message="Đang tải biểu đồ..." />
      <ErrorMessage 
        v-else-if="error" 
        :message="error"
        @retry="$emit('update-chart', chartType)"
      />
      <component v-else :is="chartComponent" :data="data" :options="options" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar, Line, Pie } from 'vue-chartjs'
import SelectControl from '@/components/ui/SelectControl.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'

const props = defineProps({
  chartType: {
    type: String,
    default: 'month'
  },
  chartMode: {
    type: String,
    default: 'bar'
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    required: true
  },
  selectedSeller: {
    type: [String, Number],
    default: ''
  }
})

defineEmits(['update-chart', 'type-change', 'mode-change'])

const chartTypeOptions = [
  { value: 'day', label: 'Ngày' },
  { value: 'month', label: 'Tháng' },
  { value: 'year', label: 'Năm' }
]

const chartModeOptions = [
  { value: 'bar', label: 'Cột' },
  { value: 'line', label: 'Đường' },
  { value: 'pie', label: 'Tròn' }
]

const chartComponent = computed(() => {
  if (props.chartMode === 'bar') return Bar
  if (props.chartMode === 'line') return Line
  if (props.chartMode === 'pie') return Pie
  return Bar
})
</script> 