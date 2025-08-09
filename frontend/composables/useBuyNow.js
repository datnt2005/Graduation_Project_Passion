import { checkoutPerformance, shippingPerformance } from '~/utils/performance';

export function useBuyNow(selectedAddress, storeNotes) {
  const config = useRuntimeConfig();
  const { toast } = useToast();
  const { logout } = useAuth();
  const {
    cart,
    loading,
    error,
    selectedItems,
    parsePrice,
    fetchCart,
    clearOrderedItems,
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
  const isPlacingOrder = ref(false);

  const formatPrice = (price) => {
    const parsed = parsePrice(price);
    return parsed.toLocaleString('vi-VN');
  };

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
const shippingFeeCache = ref(new Map());
const sellerAddressCache = ref(new Map());
const serviceCache = ref(new Map());
const CACHE_TTL = 3600 * 1000;
const ADDRESS_CACHE_TTL = 1800 * 1000;
const SERVICE_CACHE_TTL = 900 * 1000;

const getCacheKey = (payload) => {
  return `${payload.seller_id}_${payload.from_district_id}_${payload.from_ward_code}_${payload.service_id}_${payload.to_district_id}_${payload.to_ward_code}_${payload.weight}_${payload.height}_${payload.length}_${payload.width}`;
};
const getCachedFee = (cacheKey) => {
  const cached = shippingFeeCache.value.get(cacheKey);
  if (cached && cached.timestamp + CACHE_TTL > Date.now()) return cached.fee;
  shippingFeeCache.value.delete(cacheKey);
  return null;
};
const setCachedFee = (cacheKey, fee) => {
  shippingFeeCache.value.set(cacheKey, { fee, timestamp: Date.now() });
};

const getCachedSellerAddress = (sellerId) => {
  const cached = sellerAddressCache.value.get(sellerId);
  if (cached && cached.timestamp + ADDRESS_CACHE_TTL > Date.now()) return cached.address;
  sellerAddressCache.value.delete(sellerId);
  return null;
};
const setCachedSellerAddress = (sellerId, address) => {
  sellerAddressCache.value.set(sellerId, { address, timestamp: Date.now() });
};

const getCachedServices = (sellerId, fromDistrictId, toDistrictId) => {
  const key = `${sellerId}_${fromDistrictId}_${toDistrictId}`;
  const cached = serviceCache.value.get(key);
  if (cached && cached.timestamp + SERVICE_CACHE_TTL > Date.now()) return cached.services;
  serviceCache.value.delete(key);
  return null;
};
const setCachedServices = (sellerId, fromDistrictId, toDistrictId, services) => {
  const key = `${sellerId}_${fromDistrictId}_${toDistrictId}`;
  serviceCache.value.set(key, { services, timestamp: Date.now() });
};

const fetchSellerAddress = async (sellerId) => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Thiếu access token');

    const cached = getCachedSellerAddress(sellerId);
    if (cached) return cached;

    const res = await fetch(`${config.public.apiBaseUrl}/sellers/${sellerId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const { data } = await res.json();
    if (!data) throw new Error('Không có dữ liệu cửa hàng');

    const wardRes = await fetch(`${config.public.apiBaseUrl}/ghn/wards`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ district_id: data.district_id }),
    });
    const wardData = await wardRes.json();
    const ward = wardData.data.find(w => w.WardCode === data.ward_id || w.WardCode.includes(data.ward_id));
    if (!ward) throw new Error('Không tìm thấy ward_code');

    const address = {
      province_id: data.province_id,
      district_id: data.district_id,
      ward_id: data.ward_id,
      ward_code: ward.WardCode,
      address: data.address,
    };
    setCachedSellerAddress(sellerId, address);
    return address;
  } catch (err) {
    console.error('fetchSellerAddress error:', err);
    toast('error', 'Không lấy được địa chỉ shop');
    return null;
  }
};

const fetchGHNServiceId = async (sellerId, fromDistrictId, toDistrictId) => {
  const token = localStorage.getItem('access_token');
  if (!token) throw new Error('Thiếu access token');

  const cached = getCachedServices(sellerId, fromDistrictId, toDistrictId);
  if (cached) return cached;

  const res = await fetch(`${config.public.apiBaseUrl}/ghn/services`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      seller_id: sellerId,
      from_district_id: fromDistrictId,
      to_district_id: toDistrictId,
    }),
  });
  const data = await res.json();
  const services = data.data || [];
  setCachedServices(sellerId, fromDistrictId, toDistrictId, services);
  return services;
};

const calculateShippingFee = async (sellerId, fromAddress, toAddress, item) => {
  try {
    const services = await fetchGHNServiceId(sellerId, fromAddress.district_id, toAddress.district_id);
    if (!services.length) throw new Error('Không có dịch vụ GHN');

    const totalWeight = (item.productVariant?.weight || 1000) * item.quantity;
    const dimensions = {
      length: item.productVariant?.length || 30,
      width: item.productVariant?.width || 20,
      height: (item.productVariant?.height || 10) * item.quantity,
    };

    const service =
      services.find(s => [53321, 53322].includes(s.service_id)) ||
      services[0];
    const serviceId = service?.service_id;
    if (!serviceId) throw new Error('Không tìm thấy service_id');

    const payload = {
      seller_id: sellerId,
      from_district_id: fromAddress.district_id,
      from_ward_code: fromAddress.ward_code,
      to_district_id: toAddress.district_id,
      to_ward_code: toAddress.ward_code,
      service_id: serviceId,
      weight: Math.max(totalWeight, 50),
      ...dimensions,
    };

    const cacheKey = getCacheKey(payload);
    const cachedFee = getCachedFee(cacheKey);
    if (cachedFee !== null) return { fee: cachedFee, service_id: serviceId };

    const token = localStorage.getItem('access_token');
    const res = await fetch(`${config.public.apiBaseUrl}/ghn/shipping-fee`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });
    const { data } = await res.json();
    const fee = data?.total || 0;
    setCachedFee(cacheKey, fee);
    return { fee, service_id: serviceId };
  } catch (err) {
    console.error('calculateShippingFee error:', err);
    return { fee: 0, service_id: null };
  }
};

const placeBuyNowOrder = async (buyNowItem) => {
  if (isPlacingOrder.value) return;
  isPlacingOrder.value = true;
  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để tiếp tục.');
      window.dispatchEvent(new CustomEvent('openLoginModal'));
      return;
    }

    // Lấy thông tin user
    const userResponse = await fetch(`${config.public.apiBaseUrl}/me`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
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

    // Kiểm tra sản phẩm
    if (!buyNowItem || !buyNowItem.product || !buyNowItem.product.id) {
      throw new Error('Không có sản phẩm hợp lệ để đặt hàng');
    }

    // --- BẮT ĐẦU: Tính phí GHN ---
    const fromAddress = await fetchSellerAddress(buyNowItem.seller_id);
    const toAddress = selectedAddress.value;
    const { fee, service_id } = await calculateShippingFee(
      buyNowItem.seller_id,
      fromAddress,
      toAddress,
      buyNowItem
    );
    buyNowItem.shipping_fee = fee;
    buyNowItem.service_id = service_id;
    // --- KẾT THÚC: Tính phí GHN ---

    const orderData = {
      user_id: userData.id,
      address_id: selectedAddress.value?.id || null,
      address: selectedAddress.value?.detail || 'Chưa cung cấp địa chỉ',
      receiver_name: selectedAddress.value?.name || userData.name || 'Chưa cung cấp tên',
      receiver_phone: selectedAddress.value?.phone || 'Chưa cung cấp số điện thoại',
      payment_method: selectedPaymentMethod.value,
      discount_ids: selectedDiscounts.value.map(d => d.id),
      items: [{
        product_id: buyNowItem.product.id,
        product_variant_id: buyNowItem.productVariant?.id || null,
        quantity: buyNowItem.quantity,
        price: parsePrice(buyNowItem.sale_price || buyNowItem.price),
        seller_id: buyNowItem.seller_id,
        shipping_fee: buyNowItem.shipping_fee || 0,
        service_id: buyNowItem.service_id || null,
      }],
      ward_id: selectedAddress.value?.ward_code || null,
      district_id: selectedAddress.value?.district_id || null,
      province_id: selectedAddress.value?.province_id || null,
      skip_stock_check: true,
      store_notes: storeNotes.value || {},
      store_shipping_fees: { [buyNowItem.seller_id]: buyNowItem.shipping_fee || 0 },
      store_service_ids: { [buyNowItem.seller_id]: buyNowItem.service_id || null },
      store_discounts: { [buyNowItem.seller_id]: buyNowItem.discount || 0 },
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
      const errorData = await orderResponse.json().catch(() => ({ message: 'Lỗi máy chủ' }));
      throw new Error(errorData.message || 'Lỗi khi tạo đơn hàng');
    }

    const { orders } = await orderResponse.json();
    if (!orders || !orders.length) throw new Error('Không nhận được đơn hàng từ server');

    // Xử lý thanh toán
    const paymentMethod = selectedPaymentMethod.value;
    const orderIds = orders.map(order => order.id);

    if (paymentMethod === 'VNPAY') {
      const paymentResponse = await fetch(`${config.public.apiBaseUrl}/payments/vnpay/create`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ order_ids: orderIds }),
      });
      if (!paymentResponse.ok) throw new Error('Không thể tạo thanh toán VNPay');
      const paymentData = await paymentResponse.json();
      if (paymentData.data?.payment_url) {
        window.location.href = paymentData.data.payment_url;
      } else {
        throw new Error('Không nhận được URL thanh toán VNPay');
      }
    } else if (paymentMethod === 'MOMO') {
      const paymentResponse = await fetch(`${config.public.apiBaseUrl}/payments/momo/create`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ order_ids: orderIds }),
      });
      if (!paymentResponse.ok) throw new Error('Không thể tạo thanh toán MoMo');
      const paymentData = await paymentResponse.json();
      if (paymentData.data?.payment_url) {
        window.location.href = paymentData.data.payment_url;
      } else {
        throw new Error('Không nhận được URL thanh toán MoMo');
      }
    } else {
      await navigateTo(`/order-success?ids=${orderIds.join(',')}`);
    }

    await fetchCart();
  } catch (err) {
    console.error('Lỗi khi đặt hàng:', err);
    toast('error', err.message || 'Đã xảy ra lỗi khi đặt hàng. Vui lòng thử lại.');
  } finally {
    isPlacingOrder.value = false;
  }
};


  return {
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
    placeBuyNowOrder,
    isPlacingOrder,
  };
}