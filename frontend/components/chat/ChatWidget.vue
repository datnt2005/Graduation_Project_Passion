<template>
  <div>
    <!-- Nút mở chat -->
    <button
      @click="isOpen = true"
      class="chat-toggle-button fixed bottom-6 right-6 z-50 bg-blue-600 hover:bg-blue-700 text-white rounded-full w-14 h-14 shadow-lg flex items-center justify-center"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.85L3 20l1.25-3.75A8.96 8.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
      </svg>
    </button>

    <!-- Khung chat chính -->
    <transition name="fade">
      <div
        v-if="isOpen"
        ref="chatBoxRef"
        class="fixed bottom-24 right-6 z-50 w-[320px] max-h-[480px] bg-white rounded-xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
      >
        <!-- Header -->
        <div class="bg-blue-600 text-white px-4 py-3 flex justify-between items-center">
          <span class="font-semibold text-base">
            {{ mode ? (mode === 'chatbot' ? 'Chat với Chatbot' : 'Nhân viên hỗ trợ') : 'Hỗ trợ trực tuyến' }}
          </span>
          <div class="flex items-center space-x-2">
            <button v-if="mode" @click="resetChat" class="text-white hover:text-gray-200 text-xs">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3" />
              </svg>
            </button>
            <button @click="isOpen = false" class="text-white hover:text-gray-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Giao diện chọn chế độ -->
        <div v-if="!mode" class="p-4 flex flex-col gap-3">
          <button @click="chooseMode('chatbot')"
            class="w-full py-2 rounded-lg bg-gray-100 hover:bg-gray-200 border text-sm font-medium">
            💬 Chat với Chatbot
          </button>
          <button @click="chooseMode('agent')"
            class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
            👩‍💼 Chat với Nhân viên
          </button>
        </div>

        <!-- Giao diện khung chat -->
        <template v-else>
          <!-- Tin nhắn -->
          <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2 bg-gray-50 text-sm" ref="messageBox">
            <div v-for="(msg, i) in messages" :key="i" class="flex flex-col"
              :class="msg.sender === 'me' ? 'items-end' : 'items-start'">
              <div
                :class="msg.sender === 'me' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'"
                class="px-3 py-2 rounded-lg max-w-[70%]"
              >
                {{ msg.text }}
              </div>
              <span v-if="msg.sender === 'me' && i === lastSeenIndex" class="text-xs text-gray-400 mt-1">Đã xem</span>
            </div>
            <div v-if="isTyping && messages.length && messages[messages.length - 1].sender === 'bot' && !messages[messages.length - 1].text" class="flex justify-start">
              <div class="bg-gray-300 rounded-lg px-3 py-2 max-w-[70%] flex items-center gap-1">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
              </div>
            </div>
          </div>

          <!-- Nhập tin nhắn -->
          <form @submit.prevent="sendMessage" class="flex border-t border-gray-200">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Nhập tin nhắn..."
              class="flex-1 px-4 py-2 text-sm focus:outline-none"
            />
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-r-xl"
            >
              Gửi
            </button>
          </form>
        </template>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue'

const isOpen = ref(false)
const mode = ref(null)
const newMessage = ref('')
const messages = ref([])
const messageBox = ref(null)
const chatBoxRef = ref(null)
const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const isTyping = ref(false)
const isStreaming = ref(false)
const lastSeenIndex = ref(-1)

const chooseMode = (selected) => {
  mode.value = selected
  messages.value = [
    {
      sender: 'bot',
      text: selected === 'chatbot'
        ? 'Tôi là trợ lý ảo của Passion, bạn cần hỗ trợ gì?'
        : 'Chào bạn, vui lòng đợi kết nối với nhân viên hỗ trợ!'
    }
  ]
  lastSeenIndex.value = -1
  nextTick(() => {
    messageBox.value.scrollTop = messageBox.value.scrollHeight
  })
}

const streamReply = async (fullText) => {
  let text = ''
  for (let i = 0; i < fullText.length; i++) {
    text += fullText[i]
    messages.value[messages.value.length - 1].text = text
    await new Promise(resolve => setTimeout(resolve, 10))
    messageBox.value.scrollTop = messageBox.value.scrollHeight
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  const text = newMessage.value
  messages.value.push({ text, sender: 'me' })
  newMessage.value = ''
  await nextTick(() => {
    messageBox.value.scrollTop = messageBox.value.scrollHeight
  })

  if (mode.value === 'chatbot') {
    isTyping.value = true
    isStreaming.value = true
    messages.value.push({ text: '', sender: 'bot' })

    try {
      const res = await $fetch(`${API}/chatbot`, {
        method: 'POST',
        body: { message: text }
      })
      const reply = res.choices?.[0]?.message?.content || 'Chatbot không phản hồi.'
      await streamReply(reply.trim())
      lastSeenIndex.value = messages.value.findIndex(m => m.sender === 'me')
    } catch (err) {
      messages.value.pop()
      messages.value.push({ text: 'Lỗi khi kết nối Chatbot (Laravel API)!', sender: 'bot' })
    } finally {
      isTyping.value = false
      isStreaming.value = false
      await nextTick(() => {
        messageBox.value.scrollTop = messageBox.value.scrollHeight
      })
    }
  }
}

const resetChat = () => {
  mode.value = null
  messages.value = []
  lastSeenIndex.value = -1
}

const handleClickOutside = (event) => {
  const target = event.target
  const chatBox = chatBoxRef.value
  const chatButton = document.querySelector('.chat-toggle-button')
  const clickedOutsideChat = chatBox && !chatBox.contains(target)
  const clickedButton = chatButton && chatButton.contains(target)
  if (isOpen.value && clickedOutsideChat && !clickedButton) {
    isOpen.value = false
    mode.value = null
    messages.value = []
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
@keyframes pulse {
  0% { opacity: 0.5; }
  50% { opacity: 1; }
  100% { opacity: 0.5; }
}
@keyframes blink {
  0%, 80%, 100% { opacity: 0.2; }
  40% { opacity: 1; }
}
.typing-dot {
  width: 6px;
  height: 6px;
  background-color: #4b5563;    
  border-radius: 50%;
  animation: blink 1.4s infinite both;
}
.typing-dot:nth-child(2) {
  animation-delay: 0.2s;
}
.typing-dot:nth-child(3) {
  animation-delay: 0.4s;
}
</style>

