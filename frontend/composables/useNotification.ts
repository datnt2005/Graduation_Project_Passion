import { reactive } from 'vue'

const notification = reactive({
  show: false,
  type: 'success', // hoặc 'error'
  message: ''
})

export const useNotification = () => {
  const showMessage = (message: string, type: 'success' | 'error' = 'success') => {
    notification.message = message
    notification.type = type
    notification.show = true

    setTimeout(() => {
      notification.show = false
    }, 3000)
  }

  const hideNotification = () => {
    notification.show = false
  }

  return {
    notification,
    showMessage,
    hideNotification
  }
}
