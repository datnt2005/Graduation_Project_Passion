import Swal from 'sweetalert2'

export function useNotification() {
  const showNotification = (message, type = 'success') => {
    Swal.fire({
      toast: true,
      position: 'top',
      icon: type,
      title: message,
      width: '350px',
      padding: '10px 20px',
      customClass: { popup: 'text-sm rounded-md shadow-md' },
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toastEl) => {
        toastEl.addEventListener('mouseenter', () => Swal.stopTimer());
        toastEl.addEventListener('mouseleave', () => Swal.resumeTimer());
      }
    });
  }
  return {
    showNotification
  }
}