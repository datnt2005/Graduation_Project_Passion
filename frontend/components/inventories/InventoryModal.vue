<template>
    <div class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-xl">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                {{ mode === 'create' ? 'Nhập kho mới' : mode === 'edit' ? 'Cập nhật tồn kho' : 'Đánh dấu hàng lỗi' }}
            </h2>

            <form @submit.prevent="handleSubmit">
                <div class="space-y-4">
                <div v-if="mode === 'create'">
  <label class="block text-sm font-medium text-gray-700">Biến thể sản phẩm</label>
  <select
    v-model="selectedVariantId"
    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
  >
    <option disabled value="">-- Chọn biến thể --</option>
    <option
      v-for="variant in productVariants"
      :key="variant.id"
      :value="variant.id"
    >
      {{ variant.sku }} - {{ variant.product_name }}
      <template v-if="variant.attributes && variant.attributes.length">
        (
        <span
          v-for="(attr, index) in variant.attributes"
          :key="index"
        >
          {{ attr.name }}: {{ attr.value
          }}<span v-if="index < variant.attributes.length - 1">, </span>
        </span>
        )
      </template>
    </option>
  </select>

  <!-- Hiển thị ảnh và mô tả khi đã chọn -->
  <div v-if="selectedVariantInfo" class="mt-2 flex items-center gap-3">
    <img
      :src="mediaBase + selectedVariantInfo.image_url"
      alt="Ảnh sản phẩm"
      class="w-12 h-12 object-cover rounded-md"
    />
    <div class="text-sm text-gray-700">
      <div class="font-medium">
        {{ selectedVariantInfo.product_name }}
      </div>
      <div class="text-xs text-gray-500">
        SKU: {{ selectedVariantInfo.sku }}
      </div>
    </div>
  </div>

  <p
    v-if="!selectedVariantId"
    class="mt-1 text-sm text-red-500"
  >
    Chưa chọn sản phẩm
  </p>
</div>


                    <div>
                        <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                        <input type="number" v-model.number="quantity" min="1"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                    </div>

                    <div v-if="mode !== 'edit'">
                        <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                        <textarea v-model="note"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                    </div>

                    <div v-if="mode === 'create'">
                        <label class="block text-sm font-medium text-gray-700">Vị trí kho</label>
                        <input type="text" v-model="location" placeholder="Ví dụ: Kho A1"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                    </div>

                    <div v-if="mode === 'create'">
                        <label class="block text-sm font-medium text-gray-700">Số lô (Batch)</label>
                        <input type="text" v-model="batchNumber"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                    </div>

                    <div v-if="mode === 'create'">
                        <label class="block text-sm font-medium text-gray-700">Nguồn nhập</label>
                        <input type="text" v-model="importSource" placeholder="Nhập từ nhà cung cấp, trả hàng, v.v."
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="$emit('close')"
                        class="px-4 py-2 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 text-sm">Hủy</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 rounded-md text-white hover:bg-blue-700 text-sm">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '@/composables/useToast';
import { secureAxios } from '@/utils/secureAxios';

const props = defineProps({
    mode: String, // 'create' | 'edit' | 'damage'
    inventory: Object,
});
const emit = defineEmits(['close', 'submitted']);
const toast = useToast();

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const quantity = ref(1);
const note = ref('');
const location = ref('');
const batchNumber = ref('');
const importSource = ref('');

const productVariants = ref([]);
const selectedVariantId = ref('');

const selectedVariantInfo = computed(() =>
    productVariants.value.find(p => p.id === selectedVariantId.value)
);

onMounted(async () => {
    if (props.mode === 'edit' && props.inventory) {
        quantity.value = props.inventory.quantity;
        location.value = props.inventory.location || '';
        selectedVariantId.value = props.inventory.product_variant_id || props.inventory.id;
    }

    if (props.mode === 'create') {
        try {
            const { data } = await secureAxios(`${apiBase}/product-variants`, { method: 'GET' }, ['admin', 'seller']);
            productVariants.value = data;
        } catch (e) {
            toast.error('Không thể tải danh sách biến thể sản phẩm');
        }
    }
});

/*************  ✨ Windsurf Command ⭐  *************/
/**
 * X l  form InventoryModal
 * 
 * @param {Object} e - Event submit form
 * 
 * @returns {Promise<void>}
 */
/*******  6c0ee44e-4dff-4090-a8bf-24d1b2824c4d  *******/const handleSubmit = async () => {
    try {
        if (props.mode === 'create') {
            if (!selectedVariantId.value) {
                toast.error('Vui lòng chọn biến thể sản phẩm');
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
        }

        if (props.mode === 'edit') {
            await secureAxios(`${apiBase}/inventories/${props.inventory.id}`, {
                method: 'PUT',
                data: {
                    quantity: quantity.value,
                    location: location.value || props.inventory.location || 'Kho mặc định',
                },
            }, ['admin', 'seller']);
        }

        if (props.mode === 'damage') {
            await secureAxios(`${apiBase}/inventories/${props.inventory.id}/damage`, {
                method: 'POST',
                data: {
                    quantity: quantity.value,
                    note: note.value,
                },
            }, ['admin', 'seller']);
        }

        toast.success('Thao tác thành công!');
        emit('submitted');
        emit('close');
    } catch (e) {
        toast.error('Thất bại: ' + (e.response?.data?.error || e.message));
    }
};
</script>
