<template>
  <div class="w-full max-w-5xl mx-auto px-4 mb-6">
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

      <!-- COLOR FILTER -->
      <FilterDropdown
        label="Màu sắc"
        :items="colors"
        @clear="() => {}"
        @apply="() => {}"
        :isOpen="isColorOpen"
        @toggle-open="toggleColorDropdown"
        isColor
      />

      <!-- PRICE FILTER -->
      <FilterDropdown
        label="Giá"
        :items="prices"
        @clear="() => {}"
        @apply="() => {}"
        :isOpen="isPriceOpen"
        @toggle-open="togglePriceDropdown"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import FilterDropdown from '~/components/shared/FilterDropdown.vue'

const brands = ref(['Thể thao', 'Gia dụng', 'Phần mềm', 'Apple', 'Samsung'])
const colors = ref(['Trắng', 'Đen', 'Bạc', 'Xanh', 'Đỏ'])
const prices = ref(['Dưới 1 triệu', '1 - 3 triệu', '3 - 5 triệu', 'Trên 5 triệu'])

const isOpen = ref(false)
const isColorOpen = ref(false)
const isPriceOpen = ref(false)

const selectedBrands = ref([])

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  isColorOpen.value = false
  isPriceOpen.value = false
}
const toggleColorDropdown = () => {
  isColorOpen.value = !isColorOpen.value
  isOpen.value = false
  isPriceOpen.value = false
}
const togglePriceDropdown = () => {
  isPriceOpen.value = !isPriceOpen.value
  isOpen.value = false
  isColorOpen.value = false
}

const toggleBrand = (brand) => {
  if (selectedBrands.value.includes(brand)) {
    selectedBrands.value = selectedBrands.value.filter(b => b !== brand)
  } else {
    selectedBrands.value.push(brand)
  }
}
const clearBrands = () => {
  selectedBrands.value = []
  emitFilters()
}
const emit = defineEmits(['update:filters'])
const emitFilters = () => {
  emit('update:filters', { brand: selectedBrands.value })
  isOpen.value = false
}
const applyFilters = () => {
  emitFilters()
}
</script>
