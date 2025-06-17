import { ref } from 'vue'

const notification = ref({
  show: false,
  message: '',
  type: 'success' // 'success' or 'error'
})

export function useNotification() {
  const showNotification = (message, type = 'success') => {
    notification.value = {
      show: true,
      message,
      type
    }
    
    // Auto hide after 3 seconds
    setTimeout(() => {
      hideNotification()
    }, 3000)
  }

  const hideNotification = () => {
    notification.value.show = false
  }

  return {
    notification,
    showNotification,
    hideNotification
  }
} 