<template>
  <div class="h-screen bg-white text-[#212121] overflow-hidden">
    <div class="flex h-screen">
      <!-- Sidebar -->
      <aside class="w-full sm:w-1/3 md:w-1/4 border-r border-gray-200 bg-white">
        <div
          class="p-4 font-bold text-lg border-b border-gray-200 text-[#189EFF]"
        >
          Tin nhắn
        </div>
        <div
          v-if="!selectedSession"
          class="flex-1 flex items-center justify-center text-gray-400 italic"
        >
          Hãy chọn một cuộc trò chuyện để bắt đầu
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

        <!-- Messages -->
        <div
          v-if="loadingMessages"
          class="text-gray-500 p-4 text-center italic"
        >
          Đang tải cuộc trò chuyện...
        </div>
        <div
          v-else-if="isLoadingMore"
          class="text-gray-500 p-4 text-center italic"
        >
          Đang tải thêm tin nhắn...
        </div>
        <div
          ref="chatContainer"
          class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#F9FAFB] text-[#212121]"
        >
          <TransitionGroup
            name="message"
            tag="div"
            @after-enter="scrollToBottom"
          >
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
                alt="Ảnh đại diện người dùng"
              />

              <div
                :class="
                  msg.sender_type === 'seller' ? 'flex flex-col items-end' : ''
                "
              >
                <!-- Text -->
                <div
                  v-if="msg.message"
                  :class="[
                    'px-4 py-2 rounded-2xl max-w-md break-words mb-1',
                    msg.sender_type === 'seller'
                      ? 'bg-[#189EFF] text-white rounded-br-none'
                      : 'bg-gray-100 rounded-bl-none',
                  ]"
                >
                  {{ msg.message }}
                </div>

                <!-- Image -->
                <div
                  v-if="msg.message_type === 'image' && msg.attachments?.length"
                  class="flex gap-2 flex-wrap mb-1"
                >
                  <div
                    v-for="(attachment, index) in msg.attachments"
                    :key="index"
                    class="w-24 h-24 rounded overflow-hidden cursor-pointer relative"
                    @click="
                      openImageViewer(attachment.file_url || attachment.url)
                    "
                  >
                    <img
                      :src="attachment.file_url || attachment.url"
                      class="w-full h-full object-cover border border-gray-200 rounded aspect-square"
                      alt="Ảnh đính kèm"
                      :class="{
                        'opacity-50 grayscale animate-pulse': attachment.temp,
                      }"
                    />
                    <div
                      v-if="attachment.temp"
                      class="absolute inset-0 flex items-center justify-center"
                    >
                      <svg
                        class="w-6 h-6 text-white animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        ></circle>
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v8z"
                        ></path>
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- Product -->
                <a
                  v-if="msg.message_type === 'product'"
                  :href="msg.attachments?.[0]?.meta_data?.productLink"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="block bg-[#F7F7F7] rounded-lg p-3 text-sm no-underline mb-1 max-w-xs"
                >
                  <div class="mb-2 text-[#555] font-medium">
                    Bạn đang trao đổi với Người bán về sản phẩm này
                  </div>
                  <div
                    class="flex border rounded overflow-hidden bg-white hover:shadow-md transition"
                  >
                    <img
                      :src="msg.attachments?.[0]?.meta_data?.file_url"
                      alt="Ảnh sản phẩm"
                      class="w-24 h-24 object-cover border-r"
                    />
                    <div class="flex-1 p-2 overflow-hidden">
                      <div class="font-semibold text-[#212121] line-clamp-2">
                        {{
                          msg.attachments?.[0]?.meta_data?.name || "[Sản phẩm]"
                        }}
                      </div>
                      <div class="mt-1 flex flex-wrap items-center gap-1">
                        <span
                          v-if="msg.attachments?.[0]?.meta_data?.original_price"
                          class="text-gray-400 line-through text-xs"
                        >
                          {{
                            formatPrice(
                              msg.attachments[0].meta_data.original_price
                            )
                          }}
                        </span>
                        <span
                          v-if="msg.attachments?.[0]?.meta_data?.price"
                          class="text-[#FF0000] font-semibold"
                        >
                          {{ formatPrice(msg.attachments[0].meta_data.price) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </a>

                <!-- Không có nội dung -->
                <div
                  v-if="
                    !msg.message &&
                    (!msg.attachments || !msg.attachments.length)
                  "
                  class="text-xs text-gray-400 italic"
                >
                  [Tin nhắn không xác định]
                </div>

                <!-- Thời gian gửi -->
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
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <!-- Form gửi tin -->
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

          <div class="flex items-center gap-2 relative">
            <label class="cursor-pointer hover:opacity-80 transition">
              <i class="fa fa-paperclip text-[20px]"></i>
              <input
                type="file"
                multiple
                accept="image/*"
                class="hidden"
                @change="handleFileChange"
                ref="fileInput"
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

    <!-- Modal xem ảnh -->
    <Transition name="fade">
      <div
        v-if="imageViewer.visible"
        class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
        @click.self="closeImageViewer"
      >
        <div class="relative max-w-[90vw] max-h-[90vh]">
          <img
            :src="imageViewer.url"
            alt="Xem ảnh"
            class="max-w-full max-h-[90vh] object-contain rounded shadow-xl"
          />
          <button
            class="absolute top-2 right-2 bg-gray-800 bg-opacity-50 text-white text-xl font-bold w-8 h-8 rounded-full flex items-center justify-center hover:bg-opacity-75 transition"
            @click="closeImageViewer"
          >
            ✕
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, onUnmounted } from "vue";

definePageMeta({
  layout: "default-seller",
});

const selectedImages = ref([]);
const message = ref("");
const seller = ref({});
const chatSessions = ref([]);
const isLoadingMessages = ref(false);

const selectedSession = ref(null);
const chatContainer = ref(null);
const pollingInterval = ref(null);
const currentMessages = ref([]);
const fileInput = ref(null);
const isSending = ref(false);
let lastPollingSessionId = null;
const page = ref(1);
const limit = 20;
const hasMore = ref(true);
const isLoadingMore = ref(false);
const imageViewer = ref({
  visible: false,
  url: null,
});

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

const handleSelectSession = async (session_id) => {
  loadingMessages.value = true;
  currentMessages.value = [];

  try {
    const res = await axios.get(`/api/messages/${session_id}`);
    currentMessages.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loadingMessages.value = false;
  }
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

const openImageViewer = (url) => {
  if (!url) {
    console.error("URL ảnh không hợp lệ:", url);
    return;
  }
  imageViewer.value.visible = true;
  imageViewer.value.url = url;
};

const closeImageViewer = () => {
  imageViewer.value.visible = false;
  imageViewer.value.url = null;
};

const handleEscKey = (event) => {
  if (event.key === "Escape" && imageViewer.value.visible) {
    closeImageViewer();
  }
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

  window.addEventListener("keydown", handleEscKey);

  const token = localStorage.getItem("access_token");
  if (!token) {
    console.error("Không tìm thấy access_token");
    return;
  }

  try {
    const sellerRes = await fetch(`${API}/sellers/seller/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    // console.log("sellerRes status:", sellerRes.status);
    if (!sellerRes.ok) throw new Error("Không thể lấy dữ liệu seller");
    const dataSeller = await sellerRes.json();
    // console.log("dataSeller:", dataSeller);
    seller.value = dataSeller?.seller || {};

    if (!seller.value?.id) {
      console.error("Không tìm thấy seller.id");
      return;
    }

    const sessionsRes = await fetch(
      `${API}/chat/sessions?user_id=${seller.value.id}&type=seller`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    // console.log("sessionsRes status:", sessionsRes.status);
    const sessionsData = await sessionsRes.json();
    // console.log("sessionsData:", sessionsData);
    if (!sessionsRes.ok) throw new Error("Không thể lấy danh sách session");
    chatSessions.value = Array.isArray(sessionsData)
      ? sessionsData
      : sessionsData?.data || sessionsData?.sessions || [];
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu:", error.message);
  }
});

async function selectSession(session) {
  selectedSession.value = session;

  const token = localStorage.getItem("access_token");
  if (!token || !session?.id) {
    console.error("Thiếu token hoặc session.id");
    return;
  }

  isLoadingMessages.value = true;

  try {
    const res = await fetch(
      `${API}/chat/messages/${session.id}?page=last&limit=20`,
      {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const data = await res.json();
    // console.log("messagesData:", data);
    // console.log("response status:", res.status);

    if (!res.ok) throw new Error(data?.message || "Không thể lấy tin nhắn");

    currentMessages.value = Array.isArray(data?.data)
      ? data.data
      : data?.messages || [];

    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error("Lỗi khi chọn session:", error.message);
  } finally {
    isLoadingMessages.value = false;
  }
}

onUnmounted(() => {
  stopPollingMessages();
  window.removeEventListener("keydown", handleEscKey);
});

function formatTime(ts) {
  if (!ts) return "";
  const date = new Date(ts);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function formatPrice(price) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price);
}

async function sendMessage() {
  if (isSending.value) return;
  isSending.value = true;
  const text = message.value.trim();
  const hasImages = selectedImages.value.length > 0;
  if (!text && !hasImages) return;

  const token = localStorage.getItem("access_token");
  if (!token || !seller.value?.id || !selectedSession.value?.id) return;

  const tempId = "tem-" + Date.now();

  const tempMessage = {
    id: tempId,
    sender_type: "seller",
    message: text || "",
    message_type: hasImages ? "image" : "text",
    created_at: new Date().toISOString(),
    attachments: hasImages
      ? selectedImages.value.map((img) => ({
          url: URL.createObjectURL(img.file),
          temp: true,
        }))
      : [],
    status: "uploading",
  };

  if (!currentMessages.value) currentMessages.value = [];
  currentMessages.value.push(tempMessage);
  nextTick(scrollToBottom);

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
      status: "sent",
    };

    const index = currentMessages.value.findIndex((msg) => msg.id === tempId);
    if (index !== -1) {
      currentMessages.value[index] = newMessage;
    }
    selectedImages.value.forEach((img) => URL.revokeObjectURL(img.file));
    message.value = "";
    selectedImages.value = [];
    if (fileInput.value) {
      fileInput.value.value = null;
    }

    nextTick(scrollToBottom);
  } catch (error) {
    console.error("Lỗi khi gửi tin nhắn:", error.message);
  } finally {
    isSending.value = false;
  }
}

function startPollingMessages() {
  const sessionId = selectedSession.value?.id;
  if (!sessionId) return;

  if (lastPollingSessionId === sessionId && pollingInterval.value) {
    return;
  }

  stopPollingMessages();

  lastPollingSessionId = sessionId;

  pollingInterval.value = setInterval(async () => {
    const token = localStorage.getItem("access_token");
    if (!token) {
      stopPollingMessages();
      return;
    }
    try {
      const res = await fetch(`${API}/chat/messages/${sessionId}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) {
        if (res.status === 401) {
          stopPollingMessages();
          return;
        }
        throw new Error("Lỗi khi lấy tin nhắn");
      }
      const data = await res.json();
      if (
        JSON.stringify(currentMessages.value) !== JSON.stringify(data?.data)
      ) {
        currentMessages.value = data?.data || [];
        nextTick(scrollToBottom);
      }
    } catch (error) {
      console.error("Lỗi khi polling tin nhắn:", error.message);
    }
  }, 5000);
}

function stopPollingMessages() {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
    pollingInterval.value = null;
    lastPollingSessionId = null;
  }
}

const scrollToBottom = () => {
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
  }
};

const loadMessages = async () => {
  const token = localStorage.getItem("access_token");
  if (!token || !selectedSession.value?.id) return;

  try {
    isLoadingMore.value = true;
    const res = await fetch(
      `${API}/chat/messages/${selectedSession.value.id}?page=${page.value}&limit=${limit}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const data = await res.json();
    const newMessages = data?.data || [];

    if (newMessages.length < limit) {
      hasMore.value = false;
    }

    const reversed = newMessages.reverse();
    if (!currentMessages.value) {
      currentMessages.value = reversed;
    } else {
      currentMessages.value = [...reversed, ...currentMessages.value];
    }

    page.value++;
    await nextTick();
  } catch (err) {
    console.error("Lỗi tải thêm tin nhắn:", err);
  } finally {
    isLoadingMore.value = false;
  }
};

const handleScroll = () => {
  const el = chatContainer.value;
  if (!el || isLoadingMore.value || !hasMore.value) return;

  if (el.scrollTop < 50) {
    loadMessages();
  }
};

watch(selectedSession, (newVal) => {
  stopPollingMessages();
  if (newVal?.id) {
    startPollingMessages();
  }
});
</script>

<style scoped>
@media (max-width: 640px) {
  .sidebar {
    display: none;
  }
  .sidebar.open {
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    background: white;
    z-index: 50;
  }
}

.message-enter-active,
.message-leave-active {
  transition: all 0.3s ease;
}
.message-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.message-enter-to {
  opacity: 1;
  transform: translateY(0);
}
.message-leave-from {
  opacity: 1;
  transform: translateY(0);
}
.message-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
