import { ref } from 'vue';
import Swal from 'sweetalert2';

interface Discount {
    id: number;
    name: string;
    code: string;
    description: string;
    discount_type: 'percentage' | 'fixed' | 'shipping_fee';
    discount_value: number;
    usage_limit: number;
    min_order_value: number;
    start_date: string;
    end_date: string;
    status: 'active' | 'inactive' | 'expired';
    seller_id?: number; // Added for shop-specific discounts
    is_saved?: boolean; // Thêm trường is_saved để theo dõi trạng thái lưu
}

export const useDiscount = () => {
    const discounts = ref<Discount[]>([]);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);
    const selectedDiscounts = ref<Discount[]>([]);
    const config = useRuntimeConfig();

    const fetchDiscounts = async () => {
        loading.value = true;
        error.value = null;

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/all`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                throw new Error('Lỗi khi lấy danh sách mã giảm giá');
            }

            const data = await res.json();
            console.log('Raw discounts from API:', data.data);
            
            discounts.value = data.data.filter((discount: Discount) => {
                const isValid = discount.status === 'active' &&
                    new Date(discount.end_date) > new Date() &&
                    discount.discount_value > 0 &&
                    discount.discount_value < 1000000; // Giới hạn tối đa 1 triệu
                
                if (!isValid) {
                    console.warn('Invalid discount filtered out:', discount);
                }
                
                return isValid;
            });
            
            console.log('Filtered discounts:', discounts.value);
        } catch (err) {
            console.error('Error fetching discounts:', err);
            error.value = err instanceof Error ? err.message : 'Lỗi không xác định';
        } finally {
            loading.value = false;
        }
    };

    const fetchSellerDiscounts = async (sellerId: number) => {
        loading.value = true;
        error.value = null;

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/seller/${sellerId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                throw new Error('Lỗi khi lấy danh sách mã giảm giá của shop');
            }

            const data = await res.json();
            console.log('Raw seller discounts from API:', data.data);
            
            const filteredDiscounts = data.data.filter((discount: Discount) => {
                const isValid = discount.status === 'active' &&
                    new Date(discount.end_date) > new Date() &&
                    discount.discount_value > 0 &&
                    discount.discount_value < 1000000; // Giới hạn tối đa 1 triệu
                
                if (!isValid) {
                    console.warn('Invalid seller discount filtered out:', discount);
                }
                
                return isValid;
            });
            
            console.log('Filtered seller discounts:', filteredDiscounts);
            return filteredDiscounts;
        } catch (err) {
            console.error('Error fetching seller discounts:', err);
            error.value = err instanceof Error ? err.message : 'Lỗi không xác định';
            return [];
        } finally {
            loading.value = false;
        }
    };

    const fetchMyVouchers = async () => {
        const token = localStorage.getItem('access_token');
        if (!token) {
            error.value = 'Vui lòng đăng nhập để tiếp tục';
            return navigateTo('/auth/login');
        }

        loading.value = true;
        error.value = null;

        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-vouchers`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!res.ok) {
                throw new Error('Lỗi khi lấy danh sách voucher của bạn');
            }

            const data = await res.json();
            discounts.value = data.data.map((discount: Discount) => ({
                ...discount,
                is_saved: true // Đánh dấu các voucher của tôi là đã lưu
            }));
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Lỗi không xác định';
        } finally {
            loading.value = false;
        }
    };

    const showErrorNotification = (message: string): void => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
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
    };

    const showSuccessNotification = (message: string): void => {
        Swal.fire({
            toast: true,
            position: 'top-end',
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
    };

    const applyDiscount = (discount: Discount) => {
        console.log('=== DEBUG applyDiscount ===');
        console.log('Applying discount:', discount);
        
        const isProductDiscount = discount.discount_type === 'percentage' || discount.discount_type === 'fixed';
        const isShippingDiscount = discount.discount_type === 'shipping_fee';

        console.log('Is product discount:', isProductDiscount);
        console.log('Is shipping discount:', isShippingDiscount);
        console.log('Current selected discounts:', selectedDiscounts.value);

        if (selectedDiscounts.value.find(d => d.id === discount.id)) {
            showErrorNotification('Mã giảm giá này đã được áp dụng');
            return;
        }

        // Xử lý shipping discount
        if (isShippingDiscount) {
            // Chỉ cho phép 1 shipping discount admin
            const removedShipping = selectedDiscounts.value.filter(d => d.discount_type === 'shipping_fee' && !d.seller_id);
            selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.discount_type === 'shipping_fee' && !d.seller_id));
            console.log('Removed shipping discounts:', removedShipping);
        }

        // Xử lý product discount với mutual exclusivity
        if (isProductDiscount) {
            if (discount.seller_id) {
                // Đây là shop discount - loại bỏ tất cả admin product discounts
                const removedAdmin = selectedDiscounts.value.filter(d => !d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(!d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed admin discounts:', removedAdmin);
                
                // Loại bỏ shop discount khác của cùng shop
                const removedShop = selectedDiscounts.value.filter(d => d.seller_id === discount.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.seller_id === discount.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed other shop discounts:', removedShop);
            } else {
                // Đây là admin discount - loại bỏ tất cả shop product discounts
                const removedShop = selectedDiscounts.value.filter(d => d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed shop discounts:', removedShop);
                
                // Loại bỏ admin product discount khác
                const removedAdmin = selectedDiscounts.value.filter(d => !d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(!d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed other admin discounts:', removedAdmin);
            }
        }

        selectedDiscounts.value.push(discount);
        console.log('Final selected discounts:', selectedDiscounts.value);
        console.log('=== END DEBUG ===');
        
        showSuccessNotification('Mã giảm giá được áp dụng');
    };

    const removeDiscount = (discountId: number) => {
        const index = selectedDiscounts.value.findIndex(d => d.id === discountId);
        if (index !== -1) {
            selectedDiscounts.value.splice(index, 1)[0];
            showSuccessNotification('Mã giảm giá được bỏ');
        }
    };

    const calculateDiscount = (total: number, shopId?: number) => {
        let totalDiscount = 0;

        // Lọc chỉ lấy product discounts (không phải shipping)
        const productDiscounts = selectedDiscounts.value.filter(d => 
            d.discount_type === 'percentage' || d.discount_type === 'fixed'
        );

        console.log('=== DEBUG calculateDiscount ===');
        console.log('Total:', total);
        console.log('ShopId:', shopId);
        console.log('All selected discounts:', selectedDiscounts.value);
        console.log('Product discounts:', productDiscounts);

        // Kiểm tra mutual exclusivity
        const adminDiscounts = productDiscounts.filter(d => !d.seller_id);
        const shopDiscounts = productDiscounts.filter(d => d.seller_id);

        console.log('Admin discounts:', adminDiscounts);
        console.log('Shop discounts:', shopDiscounts);

        // Nếu có cả admin và shop discount, chỉ áp dụng shop discount
        const discountsToApply = shopDiscounts.length > 0 ? shopDiscounts : adminDiscounts;

        console.log('Discounts to apply:', discountsToApply);

        discountsToApply.forEach(discount => {
            console.log('Processing discount:', discount);
            
            if (total >= (discount.min_order_value || 0)) {
                const value = Number(discount.discount_value);
                console.log('Discount value:', value, 'Type:', discount.discount_type);

                // Validation để tránh tính sai
                if (value <= 0 || value > 1000000) {
                    console.error('Invalid discount value:', value, 'for discount:', discount);
                    return;
                }

                if (discount.discount_type === 'percentage') {
                    // Validation cho percentage
                    if (value > 100) {
                        console.error('Percentage cannot be greater than 100%:', value);
                        return;
                    }
                    
                    let discountAmount = 0;
                    // Nếu là mã giảm giá shop, chỉ áp dụng cho shop đó
                    if (discount.seller_id && shopId && discount.seller_id === shopId) {
                        discountAmount = total * value / 100;
                        totalDiscount += discountAmount;
                        console.log('Shop percentage discount:', discountAmount);
                    } else if (!discount.seller_id && !shopId) {
                        // Mã toàn sàn, không truyền shopId => áp dụng cho toàn bộ đơn hàng
                        // Không tính discount admin ở đây nữa vì đã chia đều cho từng shop
                        console.log('Admin percentage discount - đã chia đều cho từng shop');
                    }
                } else {
                    // fixed
                    if (discount.seller_id && shopId && discount.seller_id === shopId) {
                        totalDiscount += value;
                        console.log('Shop fixed discount:', value);
                    } else if (!discount.seller_id && !shopId) {
                        // Không tính discount admin ở đây nữa vì đã chia đều cho từng shop
                        console.log('Admin fixed discount - đã chia đều cho từng shop');
                    }
                }
            } else {
                console.log('Order total not meet minimum requirement:', total, '<', discount.min_order_value);
            }
        });

        console.log('Final total discount:', totalDiscount);
        console.log('=== END DEBUG ===');

        return totalDiscount;
    };

    const getShippingDiscount = (total: number) => {
        // Chỉ lấy mã giảm giá phí ship toàn sàn (không phải của shop)
        const shippingDiscount = selectedDiscounts.value.find(
            d => d.discount_type === 'shipping_fee' && !d.seller_id
        );

        if (!shippingDiscount) return 0;

        if (total >= (shippingDiscount.min_order_value || 0)) {
            return Number(shippingDiscount.discount_value || 0);
        }

        return 0;
    };

    const formatPrice = (price: number): string => {
        if (!price || isNaN(price)) return '0';
        return Math.floor(price).toLocaleString('vi-VN');
    };

const saveVoucherByCode = async (code: string) => {
    const token = localStorage.getItem('access_token');
    if (!token) {
        return { success: false, message: 'Vui lòng đăng nhập để lưu voucher' };
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
        });
        const data = await res.json();
        return { success: res.ok, message: data.message || (res.ok ? 'Lưu voucher thành công' : 'Lưu voucher thất bại') };
    } catch (e) {
        return { success: false, message: 'Lỗi hệ thống' };
    }
};
    const deleteUserCoupon = async (discountId: number) => {
        const token = localStorage.getItem('access_token');
        if (!token) {
            return { success: false, message: 'Vui lòng đăng nhập để xoá voucher' };
        }
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/my-voucher/${discountId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
            const data = await res.json();
            if (res.ok) {
                const index = discounts.value.findIndex(d => d.id === discountId);
                if (index !== -1) {
                    discounts.value[index] = { ...discounts.value[index], is_saved: false };
                }
            }
            return { success: res.ok, message: data.message || (res.ok ? 'Xoá voucher thành công' : 'Xoá voucher thất bại') };
        } catch (e) {
            return { success: false, message: 'Lỗi hệ thống' };
        }
    };

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
    };
};