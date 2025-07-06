import { defineStore } from 'pinia';
import axios from 'axios';
import { useRuntimeConfig } from '#app';

export const useChatStore = defineStore('chat', {
  state: () => ({
    isOpen: false,
    messages: [],
    currentSession: null,
    sessions: []
  }),

  actions: {
    openChat(session = null) {
      this.isOpen = true;
      this.currentSession = session;
      console.log('Opening chat with session:', session);
    },

    closeChat() {
      this.isOpen = false;
      this.currentSession = null;
      this.messages = [];
    },

    sendMessage(message) {
      this.messages.push({
        id: message.id || `temp_${Date.now()}`,
        sender: message.sender || 'user',
        message: message.message,
        message_type: message.message_type || 'text',
        attachments: message.attachments || [],
        metadata: message.metadata || null,
        pending: message.pending || false,
        error: message.error || false
      });
    },

    async getOrCreateSession(userId, sellerId, sellerInfo) {
      try {
        const config = useRuntimeConfig();
        const API = config.public.apiBaseUrl;
        const token = localStorage.getItem('access_token');

        let session = this.sessions.find(
          s => s.user_id === userId && s.seller.id === sellerId
        );

        if (!session) {
          const { data } = await axios.get(`${API}/chat/sessions`, {
            params: { user_id: userId, type: 'user' },
            headers: { Authorization: `Bearer ${token}` }
          });
          session = data.find(s => s.seller.id === sellerId);

          if (!session) {
            const { data: newSession } = await axios.post(
              `${API}/chat/sessions`,
              {
                user_id: userId,
                seller_id: sellerId,
                type: 'user'
              },
              { headers: { Authorization: `Bearer ${token}` } }
            );
            session = {
              id: newSession.id,
              user_id: userId,
              seller: {
                id: sellerId,
                store_name: sellerInfo.store_name || 'Unknown Seller',
                avatar: sellerInfo.avatar || null
              }
            };
            this.sessions.push(session);
          } else {
            this.sessions.push(session);
          }
        }

        this.openChat(session);
        return session;
      } catch (error) {
        console.error('❌ Lỗi khi lấy/tạo session:', error);
        throw new Error('Không thể tạo session chat');
      }
    },

    async sendProductMessage(product, userId, sellerId) {
      if (!product || !product.id || !userId || !sellerId) {
        console.error('Invalid product, userId, or sellerId data');
        throw new Error('Dữ liệu không hợp lệ');
      }

      const config = useRuntimeConfig();
      const API = config.public.apiBaseUrl;
      const token = localStorage.getItem('access_token');

      const session = await this.getOrCreateSession(userId, sellerId, {
        store_name: product.store_name || 'Unknown Seller',
        avatar: product.avatar || null
      });

      const message = {
        message: `Mình quan tâm sản phẩm: ${product.name} - ${product.price}đ`,
        attachments: product.image ? [{
          file_type: 'image',
          file_url: product.image
        }] : [],
        metadata: {
          productId: product.id,
          variantId: product.variantId || null,
          productLink: product.link || window.location.href
        },
        message_type: 'product',
        sender: 'user'
      };

      const tempId = 'pending_' + Date.now();
      this.sendMessage({ ...message, id: tempId, pending: true });

      const payload = new FormData();
      payload.append('session_id', session.id);
      payload.append('sender_id', userId);
      payload.append('receiver_id', sellerId);
      payload.append('sender_type', 'user');
      payload.append('message_type', 'product');
      payload.append('message', message.message);
      payload.append('attachments', JSON.stringify([
        { file_type: 'image', file_url: 'https://example.com/image.jpg' }
      ]));


      try {
        const { data } = await axios.post(`${API}/chat/send-message`, payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        });

        this.messages = this.messages.filter(msg => msg.id !== tempId);

        if (data && data.id) {
          this.sendMessage({
            id: data.id,
            sender: data.sender_type || 'user',
            message: data.message,
            message_type: data.message_type || 'product',
            attachments: message.attachments,
            metadata: message.metadata,
            pending: false,
            error: false
          });
        }

        await this.loadMessages(session.id);
      } catch (error) {
        console.error('❌ Lỗi gửi tin nhắn sản phẩm:', error);
        this.messages = this.messages.map(m => {
          if (m.id === tempId) return { ...m, error: true };
          return m;
        });
        throw error;
      }
    },

   async loadMessages(sessionId) {
  try {
    const config = useRuntimeConfig();
    const API = config.public.apiBaseUrl;
    const token = localStorage.getItem('access_token');

    const { data } = await axios.get(`${API}/chat/messages/${sessionId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    this.messages = data.map(msg => ({
      id: msg.id,
      sender: msg.sender_type,
      message: msg.message,
      message_type: msg.message_type,
      attachments: Array.isArray(msg.attachments)
        ? msg.attachments
        : (() => {
            try {
              return JSON.parse(msg.attachments || '[]');
            } catch (e) {
              console.error('❌ Lỗi parse attachments:', e, msg.attachments);
              return [];
            }
          })(),
      metadata: msg.metadata || null,
      pending: false,
      error: false
    }));
  } catch (error) {
    console.error('❌ Lỗi load messages:', error);
    throw error;
  }
  }

  }
});
