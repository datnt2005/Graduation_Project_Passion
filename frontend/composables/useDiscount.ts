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
    seller_id?: number;
    is_saved?: boolean;
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
                    discount.discount_value < 1000000;
                
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
                    discount.discount_value < 1000000;
                
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
                is_saved: true
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

    const checkShopDiscount = async (discountId: number, sellerId: number, productIds: number[], orderValue: number) => {
        const token = localStorage.getItem('access_token');
        if (!token) {
            showErrorNotification('Vui lòng đăng nhập để kiểm tra mã giảm giá');
            return { success: false, message: 'Vui lòng đăng nhập để kiểm tra mã giảm giá' };
        }
        
        const requestData = {
            discount_id: discountId,
            seller_id: sellerId,
            product_ids: productIds,
            order_value: orderValue
        };
        
        console.log('=== DEBUG checkShopDiscount ===');
        console.log('Request data:', requestData);
        
        try {
            const res = await fetch(`${config.public.apiBaseUrl}/discounts/check-shop-discount`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(requestData)
            });
            
            console.log('Response status:', res.status);
            const data = await res.json();
            console.log('Response data:', data);
            
            if (data.error_code === 'USAGE_LIMIT_EXCEEDED' || (data.message && data.message.includes('hết lượt sử dụng'))) {
                showErrorNotification(data.message || 'Mã giảm giá đã hết lượt sử dụng');
            }
            return { 
                success: res.ok, 
                message: data.message || (res.ok ? 'Mã giảm giá có thể áp dụng' : 'Mã giảm giá không thể áp dụng'),
                data: data.data,
                error_code: data.error_code
            };
        } catch (e) {
            showErrorNotification('Lỗi hệ thống khi kiểm tra mã giảm giá');
            return { success: false, message: 'Lỗi hệ thống khi kiểm tra mã giảm giá' };
        }
    };

    const applyDiscount = async (discount: Discount) => {
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

        if (discount.seller_id) {
            const checkResult = await checkShopDiscount(discount.id, discount.seller_id, [], 0);
            if (checkResult.error_code === 'USAGE_LIMIT_EXCEEDED' || (checkResult.message && checkResult.message.includes('hết lượt sử dụng'))) {
                showErrorNotification(checkResult.message || 'Mã giảm giá đã hết lượt sử dụng');
                return;
            }
            if (!checkResult.success) {
                showErrorNotification(checkResult.message || 'Không thể áp dụng mã giảm giá');
                return;
            }
        }

        if (isShippingDiscount) {
            const removedShipping = selectedDiscounts.value.filter(d => d.discount_type === 'shipping_fee' && !d.seller_id);
            selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.discount_type === 'shipping_fee' && !d.seller_id));
            console.log('Removed shipping discounts:', removedShipping);
        }

        if (isProductDiscount) {
            if (discount.seller_id) {
                const removedAdmin = selectedDiscounts.value.filter(d => !d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(!d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed admin discounts:', removedAdmin);
                
                const removedShop = selectedDiscounts.value.filter(d => d.seller_id === discount.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.seller_id === discount.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed other shop discounts:', removedShop);
            } else {
                const removedShop = selectedDiscounts.value.filter(d => d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed shop discounts:', removedShop);
                
                const removedAdmin = selectedDiscounts.value.filter(d => !d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
                selectedDiscounts.value = selectedDiscounts.value.filter(d => !(!d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed')));
                console.log('Removed other admin discounts:', removedAdmin);
            }
        }

        selectedDiscounts.value.push(discount);
        console.log('Final selected discounts:', selectedDiscounts.value);
        console.log('=== END DEBUG ===');
        
        // Nếu là admin discount, emit event để checkout.vue có thể cập nhật shop discounts
        if (!discount.seller_id && (discount.discount_type === 'percentage' || discount.discount_type === 'fixed')) {
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('adminDiscountApplied', {
                    detail: {
                        discountId: discount.id,
                        discount: discount
                    }
                }));
            }
        }
        
        showSuccessNotification('Mã giảm giá được áp dụng');
    };

    const removeDiscount = (discountId: number) => {
        const index = selectedDiscounts.value.findIndex(d => d.id === discountId);
        if (index !== -1) {
            const removedDiscount = selectedDiscounts.value.splice(index, 1)[0];
            
            // Nếu là mã giảm giá admin (không có seller_id), cần cập nhật lại giá cho tất cả shop
            if (!removedDiscount.seller_id && (removedDiscount.discount_type === 'percentage' || removedDiscount.discount_type === 'fixed')) {
                // Emit event để checkout.vue có thể cập nhật lại giá shop
                if (typeof window !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('adminDiscountRemoved', {
                        detail: {
                            discountId: discountId,
                            discount: removedDiscount
                        }
                    }));
                }
            }
            
            showSuccessNotification('Mã giảm giá được bỏ');
        }
    };

    const calculateDiscount = (total: number, shopId?: number) => {
        let totalDiscount = 0;
        const productDiscounts = selectedDiscounts.value.filter(d => 
            d.discount_type === 'percentage' || d.discount_type === 'fixed'
        );
        const adminDiscounts = productDiscounts.filter(d => !d.seller_id);
        const shopDiscounts = productDiscounts.filter(d => d.seller_id);
        const discountsToApply = shopDiscounts.length > 0 ? shopDiscounts : adminDiscounts;

        console.log('=== DEBUG calculateDiscount ===');
        console.log('Total:', total);
        console.log('ShopId:', shopId);
        console.log('All selected discounts:', selectedDiscounts.value);
        console.log('Product discounts:', productDiscounts);
        console.log('Admin discounts:', adminDiscounts);
        console.log('Shop discounts:', shopDiscounts);
        console.log('Discounts to apply:', discountsToApply);

        discountsToApply.forEach(discount => {
            console.log('Processing discount:', discount);
            
            if (total >= (discount.min_order_value || 0)) {
                const value = Number(discount.discount_value);
                console.log('Discount value:', value, 'Type:', discount.discount_type);

                if (value <= 0 || value > 1000000) {
                    console.error('Invalid discount value:', value, 'for discount:', discount);
                    return;
                }

                if (discount.discount_type === 'percentage') {
                    if (value > 100) {
                        console.error('Percentage cannot be greater than 100%:', value);
                        return;
                    }
                    
                    if (discount.seller_id && shopId && discount.seller_id === shopId) {
                        const discountAmount = total * value / 100;
                        totalDiscount += discountAmount;
                        console.log('Shop percentage discount:', discountAmount);
                    } else if (!discount.seller_id && !shopId) {
                        console.log('Admin percentage discount - đã chia đều cho từng shop');
                    }
                } else {
                    if (discount.seller_id && shopId && discount.seller_id === shopId) {
                        totalDiscount += value;
                        console.log('Shop fixed discount:', value);
                    } else if (!discount.seller_id && !shopId) {
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
        deleteUserCoupon,
        checkShopDiscount
    };
};