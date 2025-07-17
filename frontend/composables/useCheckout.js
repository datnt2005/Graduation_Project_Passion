import { ref, computed } from 'vue';
import { useRuntimeConfig, navigateTo } from '#app';
import { useCart } from '~/composables/useCart';
import { usePayment } from '~/composables/usePayment';
import { useDiscount } from '~/composables/useDiscount';
import { useToast } from '~/composables/useToast';
import { useRoute } from 'vue-router';
import { useAuth } from '~/composables/useAuth';

export function useCheckout(shippingRef, selectedShippingMethod, selectedAddress, storeNotes) {
  const config = useRuntimeConfig();
  const route = useRoute();
  const { toast } = useToast();
  const { logout } = useAuth();
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
  const canUseCod = ref(true);
  const isAccountBanned = ref(false); // State để theo dõi trạng thái tài khoản

  const shopDiscounts = ref({});
  const shopDiscountIds = ref({});

  const isBuyNow = computed(() => route.query.buyNow === 'true');
  const buyNowData = ref(null);

  const filteredPaymentMethods = computed(() => {
    if (canUseCod.value) {
      return paymentMethods.value;
    }
    return paymentMethods.value.filter(method => method.name !== 'COD');
  });

  const checkCodEligibility = async () => {
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        canUseCod.value = false;
        isAccountBanned.value = false;
        toast('error', 'Vui lòng đăng nhập để kiểm tra.');
        return;
      }

      const response = await fetch(`${config.public.apiBaseUrl}/orders/check-cod-eligibility`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Lỗi khi kiểm tra trạng thái.');
      }

      const { can_use_cod, is_account_banned, message } = await response.json();
      canUseCod.value = can_use_cod;
      isAccountBanned.value = is_account_banned;
      if (is_account_banned) {
        toast('error', message);
        // Đăng xuất người dùng và chuyển hướng về trang đăng nhập
        await logout();
        await navigateTo('/login');
      } else if (!can_use_cod) {
        toast('warning', message);
      }
    } catch (err) {
      console.error('Error checking COD eligibility:', err);
      canUseCod.value = false;
      toast('error', err.message || 'Không thể kiểm tra trạng thái.');
    }
  };

  const loadBuyNowData = () => {
    if (typeof window === 'undefined' || !window.localStorage) return;
    const storedData = localStorage.getItem('buy_now');
    if (storedData) {
      buyNowData.value = JSON.parse(storedData);
      const maxAge = 30 * 60 * 1000;
      if (Date.now() - buyNowData.value.timestamp > maxAge) {
        localStorage.removeItem('buy_now');
        buyNowData.value = null;
        toast('error', 'Dữ liệu buyNow đã hết hạn. Vui lòng chọn lại sản phẩm.');
      }
    }
  };

  const updateShopDiscount = (sellerId, discount, discountId = null) => {
    shopDiscounts.value[sellerId] = discount;
    if (discountId) {
      shopDiscountIds.value[sellerId] = discountId;
    }
  };

  const getShopDiscount = (sellerId) => {
    return shopDiscounts.value[sellerId] || 0;
  };

  const getShopDiscountId = (sellerId) => {
    return shopDiscountIds.value[sellerId] || null;
  };

  const cartItems = computed(() => {
    if (isBuyNow.value && buyNowData.value) {
      const price = parsePrice(buyNowData.value.price);
      return [{
        seller_id: buyNowData.value.seller_id || null,
        store_name: buyNowData.value.store_name || '',
        store_url: buyNowData.value.store_url || '',
        items: [{
          id: buyNowData.value.product_id,
          product: {
            id: buyNowData.value.product.id,
            name: buyNowData.value.product.name || 'Unknown Product',
            slug: buyNowData.value.product.slug || '',
            images: buyNowData.value.product.images || [],
          },
          productVariant: buyNowData.value.productVariant?.id ? {
            id: buyNowData.value.productVariant.id,
            sku: buyNowData.value.productVariant.sku || '',
            thumbnail: buyNowData.value.productVariant.thumbnail || '',
            attributes: buyNowData.value.productVariant.attributes || [],
          } : null,
          quantity: buyNowData.value.quantity,
          price: price,
          sale_price: price,
        }],
        store_total: price * buyNowData.value.quantity,
        discount: getShopDiscount(buyNowData.value.seller_id),
        selectedDiscountId: getShopDiscountId(buyNowData.value.seller_id)
      }];
    }

    if (!cart.value || !cart.value.stores) return [];

    return cart.value.stores.map((store) => {
      const items = (store.items || [])
        .filter((item) => item.is_selected)
        .map((item) => ({
          id: item.id,
          quantity: item.quantity,
          price: parsePrice(item.price),
          sale_price: parsePrice(item.sale_price || item.price),
          productVariant: item.productVariant?.id ? {
            id: item.productVariant.id,
            sku: item.productVariant.sku || '',
            thumbnail: item.productVariant.thumbnail || '',
            attributes: item.productVariant.attributes || [],
          } : null,
          product: item.product?.id ? {
            id: item.product.id,
            name: item.product.name || '',
            slug: item.product.slug || '',
            images: item.product.images || [],
          } : null
        }));

      const storeTotal = items.reduce((sum, item) => sum + item.sale_price * item.quantity, 0);

      return {
        seller_id: store.seller_id,
        store_name: store.store_name || '',
        store_url: store.store_url || '',
        items,
        store_total: storeTotal,
        discount: getShopDiscount(store.seller_id),
        selectedDiscountId: getShopDiscountId(store.seller_id)
      };
    });
  });

  const total = computed(() => {
    if (isBuyNow.value && buyNowData.value) {
      const price = parsePrice(buyNowData.value.price);
      const total = price * buyNowData.value.quantity;
      return total;
    }
    return cart.value.stores.reduce((total, store) => {
      return (
        total +
        (store.items || []).reduce((storeTotal, item) => {
          if (item && selectedItems.value.has(item.id)) {
            const price = parsePrice(item.sale_price || item.price);
            return storeTotal + price * (item.quantity || 1);
          }
          return storeTotal;
        }, 0)
      );
    }, 0);
  });

  const rawShippingFee = computed(() => {
    const raw = shippingRef.value?.fees?.[selectedShippingMethod.value];
    return raw ? parsePrice(raw) : 0;
  });

  const finalShippingFee = computed(() => {
    const discount = getShippingDiscount(total.value);
    return Math.max(0, rawShippingFee.value - discount);
  });

  const finalTotal = computed(() => {
    const baseTotal = total.value;
    const productDiscount = calculateDiscount(baseTotal);
    
    const shopDiscountsTotal = cartItems.value.reduce((sum, shop) => {
      return sum + (shop.discount || 0);
    }, 0);
    
    return Math.max(0, baseTotal - productDiscount - shopDiscountsTotal + finalShippingFee.value);
  });

  const formatPrice = (price) => {
    const parsed = parsePrice(price);
    return parsed.toLocaleString('vi-VN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  };

  const formattedTotal = computed(() => `${formatPrice(total.value)} đ`);
  const formattedFinalTotal = computed(() => `${formatPrice(finalTotal.value)} đ`);
  const formattedFinalShippingFee = computed(() => `${formatPrice(finalShippingFee.value)} đ`);

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
    if (isAccountBanned.value) {
      toast('error', 'Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận.');
      return;
    }

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
      if (!userData?.id) throw new Error('Không tìm thấy thông tin người dùng');

      const allItems = [];
      cartItems.value.forEach(store => {
        if (store.items && Array.isArray(store.items)) {
          store.items.forEach(item => {
            if (item.product && item.product.id) {
              allItems.push({
                product_id: item.product.id,
                product_variant_id: item.productVariant?.id || null,
                quantity: item.quantity,
                price: parsePrice(item.sale_price || item.price),
                seller_id: store.seller_id,
              });
            }
          });
        }
      });

      const notesBySeller = storeNotes?.value || {};
      const discountIds = [];
      cartItems.value.forEach(shop => {
        if (shop.selectedDiscountId) discountIds.push(shop.selectedDiscountId);
      });
      selectedDiscounts.value.forEach(discount => {
        if (!discountIds.includes(discount.id)) discountIds.push(discount.id);
      });

      const orderData = {
        user_id: userData.id,
        address_id: selectedAddress.value.id,
        address: selectedAddress.value.address || 'Chưa cung cấp địa chỉ',
        receiver_name: selectedAddress.value.name || userData.name || 'Chưa cung cấp tên',
        receiver_phone: selectedAddress.value.phone || 'Chưa cung cấp số điện thoại',
        payment_method: selectedPaymentMethod.value,
        service_id: selectedShippingMethod.value,
        discount_ids: discountIds,
        items: allItems,
        ward_id: selectedAddress.value.ward_id,
        district_id: selectedAddress.value.district_id,
        province_id: selectedAddress.value.province_id,
        is_buy_now: isBuyNow.value,
        skip_stock_check: true,
        store_notes: notesBySeller,
      };

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
        if (errorData.message.includes('COD') && errorData.can_use_cod === false) {
          canUseCod.value = false;
          toast('error', 'Bạn không thể sử dụng COD do có quá nhiều đơn hàng bị từ chối.');
          return;
        }
        if (errorData.message.includes('khóa') && errorData.is_account_banned) {
          isAccountBanned.value = true;
          toast('error', errorData.message);
          await logout();
          await navigateTo('/login');
          return;
        }
        throw new Error(errorData.message || 'Lỗi khi tạo đơn hàng');
      }

      const { orders } = await orderResponse.json();
      if (!orders || !orders.length) throw new Error('Không nhận được đơn hàng từ server');

      await removeOrderedItems(allItems);
      if (isBuyNow.value) localStorage.removeItem('buy_now');

      if (orders.length === 1) {
        localStorage.setItem('lastOrderId', orders[0].id);
        if (selectedPaymentMethod.value === 'COD') {
          await navigateTo(`/order-success?id=${orders[0].id}`);
          await fetchCart();
          return;
        } else if (['VNPAY', 'MOMO'].includes(selectedPaymentMethod.value)) {
          const apiUrl = selectedPaymentMethod.value === 'VNPAY'
            ? `${config.public.apiBaseUrl}/payments/vnpay/create`
            : `${config.public.apiBaseUrl}/payments/momo/create`;
          const res = await fetch(apiUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({ order_id: orders[0].id }),
          });
          const { data } = await res.json();
          if (data.payment_url) {
            window.location.href = data.payment_url;
            return;
          }
        }
        await fetchCart();
        return;
      }

      const orderIds = orders.map(o => o.id);
      localStorage.setItem('lastOrderIds', orderIds.join(','));
      if (selectedPaymentMethod.value === 'COD') {
        await navigateTo(`/order-success?ids=${orderIds.join(',')}`);
        await fetchCart();
        return;
      } else if (['VNPAY', 'MOMO'].includes(selectedPaymentMethod.value)) {
        const apiUrl = selectedPaymentMethod.value === 'VNPAY'
          ? `${config.public.apiBaseUrl}/payments/vnpay/create`
          : `${config.public.apiBaseUrl}/payments/momo/create`;
        const res = await fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({ order_ids: orderIds }),
        });
        const { data } = await res.json();
        if (data.payment_url) {
          window.location.href = data.payment_url;
          return;
        }
      }
      await fetchCart();
    } catch (err) {
      error.value = err.message || 'Có lỗi xảy ra khi đặt hàng';
      toast('error', error.value);
      console.error('Place order error:', err);
    } finally {
      loading.value = false;
    }
  };

  const removeOrderedItems = async (orderedItems) => {
    try {
      const token = localStorage.getItem('access_token');
      if (!token) return;

      const cartResponse = await fetch(`${config.public.apiBaseUrl}/cart`, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });

      if (!cartResponse.ok) return;

      const cartData = await cartResponse.json();
      const cartItems = cartData.data?.stores || [];

      for (const orderedItem of orderedItems) {
        for (const store of cartItems) {
          const itemToRemove = store.items?.find(item => 
            item.product?.id === orderedItem.product_id && 
            item.product_variant?.id === orderedItem.product_variant_id
          );

          if (itemToRemove) {
            await fetch(`${config.public.apiBaseUrl}/cart/${itemToRemove.id}`, {
              method: 'DELETE',
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
              },
            });
          }
        }
      }

      await fetchCart();
    } catch (error) {
      console.error('Error removing ordered items from cart:', error);
    }
  };

  loadBuyNowData();

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
    paymentMethods: filteredPaymentMethods,
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
    removeOrderedItems,
    isBuyNow,
    buyNowData,
    updateShopDiscount,
    getShopDiscount,
    getShopDiscountId,
    canUseCod,
    isAccountBanned,
    checkCodEligibility,
  };
}