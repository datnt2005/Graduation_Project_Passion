import { ref } from 'vue'

export const useAlert = () => {
  const showAlert = ref(false)
  const alertMessage = ref('')
  const alertType = ref('success')

  const showMessage = (message: string, type: 'success' | 'error' = 'success') => {
    console.log('useAlert: Showing message:', { message, type })
    alertMessage.value = message
    alertType.value = type
    showAlert.value = true

    // Auto hide after 5 seconds
    setTimeout(() => {
      console.log('useAlert: Auto hiding alert')
      showAlert.value = false
    }, 5000)
  }

  return {
    showAlert,
    alertMessage,
    alertType,
    showMessage
  }
}

// Create a global instance
const globalAlert = useAlert()
export const useGlobalAlert = () => globalAlert 