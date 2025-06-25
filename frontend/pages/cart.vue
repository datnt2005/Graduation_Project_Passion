<template>
  <div class="bg-[#f8f9fc] font-sans text-[14px] text-[#222222]">
    <div class="max-w-[1200px] mx-auto px-4 py-4 flex flex-col md:flex-row md:space-x-4">
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

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Empty cart state -->
        <div v-else-if="!cart.stores.length" class="flex flex-col items-center justify-center py-8 space-y-4">
          <img src="/images/cart.png" alt="Giỏ hàng trống" class="w-[300px]">
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
            class="grid grid-cols-[40px_280px_1fr_120px_120px_120px_40px] items-center bg-[#f8f9fc] text-[#999999] text-[13px] font-normal rounded-md px-4 py-2 mb-2 select-none">
            <label class="flex items-center space-x-2">
              <input
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm checked:bg-[#F97316] checked:border-transparent cursor-pointer "
                type="checkbox" v-model="selectAll" @change="handleSelectAll" />
            </label>
            <span class="text-[16px]">Tất cả ({{ totalItems }} sản phẩm)</span>
            <div></div>
            <div class="text-right text-[16px]">Đơn giá</div>
            <div class="text-center text-[16px]">Số lượng</div>
            <div class="text-right text-[16px]">Thành tiền</div>
            <div class="text-center">
              <button v-if="selectedItems.size > 0" @click="handleRemoveSelectedItems"
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
                <input v-model="storeSelections[store.seller_id]"
                  class="w-4 h-4 border border-[#d9d9d9] rounded-sm checked:bg-[#007aff] checked:border-transparent cursor-pointer"
                  type="checkbox" @change="handleStoreSelection(store, $event)" />
                <i class="fas fa-store-alt text-[#666666]"></i>
                <NuxtLink :to="store.store_url" class="text-[#222222] hover:underline">
                  {{ store.store_name }}
                </NuxtLink>
                <i class="fas fa-chevron-right text-[#666666] text-xs"></i>
              </label>
              <button class="text-[#007aff] text-[13px] font-normal hover:underline" @click="selectStoreItems(store)">
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
                  :src="`${mediaBaseUrl}${item.productVariant.thumbnail}`" />
                <div class="flex flex-col text-[13px] font-normal leading-tight">
                  <NuxtLink :to="`/products/${item.product.slug}`" class="text-[#222222] font-semibold hover:underline">
                    {{ item.product.name.length > 60 ? item.product.name.slice(0, 57) + '...' : item.product.name }}
                  </NuxtLink>
                  <span class="text-[#999999] text-[12px]">
                    {{ item.productVariant.attributes.map(attr => `${attr.value}`).join(' - ') }}
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
                <button @click="handleRemoveItem(item.id)" class="hover:text-red-500">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Freeship info -->
          <!-- <div
            class="flex items-center space-x-2 px-4 py-2 border border-[#f0f0f0] rounded-md text-[14px] font-semibold text-[#222222]">
            <i class="fas fa-shipping-fast text-[#2f9e44]"></i>
            <span>Freeship 10k đơn từ 45k, Freeship 25k đơn từ 100k</span>
            <i class="fas fa-info-circle text-[#999999] text-[13px] ml-1 cursor-pointer" title="Thông tin freeship"></i>
          </div> -->
        </template>
      </div>

      <!-- Right sidebar -->
      <div class="w-full md:w-[320px] mt-6 md:mt-0 space-y-4">
        <!-- Total price box -->
        <div class="bg-white rounded-md p-4 text-[13px] text-[#222222] border border-[#f0f0f0]">
          <div class="flex justify-between items-center mb-2">
            <span class="text-[#999999]">Tổng tiền hàng</span>
            <span class="font-semibold">{{ parsePrice(selectedTotal).toLocaleString('vi-VN') }}₫</span>
          </div>
          <div class="flex justify-between items-center mb-1 border-b border-[#f0f0f0] pb-2">
            <span class="font-semibold text-[15px]">Tổng tiền thanh toán</span>
            <span class="font-semibold text-[#ff3b30] text-[18px]">{{ parsePrice(selectedTotal).toLocaleString('vi-VN')
            }}₫</span>
          </div>
          <div class="text-[11px] text-[#999999] mb-4">
            (Đã bao gồm VAT nếu có)
          </div>
          <button type="button"
            class="block w-full bg-[#ff3b30] text-white font-semibold text-[16px] rounded-md py-3 text-center"
            :class="{ 'opacity-50 cursor-not-allowed': selectedItems.size === 0 }" :disabled="selectedItems.size === 0"
            @click="navigateToCheckout">
            Mua Hàng ({{ selectedItems.size }})
          </button>
        </div>
      </div>
    </div>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6" :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="notificationType === 'success'" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="notificationType === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <button @click="showNotification = false"
            class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>
    </Teleport>

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
import { ref, computed, watch, onMounted } from 'vue';
import { navigateTo, useRuntimeConfig } from '#app';
import { useRedisCart } from '~/composables/useRedisCart';
import { useToast } from '~/composables/useToast';
const { toast } = useToast()

// Initialize cart composable
const config = useRuntimeConfig();
const mediaBaseUrl = config.public.mediaBaseUrl;
const apiBaseUrl = config.public.apiBaseUrl;

const {
  redisCartItems,
  redisCartTotal,
  fetchRedisCart,
  addToRedisCart,
  updateRedisQuantity,
  removeFromRedisCart,
  clearRedisCart,
  mergeWithUserCart,
} = useRedisCart();

const cart = ref({ stores: [], total: '0' });
const loading = ref(false);
const error = ref(null);
const selectedItems = ref(new Set());

// Notification state
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');

// Confirmation dialog state
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);

// Compute total number of items
const totalItems = computed(() => {
  return cart.value.stores.reduce((sum, store) => sum + (store.items?.length || 0), 0);
});

// Get all item IDs in the cart
const allItemIds = computed(() => {
  return cart.value.stores.flatMap(store => (store.items || []).map(item => item.id)).filter(id => id !== undefined);
});

// Handle select all
const selectAll = ref(false);

// Handle store selections
const storeSelections = ref({});

// Initialize store selections
watch(cart, (newCart) => {
  const newSelections = {};
  newCart.stores.forEach(store => {
    newSelections[store.seller_id] = (store.items || []).every(item => item && selectedItems.value.has(item.id));
  });
  storeSelections.value = newSelections;
}, { deep: true });

// Handle select all checkbox
const handleSelectAll = () => {
  selectedItems.value.clear();
  if (selectAll.value) {
    cart.value.stores.forEach(store => {
      (store.items || []).forEach(item => {
        if (item && item.id) selectedItems.value.add(item.id);
      });
    });
  }
  // Update store selections
  cart.value.stores.forEach(store => {
    storeSelections.value[store.seller_id] = selectAll.value;
  });
};

// Handle store checkbox
const handleStoreSelection = (store, event) => {
  const isChecked = event.target.checked;
  (store.items || []).forEach(item => {
    if (item && item.id) {
      if (isChecked) {
        selectedItems.value.add(item.id);
      } else {
        selectedItems.value.delete(item.id);
      }
    }
  });
  selectedItems.value = new Set(selectedItems.value);
  // Update selectAll state
  selectAll.value = allItemIds.value.every(id => selectedItems.value.has(id));
};

// Handle individual item checkbox
const handleItemSelection = (itemId, event) => {
  const isChecked = event.target.checked;
  if (isChecked) {
    selectedItems.value.add(itemId);
  } else {
    selectedItems.value.delete(itemId);
  }
  selectedItems.value = new Set(selectedItems.value);
  // Update store and selectAll states
  cart.value.stores.forEach(store => {
    storeSelections.value[store.seller_id] = (store.items || []).every(item => item && selectedItems.value.has(item.id));
  });
  selectAll.value = allItemIds.value.every(id => selectedItems.value.has(id));
};

// Calculate total price of selected items
const selectedTotal = computed(() => {
  return cart.value.stores.reduce((total, store) => {
    return total + (store.items || []).reduce((storeTotal, item) => {
      if (item && selectedItems.value.has(item.id)) {
        return storeTotal + parsePrice(item.sale_price || item.price) * (item.quantity || 1);
      }
      return storeTotal;
    }, 0);
  }, 0);
});

// Parse price from string or number
const parsePrice = (price) => {
  if (price == null || price === '') return 0;
  if (typeof price === 'number') return price;
  const priceStr = String(price).replace(/\./g, '');
  const parsed = parseInt(priceStr, 10);
  return isNaN(parsed) ? 0 : parsed;
};

// Select all items in a store
const selectStoreItems = (store) => {
  (store.items || []).forEach(item => {
    if (item && item.id) selectedItems.value.add(item.id);
  });
  selectedItems.value = new Set(selectedItems.value);
  storeSelections.value[store.seller_id] = true;
  selectAll.value = allItemIds.value.every(id => selectedItems.value.has(id));
};
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
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


// Navigate to checkout with selected items
const navigateToCheckout = () => {
  if (selectedItems.value.size === 0) {
    toast('error','Vui lòng chọn ít nhất một sản phẩm để thanh toán');
    return;
  }
  const token = localStorage.getItem('access_token');
  if (!token) {
    toast( 'error','Vui lòng đăng nhập để tiếp tục!');
    window.dispatchEvent(new CustomEvent('openLoginModal'));
    return;
  }
  const selectedItemIds = [...selectedItems.value];
  navigateTo({
    path: '/checkout',
    query: { items: selectedItemIds.join(',') },
  });
};

// Handle remove item
const handleRemoveItem = async (itemId) => {
  showConfirmationDialog(
    'Xác nhận xóa',
    'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?',
    async () => {
      try {
        await removeItem(itemId);
        toast('success','Đã xóa sản phẩm khỏi giỏ hàng');
      } catch (error) {
        toast( 'error','Không thể xóa sản phẩm. Vui lòng thử lại.');
      }
    },
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
        toast('success',`Đã xóa ${selectedItems.value.size} sản phẩm khỏi giỏ hàng`);
      } catch (error) {
        toast( 'error','Không thể xóa sản phẩm. Vui lòng thử lại.');
      }
    },
  );
};

// Update quantity with validation
const updateQuantityWithValidation = async (itemId, quantity) => {
  const item = cart.value.stores
    .flatMap(store => store.items || [])
    .find(item => item && item.id === itemId);
  if (!item) return;

  // Store the original quantity to revert if the API fails
  const originalQuantity = item.quantity;

  // Ensure quantity is a number and not negative
  const validatedQuantity = Math.max(1, Math.min(Math.floor(Number(quantity) || 1), item.stock));

  // If quantity exceeds stock, reset to stock and show notification
  if (quantity > item.stock) {
    toast('error',`Số lượng không được vượt quá ${item.stock}`);
    item.quantity = item.stock; // Reset input to maximum stock
    return;
  }

  // If quantity is less than 1, reset to 1 and show notification
  if (quantity < 1) {
    toast( 'error','Số lượng phải lớn hơn 0');
    item.quantity = 1; // Reset input to minimum
    return;
  }

  // If quantity is valid and different, update it
  if (item.quantity !== validatedQuantity) {
    item.quantity = validatedQuantity; // Optimistically update local state
    try {
      await updateQuantity(itemId, validatedQuantity);
      toast('success','Cập nhật số lượng thành công');
    } catch (error) {
      toast( 'error','Không thể cập nhật số lượng. Vui lòng thử lại.');
      item.quantity = originalQuantity; // Revert to original quantity on error
    }
  }
};

// Sync selected items after cart updates
const syncSelectedItems = () => {
  const validIds = new Set(allItemIds.value);
  const newSelected = new Set([...selectedItems.value].filter(id => validIds.has(id)));
  if (newSelected.size !== selectedItems.value.size) {
    selectedItems.value = newSelected;
  }
  // Update selectAll and storeSelections
  selectAll.value = allItemIds.value.every(id => selectedItems.value.has(id));
  cart.value.stores.forEach(store => {
    storeSelections.value[store.seller_id] = (store.items || []).every(item => item && selectedItems.value.has(item.id));
  });
};

// Fetch cart data
const fetchCart = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    await fetchRedisCart();
    cart.value = {
      stores: redisCartItems.value.length
        ? [{
            seller_id: 0,
            store_name: 'Khách',
            store_url: '/store/guest',
            items: redisCartItems.value.map(item => ({
              ...item,
              price: item.price != null ? String(item.price) : '0',
              sale_price: item.sale_price != null ? String(item.sale_price) : null,
            })),
            store_total: redisCartTotal.value,
          }]
        : [],
      total: String(redisCartTotal.value || '0'),
    };
    syncSelectedItems();
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const res = await fetch(`${apiBaseUrl}/cart`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
        'X-Requested-With': 'XMLHttpRequest',
        Origin: window.location.origin,
      },
      credentials: 'include',
    });

    if (!res.ok) {
      if (res.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
        localStorage.removeItem('access_token');
        await fetchRedisCart();
        cart.value = {
          stores: redisCartItems.value.length
            ? [{
                seller_id: 0,
                store_name: 'Khách',
                store_url: '/store/guest',
                items: redisCartItems.value.map(item => ({
                  ...item,
                  price: item.price != null ? String(item.price) : '0',
                  sale_price: item.sale_price != null ? String(item.sale_price) : null,
                })),
                store_total: redisCartTotal.value,
              }]
            : [],
          total: String(redisCartTotal.value || '0'),
        };
        syncSelectedItems();
        return;
      }
      const errorData = await res.json().catch(() => ({}));
      throw new Error(errorData.message || 'Lỗi khi lấy giỏ hàng');
    }

    const data = await res.json();
    if (data.success) {
      cart.value = {
        stores: (data.data.stores || []).map(store => ({
          ...store,
          items: (store.items || []).map(item => ({
            ...item,
            price: item.price != null ? String(item.price) : '0',
            sale_price: item.sale_price != null ? String(item.sale_price) : null,
          })),
        })),
        total: String(data.data.total || '0'),
      };
      syncSelectedItems();
    } else {
      throw new Error(data.message || 'Lỗi khi lấy giỏ hàng');
    }
  } catch (err) {
    error.value = err.message || 'Không thể lấy dữ liệu giỏ hàng';
    await fetchRedisCart();
    cart.value = {
      stores: redisCartItems.value.length
        ? [{
            seller_id: 0,
            store_name: 'Khách',
            store_url: '/store/guest',
            items: redisCartItems.value.map(item => ({
              ...item,
              price: item.price != null ? String(item.price) : '0',
              sale_price: item.sale_price != null ? String(item.sale_price) : null,
            })),
            store_total: redisCartTotal.value,
          }]
        : [],
      total: String(redisCartTotal.value || '0'),
    };
    syncSelectedItems();
  } finally {
    loading.value = false;
  }
};

// Update item quantity
const updateQuantity = async (itemId, newQuantity) => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    await updateRedisQuantity(itemId, newQuantity);
    // Update local state for Redis cart
    cart.value.stores.forEach(store => {
      const item = store.items?.find(item => item.id === itemId);
      if (item) {
        item.quantity = newQuantity;
      }
    });
    // Update total
    cart.value.total = String(redisCartTotal.value || '0');
    return;
  }

  try {
    const res = await fetch(`${apiBaseUrl}/cart/items/${itemId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ quantity: newQuantity }),
    });

    if (!res.ok) {
      if (res.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
        localStorage.removeItem('access_token');
        await updateRedisQuantity(itemId, newQuantity);
        // Update local state for Redis cart
        cart.value.stores.forEach(store => {
          const item = store.items?.find(item => item.id === itemId);
          if (item) {
            item.quantity = newQuantity;
          }
        });
        cart.value.total = String(redisCartTotal.value || '0');
        return;
      }
      const errorData = await res.json().catch(() => ({}));
      throw new Error(errorData.message || 'Lỗi khi cập nhật số lượng');
    }

    const data = await res.json();
    if (!data.success) {
      throw new Error(data.message || 'Lỗi khi cập nhật số lượng');
    }

    // Update local state with new quantity and total
    cart.value.stores.forEach(store => {
      const item = store.items?.find(item => item.id === itemId);
      if (item) {
        item.quantity = newQuantity;
        // Update store total if provided in response
        if (data.data?.store_total) {
          store.store_total = String(data.data.store_total);
        }
      }
    });

    // Update cart total if provided in response
    if (data.data?.total) {
      cart.value.total = String(data.data.total);
    } else {
      // Recalculate total locally as fallback
      cart.value.total = String(
        cart.value.stores.reduce((total, store) => {
          return total + (store.items || []).reduce((storeTotal, item) => {
            return storeTotal + parsePrice(item.sale_price || item.price) * (item.quantity || 1);
          }, 0);
        }, 0)
      );
    }
  } catch (err) {
    error.value = err.message || 'Không thể cập nhật số lượng';
    setTimeout(() => {
      error.value = null;
    }, 3000);
    throw err; // Re-throw to allow caller to revert quantity
  }
};

// Remove item
const removeItem = async (itemId) => {
  const token = localStorage.getItem('access_token');
  selectedItems.value.delete(itemId);

  try {
    if (!token) {
      await removeFromRedisCart(itemId);
      await fetchCart();
      return;
    }

    const res = await fetch(`${apiBaseUrl}/cart/items/${itemId}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    if (!res.ok) {
      if (res.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
        localStorage.removeItem('access_token');
        await removeFromRedisCart(itemId);
        await fetchCart();
        return;
      }
      throw new Error('Lỗi khi xóa sản phẩm');
    }

    await fetchCart();
  } catch (err) {
    error.value = err.message || 'Không thể xóa sản phẩm';
    setTimeout(() => {
      error.value = null;
    }, 3000);
  }
};

// Clear cart
const clearCart = async () => {
  const token = localStorage.getItem('access_token');
  selectedItems.value.clear();
  if (!token) {
    await clearRedisCart();
    cart.value = { stores: [], total: '0' };
    return;
  }

  try {
    const res = await fetch(`${apiBaseUrl}/cart`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    if (!res.ok) {
      if (res.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
        localStorage.removeItem('access_token');
        await clearRedisCart();
        cart.value = { stores: [], total: '0' };
        return;
      }
      throw new Error('Lỗi khi xóa giỏ hàng');
    }

    await fetchCart();
  } catch (err) {
    error.value = err.message || 'Không thể xóa giỏ hàng';
    setTimeout(() => {
      error.value = null;
    }, 3000);
  }
};

// Add item to cart
const addItem = async (productVariantId, quantity) => {
  const token = localStorage.getItem('access_token');
  try {
    if (!token) {
      await addToRedisCart(productVariantId, quantity);
      await fetchCart();
      return;
    }

    loading.value = true;
    error.value = null;

    const res = await fetch(`${apiBaseUrl}/cart/add`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ product_variant_id: productVariantId, quantity }),
    });

    if (!res.ok) {
      if (res.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
        localStorage.removeItem('access_token');
        await addToRedisCart(productVariantId, quantity);
        await fetchCart();
        return;
      }
      throw new Error('Lỗi khi thêm vào giỏ hàng');
    }

    const data = await res.json();
    if (data.success) {
      await fetchCart();
    } else {
      throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng');
    }
  } catch (err) {
    error.value = err.message || 'Không thể thêm vào giỏ hàng';
    await addToRedisCart(productVariantId, quantity);
    await fetchCart();
  } finally {
    loading.value = false;
  }
};

// Merge Redis cart with user cart
const mergeCart = async () => {
  await mergeWithUserCart();
  await fetchCart();
};

// Initialize cart on mount
onMounted(async () => {
  try {
    await fetchCart();
    const token = localStorage.getItem('access_token');
    const redisCartId = localStorage.getItem('redis_cart_id');
    if (token && redisCartId) {
      await mergeCart();
    }
  } catch (err) {
    console.error('Failed to initialize cart:', err);
    toast( 'error','Không thể tải giỏ hàng. Vui lòng thử lại.');
  }
  window.addEventListener('loginSuccess', () => {
    window.location.reload();
  });
});
</script>

<style>
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