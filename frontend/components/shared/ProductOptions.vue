<template>
  <div>
    <!-- Màu sắc -->
    <div class="flex items-center space-x-2 text-xs text-gray-600 mb-4">
      <span class="w-20 shrink-0">Màu Sắc</span>
      <div class="flex space-x-2">
        <button
          v-for="(color, index) in colors"
          :key="index"
          :class="[
            'flex items-center space-x-1 border rounded px-3 py-1 text-xs text-gray-700 cursor-pointer',
            selectedColor === color.label ? 'border-blue-500' : 'border-gray-300'
          ]"
          @click="selectColor(color.label)"
        >
          <img
            :alt="color.alt"
            class="w-6 h-6 object-cover rounded"
            :src="color.img"
            width="24"
            height="24"
          />
          <span>{{ color.label }}</span>
        </button>
      </div>
    </div>

    <!-- Size -->
    <div class="flex items-center space-x-2 text-xs text-gray-600 mb-4">
      <span class="w-20 shrink-0">Size</span>
      <div class="flex space-x-2">
        <button
          v-for="(size, index) in sizes"
          :key="index"
          :class="[
            'border rounded w-12 h-10 text-gray-700 text-sm cursor-pointer',
            selectedSize === size ? 'border-blue-500' : 'border-gray-300'
          ]"
          @click="selectSize(size)"
        >
          {{ size }}
        </button>
      </div>
    </div>

    <!-- Số lượng -->
    <div class="flex items-center space-x-2 text-xs text-gray-600">
      <span class="w-20 shrink-0">Số Lượng</span>
      <div class="flex items-center border border-gray-300 rounded w-32 h-10 select-none">
        <button
          class="w-10 h-full text-gray-400"
          :disabled="quantity <= 1"
          @click="quantity--"
        >−</button>
        <input
          class="w-full h-full text-center text-gray-600 bg-white border-x border-gray-300 focus:outline-none"
          type="text"
          :value="quantity"
          readonly
        />
        <button
          class="w-10 h-full text-gray-400"
          @click="quantity++"
        >+</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps(['options'])
const selected = defineModel('selected')
import { ref } from 'vue'

const colors = [
  { label: 'Đen', img: 'https://storage.googleapis.com/a1aa/image/842c6d05-adf7-45f5-4815-499441768067.jpg', alt: 'Black plaid shirt color option' },
  { label: 'Vàng', img: 'https://storage.googleapis.com/a1aa/image/81c6f632-fdda-4675-bb00-c0cd7b2613e3.jpg', alt: 'Brown plaid shirt color option' },
  { label: 'Xanh', img: 'https://storage.googleapis.com/a1aa/image/14f20395-c07b-4253-0383-c06582ea1ace.jpg', alt: 'Blue plaid shirt color option' },
  { label: 'Móc khóa', img: 'https://storage.googleapis.com/a1aa/image/e2d0862e-3ef8-4ac9-c496-dd6f422b3f08.jpg', alt: 'Keychain color option' }
]

const sizes = ['S', 'M', 'L', 'XL', 'XXL']

const selectedColor = ref('')
const selectedSize = ref('')
const quantity = ref(1)

// Hàm xử lý
const selectColor = (label) => {
  selectedColor.value = label
}
const selectSize = (size) => {
  selectedSize.value = size
}

// Dữ liệu được chọn gộp lại thành object:
const selectedOptions = computed(() => ({
  color: selectedColor.value,
  size: selectedSize.value,
  quantity: quantity.value
}))
</script>