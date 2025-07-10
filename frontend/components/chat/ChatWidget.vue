<!-- ChatWidget.vue -->
<template>
  <div>
    <!-- Nút mở danh sách chat -->
    <div
      v-if="user?.role?.toLowerCase() !== 'seller'"
      class="fixed bottom-4 right-4 z-40"
    >
      <button
        @click="toggleChatList"
        class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg"
      >
        💬
      </button>
    </div>

    <!-- Danh sách cuộc trò chuyện -->
    <div
      v-show="showChatList"
      class="fixed bottom-20 right-4 bg-white rounded-lg shadow-xl w-[400px] h-[600px] z-40"
    >
      <div class="p-3 border-b font-bold text-gray-700">Tin nhắn trước đây</div>
      <ul class="max-h-[calc(600px-48px)] overflow-y-auto">
        <li
          v-for="session in chatSessions"
          :key="session.id"
          @click="openChat(session)"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b flex items-center gap-2"
        >
          <img
            :src="session.seller?.user?.avatar || DEFAULT_AVATAR"
            @error="handleImageError"
            class="w-10 h-10 rounded-full object-cover"
          />

          <div class="flex flex-col flex-1">
            <div class="font-medium text-gray-800">
              {{ session.seller?.store_name || "Cửa hàng" }}
            </div>
            <div class="text-sm text-gray-600 truncate">
              {{ getLastMessagePreview(session) }}
            </div>
          </div>
          <span class="text-xs text-gray-400 mt-1 whitespace-nowrap">
            {{ formatTime(session.last_message_at) }}
          </span>
        </li>
      </ul>
    </div>

    <!-- Bubble chat -->
    <div
      v-show="showChat"
      class="fixed bottom-4 right-24 bg-white rounded-lg shadow-lg w-[400px] h-[600px] flex flex-col z-50"
    >
      <!-- Header -->
      <div class="flex justify-between items-center p-3 border-b bg-[#F0F2F5]">
        <div class="flex items-center gap-2">
          <img
            :src="currentSession?.seller?.user?.avatar || DEFAULT_AVATAR"
            @error="handleImageError"
            class="w-8 h-8 rounded-full object-cover"
          />
          <span class="font-semibold text-sm">{{ chatTitle }}</span>
        </div>
        <button
          @click="closeChat"
          class="text-gray-500 hover:text-red-500 text-xl"
        >
          ×
        </button>
      </div>
      <!-- Nội dung tin nhắn người dùng -->
      <div
        ref="chatMessages"
        class="grow min-h-0 p-3 space-y-4 overflow-y-auto text-sm"
      >
        <div
          v-for="message in currentSession?.messages"
          :key="message.id"
          :class="[
            'flex items-end gap-4', // Tăng khoảng cách giữa avatar và nội dung
            message.sender_type === 'user'
              ? 'flex-row-reverse '
              : 'justify-start',
          ]"
        >
          <!-- Avatar -->
          <img
            :src="message.sender_user?.avatar || DEFAULT_AVATAR"
            class="w-8 h-8 rounded-full object-cover"
            :alt="
              message.sender_type === 'user' ? 'User avatar' : 'Seller avatar'
            "
          />

          <!-- Nội dung -->
          <div
            :class="[
              'flex flex-col',
              message.sender_type === 'user' ? 'items-end' : 'items-start',
            ]"
          >
            <!-- Tin nhắn chữ -->
            <div
              v-if="message.message && message.message_type === 'text'"
              :class="[
                'px-4 py-2 rounded-2xl max-w-xs break-words mb-1',
                message.sender_type === 'user'
                  ? 'bg-blue-500 text-white rounded-br-none'
                  : 'bg-gray-100 rounded-bl-none',
              ]"
            >
              {{ message.message }}
            </div>

            <!-- Tin nhắn ảnh -->
            <div v-if="message.message_type === 'image'" class="space-y-2">
              <!-- Nếu có text kèm ảnh -->
              <div
                v-if="message.message"
                :class="[
                  'px-4 py-2 rounded-2xl max-w-xs break-words',
                  message.sender_type === 'user'
                    ? 'bg-blue-500 text-white rounded-br-none'
                    : 'bg-gray-100 rounded-bl-none',
                ]"
              >
                {{ message.message }}
              </div>

              <!-- Ảnh -->
              <div class="flex gap-2 mt-1 flex-wrap">
                <div
                  v-for="(attachment, index) in message.attachments"
                  :key="index"
                  class="w-24 h-24 rounded overflow-hidden cursor-pointer"
                >
                  <img
                    :src="attachment.file_url || attachment.url"
                    class="w-full h-full object-cover rounded border border-gray-200"
                    alt="Ảnh"
                    @error="handleImageError"
                  />
                </div>
              </div>
            </div>

            <!-- Tin nhắn sản phẩm -->
            <div
              v-if="
                message.message_type === 'product' &&
                message.attachments?.length
              "
              :class="[
                'px-4 py-2 rounded-2xl max-w-xs break-words',
                message.sender_type === 'user'
                  ? 'bg-blue-500 text-white rounded-br-none'
                  : 'bg-gray-100 rounded-bl-none',
              ]"
            >
              <div class="font-semibold">
                Sản phẩm:
                {{ message.attachments[0].meta_data?.name || "Không rõ" }}
              </div>
              <div v-if="message.attachments[0].meta_data?.price">
                Giá: {{ formatPrice(message.attachments[0].meta_data.price) }}
              </div>
              <div v-if="message.message">{{ message.message }}</div>
            </div>

            <!-- Thời gian -->
            <div
              :class="[
                'text-xs mt-1',
                message.sender_type === 'user'
                  ? 'text-gray-500 flex items-center gap-1 justify-end'
                  : 'text-gray-400',
              ]"
            >
              {{
                new Date(message.created_at).toLocaleTimeString([], {
                  hour: "2-digit",
                  minute: "2-digit",
                })
              }}
            </div>
          </div>
        </div>
      </div>
      <!-- Form gửi tin -->
      <form
        @submit.prevent="sendMessage"
        class="p-3 border-t flex flex-col gap-2"
      >
        <div class="flex gap-2 flex-wrap">
          <div
            v-for="(file, index) in previewImages"
            :key="index"
            class="relative group"
          >
            <img
              :src="file.url"
              class="w-20 h-20 object-cover rounded-lg border border-gray-300"
            />
            <button
              type="button"
              @click="removeImage(index)"
              class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center hover:bg-red-600 transition"
            >
              ×
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2 relative">
          <label class="cursor-pointer">
            📎
            <input
              type="file"
              multiple
              class="hidden"
              accept="image/*"
              @change="handleImageSelect"
            />
          </label>

          <button type="button" @click="toggleEmojiPicker" class="text-xl">
            😊
          </button>
          <ClientOnly>
            <emoji-picker
              v-if="showEmoji"
              class="absolute bottom-16 right-4 z-50"
              @emoji-click="addEmoji"
            />
          </ClientOnly>

          <input
            v-model="chatInput"
            type="text"
            placeholder="Aa..."
            class="flex-1 bg-gray-100 px-3 py-2 rounded-full text-sm"
          />
          <button type="submit" class="text-blue-600 font-bold">Gửi</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from "vue";
import "emoji-picker-element";

const user = ref(null);
const chatSessions = ref([]);
const currentSession = ref(null);
const showChatList = ref(false);
const showChat = ref(false);
const showEmoji = ref(false);
const chatTitle = ref("Cửa hàng");
const chatInput = ref("");
const previewImages = ref([]);
const chatMessages = ref(null);
const pollingInterval = ref(null);

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const DEFAULT_AVATAR = config.public.mediaBaseUrl + "avatars/default.jpg";

// Format thời gian
const formatTime = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
};

// Format giá
const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price);
};

// Hiển thị preview tin nhắn cuối
const getLastMessagePreview = (session) => {
  const lastMessage = session.messages?.[session.messages.length - 1];
  if (!lastMessage) return "Chưa có tin nhắn";
  if (lastMessage.message_type === "text")
    return lastMessage.message || "Tin nhắn rỗng";
  if (lastMessage.message_type === "image") return "[Hình ảnh]";
  if (lastMessage.message_type === "product") {
    return lastMessage.attachments?.[0]?.meta_data?.name || "[Sản phẩm]";
  }
  return "Chưa có tin nhắn";
};

// Lấy user và danh sách session
onMounted(async () => {
  const token = localStorage.getItem("access_token");
  if (!token) return;

  try {
    const resUser = await fetch(`${API}/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const dataUser = await resUser.json();
    user.value = dataUser?.data || {};

    if (!user.value?.id) return;

    const resSessions = await fetch(
      `${API}/chat/sessions?user_id=${user.value.id}&type=user`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const raw = await resSessions.json();
    const dataSessions = Array.isArray(raw) ? raw : raw?.data || [];
    chatSessions.value = [...dataSessions];

    // Nếu đang mở cuộc chat → fetch lại tin nhắn
    if (showChat.value && currentSession.value?.id) {
      const session = chatSessions.value.find(
        (s) => s.id === currentSession.value.id
      );
      if (session) openChat(session);
    }
  } catch (error) {
    console.error("Lỗi fetch:", error);
  }
});

// Hiển thị hoặc ẩn danh sách chat
const toggleChatList = () => {
  showChatList.value = !showChatList.value;
  if (showChatList.value) showChat.value = false;
};

// Mở cuộc trò chuyện
async function openChat(session) {
  currentSession.value = session;
  chatTitle.value =
    session.seller?.store_name || session.user?.name || "Cửa hàng";
  showChat.value = true;
  showChatList.value = false;

  const token = localStorage.getItem("access_token");
  if (!token) {
    console.warn("Chưa đăng nhập!");
    return;
  }

  try {
    const res = await fetch(`${API}/chat/messages/${session.id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    // Gán tin nhắn vào session
    currentSession.value.messages = (data?.data || []).map((msg) => ({
      ...msg,
      attachments: msg.attachments || [],
    }));

    // Gọi polling để tự cập nhật mỗi 3s
    stopPollingMessages();
    startPollingMessages();

    await nextTick(scrollToBottom);
  } catch (err) {
    console.error("Lỗi load tin nhắn:", err);
    alert("Không tải được tin nhắn: " + err.message);
  }
}

// Polling – tự động fetch tin nhắn mới mỗi 3 giây
function startPollingMessages() {
  if (!currentSession.value?.id) return;

  pollingInterval.value = setInterval(async () => {
    const token = localStorage.getItem("access_token");
    if (!token) return;

    try {
      const res = await fetch(
        `${API}/chat/messages/${currentSession.value.id}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      // So sánh số lượng tin nhắn mới để cập nhật nếu cần
      const newMessages = data?.data || [];
      if (newMessages.length !== (currentSession.value.messages?.length || 0)) {
        currentSession.value.messages = newMessages.map((msg) => ({
          ...msg,
          attachments: msg.attachments || [],
        }));
        await nextTick(scrollToBottom);
      }
    } catch (err) {
      console.error("Lỗi polling tin nhắn:", err);
    }
  }, 3000);
}
// Đảm bảo dừng polling khi đóng chat hoặc rời trang
function stopPollingMessages() {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
    pollingInterval.value = null;
  }
}

// Dừng polling khi đóng chat
const closeChat = () => {
  stopPollingMessages();
  showChat.value = false;
  currentSession.value = null;
  chatInput.value = "";
  previewImages.value = [];
};

// Gửi tin nhắn
const sendMessage = async () => {
  const text = chatInput.value.trim();
  const hasImages = previewImages.value.length > 0;

  if (!text && !hasImages) {
    alert("Vui lòng nhập tin nhắn hoặc chọn ảnh!");
    return;
  }

  const token = localStorage.getItem("access_token");
  if (!token || !user.value?.id || !currentSession.value?.id) {
    alert("Vui lòng đăng nhập hoặc chọn cuộc trò chuyện!");
    return;
  }

  const formData = new FormData();
  formData.append("session_id", currentSession.value.id);
  formData.append("sender_id", user.value.id);
  formData.append("sender_type", "user");
  formData.append("message_type", hasImages ? "image" : "text");
  if (text) formData.append("message", text);

  previewImages.value.forEach((img) => {
    formData.append("file[]", img.file);
  });

  try {
    const res = await fetch(`${API}/chat/message`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
      },
      body: formData,
    });

    const result = await res.json();
    if (!res.ok) {
      console.error("Gửi thất bại:", result);
      alert(`Gửi thất bại: ${result.error || "Lỗi không xác định"}`);
      return;
    }

    const newMessage = {
      id: result.message.id,
      sender_type: "user",
      message: text || "",
      message_type: result.message.message_type,
      created_at: new Date().toISOString(),
      attachments: result.attachments || [],
    };

    if (!currentSession.value.messages) currentSession.value.messages = [];
    currentSession.value.messages.push(newMessage);

    chatInput.value = "";
    previewImages.value = [];
    nextTick(scrollToBottom);
  } catch (err) {
    console.error("❌ Lỗi gửi:", err);
    // alert('Lỗi gửi tin nhắn: ' + err.message)
  }
};

// Chọn ảnh
const handleImageSelect = (e) => {
  const files = Array.from(e.target.files);
  const validTypes = [
    "image/jpeg",
    "image/png",
    "image/jpg",
    "image/gif",
    "image/webp",
  ];
  const maxSize = 5 * 1024 * 1024; // 5MB

  files.forEach((file) => {
    if (!validTypes.includes(file.type)) {
      alert(`File ${file.name} không phải định dạng ảnh hợp lệ!`);
      return;
    }
    if (file.size > maxSize) {
      alert(`File ${file.name} vượt quá kích thước cho phép (5MB)!`);
      return;
    }
    const reader = new FileReader();
    reader.onload = (evt) => {
      previewImages.value.push({ file, url: evt.target.result });
    };
    reader.readAsDataURL(file);
  });
  e.target.value = "";
};

// Xóa ảnh
const removeImage = (index) => {
  previewImages.value.splice(index, 1);
};

// Emoji picker
const toggleEmojiPicker = () => {
  showEmoji.value = !showEmoji.value;
};
const addEmoji = (event) => {
  chatInput.value += event.detail.unicode;
};

// Xử lý lỗi khi ảnh không tải được

const handleImageError = (event) => {
  event.target.src = DEFAULT_AVATAR;
};

// Ẩn emoji picker khi click ngoài
const handleClickOutside = (e) => {
  const picker = document.querySelector("emoji-picker");
  const toggleBtn = e.target.closest("button");
  if (picker && !picker.contains(e.target) && !toggleBtn) {
    showEmoji.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});
onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});

// Cuộn xuống dưới cùng
const scrollToBottom = () => {
  if (chatMessages.value) {
    chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
  }
};
</script>

<style scoped>
emoji-picker {
  max-height: 300px;
  z-index: 9999;
}
</style>
