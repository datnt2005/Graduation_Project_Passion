import { defineStore } from "pinia";
import axios from "axios";
import { useRuntimeConfig } from "#app";

export const useChatStore = defineStore("chat", {
  state: () => ({
    isOpen: false,
    messages: [],
    currentSession: null,
    sessions: [],
  }),

  actions: {
    openChat(session = null) {
      this.isOpen = true;
      this.currentSession = session;
    },

    closeChat() {
      this.isOpen = false;
      this.currentSession = null;
      this.messages = [];
    },

    sendMessage(message) {
      this.messages.push({
        id: message.id || `temp_${Date.now()}`,
        sender: message.sender || "user",
        message: message.message,
        message_type: message.message_type || "text",
        attachments: message.attachments || [],
        metadata: message.metadata || null,
        pending: message.pending || false,
        error: message.error || false,
      });
    },

    async getOrCreateSession(userId, sellerId, sellerInfo) {
      const config = useRuntimeConfig();
      const API = config.public.apiBaseUrl;
      const token = localStorage.getItem("access_token");

      if (!token) {
        throw new Error("Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.");
      }

      try {
        // 1. Tìm session trong danh sách hiện tại
        let session = this.sessions.find(
          (s) => s.user_id === userId && s.seller?.id === sellerId
        );

        // 2. Nếu chưa có session -> kiểm tra lại trên server
        if (!session) {
          const { data: responseData } = await axios.get(
            `${API}/chat/sessions`,
            {
              params: { user_id: userId, type: "user" },
              headers: { Authorization: `Bearer ${token}` },
            }
          );

          const sessionList = Array.isArray(responseData)
            ? responseData
            : responseData?.data || [];

          session = sessionList.find((s) => s.seller?.id === sellerId);

          // 3. Nếu vẫn chưa có -> tạo session mới
          if (!session) {
            const { data: newSession } = await axios.post(
              `${API}/chat/session`,
              {
                user_id: userId,
                seller_id: sellerId,
                type: "user",
              },
              {
                headers: { Authorization: `Bearer ${token}` },
              }
            );

            session = {
              id: newSession.id,
              user_id: userId,
              seller: {
                id: sellerId,
                store_name: sellerInfo.store_name || "Unknown Seller",
                avatar: sellerInfo.avatar || null,
              },
            };
          }

          this.sessions.push(session); // cache lại session
        }

        this.openChat(session); // mở khung chat
        return session;
      } catch (err) {
        console.error(
          "❌ getOrCreateSession error:",
          err.response?.data || err.message
        );
        throw new Error("Không thể tạo hoặc lấy session chat");
      }
    },
    async sendProductMessage(product, userId, sellerId) {
      if (!product || !product.id || !userId || !sellerId) {
        console.error("❌ Dữ liệu không hợp lệ:", {
          product,
          userId,
          sellerId,
        });
        throw new Error("Dữ liệu không hợp lệ");
      }
      console.log("📨 Sending product message:", product);
      
      const config = useRuntimeConfig();
      const API = config.public.apiBaseUrl;
      const mediaBaseUrl = config.public.mediaBaseUrl;
      const token = localStorage.getItem("access_token");
      const DEFAULT_PRODUCT = mediaBaseUrl + "products/default.jpg";

      const getImageUrl = (path) => {
        if (!path) return DEFAULT_PRODUCT;
        const cleaned = path.trim().replace(/^\/+|\/+$/g, "");
        if (/^https?:\/\//.test(cleaned)) return cleaned;
        return `${mediaBaseUrl}${cleaned}`;
      };

      const imageUrl = getImageUrl(product.image);

      const session = await this.getOrCreateSession(userId, sellerId, {
        store_name: product.store_name,
        avatar: product.avatar,
      });

      const message = {
        message: `Mình quan tâm sản phẩm: ${product.name} - ${product.price}đ`,
        message_type: "product",
        sender: "user",
        metadata: {
          productId: product.id,
          name: product.name,
          price: product.price,
          variantId: product.variantId || null,
          slug: product.slug,
          file_url: imageUrl,
        },
      };

      const tempId = "pending_" + Date.now();
      this.sendMessage({ ...message, id: tempId, pending: true });

      const payload = new FormData();
      payload.append("session_id", session.id);
      payload.append("sender_id", userId);
      payload.append("receiver_id", sellerId);
      payload.append("sender_type", "user");
      payload.append("message_type", "product");
      payload.append("message", message.message);

      const meta = message.metadata;
      Object.entries(meta).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          payload.append(`meta_data[${key}]`, value);
        }
      });

      try {
        const { data } = await axios.post(`${API}/chat/message`, payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data",
          },
        });

        this.messages = this.messages.filter((msg) => msg.id !== tempId);

        const attachments = this.safeParseAttachments(data.attachments);

        this.sendMessage({
          id: data.message?.id,
          sender: data.message?.sender_type || "user",
          message: data.message?.message,
          message_type: data.message?.message_type || "product",
          attachments,
          metadata: message.metadata,
          pending: false,
          error: false,
        });

        await this.loadMessages(session.id);
      } catch (error) {
        console.error("❌ Lỗi gửi tin nhắn sản phẩm:", error);
        this.messages = this.messages.map((m) =>
          m.id === tempId ? { ...m, error: true } : m
        );
        throw error;
      }
    },

    async loadMessages(sessionId) {
      try {
        const config = useRuntimeConfig();
        const API = config.public.apiBaseUrl;
        const token = localStorage.getItem("access_token");
        if (!token)
          throw new Error("Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.");

        const { data } = await axios.get(`${API}/chat/messages/${sessionId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (!Array.isArray(data)) {
          // console.error("❌ Dữ liệu trả về không phải mảng:", data);
          return;
        }

        this.messages = data.map((msg) => {
          const attachments = this.parseAttachments(
            msg.attachments,
            msg.message
          );
          const metadata = this.safeParseJSON(msg.metadata);

          return {
            id: msg.id,
            sender: msg.sender_type,
            message: msg.message,
            message_type: msg.message_type,
            attachments,
            metadata,
            pending: false,
            error: false,
            created_at: msg.created_at,
          };
        });
      } catch (error) {
        console.error("❌ Lỗi load messages:", error);
        throw error;
      }
    },

    safeParseJSON(input) {
      try {
        return typeof input === "string" ? JSON.parse(input) : input || null;
      } catch (e) {
        console.warn("⚠️ Không parse được JSON:", input, e);
        return null;
      }
    },

    safeParseAttachments(input) {
      try {
        return typeof input === "string" ? JSON.parse(input) : input || [];
      } catch (e) {
        console.warn("⚠️ Không parse được attachments:", input, e);
        return [];
      }
    },

    parseAttachments(raw, fallbackMessage) {
      let attachments = [];

      try {
        const parsed = Array.isArray(raw)
          ? raw
          : typeof raw === "string"
          ? JSON.parse(raw)
          : [];

        attachments = parsed.map((att) => {
          if (att.message_data) {
            const meta = this.safeParseJSON(att.message_data);
            return {
              ...att,
              file_url: meta?.file_url || att.file_url || "",
              file_type: att.file_type || "image",
              file_name:
                meta?.file_name ||
                att.file_name ||
                fallbackMessage?.split(":")[1]?.split("-")[0]?.trim() ||
                "Sản phẩm",
            };
          }
          return att;
        });
      } catch (err) {
        console.warn("⚠️ Không parse được attachments:", raw, err);
      }

      return attachments;
    },
  },
});