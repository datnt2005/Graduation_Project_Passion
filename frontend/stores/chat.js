import { defineStore } from 'pinia'
import axios from 'axios'
import { useRuntimeConfig } from '#app'

export const useChatStore = defineStore('chat', {
  state: () => ({
    isOpen: false,
    messages: [],
    currentSession: null,
    sessions: []
  }),

  actions: {
    openChat(session = null) {
      this.isOpen = true
      this.currentSession = session
      console.log('🔓 Opening chat with session:', session)
    },

    closeChat() {
      this.isOpen = false
      this.currentSession = null
      this.messages = []
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
      })
    },

    async getOrCreateSession(userId, sellerId, sellerInfo) {
      try {
        const config = useRuntimeConfig()
        const API = config.public.apiBaseUrl
        const token = localStorage.getItem('access_token')
        if (!token) throw new Error('Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.')

        let session = this.sessions.find(
          s => s.user_id === userId && s.seller.id === sellerId
        )

        if (!session) {
          const { data } = await axios.get(`${API}/chat/sessions`, {
            params: { user_id: userId, type: 'user' },
            headers: { Authorization: `Bearer ${token}` }
          })

          session = data.find(s => s.seller.id === sellerId)

          if (!session) {
            const { data: newSession } = await axios.post(
              `${API}/chat/sessions`,
              {
                user_id: userId,
                seller_id: sellerId,
                type: 'user'
              },
              { headers: { Authorization: `Bearer ${token}` } }
            )

            session = {
              id: newSession.id,
              user_id: userId,
              seller: {
                id: sellerId,
                store_name: sellerInfo.store_name || 'Unknown Seller',
                avatar: sellerInfo.avatar || null
              }
            }
          }

          this.sessions.push(session)
        }

        this.openChat(session)
        return session
      } catch (error) {
        console.error('❌ Lỗi khi lấy/tạo session:', error)
        throw new Error(error.message || 'Không thể tạo session chat')
      }
    },

    async sendProductMessage(product, userId, sellerId) {
      if (!product || !product.id || !userId || !sellerId) {
        console.error('❌ Dữ liệu không hợp lệ:', { product, userId, sellerId })
        throw new Error('Dữ liệu không hợp lệ')
      }

      const config = useRuntimeConfig()
      const API = config.public.apiBaseUrl
      const token = localStorage.getItem('access_token')
      const R2_BASE = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/'
      const DEFAULT_PRODUCT = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/products/images/default.jpg'

        const getImageUrl = (path) => {
      if (!path) return DEFAULT_PRODUCT
      const cleaned = path.trim().replace(/^\/+|\/+$/g, '')
      if (/^https?:\/\//.test(cleaned)) return cleaned
      return `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${cleaned}`
    }

      const imageUrl = getImageUrl(product.image)

      const session = await this.getOrCreateSession(userId, sellerId, {
        store_name: product.store_name,
        avatar: product.avatar
      })

      const message = {
        message: `Mình quan tâm sản phẩm: ${product.name} - ${product.price}đ`,
        message_type: 'product',
        sender: 'user',
        metadata: {
          productId: product.id,
          variantId: product.variantId || null,
          productLink: product.link || window.location.href,
          file_url: imageUrl
        }
      }

      const tempId = 'pending_' + Date.now()
      this.sendMessage({ ...message, id: tempId, pending: true })

      const payload = new FormData()
      payload.append('session_id', session.id)
      payload.append('sender_id', userId)
      payload.append('receiver_id', sellerId)
      payload.append('sender_type', 'user')
      payload.append('message_type', 'product')
      payload.append('message', message.message)

      // Gửi từng trường meta_data như array trong form-data
     const meta = {
        productId: product.id,
        variantId: product.variantId || null,
        productLink: product.link || window.location.href,
        file_url: imageUrl
      }
      payload.append('meta_data', JSON.stringify(meta))

      if (product.variantId) payload.append('meta_data[variantId]', product.variantId)
      payload.append('meta_data[productLink]', product.link || window.location.href)
      payload.append('meta_data[file_url]', imageUrl)

      try {
        const { data } = await axios.post(`${API}/chat/send-message`, payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        })

        this.messages = this.messages.filter(msg => msg.id !== tempId)

        const attachments = Array.isArray(data.chat_message.attachments)
          ? data.chat_message.attachments
          : (typeof data.chat_message.attachments === 'string'
            ? JSON.parse(data.chat_message.attachments)
            : [])

        this.sendMessage({
          id: data.chat_message.id,
          sender: data.chat_message.sender_type || 'user',
          message: data.chat_message.message,
          message_type: data.chat_message.message_type || 'product',
          attachments,
          metadata: message.metadata,
          pending: false,
          error: false
        })

        await this.loadMessages(session.id)
      } catch (error) {
        console.error('❌ Lỗi gửi tin nhắn sản phẩm:', error)
        this.messages = this.messages.map(m => {
          if (m.id === tempId) return { ...m, error: true }
          return m
        })
        throw error
      }
    },

  //  async loadMessages(sessionId) {
  //       try {
  //         const config = useRuntimeConfig()
  //         const API = config.public.apiBaseUrl
  //         const token = localStorage.getItem('access_token')
  //         if (!token) throw new Error('Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.')

  //         const { data } = await axios.get(`${API}/chat/messages/${sessionId}`, {
  //           headers: { Authorization: `Bearer ${token}` }
  //         })

  //         if (!Array.isArray(data)) {
  //           console.error('❌ Dữ liệu trả về không phải mảng:', data)
  //           return
  //         }

  //         this.messages = data.map(msg => {
  //           let attachments = []
  //           try {
  //             attachments = Array.isArray(msg.attachments)
  //               ? msg.attachments
  //               : (typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : [])
  //           } catch (e) {
  //             console.warn('⚠️ Không parse được attachments:', msg.attachments, e)
  //           }

  //           let metadata = null
  //           try {
  //             metadata = typeof msg.metadata === 'string'
  //               ? JSON.parse(msg.metadata)
  //               : msg.metadata

  //             // ✅ xử lý metadata có ảnh sản phẩm
  //             if (metadata?.file_url) {
  //               const imageUrl = getImageUrl(metadata.file_url)
  //               metadata.image = imageUrl

  //               attachments.push({
  //                 file_url: imageUrl,
  //                 file_type: 'image',
  //                 file_name: metadata?.file_name || 'Sản phẩm'
  //               })
  //             }
  //           } catch (e) {
  //             console.warn('⚠️ Không parse được metadata:', msg.metadata, e)
  //           }

  //           return {
  //             id: msg.id,
  //             sender: msg.sender_type,
  //             message: msg.message,
  //             message_type: msg.message_type,
  //             attachments,
  //             metadata,
  //             pending: false,
  //             error: false,
  //             created_at: msg.created_at
  //           }
  //         })
  //       } catch (error) {
  //         console.error('❌ Lỗi load messages:', error)
  //         throw error
  //       }
  //     }
  async loadMessages(sessionId) {
  try {
    const config = useRuntimeConfig();
    const API = config.public.apiBaseUrl;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.');

    const { data } = await axios.get(`${API}/chat/messages/${sessionId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (!Array.isArray(data)) {
      console.error('❌ Dữ liệu trả về không phải mảng:', data);
      return;
    }

    this.messages = data.map(msg => {
      let attachments = [];
      try {
        attachments = Array.isArray(msg.attachments)
          ? msg.attachments
          : (typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : []);
        // Xử lý message_data từ attachments
        attachments = attachments.map(attachment => {
          if (attachment.message_data) {
            const metadata = JSON.parse(attachment.message_data);
            return {
              ...attachment,
              file_url: metadata.file_url || attachment.file_url || 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/products/images/xGu0ps0BpyL6YHUcD2XpAK8xB0vKArvw8HqXnIip.jpg', // Fallback URL
              file_type: attachment.file_type || 'image',
              file_name: metadata.file_name || attachment.file_name || msg.message.split(':')[1]?.split('-')[0].trim() || 'Sản phẩm'
            };
          }
          return attachment;
        });
      } catch (e) {
        console.warn('⚠️ Không parse được attachments:', msg.attachments, e);
      }

      let metadata = null;
      try {
        metadata = typeof msg.metadata === 'string'
          ? JSON.parse(msg.metadata)
          : msg.metadata;
      } catch (e) {
        console.warn('⚠️ Không parse được metadata:', msg.metadata, e);
      }

      return {
        id: msg.id,
        sender: msg.sender_type,
        message: msg.message,
        message_type: msg.message_type,
        attachments,
        metadata,
        pending: false,
        error: false,
        created_at: msg.created_at
      };
    });
  } catch (error) {
    console.error('❌ Lỗi load messages:', error);
    throw error;
  }
},
    }
})
