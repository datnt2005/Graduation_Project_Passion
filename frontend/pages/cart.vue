<template>
  <div class="bg-[#f8f9fc] font-sans text-[14px] text-[#222222]">
    <div
      class="max-w-[1200px] mx-auto px-4 py-4 flex flex-col md:flex-row md:space-x-4"
    >
    <!-- Left main content -->
    <div class="flex-1">
        <!-- Breadcrumb -->
               <div class="w-full max-w-6xl mb-4">
                 <div class="text-sm text-gray-500  px-4 py-2 rounded ">
                   <span class="text-gray-400">Trang chủ</span>
                   <span class="mx-1">›</span>
                   <span class="text-black font-medium">Giỏ hàng</span>
                 </div>
               </div>
        <h2 class="font-normal text-[22px] mb-3">GIỎ HÀNG</h2>

        <!-- Error message -->
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
          {{ error }}
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Empty cart state -->
        <div v-else-if="!cartItems.length" class="flex flex-col items-center justify-center py-8 space-y-4">
          <span class="text-gray-500">Giỏ hàng trống</span>
          <NuxtLink to="/" class="text-blue-600 hover:underline">Tiếp tục mua sắm</NuxtLink>
        </div>

        <!-- Cart content -->
        <template v-else>
          <!-- Header row -->
          <div class="flex items-center bg-[#f8f9fc] text-[#999999] text-[13px] font-normal rounded-md px-4 py-2 mb-2 select-none">
            <label class="flex items-center space-x-2 w-[280px]">
              <input
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm bg-[#1BA0E2] checked:bg-[#1BA0E2] checked:border-transparent"
                type="checkbox"
                :checked="selectAll"
                @change="toggleSelectAll"
              />
              <span class="text-[18px]">Tất cả ({{ cartItems.length }} sản phẩm)</span>
            </label>
            <div class="flex-1"></div>
            <div class="w-[90px] text-right text-[16px]">Đơn giá</div>
            <div class="w-[90px] text-center text-[16px]">Số lượng</div>
            <div class="w-[90px] text-right text-[16px]">Thành tiền</div>
            <div class="w-[40px] text-center">
              <button 
                v-if="selectedItems.size > 0"
                @click="handleRemoveSelectedItems"
                class="text-red-500 hover:text-red-700 transition-colors"
                :disabled="loading"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>

          <!-- Shop section -->
          <div class="mb-4 border border-[#f8b77b] rounded-md">
            <!-- Shop header -->
            <div class="flex items-center space-x-2 px-4 py-2 border-b border-[#f8b77b] bg-[#ffe0b3]">
              <label class="flex items-center space-x-2 text-[13px] font-normal flex-1">
                <input
                  :checked="selectAll"
                  class="w-4 h-4 border border-[#d9d9d9] rounded-sm text-[#007aff]"
                  type="checkbox"
                  @change="toggleSelectAll"
                />
                <i class="fas fa-store-alt text-[#666666]"></i>
                <span>AHABOOKS</span>
                <i class="fas fa-chevron-right text-[#666666] text-xs"></i>
              </label>
              <a class="text-[#007aff] text-[13px] font-normal hover:underline" href="#">
                Chọn sản phẩm
              </a>
            </div>

            <!-- Product items -->
            <div v-for="item in cartItems" :key="item.id" class="flex items-center px-4 py-3 space-x-4">
              <label class="flex items-center space-x-2 w-[280px]">
                <input
                  :checked="selectedItems.has(item.id)"
                  class="w-4 h-4 border border-[#d9d9d9] rounded-sm text-[#007aff]"
                  type="checkbox"
                  @change="toggleSelectItem(item.id)"
                />
                <img
                  :alt="item.productVariant?.product?.name"
                  class="w-[60px] h-[60px] object-cover border border-[#d9d9d9] rounded-sm"
                  height="60"
                  :src="item.productVariant?.thumbnail ? `${mediaBaseUrl}${item.productVariant.thumbnail}` : '/images/default-product.jpg'"
                  width="60"
                />
                <div class="flex flex-col text-[13px] font-normal leading-tight">
                  <span class="text-[#222222] font-semibold">
                    {{ item.productVariant?.product?.name || 'Sản phẩm không xác định' }}
                  </span>
                  <div class="flex items-center space-x-1 text-[#999999] text-[12px]">
                    <i class="fas fa-truck"></i>
                    <span>Giao thứ 7, 07/06</span>
                  </div>
                  <span class="text-[#999999] text-[12px]">
                    Phiên bản {{ item.product_variant_id }}
                  </span>
                </div>
              </label>
              <div class="w-[90px] text-right font-semibold text-[14px]">
                {{ Number(item.price || 0).toLocaleString() }}₫
              </div>
              <div class="w-[90px] flex justify-center items-center space-x-1">
                <button
                  aria-label="Decrease quantity"
                  class="border border-[#d9d9d9] rounded-l-sm w-6 h-6 text-[14px] font-semibold text-[#222222] disabled:opacity-50"
                  @click="updateQuantity(item.id, Number(item.quantity) - 1)"
                  :disabled="Number(item.quantity) <= 1 || loading"
                >
                  −
                </button>
                <input
                  class="w-10 h-6 border-t border-b border-[#d9d9d9] text-center text-[14px] font-semibold text-[#222222] outline-none disabled:opacity-50"
                  min="1"
                  type="number"
                  v-model.number="item.quantity"
                  @change="updateQuantity(item.id, Number(item.quantity))"
                  :disabled="loading"
                />
                <button
                  aria-label="Increase quantity"
                  class="border border-[#d9d9d9] rounded-r-sm w-6 h-6 text-[14px] font-semibold text-[#222222] disabled:opacity-50"
                  @click="updateQuantity(item.id, Number(item.quantity) + 1)"
                  :disabled="loading"
                >
                  +
                </button>
              </div>
              <div class="w-[90px] text-right text-[#ff3b30] font-semibold text-[14px]">
                {{ (Number(item.price || 0) * Number(item.quantity || 1)).toLocaleString() }}₫
              </div>
              <div class="w-[40px] text-center text-[#999999] cursor-pointer" @click="handleRemoveItem(item.id)">
                <i class="fas fa-trash-alt"></i>
              </div>
            </div>

            <!-- Shop promo -->
            <div class="flex items-center space-x-2 px-4 py-2 text-[#007aff] text-[13px] font-normal cursor-pointer select-none">
              <i class="fas fa-ticket-alt"></i>
              <span>Thêm mã khuyến mãi của Shop</span>
              <i class="fas fa-chevron-right ml-auto"></i>
            </div>
          </div>

          <!-- Shop freeship -->
          <div class="flex items-center space-x-2 px-4 py-2 border border-[#f0f0f0] rounded-md text-[14px] font-semibold text-[#222222]">
            <i class="fas fa-shipping-fast text-[#2f9e44]"></i>
            <span>Freeship 10k đơn từ 45k, Freeship 25k đơn từ 100k</span>
            <i class="fas fa-info-circle text-[#999999] text-[13px] ml-1 cursor-pointer" title="Thông tin freeship"></i>
          </div>
        </template>
      </div>
      <!-- Right sidebar -->
      <div class="w-full md:w-[320px] mt-6 md:mt-0 space-y-4">
        <!-- Total price box -->
        <div
          class="bg-white rounded-md p-4 text-[13px] text-[#222222] border border-[#f0f0f0]"
        >
          <div class="flex justify-between items-center mb-2">
            <span class="text-[#999999]">Tổng tiền hàng</span>
            <span class="font-semibold">{{ Number(cartTotal || 0).toLocaleString() }}₫</span>
          </div>
          <div
            class="flex justify-between items-center mb-1 border-b border-[#f0f0f0] pb-2"
          >
            <span class="font-semibold text-[15px]">Tổng tiền thanh toán</span>
            <span class="font-semibold text-[#ff3b30] text-[18px]">{{ Number(cartTotal || 0).toLocaleString() }}₫</span>
          </div>
          <div class="text-[11px] text-[#999999] mb-4">
            (Đã bao gồm VAT nếu có)
          </div>
          <button
            type="button"
            class="block w-full bg-[#ff3b30] text-white font-semibold text-[16px] rounded-md py-3 text-center"
            :class="{ 'opacity-50 cursor-not-allowed': !cartItems.length }"
            :disabled="!cartItems.length"
            @click="navigateToCheckout"
          >
            Mua Hàng ({{ cartItems.length || 0 }})
          </button>
        </div>
      </div>
    </div>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
        >
          <div class="flex-shrink-0">
            <svg
              class="h-6 w-6"
              :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                v-if="notificationType === 'success'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
              <path
                v-if="notificationType === 'error'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button
              @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <svg
                class="h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirmation Dialog -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
              class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            >
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg 
                      class="h-6 w-6 text-red-600" 
                      xmlns="http://www.w3.org/2000/svg" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >
                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" 
                      />
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
                <button
                  type="button"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="handleConfirmAction"
                >
                  Xác nhận
                </button>
                <button
                  type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="closeConfirmDialog"
                >
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
import { onMounted, ref } from "vue";
import { navigateTo } from '#app'

// Use the cart composable
const { 
  cartItems, 
  cartTotal, 
  loading, 
  error, 
  selectedItems,
  selectAll,
  fetchCart, 
  updateQuantity, 
  removeItem, 
  clearCart,
  toggleSelectAll,
  toggleSelectItem,
  mergeCart
} = useCart()
const config = useRuntimeConfig()
const mediaBaseUrl = config.public.mediaBaseUrl

// Notification state
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');

// Confirmation dialog state
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);

// Show notification message
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Close confirmation dialog
const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

// Handle confirm action
const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};

// Show confirmation dialog
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

onMounted(async () => {
  await fetchCart();
  // Check if user just logged in and has Redis cart
  const token = localStorage.getItem('access_token');
  const redisCartId = localStorage.getItem('redis_cart_id');
  if (token && redisCartId) {
    await mergeCart();
  }
  // Listen for loginSuccess event to reload page
  window.addEventListener('loginSuccess', () => {
    window.location.reload();
  });
});

const navigateToCheckout = () => {
  if (!cartItems.value.length) {
    showNotificationMessage('Giỏ hàng trống. Vui lòng thêm sản phẩm vào giỏ hàng', 'error');
    return;
  }
  const token = localStorage.getItem('access_token')
  if (!token) {
    showNotificationMessage('Vui lòng đăng nhập để tiếp tục', 'error');
    console.log('Emit openLoginModal event');
    window.dispatchEvent(new CustomEvent('openLoginModal'));
    return;
  }
  navigateTo('/checkout')
}

// Handle remove item
const handleRemoveItem = async (itemId) => {
  showConfirmationDialog(
    'Xác nhận xóa',
    'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?',
    async () => {
      try {
        await removeItem(itemId);
        showNotificationMessage('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
      } catch (error) {
        showNotificationMessage('Không thể xóa sản phẩm. Vui lòng thử lại', 'error');
      }
    }
  );
};

// Handle remove selected items
const handleRemoveSelectedItems = async () => {
  showConfirmationDialog(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa ${selectedItems.value.size} sản phẩm đã chọn khỏi giỏ hàng?`,
    async () => {
      try {
        const itemsToRemove = [...selectedItems.value];
        for (const itemId of itemsToRemove) {
          await removeItem(itemId);
        }
        showNotificationMessage(`Đã xóa ${itemsToRemove.length} sản phẩm khỏi giỏ hàng`, 'success');
      } catch (error) {
        showNotificationMessage('Không thể xóa sản phẩm. Vui lòng thử lại', 'error');
      }
    }
  );
};
</script>

<style>
/* Custom scrollbar for quantity input */
input::-webkit-inner-spin-button,
input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
span {
  font-size: 16px;
}
</style>