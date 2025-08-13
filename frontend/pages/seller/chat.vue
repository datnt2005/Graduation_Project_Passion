<template>
  <div class="h-full bg-white text-[#212121] overflow-hidden">
    <div class="h-full grid grid-cols-1 sm:grid-cols-[340px,1fr] overflow-hidden">

      <!-- SIDEBAR -->
      <aside class="relative bg-white border-r border-gray-200 min-h-0 flex flex-col">
        <!-- Header -->
        <div class="p-4 font-bold text-lg border-b border-gray-200 bg-blue-500 text-white flex items-center justify-between shrink-0">
          <span>Tin nhắn</span>
          <button
            v-if="selectedSession && isSmallScreen"
            class="sm:hidden text-[#189EFF] hover:text-[#0f89e0] text-xl"
            @click="closeSessionMobile"
            aria-label="Đóng"
          >✖</button>
        </div>

        <!-- Empty state -->
        <div
          v-if="!chatSessions.length && !loadingSessions"
          class="flex-1 min-h-0 flex items-center justify-center text-gray-400 italic"
        >
          Chưa có cuộc trò chuyện nào
        </div>

        <!-- Sessions list -->
        <ul v-else class="flex-1 min-h-0 overflow-y-auto custom-scrollbars">
          <li
            v-for="(session, i) in chatSessions"
            :key="session.id || i"
            @click="selectSession(session)"
            class="relative p-4 cursor-pointer border-b border-gray-100 hover:bg-[#F2F9FF] transition"
            :class="{'bg-[#F2F9FF]': selectedSession?.id === session.id}"
          >
            <div class="flex items-center gap-3">
              <img :src="getAvatar(session.user?.avatar.startsWith('http') ? session.user.avatar : `${mediaBaseUrl}${session.user.avatar}`)" class="w-10 h-10 rounded-full object-cover border" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <span class="font-medium text-sm truncate">
                    {{ session.user?.name || "Người dùng" }}
                  </span>
                  <span class="text-xs text-gray-400 shrink-0">
                    {{ formatTime(session.last_message_at) }}
                  </span>
                </div>
                <div class="text-xs text-gray-500 truncate mt-0.5">
                  {{ session.last_message || "..." }}
                </div>
              </div>
            </div>

            <span
              v-if="session.unread_count > 0"
              class="absolute top-3 right-3 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5"
            >{{ session.unread_count }}</span>
          </li>
        </ul>

        <!-- Mobile overlay -->
        <transition name="slide-left-slow">
          <div
            v-if="selectedSession && isSmallScreen && !showSidebar"
            class="absolute inset-0 bg-white z-10"
          ></div>
        </transition>
      </aside>

      <!-- CHAT -->
      <main class="relative flex flex-col bg-[#F9FAFB] min-h-0 overflow-hidden">
        <!-- Top bar -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-white shrink-0">
          <div class="flex items-center gap-3 min-w-0">
            <button
              class="sm:hidden text-[#189EFF] hover:text-[#0f89e0]"
              @click="openSidebarMobile"
              aria-label="Quay lại danh sách"
            >
              🔙
            </button>

            <img
              v-if="selectedSession?.user?.avatar"
              :src="getAvatar(selectedSession.user.avatar.startsWith('http') ? selectedSession.user.avatar : `${mediaBaseUrl}${selectedSession.user.avatar}`)"
              class="w-8 h-8 rounded-full object-cover border"
              alt="Avatar KH"
            />

            <div class="font-semibold text-[#212121] truncate">
              {{ selectedSession ? (selectedSession.user?.name || 'Khách hàng') : 'Chọn một cuộc trò chuyện' }}
            </div>
          </div>
        </div>

        <!-- Empty guide -->
        <div v-if="!selectedSession" class="flex-1 min-h-0 grid place-items-center text-gray-400 italic p-6">
          Hãy chọn một cuộc trò chuyện để bắt đầu
        </div>

        <!-- Messages -->
        <div
          v-else
          ref="chatContainer"
          class="flex-1 min-h-0 overflow-y-auto p-4 space-y-4 text-[#212121] custom-scrollbars pb-32"
          @scroll="handleScroll"
          @click="closeContext"
        >
          <div v-if="isLoadingMessages" class="text-gray-500 text-center italic py-2">
            Đang tải cuộc trò chuyện...
          </div>
          <div v-else-if="isLoadingMore" class="text-gray-500 text-center italic py-2">
            Đang tải thêm tin nhắn...
          </div>

          <TransitionGroup name="message-fade" tag="div" @after-enter="scrollToBottom">
            <div
              v-for="msg in currentMessages"
              :key="msg.id"
              :class="[
                'flex items-end gap-2 group',
                msg.sender_type === 'seller' ? 'justify-end' : ''
              ]"
              @contextmenu.prevent="openContext(msg.id, $event)"
            >
              <!-- Avatar chỉ cho user -->
              <img
                v-if="msg.sender_type === 'user'"
                :src="getAvatar(msg.avatar.startsWith('http') ? msg.avatar : `${mediaBaseUrl}${msg.avatar}`)"
                class="w-8 h-8 rounded-full object-cover"
                alt="Avatar"
              />

              <!-- Nội dung tin -->
              <div :class="msg.sender_type === 'seller' ? 'flex flex-col items-end' : ''" class="max-w-[85%]">

                <!-- Reply preview nếu có -->
                <div v-if="getReplyMeta(msg)" class="mb-1 text-[12px] border-l-4 pl-2 rounded bg-gray-50 text-gray-600">
                  <div class="font-medium mb-0.5">Đang trả lời</div>
                  <div class="line-clamp-2 break-words">{{ getReplyMeta(msg)?.text || '' }}</div>
                </div>

                <!-- Bong bóng text -->
                <div
                  v-if="msg.message && msg.message_type === 'text'"
                  :class="[
                    'px-4 py-2 rounded-2xl mb-1 shadow-sm cursor-pointer select-text',
                    msg.sender_type === 'seller'
                      ? 'bg-[#189EFF] text-white rounded-br-none'
                      : 'bg-white text-[#212121] border rounded-bl-none'
                  ]"
                  role="button"
                  tabindex="0"
                  @click.stop="toggleTime(msg.id)"
                  @keyup.enter.stop="toggleTime(msg.id)"
                  @keyup.space.stop="toggleTime(msg.id)"
                  :aria-pressed="isTimeShown(msg.id)"
                >
                  {{ msg.message }}
                </div>

                <!-- Ảnh -->
                <div
                  v-if="msg.message_type === 'image' && msg.attachments?.length"
                  class="flex gap-2 flex-wrap mb-1 cursor-pointer"
                  role="button"
                  tabindex="0"
                  @click.stop="toggleTime(msg.id)"
                  @keyup.enter.stop="toggleTime(msg.id)"
                  @keyup.space.stop="toggleTime(msg.id)"
                  :aria-pressed="isTimeShown(msg.id)"
                >
                  <div
                    v-for="(attachment, index) in msg.attachments"
                    :key="index"
                    class="w-24 h-24 rounded-md overflow-hidden relative border bg-white"
                    @click.stop="openImageViewer(attachment.file_url || attachment.url)"
                  >
                    <img
                      :src="attachment.file_url || attachment.url"
                      class="w-full h-full object-cover"
                      alt="Ảnh đính kèm"
                      :class="{'opacity-50 grayscale animate-pulse': attachment.temp}"
                    />
                    <div v-if="attachment.temp" class="absolute inset-0 grid place-items-center">
                      <svg class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a 8 8 0 0 1 8-8v8z"></path>
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- Sản phẩm -->
                <div
                  v-if="msg.message_type === 'product'"
                  class="mb-1 max-w-xs cursor-pointer"
                  role="button"
                  tabindex="0"
                  @click.stop="toggleTime(msg.id)"
                  @keyup.enter.stop="toggleTime(msg.id)"
                  @keyup.space.stop="toggleTime(msg.id)"
                  :aria-pressed="isTimeShown(msg.id)"
                >
                  <a
                    :href="msg.attachments?.[0]?.meta_data?.productLink"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block bg-[#F7F7F7] rounded-lg p-3 text-sm no-underline hover:shadow-md transition"
                    @click.stop
                  >
                    <div class="mb-2 text-[#555] font-medium">
                      Khách hàng muốn trao đổi về sản phẩm này
                    </div>
                    <div class="flex border rounded overflow-hidden bg-white">
                      <img :src="msg.attachments?.[0]?.meta_data?.file_url" alt="Sản phẩm" class="w-24 h-24 object-cover border-r" />
                      <div class="flex-1 p-2 overflow-hidden">
                        <div class="font-semibold text-[#212121] line-clamp-2">
                          {{ msg.attachments?.[0]?.meta_data?.name || "[Sản phẩm]" }}
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-1">
                          <span
                            v-if="msg.attachments?.[0]?.meta_data?.original_price"
                            class="text-gray-400 line-through text-xs"
                          >
                            {{ formatPrice(msg.attachments[0].meta_data.original_price) }}
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
                </div>

                <!-- Fallback -->
                <div
                  v-if="!msg.message && (!msg.attachments || !msg.attachments.length) && msg.message_type !== 'product'"
                  class="text-xs text-gray-400 italic cursor-pointer"
                  role="button"
                  tabindex="0"
                  @click.stop="toggleTime(msg.id)"
                  @keyup.enter.stop="toggleTime(msg.id)"
                  @keyup.space.stop="toggleTime(msg.id)"
                >
                  [Tin nhắn không xác định]
                </div>

                <!-- Thời gian -->
                <div
                  v-if="isTimeShown(msg.id)"
                  :class="[
                    'msg-time text-xs mt-1 select-none',
                    msg.sender_type === 'seller' ? 'text-gray-500 text-right' : 'text-gray-400 text-left'
                  ]"
                >
                  {{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                </div>
              </div>

              <!-- Nút 3 chấm: chỉ cho tin của seller + khi đã bật giờ -->
              <button
                v-if="msg.sender_type === 'seller' && isTimeShown(msg.id)"
                type="button"
                @click.stop="openContext(msg.id, $event)"
                class="text-gray-400 hover:text-gray-600 text-lg px-1"
                aria-label="Mở menu tin nhắn"
              >
                ⋮
              </button>

              <!-- Context menu (fixed để luôn click được) -->
              <div
                v-if="contextMenu.open && contextMenu.messageId === msg.id && msg.sender_type === 'seller'"
                id="ctx-menu"
                class="fixed z-[9999] bg-white border rounded shadow-lg text-sm"
                :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px', minWidth: '180px' }"
                @click.stop
              >
                <button
                  @click="startReply(msg)"
                  class="block w-full text-left px-3 py-2 hover:bg-gray-100"
                  type="button"
                >
                  Trả lời
                </button>
                <button
                  v-if="canEdit(msg)"
                  @click="startEdit(msg)"
                  class="block w-full text-left px-3 py-2 hover:bg-gray-100"
                  type="button"
                >
                  Sửa
                </button>
                <button
                  @click="askRevoke(msg)"
                  class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-red-600"
                  type="button"
                >
                  Thu hồi
                </button>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <!-- BAR trạng thái trên input: Reply / Editing / Confirm revoke -->
        <div
          v-if="selectedSession"
          class="px-3 pt-2 space-y-2 border-t bg-white"
        >
          <!-- Reply preview -->
          <div v-if="replyTarget" class="flex items-start gap-2 text-sm bg-gray-50 border rounded p-2">
            <div class="border-l-4 border-blue-500 pl-2 flex-1">
              <div class="font-medium text-gray-700">Trả lời</div>
              <div class="text-gray-600 line-clamp-2">{{ replyTarget.message || '[Không có nội dung]' }}</div>
            </div>
            <button class="text-gray-500 hover:text-gray-700" @click="cancelReply" type="button">✕</button>
          </div>

          <!-- Editing bar -->
          <div v-if="editing.active" class="flex items-center justify-between text-sm bg-amber-50 border border-amber-200 rounded p-2">
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

        <!-- INPUT BAR -->
        <form
          v-if="selectedSession"
          class="sticky bottom-0 left-0 right-0 z-20 p-3 border-t border-gray-200 bg-white flex flex-col gap-2 w-full backdrop-blur-0"
          @submit.prevent="onSubmit"
          style="padding-bottom: env(safe-area-inset-bottom);"
        >
          <div class="flex gap-2 flex-wrap max-h-32 overflow-y-auto custom-scrollbars">
            <div v-for="(img, i) in selectedImages" :key="i" class="relative group">
              <img :src="img.url" class="w-20 h-20 object-cover rounded-lg border border-gray-300" />
              <button
                type="button"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 grid place-items-center hover:bg-red-600"
                @click="removeImage(i)"
              >×</button>
            </div>
          </div>

          <div class="flex items-center gap-2 relative">
            <label class="cursor-pointer hover:opacity-80 transition" title="Đính kèm ảnh">
              <i class="fa fa-paperclip text-[20px]"></i>
              <input type="file" multiple accept="image/*" class="hidden" @change="handleFileChange" ref="fileInput" />
            </label>

            <input
              v-model="message"
              ref="messageInput"
              type="text"
              :placeholder="editing.active ? 'Nhập nội dung sửa...' : 'Nhập tin nhắn...'"
              class="flex-1 bg-gray-100 text-[#212121] px-4 py-2 rounded-full text-sm focus:outline-none"
              @focus="onInputFocus"
            />

            <button type="button" @click="toggleEmojiPicker" class="text-xl" title="Emoji">😊</button>
            <emoji-picker id="emojiPicker" class="absolute bottom-14 right-4 hidden z-50"></emoji-picker>

            <button
              type="submit"
              :disabled="isSending"
              class="bg-[#189EFF] hover:bg-[#0f89e0] px-4 py-2 rounded-full text-white font-medium disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {{ isSending ? 'Đang gửi...' : (editing.active ? 'Lưu' : 'Gửi') }}
            </button>
          </div>
        </form>

        <!-- TOAST -->
        <div
          v-if="toast.open"
          class="fixed left-1/2 -translate-x-1/2 bottom-3 z-[9999] bg-black/80 text-white text-sm px-3 py-2 rounded"
        >
          {{ toast.text }}
        </div>
      </main>
    </div>

    <!-- Image Viewer -->
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
          >
            ✕
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, onUnmounted, computed } from "vue";
definePageMeta({ layout: "default-seller" });

/* ===== state sẵn có ===== */
const selectedImages = ref([]);
const message = ref("");
const seller = ref({});
const chatSessions = ref([]);
const loadingSessions = ref(false);

const isLoadingMessages = ref(false);
const selectedSession = ref(null);
const chatContainer = ref(null);
const messageInput = ref(null);
const pollingInterval = ref(null);
const currentMessages = ref([]);
const fileInput = ref(null);
const isSending = ref(false);
let lastPollingSessionId = null;
const page = ref(1);
const limit = 20;
const hasMore = ref(true);
const isLoadingMore = ref(false);
const showSidebar = ref(true);
const imageViewer = ref({ visible: false, url: null });

const isSmallScreen = ref(false);
const updateScreenFlag = () => { try { isSmallScreen.value = window.innerWidth < 640; } catch {} };

/* ===== NEW: edit/reply/delete & menu ===== */
const contextMenu = ref({ open: false, messageId: null, x: 0, y: 0 });
const editing = ref({ active: false, id: null });
const replyTarget = ref(null);
const confirmRevoke = ref({ open: false, id: null });

const toast = ref({ open: false, text: "" });
const showToast = (text = "", ms = 1800) => {
  toast.value = { open: true, text };
  setTimeout(() => (toast.value.open = false), ms);
};

/* 5 phút cho sửa (chỉ cho phép sửa trong 5 phút đầu) */
const EDIT_WINDOW_MS = 5 * 60 * 1000;
const nowMs = ref(Date.now());
let editTick = null;

/* Hiển thị giờ theo tin */
const shownTimes = ref(new Set());
function toggleTime(id) {
  if (!id) return;
  if (shownTimes.value.has(id)) shownTimes.value.delete(id);
  else shownTimes.value.add(id);
  // đóng menu nếu đang mở cho tin khác
  if (contextMenu.value.open && contextMenu.value.messageId !== id) contextMenu.value.open = false;
}
function isTimeShown(id) { return shownTimes.value.has(id); }

/* ====== utils & config ====== */
const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const DEFAULT_AVATAR = config.public.mediaBaseUrl + "avatars/default.jpg";
const getAvatar = (avatar) => (avatar && String(avatar).trim() !== "" ? avatar : DEFAULT_AVATAR);
const mediaBaseUrl = config.public.mediaBaseUrl;
const formatTime = (ts) => ts ? new Date(ts).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : "";
const formatPrice = (price) => new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price);

/* ====== reply meta render helper ====== */
const getReplyMeta = (msg) => {
  const a = (msg.attachments || []).find((x) => x.file_type === "reply");
  return a?.meta_data || msg.meta_data?.reply_to || null;
};

/* ====== file/emoji/viewer helpers ====== */
const handleFileChange = (e) => {
  const files = Array.from(e.target?.files || []);
  selectedImages.value.push(...files.map((file) => ({ file, url: URL.createObjectURL(file) })));
  if (e.target) e.target.value = "";
};
const removeImage = (index) => selectedImages.value.splice(index, 1);
const toggleEmojiPicker = () => document.getElementById("emojiPicker")?.classList.toggle("hidden");
const openImageViewer = (url) => { if (!url) return; imageViewer.value.visible = true; imageViewer.value.url = url; };
const closeImageViewer = () => { imageViewer.value.visible = false; imageViewer.value.url = null; };
const handleEscKey = (e) => { if (e.key === "Escape" && imageViewer.value.visible) closeImageViewer(); if (e.key === "Escape" && contextMenu.value.open) closeContext(); };
const openSidebarMobile = () => { showSidebar.value = true; selectedSession.value = null; };
const closeSessionMobile = () => { showSidebar.value = false; };
const onInputFocus = () => setTimeout(() => { scrollToBottom(); messageInput.value?.scrollIntoView?.({ block: "end", behavior: "smooth" }); }, 50);

/* ===== lifecycle ===== */
onMounted(async () => {
  updateScreenFlag();
  window.addEventListener("resize", updateScreenFlag);

  if (!customElements.get("emoji-picker")) await import("emoji-picker-element");
  const emojiPicker = document.getElementById("emojiPicker");
  emojiPicker?.addEventListener("emoji-click", (e) => (message.value += e.detail.unicode));

  document.addEventListener("click", onDocClick);
  window.addEventListener("keydown", handleEscKey);

  // ticking 15s để tự cập nhật trạng thái 5 phút
  editTick = setInterval(() => (nowMs.value = Date.now()), 15000);

  const token = localStorage.getItem("access_token");
  if (!token) return;

  try {
    const sellerRes = await fetch(`${API}/sellers/me`, { headers: { Authorization: `Bearer ${token}` } });
    const dataSeller = await sellerRes.json();
    if (!sellerRes.ok) throw new Error("Không thể lấy dữ liệu seller");
    seller.value = dataSeller?.seller || {};
    if (!seller.value?.id) return;

    loadingSessions.value = true;
    const sessionsRes = await fetch(`${API}/chat/sessions?user_id=${seller.value.id}&type=seller`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const sessionsData = await sessionsRes.json();
    chatSessions.value = Array.isArray(sessionsData) ? sessionsData : sessionsData?.data || sessionsData?.sessions || [];
  } catch (e) {
    console.error(e);
  } finally {
    loadingSessions.value = false;
  }
});

onUnmounted(() => {
  stopPollingMessages();
  window.removeEventListener("keydown", handleEscKey);
  window.removeEventListener("resize", updateScreenFlag);
  document.removeEventListener("click", onDocClick);
  if (editTick) clearInterval(editTick);
});

/* ===== sessions ===== */
async function selectSession(session) {
  selectedSession.value = session;
  showSidebar.value = false;
  const token = localStorage.getItem("access_token");
  if (!token || !session?.id) return;

  // reset trạng thái bar
  editing.value = { active: false, id: null };
  replyTarget.value = null;
  confirmRevoke.value.open = false;

  isLoadingMessages.value = true;
  try {
    const response = await fetch(`${API}/chat/messages/${session.id}`, { headers: { Authorization: `Bearer ${token}` } });
    const data = await response.json();
    currentMessages.value = data.messages || data.data || [];

    shownTimes.value = new Set();

    await fetch(`${API}/chat/messages/${session.id}/read`, {
      method: "POST",
      headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
      body: JSON.stringify({ sender_type: "seller" }),
    }).catch(() => {});
    session.unread_count = 0;
    await nextTick();
    scrollToBottom();
  } catch (err) {
    console.error(err);
  } finally {
    isLoadingMessages.value = false;
  }
}

/* ===== send / edit / reply / revoke ===== */
const onSubmit = async () => {
  if (editing.value.active) await commitEdit();
  else await sendMessage();
};

async function sendMessage() {
  if (isSending.value) return;
  const text = message.value.trim();
  const hasImages = selectedImages.value.length > 0;
  if (!text && !hasImages) return;

  isSending.value = true;
  const token = localStorage.getItem("access_token");
  if (!token || !seller.value?.id || !selectedSession.value?.id) { isSending.value = false; return; }

  const tempId = "tem-" + Date.now();
  const tempMessage = {
    id: tempId,
    sender_type: "seller",
    message: text || "",
    message_type: hasImages ? "image" : "text",
    created_at: new Date().toISOString(),
    attachments: hasImages ? selectedImages.value.map((img) => ({ url: URL.createObjectURL(img.file), temp: true })) : [],
    status: "uploading",
    meta_data: replyTarget.value
      ? { reply_to: { id: replyTarget.value.id, text: replyTarget.value.message, sender_type: replyTarget.value.sender_type } }
      : undefined
  };
  currentMessages.value.push(tempMessage);
  await nextTick(scrollToBottom);

  const formData = new FormData();
  formData.append("session_id", selectedSession.value.id);
  formData.append("sender_id", seller.value.id);
  formData.append("sender_type", "seller");
  formData.append("message_type", hasImages ? "image" : "text");
  if (text) formData.append("message", text);
  if (replyTarget.value) {
    formData.append("meta_data[reply_to][id]", replyTarget.value.id);
    formData.append("meta_data[reply_to][text]", replyTarget.value.message || "");
    formData.append("meta_data[reply_to][sender_type]", replyTarget.value.sender_type || "");
  }
  selectedImages.value.forEach((img) => formData.append("file[]", img.file));

  try {
    const res = await fetch(`${API}/chat/message`, { method: "POST", headers: { Authorization: `Bearer ${token}` }, body: formData });
    const result = await res.json();
    if (!res.ok) throw new Error(result.message || "Send failed");

    const newMessage = {
      id: result.message.id,
      sender_type: "seller",
      message: result.message.message,
      message_type: result.message.message_type,
      created_at: new Date().toISOString(),
      attachments: result.attachments || [],
      status: "sent",
      meta_data: tempMessage.meta_data
    };
    const index = currentMessages.value.findIndex((m) => m.id === tempId);
    if (index !== -1) currentMessages.value[index] = newMessage;

    selectedImages.value.forEach((img) => URL.revokeObjectURL(img.file));
    message.value = "";
    selectedImages.value = [];
    replyTarget.value = null;
    if (fileInput.value) fileInput.value.value = null;
    await nextTick(scrollToBottom);
  } catch (e) {
    console.error(e);
    showToast("Gửi thất bại");
    // xoá temp
    currentMessages.value = currentMessages.value.filter((m) => m.id !== tempId);
  } finally {
    isSending.value = false;
  }
}

const canEdit = (msg) => {
  if (!msg) return false;
  if (msg.sender_type !== "seller") return false;
  if (msg.message_type !== "text") return false;
  const created = new Date(msg.created_at).getTime();
  if (!created) return false;
  return nowMs.value - created <= EDIT_WINDOW_MS; // chỉ trong 5 phút đầu
};

const startEdit = (msg) => {
  editing.value = { active: true, id: msg.id };
  message.value = msg.message || "";
  contextMenu.value.open = false;
};
const cancelEdit = () => {
  editing.value = { active: false, id: null };
  message.value = "";
};
const commitEdit = async () => {
  const newContent = message.value.trim();
  if (!newContent) {
    showToast("Nội dung trống");
    return;
  }
  const msg = currentMessages.value.find((m) => m.id === editing.value.id);
  if (!canEdit(msg)) {
    showToast("Đã quá 5 phút — không thể sửa");
    cancelEdit();
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
      const idx = currentMessages.value.findIndex((m) => m.id === editing.value.id);
      if (idx !== -1) {
        currentMessages.value[idx].message = newContent;
        currentMessages.value[idx].status = "edited";
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

/* Reply */
const startReply = (msg) => {
  replyTarget.value = { id: msg.id, message: msg.message, sender_type: msg.sender_type };
  contextMenu.value.open = false;
};
const cancelReply = () => (replyTarget.value = null);

/* Revoke (delete) inline */
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
      currentMessages.value = currentMessages.value.filter((m) => m.id !== id);
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

/* ===== polling / paging ===== */
function startPollingMessages() {
  const sessionId = selectedSession.value?.id;
  if (!sessionId) return;
  if (lastPollingSessionId === sessionId && pollingInterval.value) return;

  stopPollingMessages();
  lastPollingSessionId = sessionId;

  pollingInterval.value = setInterval(async () => {
    const token = localStorage.getItem("access_token");
    if (!token) { stopPollingMessages(); return; }
    try {
      const res = await fetch(`${API}/chat/messages/${sessionId}`, { headers: { Authorization: `Bearer ${token}` } });
      const data = await res.json();
      const msgs = data.messages || data.data || [];
      if (JSON.stringify(currentMessages.value) !== JSON.stringify(msgs)) {
        currentMessages.value = msgs;
        await nextTick(scrollToBottom);
      }
    } catch (e) { console.error(e); }
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
  const el = chatContainer.value;
  if (!el) return;
  el.scrollTop = el.scrollHeight + 9999;
};

const loadMessages = async () => {
  const token = localStorage.getItem("access_token");
  if (!token || !selectedSession.value?.id) return;

  try {
    isLoadingMore.value = true;
    const res = await fetch(
      `${API}/chat/messages/${selectedSession.value.id}?page=${page.value}&limit=${limit}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );
    const data = await res.json();
    const newMessages = data?.data || data?.messages || [];
    if (newMessages.length < limit) hasMore.value = false;

    const reversed = [...newMessages].reverse();
    currentMessages.value = currentMessages.value?.length ? [...reversed, ...currentMessages.value] : reversed;

    if (page.value === 1) {
      await fetch(`${API}/chat/messages/${selectedSession.value.id}/read`, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
        body: JSON.stringify({ sender_type: "seller" }),
      }).catch(() => {});
    }

    page.value++;
    await nextTick();
  } catch (err) {
    console.error("Lỗi tải thêm tin nhắn:", err);
  } finally {
    isLoadingMore.value = false;
  }
};

/* Infinite scroll: lên gần top thì tải thêm */
const handleScroll = () => {
  const el = chatContainer.value;
  if (!el || isLoadingMore.value || !hasMore.value) return;
  if (el.scrollTop < 50) loadMessages();
};

/* Khi đổi session: start polling và reset timestamp */
watch(selectedSession, (nv) => {
  stopPollingMessages();
  shownTimes.value = new Set();
  if (nv?.id) startPollingMessages();
});

/* ===== context menu helpers ===== */
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
</script>

<style scoped>
/* Keep page fixed; only sidebar list & messages scroll */
:global(html), :global(body), :global(#__nuxt) { height: 100%; overflow: hidden; }

/* Smooth mobile sidebar slide */
.slide-left-slow-enter-active,
.slide-left-slow-leave-active { transition: transform 0.7s cubic-bezier(.22,.61,.36,1); }
.slide-left-slow-enter-from,
.slide-left-slow-leave-to { transform: translateX(0%); }
.slide-left-slow-leave-from,
.slide-left-slow-enter-to { transform: translateX(-100%); }

/* Message fade */
.message-fade-enter-active,
.message-fade-leave-active { transition: all .25s ease; }
.message-fade-enter-from { opacity: 0; transform: translateY(6px); }
.message-fade-enter-to   { opacity: 1; transform: translateY(0); }
.message-fade-leave-from { opacity: 1; transform: translateY(0); }
.message-fade-leave-to   { opacity: 0; transform: translateY(6px); }

/* Image viewer (fade) */
.fade-enter-active, .fade-leave-active { transition: opacity .25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Scrollbar */
.custom-scrollbars::-webkit-scrollbar { width: 8px; height: 8px; }
.custom-scrollbars::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
.custom-scrollbars::-webkit-scrollbar-track { background: transparent; }

/* Timestamp subtle style */
.msg-time { opacity: .85; line-height: 1.1; }
</style>
