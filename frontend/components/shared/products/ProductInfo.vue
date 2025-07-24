<template>
  <div class="flex-1 space-y-4">
    <!-- Title -->
    <div class="flex items-center justify-between">
      <h1 class="text-lg font-normal leading-tight">{{ product.name }}</h1>
      <button @click="toggleFavorite" class="text-red-500 text-xl focus:outline-none">
        <i :class="[isFavorite ? 'fas' : 'far', 'fa-heart']"></i>
      </button>
    </div>
    <!-- Rating and sold -->
    <div class="flex items-center text-xs text-[#222222] space-x-2">
      <span>{{ product.rating }}</span>
      <div class="flex space-x-0.5 text-[#fdd835]">
        <i v-for="n in product.stars" :key="n" class="fas fa-star"></i>
        <i v-for="n in 5 - product.stars" :key="'empty-' + n" class="far fa-star"></i>
      </div>
      <span>(Đã bán: {{ product.sold }})</span>
      <button @click="openReportModal" class="text-gray-400 ml-2 hover:text-red-500 text-xs font-semibold" aria-label="Report product">
        Tố cáo
      </button>
    </div>
    <!-- Report Modal -->
    <div v-if="isReportModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-semibold mb-4">Tố cáo sản phẩm</h2>
        <div class="space-y-3">
          <div v-for="reason in reportReasons" :key="reason.value" class="flex items-center">
            <input
              type="radio"
              :id="reason.value"
              :value="reason.value"
              v-model="selectedReportReason"
              class="mr-2"
              :aria-label="reason.label"
            />
            <label :for="reason.value" class="text-sm text-gray-700">{{ reason.label }}</label>
          </div>
          <!-- Custom reason input for "Lý do khác" -->
          <div v-if="selectedReportReason === 'other'" class="mt-3">
            <textarea
              v-model="customReportReason"
              placeholder="Nhập lý do khác... (vui lòng nhập từ 10 đến 50 ký tự)"
              class="w-full p-2 border border-gray-300 rounded-sm text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500"
              rows="4"
              aria-label="Custom report reason"
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end space-x-4 mt-6">
          <button
            @click="closeReportModal"
            class="border border-gray-300 text-gray-700 rounded-sm px-4 py-2 text-sm hover:bg-gray-100"
            aria-label="Cancel report"
          >
            Hủy
          </button>
          <button
            @click="submitReport"
            :disabled="isSubmitDisabled"
            class="bg-red-500 text-white rounded-sm px-4 py-2 text-sm hover:bg-red-600 disabled:opacity-50"
            aria-label="Submit report"
          >
            Gửi tố cáo
          </button>
        </div>
      </div>
    </div>
    <!-- Seller Info -->
    <div class="flex justify-between items-center bg-white border rounded-sm p-4">
      <div class="flex items-center space-x-4">
        <img
          v-if="seller.avatar"
          :src="seller.avatar.startsWith('http') ? seller.avatar : `${mediaBase}${seller.avatar}`"
          alt="Avatar"
          class="w-14 h-14 rounded-full object-cover"
        />
        <span v-else>📘</span>
        <div>
          <h2 class="font-semibold text-lg">{{ seller.store_name }}</h2>
          <div class="flex space-x-2 mt-2">
            <button
              :disabled="loading || !seller?.id"
              @click="emit('chat-with-shop')"
              class="bg-[#1BA0E2] text-white border px-3 py-1 rounded text-sm flex items-center disabled:opacity-50"
              aria-label="chat shop"
            >
              <i class="fas fa-comment-alt mr-1"></i> Chat Ngay
            </button>
            <button
              @click="$emit('view-shop')"
              class="border px-3 py-1 rounded text-sm flex items-center"
              aria-label="View shop"
            >
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
          parseFloat(selectedVariant.sale_price) < parseFloat(selectedVariant.original_price)
            ? formatPrice(selectedVariant.sale_price)
            : formatPrice(selectedVariant.original_price || selectedVariant.price)
        }}</span>
        <sup class="text-sm">₫</sup>
      </div>
      <div
        v-if="parseFloat(selectedVariant.sale_price) < parseFloat(selectedVariant.original_price)"
        class="text-gray-500 line-through text-xl"
      >
        {{ formatPrice(selectedVariant.original_price || selectedVariant.price) }}
        <sup class="text-sm">₫</sup>
      </div>
      <div
        v-if="selectedVariant.sale_price && selectedVariant.discount_percent > 0"
        class="text-[#f15a24] text-xs font-semibold mt-1 bg-blue-50 rounded-full px-2 py-0.5"
      >
        -{{ selectedVariant.discount_percent }}%
      </div>
    </div>
    <!-- Product Options -->
    <div>
      <div :class="['p-4 rounded-sm', validationMessage ? 'bg-[#FFF5F5]' : 'bg-white']">
        <div
          v-for="attr in variantAttributes"
          :key="attr.name"
          class="flex items-center space-x-2 text-xs font-medium mb-4 text-sm"
        >
          <span class="w-20 shrink-0">{{ attr.name }}</span>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in attr.options"
              :key="`${attr.name}-${option.value}`"
              :class="[
                'flex items-center space-x-1 border rounded-sm px-4 py-1.5 text-sm text-gray-900 cursor-pointer transition-colors duration-150',
                selectedOptions[attr.name] === option.value
                  ? 'border-blue-500 bg-blue-50 ring-1'
                  : 'border-gray-300',
                !isOptionAvailable(attr.name, option.value)
                  ? 'opacity-50 cursor-pointer bg-gray-200'
                  : 'hover:bg-gray-50',
              ]"
              @click="handleSelectOption(attr.name, option.value)"
              :aria-label="`Select ${attr.name} ${option.value}`"
            >
              <img
                v-if="option.thumbnail"
                :src="`${mediaBase}${option.thumbnail}`"
                :alt="option.alt || `${attr.name} ${option.value}`"
                class="w-5 h-5 mr-1.5 object-cover rounded"
                loading="lazy"
              />
              <span>{{ option.label }}</span>
            </button>
          </div>
        </div>
        <div class="flex items-center space-x-2 text-sm font-medium text-gray-700 mb-1">
          <span class="w-20 shrink-0">Số Lượng</span>
          <div class="flex items-center border border-gray-300 rounded-md w-32 h-10 bg-white">
            <button
              class="w-10 h-10 text-gray-600 hover:bg-gray-100 rounded-l-md"
              :disabled="quantity <= 1"
              @click="$emit('decrease-quantity')"
              aria-label="Decrease quantity"
            >
              <i class="fas fa-minus"></i>
            </button>
            <input
              v-model.number="localQuantity"
              @input="handleQuantityInput($event.target.value)"
              @keydown="blockInvalidKeys"
              type="number"
              step="1"
              class="input-no-spinner w-full h-10 text-center text-sm text-gray-900 bg-transparent border-x border-gray-200 focus:outline-none"
              :min="1"
              :max="selectedVariant?.stock || 0"
              aria-label="Quantity"
            />
            <button
              class="w-10 h-10 text-gray-600 hover:bg-gray-100 rounded-r-md"
              :disabled="quantity >= (selectedVariant?.stock || 0)"
              @click="$emit('increase-quantity')"
              aria-label="Increase quantity"
            >
              <i class="fas fa-plus"></i>
            </button>
          </div>
          <span v-if="selectedVariant?.stock >= 0" class="text-gray-500 text-sm ml-2">
            {{ selectedVariant.stock }} sản phẩm có sẵn
          </span>
        </div>
        <div v-if="validationMessage" class="text-red-500 text-sm mt-2 ml-[80px]">
          {{ validationMessage }}
        </div>
      </div>
      <div class="flex space-x-4 mt-4">
        <button
          @click="handleAction('add-to-cart')"
          :disabled="isAddingToCart || !props.isVariantFullySelected"
          class="flex items-center justify-center border border-[#0d5cb6] text-[#0d5cb6] bg-white rounded-sm w-48 h-12 text-sm font-semibold hover:bg-[#e6f0fb] transition-colors duration-200 disabled:opacity-50"
          aria-label="Add to cart"
        >
          <i class="fas fa-shopping-cart mr-2"></i>
          Thêm Vào Giỏ Hàng
        </button>
        <button
          @click="handleAction('buy-now')"
          :disabled="isAddingToCart || !props.isVariantFullySelected"
          class="bg-[#0d5cb6] text-white rounded-sm w-48 h-12 text-sm font-semibold hover:bg-[#084d9d] transition-colors duration-200 disabled:opacity-50"
          aria-label="Buy now"
        >
          Mua Ngay
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useToast } from "@/composables/useToast";
import { useRouter } from "vue-router";
import { useAuthStore } from "~/stores/auth";
import { useRuntimeConfig } from '#app'

const auth = useAuthStore();
const { toast } = useToast();
const router = useRouter();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

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
  validationMessage: { type: String, default: "" },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits([
  "toggle-favorite",
  "view-shop",
  "select-option",
  "increase-quantity",
  "decrease-quantity",
  "validate-selection",
  "add-to-cart",
  "buy-now",
  "update:quantity",
  "update:validationMessage",
  "update:selectedOptions",
  "clear-validation",
  "chat-with-shop",
]);

const isFavorite = ref(props.isFavorite);
const isReportModalOpen = ref(false);
const selectedReportReason = ref("");
const customReportReason = ref("");
const isSubmittingReport = ref(false);

const reportReasons = [
  { value: "not_matching_description", label: "Sản phẩm không đúng với mô tả" },
  { value: "fraud_signs", label: "Sản phẩm có dấu hiệu lừa đảo" },
  { value: "counterfeit", label: "Hàng giả, hàng nhái" },
  { value: "unknown_origin", label: "Sản phẩm không rõ nguồn gốc, xuất xứ" },
  { value: "unclear_images", label: "Hình ảnh sản phẩm không rõ ràng" },
  { value: "offensive_content", label: "Sản phẩm có hình ảnh hoặc nội dung phản cảm" },
  { value: "mismatched_name_image", label: "Tên sản phẩm không phù hợp với hình ảnh" },
  { value: "prohibited_product", label: "Sản phẩm bị cấm buôn bán (như động vật hoang dã, sản phẩm 18+,...)" },
  { value: "other", label: "Lý do khác" },
];


const isSubmitDisabled = computed(() => {
  if (selectedReportReason.value === "other") {
    return !customReportReason.value.trim() || isSubmittingReport.value;
  }
  return !selectedReportReason.value || isSubmittingReport.value;
});

async function toggleFavorite() {
  const token = localStorage.getItem('access_token');

  if (!token || !auth.isLoggedIn || !auth.currentUser) {
    toast('error', 'Vui lòng đăng nhập để sử dụng chức năng này!');
    router.push('/login');
    return;
  }

  try {
    const res = await $fetch(`${apiBase}/favorites/toggle`, {
      method: 'POST',
      body: { product_id: props.product.id },
      headers: { Authorization: `Bearer ${token}` },
    });

    isFavorite.value = res.favorited;
  } catch (e) {
    toast('error', e?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại!');
  }
}

function openReportModal() {
  if (!auth.isLoggedIn) {
    toast('error', 'Vui lòng đăng nhập để tố cáo sản phẩm!');
    router.push('/login');
    return;
  }
  isReportModalOpen.value = true;
  selectedReportReason.value = "";
  customReportReason.value = "";
}

function closeReportModal() {
  isReportModalOpen.value = false;
  selectedReportReason.value = "";
  customReportReason.value = "";
}

async function submitReport() {
  if (!selectedReportReason.value) {
    toast('error', 'Vui lòng chọn lý do tố cáo!');
    return;
  }

  if (selectedReportReason.value === "other" && !customReportReason.value.trim()) {
    toast('error', 'Vui lòng nhập lý do tố cáo!');
    return;
  }

  const token = localStorage.getItem('access_token');
  isSubmittingReport.value = true;

  try {
    await $fetch(`${apiBase}/reports`, {
      method: 'POST',
      body: {
        target_id: props.product.id,
        type: "product",
        reason: selectedReportReason.value === "other" ? customReportReason.value : selectedReportReason.value,
      },
      headers: { Authorization: `Bearer ${token}` },
    });

    toast('success', 'Tố cáo đã được gửi thành công!');
    closeReportModal();
  } catch (e) {
    toast('error', e?.data?.message || 'Có lỗi xảy ra khi gửi tố cáo, vui lòng thử lại!');
  } finally {
    isSubmittingReport.value = false;
  }
}

onMounted(async () => {
  const token = localStorage.getItem('access_token');
  if (token && !auth.isLoggedIn) {
    try {
      const user = await $fetch('/user', {
        headers: { Authorization: `Bearer ${token}` },
      });
      auth.currentUser = user;
      auth.isLoggedIn = true;
    } catch (err) {
      auth.currentUser = null;
      auth.isLoggedIn = false;
    }
  }

  if (auth.isLoggedIn) {
    try {
      const res = await $fetch(`${apiBase}/favorites`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const favoriteProductIds = res.data.map(item => item.product.id);
      isFavorite.value = favoriteProductIds.includes(props.product.id);
    } catch (e) {
      console.error('Không thể tải danh sách yêu thích:', e);
    }
  }
});

const localQuantity = ref(props.quantity);
const isAddingToCart = ref(false);

function isOptionAvailable(attrName, value) {
  const otherSelections = { ...props.selectedOptions };
  delete otherSelections[attrName];

  return props.variants.some(
    (variant) =>
      variant.attributes.some(
        (attr) => attr.attribute_name === attrName && attr.value === value
      ) &&
      variant.stock > 0 &&
      Object.entries(otherSelections).every(
        ([key, val]) =>
          !val ||
          variant.attributes.some(
            (attr) => attr.attribute_name === key && attr.value === val
          )
      )
  );
}

function formatPrice(price) {
  if (!price || price === "null" || price === null || price === undefined) {
    return "0";
  }
  const parsedPrice = parseFloat(price);
  return isNaN(parsedPrice)
    ? "0"
    : parsedPrice.toLocaleString("vi-VN", { style: "decimal" });
}

function handleSelectOption(attrName, value) {
  emit("select-option", attrName, value);
  emit("update:validationMessage", "");
}

function handleQuantityInput(value) {
  const maxStock = props.selectedVariant?.stock || 0;
  let newQuantity = parseInt(value, 10);

  if (isNaN(newQuantity) || newQuantity < 1) {
    newQuantity = 1;
    emit("update:validationMessage", "Số lượng phải từ 1 trở lên.");
  } else if (maxStock > 0 && newQuantity > maxStock) {
    newQuantity = maxStock;
    emit("update:validationMessage", `Chỉ còn lại ${maxStock} sản phẩm.`);
  } else {
    emit("update:validationMessage", "");
  }

  localQuantity.value = newQuantity;
  emit("update:quantity", newQuantity);
  emit("validate-selection");
}

function handleAction(action) {
  if (!props.isVariantFullySelected) {
    emit("update:validationMessage", "Vui lòng chọn Phân loại hàng");
    return;
  }

  isAddingToCart.value = true;
  const maxStock = props.selectedVariant?.stock || 0;

  if (localQuantity.value < 1) {
    emit("update:validationMessage", "Số lượng phải từ 1 trở lên.");
    isAddingToCart.value = false;
    return;
  }
  if (maxStock > 0 && localQuantity.value > maxStock) {
    emit("update:validationMessage", `Chỉ còn lại ${maxStock} sản phẩm.`);
    isAddingToCart.value = false;
    return;
  }

  emit(action);
  isAddingToCart.value = false;
}

function blockInvalidKeys(event) {
  const invalidKeys = ["-", "+", "e", "E", ".", ","];
  if (invalidKeys.includes(event.key)) {
    event.preventDefault();
  }
  if (event.type === "paste") {
    const pastedText = event.clipboardData.getData("text");
    if (!/^\d+$/.test(pastedText)) {
      event.preventDefault();
    }
  }
}

watch(
  () => [props.selectedVariant, props.quantity],
  () => {
    localQuantity.value = props.quantity;
  },
  { immediate: true }
);
</script>

<style scoped>
.input-no-spinner::-webkit-outer-spin-button,
.input-no-spinner::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.input-no-spinner {
  -moz-appearance: textfield;
}
</style>