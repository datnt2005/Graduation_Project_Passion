import { ref, computed } from 'vue'
import Swal from 'sweetalert2'
import { useCart } from '~/composables/useCart'
import { usePayment } from '~/composables/usePayment'
import { useDiscount } from '~/composables/useDiscount'



export function useCheckout(config, shippingRef, selectedShippingMethod, selectedAddress, provinces, districts, wards) {
  const { cartItems, cartTotal, loading, error, fetchCart } = useCart()
  const { paymentMethods, loading: paymentLoading, error: paymentError, fetchPaymentMethods, processPayment } = usePayment()
  const { discounts, selectedDiscounts, loading: discountLoading, error: discountError, fetchDiscounts, applyDiscount, removeDiscount, calculateDiscount } = useDiscount()

  const selectedPaymentMethod = ref('')

  const showSuccessNotification = (message) => {
    Swal.fire({
      toast: true,
      position: 'top-end', // đổi sang top-end để toast nằm bên phải
      icon: 'success',
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
  const showErrorNotification = (message) => {
    Swal.fire({
      toast: true,
      position: 'top-end', // đổi sang top-end để toast nằm bên phải
      icon: 'error', // icon X
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

  const formatPrice = (price) => {
    if (!price) return '0'
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  }

  const finalTotal = computed(() => {
    const couponDiscount = calculateDiscount(cartTotal.value || 0)
    const total = Math.max(0, (cartTotal.value || 0) - couponDiscount)
    return total
  })
  const formattedFinalTotal = computed(() => formatPrice(finalTotal.value) + ' đ')
  const formattedCartTotal = computed(() => formatPrice(cartTotal.value) + ' đ')

  const getPaymentMethodLabel = (methodName) => {
    switch (methodName) {
      case 'COD': return 'Thanh toán khi nhận hàng'
      case 'VNPAY': return 'Thanh toán qua VNPAY'
      case 'MOMO': return 'Thanh toán qua Ví MoMo'
      default: return methodName
    }
  }

  const placeOrder = async () => {
    if (!cartItems.value?.length) {
      showErrorNotification('Giỏ hàng trống')
      return
    }
    if (!selectedPaymentMethod.value) {
      showErrorNotification('Vui lòng chọn phương thức thanh toán')
      return
    }
    const serviceId = selectedShippingMethod?.value || shippingRef?.value?.selectedMethod
if (!serviceId) {
  showErrorNotification('Vui lòng chọn hình thức giao hàng')
  return
}


  try {
    loading.value = true
    error.value = null
    const token = localStorage.getItem('access_token')
    if (!token) {
      window.location.href = '/auth/login'
      return
    }

    const userResponse = await fetch(`${config.public.apiBaseUrl}/me`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!userResponse.ok) throw new Error('Không thể lấy thông tin người dùng')
    const { data: userData } = await userResponse.json()
    if (!userData?.id) throw new Error('Không tìm thấy thông tin người dùng')

    const orderData = {
      user_id: userData.id,
      address_id: selectedAddress.value?.id || null,
      payment_method: selectedPaymentMethod.value,
      service_id: serviceId, // ✅ đã định nghĩa đúng
      discount_id: selectedDiscounts.value?.[0]?.id || null,
      items: cartItems.value.map(item => ({
        product_id: item.productVariant?.product?.id,
        product_variant_id: item.product_variant_id,
        quantity: item.quantity,
        price: item.price
      }))
    }
      const orderResponse = await fetch(`${config.public.apiBaseUrl}/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(orderData)
      })
      if (!orderResponse.ok) {
        const errorData = await orderResponse.json()
        throw new Error(errorData.message || 'Lỗi khi tạo đơn hàng')
      }
      const { data: orderResult } = await orderResponse.json()
      if (selectedPaymentMethod.value === 'COD') {
        window.location.href = `/order-success/${orderResult.id}`
      } else {
        const { url } = await processPayment(orderResult.id, selectedPaymentMethod.value)
        if (url) {
          window.location.href = url
        } else {
          throw new Error('Không nhận được URL thanh toán')
        }
      }
    } catch (err) {
      error.value = err.message || 'Có lỗi xảy ra khi đặt hàng'
      showErrorNotification(error.value)
    } finally {
      loading.value = false
    }
  }

  return {
    cartItems,
    cartTotal,
    loading,
    error,
    paymentMethods,
    paymentLoading,
    paymentError,
    discounts,
    selectedDiscounts,
    discountLoading,
    discountError,
    fetchCart,
    fetchPaymentMethods,
    fetchDiscounts,
    applyDiscount,
    removeDiscount,
    calculateDiscount,
    selectedPaymentMethod,
    formatPrice,
    finalTotal,
    formattedFinalTotal,
    formattedCartTotal,
    getPaymentMethodLabel,
    placeOrder,
    showSuccessNotification,
    showErrorNotification
  }
}