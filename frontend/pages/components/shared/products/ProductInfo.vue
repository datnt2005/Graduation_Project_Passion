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
                <img :src="seller.avatar ? `${mediaBase}${seller.avatar}` : `${mediaBase}/default-avatar.png`"
                    :alt="seller.store_name + ' avatar'" class="w-16 h-16 rounded-full border" loading="lazy" />
                <div>
                    <h2 class="font-semibold text-lg">{{ seller.store_name }}</h2>
                    <p class="text-sm text-gray-500">{{ seller.last_active }}</p>
                    <div class="flex space-x-2 mt-2">
                        <button class="bg-[#1BA0E2] text-white px-3 py-1 rounded flex items-center text-sm">
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
                <span class="text-[25px]">{{
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

           <!-- Shipping -->
        <div class="flex items-start space-x-2 text-sm text-gray-600">
          <span class="w-20 shrink-0">
            Vận Chuyển
          </span>
          <div class="flex-1">
            <div class="flex items-center space-x-1 text-[#009688] font-[8px]">
              <i class="fas fa-truck">
              </i>
              <span>
                Nhận từ 13 Th06 - 14 Th06, phí giao ₫0
              </span>
              <i class="fas fa-chevron-right text-xs">
              </i>
            </div>
            <div class="text-[10px] text-gray-400 mt-0.5 select-none">
              Tặng Voucher ₫15.000 nếu đơn giao sau thời gian trên.
            </div>
          </div>
        </div>

         <div class="flex items-start space-x-2 text-sm text-gray-600">
          <span class="w-20 shrink-0">
            An Tâm Mua Sắm Cùng Passion
          </span>
          <div class="flex-1 flex items-center space-x-2 font-semibold text-[#222222] cursor-pointer select-none">
            <i class="fas fa-shield-alt text-[#e74c3c]">
            </i>
            <span>
              Trả hàng miễn phí 15 ngày · Bảo hiểm Thời trang
            </span>
            <i class="fas fa-chevron-down text-xs">
            </i>
          </div>
        </div>
        <!-- Product Options -->
    <div
        :class="[
            'p-4 rounded-md border space-y-4',
            validationMessage ? 'bg-[#FFF5F5] border-red-300' : 'bg-white border-gray-200'
        ]"
        >
        <!-- Vòng lặp thuộc tính (Màu, Kích thước,...) -->
        <div
            v-for="attr in variantAttributes"
            :key="attr.name"
            class="flex items-start text-sm font-medium gap-3"
        >
            <label class="w-24 pt-1 text-gray-700">{{ attr.name }}</label>
            <div class="flex flex-wrap gap-2">
            <button
                v-for="option in attr.options"
                :key="`${attr.name}-${option.value}`"
                @click="$emit('select-option', attr.name, option.value)"
                :class="[
                'px-3 py-1 text-sm rounded-md border transition duration-150 select-none',
                selectedOptions[attr.name] === option.value
                    ? 'bg-blue-50 border-blue-500 text-blue-600 ring-1 ring-blue-300'
                    : 'border-gray-300 text-gray-800 hover:border-blue-400',
                !isOptionAvailable(attr.name, option.value) ? 'opacity-50 cursor-not-allowed bg-gray-100' : ''
                ]"
            >
                <img
                v-if="option.thumbnail"
                :src="`${mediaBase}${option.thumbnail}`"
                class="w-5 h-5 mr-1 object-cover rounded"
                :alt="option.alt || `${attr.name} ${option.value}`"
                />
                <span>{{ option.label }}</span>
            </button>
            </div>
        </div>

        <!-- Số lượng -->
        <div class="flex items-center gap-3 text-sm font-medium text-gray-700">
            <label class="w-24 shrink-0">Số Lượng</label>
            <div class="flex items-center border border-gray-300 rounded-md w-32 h-9 bg-white overflow-hidden">
            <button
                class="w-9 h-9 text-gray-600 hover:bg-gray-100 disabled:text-gray-300"
                :disabled="quantity <= 1"
                @click="$emit('decrease-quantity')"
            >
                <i class="fas fa-minus text-xs"></i>
            </button>
            <input
                class="w-full h-full text-center text-sm text-gray-800 bg-white focus:outline-none border-x border-gray-200"
                type="number"
                :value="quantity"
                min="1"
                :max="selectedVariant?.stock || 0"
                @input="handleQuantityInput($event.target.value)"
                @keydown="blockInvalidKeys"
            />
            <button
                class="w-9 h-9 text-gray-600 hover:bg-gray-100 disabled:text-gray-300"
                :disabled="quantity >= (selectedVariant?.stock || 0)"
                @click="$emit('increase-quantity')"
            >
                <i class="fas fa-plus text-xs"></i>
            </button>
            </div>
            <span class="text-xs text-gray-500">
            {{ selectedVariant.stock }} sản phẩm có sẵn
            </span>
        </div>

        <!-- Lỗi -->
        <div v-if="validationMessage" class="text-red-500 text-sm ml-[96px]">
            {{ validationMessage }}
        </div>
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
import { computed } from 'vue';

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
console.log(props.validationMessage);

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
    'clear-validation'
]);

function isOptionAvailable(attrName, value) {
    const otherSelections = { ...props.selectedOptions };
    delete otherSelections[attrName];

    return props.variants.some(variant =>
        variant.attributes.some(attr => attr.attribute_name === attrName && attr.value === value) &&
        variant.stock > 0 &&
        Object.entries(otherSelections).every(([key, val]) =>
            !val || variant.attributes.some(attr => attr.attribute_name === key && attr.value === val)
        )
    );
}

function formatPrice(price) {
    if (!price || price === 'null' || price === null || price === undefined) {
        return '0';
    }
    const parsedPrice = parseFloat(price);
    if (isNaN(parsedPrice)) {
        return '0';
    }
    return parsedPrice.toLocaleString('vi-VN', { style: 'decimal' });
}

function handleQuantityInput(value) {
    const maxStock = props.selectedVariant?.stock || 0;

    let newQuantity = parseInt(value, 10);

    if (isNaN(newQuantity) || newQuantity < 1) {
        newQuantity = 1;
    } else if (newQuantity > maxStock) {
        newQuantity = maxStock;
    }

    emit('update:quantity', newQuantity);
    emit('validate-selection');
}

function handleAddToCart() {
    emit('validate-selection');
    if (!props.isVariantFullySelected) return;
    emit('add-to-cart');
}

function handleBuyNow() {
    emit('validate-selection');
    if (!props.isVariantFullySelected) return;
    emit('buy-now');
}

function blockInvalidKeys(event) {
  const invalidKeys = ['-', '+', 'e', 'E', '.', ','];
  if (invalidKeys.includes(event.key)) {
    event.preventDefault();
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