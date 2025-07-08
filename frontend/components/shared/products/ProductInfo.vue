<template>
    <div class="flex-1 space-y-4">
        <!-- Title -->
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-normal leading-tight">{{ product.name }}</h1>
            <button @click="$emit('toggle-favorite')" class="text-red-500 text-xl focus:outline-none"
                aria-label="Toggle favorite">
                <i :class="[isFavorite ? 'fas' : 'far', 'fa-heart']"></i>
            </button>
        </div>
        <!-- Rating and sold -->
        <div class="flex items-center text-xs text-[#222222] space-x-2">
            <span>{{ product.rating }}</span>
            <div class="flex space-x-0.5 text-[#fdd835]">
                <i v-for="n in product.stars" :key="n" class="fas fa-star"></i>
                <i v-for="n in (5 - product.stars)" :key="'empty-' + n" class="far fa-star"></i>
            </div>
            <span>(Đã bán: {{ product.sold }})</span>
        </div>
        <!-- Seller Info -->
        <div class="flex justify-between items-center bg-white border rounded-sm p-4">
            <div class="flex items-center space-x-4">
                <img v-if="seller.avatar"
                    :src="seller.avatar.startsWith('http') ? seller.avatar : `${mediaBase}${seller.avatar}`"
                    alt="Avatar" class="w-14 h-14  rounded-full object-cover" />
                <span v-else>📘</span>
                <div>
                    <h2 class="font-semibold text-lg">{{ seller.store_name }}</h2>
                    <!-- <p class="text-sm text-gray-500">{{ seller.address }}</p> -->
                    <div class="flex space-x-2 mt-2">
                    <button
                        :disabled="loading || !seller?.id"
                        @click="emit('chat-with-shop')"
                        class="bg-[#1BA0E2] text-white border px-3 py-1 rounded text-sm flex items-center disabled:opacity-50"
                        aria-label="chat shop"
                    >
                        <i class="fas fa-comment-alt mr-1"></i> Chat Ngay
                    </button>

                        <button @click="$emit('view-shop')" class="border px-3 py-1 rounded text-sm flex items-center"
                            aria-label="View shop">
                            <i class="fas fa-store mr-1"></i> Xem Shop
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-right text-sm">
                <p class="text-gray-500">Đánh Giá</p>
                <p class="text-red-500 font-semibold">{{ seller.rating }}</p>
                <p class="text-gray-500 mt-2">Sản Phẩm</p>
                <p class="text-red-500 font-semibold">{{ seller.products_count }}</p>
            </div>
        </div>
        <!-- Price and discount -->
        <div class="bg-[#fef0ef] p-4 rounded-sm flex items-center space-x-4">
            <div class="text-[#D82E44] font-bold flex items-center space-x-1">
                <span class="text-3xl">{{
                    parseFloat(selectedVariant.sale_price) < parseFloat(selectedVariant.original_price) ?
                        formatPrice(selectedVariant.sale_price) : formatPrice(selectedVariant.original_price ||
                            selectedVariant.price) }}</span>
                        <sup class="text-sm">₫</sup>
            </div>
            <div v-if="parseFloat(selectedVariant.sale_price) < parseFloat(selectedVariant.original_price)"
                class="text-gray-500 line-through text-xl">
                {{ formatPrice(selectedVariant.original_price || selectedVariant.price) }} <sup class="text-sm">₫</sup>
            </div>
            <div v-if="selectedVariant.sale_price && selectedVariant.discount_percent > 0"
                class="text-[#f15a24] text-xs font-semibold mt-1 bg-blue-50 rounded-full px-2 py-0.5">
                -{{ selectedVariant.discount_percent }}%
            </div>
        </div>
        <!-- Product Options -->
        <div>
            <!-- All Variant Attributes -->
            <!-- Wrapper đổi màu nền khi có lỗi -->
            <div :class="['p-4 rounded-sm', validationMessage ? 'bg-[#FFF5F5]' : 'bg-white']">
                <!-- Các thuộc tính: in ấn, size,... -->
                <div v-for="attr in variantAttributes" :key="attr.name"
                    class="flex items-center space-x-2 text-xs font-medium mb-4 text-sm">
                    <span class="w-20 shrink-0">{{ attr.name }}</span>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="option in attr.options" :key="`${attr.name}-${option.value}`" :class="[
                            'flex items-center space-x-1 border rounded-sm px-4 py-1.5 text-sm text-gray-900 cursor-pointer transition-colors duration-150',
                            selectedOptions[attr.name] === option.value ? 'border-blue-500 bg-blue-50 ring-1' : 'border-gray-300',
                            !isOptionAvailable(attr.name, option.value) ? 'opacity-50 cursor-pointer bg-gray-200' : 'hover:bg-gray-50'
                        ]" @click="$emit('select-option', attr.name, option.value)"
                            :aria-label="`Select ${attr.name} ${option.value}`">
                            <img v-if="option.thumbnail" :src="`${mediaBase}${option.thumbnail}`"
                                :alt="option.alt || `${attr.name} ${option.value}`"
                                class="w-5 h-5 mr-1.5 object-cover rounded" loading="lazy" />
                            <span>{{ option.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Số Lượng -->
                <div class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-1">
                    <span class="w-20 shrink-0">Số Lượng</span>
                    <div class="flex items-center border border-gray-300 rounded-md w-32 h-10 bg-white">
                        <button class="w-10 h-10 text-gray-600 hover:bg-gray-100 rounded-l-md" :disabled="quantity <= 1"
                            @click="$emit('decrease-quantity')" aria-label="Decrease quantity">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input v-model.number="localQuantity" @input="handleQuantityInput($event.target.value)"
                            @keydown="blockInvalidKeys" type="number" step="1"
                            class="input-no-spinner w-full h-10 text-center text-sm text-gray-900 bg-transparent border-x border-gray-200 focus:outline-none"
                            :min="1" :max="selectedVariant?.stock || 0" aria-label="Quantity" />
                        <button class="w-10 h-10 text-gray-600 hover:bg-gray-100 rounded-r-md"
                            :disabled="quantity >= (selectedVariant?.stock || 0)" @click="$emit('increase-quantity')"
                            aria-label="Increase quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <span v-if="selectedVariant?.stock >= 0" class="text-gray-500 text-sm ml-2">
                        {{ selectedVariant.stock }} sản phẩm có sẵn
                    </span>
                </div>

                <!-- Thông báo lỗi -->
                <div v-if="validationMessage" class="text-red-500 text-sm mt-2 ml-[80px]">
                    {{ validationMessage }}
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4 mt-4">
                <button @click="handleAddToCart" :disabled="!selectedVariant.stock"
                    class="flex items-center justify-center border border-[#0d5cb6] text-[#0d5cb6] bg-white rounded-sm w-48 h-12 text-sm font-semibold hover:bg-[#e6f0fb] transition-colors duration-200"
                    aria-label="Add to cart">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Thêm Vào Giỏ Hàng
                </button>
                <button @click="handleBuyNow" :disabled="!selectedVariant.stock"
                    class="bg-[#0d5cb6] text-white rounded-sm w-48 h-12 text-sm font-semibold hover:bg-[#084d9d] transition-colors duration-200"
                    aria-label="Buy now">
                    Mua Ngay
                </button>
            </div>
        </div>
    </div>
</template>
<script setup>
import { computed } from 'vue'
import { useChatStore } from '~/stores/chat'
const chatStore = useChatStore()

const handleChat = () => {
  chatStore.openChat() // Mở modal
}

// --- Props ---
const props = defineProps({
    product: { type: Object, required: true },
    seller: { type: Object, required: true },
    mediaBase: { type: String, required: true },
    selectedVariant: { type: Object, required: true },
    variantAttributes: { type: Array, required: true },
    selectedOptions: { type: Object, required: true },
    quantity: { type: Number, required: true },
    isFavorite: { type: Boolean, required: true },
    isVariantFullySelected: { type: Boolean, required: true },
    variants: { type: Array, required: true },
    validationMessage: { type: String, default: '' }    
});

const emit = defineEmits([
    'toggle-favorite',
    'view-shop',
    'select-option',
    'increase-quantity',
    'decrease-quantity',
    'validate-selection',
    'add-to-cart',
    'buy-now',
    'update:quantity',
    'update:validationMessage',
    'update:selectedOptions',
    'clear-validation',
    'chat-with-shop'
]);

const localQuantity = ref(props.quantity);
function isOptionAvailable(attrName, value) {
  const otherSelections = { ...props.selectedOptions }
  delete otherSelections[attrName]

  return props.variants.some(variant =>
    variant.attributes.some(attr => attr.attribute_name === attrName && attr.value === value) &&
    variant.stock > 0 &&
    Object.entries(otherSelections).every(([key, val]) =>
      !val || variant.attributes.some(attr => attr.attribute_name === key && attr.value === val)
    )
  )
}

function formatPrice(price) {
  if (!price || price === 'null' || price === null || price === undefined) {
    return '0'
  }
  const parsedPrice = parseFloat(price)
  return isNaN(parsedPrice)
    ? '0'
    : parsedPrice.toLocaleString('vi-VN', { style: 'decimal' })
}

watch(
    () => [props.selectedVariant, props.quantity],
    () => {
        localQuantity.value = props.quantity;
    },
    { immediate: true }
);

function handleQuantityInput(value) {
    const maxStock = props.selectedVariant?.stock || 0;
    let newQuantity = parseInt(value, 10);

    if (isNaN(newQuantity) || newQuantity < 1) {
        newQuantity = 1;
        emit('update:validationMessage', 'Số lượng phải từ 1 trở lên.');
    } else if (newQuantity > maxStock) {
        newQuantity = maxStock;
        emit('update:validationMessage', `Chỉ còn lại ${maxStock} sản phẩm.`);
    } else {
        emit('update:validationMessage', '');
    }

    localQuantity.value = newQuantity;
    emit('update:quantity', newQuantity);
    emit('validate-selection');
}

function handleAddToCart() {
    const maxStock = props.selectedVariant?.stock || 0;

    if (!props.isVariantFullySelected){
        emit('update:validationMessage', 'Vui lòng chọn Phân loại hàng');
        return;
    };
    if (localQuantity.value < 1) {
        emit('update:validationMessage', 'Số lượng phải từ 1 trở lên.');
        return;
    }
    if (localQuantity.value > maxStock) {
        emit('update:validationMessage', `Chỉ còn lại ${maxStock} sản phẩm.`);
        return;
    }

    emit('add-to-cart');
}

function handleBuyNow() {
    const maxStock = props.selectedVariant?.stock || 0;

    if (!props.isVariantFullySelected) {
        emit('update:validationMessage', 'Vui lòng chọn Phân loại hàng');
        return;
    }
    if (localQuantity.value < 1) {
        emit('update:validationMessage', 'Số lượng phải từ 1 trở lên.');
        return;
    }
    if (localQuantity.value > maxStock) {
        emit('update:validationMessage', `Chỉ còn lại ${maxStock} sản phẩm.`);
        return;
    }

    emit('buy-now');
}

function blockInvalidKeys(event) {
    const invalidKeys = ['-', '+', 'e', 'E', '.', ','];
    if (invalidKeys.includes(event.key)) {
        event.preventDefault();
    }
    // Prevent pasting invalid values
    if (event.type === 'paste') {
        const pastedText = event.clipboardData.getData('text');
        if (!/^\d+$/.test(pastedText)) {
            event.preventDefault();
        }
    }
}

</script>

<style scoped>
.input-no-spinner::-webkit-outer-spin-button,
.input-no-spinner::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.input-no-spinner {
    -moz-appearance: textfield;
    /* Firefox */
}
</style>