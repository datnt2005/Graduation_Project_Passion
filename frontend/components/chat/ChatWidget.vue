<template>
  <div>
    <!-- Nút mở danh sách chat (floating) -->
    <div v-if="user?.role?.toLowerCase() !== 'seller'" class="fixed bottom-4 right-4 z-40">
      <button
        @click="toggleChatList"
        class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg relative"
        aria-label="Mở danh sách chat"
      >
        💬
        <span
          v-if="totalUnread > 0"
          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1"
        >
          {{ totalUnread }}
        </span>
      </button>
    </div>

    <!-- Danh sách cuộc trò chuyện -->
    <div
      v-show="showChatList"
      class="fixed bottom-20 right-2 bg-white rounded-lg shadow-xl w-[95vw] max-w-[400px] h-[85vh] md:w-[400px] md:h-[600px] z-40"
    >
      <div class="p-3 border-b font-bold text-gray-700">Tin nhắn</div>

      <ul class="max-h-[552px] overflow-y-auto">
        <li
          v-for="session in chatSessions"
          :key="session.id"
          @click="openChat(session)"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b flex items-center gap-2"
        >
          <!-- Avatar shop/seller ở sidebar -->
          <img
            :src="getSidebarAvatar(session)"
            @error="handleImageError"
            class="w-10 h-10 rounded-full object-cover border"
            alt="Avatar cửa hàng"
          />
          <div class="flex flex-col flex-1 min-w-0">
            <div class="font-medium text-gray-800 truncate">
              {{ session.seller?.store_name || session.seller?.user?.name || "Cửa hàng" }}
            </div>
            <div class="text-sm text-gray-600 truncate">
              {{ getLastMessagePreview(session) }}
            </div>
          </div>
          <div class="flex flex-col items-end shrink-0 pl-2">
            <span class="text-xs text-gray-400 mt-1 whitespace-nowrap">
              {{ formatTime(session.last_message_at) }}
            </span>
            <span
              v-if="session.unread_count > 0"
              class="mt-1 bg-red-500 text-white text-xs rounded-full px-2 py-0.5"
            >
              {{ session.unread_count }}
            </span>
          </div>
        </li>
      </ul>
    </div>

    <!-- Hộp chat -->
    <div
      v-show="showChat"
      class="fixed bottom-4 right-2 bg-white rounded-lg shadow-lg w-[95vw] max-w-[400px] h-[70vh] md:w-[400px] md:h-[550px] flex flex-col z-50"
    >
      <!-- Header -->
      <div class="flex justify-between items-center p-3 border-b bg-[#F0F2F5]">
        <div class="flex items-center gap-2 min-w-0">
          <!-- Quay lại sidebar -->
          <button
            v-if="showChat"
            @click="backToList"
            class="mr-1 text-[#189EFF] hover:text-[#108fe0] text-lg leading-none"
            title="Quay lại danh sách"
            aria-label="Quay lại danh sách"
          >←</button>

          <!-- Avatar tiêu đề: seller/shop -->
          <img
            :src="getSidebarAvatar(currentSession)"
            @error="handleImageError"
            class="w-8 h-8 rounded-full object-cover border"
            alt="Avatar cửa hàng"
          />
          <!-- Tiêu đề -->
           <!-- <p>{{ currentSession }}</p> -->
          <span class="font-semibold text-sm truncate">{{ headerTitle }}</span>
        </div>

        <button @click="closeChat" class="text-gray-500 hover:text-red-500 text-xl" aria-label="Đóng">×</button>
      </div>

      <!-- Danh sách tin nhắn -->
      <div
        ref="chatMessages"
        class="grow min-h-0 p-3 space-y-4 overflow-y-auto text-sm"
        @scroll="handleScroll"
        @click="closeContext"
      >
        <!--  Đang tải thêm -->
        <div v-if="isLoadingMore" class="text-center text-gray-400 text-xs my-2">
          Đang tải thêm tin nhắn...
        </div>

        <!-- Hết tin nhắn -->
        <div v-else-if="!hasMore && currentSession?.messages?.length" class="text-center text-gray-400 text-xs my-2">
          Bạn đã xem toàn bộ tin nhắn
        </div>

        <div
          v-for="(message, index) in currentSession?.messages"
          :key="message.id || index"
          :class="[
            'flex gap-3',
            message.sender_type === 'user' ? 'justify-end text-right' : 'justify-start text-left',
          ]"
          @contextmenu.prevent="openContext(message.id, $event)"
        >
          <!-- Avatar (chỉ hiển thị cho tin của seller) -->
          <img
            v-if="message.sender_type !== 'user'"
            :src="message.avatar.startsWith('http') ? message.avatar : `${mediaBaseUrl}${message.avatar}` || DEFAULT_AVATAR"
            class="w-8 h-8 rounded-full object-cover"
            alt="Avatar"
          />
          <!-- Nội dung tin -->
          <div class="max-w-[80%] sm:max-w-[70%]">
            <!-- Reply preview (nếu có) -->
            <div v-if="getReplyMeta(message)" class="mb-1 text-xs border-l-4 pl-2 rounded bg-gray-50 text-gray-600">
              <div class="font-medium mb-0.5">Đang trả lời</div>
              <div class="line-clamp-2 break-words">{{ getReplyMeta(message)?.text || '' }}</div>
            </div>

            <!-- Text -->
            <div
              v-if="message.message && message.message_type === 'text'"
              :class="[
                'inline-block px-4 py-2 rounded-2xl break-words mb-1 cursor-pointer select-text shadow-sm',
                message.sender_type === 'user'
                  ? 'bg-[#189EFF] text-white rounded-br-none'
                  : 'bg-gray-100 text-gray-800 rounded-bl-none',
              ]"
              role="button"
              tabindex="0"
              @click.stop="toggleTime(message.id)"
              @keyup.enter.stop="toggleTime(message.id)"
              @keyup.space.stop="toggleTime(message.id)"
              :aria-pressed="isTimeShown(message.id)"
            >
              {{ message.message }}
            </div>

            <!-- Ảnh -->
            <div
              v-if="message.message_type === 'image'"
              class="space-y-2 cursor-pointer"
              role="button"
              tabindex="0"
              @click.stop="toggleTime(message.id)"
              @keyup.enter.stop="toggleTime(message.id)"
              @keyup.space.stop="toggleTime(message.id)"
              :aria-pressed="isTimeShown(message.id)"
            >
              <div v-if="message.message" class="text-sm text-gray-700 mb-1">
                {{ message.message }}
              </div>
              <div class="flex flex-wrap gap-2">
                <div
                  v-for="(attachment, i) in message.attachments"
                  :key="i"
                  class="w-24 h-24 rounded overflow-hidden cursor-pointer"
                >
                  <img
                    :src="attachment.file_url || attachment.url || '/images/image.png'"
                    @error="handleImageError"
                    class="w-full h-full object-cover rounded border border-gray-200"
                    @click.stop="openImageViewer(attachment.file_url || attachment.url || '/images/image.png')"
                  />
                </div>
              </div>
            </div>

            <!-- Sản phẩm -->
            <div
              v-if="message.message_type === 'product'"
              class="cursor-pointer"
              role="button"
              tabindex="0"
              @click.stop="toggleTime(message.id)"
              @keyup.enter.stop="toggleTime(message.id)"
              @keyup.space.stop="toggleTime(message.id)"
              :aria-pressed="isTimeShown(message.id)"
            >
              <a
                :href="'/products/' + (message.attachments?.[0]?.meta_data?.slug || '')"
                target="_blank"
                rel="noopener noreferrer"
                class="block bg-[#F7F7F7] rounded-lg p-3 text-sm no-underline"
                @click.stop
              >
                <div class="mb-2 text-[#555] font-medium">
                  Bạn đang trao đổi với Người bán về sản phẩm này
                </div>
                <div class="flex border rounded overflow-hidden bg-white hover:shadow-md transition">
                  <img
                    :src="message.attachments?.[0]?.meta_data?.file_url || '/images/image.png'"
                    alt="Ảnh sản phẩm"
                    class="w-24 h-24 object-cover border-r cursor-pointer"
                    @click.stop="openImageViewer(message.attachments?.[0]?.meta_data?.file_url || '/images/image.png')"
                    @error="handleImageError"
                  />
                  <div class="flex-1 p-2 overflow-hidden">
                    <div class="text-sm font-semibold mb-1 text-gray-800 line-clamp-2 leading-snug break-words">
                      {{
                        shortenProductName(
                          parseMessage(message.attachments?.[0]?.meta_data?.name) || "[Sản phẩm]"
                        )
                      }}
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-1">
                      <span
                        v-if="parseMessage(message.attachments?.[0]?.meta_data?.price)"
                        class="text-[#FF0000] font-semibold"
                      >
                        {{ formatPrice(parseMessage(message.attachments?.[0]?.meta_data?.price)) }}
                      </span>
                      <span v-else class="text-gray-400 text-xs">Liên hệ để biết giá</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>

            <!-- Thời gian: chỉ hiện khi click -->
            <div
              v-if="isTimeShown(message.id)"
              class="text-[11px] mt-1 select-none"
              :class="message.sender_type === 'user' ? 'text-blue-300 text-right' : 'text-gray-400 text-left'"
            >
              {{ formatTimeMsg(message.created_at) }}
            </div>
          </div>

          <!-- Nút 3 chấm: chỉ hiện khi đã click vào tin + là tin của user -->
          <button
            v-if="message.sender_type === 'user' && isTimeShown(message.id)"
            type="button"
            @click.stop="openContext(message.id, $event)"
            class="text-gray-400 hover:text-gray-600 text-lg px-1"
            aria-label="Mở menu tin nhắn"
          >
            ⋮
          </button>

          <!-- MENU (fixed để luôn click được) -->
          <div
            v-if="contextMenu.open && contextMenu.messageId === message.id && message.sender_type === 'user'"
            id="ctx-menu"
            class="fixed z-[9999] bg-white border rounded shadow-lg text-sm"
            :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px', minWidth: '180px' }"
            @click.stop
          >
            <!-- Trả lời -->
            <button
              @click="startReply(message)"
              class="block w-full text-left px-3 py-2 hover:bg-gray-100"
              type="button"
            >
              Trả lời
            </button>

            <!-- CHỈ hiện Sửa trong 5 phút đầu -->
            <button
              v-if="canEdit(message)"
              @click="startEdit(message)"
              class="block w-full text-left px-3 py-2 hover:bg-gray-100"
              type="button"
            >
              Sửa
            </button>

            <button
              @click="askRevoke(message)"
              class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-red-600"
              type="button"
            >
              Thu hồi
            </button>
          </div>
        </div>
      </div>

      <!-- Bar trạng thái phía trên ô nhập: Reply / Đang sửa / Cảnh báo xoá -->
      <div class="px-3 pt-2 space-y-2 border-t bg-white">
        <!-- Reply preview -->
        <div v-if="replyTarget" class="flex items-start gap-2 text-sm bg-gray-50 border rounded p-2">
          <div class="border-l-4 border-blue-500 pl-2 flex-1">
            <div class="font-medium text-gray-700">Trả lời</div>
            <div class="text-gray-600 line-clamp-2">{{ replyTarget.message || '[Không có nội dung]' }}</div>
          </div>
          <button class="text-gray-500 hover:text-gray-700" @click="cancelReply" type="button" aria-label="Huỷ trả lời">✕</button>
        </div>

        <!-- Editing bar -->
        <div
          v-if="editing.active"
          class="flex items-center justify-between text-sm bg-amber-50 border border-amber-200 rounded p-2"
        >
          <div class="text-amber-800">Đang sửa tin nhắn</div>
          <button class="text-amber-700 hover:underline" @click="cancelEdit" type="button">Hủy</button>
        </div>

        <!-- Inline confirm revoke -->
        <div
          v-if="confirmRevoke.open"
          class="flex items-center justify-between text-sm bg-red-50 border border-red-200 rounded p-2"
        >
          <div class="text-red-700">
            Xoá tin nhắn này? Hành động không thể hoàn tác.
          </div>
          <div class="flex items-center gap-2">
            <button class="px-2 py-1 rounded hover:bg-red-100" @click="confirmRevoke.open = false" type="button">Hủy</button>
            <button class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700" @click="doRevoke" type="button">
              Xoá
            </button>
          </div>
        </div>
      </div>

      <!-- Gửi / Sửa -->
      <form @submit.prevent="onSubmit" class="p-3 border-t flex flex-col gap-2">
        <!-- Ảnh preview -->
        <div class="flex gap-2 flex-wrap">
          <div v-for="(file, index) in previewImages" :key="index" class="relative group">
            <img :src="file.url" class="w-20 h-20 object-cover rounded-lg border border-gray-300" />
            <button
              type="button"
              @click="removeImage(index)"
              class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center hover:bg-red-600"
              aria-label="Xoá ảnh"
            >
              ×
            </button>
          </div>
        </div>

        <!-- Ô nhập tin -->
        <div class="flex items-center gap-2 relative">
          <label class="cursor-pointer" title="Đính kèm ảnh">
            <i class="fa fa-paperclip text-[15px]"></i>
            <input
              type="file"
              multiple
              class="hidden"
              accept="image/*"
              @change="handleImageSelect"
              ref="fileInput"
            />
          </label>

          <!-- Emoji -->
          <button type="button" @click="toggleEmojiPicker" class="text-xl" aria-label="Emoji">😊</button>
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
            :placeholder="editing.active ? 'Nhập nội dung sửa...' : 'Aa...'"
            class="flex-1 bg-gray-100 px-3 py-2 rounded-full text-sm"
          />
          <button
            type="submit"
            class="text-white bg-[#189EFF] px-3 py-2 rounded-full font-medium hover:bg-[#108fe0]"
            :aria-label="editing.active ? 'Lưu chỉnh sửa' : 'Gửi tin nhắn'"
          >
            {{ editing.active ? 'Lưu' : 'Gửi' }}
          </button>
        </div>
      </form>

      <!-- Toast -->
      <div
        v-if="toast.open"
        class="fixed left-1/2 -translate-x-1/2 bottom-3 z-[9999] bg-black/80 text-white text-sm px-3 py-2 rounded"
      >
        {{ toast.text }}
      </div>
    </div>

    <!-- Modal xem ảnh -->
    <Transition name="fade">
      <div
        v-if="imageViewer.visible"
        class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
        @click.self="closeImageViewer"
      >
        <div class="relative max-w-[90vw] max-h-[90vh]">
          <img :src="imageViewer.url" alt="Xem ảnh" class="max-w-full max-h-[90vh] object-contain rounded shadow-xl" />
          <button
            class="absolute top-2 right-2 bg-gray-800 bg-opacity-50 text-white text-xl font-bold w-8 h-8 rounded-full flex items-center justify-center hover:bg-opacity-75 transition"
            @click="closeImageViewer"
            aria-label="Đóng"
          >
            ✕
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted, computed } from "vue";
import axios from "axios";
import "emoji-picker-element";

const user = ref(null);
const chatSessions = ref([]);
const totalUnread = ref(0);
const currentSession = ref(null);

const showChatList = ref(false);
const showChat = ref(false);
const showEmoji = ref(false);

const chatInput = ref("");
const previewImages = ref([]);
const chatMessages = ref(null);
const pollingInterval = ref(null);
const fileInput = ref(null);
const page = ref(1);
const limit = 20;
const isLoadingMore = ref(false);
const hasMore = ref(true);
const imageViewer = ref({ visible: false, url: null });
const contextMenu = ref({ open: false, messageId: null, x: 0, y: 0 });

/* Hiển thị thời gian & 3 chấm khi click */
const shownTimes = ref(new Set());
const toggleTime = (id) => {
  if (!id) return;
  if (shownTimes.value.has(id)) shownTimes.value.delete(id);
  else shownTimes.value.add(id);
  if (contextMenu.value.open && contextMenu.value.messageId !== id) {
    contextMenu.value.open = false;
  }
};
const isTimeShown = (id) => shownTimes.value.has(id);

/* Rule 5 phút: CHỈ cho sửa trong 5 phút đầu */
const EDIT_WINDOW_MS = 5 * 60 * 1000;
const nowMs = ref(Date.now());
let editTick = null;

/* Trạng thái Edit / Reply / Confirm delete / Toast */
const editing = ref({ active: false, id: null });
const replyTarget = ref(null);
const confirmRevoke = ref({ open: false, id: null });
const toast = ref({ open: false, text: "" });
const showToast = (text = "", ms = 2000) => {
  toast.value = { open: true, text };
  setTimeout(() => (toast.value.open = false), ms);
};

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl;
const DEFAULT_AVATAR = config.public.mediaBaseUrl + "avatars/default.jpg";

/* ===== Header avatar & title (computed) ===== */
const headerAvatar = computed(() =>
  currentSession.value?.seller?.user?.avatar
  || currentSession.value?.seller?.avatar
  || currentSession.value?.user?.avatar
  || DEFAULT_AVATAR
);
const headerTitle = computed(() =>
  currentSession.value?.seller?.store_name
  || currentSession.value?.seller?.user?.name
  || currentSession.value?.user?.name
  || "Cửa hàng"
);

/* Avatar ở sidebar: ưu tiên seller.user.avatar */
const getSidebarAvatar = (session) => {
  if (!session) return DEFAULT_AVATAR;
  return session.seller?.user?.avatar?.startsWith('http')
    ? session.seller.user.avatar
    : `${mediaBaseUrl}${session.seller?.user?.avatar || session.seller?.avatar || DEFAULT_AVATAR}`;
};

/* ===== Sessions ===== */
const parseMessage = (message) => {
  try {
    const parsed = JSON.parse(message);
    if (typeof parsed === "object" && parsed !== null) return parsed;
    return message;
  } catch {
    return message;
  }
};

const fetchSessions = async () => {
  try {
    const token = localStorage.getItem("access_token");
    const resUser = await axios.get(`${API}/me`, { headers: { Authorization: `Bearer ${token}` } });
    user.value = resUser.data.data;
    if (!user.value?.id) return;

    const res = await axios.get(`${API}/chat/sessions`, {
      params: { user_id: user.value.id, type: "user" },
      headers: { Authorization: `Bearer ${token}` },
    });
    chatSessions.value = res.data.data || [];

    totalUnread.value = chatSessions.value.reduce((sum, s) => sum + (s.unread_count || 0), 0);
  } catch (err) {
    console.error("Lỗi khi fetch sessions:", err);
  }
};

const markAsRead = async (session) => {
  try {
    const token = localStorage.getItem("access_token");
    if (!token) return;
    const response = await axios.post(
      `${API}/chat/messages/${session.id}/read`,
      { sender_type: "user" },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    if (response.data.success) {
      const idx = chatSessions.value.findIndex((s) => s.id === session.id);
      if (idx !== -1) {
        chatSessions.value[idx].unread_count = 0;
        totalUnread.value = chatSessions.value.reduce((sum, s) => sum + (s.unread_count || 0), 0);
      }
    }
  } catch (err) {
    console.error("Lỗi markAsRead:", err.response?.data?.message || err.message);
  }
};

// Format thời gian
const formatTime = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleString("vi-VN", { hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric" });
};
const formatTimeMsg = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
};
const formatPrice = (price) => new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price);
const getLastMessagePreview = (session) => {
  if (!session.last_message) return "Chưa có tin nhắn";
  if (session.last_message.startsWith("[Ảnh]")) return "[Hình ảnh]";
  if (session.last_message.startsWith("[Sản phẩm]")) return "[Sản phẩm]";
  return session.last_message;
};
const shortenProductName = (name) => (name && name.length > 30 ? name.substring(0, 30) + "..." : name || "");

/* ===== Lifecycle ===== */
onMounted(async () => {
  const token = localStorage.getItem("access_token");
  if (!token) return;

  try {
    const resUser = await fetch(`${API}/me`, { headers: { Authorization: `Bearer ${token}` } });
    const dataUser = await resUser.json();
    user.value = dataUser?.data || {};
    if (!user.value?.id) return;

    const resSessions = await fetch(`${API}/chat/sessions?user_id=${user.value.id}&type=user`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const raw = await resSessions.json();
    const dataSessions = Array.isArray(raw) ? raw : raw?.data || [];
    chatSessions.value = [...dataSessions];
  } catch (error) {
    console.error("Lỗi fetch:", error);
  }

  // ticking để UI tự ẩn/hiện nút Sửa theo 5 phút
  editTick = setInterval(() => (nowMs.value = Date.now()), 15000);

  document.addEventListener("click", onDocClick);
  window.addEventListener("keydown", handleEscKey);
});

onUnmounted(() => {
  document.removeEventListener("click", onDocClick);
  window.removeEventListener("keydown", handleEscKey);
  stopPollingMessages();
  if (editTick) clearInterval(editTick);
});

const toggleChatList = () => {
  showChatList.value = !showChatList.value;
  if (showChatList.value) showChat.value = false;
};

// Quay lại danh sách (sidebar)
const backToList = () => {
  stopPollingMessages();
  showChatList.value = true;
  showChat.value = false;
};

async function openChat(session) {
  currentSession.value = session;
  showChat.value = true;
  showChatList.value = false;

  // reset bar states
  editing.value = { active: false, id: null };
  replyTarget.value = null;
  confirmRevoke.value.open = false;

  await markAsRead(session);

  const token = localStorage.getItem("access_token");
  if (!token) return;

  try {
    const res = await fetch(`${API}/chat/messages/${session.id}`, { headers: { Authorization: `Bearer ${token}` } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    currentSession.value.messages = (data?.data || [])
      .map((msg) => ({ ...msg, attachments: msg.attachments || [] }))
      .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    shownTimes.value = new Set();
    contextMenu.value.open = false;

    stopPollingMessages();
    startPollingMessages();

    await nextTick(scrollToBottom);
  } catch (err) {
    console.error("Lỗi load tin nhắn:", err);
    showToast("Không tải được tin nhắn");
  }
}

/* ===== Reply meta render helper ===== */
const getReplyMeta = (message) => {
  const a = (message.attachments || []).find((x) => x.file_type === "reply");
  return a?.meta_data || message.meta_data?.reply_to || null;
};

/* ===== Tạo session nếu cần ===== */
async function createSessionWithSeller(sellerId) {
  const token = localStorage.getItem("access_token");
  if (!token) return null;

  const resUser = await fetch(`${API}/me`, { headers: { Authorization: `Bearer ${token}` } });
  const userData = await resUser.json();
  const _userId = userData?.data?.id;
  if (!_userId) return null;

  const res = await fetch(`${API}/chat/session`, {
    method: "POST",
    headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
    body: JSON.stringify({ user_id: _userId, seller_id: sellerId }),
  });
  if (!res.ok) return null;
  return await res.json();
}
function openChatWithUser(sellerId) {
  const existing = chatSessions.value.find((s) => s.seller?.user?.id === sellerId);
  if (existing) openChat(existing);
  else createSessionWithSeller(sellerId)
    .then((session) => {
      if (!session) throw new Error("Tạo session thất bại");
      chatSessions.value.push(session);
      openChat(session);
    })
    .catch((err) => console.error("❌ Không thể tạo session chat:", err));
}

/* ===== Polling ===== */
async function startPollingMessages() {
  if (!currentSession.value?.id) return;
  stopPollingMessages();

  pollingInterval.value = setInterval(async () => {
    if (!currentSession.value?.id) return;
    const token = localStorage.getItem("access_token");
    if (!token) return;

    try {
      const res = await fetch(`${API}/chat/messages/${currentSession.value.id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();
      const fetchedMessages = data?.data || [];
      if (!currentSession.value) return;

      if (!Array.isArray(currentSession.value.messages)) {
        currentSession.value.messages = fetchedMessages
          .map((msg) => ({ ...msg, attachments: msg.attachments || [] }))
          .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        await nextTick(scrollToBottom);
        return;
      }

      const currentIds = new Set(currentSession.value.messages.map((m) => m.id));
      let newMessages = fetchedMessages
        .filter((msg) => !currentIds.has(msg.id))
        .map((msg) => ({ ...msg, attachments: msg.attachments || [] }));

      if (newMessages.length > 0) {
        newMessages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        currentSession.value.messages.push(...newMessages);
        await markAsRead(currentSession.value);
        await fetchSessions();
        await nextTick(() => {
          const el = chatMessages.value;
          if (el) {
            const isAtBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 100;
            if (isAtBottom) scrollToBottom();
          }
        });
      }
    } catch (err) {
      console.error("Lỗi polling tin nhắn:", err);
    }
  }, 3000);
}
function stopPollingMessages() {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
    pollingInterval.value = null;
  }
}

const closeChat = () => {
  stopPollingMessages();
  showChat.value = false;
  currentSession.value = null;
  chatInput.value = "";
  previewImages.value = [];
  contextMenu.value.open = false;
  editing.value = { active: false, id: null };
  replyTarget.value = null;
  confirmRevoke.value.open = false;
};

/* ===== Gửi / Sửa ===== */
const onSubmit = async () => {
  if (editing.value.active) {
    await commitEdit();
  } else {
    await sendMessage();
  }
};

const sendMessage = async () => {
  const text = chatInput.value.trim();
  const hasImages = previewImages.value.length > 0;
  if (!text && !hasImages) return;

  const token = localStorage.getItem("access_token");
  if (!token || !user.value?.id || !currentSession.value?.id) return;

  const tempId = "tem-" + Date.now();
  const tempMessage = {
    id: tempId,
    sender_type: "user",
    message: text || "",
    message_type: hasImages ? "image" : "text",
    created_at: new Date().toISOString(),
    attachments: hasImages
      ? previewImages.value.filter(Boolean).map((img) => ({ url: URL.createObjectURL(img.file), temp: true }))
      : [],
    status: "uploading",
    meta_data: replyTarget.value
      ? { reply_to: { id: replyTarget.value.id, text: replyTarget.value.message, sender_type: replyTarget.value.sender_type } }
      : undefined
  };

  if (!currentSession.value.messages) currentSession.value.messages = [tempMessage];
  else currentSession.value.messages.push(tempMessage);

  const formData = new FormData();
  formData.append("session_id", currentSession.value.id);
  formData.append("sender_id", user.value.id);
  formData.append("sender_type", "user");
  formData.append("message_type", hasImages ? "image" : "text");
  if (text) formData.append("message", text);
  if (replyTarget.value) {
    formData.append("meta_data[reply_to][id]", replyTarget.value.id);
    formData.append("meta_data[reply_to][text]", replyTarget.value.message || "");
    formData.append("meta_data[reply_to][sender_type]", replyTarget.value.sender_type || "");
  }
  previewImages.value.forEach((img) => formData.append("file[]", img.file));

  try {
    const res = await fetch(`${API}/chat/message`, {
      method: "POST",
      headers: { Authorization: `Bearer ${token}` },
      body: formData,
    });

    const result = await res.json();
    if (!res.ok) {
      currentSession.value.messages = currentSession.value.messages.filter((m) => m.id !== tempId);
      showToast(result?.error || "Gửi thất bại");
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
      meta_data: tempMessage.meta_data
    };

    const index = currentSession.value.messages.findIndex((msg) => msg.id === tempId);
    if (index !== -1) currentSession.value.messages[index] = newMessage;

    previewImages.value.forEach((img) => URL.revokeObjectURL(img.file));
    previewImages.value = [];
    chatInput.value = "";
    if (fileInput.value) fileInput.value.value = null;
    replyTarget.value = null;

    await fetchSessions();
    await nextTick(scrollToBottom);
  } catch (err) {
    console.error("❌ Lỗi gửi:", err);
    showToast("Lỗi gửi tin nhắn");
  }
};

const canEdit = (msg) => {
  if (!msg) return false;
  if (msg.sender_type !== "user") return false;
  if (msg.message_type !== "text") return false;
  const created = new Date(msg.created_at).getTime();
  if (!created) return false;
  return nowMs.value - created <= EDIT_WINDOW_MS; // chỉ trong 5 phút đầu
};

const startEdit = (msg) => {
  editing.value = { active: true, id: msg.id };
  chatInput.value = msg.message || "";
  contextMenu.value.open = false;
};
const cancelEdit = () => {
  editing.value = { active: false, id: null };
  chatInput.value = "";
};
const commitEdit = async () => {
  const newContent = chatInput.value.trim();
  if (!newContent) {
    showToast("Nội dung trống");
    return;
  }
  try {
    const token = localStorage.getItem("access_token");
    const res = await fetch(`${API}/chat/messages/${editing.value.id}/action`, {
      method: "PUT",
      headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
      body: JSON.stringify({ action: "edit", message: newContent }),
    });
    const data = await res.json();
    if (data?.success) {
      const idx = currentSession.value.messages.findIndex((m) => m.id === editing.value.id);
      if (idx !== -1) {
        currentSession.value.messages[idx].message = newContent;
        currentSession.value.messages[idx].status = "edited";
      }
      showToast("Đã lưu chỉnh sửa");
      cancelEdit();
    } else {
      showToast(data?.message || "Không thể sửa tin nhắn");
    }
  } catch (err) {
    console.error(err);
    showToast("Không thể sửa tin nhắn");
  }
};

/* ===== Reply ===== */
const startReply = (msg) => {
  replyTarget.value = { id: msg.id, message: msg.message, sender_type: msg.sender_type };
  contextMenu.value.open = false;
};
const cancelReply = () => (replyTarget.value = null);

/* ===== Xoá (inline confirm) ===== */
const askRevoke = (msg) => {
  confirmRevoke.value = { open: true, id: msg.id };
  contextMenu.value.open = false;
};
const doRevoke = async () => {
  const id = confirmRevoke.value.id;
  if (!id) return;
  try {
    const token = localStorage.getItem("access_token");
    const res = await fetch(`${API}/chat/messages/${id}/action`, {
      method: "PUT",
      headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
      body: JSON.stringify({ action: "revoke" }),
    });
    const data = await res.json();
    if (data?.success) {
      currentSession.value.messages = currentSession.value.messages.filter((m) => m.id !== id);
      showToast("Đã xoá tin nhắn");
    } else {
      showToast(data?.message || "Không thể xoá");
    }
  } catch (err) {
    console.error(err);
    showToast("Không thể xoá");
  } finally {
    confirmRevoke.value.open = false;
  }
};

/* ===== Ảnh & Emoji ===== */
const handleImageSelect = (e) => {
  const files = Array.from(e.target.files);
  const validTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif", "image/webp"];
  const maxSize = 5 * 1024 * 1024;

  files.forEach((file) => {
    if (!validTypes.includes(file.type)) {
      showToast(`Định dạng không hợp lệ: ${file.name}`);
      return;
    }
    if (file.size > maxSize) {
      showToast(`Vượt 5MB: ${file.name}`);
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
const removeImage = (index) => previewImages.value.splice(index, 1);
const toggleEmojiPicker = () => (showEmoji.value = !showEmoji.value);
const addEmoji = (event) => (chatInput.value += event.detail.unicode);

/* ===== Image viewer ===== */
const openImageViewer = (url) => {
  if (!url) return;
  imageViewer.value.visible = true;
  imageViewer.value.url = url;
};
const closeImageViewer = () => {
  imageViewer.value.visible = false;
  imageViewer.value.url = null;
};

/* ===== Context menu ===== */
const openContext = (id, e) => {
  const padding = 8, menuW = 200, menuH = 140;
  let x = e.clientX, y = e.clientY;
  const vw = window.innerWidth || 0, vh = window.innerHeight || 0;
  if (x + menuW + padding > vw) x = Math.max(padding, vw - menuW - padding);
  if (y + menuH + padding > vh) y = Math.max(padding, vh - menuH - padding);

  contextMenu.value = { open: true, messageId: id, x, y };
};
const closeContext = () => (contextMenu.value = { open: false, messageId: null, x: 0, y: 0 });
const onDocClick = (ev) => {
  if (!contextMenu.value.open) return;
  const el = document.getElementById("ctx-menu");
  if (el && !el.contains(ev.target)) closeContext();
};

/* ===== Misc ===== */
const handleImageError = (event) => (event.target.src = DEFAULT_AVATAR);
const handleEscKey = (event) => {
  if (event.key === "Escape" && imageViewer.value.visible) closeImageViewer();
  if (event.key === "Escape" && contextMenu.value.open) closeContext();
};
const scrollToBottom = () => {
  if (chatMessages.value) chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
};

/* ===== Infinite load older ===== */
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
      { headers: { Authorization: `Bearer ${token}` } }
    );
    const data = await res.json();
    const newMessages = data?.data || [];

    if (newMessages.length < limit) hasMore.value = false;

    const reversed = newMessages.reverse().map((msg) => ({ ...msg, attachments: msg.attachments || [] }));

    if (!currentSession.value.messages) currentSession.value.messages = reversed;
    else reversed.forEach((msg) => currentSession.value.messages.unshift(msg));

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
  if (el.scrollTop < 50) loadMessages();
};

// expose (dùng nơi khác để mở chat với seller cụ thể)
defineExpose({ openChatWithUser });
</script>

<style scoped>
emoji-picker { max-height: 300px; z-index: 9999; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
