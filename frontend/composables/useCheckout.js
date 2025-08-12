import { checkoutPerformance, shippingPerformance } from '~/utils/performance';
import { nextTick, watch } from 'vue';

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
  const canUseCod = ref(true);
  const isAccountBanned = ref(false);
  const rejectedOrdersCount = ref(0);
  const shopDiscounts = ref({});
  const shopDiscountIds = ref({});
  const sellerAddresses = ref({});
  const shippingMethods = ref({});
  const shopServiceIds = ref({});
  const shippingFeeCache = ref(new Map());
  const CACHE_TTL = 3600 * 1000; // 1 giờ
  const isPlacingOrder = ref(false);
  const lastShippingCalculation = ref(0);
  const SHIPPING_COOLDOWN = 1000; // 1 giây
  const isCheckoutCalculatingShipping = ref(false);

  // Cache cho seller addresses và services
  const sellerAddressCache = ref(new Map());
  const serviceCache = ref(new Map());
  const ADDRESS_CACHE_TTL = 1800 * 1000; // 30 phút
  const SERVICE_CACHE_TTL = 900 * 1000; // 15 phút

  const isBuyNow = computed(() => route.query.buyNow === 'true');
  const buyNowData = ref(null);

  const filteredPaymentMethods = computed(() => {
    if (canUseCod.value) {
      return paymentMethods.value;
    }
    return paymentMethods.value.filter(method => method.name !== 'COD');
  });

  const defaultShippingMethod = computed(() => {
    const methodsMap = new Map();
    Object.values(shippingMethods.value).forEach(methods => {
      methods.forEach(method => {
        const shop = (isBuyNow.value ? buyNowItems.value : cartItems.value)
          .find(s => s.seller_id === method.seller_id);
        const totalWeight = shop ? calculateTotalWeight(shop) : 0;

        // Loại 100039 nếu < 2000g
        if (method.service_id !== 100039 || totalWeight >= 2000) {
          methodsMap.set(method.service_id, method);
        }
      });
    });
    const available = Array.from(methodsMap.values());
    return available.find(m => [53321, 53322].includes(m.service_id)) || available[0] || null;
  });

  const calculateTotalWeight = (shop) => {
    return shop.items.reduce((sum, item) => {
      const itemWeight = item.productVariant?.weight || 1000;
      return sum + itemWeight * item.quantity;
    }, 0);
  };

  const getCacheKey = (payload) => {
    return `${payload.seller_id}_${payload.from_district_id}_${payload.from_ward_code}_${payload.service_id}_${payload.to_district_id}_${payload.to_ward_code}_${payload.weight}_${payload.height}_${payload.length}_${payload.width}`;
  };

  const getCachedFee = (cacheKey) => {
    const cached = shippingFeeCache.value.get(cacheKey);
    if (cached && cached.timestamp + CACHE_TTL > Date.now()) {
      return cached.fee;
    }
    shippingFeeCache.value.delete(cacheKey);
    return null;
  };

  const setCachedFee = (cacheKey, fee) => {
    shippingFeeCache.value.set(cacheKey, {
      fee,
      timestamp: Date.now()
    });
  };

  const getCachedSellerAddress = (sellerId) => {
    const cached = sellerAddressCache.value.get(sellerId);
    if (cached && cached.timestamp + ADDRESS_CACHE_TTL > Date.now()) {
      return cached.address;
    }
    sellerAddressCache.value.delete(sellerId);
    return null;
  };

  const setCachedSellerAddress = (sellerId, address) => {
    sellerAddressCache.value.set(sellerId, {
      address,
      timestamp: Date.now()
    });
  };

  const getCachedServices = (sellerId, fromDistrictId, toDistrictId) => {
    const cacheKey = `${sellerId}_${fromDistrictId}_${toDistrictId}`;
    const cached = serviceCache.value.get(cacheKey);
    if (cached && cached.timestamp + SERVICE_CACHE_TTL > Date.now()) {
      return cached.services;
    }
    serviceCache.value.delete(cacheKey);
    return null;
  };

  const setCachedServices = (sellerId, fromDistrictId, toDistrictId, services) => {
    const cacheKey = `${sellerId}_${fromDistrictId}_${toDistrictId}`;
    serviceCache.value.set(cacheKey, {
      services,
      timestamp: Date.now()
    });
  };

  const fetchDefaultAddress = async (userId) => {
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        throw new Error('Thiếu access token. Vui lòng đăng nhập lại.');
      }
      const response = await fetch(`${config.public.apiBaseUrl}/address`, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: `Lỗi máy chủ: ${response.status}` }));
        throw new Error(errorData.message || 'Không thể lấy địa chỉ mặc định');
      }
      const { data } = await response.json();
      const addresses = data || [];
      const defaultAddress = addresses.find(addr => addr.is_default === 1) || addresses[0] || null;
      if (!defaultAddress) {
        throw new Error('Không tìm thấy địa chỉ mặc định');
      }
      return {
        id: defaultAddress.id,
        user_id: defaultAddress.user_id,
        name: defaultAddress.name,
        phone: defaultAddress.phone,
        province_id: defaultAddress.province_id,
        district_id: defaultAddress.district_id,
        ward_code: defaultAddress.ward_code,
        detail: defaultAddress.detail,
        is_default: defaultAddress.is_default,
        address_type: defaultAddress.address_type,
      };
    } catch (err) {
      console.error(`Lỗi khi lấy địa chỉ mặc định cho user ${userId}:`, err);
      throw err;
    }
  };

  const fetchSellerAddress = async (sellerId) => {
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        throw new Error('Thiếu access token. Vui lòng đăng nhập lại.');
      }

      const cachedAddress = getCachedSellerAddress(sellerId);
      if (cachedAddress) {
        sellerAddresses.value[sellerId] = cachedAddress;
        return cachedAddress;
      }

      if (sellerAddresses.value[sellerId]?.district_id && sellerAddresses.value[sellerId]?.ward_code) {
        return sellerAddresses.value[sellerId];
      }

      const response = await fetch(`${config.public.apiBaseUrl}/sellers/${sellerId}`, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: `Lỗi máy chủ: ${response.status}` }));
        if (response.status === 401) {
          toast('error', 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.');
          await logout();
          await navigateTo('/login');
          throw new Error('Phiên đăng nhập hết hạn');
        }
        console.error('Phản hồi lỗi từ /sellers:', JSON.stringify(errorData, null, 2));
        throw new Error(errorData.message || `Không thể lấy thông tin cửa hàng ${sellerId}`);
      }
      const { data } = await response.json();
      if (!data || !data.district_id || !data.ward_id) {
        throw new Error(`Thiếu district_id hoặc ward_id cho seller ${sellerId}`);
      }

      const wardResponse = await fetch(`${config.public.apiBaseUrl}/ghn/wards`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ district_id: data.district_id }),
      });
      if (!wardResponse.ok) {
        const wardError = await wardResponse.json().catch(() => ({ message: `Lỗi máy chủ: ${wardResponse.status}` }));
        console.error('Phản hồi lỗi từ /ghn/wards:', JSON.stringify(wardError, null, 2));
        throw new Error(`Không thể lấy ward_code cho seller ${sellerId}: ${wardError.message}`);
      }
      const wardData = await wardResponse.json();
      const ward = wardData.data.find(w => w.WardCode === data.ward_id || w.WardCode.includes(data.ward_id));
      if (!ward) {
        throw new Error(`Không tìm thấy ward_code tương ứng cho ward_id ${data.ward_id} trong district_id ${data.district_id}`);
      }

      const sellerAddress = {
        province_id: data.province_id,
        district_id: data.district_id,
        ward_id: data.ward_id,
        ward_code: ward.WardCode,
        address: data.address,
      };

      sellerAddresses.value[sellerId] = sellerAddress;
      setCachedSellerAddress(sellerId, sellerAddress);
      return sellerAddress;
    } catch (err) {
      console.error(`Lỗi khi lấy địa chỉ seller ${sellerId}:`, err.message);
      toast('error', `Không thể lấy thông tin địa chỉ của cửa hàng ${sellerId}.`);
      return null;
    }
  };

  const fetchGHNServiceId = async (sellerId, fromDistrictId, toDistrictId, retries = 2) => {
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        throw new Error('Thiếu access token. Vui lòng đăng nhập lại.');
      }

      const cachedServices = getCachedServices(sellerId, fromDistrictId, toDistrictId);
      if (cachedServices) {
        shippingMethods.value[sellerId] = cachedServices;
        return cachedServices;
      }

      const response = await fetch(`${config.public.apiBaseUrl}/ghn/services`, {
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

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: `Lỗi máy chủ: ${response.status}` }));
        console.error('Phản hồi lỗi từ /ghn/services:', JSON.stringify(errorData, null, 2));
        if (response.status === 500 && retries > 0) {
          await new Promise(resolve => setTimeout(resolve, 1000));
          return await fetchGHNServiceId(sellerId, fromDistrictId, toDistrictId, retries - 1);
        }
        if (response.status === 401) {
          toast('error', 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.');
          await logout();
          await navigateTo('/login');
          throw new Error('Phiên đăng nhập hết hạn');
        }
        throw new Error(errorData.message || 'Không thể lấy danh sách dịch vụ');
      }

      const data = await response.json();
      const services = data.data || [];

      shippingMethods.value[sellerId] = services;
      setCachedServices(sellerId, fromDistrictId, toDistrictId, services);
      return services;
    } catch (err) {
      console.error(`Lỗi khi lấy danh sách dịch vụ cho seller ${sellerId}:`, err.message);
      throw err;
    }
  };

  const calculateShippingFee = async (sellerId, fromAddress, toAddress) => {
    const startTime = shippingPerformance.startCalculation();
    shippingPerformance.currentCalculation.sellerId = sellerId;

    if (!fromAddress || !fromAddress.district_id || !fromAddress.ward_code) {
      fromAddress = await fetchSellerAddress(sellerId);
      if (!fromAddress || !fromAddress.district_id || !fromAddress.ward_code) {
        console.error(`Thiếu thông tin địa chỉ của cửa hàng ${sellerId}.`);
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }
    }
    if (!toAddress || !toAddress.district_id || !toAddress.ward_code) {
      console.warn(`Thiếu thông tin địa chỉ nhận cho seller ${sellerId}:`, toAddress);
      shippingPerformance.endCalculation(startTime);
      return { fee: 0, service_id: null };
    }
    try {
      const services = await fetchGHNServiceId(sellerId, fromAddress.district_id, toAddress.district_id);
      if (!services || services.length === 0) {
        console.warn(`Không tìm thấy dịch vụ GHN phù hợp cho seller ${sellerId}`);
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }
      const storeItems = (isBuyNow.value ? buyNowItems.value : cartItems.value).find(store => store.seller_id === sellerId)?.items || [];
      const totalWeight = storeItems.reduce((sum, item) => {
        const weight = item.productVariant?.weight || 1000;
        return sum + weight * item.quantity;
      }, 0);
      if (totalWeight < 50) {
        console.warn(`Cân nặng quá thấp: ${totalWeight}g cho seller ${sellerId}. Tối thiểu 50g.`);
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }
      let service = services.find(s => s.service_id === shopServiceIds.value[sellerId]);
      if (!service) {
        service = services.find(s => [53321, 53322].includes(s.service_id)) || services[0];
        shopServiceIds.value[sellerId] = service.service_id;
      }
      const serviceId = service?.service_id || null;
      if (!serviceId) {
        console.error('Không tìm thấy dịch vụ vận chuyển hợp lệ');
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }
      if (serviceId === 100039 && totalWeight < 2000) {
        console.warn(`Cân nặng ${totalWeight}g không hợp lệ cho dịch vụ Hàng nặng (service_id: 100039).`);
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }
      const dimensions = storeItems.reduce(
        (acc, item) => {
          const length = item.productVariant?.length || 30;
          const width = item.productVariant?.width || 20;
          const height = item.productVariant?.height || 10;
          return {
            length: Math.max(acc.length, length),
            width: Math.max(acc.width, width),
            height: acc.height + height * item.quantity,
          };
        },
        { length: 30, width: 20, height: 0 }
      );

      const payload = {
        seller_id: sellerId,
        from_district_id: fromAddress.district_id,
        from_ward_code: fromAddress.ward_code,
        to_district_id: toAddress.district_id,
        to_ward_code: toAddress.ward_code,
        service_id: serviceId,
        weight: Math.max(totalWeight, 50),
        length: dimensions.length,
        width: dimensions.width,
        height: dimensions.height,
      };
      const cacheKey = getCacheKey(payload);
      const cachedFee = getCachedFee(cacheKey);
      if (cachedFee !== null) {
        shippingPerformance.cacheHit();
        const shop = (isBuyNow.value ? buyNowItems.value : cartItems.value).find(s => s.seller_id === sellerId);
        if (shop) {
          shop.shipping_fee = cachedFee;
          shop.service_id = serviceId;
          shop.original_shipping_fee = cachedFee;
        }
        if (isBuyNow.value && buyNowData.value && Number(buyNowData.value.seller_id) === Number(sellerId)) {
          buyNowData.value.shipping_fee = cachedFee;
          buyNowData.value.service_id = serviceId;
        }
        shippingPerformance.endCalculation(startTime);
        return { fee: cachedFee, service_id: serviceId };
      }

      shippingPerformance.cacheMiss();
      shippingPerformance.apiCall();
      const token = localStorage.getItem('access_token');
      if (!token) {
        console.error('Thiếu access token');
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }

      const response = await fetch(`${config.public.apiBaseUrl}/ghn/shipping-fee`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Lỗi máy chủ' }));
        console.error('Lỗi phản hồi từ API GHN shipping-fee:', errorData);
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: serviceId };
      }
      const { data } = await response.json();
      const shippingFee = data?.total || 0;
      if (shippingFee < 1000) {
        console.error(`Phí vận chuyển ${shippingFee} VNĐ quá thấp, có thể do lỗi dữ liệu từ API.`);
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: serviceId };
      }

      setCachedFee(cacheKey, shippingFee);
      const shop = (isBuyNow.value ? buyNowItems.value : cartItems.value).find(s => s.seller_id === sellerId);
      if (shop) {
        shop.shipping_fee = shippingFee;
        shop.service_id = serviceId;
        shop.original_shipping_fee = shippingFee;
      }
      if (isBuyNow.value && buyNowData.value && Number(buyNowData.value.seller_id) === Number(sellerId)) {
        buyNowData.value.shipping_fee = shippingFee;
        buyNowData.value.service_id = serviceId;
      }
      shippingPerformance.endCalculation(startTime);
      return { fee: shippingFee, service_id: serviceId };
    } catch (err) {
      console.error(`Lỗi khi tính phí vận chuyển cho seller ${sellerId}:`, err);
      shippingPerformance.error();
      shippingPerformance.endCalculation(startTime);
      return { fee: 0, service_id: null };
    }
  };

  const loadShippingFees = async () => {
    const now = Date.now();
    if (now - lastShippingCalculation.value < SHIPPING_COOLDOWN) {
      return;
    }

    if (isCheckoutCalculatingShipping.value) {
      return;
    }

    isCheckoutCalculatingShipping.value = true;
    lastShippingCalculation.value = now;

    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    if (!items.length) {
      console.warn('Giỏ hàng trống');
      isCheckoutCalculatingShipping.value = false;
      return;
    }

    console.time('loadShippingFees-total');
    try {
      console.time('fetch-seller-addresses');
      const sellerAddressPromises = items.map(async (store) => {
        if (!store.seller_id) {
          console.warn('Thiếu seller_id:', store);
          toast('error', `Thiếu thông tin cửa hàng: ${store.store_name || 'Cửa hàng'}`);
          store.shipping_fee = 0;
          return null;
        }

        const hasValidShippingFee = store.shipping_fee > 0 && store.service_id && store.district_id && store.ward_code;
        if (hasValidShippingFee) {
          return store;
        }

        if (!sellerAddresses.value[store.seller_id]) {
          await fetchSellerAddress(store.seller_id);
        }

        return store;
      });

      await Promise.all(sellerAddressPromises);
      console.timeEnd('fetch-seller-addresses');

      console.time('calculate-shipping-fees');
      const shippingFeePromises = items.map(async (store) => {
        if (!store.seller_id) return;

        // Với Buy Now, luôn tính toán phí ship để đảm bảo cập nhật
        // Với Cart, chỉ tính toán nếu chưa có phí ship
        const shouldCalculate = isBuyNow.value || store.shipping_fee <= 0;
        if (!shouldCalculate) return;

        const { fee, service_id } = await calculateShippingFee(store.seller_id, sellerAddresses.value[store.seller_id], selectedAddress.value);

        const storeIndex = items.findIndex(s => s.seller_id === store.seller_id);
        if (storeIndex !== -1) {
          items[storeIndex].shipping_fee = fee;
          items[storeIndex].original_shipping_fee = fee;
          items[storeIndex].service_id = service_id;
          items[storeIndex].district_id = sellerAddresses.value[store.seller_id]?.district_id;
          items[storeIndex].ward_code = sellerAddresses.value[store.seller_id]?.ward_code;
        }
        if (isBuyNow.value && buyNowData.value && Number(buyNowData.value.seller_id) === Number(store.seller_id)) {
          buyNowData.value.shipping_fee = fee;
          buyNowData.value.service_id = service_id;
        }
        if (service_id) {
          shopServiceIds.value[store.seller_id] = service_id;
        }

        if (cart.value && cart.value.stores && !isBuyNow.value) {
          const cartStore = cart.value.stores.find(s => s.seller_id === store.seller_id);
          if (cartStore) {
            cartStore.shipping_fee = fee;
            cartStore.service_id = service_id;
          }
        }
      });

      await Promise.all(shippingFeePromises);
    } catch (error) {
      console.error('Lỗi trong loadShippingFees:', error);
      toast('error', 'Có lỗi xảy ra khi tính phí vận chuyển');
    } finally {
      isCheckoutCalculatingShipping.value = false;
    }
  };

  const checkCodEligibility = async () => {
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        canUseCod.value = false;
        isAccountBanned.value = false;
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
        const errorData = await response.json().catch(() => ({ message: 'Lỗi máy chủ' }));
        throw new Error(errorData.message || 'Lỗi khi kiểm tra trạng thái COD.');
      }
      const { can_use_cod, is_account_banned, message, rejected_orders_count } = await response.json();
      canUseCod.value = can_use_cod;
      isAccountBanned.value = is_account_banned;
      rejectedOrdersCount.value = rejected_orders_count || 0;
      if (is_account_banned) {
        await logout();
        await navigateTo('/login');
      }
    } catch (err) {
      console.error('Lỗi khi kiểm tra điều kiện COD:', err);
      canUseCod.value = false;
    }
  };

  const loadBuyNowData = () => {
    const buyNowDataString = localStorage.getItem('buy_now');
    if (buyNowDataString) {
      try {
        const data = JSON.parse(buyNowDataString);
        const maxAge = 30 * 60 * 1000; // 30 phút
        if (Date.now() - data.timestamp > maxAge) {
          localStorage.removeItem('buy_now');
          buyNowData.value = null;
          toast('error', 'Dữ liệu Buy Now đã hết hạn. Vui lòng chọn lại sản phẩm.');
        } else {
          buyNowData.value = data;
          if (data.seller_id) {
            // Fetch seller address và tự động tính phí ship
            fetchSellerAddress(data.seller_id).then(addr => {
              // Sau khi có địa chỉ seller, luôn gọi loadShippingFees
              // Nếu có selectedAddress, tính phí ship ngay
              // Nếu không có, sẽ được tính khi selectedAddress được set
              if (addr) {
                nextTick(() => {
                  if (selectedAddress.value && selectedAddress.value.province_id && selectedAddress.value.district_id) {
                    loadShippingFees();
                  }
                });
              }
            }).catch(e => {
              console.warn('[BUY_NOW] fetchSellerAddress error:', e);
            });
          }
        }
      } catch (error) {
        console.error('Error parsing buy now data:', error);
        localStorage.removeItem('buy_now');
        buyNowData.value = null;
        toast('error', 'Dữ liệu Buy Now không hợp lệ. Vui lòng chọn lại sản phẩm.');
      }
    }
  };

  const updateShopDiscount = async (sellerId, discount, discountId = null) => {
    if (discountId && cart.value && cart.value.stores) {
      const shop = cart.value.stores.find(s => s.seller_id === sellerId);
      if (shop && shop.items && shop.items.length > 0) {
        const productIds = shop.items.map(item => {
          const productId = item.product_id || item.product?.id || item.id;
          return productId;
        }).filter(id => id);
        const orderValue = shop.items.reduce((total, item) => {
          return total + (parsePrice(item.sale_price || item.price || 0) * (item.quantity || 1));
        }, 0);
        const discountObject = discounts.value.find(d => d.id === discountId);
        if (discountObject && discountObject.products && discountObject.products.length > 0) {
          const applicableProducts = discountObject.products.filter(product =>
            productIds.includes(product.id)
          );

          if (applicableProducts.length !== productIds.length) {
            toast('error', 'Mã giảm giá này chỉ áp dụng cho một số sản phẩm, không thể áp dụng cho toàn bộ đơn hàng');
            return false;
          }
        }

        try {
          const token = localStorage.getItem('access_token');
          const res = await fetch(`${config.public.apiBaseUrl}/discounts/check-shop-discount`, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
              discount_id: discountId,
              seller_id: sellerId,
              product_ids: productIds,
              order_value: orderValue
            })
          });

          const data = await res.json();
          if (!res.ok) {
            toast('error', data.message || 'Mã giảm giá không thể áp dụng cho các sản phẩm này');
            return false;
          }
        } catch (error) {
          console.error('Error checking discount:', error);
          toast('error', 'Lỗi khi kiểm tra mã giảm giá');
          return false;
        }
      }
    }

    shopDiscounts.value[sellerId] = discount;
    if (discountId) {
      shopDiscountIds.value[sellerId] = discountId;
    }

    return true;
  };

  const removeShopDiscount = (sellerId) => {
    if (shopDiscounts.value[sellerId]) {
      delete shopDiscounts.value[sellerId];
    }
    if (shopDiscountIds.value[sellerId]) {
      delete shopDiscountIds.value[sellerId];
    }
  };

  const recalculateAllShopDiscounts = () => {
    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    const shopCount = items.length;
    if (shopCount === 0) return;

    Object.keys(shopDiscounts.value).forEach(sellerId => {
      removeShopDiscount(sellerId);
    });

    const perShopDiscount = getProductDiscountPerShop(total.value, shopCount);
    if (perShopDiscount > 0) {
      const adminDiscount = selectedDiscounts.value.find(d => !d.seller_id && (d.discount_type === 'percentage' || d.discount_type === 'fixed'));
      if (adminDiscount) {
        items.forEach(shop => {
          if (shop.seller_id) {
            shopDiscounts.value[shop.seller_id] = perShopDiscount;
            shopDiscountIds.value[shop.seller_id] = adminDiscount.id;
          }
        });
      }
    }
  };

  const getShopDiscount = (sellerId) => {
    return shopDiscounts.value[sellerId] || 0;
  };

  const getShopDiscountId = (sellerId) => {
    return shopDiscountIds.value[sellerId] || null;
  };

  const getShippingDiscountPerShop = (total, shopCount) => {
    const discount = getShippingDiscount(total);
    if (!discount || !shopCount) return 0;
    return Math.floor(discount / shopCount);
  };

  const getProductDiscountPerShop = (total, shopCount) => {
    let totalAdminDiscount = 0;
    if (selectedDiscounts.value && Array.isArray(selectedDiscounts.value)) {
      const adminProductDiscounts = selectedDiscounts.value.filter(d =>
        (d.discount_type === 'percentage' || d.discount_type === 'fixed') && !d.seller_id
      );
      adminProductDiscounts.forEach(discount => {
        if (total >= (discount.min_order_value || 0)) {
          const value = Number(discount.discount_value);
          if (value > 0 && value <= 1000000) {
            if (discount.discount_type === 'percentage') {
              if (value <= 100) {
                const discountAmount = total * value / 100;
                totalAdminDiscount += discountAmount;
              }
            } else {
              totalAdminDiscount += value;
            }
          }
        }
      });
    }
    const perShopDiscount = Math.floor(totalAdminDiscount / shopCount);
    return perShopDiscount;
  };

  const cartItems = computed(() => {
    if (!cart.value || !cart.value.stores) return [];
    const shopCount = cart.value.stores.length;
    const shippingDiscountPerShop = getShippingDiscountPerShop(total.value, shopCount);
    return cart.value.stores
      .map((store) => {
        const sellerAddress = sellerAddresses.value[store.seller_id] || {};
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
              weight: item.productVariant.weight || 1000,
              length: item.productVariant.length || 30,
              width: item.productVariant.width || 20,
              height: item.productVariant.height || 10,
            } : null,
            product: item.product?.id ? {
              id: item.product.id,
              name: item.product.name || '',
              slug: item.product.slug || '',
              images: item.product.images || [],
            } : null
          }));
        if (items.length === 0) return null;
        const storeTotal = items.reduce((sum, item) => sum + item.sale_price * item.quantity, 0);
        return {
          seller_id: store.seller_id,
          store_name: store.store_name || '',
          store_url: store.store_url || '',
          district_id: sellerAddress.district_id || null,
          ward_code: sellerAddress.ward_code || null,
          items,
          store_total: storeTotal,
          discount: getShopDiscount(store.seller_id),
          selectedDiscountId: getShopDiscountId(store.seller_id),
          shipping_fee: store.shipping_fee || 0,
          original_shipping_fee: store.shipping_fee || 0,
          service_id: shopServiceIds.value[store.seller_id] || null,
          shipping_discount: shippingDiscountPerShop, // Áp dụng mã giảm giá vận chuyển
        };
      })
      .filter(store => store !== null);
  });

  const buyNowItems = computed(() => {
    if (!isBuyNow.value || !buyNowData.value) return [];
    const price = parsePrice(buyNowData.value.price);
    const sellerId = buyNowData.value.seller_id;
    const sellerAddress = sellerAddresses.value[sellerId] || {};
    const shippingDiscount = getShippingDiscountPerShop(total.value, 1); // Chỉ 1 shop cho Buy Now
    
    // Sử dụng phí ship đã được tính toán từ loadShippingFees
    // Nếu có service_id trong shopServiceIds, sử dụng phí ship đã tính
    // Nếu không, sử dụng phí ship từ buyNowData (có thể là 0)
    const hasCalculatedShipping = shopServiceIds.value[sellerId];
    const calculatedShippingFee = hasCalculatedShipping ? 
      (buyNowData.value.shipping_fee || 0) : 0;
    
    return [{
      seller_id: sellerId || null,
      store_name: buyNowData.value.store_name || '',
      store_url: buyNowData.value.store_url || '',
      district_id: sellerAddress.district_id || null,
      ward_code: sellerAddress.ward_code || null,
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
          weight: buyNowData.value.productVariant.weight || 1000,
          length: buyNowData.value.productVariant.length || 30,
          width: buyNowData.value.productVariant.width || 20,
          height: buyNowData.value.productVariant.height || 10,
        } : null,
        quantity: buyNowData.value.quantity,
        price,
        sale_price: price,
      }],
      store_total: price * buyNowData.value.quantity,
      discount: getShopDiscount(sellerId),
      selectedDiscountId: getShopDiscountId(sellerId),
      shipping_fee: calculatedShippingFee,
      original_shipping_fee: calculatedShippingFee,
      service_id: shopServiceIds.value[sellerId] || buyNowData.value.service_id || null,
      shipping_discount: shippingDiscount, // Áp dụng mã giảm giá vận chuyển
    }];
  });

  const total = computed(() => {
    if (isBuyNow.value && buyNowData.value) {
      const price = parsePrice(buyNowData.value.price);
      return price * buyNowData.value.quantity;
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

  const totalShippingFee = computed(() => {
    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    return items.reduce((sum, store) => sum + (store.shipping_fee || 0), 0);
  });

  const finalShippingFee = computed(() => {
    const discount = getShippingDiscount(total.value);
    return Math.max(0, totalShippingFee.value - discount);
  });

  const finalTotal = computed(() => {
    const baseTotal = total.value;
    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    const shopDiscountsTotal = items.reduce((sum, shop) => sum + (shop.discount || 0), 0);
    return Math.max(0, baseTotal - shopDiscountsTotal + finalShippingFee.value);
  });

  const totalShippingDiscount = computed(() => {
    return typeof getShippingDiscount === 'function' ? getShippingDiscount(total.value) : 0;
  });

  const realShippingFee = computed(() => {
    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    const shopCount = items.length;
    if (shopCount === 0) return 0;

    const totalRealShippingFee = items.reduce((sum, shop) => {
      const originalFee = shop.original_shipping_fee || shop.shipping_fee || 0;
      const discount = shop.shipping_discount || 0;
      const realFee = Math.max(0, originalFee - discount);
      return sum + realFee;
    }, 0);

    return totalRealShippingFee;
  });

  const realFinalTotal = computed(() => {
    const baseTotal = total.value;
    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    const shopDiscountsTotal = items.reduce((sum, shop) => sum + (shop.discount || 0), 0);
    return Math.max(0, baseTotal - shopDiscountsTotal + realShippingFee.value);
  });

  const formatPrice = (price) => {
    if (price == null || price === '') return '0';
    const parsed = parseFloat(price);
    if (isNaN(parsed)) return '0';
    return parsed.toLocaleString('vi-VN', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    });
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
    if (isPlacingOrder.value) return;

    if (isAccountBanned.value) {
      throw new Error('Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận.');
    }

    const items = isBuyNow.value ? buyNowItems.value : cartItems.value;
    if (!items || !items.length) {
      throw new Error('Giỏ hàng trống hoặc chưa chọn sản phẩm.');
    }
    if (!selectedPaymentMethod.value) {
      throw new Error('Vui lòng chọn phương thức thanh toán.');
    }
    
    if (!selectedAddress.value || !selectedAddress.value.district_id || !selectedAddress.value.ward_code) {
      throw new Error('Vui lòng chọn địa chỉ giao hàng.');
    }
    
    // Kiểm tra xem tất cả các shop đã có phí ship chưa
    for (const store of items) {
      if (!store.shipping_fee || !store.service_id) {
        throw new Error('Vui lòng chờ phí vận chuyển được tính toán hoàn tất.');
      }
    }

    isPlacingOrder.value = true;
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        window.dispatchEvent(new CustomEvent('openLoginModal'));
        throw new Error('Vui lòng đăng nhập để tiếp tục.');
      }

      // Lấy thông tin user
      const userRes = await fetch(`${config.public.apiBaseUrl}/me`, {
        headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      });
      if (!userRes.ok) {
        if (userRes.status === 401) {
          localStorage.removeItem('access_token');
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          throw new Error('Phiên đăng nhập hết hạn');
        }
        throw new Error('Không thể lấy thông tin người dùng');
      }
      const { data: userData } = await userRes.json();
      if (!userData?.id) throw new Error('Không tìm thấy thông tin người dùng');

      // Xử lý phí ship trước
      if (isBuyNow.value) {
        const bnItem = buyNowItems.value[0];
        if (!bnItem.service_id || !bnItem.shipping_fee) {
          console.warn('[BUY_NOW] Thiếu phí ship, tính lại...');
          const { fee, service_id } = await calculateShippingFee(
            bnItem.seller_id,
            sellerAddresses.value[bnItem.seller_id],
            selectedAddress.value
          );
          if (!fee || !service_id) {
            throw new Error('Không thể tính phí vận chuyển cho đơn mua ngay');
          }
          bnItem.shipping_fee = fee;
          bnItem.service_id = service_id;
        }
      } else {
        for (const store of items) {
          if (!store.service_id || !store.shipping_fee) {
            console.warn(`[CART] Thiếu phí ship cho shop ${store.seller_id}, tính lại...`);
            const { fee, service_id } = await calculateShippingFee(
              store.seller_id,
              sellerAddresses.value[store.seller_id],
              selectedAddress.value
            );
            store.shipping_fee = fee;
            store.service_id = service_id;
          }
        }
      }

      // Build dữ liệu gửi API
      const allItems = [];
      const storeShippingFees = {};
      const storeServiceIds = {};
      const storeDiscounts = {};
      const storeShippingDiscounts = {}; // Thêm riêng shipping discounts
      const storeProductDiscounts = {}; // Thêm riêng product discounts

      if (isBuyNow.value) {
        const store = buyNowItems.value?.[0];
        if (!store) throw new Error('Không có dữ liệu Buy Now');

        const line = store.items?.[0];
        if (!line?.product?.id) {
          console.error('BuyNow line item thiếu product_id:', line);
          throw new Error('Thiếu product_id cho đơn mua ngay');
        }

        if (!store.service_id || !store.shipping_fee) {
          const { fee, service_id } = await calculateShippingFee(
            store.seller_id,
            sellerAddresses.value[store.seller_id],
            selectedAddress.value
          );
          if (!service_id || !fee) {
            throw new Error('Không thể tính phí vận chuyển cho đơn mua ngay');
          }
          store.service_id = service_id;
          store.shipping_fee = fee;
        }

        const unitPrice = parsePrice(line.sale_price ?? line.price);
        if (!unitPrice || unitPrice <= 0) {
          console.error('Giá đơn vị không hợp lệ ở buy now:', line);
          throw new Error('Giá sản phẩm không hợp lệ');
        }

        allItems.push({
          product_id: line.product.id,
          product_variant_id: line.productVariant?.id || null,
          quantity: line.quantity,
          price: unitPrice,
          seller_id: store.seller_id,
          shipping_fee: store.shipping_fee,
          service_id: store.service_id,
          shipping_discount: store.shipping_discount || 0, // Thêm shipping_discount
        });

        storeShippingFees[store.seller_id] = store.shipping_fee;
        storeServiceIds[store.seller_id] = store.service_id;
        storeDiscounts[store.seller_id] = (store.discount || 0) + (store.shipping_discount || 0); // Tổng hợp cả giảm giá sản phẩm và vận chuyển
        storeShippingDiscounts[store.seller_id] = store.shipping_discount || 0; // Riêng shipping discount
        storeProductDiscounts[store.seller_id] = store.discount || 0; // Riêng product discount
      } else {
        items.forEach(store => {
          const serviceId = store.service_id;
          const shippingFee = store.shipping_fee;
          const shopDiscount = (store.discount || 0) + (store.shipping_discount || 0); // Tổng hợp cả giảm giá sản phẩm và vận chuyển
          store.items.forEach(p => {
            allItems.push({
              product_id: p.product.id,
              product_variant_id: p.productVariant?.id || null,
              quantity: p.quantity,
              price: parsePrice(p.sale_price || p.price),
              seller_id: store.seller_id,
              shipping_fee: shippingFee,
              service_id: serviceId,
              shipping_discount: store.shipping_discount || 0, // Thêm shipping_discount
            });
          });
          storeShippingFees[store.seller_id] = shippingFee;
          storeServiceIds[store.seller_id] = serviceId;
          storeDiscounts[store.seller_id] = shopDiscount;
          storeShippingDiscounts[store.seller_id] = store.shipping_discount || 0; // Riêng shipping discount
          storeProductDiscounts[store.seller_id] = store.discount || 0; // Riêng product discount
        });
      }

      if (!allItems.length) throw new Error('Không có sản phẩm hợp lệ để đặt hàng');

      const orderData = {
        user_id: userData.id,
        address_id: selectedAddress.value?.id || null,
        address: selectedAddress.value?.detail || 'Chưa cung cấp địa chỉ',
        receiver_name: selectedAddress.value?.name || userData.name,
        receiver_phone: selectedAddress.value?.phone || '',
        payment_method: selectedPaymentMethod.value,
        discount_ids: selectedDiscounts.value.map(d => d.id),
        items: allItems,
        ward_id: selectedAddress.value?.ward_code || null,
        district_id: selectedAddress.value?.district_id || null,
        province_id: selectedAddress.value?.province_id || null,
        is_buy_now: isBuyNow.value,
        skip_stock_check: true,
        store_notes: storeNotes.value || {},
        store_shipping_fees: storeShippingFees,
        store_service_ids: storeServiceIds,
        store_discounts: storeDiscounts, // Bao gồm cả giảm giá sản phẩm và vận chuyển
        store_shipping_discounts: storeShippingDiscounts, // Riêng shipping discounts
        store_product_discounts: storeProductDiscounts, // Riêng product discounts
      };

      console.log('Dữ liệu đơn hàng gửi API:', orderData); // Log để debug

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

      const orderedItemsForRemoval = allItems.map(item => ({
        product_id: item.product_id,
        product_variant_id: item.product_variant_id,
        quantity: item.quantity
      }));

      if (isBuyNow.value) localStorage.removeItem('buy_now');

      const orderIds = orders.map(order => order.id).join(',');

      if (selectedPaymentMethod.value === 'COD') {
        if (orderedItemsForRemoval.length > 0) {
          try {
            await clearOrderedItems(orderedItemsForRemoval);
          } catch (error) {
            console.error('COD - Lỗi khi gọi clearOrderedItems:', error);
          }
        } else {
          console.log('COD - Không có items nào để xóa');
        }
      } else {
        console.log(`${selectedPaymentMethod.value} - Không xóa cart items ngay, đợi thanh toán thành công`);
      }
      if (selectedPaymentMethod.value === 'VNPAY') {
        const paymentResponse = await fetch(`${config.public.apiBaseUrl}/payments/vnpay/create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            order_ids: orders.map(order => order.id)
          }),
        });
        if (!paymentResponse.ok) {
          throw new Error('Không thể tạo thanh toán VNPay');
        }
        const paymentData = await paymentResponse.json();
        if (paymentData.data && paymentData.data.payment_url) {
          window.location.href = paymentData.data.payment_url;
        } else {
          throw new Error('Không nhận được URL thanh toán VNPay');
        }
      } else if (selectedPaymentMethod.value === 'MOMO') {
        const paymentResponse = await fetch(`${config.public.apiBaseUrl}/payments/momo/create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            order_ids: orders.map(order => order.id)
          }),
        });
        if (!paymentResponse.ok) {
          throw new Error('Không thể tạo thanh toán MoMo');
        }
        const paymentData = await paymentResponse.json();
        if (paymentData.data && paymentData.data.payment_url) {
          window.location.href = paymentData.data.payment_url;
        } else {
          throw new Error('Không nhận được URL thanh toán MoMo');
        }
      } else {
        await navigateTo(`/order-success?ids=${orderIds}`);
      }
      await fetchCart();
    } catch (err) {
      console.error('Lỗi khi đặt hàng:', err);
      toast('error', err.message || 'Đã xảy ra lỗi khi đặt hàng. Vui lòng thử lại.');
    } finally {
      isPlacingOrder.value = false;
    }
  };

  loadBuyNowData();
  checkCodEligibility();

  // Watch selectedAddress để tự động tính phí ship cho Buy Now
  watch(selectedAddress, async (newAddress) => {
    if (isBuyNow.value && buyNowData.value && newAddress && newAddress.province_id && newAddress.district_id) {
      console.log('[BUY_NOW] Address changed, triggering loadShippingFees');
      // Đợi một chút để đảm bảo sellerAddresses đã được cập nhật
      await nextTick();
      await loadShippingFees();
    }
  });

  return {
    cartItems,
    buyNowItems,
    cart,
    total,
    formattedTotal,
    finalTotal,
    formattedFinalTotal,
    totalShippingFee,
    finalShippingFee,
    formattedFinalShippingFee,
    loading,
    error,
    paymentMethods: filteredPaymentMethods,
    paymentLoading,
    paymentError,
    placeOrder,
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
    selectStoreItems,
    isBuyNow,
    buyNowData,
    updateShopDiscount,
    getShopDiscount,
    getShopDiscountId,
    canUseCod,
    isAccountBanned,
    rejectedOrdersCount,
    checkCodEligibility,
    loadShippingFees,
    sellerAddresses,
    fetchDefaultAddress,
    fetchSellerAddress,
    fetchGHNServiceId,
    calculateShippingFee,
    shippingMethods,
    defaultShippingMethod,
    shopServiceIds,
    shippingFeeCache,
    getShippingDiscountPerShop,
    getProductDiscountPerShop,
    realShippingFee,
    realFinalTotal,
    totalShippingDiscount,
    isPlacingOrder,
    isCheckoutCalculatingShipping,
    removeShopDiscount,
    recalculateAllShopDiscounts
  };
}