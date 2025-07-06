import { ref, computed } from "vue";
import { useRuntimeConfig, navigateTo } from "#app";
import { useCart } from "~/composables/useCart";
import { usePayment } from "~/composables/usePayment";
import { useDiscount } from "~/composables/useDiscount";
import { useToast } from "~/composables/useToast";

export function useCheckout(
  shippingRef,
  selectedShippingMethod,
  selectedAddress
) {
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

  const selectedPaymentMethod = ref("");

  // Derive cartItems from cart.stores
  const cartItems = computed(() =>
    cart.value.stores
      .flatMap((store) =>
        (store.items || []).filter((item) => selectedItems.value.has(item.id))
      )
      .filter((item) => item && item.id && item.quantity)
  );

  // Total based on selected items
  const total = computed(() => {
    return cart.value.stores.reduce((total, store) => {
      return (
        total +
        (store.items || []).reduce((storeTotal, item) => {
          if (item && selectedItems.value.has(item.id)) {
            return (
              storeTotal +
              (parsePrice(item.sale_price) || parsePrice(item.price)) *
                (item.quantity || 1)
            );
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
    return parsed.toLocaleString("vi-VN");
  };

  const formattedTotal = computed(() => `${formatPrice(total.value)} đ`);
  // const formattedFinalTotal = computed(() => `${formatPrice(finalTotal.value)} đ`);
  const formattedFinalTotal = computed(() => formatPrice(calculatedFinalTotal.value));
  const formattedFinalShippingFee = computed(() => `${formatPrice(finalShippingFee.value)} đ`);

  // Payment method label
  const getPaymentMethodLabel = (methodName) => {
    switch (methodName) {
      case "COD":
        return "Thanh toán khi nhận hàng";
      case "VNPAY":
        return "Thanh toán qua VNPAY";
      case "MOMO":
        return "Thanh toán qua Ví MoMo";
      default:
        return methodName;
    }
  };

  const calculatedFinalTotal = computed(() => {
  return (
    total.value +
    finalShippingFee.value -
    getShippingDiscount(total.value) -
    calculateDiscount(total.value)
  );
});



const placeOrder = async () => {
  if (!cartItems.value.length) {
    toast("error", "Giỏ hàng trống hoặc chưa chọn sản phẩm");
    return;
  }
  if (!selectedPaymentMethod.value) {
    toast("error", "Vui lòng chọn phương thức thanh toán");
    return;
  }
  if (!selectedShippingMethod.value) {
    toast("error", "Vui lòng chọn hình thức giao hàng");
    return;
  }
  if (!selectedAddress.value?.id) {
    toast("error", "Vui lòng chọn địa chỉ giao hàng");
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const token = localStorage.getItem("access_token");
    if (!token) {
      toast("error", "Vui lòng đăng nhập để tiếp tục");
      window.dispatchEvent(new CustomEvent("openLoginModal"));
      return;
    }

    const userResponse = await fetch(`${config.public.apiBaseUrl}/me`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    });

    if (!userResponse.ok) {
      if (userResponse.status === 401) {
        localStorage.removeItem("access_token");
        window.dispatchEvent(new CustomEvent("openLoginModal"));
        throw new Error("Phiên đăng nhập hết hạn");
      }
      throw new Error("Không thể lấy thông tin người dùng");
    }

    const { data: userData } = await userResponse.json();
    if (!userData?.id) throw new Error("Không tìm thấy thông tin người dùng");

    const items = cartItems.value
      .filter((item) => item.productVariant && item.product && selectedItems.value.has(item.id))
      .map((item) => ({
        product_id: item.product.id,
        product_variant_id: item.productVariant.id,
        quantity: item.quantity,
        price: parsePrice(item.sale_price || item.price),
        id: item.id,
      }));

    if (!items.length) {
      toast("error", "Không có sản phẩm hợp lệ để đặt hàng");
      return;
    }

    // 🔐 Lưu danh sách cart_item_id lại để xóa sau khi thanh toán
    const cartItemIds = items.map(i => i.id);
    localStorage.setItem("lastOrderCartItemIds", JSON.stringify(cartItemIds));

    const orderData = {
      user_id: userData.id,
      address_id: selectedAddress.value.id,
      address: selectedAddress.value.address || 'Chưa cung cấp địa chỉ',
      receiver_name: selectedAddress.value.name || userData.name || 'Chưa cung cấp tên',
      receiver_phone: selectedAddress.value.phone || 'Chưa cung cấp số điện thoại',
      payment_method: selectedPaymentMethod.value,
      service_id: selectedShippingMethod.value,
      discount_id: selectedDiscounts.value?.[0]?.id || null,
      items,
      ward_id: selectedAddress.value.ward_id,
      district_id: selectedAddress.value.district_id,
      province_id: selectedAddress.value.province_id,
      shipping_fee: finalShippingFee.value,
      shipping_discount: getShippingDiscount(total.value),
      product_discount: calculateDiscount(total.value),
      final_price: calculatedFinalTotal.value
    };

    const orderResponse = await fetch(`${config.public.apiBaseUrl}/orders`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(orderData),
    });

    if (!orderResponse.ok) {
      const errorData = await orderResponse.json();
      throw new Error(errorData.message || "Lỗi khi tạo đơn hàng");
    }

    const { data: orderResult } = await orderResponse.json();

    if (selectedPaymentMethod.value === 'COD') {
      localStorage.setItem("lastOrderId", orderResult.id);
      await navigateTo(`/order-success?id=${orderResult.id}`);
      return;
    } else {
      const { url } = await processPayment(orderResult.id, selectedPaymentMethod.value);
      if (url) {
        window.location.href = url;
      } else {
        throw new Error("Không nhận được URL thanh toán");
      }
    }

    await fetchCart();
  } catch (err) {
    error.value = err.message || "Có lỗi xảy ra khi đặt hàng";
    toast("error", error.value);
    console.error("Place order error:", err);
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
