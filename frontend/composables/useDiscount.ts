import { ref } from 'vue';
import Swal from 'sweetalert2'

interface Discount {
    id: number
    name: string
    code: string
    description: string
    discount_type: 'percentage' | 'fixed' | 'shipping_fee'
    discount_value: number
    usage_limit: number
    min_order_value: number
    start_date: string
    end_date: string
    status: 'active' | 'inactive' | 'expired'
    seller_id?: number // Added for shop-specific discounts
}

export const useDiscount = () => {
    const discounts = ref<Discount[]>([])
    const loading = ref<boolean>(false)
    const error = ref<string | null>(null)
    const selectedDiscounts = ref<Discount[]>([])
    const config = useRuntimeConfig()


    const fetchDiscounts = async () => {
        loading.value = true
        error.value = null

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/all`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
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

    const fetchSellerDiscounts = async (sellerId: number) => {
        loading.value = true
        error.value = null

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/seller/${sellerId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })

            if (!res.ok) {
                throw new Error('Lỗi khi lấy danh sách mã giảm giá của shop')
            }

            const data = await res.json()
            return data.data.filter((discount: Discount) =>
                discount.status === 'active' &&
                new Date(discount.end_date) > new Date()
            )
        } catch (err) {
            console.error('Error fetching seller discounts:', err)
            error.value = err instanceof Error ? err.message : 'Lỗi không xác định'
            return []
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
        const isProductDiscount = discount.discount_type === 'percentage' || discount.discount_type === 'fixed';
        const isShippingDiscount = discount.discount_type === 'shipping_fee';

        // Kiểm tra trùng mã (id)
        if (selectedDiscounts.value.find(d => d.id === discount.id)) {
            showErrorNotification('Mã giảm giá này đã được áp dụng');
            return;
        }

        // Đảm bảo không có nhiều hơn 1 voucher phí ship admin
        if (isShippingDiscount) {
            // Xóa tất cả các voucher phí ship admin cũ trước khi thêm mới
            selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.discount_type === 'shipping_fee' && !d.seller_id));
        }

        // Đảm bảo không có nhiều hơn 1 voucher shop cho mỗi shop
        if (isProductDiscount && discount.seller_id) {
            selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.seller_id === discount.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
        }

        // Đảm bảo không có nhiều hơn 1 voucher admin sản phẩm
        if (isProductDiscount && !discount.seller_id) {
            selectedDiscounts.value = selectedDiscounts.value.filter(d => !(!d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
        }

        // Hợp lệ → áp dụng
        selectedDiscounts.value.push(discount);
        showSuccessNotification('Mã giảm giá được áp dụng');
    };


    const removeDiscount = (discountId: number) => {
        const index = selectedDiscounts.value.findIndex(d => d.id === discountId)
        if (index !== -1) {
            selectedDiscounts.value.splice(index, 1)[0]
            // discounts.value.push(removedDiscount)
            // bỏ mã giảm giá thành công 
            showSuccessNotification('Mã giảm giá được bỏ')
        }
    }

    const calculateDiscount = (total: number) => {
        let totalDiscount = 0

        selectedDiscounts.value
            .filter(d => d.discount_type === 'percentage' || d.discount_type === 'fixed')
            .forEach(discount => {
                if (total >= (discount.min_order_value || 0)) {
                    const value = Number(discount.discount_value)

                    if (discount.discount_type === 'percentage') {
                        totalDiscount += total * value / 100
                    } else {
                        totalDiscount += value
                    }
                }
            })

        return totalDiscount
    }

    const getShippingDiscount = (total: number) => {
        const shippingDiscount = selectedDiscounts.value.find(
            d => d.discount_type === 'shipping_fee'
        );

        if (!shippingDiscount) return 0;

        // Nếu có điều kiện min_order_value → áp dụng
        if (total >= (shippingDiscount.min_order_value || 0)) {
            return Number(shippingDiscount.discount_value || 0);
        }

        return 0;
    };



    const formatPrice = (price: number): string => {
        if (!price || isNaN(price)) return '0'
        return Math.floor(price).toLocaleString('vi-VN') // đảm bảo không có dấu sai
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
        formatPrice,
        fetchDiscounts,
        fetchSellerDiscounts,
        fetchMyVouchers,
        applyDiscount,
        removeDiscount,
        calculateDiscount,
        getShippingDiscount,
        saveVoucherByCode,
        deleteUserCoupon
    }
} 