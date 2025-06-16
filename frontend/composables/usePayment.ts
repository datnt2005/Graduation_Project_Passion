import { ref } from 'vue'

interface PaymentMethod {
    id: number
    name: string
    status: string
}

type PaymentMethodType = 'VNPAY' | 'MOMO' | 'COD'

interface PaymentResult {
    url: string
}

// Create a singleton instance
const paymentMethods = ref<PaymentMethod[]>([])
const loading = ref<boolean>(false)
const error = ref<string | null>(null)

export const usePayment = () => {
    const config = useRuntimeConfig()

    const fetchPaymentMethods = async () => {
        // Return cached data if available
        if (paymentMethods.value.length > 0) {
            return paymentMethods.value
        }

        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return navigateTo('/auth/login')
        }

        loading.value = true
        error.value = null

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/payment-methods`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })

            if (!res.ok) {
                if (res.status === 401) {
                    error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
                    localStorage.removeItem('access_token')
                    return navigateTo('/auth/login')
                }
                throw new Error('Lỗi khi lấy phương thức thanh toán')
            }

            const data = await res.json()
            paymentMethods.value = data.data
            return paymentMethods.value
        } catch (err: any) {
            console.error('Lỗi:', err)
            error.value = err.message || 'Không thể lấy phương thức thanh toán'
            return []
        } finally {
            loading.value = false
        }
    }

    const processPayment = async (orderId: number, paymentMethod: PaymentMethodType): Promise<PaymentResult> => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            navigateTo('/auth/login')
            throw new Error('Vui lòng đăng nhập để tiếp tục')
        }

        loading.value = true
        error.value = null

        try {
            if (paymentMethod === 'VNPAY' || paymentMethod === 'MOMO') {
                const endpoint = paymentMethod === 'VNPAY' ? 'vnpay/create' : 'momo/create'
                const res = await fetch(`${config.public.apiBaseUrl}/payments/${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ order_id: orderId })
                })

                if (!res.ok) {
                    throw new Error(`Lỗi khi tạo thanh toán ${paymentMethod}`)
                }

                const data = await res.json()
                return { url: data.data.payment_url }
            } else {
                // COD or other payment methods
                return { url: `/order-success/${orderId}` }
            }
        } catch (err: any) {
            console.error('Lỗi:', err)
            error.value = err.message || 'Không thể xử lý thanh toán'
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        paymentMethods,
        loading,
        error,
        fetchPaymentMethods,
        processPayment
    }
}