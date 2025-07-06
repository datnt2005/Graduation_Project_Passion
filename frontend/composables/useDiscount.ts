import { ref } from 'vue'

interface Discount {
    id: number
    name: string
    code: string
    description: string
    discount_type: 'percentage' | 'fixed'
    discount_value: number
    usage_limit: number
    min_order_value: number
    start_date: string
    end_date: string
    status: 'active' | 'inactive' | 'expired'
}

export const useDiscount = () => {
    const discounts = ref<Discount[]>([])
    const loading = ref<boolean>(false)
    const error = ref<string | null>(null)
    const selectedDiscounts = ref<Discount[]>([])
    const config = useRuntimeConfig()

    const fetchDiscounts = async () => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return navigateTo('/auth/login')
        }

        loading.value = true
        error.value = null

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })

            if (!res.ok) {
                throw new Error('Lỗi khi lấy danh sách mã giảm giá')
            }

            const data = await res.json()
            discounts.value = data.data.filter((discount: Discount) => 
                discount.status === 'active' && 
                new Date(discount.end_date) > new Date()
            )
        } catch (err) {
            console.error('Error fetching discounts:', err)
            error.value = err instanceof Error ? err.message : 'Lỗi không xác định'
        } finally {
            loading.value = false
        }
    }

    const fetchMyVouchers = async () => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return navigateTo('/auth/login')
        }

        loading.value = true
        error.value = null

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-vouchers`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })

            if (!res.ok) {
                throw new Error('Lỗi khi lấy danh sách voucher của bạn')
            }

            const data = await res.json()
            discounts.value = data.data || []
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Lỗi không xác định'
        } finally {
            loading.value = false
        }
    }

    const showErrorNotification = (message: string): void => {
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

    const showSuccessNotification = (message: string): void => {
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


    // const applyDiscount = (discount: Discount) => {
    //     if (selectedDiscounts.value.length >= 1) {
    //         showErrorNotification('Chỉ được chọn tối đa 1 mã giảm giá')
    //         return
    //     }

    //     if (selectedDiscounts.value.find(d => d.id === discount.id)) {
    //         showErrorNotification('Mã giảm giá này đã được áp dụng')
    //         return
    //     }

    //     selectedDiscounts.value.push(discount)
    //     // thông báo áp dụng mã thành công 
    //     showSuccessNotification('Mã giảm giá được áp dụng')

    //     // const index = discounts.value.findIndex(d => d.id === discount.id)
    //     // if (index !== -1) {
    //     //     discounts.value.splice(index, 1)
    //     // }

    //     console.log('Applied discount:', discount)
    //     console.log('Selected discounts:', selectedDiscounts.value)
    // }

    const applyDiscount = (discount: Discount) => {
        if (selectedDiscounts.value.length >= 2) {
            alert('Chỉ được chọn tối đa 2 mã giảm giá')
            return
        }
        
        if (selectedDiscounts.value.find(d => d.id === discount.id)) {
            alert('Mã giảm giá này đã được áp dụng')
            return
        }

        selectedDiscounts.value.push(discount)
        const index = discounts.value.findIndex(d => d.id === discount.id)
        if (index !== -1) {
            discounts.value.splice(index, 1)
        }

        console.log('Applied discount:', discount)
        console.log('Selected discounts:', selectedDiscounts.value)
    }

    const removeDiscount = (discountId: number) => {
        const index = selectedDiscounts.value.findIndex(d => d.id === discountId)
        if (index !== -1) {
            const removedDiscount = selectedDiscounts.value.splice(index, 1)[0]
            discounts.value.push(removedDiscount)
        }
    }

    const calculateDiscount = (total: number) => {
        let totalDiscount = 0
        selectedDiscounts.value.forEach(discount => {
            if (total >= (discount.min_order_value || 0)) {
                if (discount.discount_type === 'percentage') {
                    totalDiscount += (total * discount.discount_value / 100)
                } else {
                    totalDiscount += discount.discount_value
                }
            }
        })
        return totalDiscount
    }

    const saveVoucherByCode = async (code: string) => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return { success: false, message: 'Vui lòng đăng nhập để tiếp tục' }
        }
        loading.value = true
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/save-by-code`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ code })
            })
            const data = await res.json()
            if (res.ok && data.success) {
                // Thêm voucher mới vào danh sách
                discounts.value.unshift(data.data)
                return { success: true, message: data.message }
            } else {
                return { success: false, message: data.message || 'Có lỗi xảy ra' }
            }
        } catch (err) {
            return { success: false, message: 'Lỗi không xác định' }
        } finally {
            loading.value = false
        }
    }

    const fetchMyVouchers = async () => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return
        }
        loading.value = true
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-vouchers`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            const data = await res.json()
            if (res.ok && data.success) {
                discounts.value = data.data
            } else {
                error.value = data.message || 'Không lấy được danh sách voucher'
            }
        } catch (err) {
            error.value = 'Lỗi không xác định'
        } finally {
            loading.value = false
        }
    }

    // Lấy các coupon mà user đã lưu
    const fetchUserCoupons = async () => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return
        }
        loading.value = true
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-vouchers`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            const data = await res.json()
            if (res.ok && data.success) {
                discounts.value = data.data
            } else {
                error.value = data.message || 'Không lấy được danh sách voucher đã lưu'
            }
        } catch (err) {
            error.value = 'Lỗi không xác định'
        } finally {
            loading.value = false
        }
    }

    // Xoá mã giảm giá đã lưu của user
    const deleteUserCoupon = async (discountId: number) => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục'
            return { success: false, message: 'Vui lòng đăng nhập để tiếp tục' }
        }
        loading.value = true
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-voucher/${discountId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            const data = await res.json()
            if (res.ok && data.success) {
                // Xoá khỏi danh sách
                discounts.value = discounts.value.filter(d => d.id !== discountId)
                return { success: true, message: data.message }
            } else {
                return { success: false, message: data.message || 'Không xoá được mã giảm giá' }
            }
        } catch (err) {
            return { success: false, message: 'Lỗi không xác định' }
        } finally {
            loading.value = false
        }
    }

    const saveVoucherByCode = async (code: string) => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            return { success: false, message: 'Vui lòng đăng nhập để lưu voucher' }
        }
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/save-by-code`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ code })
            })
            const data = await res.json()
            return { success: res.ok, message: data.message || (res.ok ? 'Lưu voucher thành công' : 'Lưu voucher thất bại') }
        } catch (e) {
            return { success: false, message: 'Lỗi hệ thống' }
        }
    }

    const deleteUserCoupon = async (discountId: number) => {
        const token = localStorage.getItem('access_token')
        if (!token) {
            return { success: false, message: 'Vui lòng đăng nhập để xoá voucher' }
        }
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-voucher/${discountId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            const data = await res.json()
            return { success: res.ok, message: data.message || (res.ok ? 'Xoá voucher thành công' : 'Xoá voucher thất bại') }
        } catch (e) {
            return { success: false, message: 'Lỗi hệ thống' }
        }
    }

    return {
        discounts,
        selectedDiscounts,
        loading,
        error,
        fetchDiscounts,
        fetchMyVouchers,
        applyDiscount,
        removeDiscount,
        calculateDiscount,
        getShippingDiscount,
        saveVoucherByCode,
        fetchUserCoupons,
        deleteUserCoupon
    }
} 