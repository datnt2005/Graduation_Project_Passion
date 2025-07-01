import Swal from 'sweetalert2'

export function useToast() {
  const toast = (icon = 'info', title = 'Thông báo') => {
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
      didOpen: (el) => {
        el.addEventListener('mouseenter', () => Swal.stopTimer())
        el.addEventListener('mouseleave', () => Swal.resumeTimer())
      }
    })
  }

  const showError = async (message = 'Đã xảy ra lỗi', title = '') => {
    const fullMessage = title ? `${title} ${message}` : message
    toast('error', fullMessage)
  }


  const showSuccess = async (message = 'Thành công') => {
    toast('success', message)
  }

  const showWarning = async (message = 'Cảnh báo') => {
    toast('warning', message)
  }

  const showInfo = async (message = 'Thông báo') => {
    toast('info', message)
  }

  const showConfirm = async (
    title = 'Bạn có chắc chắn?',
    text = 'Hành động này không thể hoàn tác',
    confirmButtonText = 'Xác nhận'
  ) => {
    return await Swal.fire({
      title,
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText,
      cancelButtonText: 'Hủy',
      reverseButtons: true,
    })
  }

  return { toast, showError, showSuccess, showWarning, showInfo, showConfirm }
}
