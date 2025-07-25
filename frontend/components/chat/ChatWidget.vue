<template>
  <div>
    <!-- Nút mở danh sách chat -->
    <div v-if="user?.role?.toLowerCase() !== 'seller'" class="fixed bottom-4 right-4 z-40">
      <button @click="toggleChatList"
        class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
        💬
      </button>
    </div>

    <!-- Danh sách cuộc trò chuyện -->
    <div v-show="showChatList" class="fixed bottom-20 right-4 bg-white rounded-lg shadow-xl w-[400px] h-[600px] z-40">
      <div class="p-3 border-b font-bold text-gray-700">Tin nhắn</div>
      <ul class="max-h-[552px] overflow-y-auto">
        <li v-for="session in chatSessions" :key="session.id" @click="openChat(session)"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b flex items-center gap-2">
          <img :src="session.seller?.user?.avatar || DEFAULT_AVATAR" @error="handleImageError"
            class="w-10 h-10 rounded-full object-cover" />
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

    <!-- Hộp chat -->
    <div v-show="showChat"
      class="fixed bottom-4 right-24 bg-white rounded-lg shadow-lg w-[400px] h-[600px] flex flex-col z-50">
      <!-- Header -->
      <div class="flex justify-between items-center p-3 border-b bg-[#F0F2F5]">
        <div class="flex items-center gap-2">
          <img :src="currentSession?.seller?.user?.avatar || DEFAULT_AVATAR" @error="handleImageError"
            class="w-8 h-8 rounded-full object-cover" />
          <span class="font-semibold text-sm">{{ chatTitle }}</span>
        </div>
        <button @click="closeChat" class="text-gray-500 hover:text-red-500 text-xl">
          ×
        </button>
      </div>

      <!-- Tin nhắn -->
      <div ref="chatMessages" class="grow min-h-0 p-3 space-y-4 overflow-y-auto text-sm" @scroll="handleScroll">
        <!--  Đang tải thêm -->
        <div v-if="isLoadingMore" class="text-center text-gray-400 text-xs my-2">
          Đang tải thêm tin nhắn...
        </div>

        <!-- Hết tin nhắn -->
        <div v-else-if="!hasMore && currentSession?.messages?.length" class="text-center text-gray-400 text-xs my-2">
          Bạn đã xem toàn bộ tin nhắn
        </div>

        <div v-for="(message, index) in currentSession?.messages" :key="message.id || index" :class="[
          'flex gap-3',
          message.sender_type === 'user'
            ? 'justify-end text-right'
            : 'justify-start text-left',
        ]">
          <!-- Avatar -->
          <img :src="message.sender_user?.avatar || DEFAULT_AVATAR" class="w-8 h-8 rounded-full object-cover"
            alt="Avatar" v-if="message.sender_type !== 'user'" />

          <!-- Nội dung tin nhắn -->
          <div>
            <!-- Text -->
            <div v-if="message.message && message.message_type === 'text'" :class="[
              'inline-block px-4 py-2 rounded-2xl max-w-xs break-words mb-1',
              message.sender_type === 'user'
                ? 'bg-[#189EFF] text-white rounded-br-none'
                : 'bg-gray-100 rounded-bl-none',
            ]">
              {{ message.message }}
            </div>

            <!-- Ảnh -->
            <div v-if="message.message_type === 'image'" class="space-y-2">
              <div v-if="message.message" class="text-sm text-gray-700 mb-1">
                {{ message.message }}
              </div>
              <div class="flex flex-wrap gap-2">
                <div v-for="(attachment, i) in message.attachments" :key="i"
                  class="w-24 h-24 rounded overflow-hidden cursor-pointer">
                  <img :src="attachment.file_url ||
                    attachment.url ||
                    '/images/image.png'
                    " @error="handleImageError" class="w-full h-full object-cover rounded border border-gray-200"
                    @click="
                      openImageViewer(
                        attachment.file_url ||
                        attachment.url ||
                        '/images/image.png'
                      )
                      " />
                </div>
              </div>
            </div>

            <!-- Sản phẩm -->
            <a v-if="message.message_type === 'product'" :href="/products/ + message.attachments?.[0]?.meta_data?.slug"
              target="_blank" rel="noopener noreferrer" class="block bg-[#F7F7F7] rounded-lg p-3 text-sm no-underline">
              <div class="mb-2 text-[#555] font-medium">
                Bạn đang trao đổi với Người bán về sản phẩm này
              </div>
              <div class="flex border rounded overflow-hidden bg-white hover:shadow-md transition">
                <img :src="message.attachments?.[0]?.meta_data?.file_url ||
                  '/images/image.png'
                  " alt="Ảnh sản phẩm" class="w-24 h-24 object-cover border-r cursor-pointer" @click.stop="
                    openImageViewer(
                      message.attachments?.[0]?.meta_data?.file_url ||
                      '/images/image.png'
                    )
                    " @error="handleImageError" />
                <div class="flex-1 p-2 overflow-hidden">
                  <div class="text-sm font-semibold mb-1 text-gray-800 line-clamp-2 leading-snug break-words">
                    {{ shortenProductName(parseMessage(message.attachments?.[0]?.meta_data?.name) || "[Sản phẩm]") }}
                  </div>
                  <div class="mt-1 flex flex-wrap items-center gap-1">
                    <span v-if="
                      parseMessage(
                        message.attachments?.[0]?.meta_data?.price
                      )
                    " class="text-[#FF0000] font-semibold">
                      {{
                        formatPrice(
                          parseMessage(
                            message.attachments?.[0]?.meta_data?.price
                          )
                        )
                      }}
                    </span>
                    <span v-if="
                      !parseMessage(
                        message.attachments?.[0]?.meta_data?.price
                      )
                    " class="text-gray-400 text-xs">
                      Liên hệ để biết giá
                    </span>
                  </div>
                </div>
              </div>
            </a>

            <!-- Thời gian -->
            <div class="text-xs text-gray-400 mt-1">
              {{
                new Date(message.created_at).toLocaleTimeString("vi-VN", {
                  hour: "2-digit",
                  minute: "2-digit",
                })
              }}
            </div>
          </div>
        </div>
      </div>

      <!-- Gửi tin -->
      <form @submit.prevent="sendMessage" class="p-3 border-t flex flex-col gap-2">
        <!-- Ảnh preview -->
        <div class="flex gap-2 flex-wrap">
          <div v-for="(file, index) in previewImages" :key="index" class="relative group">
            <img :src="file.url" class="w-20 h-20 object-cover rounded-lg border border-gray-300" />
            <button type="button" @click="removeImage(index)"
              class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center hover:bg-red-600">
              ×
            </button>
          </div>
        </div>

        <!-- Ô nhập tin -->
        <div class="flex items-center gap-2 relative">
          <label class="cursor-pointer">
            📎
            <input type="file" multiple class="hidden" accept="image/*" @change="handleImageSelect" ref="fileInput" />
          </label>

          <!-- Emoji -->
          <button type="button" @click="toggleEmojiPicker" class="text-xl">
            😊
          </button>
          <ClientOnly>
            <emoji-picker v-if="showEmoji" class="absolute bottom-16 right-4 z-50" @emoji-click="addEmoji" />
          </ClientOnly>

          <input v-model="chatInput" type="text" placeholder="Aa..."
            class="flex-1 bg-gray-100 px-3 py-2 rounded-full text-sm" />
          <button type="submit" class="text-blue-600 font-bold">Gửi</button>
        </div>
      </form>
    </div>

    <!-- Modal xem ảnh -->
    <Transition name="fade">
      <div v-if="imageViewer.visible" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
        @click.self="closeImageViewer">
        <div class="relative max-w-[90vw] max-h-[90vh]">
          <img :src="imageViewer.url" alt="Xem ảnh" class="max-w-full max-h-[90vh] object-contain rounded shadow-xl" />
          <button
            class="absolute top-2 right-2 bg-gray-800 bg-opacity-50 text-white text-xl font-bold w-8 h-8 rounded-full flex items-center justify-center hover:bg-opacity-75 transition"
            @click="closeImageViewer">
            ✕
          </button>
        </div>
      </div>
    </Transition>
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
const fileInput = ref(null);
const page = ref(1);
const limit = 20;
const isLoadingMore = ref(false);
const hasMore = ref(true);
const imageViewer = ref({
  visible: false,
  url: null,
});

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const DEFAULT_AVATAR = config.public.mediaBaseUrl + "avatars/default.jpg";

const parseMessage = (message) => {
  try {
    const parsed = JSON.parse(message);
    if (typeof parsed === "object" && parsed !== null) {
      return parsed;
    }
    return message;
  } catch (error) {
    return message;
  }
};

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
    return shortenProductName(parseMessage(lastMessage.message)?.name || "[Sản phẩm]");
  }
  return "Chưa có tin nhắn";
};

// Hàm rút gọn tên sản phẩm
const shortenProductName = (name) => {
  if (name.length > 30) {
    return name.substring(0, 30) + "...";
  }
  return name;
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

    currentSession.value.messages = (data?.data || [])
      .map((msg) => ({
        ...msg,
        attachments: msg.attachments || [],
      }))
      .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    stopPollingMessages();
    startPollingMessages();

    await nextTick(scrollToBottom);
  } catch (err) {
    console.error("Lỗi load tin nhắn:", err);
    alert("Không tải được tin nhắn: " + err.message);
  }
}

// tạo tin nhắn
async function createSessionWithSeller(sellerId) {
  const token = localStorage.getItem("access_token");
  if (!token) {
    console.error("❌ Chưa có access_token");
    return null;
  }

  // Lấy user_id từ API /me
  const resUser = await fetch(`${API}/me`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  const userData = await resUser.json();
  const userId = userData?.data?.id;

  if (!userId) {
    console.error("❌ Không tìm thấy user_id từ API /me");
    return null;
  }

  // Gửi request tạo session mới
  const res = await fetch(`${API}/chat/session`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      user_id: userId,
      seller_id: sellerId,
    }),
  });

  if (!res.ok) {
    console.error("❌ Tạo session thất bại:", res.statusText);
    return null;
  }

  const session = await res.json();
  return session;
}

function openChatWithUser(sellerId) {
  const existing = chatSessions.value.find(
    (s) => s.seller?.user?.id === sellerId
  );

  if (existing) {
    openChat(existing); // ✅ Nếu đã có session với seller → mở chat luôn
  } else {
    createSessionWithSeller(sellerId) // ❗ Nếu chưa có → gọi API tạo session mới
      .then((session) => {
        chatSessions.value.push(session);
        openChat(session); // Rồi mới mở chat
      })
      .catch((err) => {
        console.error("❌ Không thể tạo session chat:", err);
      });
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
      const fetchedMessages = data?.data || [];

      if (!Array.isArray(currentSession.value.messages)) {
        currentSession.value.messages = fetchedMessages
          .map((msg) => ({
            ...msg,
            attachments: msg.attachments || [],
          }))
          .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        await nextTick(scrollToBottom);
        return;
      }

      const currentIds = new Set(
        currentSession.value.messages.map((m) => m.id)
      );
      let newMessages = fetchedMessages
        .filter((msg) => !currentIds.has(msg.id))
        .map((msg) => ({
          ...msg,
          attachments: msg.attachments || [],
        }));

      if (newMessages.length > 0) {
        newMessages.sort(
          (a, b) => new Date(a.created_at) - new Date(b.created_at)
        );
        // Chỉ thêm tin nhắn mới vào cuối
        const originalLength = currentSession.value.messages.length;
        newMessages.forEach((msg) => {
          currentSession.value.messages.push(msg);
        });

        await nextTick(() => {
          const el = chatMessages.value;
          if (el) {
            const isAtBottom =
              el.scrollHeight - el.scrollTop - el.clientHeight < 100;
            if (isAtBottom) {
              scrollToBottom();
            } else {
              console.log("Có tin nhắn mới nhưng không cuộn vì không ở cuối");
            }
          }
        });
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

  if (!text && !hasImages) return;

  const token = localStorage.getItem("access_token");
  if (!token || !user.value?.id || !currentSession.value?.id) return;

  const tempId = "tem-" + Date.now();

  // Hiển thị tin nhắn tạm
  const tempMessage = {
    id: tempId,
    sender_type: "user",
    message: text || "",
    message_type: hasImages ? "image" : "text",
    created_at: new Date().toISOString(),
    attachments: hasImages
      ? previewImages.value.filter(Boolean).map((img) => ({
        url: URL.createObjectURL(img.file),
        temp: true,
      }))
      : [],
    status: "uploading",
  };

  if (!currentSession.value.messages)
    currentSession.value.messages = [tempMessage];
  else currentSession.value.messages.push(tempMessage);

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
      status: "sent",
    };

    // Cập nhật lại tin nhắn tạm
    const index = currentSession.value.messages.findIndex(
      (msg) => msg.id === tempId
    );
    if (index !== -1) {
      currentSession.value.messages[index] = newMessage;
    }

    // Cleanup
    previewImages.value.forEach((img) => URL.revokeObjectURL(img.file));
    previewImages.value = [];
    chatInput.value = "";

    // Reset input file
    if (fileInput.value) {
      fileInput.value.value = null;
    }
    nextTick(scrollToBottom);
  } catch (err) {
    console.error("❌ Lỗi gửi:", err);
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

// Mở modal xem ảnh
const openImageViewer = (url) => {
  if (!url) {
    console.error("URL ảnh không hợp lệ:", url);
    return;
  }
  imageViewer.value.visible = true;
  imageViewer.value.url = url;
};

// Đóng modal xem ảnh
const closeImageViewer = () => {
  imageViewer.value.visible = false;
  imageViewer.value.url = null;
};

// Đóng modal bằng phím Esc
const handleEscKey = (event) => {
  if (event.key === "Escape" && imageViewer.value.visible) {
    closeImageViewer();
  }
};

// Ẩn emoji picker khi click ngoài
const handleClickOutside = (e) => {
  const picker = document.querySelector("emoji-picker");
  const toggleBtn = e.target.closest("button");
  if (picker && !picker.contains(e.target) && !toggleBtn) {
    showEmoji.value = false;
  }
};

// Cuộn xuống dưới cùng
const scrollToBottom = () => {
  if (chatMessages.value) {
    chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("keydown", handleEscKey);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("keydown", handleEscKey);
  stopPollingMessages();
});

// Xử lý cuộn để tải thêm tin nhắn
const loadMessages = async () => {
  const token = localStorage.getItem("access_token");
  if (!token || !currentSession.value?.id) return;

  const container = chatMessages.value;
  const oldScrollHeight = container.scrollHeight;
  const oldScrollTop = container.scrollTop;

  try {
    isLoadingMore.value = true;

    const res = await fetch(
      `${API}/chat/messages/${currentSession.value.id}?page=${page.value}&limit=${limit}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const data = await res.json();
    const newMessages = data?.data || [];

    if (newMessages.length < limit) {
      hasMore.value = false;
    }

    const reversed = newMessages.reverse().map((msg) => ({
      ...msg,
      attachments: msg.attachments || [],
    }));

    if (!currentSession.value.messages) {
      currentSession.value.messages = reversed;
    } else {
      reversed.forEach((msg) => {
        currentSession.value.messages.unshift(msg);
      });
    }

    page.value++;

    await nextTick(() => {
      const newScrollHeight = container.scrollHeight;
      container.scrollTop = oldScrollTop + (newScrollHeight - oldScrollHeight);
    });
  } catch (err) {
    console.error("Lỗi tải thêm tin nhắn:", err);
  } finally {
    isLoadingMore.value = false;
  }
};
const handleScroll = () => {
  const el = chatMessages.value;
  if (!el || isLoadingMore.value || !hasMore.value) return;

  if (el.scrollTop < 50) {
    loadMessages();
  }
};

// Theo dõi cuộn
onMounted(() => {
  if (chatMessages.value) {
    chatMessages.value.addEventListener("scroll", handleScroll);
  }
});

onUnmounted(() => {
  if (chatMessages.value) {
    chatMessages.value.removeEventListener("scroll", handleScroll);
  }
});

defineExpose({
  openChatWithUser,
});
</script>

<style scoped>
emoji-picker {
  max-height: 300px;
  z-index: 9999;
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