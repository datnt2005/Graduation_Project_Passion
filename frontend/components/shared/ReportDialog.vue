<template>
  <Teleport to="body">
    <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg w-[500px] p-6">
        <h2 class="text-lg font-bold mb-4">Báo Cáo Đánh Giá Này</h2>
        <p class="mb-4">Vui lòng chọn lý do báo cáo</p>

        <div class="space-y-2 mb-4">
          <label
            v-for="option in reasons"
            :key="option.value"
            class="flex items-center gap-2 cursor-pointer"
          >
            <input type="radio" v-model="selected" :value="option.value" class="mt-0.5" />
            <span class="leading-[1.4]">{{ option.label }}</span>
          </label>

          <div v-if="selected === 'other'" class="mt-2">
            <textarea
              v-model="detail"
              rows="3"
              class="w-full p-2 border rounded resize-none"
              placeholder="Vui lòng mô tả chi tiết vi phạm (bắt buộc)"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="closeDialog" class="px-4 py-2 border rounded hover:bg-gray-100">Hủy</button>
          <button @click="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Gửi</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
<script setup>
import { ref } from 'vue'
import { useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'

const { toast } = useToast()

const props = defineProps({
  targetId: Number,
  type: String // 'review' | 'product' | ...
})

const emit = defineEmits(['close'])

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const visible = ref(true)
const selected = ref('')
const detail = ref('')

const reasons = [
  { value: 'offensive', label: 'Đánh giá thô tục phản cảm' },
  { value: 'image', label: 'Chứa hình ảnh phản cảm, khỏa thân, khiêu dâm' },
  { value: 'duplicate', label: 'Đánh giá trùng lặp (thông tin rác)' },
  { value: 'personal', label: 'Chứa thông tin cá nhân' },
  { value: 'ads', label: 'Quảng cáo trái phép' },
  { value: 'wrong', label: 'Đánh giá không chính xác / gây hiểu lầm' },
  { value: 'other', label: 'Vi phạm khác' }
]

const closeDialog = () => {
  visible.value = false
  emit('close')
}

const submit = async () => {
  if (!selected.value) {
    return toast('warning', 'Vui lòng chọn lý do báo cáo')
  }

  if (selected.value === 'other' && !detail.value.trim()) {
    return toast('warning', 'Vui lòng nhập mô tả chi tiết')
  }

  const token = localStorage.getItem('access_token')
  if (!token) {
    return toast('info', 'Vui lòng đăng nhập để báo cáo')
  }

  try {
    const res = await fetch(`${apiBase}/reports`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({
        target_id: props.targetId,
        type: props.type,
        reason: selected.value === 'other' ? detail.value.trim() : selected.value
      })
    })
    const data = await res.json()
    if (!res.ok) {
      if (res.status === 409) {
        return toast('info', data.message || 'Bạn đã gửi báo cáo trước đó.')
      }
      throw new Error(data.message || 'Báo cáo không thành công')
    }

    toast('success', 'Báo cáo đã được gửi')
    closeDialog()
  } catch (err) {
    toast('error', err.message || 'Đã xảy ra lỗi')
  }
}
</script>

