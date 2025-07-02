<template>
  <div class="flex flex-col sm:flex-row h-screen bg-white">
    <!-- Sidebar Danh sách khách hàng -->
    <aside
      :class="[
        'bg-gray-50 border-r w-full sm:w-1/4 overflow-y-auto transition-all duration-300',
        selectedSession && isMobile ? 'hidden' : 'block'
      ]"
    >
      <h2 class="text-base font-bold p-4 border-b">📋 Khách hàng</h2>
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
          />
          <span class="truncate text-sm">{{ session.user?.name || 'Người dùng' }}</span>
        </li>
      </ul>
    </aside>

    <!-- Nội dung Chat -->
    <section
      v-if="selectedSession || !isMobile"
      class="flex-1 flex flex-col"
    >
      <!-- Header Chat -->
      <div class="flex items-center justify-between px-4 py-2 border-b bg-white text-sm">
        <div>
          💬 Trò chuyện với: <span class="text-blue-600 font-bold">{{ selectedSession?.user?.name }}</span>
        </div>
        <!-- Nút back trên mobile -->
        <button
          @click="selectedSession = null"
          class="sm:hidden text-gray-600 hover:text-black"
        >
          ← Quay lại
        </button>
      </div>

      <!-- Danh sách tin nhắn -->
      <div class="flex-1 overflow-y-auto p-3 space-y-3 min-h-0 max-h-full" ref="chatBox">
         <div
            v-for="msg in messages"
            :key="msg.id"
            class="flex flex-col space-y-1 relative"
            :class="msg.sender_type === 'seller' ? 'items-end' : 'items-start'"
            >
            <!-- Khung chứa tin nhắn hoặc file -->
            <div class="max-w-[75%] flex flex-col gap-2"
                :class="msg.sender_type === 'seller' ? 'items-end' : 'items-start'">
                
                <!-- Tin nhắn văn bản -->
                <div
                v-if="msg.message"
                class="p-2 rounded-lg shadow"
                :class="[
                    msg.sender_type === 'seller' ? 'bg-blue-500 text-white' : 'bg-white text-gray-800',
                    msg.pending ? 'opacity-60' : '',
                    msg.error ? 'border border-red-500' : ''
                ]"
                >
             <p class="whitespace-pre-line break-words">{{ msg.message }}</p>
    </div>

    <!-- File đính kèm -->
    <div v-if="Array.isArray(msg.attachments) && msg.attachments.length"
         class="flex flex-wrap gap-2">
      <template v-for="file in msg.attachments" :key="file.id">
        <img
          v-if="file.file_type === 'image'"
          :src="file.file_url"
          class="w-[80px] h-[80px] object-cover rounded border border-gray-200 shadow"
          :class="msg.pending ? 'opacity-60' : ''"
        />
        <a
          v-else
          :href="file.file_url"
          target="_blank"
          class="text-blue-500 underline text-sm truncate max-w-[200px]"
        >
          📎 {{ file.file_name }}
        </a>
      </template>
    </div>

    <!-- Trạng thái gửi -->
    <div
      v-if="msg.pending"
      class="text-xs text-gray-500 italic mt-1 ml-1"
    >
      <i class="fas fa-spinner animate-spin mr-1"></i> Đang gửi...
    </div>
  </div>
        </div>
        </div>

    
      <!-- Form gửi tin -->
      <form @submit.prevent="sendMessage"  class="sticky bottom-0 z-10 bg-white border-t p-4 flex flex-col gap-3">  
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
            />
            <button
              type="button"
              @click="removeImage(i)"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
            >×</button>
          </div>
        </div>

        <!-- Nhập và gửi -->
        <div class="flex items-center gap-2">
          <input
            v-model="form.message"
            type="text"
            placeholder="Nhập tin nhắn..."
            class="flex-1 border rounded px-3 py-2 text-sm"
          />
          <input ref="fileInput" type="file" multiple @change="handleFile" class="hidden" />
          <button type="button" @click="fileInput.click()" class="bg-gray-200 px-3 py-2 rounded text-sm">📎</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">Gửi</button>
        </div>
      </form>
    </section>
  </div>
</template>



<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'

definePageMeta({
  layout: 'default-seller'
})
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

const config = useRuntimeConfig()
const API = config.public.apiBaseUrl
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000').replace(/\/?$/, '/')

const DEFAULT_AVATAR = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/avatars/default.jpg'
const getAvatarUrl = (avatar) => {
  if (!avatar) return DEFAULT_AVATAR
  const cleaned = avatar.trim()
  return cleaned.startsWith('http') ? cleaned : mediaBase + cleaned
}

const loadSellerInfo = async () => {
  try {
    const storedToken = localStorage.getItem('access_token')
    if (!storedToken) return alert('Vui lòng đăng nhập')
    token.value = storedToken
    const res = await axios.get(`${API}/sellers/seller/me`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    seller.value = res.data.seller
    sellerId.value = seller.value.id
  } catch (error) {
    console.error('❌ Lỗi khi lấy thông tin người bán:', error)
  }
}

const loadSessions = async () => {
  try {
    const res = await axios.get(`${API}/chat/sessions`, {
      params: { user_id: sellerId.value, type: 'seller' },
      headers: { Authorization: `Bearer ${token.value}` }
    })
    sessions.value = res.data
  } catch (error) {
    console.error('❌ Lỗi load sessions:', error)
  }
}

const loadMessages = async (sessionId) => {
  try {
    const res = await axios.get(`${API}/chat/messages/${sessionId}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    messages.value = res.data

    await nextTick()
    if (chatBox.value) {
      chatBox.value.scrollTop = chatBox.value.scrollHeight
    }
  } catch (error) {
    console.error('❌ Lỗi load messages:', error)
  }
}


const selectSession = async (session) => {
  selectedSession.value = session
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
      id: i,
      file_type: 'image',
      file_url: img
    })),
    pending: true
  }
  messages.value.push(newMsg)
  await nextTick()
  chatBox.value.scrollTop = chatBox.value.scrollHeight

  const payload = new FormData()
  payload.append('session_id', selectedSession.value.id)
  payload.append('sender_id', sellerId.value)
  payload.append('receiver_id', selectedSession.value.user.id)
  payload.append('sender_type', 'seller')
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
    messages.value = messages.value.filter(m => m.id !== tempId)
    messages.value.push(data)
    await nextTick()
    chatBox.value.scrollTop = chatBox.value.scrollHeight
  } catch (err) {
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

let polling = null
onMounted(async () => {
  await loadSellerInfo()
  if (sellerId.value) {
    await loadSessions()
    if (sessions.value.length) {
      selectedSession.value = sessions.value[0]
      await loadMessages(selectedSession.value.id)
    }
  }

  polling = setInterval(async () => {
    if (selectedSession.value) {
      await loadMessages(selectedSession.value.id)
    }
  }, 2000)
})

onUnmounted(() => {
  clearInterval(polling)
})

onMounted(() => {
  const checkWidth = () => {
    isMobile.value = window.innerWidth < 640
  }
  checkWidth()
  window.addEventListener('resize', checkWidth)
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
