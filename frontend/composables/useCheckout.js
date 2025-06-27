import { ref, computed } from 'vue';
import { useRuntimeConfig, navigateTo } from '#app';
import { useCart } from '~/composables/useCart';
import { usePayment } from '~/composables/usePayment';
import { useDiscount } from '~/composables/useDiscount';
import { useToast } from '~/composables/useToast';

export function useCheckout(shippingRef, selectedShippingMethod, selectedAddress) {
  const config = useRuntimeConfig();
  const { toast } = useToast();
  const {
    cart,
    loading,
    error,
    selectedItems,
    parsePrice,
    selectStoreItems,
    fetchCart,
  } = useCart();
  const {
    paymentMethods,
    loading: paymentLoading,
    error: paymentError,
    fetchPaymentMethods,
    processPayment,
  } = usePayment();
  const {
    discounts,
    selectedDiscounts,
    loading: discountLoading,
    error: discountError,
    fetchDiscounts,
    applyDiscount,
    removeDiscount,
    calculateDiscount,
    getShippingDiscount,
  } = useDiscount();

  const selectedPaymentMethod = ref('');

  // Derive cartItems from cart.stores
  const cartItems = computed(() =>
    cart.value.stores
      .flatMap((store) => (store.items || []).filter((item) => selectedItems.value.has(item.id)))
      .filter((item) => item && item.product_variant_id && item.quantity)
  );

  // Total based on selected items (reusing selectedTotal from useCart)
  const total = computed(() => {
    return cart.value.stores.reduce((total, store) => {
      return (
        total +
        (store.items || []).reduce((storeTotal, item) => {
          if (item && selectedItems.value.has(item.id)) {
            return storeTotal + (parsePrice(item.sale_price) || parsePrice(item.price)) * (item.quantity || 1);
          }
          return storeTotal;
        }, 0)
      );
    }, 0);
  });

  // Shipping fee calculations
  const rawShippingFee = computed(() => {
    const raw = shippingRef.value?.fees?.[selectedShippingMethod.value];
    return raw ? parsePrice(raw) : 0;
  });

  const finalShippingFee = computed(() => {
    const discount = getShippingDiscount(total.value);
    return Math.max(0, rawShippingFee.value - discount);
  });

  // Final total including discounts and shipping
  const finalTotal = computed(() => {
    const baseTotal = total.value;
    const productDiscount = calculateDiscount(baseTotal);
    return Math.max(0, baseTotal - productDiscount + finalShippingFee.value);
  });

  // Formatted price utilities

const formatPrice = (price) => {
  const parsed = parsePrice(price);
  return parsed.toLocaleString('vi-VN');
};

  const formattedTotal = computed(() => `${formatPrice(total.value)} đ`);
  const formattedFinalTotal = computed(() => `${formatPrice(finalTotal.value)} đ`);
  const formattedFinalShippingFee = computed(() => `${formatPrice(finalShippingFee.value)} đ`);

  // Payment method label
  const getPaymentMethodLabel = (methodName) => {
    switch (methodName) {
      case 'COD':
        return 'Thanh toán khi nhận hàng';
      case 'VNPAY':
        return 'Thanh toán qua VNPAY';
      case 'MOMO':
        return 'Thanh toán qua Ví MoMo';
      default:
        return methodName;
    }
  };

  const placeOrder = async () => {
    // Validate inputs
    if (!cartItems.value.length) {
      toast('error', 'Giỏ hàng trống hoặc chưa chọn sản phẩm');
      return;
    }
    if (!selectedPaymentMethod.value) {
      toast('error', 'Vui lòng chọn phương thức thanh toán');
      return;
    }
    if (!selectedShippingMethod.value) {
      toast('error', 'Vui lòng chọn hình thức giao hàng');
      return;
    }
    if (!selectedAddress.value?.id) {
      toast('error', 'Vui lòng chọn địa chỉ giao hàng');
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        toast('error', 'Vui lòng đăng nhập để tiếp tục');
        window.dispatchEvent(new CustomEvent('openLoginModal'));
        return;
      }

      // Fetch user data
      const userResponse = await fetch(`${config.public.apiBaseUrl}/me`, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });

      if (!userResponse.ok) {
        if (userResponse.status === 401) {
          localStorage.removeItem('access_token');
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          throw new Error('Phiên đăng nhập hết hạn');
        }
        throw new Error('Không thể lấy thông tin người dùng');
      }

      const { data: userData } = await userResponse.json();
      if (!userData?.id) {
        throw new Error('Không tìm thấy thông tin người dùng');
      }

      // Prepare order data
      const orderData = {
        user_id: userData.id,
        address_id: selectedAddress.value.id,
        payment_method: selectedPaymentMethod.value,
        service_id: selectedShippingMethod.value,
        discount_id: selectedDiscounts.value?.[0]?.id || null,
        items: cartItems.value.map((item) => {
          if (!item.productVariant?.product?.id) {
            throw new Error(`Sản phẩm không hợp lệ: ${item.id}`);
          }
          return {
            product_id: item.productVariant.product.id,
            product_variant_id: item.product_variant_id,
            quantity: item.quantity,
            price: parsePrice(item.sale_price || item.price),
          };
        }),
      };

      // Create order
      const orderResponse = await fetch(`${config.public.apiBaseUrl}/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(orderData),
      });

      if (!orderResponse.ok) {
        const errorData = await orderResponse.json();
        throw new Error(errorData.message || 'Lỗi khi tạo đơn hàng');
      }

      const { data: orderResult } = await orderResponse.json();

      // Handle payment
      if (selectedPaymentMethod.value === 'COD') {
        await navigateTo(`/order-success/${orderResult.id}`);
      } else {
        const { url } = await processPayment(orderResult.id, selectedPaymentMethod.value);
        if (url) {
          window.location.href = url; // Use window.location for external payment URLs
        } else {
          throw new Error('Không nhận được URL thanh toán');
        }
      }

      // Refresh cart after successful order
      await fetchCart();
    } catch (err) {
      error.value = err.message || 'Có lỗi xảy ra khi đặt hàng';
      toast('error', error.value);
      console.error('Place order error:', err);
    } finally {
      loading.value = false;
    }
  };

  return {
    cartItems,
    total,
    formattedTotal,
    finalTotal,
    formattedFinalTotal,
    rawShippingFee,
    finalShippingFee,
    formattedFinalShippingFee,
    loading,
    error,
    paymentMethods,
    paymentLoading,
    paymentError,
    discounts,
    selectedDiscounts,
    discountLoading,
    discountError,
    selectedPaymentMethod,
    fetchPaymentMethods,
    fetchDiscounts,
    applyDiscount,
    removeDiscount,
    calculateDiscount,
    getShippingDiscount,
    formatPrice,
    parsePrice,
    getPaymentMethodLabel,
    placeOrder,
    selectStoreItems,
  };
}