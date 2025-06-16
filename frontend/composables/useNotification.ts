import { ref } from 'vue'

export const useNotification = () => {
  const showNotification = ref(false)
  const notificationMessage = ref('')
  const notificationType = ref('success')

  const showMessage = (message: string, type: 'success' | 'error' = 'success') => {
    console.log('Showing notification:', { message, type })
    notificationMessage.value = message
    notificationType.value = type
    showNotification.value = true

    setTimeout(() => {
      showNotification.value = false
    }, 3000)
  }

  return {
    showNotification,
    notificationMessage,
    notificationType,
    showMessage
  }
} 