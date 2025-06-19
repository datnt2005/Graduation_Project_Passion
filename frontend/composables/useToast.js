import Swal from 'sweetalert2'

export function useToast() {
  const toast = (icon, title) => {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      width: '350px',
      padding: '10px 20px',
      customClass: { popup: 'text-sm rounded-md shadow-md' },
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toastEl) => {
        toastEl.addEventListener('mouseenter', () => Swal.stopTimer())
        toastEl.addEventListener('mouseleave', () => Swal.resumeTimer())
      }
    })
  }

  return { toast }
}