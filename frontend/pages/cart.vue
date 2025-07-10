<template>
  <div class="bg-[#f8f9fc] font-sans text-[14px] text-[#222222]">
    <div class="max-w-[1200px] mx-auto px-4 py-4 flex flex-col md:flex-row md:space-x-4">
      <!-- Refresh button -->
      

      <!-- Left main content -->
      <div class="flex-1">
        <!-- Breadcrumb -->
        <div class="w-full mb-4">
          <div class="text-sm text-gray-500 px-4 py-2 rounded">
            <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
            <span class="mx-1">›</span>
            <span class="text-black font-medium">Giỏ hàng</span>
          </div>
        </div>
        <h2 class="font-normal text-[22px] mb-4">GIỎ HÀNG</h2>

        <!-- Error message -->
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
          {{ error }}
        </div>

       <!-- Skeleton loading for cart -->
<div
  v-if="!isCartReady"
  class="max-w-[1200px] mx-auto px-4 py-4 flex flex-col md:flex-row md:space-x-4 animate-pulse"
>
  <!-- Left side (cart items) -->
  <div class="flex-1 space-y-4">
    <!-- Fake store block (lặp lại 2 lần nếu muốn nhiều) -->
    <div class="border border-[#f8b77b] rounded-md bg-white">
      <div class="flex items-center px-4 py-2 border-b border-[#f8b77b] bg-[#ffe0b3]">
        <div class="w-4 h-4 bg-gray-200 rounded-sm mr-2"></div>
        <div class="w-32 h-4 bg-gray-200 rounded mr-2"></div>
        <div class="w-4 h-4 bg-gray-200 rounded ml-auto"></div>
      </div>
      <!-- Fake product row -->
      <div class="grid grid-cols-[40px_280px_1fr_120px_120px_120px_40px] items-center px-4 py-4 gap-2">
        <div class="w-4 h-4 bg-gray-200 rounded-sm"></div>
        <div class="flex items-center gap-2">
          <div class="w-[60px] h-[60px] bg-gray-200 rounded-sm"></div>
          <div class="space-y-2">
            <div class="w-48 h-4 bg-gray-200 rounded"></div>
            <div class="w-32 h-3 bg-gray-200 rounded"></div>
          </div>
        </div>
        <div></div>
        <div class="w-20 h-4 bg-gray-200 rounded mx-auto"></div>
        <div class="flex justify-center gap-1">
          <div class="w-6 h-6 bg-gray-200 rounded-sm"></div>
          <div class="w-10 h-6 bg-gray-200 rounded-sm"></div>
          <div class="w-6 h-6 bg-gray-200 rounded-sm"></div>
        </div>
        <div class="w-20 h-4 bg-gray-200 rounded mx-auto"></div>
        <div class="w-4 h-4 bg-gray-200 rounded mx-auto"></div>
      </div>
    </div>
  </div>
</div>


        <!-- Empty cart state -->
        <div v-else-if="!cart.stores.length" class="flex flex-col items-center justify-center py-8 space-y-4">
          <img src="/images/cart.png" alt="Giỏ hàng trống" class="w-[300px]" loading="lazy">
          <NuxtLink to="/"
            class="inline-flex items-center gap-2 px-5 py-2 bg-blue-500 text-white rounded-full shadow-md hover:bg-blue-600 transition duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.3 5.3A1 1 0 007 20h10a1 1 0 001-1l1-4M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
            Tiếp tục mua sắm
          </NuxtLink>
        </div>

        <!-- Cart content -->
        <template v-else>
          <!-- Header row -->
          <div
            class="grid grid-cols-[40px_280px_1fr_120px_120px_120px_40px] items-center bg-white text-[#999999] text-[13px] font-normal rounded-md px-4 py-2 mb-2 select-none">
            <label class="flex items-center space-x-2">
              <input
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm checked:bg-[#F97316] checked:border-transparent cursor-pointer"
                type="checkbox"  :checked="selectAll" @change="handleSelectAll" />
            </label>
            <span class="text-[16px]">Tất cả ({{ totalItems }} sản phẩm)</span>
            <div></div>
            <div class="text-right text-[16px]">Đơn giá</div>
            <div class="text-center text-[16px]">Số lượng</div>
            <div class="text-right text-[16px]">Thành tiền</div>
            <div class="text-center">
              <button v-if="selectedItems.size > 0" @click="showRemoveSelectedDialog"
                class="text-red-500 hover:text-red-700 transition-colors" :disabled="loading">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>

          <!-- Store sections -->
          <div v-for="store in cart.stores" :key="store.seller_id" class="mb-4 border border-[#f8b77b] rounded-md">
            <!-- Store header -->
            <div class="flex items-center space-x-2 px-4 py-2 border-b border-[#f8b77b] bg-[#ffe0b3]">
              <label class="flex items-center space-x-2 text-[13px] font-normal flex-1">
                <input :checked="storeSelections[store.seller_id]"
                  class="w-4 h-4 border border-[#d9d9d9] rounded-sm checked:bg-[#007aff] checked:border-transparent cursor-pointer"
                  type="checkbox" @change="handleStoreSelection(store, $event)" />
                <i class="fas fa-store-alt text-[#666666]"></i>
                <NuxtLink :to="store.store_url" class="text-[#222222] hover:underline">
                  {{ store.store_name }}
                </NuxtLink>
                <i class="fas fa-chevron-right text-[#666666] text-xs"></i>
              </label>
              <button class="text-[#007aff] text-[13px] font-normal hover:underline"
                @click="selectStoreItems(store)">
                Chọn tất cả sản phẩm
              </button>
            </div>

            <!-- Product items -->
            <div v-for="item in store.items" :key="item.id"
              class="grid grid-cols-[40px_280px_1fr_120px_120px_120px_40px] items-center px-4 py-3">
              <label class="flex items-center space-x-2">
                <input
                  class="w-4 h-4 border border-[#d9d9d9] rounded-sm checked:bg-[#F97316] checked:border-transparent cursor-pointer"
                  type="checkbox" :checked="selectedItems.has(item.id)"
                  @change="handleItemSelection(item.id, $event)" />
              </label>
              <div class="flex items-center space-x-2">
                <img :alt="item.product.name" class="w-[60px] h-[60px] object-cover border border-[#d9d9d9] rounded-sm"
                  :src="`${mediaBaseUrl}${item.productVariant.thumbnail}`" loading="lazy" />
                <div class="flex flex-col text-[13px] font-normal leading-tight">
                  <NuxtLink :to="`/products/${item.product.slug}`" class="text-[#222222] font-semibold hover:underline">
                    {{ item.product.name.length > 60 ? item.product.name.slice(0, 57) + '...' : item.product.name }}
                  </NuxtLink>
                  <span class="text-[#999999] text-[12px]">
                    {{ item.productVariant.attributes.map(attr => attr.value).join(' - ') }}
                  </span>
                </div>
              </div>
              <div></div>
              <div class="text-right font-semibold text-[14px]">
                <span v-if="item.sale_price && item.sale_price !== item.price" class="text-red-500">
                  {{ parsePrice(item.sale_price).toLocaleString('vi-VN') }}₫
                </span>
                <span v-if="item.sale_price && item.sale_price !== item.price"
                  class="text-gray-500 text-[12px] line-through">
                  {{ parsePrice(item.price).toLocaleString('vi-VN') }}₫
                </span>
                <span v-else class="text-red-500">
                  {{ parsePrice(item.price).toLocaleString('vi-VN') }}₫
                </span>
              </div>
              <div class="flex justify-center items-center space-x-1">
                <button aria-label="Decrease quantity"
                  class="border border-[#d9d9d9] rounded-l-sm w-6 h-6 text-[14px] font-semibold text-[#222222] disabled:opacity-50"
                  @click="updateQuantityWithValidation(item.id, item.quantity - 1)"
                  :disabled="item.quantity <= 1 || loading">
                  −
                </button>
                <input
                  class="w-10 h-6 border-t border-b border-[#d9d9d9] text-center text-[14px] font-semibold text-[#222222] outline-none disabled:opacity-50"
                  min="1" :max="item.stock" type="number" v-model.number="item.quantity"
                  @change="updateQuantityWithValidation(item.id, item.quantity)" :disabled="loading" />
                <button aria-label="Increase quantity"
                  class="border border-[#d9d9d9] rounded-r-sm w-6 h-6 text-[14px] font-semibold text-[#222222] disabled:opacity-50"
                  @click="updateQuantityWithValidation(item.id, item.quantity + 1)"
                  :disabled="item.quantity >= item.stock || loading">
                  +
                </button>
              </div>
              <div class="text-right text-[#ff3b30] font-semibold text-[14px]">
                {{ (parsePrice(item.sale_price || item.price) * item.quantity).toLocaleString('vi-VN') }}₫
              </div>
              <div class="text-center text-[#999999] cursor-pointer">
                <button @click="showRemoveItemDialog(item.id)" class="hover:text-red-500">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Right sidebar -->
      <div class="w-full md:w-[320px] mt-6 md:mt-0 space-y-4">
        <div class="bg-white rounded-md p-4 text-[13px] text-[#222222] border border-[#f0f0f0]">
          <div class="flex justify-between items-center mb-2">
            <span class="text-[#999999]">Tổng tiền hàng</span>
            <span class="font-semibold">{{ selectedTotal.toLocaleString('vi-VN') }}₫</span>
          </div>
          <div class="flex justify-between items-center mb-1 border-b border-[#f0f0f0] pb-2">
            <span class="font-semibold text-[15px]">Tổng tiền thanh toán</span>
            <span class="font-semibold text-[#ff3b30] text-[18px]">{{ selectedTotal.toLocaleString('vi-VN') }}₫</span>
          </div>
          <div class="text-[11px] text-[#999999] mb-4">
            (Đã bao gồm VAT nếu có)
          </div>
          <button type="button"
            class="block w-full bg-[#ff3b30] text-white font-semibold text-[16px] rounded-md py-3 text-center"
            :class="{ 'opacity-50 cursor-not-allowed': selectedItems.size === 0 || loading }"
            :disabled="selectedItems.size === 0 || loading" @click="navigateToCheckout">
            Mua Hàng ({{ selectedItems.size }})
          </button>
        </div>
      </div>
    </div>

    <!-- Confirmation Dialog -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
            <div
              class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div
                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      {{ confirmDialogTitle }}
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        {{ confirmDialogMessage }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                  @click="handleConfirmAction">
                  Xác nhận
                </button>
                <button type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="closeConfirmDialog">
                  Hủy
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCart } from '~/composables/useCart';
import { useToast } from '~/composables/useToast';

const { toast } = useToast();
const {
  cart,
  loading,
  error,
  selectedItems,
  selectAll,
  storeSelections,
  totalItems,
  isCartReady,
  selectedTotal,
  fetchCart,
  mergeCart,
  navigateToCheckout,
  handleSelectAll,
  handleStoreSelection,
  handleItemSelection,
  selectStoreItems,
  updateQuantityWithValidation,
  removeItem,
  parsePrice,
  mediaBaseUrl,
} = useCart();

const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);

const showRemoveItemDialog = (itemId) => {
  showConfirmationDialog(
    'Xác nhận xóa',
    'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?',
    async () => {
      try {
        await removeItem(itemId);
        toast('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
      } catch (error) {
        toast('error', 'Không thể xóa sản phẩm');
      }
    }
  );
};

const showRemoveSelectedDialog = () => {
  const count = selectedItems.value.size;
  showConfirmationDialog(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa ${count} sản phẩm đã chọn khỏi giỏ hàng?`,
    async () => {
      try {
        const itemsToRemove = [...selectedItems.value];
        await Promise.all(itemsToRemove.map(id => removeItem(id)));
        toast('success', `Đã xóa ${count} sản phẩm khỏi giỏ hàng`);
      } catch (error) {
        toast('error', 'Không thể xóa sản phẩm');
      }
    }
  );
};

const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};
</script>

<style scoped>
input::-webkit-inner-spin-button,
input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="checkbox"] {
  cursor: pointer;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>