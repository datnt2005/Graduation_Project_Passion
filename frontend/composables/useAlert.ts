import Swal from 'sweetalert2'

export const useAlert = () => {
  const showMessage = (message: string, type: 'success' | 'error' = 'success') => {
    Swal.fire({
      toast: true,
      position: 'bottom-end',
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
    showMessage
  }
}

const globalAlert = useAlert()
export const useGlobalAlert = () => globalAlert