<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div
        :class="{
          'bg-green-50 border-b border-green-200': mode === 'create',
          'bg-blue-50 border-b border-blue-200': mode === 'edit',
          'bg-red-50 border-b border-red-200': mode === 'damage',
        }"
        class="p-6 flex items-start justify-between"
      >
        <div class="flex items-center gap-3">
          <div
            :class="{
              'bg-green-50 text-green-600 border border-green-200': mode === 'create',
              'bg-blue-50 text-blue-600 border border-blue-200': mode === 'edit',
              'bg-red-50 text-red-600 border border-red-200': mode === 'damage',
            }"
            class="p-2 rounded-lg"
          >
            <svg v-if="mode === 'create'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <svg v-else-if="mode === 'edit'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 13.828a4 4 0 01-1.414.828l-4.242 1.414 1.414-4.242a4 4 0 01.828-1.414z"
              />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01M10.29 3.86l-7.38 12.78A1 1 0 003.76 19h16.48a1 1 0 00.85-1.53L13.71 3.86a1 1 0 00-1.71 0z"
              />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">
              {{ mode === 'create' ? 'Nhập kho mới' : mode === 'edit' ? 'Cập nhật tồn kho' : 'Xác Nhận' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
              {{ mode === 'create' ? 'Thêm sản phẩm mới vào kho' : mode === 'edit' ? 'Cập nhật số lượng tồn kho' : 'Xuất/Trả sản phẩm lỗi' }}
            </p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form Nội dung -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Biến thể sản phẩm -->
        <div v-if="mode === 'create'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Biến thể sản phẩm *</label>
          <select
            v-model="selectedVariantId"
            required
            class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option disabled value="">-- Chọn sản phẩm --</option>
            <option v-for="variant in productVariants" :key="variant.id" :value="variant.id">
              {{ variant.sku }} - {{ variant.product_name }}
              <template v-if="variant.attributes && variant.attributes.length">
                ({{ variant.attributes.map(attr => `${attr.name}: ${attr.value}`).join(', ') }})
              </template>
            </option>
          </select>

          <!-- Preview -->
          <div v-if="selectedVariantInfo" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="flex items-center gap-4">
              <img
                :src="mediaBase + selectedVariantInfo.image_url"
                class="w-14 h-14 rounded-md border object-cover"
                alt="Product Image"
              />
              <div>
                <h4 class="font-semibold text-gray-900">{{ selectedVariantInfo.product_name }}</h4>
                <div class="flex gap-2 text-xs mt-1">
                  <span class="bg-gray-100 px-2 py-1 rounded font-mono text-gray-600">{{ selectedVariantInfo.sku }}</span>
                  <span
                    v-if="selectedVariantInfo.current_stock != null"
                    class="bg-gray-100 px-2 py-1 rounded text-gray-600"
                  >
                    Tồn kho: {{ selectedVariantInfo.current_stock }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Số lượng -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Số lượng *</label>
          <input
            type="number"
            v-model.number="quantity"
            min="1"
            required
            class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          />
        </div>

        <!-- Ghi chú -->
        <div v-if="mode !== 'edit'">
          <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
          <textarea
            v-model="note"
            rows="3"
            class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Ghi chú thêm..."
          ></textarea>
        </div>

        <!-- Action type -->
        <div v-if="mode === 'damage'">
          <label class="block text-sm font-medium text-gray-700">Hành động</label>
          <select
            v-model="actionType"
            required
            class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option disabled value="">-- Chọn hành động --</option>
            <option value="export">Xuất kho</option>
            <option value="damage">Trả hàng lỗi</option>
          </select>
        </div>

        <!-- Nhập kho thêm -->
        <div v-if="mode === 'create'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Vị trí kho</label>
            <input
              v-model="location"
              class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              placeholder="Ví dụ: Kho A1"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Số lô (Batch)</label>
            <input
              v-model="batchNumber"
              class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              placeholder="Nhập số lô"
            />
          </div>
        </div>

        <div v-if="mode === 'create'">
          <label class="block text-sm font-medium text-gray-700">Nguồn nhập</label>
          <input
            v-model="importSource"
            class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Nhập từ nhà cung cấp, trả hàng, v.v."
          />
        </div>

        <!-- Nút hành động -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 text-sm"
          >
            Hủy
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            :class="[
              'px-4 py-2 rounded-md font-medium flex items-center gap-2 text-sm',
              {
                'bg-green-600 hover:bg-green-700 text-white': mode === 'create' && !isSubmitting,
                'bg-blue-600 hover:bg-blue-700 text-white': mode === 'edit' && !isSubmitting,
                'bg-red-600 hover:bg-red-700 text-white': mode === 'damage' && !isSubmitting,
                'opacity-70 cursor-not-allowed': isSubmitting,
              },
            ]"
          >
            <svg
              v-if="isSubmitting"
              class="w-4 h-4 animate-spin"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span>
              {{
                isSubmitting
                  ? mode === 'create'
                    ? 'Đang nhập...'
                    : mode === 'edit'
                    ? 'Đang cập nhật...'
                    : 'Đang đánh dấu...'
                  : mode === 'create'
                  ? 'Nhập kho'
                  : mode === 'edit'
                  ? 'Cập nhật'
                  : 'Lưu'
              }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>

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
        class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50 max-w-sm"
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
          <p class="text-sm font-medium text-gray-900">{{ notificationMessage }}</p>
        </div>
        <div class="flex-shrink-0">
          <button
            @click="showNotification = false"
            class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRuntimeConfig } from '#app';
import { secureAxios } from '@/utils/secureAxios';
import { useNotification } from '@/composables/useNotification';

const { showNotification, notificationMessage, notificationType, setNotification } = useNotification();

const props = defineProps({
  mode: {
    type: String,
    required: true,
    validator: (value) => ['create', 'edit', 'damage'].includes(value),
  },
  inventory: Object,
});

const emit = defineEmits(['close', 'submitted']);

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const quantity = ref(1);
const note = ref('');
const location = ref('');
const batchNumber = ref('');
const importSource = ref('');
const actionType = ref('');
const productVariants = ref([]);
const selectedVariantId = ref('');
const isSubmitting = ref(false);

const selectedVariantInfo = computed(() => productVariants.value.find((p) => p.id === selectedVariantId.value));

onMounted(async () => {
  if (props.mode === 'edit' && props.inventory) {
    quantity.value = props.inventory.quantity || 1;
    location.value = props.inventory.location || '';
    selectedVariantId.value = props.inventory.product_variant_id || props.inventory.id;
  }

  if (props.mode === 'create') {
    try {
      const { data } = await secureAxios(`${apiBase}/product-variants`, { method: 'GET' }, ['admin', 'seller']);
      productVariants.value = data;
    } catch (e) {
      setNotification('Không thể tải danh sách biến thể sản phẩm. Vui lòng thử lại sau.', 'error');
    }
  }
});

const handleSubmit = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    if (props.mode === 'create') {
      if (!selectedVariantId.value) {
        setNotification('Vui lòng chọn biến thể sản phẩm.', 'error');
        isSubmitting.value = false;
        return;
      }

      await secureAxios(`${apiBase}/inventories/import`, {
        method: 'POST',
        data: {
          product_variant_id: selectedVariantId.value,
          quantity: quantity.value,
          note: note.value,
          location: location.value || undefined,
          batch_number: batchNumber.value || undefined,
          import_source: importSource.value || undefined,
        },
      }, ['admin', 'seller']);

      setNotification('Nhập kho thành công!', 'success');
    }

    if (props.mode === 'edit') {
      await secureAxios(`${apiBase}/inventories/${props.inventory.id}`, {
        method: 'PUT',
        data: {
          quantity: quantity.value,
          location: location.value || props.inventory.location || 'Kho mặc định',
        },
      }, ['admin', 'seller']);

      setNotification('Cập nhật tồn kho thành công!', 'success');
    }

    if (props.mode === 'damage') {
      if (!actionType.value) {
        setNotification('Vui lòng chọn hành động (Xuất kho hoặc Trả hàng lỗi).', 'error');
        isSubmitting.value = false;
        return;
      }

      await secureAxios(`${apiBase}/inventories/${props.inventory.id}/action`, {
        method: 'POST',
        data: {
          quantity: quantity.value,
          note: note.value,
          action_type: actionType.value,
        },
      }, ['admin', 'seller']);

      setNotification('Đánh dấu lỗi thành công!', 'success');
    }

    emit('submitted');
    emit('close');
  } catch (e) {
    setNotification(
      e.response?.data?.message || 'Đã xảy ra lỗi. Vui lòng thử lại sau.',
      'error'
    );
  } finally {
    isSubmitting.value = false;
  }
};
</script>