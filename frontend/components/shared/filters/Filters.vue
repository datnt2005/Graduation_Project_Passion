<template>
  <div class="w-full max-w-5xl px-4 mb-6">
    <div class="flex flex-wrap gap-6 text-sm">
      <!-- BRAND FILTER -->
      <FilterDropdown
        label="Thương hiệu"
        :items="brands"
        :selected="selectedBrands"
        @toggle="toggleBrand"
        @clear="clearBrands"
        @apply="applyFilters"
        :isOpen="isOpen"
        @toggle-open="toggleDropdown"
      />

      <!-- PRICE FILTER -->
      <FilterDropdown
        label="Giá"
        :isOpen="isPriceOpen"
        @toggle-open="togglePriceDropdown"
        @clear="clearPrice"
        @apply="applyFilters"
      >
        <div class="p-3">
          <div class="text-sm text-gray-600">
            <!-- Thanh kéo giá -->
            <div class="relative h-1 bg-gray-200 rounded-full mb-4">
              <!-- Thanh tiến trình giữa hai thumb -->
              <div class="absolute h-1 bg-blue-500 rounded-full"
                   :style="{ left: minPercent + '%', width: (maxPercent - minPercent) + '%' }"></div>
              <!-- Input cho giá tối thiểu -->
              <input type="range" v-model.number="priceRange[0]" :min="priceMin" :max="priceMax" :step="10000"
                     @input="onPriceInput(0, $event)"
                     class="absolute w-full h-1 bg-transparent appearance-none cursor-pointer pointer-events-auto"
                     style="z-index: 2;" />
              <!-- Input cho giá tối đa -->
              <input type="range" v-model.number="priceRange[1]" :min="priceMin" :max="priceMax" :step="10000"
                     @input="onPriceInput(1, $event)"
                     class="absolute w-full h-1 bg-transparent appearance-none cursor-pointer pointer-events-auto"
                     style="z-index: 1;" />
            </div>
            <!-- Hiển thị giá -->
            <div class="flex justify-between text-xs text-gray-500 mb-2">
              <span>{{ priceRange[0].toLocaleString('vi-VN') }} ₫</span>
              <span>{{ priceRange[1].toLocaleString('vi-VN') }} ₫</span>
            </div>
            
          </div>
        </div>
      </FilterDropdown>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import FilterDropdown from './FilterDropdown.vue';

const props = defineProps({
  brands: Array,
  priceMin: Number,
  priceMax: Number,
  priceRange: Array
});

const emit = defineEmits(['update:filters']);

const isOpen = ref(false);
const isPriceOpen = ref(false);
const selectedBrands = ref([]);
const priceRange = ref([...props.priceRange]);

watch(() => props.priceRange, (val) => {
  priceRange.value = [...val];
});

// Tính phần trăm vị trí của thumb
const minPercent = computed(() => {
  return ((priceRange.value[0] - props.priceMin) / (props.priceMax - props.priceMin)) * 100;
});
const maxPercent = computed(() => {
  return ((priceRange.value[1] - props.priceMin) / (props.priceMax - props.priceMin)) * 100;
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  isPriceOpen.value = false;
};
const togglePriceDropdown = () => {
  isPriceOpen.value = !isPriceOpen.value;
  isOpen.value = false;
};

const toggleBrand = (brand) => {
  if (selectedBrands.value.includes(brand)) {
    selectedBrands.value = selectedBrands.value.filter(b => b !== brand);
  } else {
    selectedBrands.value.push(brand);
  }
};

const clearBrands = () => {
  selectedBrands.value = [];
  emitFilters();
};

const clearPrice = () => {
  priceRange.value = [props.priceMin, props.priceMax];
  emitFilters();
};

const emitFilters = () => {
  emit('update:filters', { brand: selectedBrands.value, priceRange: priceRange.value });
  isOpen.value = false;
  isPriceOpen.value = false;
};

const applyFilters = () => {
  emitFilters();
};

const onPriceInput = (index, event) => {
  let val = Number(event.target.value);
  if (index === 0) {
    if (val > priceRange.value[1] - 10000) val = priceRange.value[1] - 10000;
    priceRange.value[0] = Math.max(props.priceMin, val);
  } else {
    if (val < priceRange.value[0] + 10000) val = priceRange.value[0] + 10000;
    priceRange.value[1] = Math.min(props.priceMax, val);
  }
};
</script>

<style scoped>
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  pointer-events: none;
}
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  background: #1BA0E2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  pointer-events: auto;
  position: relative;
  z-index: 3;
}
input[type="range"]::-moz-range-thumb {
  width: 16px;
  height: 16px;
  background: #1BA0E2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  pointer-events: auto;
}
input[type="range"]::-ms-thumb {
  width: 16px;
  height: 16px;
  background: #1BA0E2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  pointer-events: auto;
}
input[type="range"]:focus {
  outline: none;
}
</style>