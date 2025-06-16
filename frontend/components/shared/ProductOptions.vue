<template>
  <div class="flex flex-col gap-4">
    <!-- Attribute Sections -->
    <div
      v-for="(values, attrName) in options"
      :key="attrName"
      class="flex items-center gap-4 text-sm text-gray-600"
    >
      <span class="w-24 shrink-0 font-medium capitalize">{{ attrName }}</span>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="value in values"
          :key="value.id"
          :class="[
            'flex items-center gap-2 border rounded px-3 py-1.5 text-sm text-gray-700 cursor-pointer transition-colors',
            selectedOptions[attrName] === value.id
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-300 hover:border-gray-400',
          ]"
          @click="selectOption(attrName, value.id)"
          :aria-label="`Chọn ${attrName} ${value.name}`"
          type="button"
        >
          <img
            v-if="attrName.toLowerCase() === 'color' && value.image"
            :src="value.image"
            :alt="`Màu ${value.name}`"
            class="w-6 h-6 object-cover rounded"
            width="24"
            height="24"
            loading="lazy"
          />
          <span>{{ value.name }}</span>
        </button>
      </div>
    </div>

    <!-- Quantity Selector -->
    <div class="flex items-center gap-4 text-sm text-gray-600">
      <span class="w-24 shrink-0 font-medium">Số Lượng</span>
      <div class="flex items-center border border-gray-300 rounded w-32 h-10 select-none">
        <button
          class="w-10 h-full text-gray-600 hover:bg-gray-100 transition-colors"
          :disabled="quantity <= 1"
          @click="decreaseQuantity"
          aria-label="Giảm số lượng"
          type="button"
        >−</button>

        <input
          class="w-full h-full text-center text-gray-600 bg-white border-x border-gray-300 focus:outline-none"
          type="text"
          :value="quantity"
          readonly
          aria-label="Số lượng sản phẩm"
        />

        <button
          class="w-10 h-full text-gray-600 hover:bg-gray-100 transition-colors"
          :disabled="quantity >= maxQuantity"
          @click="increaseQuantity"
          aria-label="Tăng số lượng"
          type="button"
        >+</button>
      </div>
      <span v-if="maxQuantity !== Infinity" class="text-xs text-gray-500">
        (Còn {{ maxQuantity }} sản phẩm)
      </span>
    </div>

    <!-- No Options Warning -->
    <div v-if="!Object.keys(options).length" class="text-sm text-red-600">
      Không có tùy chọn nào cho sản phẩm này.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// Props
const props = defineProps({
  options: {
    type: Object,
    default: () => ({}),
  },
  maxQuantity: {
    type: Number,
    default: Infinity,
  }
})

// Two-way binding: v-model:selected
const selected = defineModel('selected', { type: Object, default: () => ({}) })

// Internal quantity state
const quantity = ref(selected.value?.quantity || 1)

// Computed selectedOptions (merge selected & quantity)
const selectedOptions = computed({
  get() {
    const result = { ...selected.value }
    Object.keys(props.options).forEach(attr => {
      if (!(attr in result)) result[attr] = null
    })
    result.quantity = quantity.value
    return result
  },
  set(newVal) {
    quantity.value = newVal.quantity || 1
    selected.value = { ...newVal }
  },
})

// Watch for changes to keep selected synced
watch(
  () => selected.value,
  (val) => {
    quantity.value = val.quantity || 1
  },
  { deep: true }
)

// Methods
function selectOption(attrName, valueId) {
  selectedOptions.value[attrName] = valueId
  selected.value[attrName] = valueId
}

function increaseQuantity() {
  if (quantity.value < props.maxQuantity) quantity.value++
  selected.value.quantity = quantity.value
}

function decreaseQuantity() {
  if (quantity.value > 1) quantity.value--
  selected.value.quantity = quantity.value
}
</script>