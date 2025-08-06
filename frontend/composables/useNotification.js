import { ref } from 'vue';

export function useNotification() {
  const showNotification = ref(false);
  const notificationMessage = ref('');
  const notificationType = ref('success');

  const setNotification = (message, type = 'success' ) => {
    notificationMessage.value = message;
    notificationType.value = type;
    showNotification.value = true;
    setTimeout(() => {
      showNotification.value = false;
    }, 3000);
  };

  return {
    showNotification,
    notificationMessage,
    notificationType,
    setNotification,
  };
}