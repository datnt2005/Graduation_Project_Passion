<template>
  <div class="flex flex-col sm:flex-row h-[100dvh] bg-white">
    <!-- Sidebar Danh sách người dùng -->
    <aside
      :class="[
        'bg-gray-100 border-r sm:w-[300px] w-full sm:static fixed inset-0 z-40 transition-transform duration-300',
        selectedSession && isMobile ? '-translate-x-full' : 'translate-x-0'
      ]"
    >
      <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
        <h2 class="font-bold text-base">📋 Danh sách khách</h2>
      </div>
      <ul class="divide-y">
        <li
          v-for="session in sessions"
          :key="session.id"
          @click="selectSession(session)"
          class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-blue-50 transition"
          :class="{ 'bg-blue-100': selectedSession?.id === session.id }"
        >
          <img
            :src="getAvatarUrl(session.user?.avatar)"
            class="w-10 h-10 rounded-full object-cover"
            alt="avatar"
          />
          <div class="flex-1 truncate">
            <p class="font-medium text-sm truncate">{{ session.user?.name || 'Người dùng' }}</p>
            <p class="text-xs text-gray-500 truncate">{{ session.messages?.[0]?.message || 'Tin nhắn gần đây...' }}</p>
          </div>
        </li>
      </ul>
    </aside>

    <!-- Khu vực Chat -->
    <section v-if="selectedSession || !isMobile" class="flex-1 flex flex-col items-center bg-white overflow-hidden">
      <div class="w-full max-w-screen-md flex flex-col flex-1 relative">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b bg-white sticky top-0 z-10">
          <div class="flex items-center gap-2">
            <img
              :src="getAvatarUrl(selectedSession?.user?.avatar)"
              class="w-8 h-8 rounded-full object-cover"
              alt="avatar"
            />
            <span class="font-semibold text-sm">{{ selectedSession?.user?.name || 'Người dùng' }}</span>
          </div>
          <button v-if="isMobile" @click="selectedSession = null" class="text-gray-500 hover:text-black">←</button>
        </div>

        <!-- Tin nhắn -->
        <div class="flex-1 p-4 space-y-3 overflow-y-auto" ref="chatBox">
          <!-- Chỉ báo loading -->
          <div v-if="isLoadingMore" class="text-center py-2">
            <span class="loading-spinner text-gray-500 text-sm">Đang tải tin nhắn...</span>
          </div>
          <!-- Danh sách tin nhắn -->
          <div
            v-for="msg in messages"
            :key="msg.id"
            class="flex w-full"
            :class="msg.sender_type === 'seller' ? 'justify-end' : 'justify-start'"
          >
            <div
              class="relative p-3 rounded-xl shadow max-w-[80%] break-words"
              :class="[
                msg.sender_type === 'seller' ? 'bg-[#0084ff] text-white' : 'bg-gray-100 text-black',
                msg.pending ? 'opacity-60' : '',
                msg.error ? 'border border-red-500' : ''
              ]"
              @contextmenu.prevent="openContext(msg.id, $event)"
            >
              <!-- Nội dung -->
              <p
                class="whitespace-pre-wrap text-sm leading-relaxed"
                :class="msg.message_type === 'revoked' ? 'italic text-gray-300' : ''"
              >
                {{ msg.message }}
                <span v-if="msg.message_type === 'edited'" class="text-xs italic ml-1 opacity-70">(đã sửa)</span>
              </p>

              <!-- File/ảnh -->
              <div v-if="msg.message_type !== 'revoked' && msg.attachments?.length" class="flex flex-wrap gap-2 mt-2">
                <template v-for="file in msg.attachments" :key="file.id">
                  <img
                    v-if="file.file_type === 'image'"
                    :src="file.file_url"
                    class="w-[80px] h-[80px] object-cover rounded border shadow"
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

              <div v-if="msg.pending" class="text-xs mt-1 italic text-white/70">Đang gửi...</div>
            </div>

            <!-- Nút menu chỉnh sửa -->
            <button
              v-if="msg.sender_type === 'seller' && msg.message_type !== 'revoked'"
              @click.stop="openContext(msg.id, $event)"
              class="text-gray-400 hover:text-gray-600 text-lg px-1"
            >
              ⋮
            </button>

            <div
              v-if="contextMenu.open && contextMenu.messageId === msg.id"
              :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
              class="fixed z-50 bg-white border rounded shadow-md w-36 text-sm"
            >
              <button @click="editMessage(msg)" class="block w-full text-left px-3 py-2 hover:bg-gray-100">
                ✏️ Sửa
              </button>
              <button @click="revokeMessage(msg)" class="block w-full text-left px-3 py-2 hover:bg-gray-100">
                🗑️ Thu hồi
              </button>
            </div>
          </div>
        </div>

        <!-- Form gửi -->
        <form @submit.prevent="sendMessageDebounced" class="border-t p-3 flex flex-col gap-3 bg-white">
          <!-- Preview ảnh -->
          <div v-if="imagePreview.length" class="flex flex-wrap gap-2">
            <div v-for="(img, i) in imagePreview" :key="i" class="relative w-[70px] h-[70px] group">
              <img :src="img" class="w-full h-full object-cover rounded border shadow" />
              <button
                type="button"
                @click="removeImage(i)"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
              >
                ×
              </button>
            </div>
          </div>

          <!-- Input -->
          <div class="flex items-center gap-2">
            <input
              v-model="form.message"
              type="text"
              placeholder="Aa..."
              class="flex-1 border rounded-full px-4 py-2 text-sm"
            />
            <input ref="fileInput" type="file" multiple accept="image/*" @change="handleFile" class="hidden" />
            <button type="button" @click="fileInput.click()" class="text-xl">📎</button>
            <button
              type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm disabled:bg-gray-400"
              :disabled="(!form.message.trim() && !form.file.length) || isSending"
            >
              Gửi
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash'

definePageMeta({ layout: 'default-seller' })

// --- Biến trạng thái
const isMobile = ref(false)
const token = ref('')
const sellerId = ref(null)
const seller = ref({})
const sessions = ref([])
const selectedSession = ref(null)
const form = ref({ message: '', file: [] })
const imagePreview = ref([])
const fileInput = ref(null)
let polling = null
const contextMenu = ref({ open: false, messageId: null, x: 0, y: 0 })
const isSending = ref(false)
const chatBox = ref(null)
const messages = ref([])
const lastMessageId = ref(null)
const isLoadingMore = ref(false)
const noMoreMessages = ref(false)

// --- Config
const config = useRuntimeConfig()
const API = config.public.apiBaseUrl
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000').replace(/\/?$/, '/')
const DEFAULT_AVATAR = 'https://Obama-3fc809b4396849cba1c342a5b9f50be9.r2.dev/avatars/default.jpg'

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

const loadMessages = async (sessionId, isScrollUp = false) => {
  if (isScrollUp && (isLoadingMore.value || noMoreMessages.value)) return

  try {
    if (isScrollUp) isLoadingMore.value = true

    const res = await axios.get(`${API}/chat/messages/${sessionId}`, {
      params: isScrollUp && lastMessageId.value ? { before_id: lastMessageId.value } : {},
      headers: { Authorization: `Bearer ${token.value}` }
    })

    const sortedMessages = res.data.sort(
      (a, b) => new Date(a.created_at) - new Date(b.created_at)
    )

    if (!sortedMessages.length && isScrollUp) {
      noMoreMessages.value = true
    }

    if (isScrollUp) {
      const prevScrollHeight = chatBox.value.scrollHeight
      messages.value.unshift(...sortedMessages)
      await nextTick()
      const newScrollHeight = chatBox.value.scrollHeight
      chatBox.value.scrollTop += newScrollHeight - prevScrollHeight
    } else {
      messages.value = sortedMessages
      nextTick(scrollToBottom)
    }

    if (messages.value.length) {
      lastMessageId.value = messages.value[0].id
    }
  } catch (err) {
    console.error('❌ Lỗi load messages:', err)
  } finally {
    isLoadingMore.value = false
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
  messages.value = []
  lastMessageId.value = null
  noMoreMessages.value = false
  await loadMessages(session.id)
}

const pendingId = () => 'pending_' + Date.now()

const moveSessionToTop = (sessionId) => {
  const index = sessions.value.findIndex(s => s.id === sessionId)
  if (index !== -1) {
    const [item] = sessions.value.splice(index, 1)
    sessions.value.unshift(item)
  }
}

const sendMessage = async () => {
  if (!selectedSession.value) return

  const hasText = form.value.message.trim() !== ''
  const hasFiles = form.value.file.length > 0
  if (!hasText && !hasFiles) return
  if (isSending.value) return

  isSending.value = true
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

  messages.value.push(newMsg)
  moveSessionToTop(selectedSession.value.id)
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

    messages.value = messages.value.filter(m => m.id !== tempId)

    if (data && typeof data.chat_message === 'object') {
      messages.value.push({
        ...data.chat_message,
        attachments: data.chat_message.attachments ?? []
      })
    } else {
      console.warn('⚠️ Phản hồi không hợp lệ:', data)
    }
  } catch (err) {
    messages.value = messages.value.map(m =>
      m.id === tempId ? { ...m, error: true } : m
    )
    console.error('❌ Lỗi gửi tin nhắn:', err)
  } finally {
    isSending.value = false
    form.value.message = ''
    form.value.file = []
    imagePreview.value = []
    if (fileInput.value) fileInput.value.value = ''
  }
}

// ✅ Debounce gửi
const sendMessageDebounced = debounce(sendMessage, 800)

const openContext = (id, e) => {
  contextMenu.value = { open: true, messageId: id, x: e.clientX, y: e.clientY }
}
const closeContext = () => {
  contextMenu.value = { open: false, messageId: null, x: 0, y: 0 }
}

// --- EDIT / REVOKE ---
const editMessage = async (msg) => {
  const newContent = prompt('✏️ Nhập nội dung mới:', msg.message)
  if (newContent && newContent.trim()) {
    try {
      const res = await axios.put(`${API}/chat/messages/${msg.id}/action`, {
        action: 'edit',
        message: newContent
      }, { headers: { Authorization: `Bearer ${token.value}` } })
      if (res.data.success) await loadMessages(selectedSession.value.id)
    } catch (err) {
      alert('❌ Không thể sửa tin nhắn.')
      console.error(err)
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
    alert('❌ Không thể thu hồi.')
    console.error(err)
  }
  closeContext()
}

const handleScroll = () => {
  if (!chatBox.value || !selectedSession.value) return
  if (chatBox.value.scrollTop < 80) {
    loadMessages(selectedSession.value.id, true) // load cũ hơn
  }
}

// --- Lifecycle ---
onMounted(async () => {
  window.addEventListener('resize', () => isMobile.value = window.innerWidth < 640)
  window.addEventListener('click', closeContext)
  isMobile.value = window.innerWidth < 640
  nextTick(() => {
    if (chatBox.value) {
      chatBox.value.addEventListener('scroll', handleScroll)
    }
  })

  await loadSellerInfo()
  if (sellerId.value) {
    await loadSessions()
    if (sessions.value.length && !selectedSession.value) {
      await selectSession(sessions.value[0])
    }

    polling = setInterval(async () => {
      if (selectedSession.value) await loadMessages(selectedSession.value.id)
    }, 3000)
  }
})

onUnmounted(() => {
  clearInterval(polling)
  window.removeEventListener('click', closeContext)
  window.removeEventListener('resize', () => {})
  if (chatBox.value) {
    chatBox.value.removeEventListener('scroll', handleScroll)
  }
})

// ✅ Watch messages để scroll xuống cuối
watch(messages, async () => {
  if (!isLoadingMore.value) {
    await nextTick()
    if (chatBox.value) {
      chatBox.value.scrollTop = chatBox.value.scrollHeight
    }
  }
}, { deep: true })
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

@keyframes spin {
  to { transform: rotate(360deg); }
}
.loading-spinner::before {
  content: '';
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #ccc;
  border-top-color: #0084ff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}

@media (max-width: 640px) {
  aside {
    transform: translateX(0);
  }
}
</style>