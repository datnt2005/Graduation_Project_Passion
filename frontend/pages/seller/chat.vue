<template>
  <div class="flex flex-col sm:flex-row h-[100dvh] bg-white">
    <!-- Sidebar -->
    <aside
      :class="[
        'bg-gray-50 border-r overflow-y-auto transition-transform duration-300 fixed sm:static inset-0 sm:inset-auto z-40',
        isMobile ? 'w-full' : 'w-1/4',
        selectedSession && isMobile ? '-translate-x-full' : 'translate-x-0'
      ]"
    >
      <h2 class="text-base font-bold p-4 border-b bg-white sticky top-0 z-10">📋 Khách hàng</h2>
      <ul class="divide-y">
        <li
          v-for="session in sessions"
          :key="session.id"
          @click="selectSession(session)"
          class="flex items-center gap-2 p-3 cursor-pointer hover:bg-blue-100"
          :class="{ 'bg-blue-50': selectedSession?.id === session.id }"
        >
          <img
            :src="getAvatarUrl(session.user?.avatar)"
            class="w-8 h-8 rounded-full object-cover"
            alt="User avatar"
          />
          <span class="truncate text-sm">{{ session.user?.name || 'Người dùng' }}</span>
        </li>
      </ul>
    </aside>

    <!-- Nội dung Chat -->
    <section v-if="selectedSession || !isMobile" class="flex-1 flex flex-col items-center bg-white overflow-y-auto">
      <div class="w-full max-w-screen-lg flex flex-col flex-1">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-2 border-b bg-white text-sm">
          <div>
            💬 Trò chuyện với: <span class="text-blue-600 font-bold">{{ selectedSession?.user?.name || 'Chọn khách hàng' }}</span>
          </div>
          <button
            v-if="isMobile"
            @click="selectedSession = null"
            class="sm:hidden text-sm text-gray-600 hover:text-black"
          >
            ← Trở lại
          </button>
        </div>

     <!-- Danh sách tin nhắn -->
    <div class="flex-1 w-[80%] p-3 space-y-3 " ref="chatBox">
      <div
        v-for="msg in messages"
        :key="msg.id"
        class="flex gap-2 items-start"
        :class="msg.sender_type === 'seller' ? 'justify-end' : 'justify-start'"
        @contextmenu.prevent="openContext(msg.id, $event)"
      >
        <!-- Nội dung -->
        <div
          class="relative p-2 rounded-lg shadow max-w-[85%] sm:max-w-[70%]"
          :class="[
            msg.sender_type === 'seller' ? 'bg-blue-500 text-white' : 'bg-white text-gray-800',
            msg.pending ? 'opacity-60' : '',
            msg.error ? 'border border-red-500' : ''
          ]"
        >
          <!-- Nội dung tin nhắn -->
          <p
            class="whitespace-pre-line break-words break-all"
            :class="msg.message_type === 'revoked' ? 'italic text-gray-300' : ''"
          >
            {{ msg.message }}
            <span
              v-if="msg.message_type === 'edited'"
              class="text-xs italic text-gray-300 ml-1"
            >
              (Đã chỉnh sửa)
            </span>
          </p>

          <!-- File đính kèm (chỉ hiện nếu chưa thu hồi) -->
          <div v-if="msg.message_type !== 'revoked' && msg.attachments?.length" class="flex flex-wrap gap-2 mt-2">
            <template v-for="file in msg.attachments" :key="file.id">
              <img
                v-if="file.file_type === 'image'"
                :src="file.file_url"
                class="w-[80px] h-[80px] object-cover rounded border border-gray-200 shadow"
                :class="msg.pending ? 'opacity-60' : ''"
                alt="Attachment"
              />
              <a
                v-else
                :href="file.file_url"
                target="_blank"
                class="text-blue-200 underline text-sm truncate max-w-[200px]"
              >
                📎 {{ file.file_name }}
              </a>
            </template>
          </div>

          <!-- Trạng thái gửi -->
          <div v-if="msg.pending" class="text-xs text-gray-200 italic mt-1">
            <i class="fas fa-spinner animate-spin mr-1"></i> Đang gửi...
          </div>
        </div>

        <!-- Nút 3 chấm (chỉ hiện nếu chưa thu hồi) -->
        <button
          v-if="msg.sender_type === 'seller' && msg.message_type !== 'revoked'"
          @click.stop="openContext(msg.id, $event)"
          class="text-gray-400 hover:text-gray-600 text-lg px-1"
        >
          ⋮
        </button>

        <!-- Menu sửa / thu hồi -->
        <div
          v-if="contextMenu.open && contextMenu.messageId === msg.id && msg.sender_type === 'seller'"
          :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
          class="fixed z-50 bg-white border rounded shadow-md w-36 text-sm"
        >
          <button @click="editMessage(msg)" class="block w-full text-left px-3 py-2 hover:bg-gray-100">✏️ Sửa</button>
          <button @click="revokeMessage(msg)" class="block w-full text-left px-3 py-2 hover:bg-gray-100">🗑️ Thu hồi</button>
        </div>
      </div>
    </div>

        <!-- Gửi tin nhắn -->
        <form @submit.prevent="sendMessage"  class="position: sticky bottom-0 z-10 bg-white border-t p-3 flex flex-col gap-3">
          <!-- Ảnh preview -->
          <div v-if="imagePreview.length" class="flex flex-wrap gap-2">
            <div
              v-for="(img, i) in imagePreview"
              :key="i"
              class="relative w-[70px] h-[70px] group"
            >
              <img
                :src="img"
                class="w-full h-full object-cover rounded border border-gray-300 shadow-sm"
                alt="Image preview"
              />
              <button
                type="button"
                @click="removeImage(i)"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
              >×</button>
            </div>
          </div>

          <!-- Input tin nhắn -->
          <div class="flex items-center gap-2">
            <input
              v-model="form.message"
              type="text"
              placeholder="Nhập tin nhắn..."
              class="flex-1 border rounded px-3 py-2 text-sm"
            />
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/*"
              @change="handleFile"
              class="hidden"
            />
            <button
              type="button"
              @click="fileInput.click()"
              class="bg-gray-100 px-3 py-2 rounded"
            >📎</button>
            <button
              type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded text-sm disabled:bg-gray-400"
              :disabled="!form.message.trim() && !form.file.length"
            >Gửi</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</template>



<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'

definePageMeta({ layout: 'default-seller' })

// --- Biến trạng thái
const isMobile = ref(false)
const token = ref('')
const sellerId = ref(null)
const seller = ref({})
const sessions = ref([])
const selectedSession = ref(null)
const messages = ref([])
const form = ref({ message: '', file: [] })
const imagePreview = ref([])
const chatBox = ref(null)
const fileInput = ref(null)
let polling = null
const contextMenu = ref({ open: false, messageId: null, x: 0, y: 0 })
let lastMessageCount = 0

// --- Config
const config = useRuntimeConfig()
const API = config.public.apiBaseUrl
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000').replace(/\/?$/, '/')
const DEFAULT_AVATAR = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/avatars/default.jpg'

const getAvatarUrl = (avatar) => {
  if (!avatar) return DEFAULT_AVATAR
  const cleaned = avatar.trim()
  return cleaned.startsWith('http') ? cleaned : mediaBase + cleaned
}

// --- API CALLS ---
const loadSellerInfo = async () => {
  try {
    const storedToken = localStorage.getItem('access_token')
    if (!storedToken) return alert('Vui lòng đăng nhập')
    token.value = storedToken
    const res = await axios.get(`${API}/sellers/seller/me`, { headers: { Authorization: `Bearer ${token.value}` } })
    seller.value = res.data.seller
    sellerId.value = seller.value.id
  } catch (err) {
    console.error('❌ Lỗi lấy thông tin người bán:', err)
  }
}

const loadSessions = async () => {
  try {
    const res = await axios.get(`${API}/chat/sessions`, {
      params: { user_id: sellerId.value, type: 'seller' },
      headers: { Authorization: `Bearer ${token.value}` }
    })
    sessions.value = res.data
  } catch (err) {
    console.error('❌ Lỗi load sessions:', err)
  }
}

const loadMessages = async (sessionId) => {
  try {
    const res = await axios.get(`${API}/chat/messages/${sessionId}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    const newMessages = res.data

    // 👉 Đảm bảo thứ tự từ cũ -> mới
    messages.value = newMessages.sort(
      (a, b) => new Date(a.created_at) - new Date(b.created_at)
    )

    const shouldScroll = newMessages.length !== lastMessageCount
    lastMessageCount = newMessages.length
    if (shouldScroll) scrollToBottom()
  } catch (err) {
    console.error('❌ Lỗi load messages:', err)
  }
}


// --- UI HANDLERS ---
const scrollToBottom = () => {
  nextTick(() => {
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
  })
}

const handleFile = (e) => {
  const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'))
  form.value.file = files
  imagePreview.value = files.map(file => URL.createObjectURL(file))
}

const removeImage = (i) => {
  form.value.file.splice(i, 1)
  imagePreview.value.splice(i, 1)
}

const selectSession = async (session) => {
  selectedSession.value = session
  await loadMessages(session.id)
}

const pendingId = () => 'pending_' + Date.now()

const sendMessage = async () => {
  if (!selectedSession.value) return

  const hasText = form.value.message.trim() !== ''
  const hasFiles = form.value.file.length > 0
  if (!hasText && !hasFiles) return

  const tempId = pendingId()
  const newMsg = {
    id: tempId,
    sender_type: 'seller',
    message: form.value.message,
    attachments: imagePreview.value.map((img, i) => ({
      id: `temp_${i}`,
      file_type: 'image',
      file_url: img
    })),
    pending: true
  }

  // Đảm bảo messages là mảng
  if (!Array.isArray(messages.value)) {
    messages.value = []
  }

  messages.value.push(newMsg)
  scrollToBottom()

  const payload = new FormData()
  payload.append('session_id', selectedSession.value.id)
  payload.append('sender_id', sellerId.value)
  payload.append('receiver_id', selectedSession.value.user.id)
  payload.append('sender_type', 'seller')
  payload.append('message_type', hasFiles ? 'image' : 'text')
  if (hasText) payload.append('message', form.value.message)
  form.value.file.forEach(file => payload.append('file[]', file))

  try {
    const { data } = await axios.post(`${API}/chat/send-message`, payload, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    // Xoá tin nhắn tạm
    messages.value = messages.value.filter(m => m.id !== tempId)

    // Kiểm tra dữ liệu và thêm tin nhắn thực tế
    if (data && typeof data.chat_message === 'object') {
      const finalMessage = {
          ...data.chat_message,
          attachments: data.chat_message.attachments ?? []
        }
messages.value.push(finalMessage)
    } else {
      console.warn('⚠️ Phản hồi không hợp lệ:', data)
    }

    scrollToBottom()
  } catch (err) {
    // Đảm bảo messages là mảng trước khi map
    if (!Array.isArray(messages.value)) {
      messages.value = []
    }

    messages.value = messages.value.map(m =>
      m.id === tempId ? { ...m, error: true } : m
    )
    console.error('❌ Lỗi gửi tin nhắn:', err)
  }

  // Reset form
  form.value.message = ''
  form.value.file = []
  imagePreview.value = []
  if (fileInput.value) fileInput.value.value = ''
}



const openContext = (id, e) => {
  contextMenu.value = { open: true, messageId: id, x: e.clientX, y: e.clientY }
}

const closeContext = () => {
  contextMenu.value = { open: false, messageId: null, x: 0, y: 0 }
}

// --- EDIT + REVOKE ---
const editMessage = async (msg) => {
  const newContent = prompt('✏️ Nhập nội dung mới:', msg.message)
  if (newContent && newContent.trim()) {
    try {
      const res = await axios.put(`${API}/chat/messages/${msg.id}/action`, {
        action: 'edit', message: newContent
      }, { headers: { Authorization: `Bearer ${token.value}` } })
      if (res.data.success) await loadMessages(selectedSession.value.id)
    } catch (err) {
      alert('❌ Không thể sửa tin nhắn.'); console.error(err)
    }
  }
  closeContext()
}

const revokeMessage = async (msg) => {
  if (!confirm('🗑️ Bạn có chắc muốn thu hồi không?')) return
  try {
    const res = await axios.put(`${API}/chat/messages/${msg.id}/action`, {
      action: 'revoke'
    }, { headers: { Authorization: `Bearer ${token.value}` } })
    if (res.data.success) await loadMessages(selectedSession.value.id)
  } catch (err) {
    alert('❌ Không thể thu hồi.'); console.error(err)
  }
  closeContext()
}

// --- Lifecycle ---
onMounted(async () => {
  window.addEventListener('resize', () => isMobile.value = window.innerWidth < 640)
  window.addEventListener('click', closeContext)
  isMobile.value = window.innerWidth < 640

  await loadSellerInfo()
  if (sellerId.value) {
    await loadSessions()
    if (sessions.value.length) {
      selectedSession.value = sessions.value[0]
      await loadMessages(selectedSession.value.id)
    }

    polling = setInterval(async () => {
      if (selectedSession.value) await loadMessages(selectedSession.value.id)
    }, 5000) // 👉 Delay 5s để tránh quá tải
  }
})

onUnmounted(() => {
  clearInterval(polling)
  window.removeEventListener('click', closeContext)
  window.removeEventListener('resize', () => {})
})
</script>


<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
.animate-shake {
  animation: shake 0.5s;
}

@media (max-width: 640px) {
  aside {
    transform: translateX(0);
  }
}
</style>