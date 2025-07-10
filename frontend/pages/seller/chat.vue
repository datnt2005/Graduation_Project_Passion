<template>
  <div class="h-screen bg-white text-[#212121] overflow-hidden">
    <div class="flex h-screen">
      <!-- Sidebar -->
      <aside class="w-full sm:w-1/3 md:w-1/4 border-r border-gray-200 bg-white">
        <div
          class="p-4 font-bold text-lg border-b border-gray-200 text-[#189EFF]"
        >
          💬 Tin nhắn
        </div>
        <ul class="overflow-y-auto h-full">
          <li
            v-for="(session, i) in chatSessions"
            :key="session.id || i"
            @click="selectSession(session)"
            class="p-4 hover:bg-[#F2F9FF] cursor-pointer border-b border-gray-100"
          >
            <div class="flex justify-between items-center">
              <span class="font-medium text-sm">{{
                session.user?.name || "Người dùng"
              }}</span>
              <span class="text-xs text-gray-400">
                {{ formatTime(session.last_message_at) }}
              </span>
            </div>
            <div class="text-xs text-gray-500 truncate">
              {{ session.last_message || "..." }}
            </div>
          </li>
        </ul>
      </aside>

      <!-- Chat -->
      <main id="chat" class="flex-1 flex flex-col">
        <!-- Header -->
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 bg-white"
        >
          <div class="flex items-center gap-2">
            <button
              class="sm:hidden text-[#189EFF] hover:text-[#0f89e0]"
              @click="toggleSidebar"
            >
              🔙
            </button>
            <div class="font-semibold text-[#212121]">{{ seller?.name }}</div>
          </div>
        </div>

        <!-- Messages -->
        <div
          ref="chatContainer"
          class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#F9FAFB] text-[#212121]"
        >
          <div class="text-center text-xs text-gray-500">Hôm nay</div>

          <div
            v-for="msg in currentMessages"
            :key="msg.id"
            :class="[
              'flex items-end gap-2',
              msg.sender_type === 'seller' ? 'justify-end' : '',
            ]"
          >
            <!-- Avatar người dùng -->
            <img
              v-if="msg.sender_type === 'user'"
              src="https://i.pravatar.cc/32"
              class="w-8 h-8 rounded-full"
              alt="User avatar"
            />

            <div
              :class="
                msg.sender_type === 'seller' ? 'flex flex-col items-end' : ''
              "
            >
              <!-- Tin nhắn chữ (dù là loại text hay image nhưng có chữ thì vẫn hiển thị) -->
              <div
                v-if="msg.message"
                :class="[
                  'px-4 py-2 rounded-2xl max-w-xs break-words mb-1',
                  msg.sender_type === 'seller'
                    ? 'bg-[#189EFF] text-white rounded-br-none'
                    : 'bg-gray-100 rounded-bl-none',
                ]"
              >
                {{ msg.message }}
              </div>

              <!-- Tin nhắn ảnh -->
              <div
                v-if="msg.message_type === 'image' && msg.attachments?.length"
                class="flex gap-2 flex-wrap mb-1"
              >
                <div
                  v-for="(attachment, index) in msg.attachments"
                  :key="index"
                  class="w-24 h-24 rounded overflow-hidden cursor-pointer"
                  @click="
                    openImageViewer(attachment.file_url || attachment.url)
                  "
                >
                  <img
                    :src="attachment.file_url || attachment.url"
                    class="w-full h-full object-cover border border-gray-200 rounded"
                    alt="Ảnh đính kèm"
                  />
                </div>
              </div>

              <!-- Trường hợp không có gì -->
              <div
                v-if="
                  !msg.message && (!msg.attachments || !msg.attachments.length)
                "
                class="text-xs text-gray-400 italic"
              >
                [Tin nhắn không xác định]
              </div>

              <!-- Thời gian và trạng thái đã xem -->
              <div
                :class="[
                  'text-xs mt-1',
                  msg.sender_type === 'seller'
                    ? 'text-gray-500 flex items-center gap-1'
                    : 'text-gray-400',
                ]"
              >
                {{
                  new Date(msg.created_at).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                  })
                }}
                <template v-if="msg.sender_type === 'seller'">
                  <span class="text-xs">Đã xem</span>
                </template>
              </div>
            </div>

            <!-- Avatar người bán -->
            <img
              v-if="msg.sender_type === 'seller'"
              src="https://i.pravatar.cc/32?img=5"
              class="w-8 h-8 rounded-full"
              alt="Seller avatar"
            />
          </div>
        </div>

        <!-- Form -->
        <form
          class="p-3 border-t border-gray-200 bg-white flex flex-col gap-2"
          @submit.prevent="sendMessage"
        >
          <div class="flex gap-2 flex-wrap">
            <div
              v-for="(img, i) in selectedImages"
              :key="i"
              class="relative group"
            >
              <img
                :src="img.url"
                class="w-20 h-20 object-cover rounded-lg border border-gray-300"
              />
              <button
                type="button"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center hover:bg-red-600"
                @click="removeImage(i)"
              >
                ×
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <label class="cursor-pointer hover:opacity-80 transition">
              <i class="fa fa-paperclip text-[20px]"></i>
              <input
                type="file"
                multiple
                accept="image/*"
                class="hidden"
                @change="handleFileChange"
              />
            </label>

            <input
              v-model="message"
              type="text"
              placeholder="Nhập tin nhắn..."
              class="flex-1 bg-gray-100 text-[#212121] px-4 py-2 rounded-full text-sm focus:outline-none"
            />
            <button type="button" @click="toggleEmojiPicker" class="text-xl">
              😊
            </button>
            <emoji-picker
              id="emojiPicker"
              class="absolute bottom-16 right-4 hidden z-50"
            ></emoji-picker>
            <button
              type="submit"
              class="bg-[#189EFF] hover:bg-[#0f89e0] px-4 py-2 rounded-full text-white font-medium"
            >
              Gửi
            </button>
          </div>
        </form>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
definePageMeta({
  layout: "default-seller",
});

const selectedImages = ref([]);
const message = ref("");
const seller = ref({});
const chatSessions = ref([]);
const currentMessages = ref([]);
const selectedSession = ref(null);
const chatContainer = ref(null);
const pollingInterval = ref(null);

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const DEFAULT_AVATAR = config.public.mediaBaseUrl + "avatars/default.jpg";

const handleFileChange = (e) => {
  const files = Array.from(e.target.files);
  selectedImages.value.push(
    ...files.map((file) => ({
      file,
      url: URL.createObjectURL(file),
    }))
  );
  e.target.value = "";
};

const removeImage = (index) => {
  selectedImages.value.splice(index, 1);
};

const toggleSidebar = () => {
  const sidebar = document.querySelector("aside");
  sidebar.classList.toggle("hidden");
};

const toggleEmojiPicker = () => {
  const picker = document.getElementById("emojiPicker");
  picker?.classList.toggle("hidden");
};

onMounted(async () => {
  if (!customElements.get("emoji-picker")) {
    await import("emoji-picker-element");
  }

  const emojiPicker = document.getElementById("emojiPicker");
  emojiPicker?.addEventListener("emoji-click", (e) => {
    message.value += e.detail.unicode;
  });

  document.addEventListener("click", (e) => {
    if (
      !emojiPicker.contains(e.target) &&
      !e.target.closest("button")?.innerText.includes("😊")
    ) {
      emojiPicker?.classList.add("hidden");
    }
  });

  const token = localStorage.getItem("access_token");
  if (!token) return;

  try {
    const sellerRes = await fetch(`${API}/sellers/seller/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const dataSeller = await sellerRes.json();
    seller.value = dataSeller?.seller || {};

    if (!seller.value?.id) return;

    const sessionsRes = await fetch(
      `${API}/chat/sessions?user_id=${seller.value.id}&type=seller`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const sessionsData = await sessionsRes.json();
    chatSessions.value = Array.isArray(sessionsData)
      ? sessionsData
      : sessionsData?.data || [];
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu seller hoặc sessions:", error);
  }
});

async function selectSession(session) {
  stopPollingMessages();
  selectedSession.value = session;
  const token = localStorage.getItem("access_token");
  const res = await fetch(`${API}/chat/messages/${session.id}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  const data = await res.json();
  currentMessages.value = data?.data || [];
  startPollingMessages();
  nextTick(scrollToBottom);
}

function formatTime(ts) {
  if (!ts) return "";
  const date = new Date(ts);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

async function sendMessage() {
  const text = message.value.trim();
  const hasImages = selectedImages.value.length > 0;
  if (!text && !hasImages) return;

  const token = localStorage.getItem("access_token");
  if (!token || !seller.value?.id || !selectedSession.value?.id) return;

  const formData = new FormData();
  formData.append("session_id", selectedSession.value.id);
  formData.append("sender_id", seller.value.id);
  formData.append("sender_type", "seller");
  formData.append("message_type", hasImages ? "image" : "text");
  if (text) formData.append("message", text);

  selectedImages.value.forEach((img) => {
    formData.append("file[]", img.file);
  });

  try {
    const res = await fetch(`${API}/chat/message`, {
      method: "POST",
      headers: { Authorization: `Bearer ${token}` },
      body: formData,
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.message);

    const newMessage = {
      id: result.message.id,
      sender_type: "seller",
      message: result.message.message,
      message_type: result.message.message_type,
      created_at: new Date().toISOString(),
      attachments: result.attachments || [],
    };

    if (!currentMessages.value) currentMessages.value = [];
    currentMessages.value.push(newMessage);

    message.value = "";
    selectedImages.value = [];
    nextTick(scrollToBottom);
  } catch (error) {
    console.error("Lỗi khi gửi tin nhắn:", error);
  }
}

function startPollingMessages() {
  if (!selectedSession.value?.id) return;

  pollingInterval.value = setInterval(async () => {
    const token = localStorage.getItem("access_token");
    const res = await fetch(
      `${API}/chat/messages/${selectedSession.value.id}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const data = await res.json();
    currentMessages.value = data?.data || [];
    nextTick(scrollToBottom);
  }, 3000);
}

function stopPollingMessages() {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
    pollingInterval.value = null;
  }
}

const scrollToBottom = () => {
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
  }
};

const openImageViewer = (url) => {
  const imgWindow = window.open("", "_blank");
  imgWindow.document.write(`
    <html>
      <head><title>Xem ảnh</title></head>
      <body style="margin:0;display:flex;justify-content:center;align-items:center;height:100vh;background:#000">
        <img src="${url}" style="max-width:100%;max-height:100%" />
      </body>
    </html>
  `);
};

// dọn sạch khi rời trang
onUnmounted(() => {
  stopPollingMessages();
});
</script>

<style scoped>
@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-5px);
  }
  75% {
    transform: translateX(5px);
  }
}
.animate-shake {
  animation: shake 0.5s;
}
</style>
