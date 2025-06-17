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
    const discounts = ref<Discount[]>([
        {
            id: 1,
            name: 'Giảm 50K đơn từ 300K',
            code: 'SAVE50K',
            description: 'Giảm 50.000đ cho đơn hàng từ 300.000đ',
            discount_type: 'fixed',
            discount_value: 50000,
            usage_limit: 100,
            min_order_value: 300000,
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            status: 'active'
        },
        {
            id: 2,
            name: 'Giảm 15% tối đa 100K',
            code: 'PERCENT15',
            description: 'Giảm 15% tối đa 100.000đ cho mọi đơn hàng',
            discount_type: 'percentage',
            discount_value: 15,
            usage_limit: 50,
            min_order_value: 0,
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            status: 'active'
        },
        {
            id: 3,
            name: 'Giảm 100K đơn từ 1 triệu',
            code: 'SAVE100K',
            description: 'Giảm 100.000đ cho đơn hàng từ 1.000.000đ',
            discount_type: 'fixed',
            discount_value: 100000,
            usage_limit: 30,
            min_order_value: 1000000,
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            status: 'active'
        },
        {
            id: 4,
            name: 'Giảm 20% tối đa 200K',
            code: 'PERCENT20',
            description: 'Giảm 20% tối đa 200.000đ cho đơn từ 500.000đ',
            discount_type: 'percentage',
            discount_value: 20,
            usage_limit: 20,
            min_order_value: 500000,
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            status: 'active'
        },
        {
            id: 5,
            name: 'Freeship đơn từ 200K',
            code: 'FREESHIP',
            description: 'Miễn phí vận chuyển cho đơn từ 200.000đ',
            discount_type: 'fixed',
            discount_value: 35000,
            usage_limit: 200,
            min_order_value: 200000,
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            status: 'active'
        }
    ])
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

    return {
        discounts,
        selectedDiscounts,
        loading,
        error,
        fetchDiscounts,
        applyDiscount,
        removeDiscount,
        calculateDiscount
    }
} 