<template>
  <!-- Nút mở chat -->
  <button
    class="fixed bottom-6 right-6 bg-white text-[#1BA0E2] font-bold px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2 border border-[#1BA0E2] hover:bg-[#1BA0E2]/10 z-50"
    :class="{ 'animate-shake': hasNewMessage }"
    @click="open = true; hasNewMessage = false"
  >
    <i class="fas fa-comment-alt text-xl"></i>
    <span>Chat</span>
  </button>

  <!-- Modal Chat -->
  <transition name="fade">
    <div
      v-if="open"
      class="fixed bottom-20 right-6 w-[360px] h-[500px] sm:w-full sm:h-screen sm:bottom-0 sm:right-0 bg-white rounded-lg sm:rounded-none shadow-lg border border-gray-300 z-50 flex flex-col overflow-hidden"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-4 py-2 bg-[#1BA0E2] text-white">
        <h2 class="font-semibold text-base">
          {{ selectedSession ? `Đang chat với: ${selectedSession?.seller?.store_name || 'Cửa hàng'}` : 'Chat với cửa hàng' }}
        </h2>
        <button @click="open = false" class="hover:opacity-80">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Nội dung -->
      <div class="flex-1 flex overflow-hidden" @click="closeContext">
        <!-- Sidebar -->
        <aside class="w-1/3 border-r p-2 bg-gray-50 hidden sm:block">
          <input
            v-model="search"
            type="text"
            placeholder="Tìm cửa hàng"
            class="w-full px-2 py-1 text-sm border rounded mb-2"
          />
          <ul class="space-y-2 overflow-y-auto max-h-[400px] pr-1">
            <li
              v-for="session in filteredSessions"
              :key="session.id"
              @click="selectSession(session)"
              :class="[
                'flex items-center gap-2 p-2 rounded cursor-pointer transition',
                selectedSession?.id === session.id ? 'bg-blue-100' : 'hover:bg-gray-100'
              ]"
            >
              <img
                :src="getAvatarUrl(session.seller?.avatar)"
                class="w-8 h-8 rounded-full object-cover"
                alt="avatar"
              />
              <span class="text-sm truncate">{{ session.seller?.store_name || 'Cửa hàng' }}</span>
            </li>
          </ul>
        </aside>

        <!-- Chat chính -->
        <section class="flex-1 flex flex-col bg-gray-100 overflow-hidden">
        <!-- Danh sách tin nhắn -->
      <div class="flex-1 p-3 space-y-3 overflow-y-auto" ref="chatBox">
        <div
          v-for="msg in messages"
          :key="msg.id"
          class="flex gap-2 items-start"
          :class="msg.sender_type === 'user' ? 'justify-end' : 'justify-start'"
          @contextmenu.prevent="openContext(msg.id, $event)"
        >
          <!-- Nội dung tin nhắn -->
          <div
            class="relative p-2 rounded-lg shadow max-w-[85%] sm:max-w-[70%]"
            :class="[
              msg.sender_type === 'user' ? 'bg-blue-500 text-white' : 'bg-white text-gray-800',
              msg.pending ? 'opacity-60' : '',
              msg.error ? 'border border-red-500' : ''
            ]"
          >
            <!-- Nội dung chính -->
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

            <!-- File đính kèm -->
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

          <!-- Nút 3 chấm -->
          <button
            v-if="msg.sender_type === 'user' && msg.message_type !== 'revoked'"
            @click.stop="openContext(msg.id, $event)"
            class="text-gray-400 hover:text-gray-600 text-lg px-1"
          >
            ⋮
          </button>

          <!-- Menu chỉnh sửa / thu hồi -->
        <div
            v-if="contextMenu.open && contextMenu.messageId === msg.id && msg.sender_type === 'user'"
            class="z-50 bg-white border rounded shadow-md text-sm mt-1"
          >
            <button @click="editMessage(msg)" class="block w-full text-left px-3 py-2 hover:bg-gray-100">✏️ Sửa</button>
            <button @click="revokeMessage(msg)" class="block w-full text-left px-3 py-2 hover:bg-gray-100">🗑️ Thu hồi</button>
          </div>
        </div>
      </div>



          <!-- Gửi tin nhắn -->
          <form @submit.prevent="sendMessage" class="p-3 border-t bg-white flex flex-col gap-3">
            <!-- Preview ảnh -->
            <div v-if="imagePreview.length" class="flex flex-wrap gap-3 px-2">
              <div
                v-for="(img, i) in imagePreview"
                :key="i"
                class="relative w-[70px] h-[70px] group"
              >
                <img
                  :src="img"
                  class="w-full h-full object-cover rounded border border-gray-300 shadow-sm"
                  alt="preview"
                />
                <button
                  type="button"
                  @click="removeImage(i)"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                >×</button>
              </div>
            </div>

            <!-- Nhập tin nhắn -->
            <div class="flex items-center gap-2">
              <input
                v-model="form.message"
                type="text"
                placeholder="Nhập tin nhắn... 😄"
                class="flex-1 border rounded px-3 py-2 text-sm min-w-0"
              />
              <input ref="fileInput" type="file" multiple @change="handleFile" class="hidden" />
              <button type="button" @click="fileInput.click()" class="text-xl">📎</button>
              <button type="submit" class="bg-[#1BA0E2] text-white px-4 py-2 rounded text-sm hover:bg-[#178fca]">
                Gửi
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>
  </transition>
</template>


<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed, watch } from 'vue'
import axios from 'axios'

const open = ref(false)
const form = ref({ message: '', file: [] })
const fileInput = ref(null)
const imagePreview = ref([])
const chatBox = ref(null)
const token = ref('')
const user = ref(null)
const userId = ref(null)
const sessions = ref([])
const messages = ref([])
const selectedSession = ref(null)
const search = ref('')
const hasNewMessage = ref(false)
const contextMenu = ref({ open: false, messageId: null, x: 0, y: 0 })

const config = useRuntimeConfig()
const API = config.public.apiBaseUrl
const DEFAULT_AVATAR = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/avatars/default.jpg'

let polling = null
let lastMessageTimestamp = ref(null) // Lưu timestamp của tin nhắn mới nhất

const getAvatarUrl = (avatar) => {
  if (!avatar) return DEFAULT_AVATAR
  const cleaned = avatar.trim()
  if (cleaned.startsWith('http://') || cleaned.startsWith('https://')) return cleaned
  return `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${cleaned}`
}

const filteredSessions = computed(() => {
  if (!search.value.trim()) return sessions.value
  return sessions.value.filter(session =>
    session.seller?.store_name?.toLowerCase().includes(search.value.toLowerCase())
  )
})

const loadUserInfo = async () => {
  const storedToken = localStorage.getItem('access_token')
  try {
    token.value = storedToken
    const { data } = await axios.get(`${API}/me`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    user.value = data?.data || null
    userId.value = user.value?.id
  } catch (error) {
    console.error('❌ Lỗi khi lấy user:', error)
  }
}

const loadSessions = async () => {
  try {
    const { data } = await axios.get(`${API}/chat/sessions`, {
      params: { user_id: userId.value, type: 'user' },
      headers: { Authorization: `Bearer ${token.value}` }
    })
    sessions.value = data || []
    if (!selectedSession.value && sessions.value.length) {
      selectedSession.value = sessions.value[0]
      await loadMessages(selectedSession.value.id)
    }
  } catch (error) {
    console.error('❌ Lỗi load sessions:', error?.response?.data || error.message)
  }
}

let lastLoadedSessionId = null

const loadMessages = async (sessionId) => {
  try {
    const params = lastMessageTimestamp.value
      ? { last_timestamp: lastMessageTimestamp.value }
      : {}
    const { data } = await axios.get(`${API}/chat/messages/${sessionId}`, {
      headers: { Authorization: `Bearer ${token.value}` },
      params
    })

    if (data.length) {
      const newMessages = data.filter(msg => !messages.value.some(m => m.id === msg.id))
      if (newMessages.length > 0) {
        messages.value.push(...newMessages)
        lastMessageTimestamp.value = data[data.length - 1].created_at
        hasNewMessage.value = true
        await nextTick()
        if (chatBox.value) {
          chatBox.value.scrollTop = chatBox.value.scrollHeight
        }
      }
    }
  } catch (error) {
    console.error('❌ Lỗi load messages:', error)
  }
}


const selectSession = async (session) => {
  selectedSession.value = session
  lastMessageTimestamp.value = null // Reset timestamp khi đổi session
  await loadMessages(session.id)
}

const handleFile = (e) => {
  const files = Array.from(e.target.files)
  form.value.file = files
  imagePreview.value = files.map(file => URL.createObjectURL(file))
}

const removeImage = (index) => {
  form.value.file.splice(index, 1)
  imagePreview.value.splice(index, 1)
}

const sendMessage = async () => {
  if (!selectedSession.value) return
  const hasText = form.value.message.trim() !== ''
  const hasFiles = form.value.file.length > 0
  if (!hasText && !hasFiles) return

  const tempId = 'pending_' + Date.now()
  const newMsg = {
    id: tempId,
    sender_type: 'user',
    message: form.value.message,
    attachments: imagePreview.value.map((img, i) => ({
      id: i,
      file_type: 'image',
      file_url: img
    })),
    pending: true
  }
  messages.value.push(newMsg)
  await nextTick()
  if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight

  const payload = new FormData()
  payload.append('session_id', selectedSession.value.id)
  payload.append('sender_id', userId.value)
  payload.append('receiver_id', selectedSession.value.seller.id)
  payload.append('sender_type', 'user')
  payload.append('message_type', hasFiles ? 'image' : 'text')
  if (form.value.message) payload.append('message', form.value.message)
  form.value.file.forEach(file => payload.append('file[]', file))

  try {
    const { data } = await axios.post(`${API}/chat/send-message`, payload, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    messages.value = messages.value.filter(msg => msg.id !== tempId)
    if (data && data.id) {
      messages.value.push(data)
      lastMessageTimestamp.value = data.created_at || Date.now()
    } else {
      lastLoadedSessionId = null
      await loadMessages(selectedSession.value.id)
    }

    await nextTick()
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
  } catch (err) {
    console.error('❌ Lỗi gửi:', err)
    messages.value = messages.value.map(m => {
      if (m.id === tempId) return { ...m, error: true }
      return m
    })
  }

  form.value.message = ''
  form.value.file = []
  imagePreview.value = []
  fileInput.value.value = ''
}

watch(messages, async () => {
  await nextTick()
  if (chatBox.value) {
    chatBox.value.scrollTop = chatBox.value.scrollHeight
  }
}, { deep: true })

function onNewIncomingMessage(msg) {
  if (!messages.value.some(m => m.id === msg.id)) {
    messages.value.push(msg)
    lastMessageTimestamp.value = msg.created_at || Date.now()
    hasNewMessage.value = true
    nextTick(() => {
      if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight
      }
    })
  }
}

const openContext = (id, e) => {
  contextMenu.value = { open: true, messageId: id, x: e.clientX, y: e.clientY }
}

const closeContext = () => {
  contextMenu.value = { open: false, messageId: null, x: 0, y: 0 }
}

const editMessage = async (msg) => {
  const newContent = prompt('✏️ Nhập nội dung mới:', msg.message)
  if (newContent && newContent.trim()) {
    try {
      const res = await axios.put(`${API}/chat/messages/${msg.id}/action`, {
        action: 'edit',
        message: newContent
      }, { headers: { Authorization: `Bearer ${token.value}` } })
      if (res.data.success) {
        lastLoadedSessionId = null
        await loadMessages(selectedSession.value.id)
      }
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
    if (res.data.success) {
      lastLoadedSessionId = null
      await loadMessages(selectedSession.value.id)
    }
  } catch (err) {
    alert('❌ Không thể thu hồi.')
    console.error(err)
  }
  closeContext()
}

onMounted(() => {
  // Giảm thời gian polling xuống 1,5 giây
  polling = setInterval(async () => {
    if (selectedSession.value) {
      await loadMessages(selectedSession.value.id)
    }
  }, 1500) // 1500ms = 1,5 giây
})

onUnmounted(() => {
  clearInterval(polling)
})

onMounted(async () => {
  await loadUserInfo()
  if (userId.value) await loadSessions()
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
</style>