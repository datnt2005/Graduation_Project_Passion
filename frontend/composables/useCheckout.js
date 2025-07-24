import { ref, computed, watch } from 'vue';
import { useRuntimeConfig, navigateTo } from '#app';
import { useToast } from '~/composables/useToast';
import { useCart } from '~/composables/useCart';
import { usePayment } from '~/composables/usePayment';
import { useDiscount } from '~/composables/useDiscount';
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
  const isAccountBanned = ref(false);
  const shopDiscounts = ref({});
  const shopDiscountIds = ref({});
  const sellerAddresses = ref({});
  const shippingMethods = ref({});
  const shopServiceIds = ref({}); // Thêm để lưu service_id riêng cho từng cửa hàng

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
        const shop = cartItems.value.find(s => s.seller_id === method.seller_id);
        const totalWeight = shop ? calculateTotalWeight(shop) : 0;
        if (method.service_id !== 100039 || totalWeight >= 2000) {
          methodsMap.set(method.service_id, method);
        }
      });
    });
    const availableMethods = Array.from(methodsMap.values());
    return availableMethods.find(m => [53321, 53322].includes(m.service_id)) || availableMethods[0] || null;
  });

  const calculateTotalWeight = (shop) => {
    return shop.items.reduce((sum, item) => {
      const itemWeight = item.productVariant?.weight || 1000;
      return sum + itemWeight * item.quantity;
    }, 0);
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
      if (sellerAddresses.value[sellerId]?.district_id && sellerAddresses.value[sellerId]?.ward_code) {
        console.log(`Lấy địa chỉ từ cache cho seller ${sellerId}:`, sellerAddresses.value[sellerId]);
        return sellerAddresses.value[sellerId];
      }
      console.log(`Gọi API để lấy địa chỉ seller ${sellerId}`);
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
      console.log(`Đã lưu địa chỉ cho seller ${sellerId}:`, sellerAddress);
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

      console.log(`Gọi API /ghn/services cho seller ${sellerId} (lần thử ${3 - retries}):`, {
        seller_id: sellerId,
        from_district_id: fromDistrictId,
        to_district_id: toDistrictId,
      });

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
          console.log(`Thử lại API /ghn/services cho seller ${sellerId}. Số lần thử còn lại: ${retries}`);
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
      console.log(`Phản hồi từ /ghn/services cho seller ${sellerId}:`, JSON.stringify(data, null, 2));
      shippingMethods.value[sellerId] = data.data || [];
      return data.data || [];
    } catch (err) {
      console.error(`Lỗi khi lấy danh sách dịch vụ cho seller ${sellerId}:`, err.message);
      throw err;
    }
  };

  const calculateShippingFee = async (sellerId, fromAddress, toAddress) => {
    if (!fromAddress || !fromAddress.district_id || !fromAddress.ward_code) {
      fromAddress = await fetchSellerAddress(sellerId);
      if (!fromAddress || !fromAddress.district_id || !fromAddress.ward_code) {
        console.error(`Thiếu thông tin địa chỉ của cửa hàng ${sellerId}.`);
        return { fee: 0, service_id: null };
      }
    }
    if (!toAddress || !toAddress.district_id || !toAddress.ward_code) {
      console.warn(`Thiếu thông tin địa chỉ nhận cho seller ${sellerId}:`, toAddress);
      return { fee: 0, service_id: null };
    }
    try {
      const services = await fetchGHNServiceId(sellerId, fromAddress.district_id, toAddress.district_id);
      if (!services || services.length === 0) {
        console.warn(`Không tìm thấy dịch vụ GHN phù hợp cho seller ${sellerId}`);
        return { fee: 0, service_id: null };
      }
      const storeItems = cartItems.value.find(store => store.seller_id === sellerId)?.items || [];
      const totalWeight = storeItems.reduce((sum, item) => {
        const weight = item.productVariant?.weight || 1000;
        return sum + weight * item.quantity;
      }, 0);
      if (totalWeight < 50) {
        console.warn(`Cân nặng quá thấp: ${totalWeight}g cho seller ${sellerId}. Tối thiểu 50g.`);
        return { fee: 0, service_id: null };
      }
      let service = services.find(s => [53321, 53322].includes(s.service_id));
      if (!service) {
        service = services.find(s => s.service_type_id === 2);
        if (!service) {
          service = services.find(s => s.service_type_id === 5);
        }
        if (!service) {
          service = services[0];
        }
      }
      const serviceId = service?.service_id || null;
      if (!serviceId) {
        console.error('Không tìm thấy dịch vụ vận chuyển hợp lệ');
        return { fee: 0, service_id: null };
      }
      if (serviceId === 100039 && totalWeight < 2000) {
        console.warn(`Cân nặng ${totalWeight}g không hợp lệ cho dịch vụ Hàng nặng (service_id: 100039).`);
        return { fee: 0, service_id: null };
      }
      shopServiceIds.value[sellerId] = serviceId; // Lưu service_id cho cửa hàng
      const dimensions = storeItems.reduce(
        (acc, item) => {
          const length = item.productVariant?.length || 30;
          const width = item.productVariant?.width || 20;
          const height = item.productVariant?.height || 10;
          return {
            length: Math.max(acc.length, length * item.quantity),
            width: Math.max(acc.width, width * item.quantity),
            height: acc.height + height * item.quantity,
          };
        },
        { length: 30, width: 20, height: 0 }
      );
      console.log(`Đang tính phí vận chuyển cho seller ${sellerId}:`, {
        seller_id: sellerId,
        from_district_id: fromAddress.district_id,
        from_ward_code: fromAddress.ward_code,
        to_district_id: toAddress.district_id,
        to_ward_code: toAddress.ward_code,
        service_id: serviceId,
        weight: totalWeight,
        ...dimensions,
      });
      const token = localStorage.getItem('access_token');
      if (!token) {
        console.error('Thiếu access token');
        return { fee: 0, service_id: null };
      }
      const response = await fetch(`${config.public.apiBaseUrl}/ghn/shipping-fee`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
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
        }),
      });
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Lỗi máy chủ' }));
        console.error('Lỗi phản hồi từ API GHN shipping-fee:', errorData);
        return { fee: 0, service_id: null };
      }
      const { data } = await response.json();
      const shippingFee = (data?.total || 0) / 100;
      console.log(`Phí vận chuyển cho seller ${sellerId}: ${shippingFee} VNĐ`);
      return { fee: shippingFee, service_id: serviceId };
    } catch (err) {
      console.error(`Lỗi khi tính phí vận chuyển cho seller ${sellerId}:`, err);
      return { fee: 0, service_id: null };
    }
  };

  const loadShippingFees = async () => {
    if (!cartItems.value.length) {
      console.warn('Giỏ hàng trống');
      toast('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm.');
      return;
    }
    for (const store of cartItems.value) {
      if (!store.seller_id) {
        console.warn('Thiếu seller_id:', store);
        toast('error', `Thiếu thông tin cửa hàng: ${store.store_name || 'Cửa hàng'}`);
        store.shipping_fee = 0;
        continue;
      }
      const { fee, service_id } = await calculateShippingFee(store.seller_id, sellerAddresses.value[store.seller_id], selectedAddress.value);
      store.shipping_fee = fee;
      if (service_id) {
        shopServiceIds.value[store.seller_id] = service_id;
      }
    }
  };

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
        const errorData = await response.json().catch(() => ({ message: 'Lỗi máy chủ' }));
        throw new Error(errorData.message || 'Lỗi khi kiểm tra trạng thái COD.');
      }
      const { can_use_cod, is_account_banned, message } = await response.json();
      canUseCod.value = can_use_cod;
      isAccountBanned.value = is_account_banned;
      if (is_account_banned) {
        toast('error', message || 'Tài khoản của bạn đã bị khóa.');
        await logout();
        await navigateTo('/login');
      } else if (!can_use_cod) {
        toast('warning', message || 'Bạn không thể sử dụng COD.');
      }
    } catch (err) {
      console.error('Lỗi khi kiểm tra điều kiện COD:', err);
      canUseCod.value = false;
      toast('error', err.message || 'Không thể kiểm tra trạng thái COD.');
    }
  };

  const loadBuyNowData = () => {
    if (typeof window === 'undefined' || !window.localStorage) return;
    const storedData = localStorage.getItem('buy_now');
    if (storedData) {
      buyNowData.value = JSON.parse(storedData);
      const maxAge = 30 * 60 * 1000; // 30 phút
      if (Date.now() - buyNowData.value.timestamp > maxAge) {
        localStorage.removeItem('buy_now');
        buyNowData.value = null;
        toast('error', 'Dữ liệu Buy Now đã hết hạn. Vui lòng chọn lại sản phẩm.');
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
      const sellerId = buyNowData.value.seller_id;
      const sellerAddress = sellerAddresses.value[sellerId] || {};
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
          price: price,
          sale_price: price,
        }],
        store_total: price * buyNowData.value.quantity,
        discount: getShopDiscount(sellerId),
        selectedDiscountId: getShopDiscountId(sellerId),
        shipping_fee: 0,
        service_id: shopServiceIds.value[sellerId] || null, // Thêm service_id
      }];
    }
    if (!cart.value || !cart.value.stores) return [];
    return cart.value.stores.map((store) => {
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
        shipping_fee: 0,
        service_id: shopServiceIds.value[store.seller_id] || null, // Thêm service_id
      };
    });
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
    return cartItems.value.reduce((sum, store) => sum + (store.shipping_fee || 0), 0);
  });

  const finalShippingFee = computed(() => {
    const discount = getShippingDiscount(total.value);
    return Math.max(0, totalShippingFee.value - discount);
  });

  const finalTotal = computed(() => {
    const baseTotal = total.value;
    const productDiscount = calculateDiscount(baseTotal);
    const shopDiscountsTotal = cartItems.value.reduce((sum, shop) => sum + (shop.discount || 0), 0);
    return Math.max(0, baseTotal - productDiscount - shopDiscountsTotal + finalShippingFee.value);
  });

  const totalShippingDiscount = computed(() => {
    return typeof getShippingDiscount === 'function' ? getShippingDiscount(total.value) : 0;
  });

  const realShippingFee = computed(() => {
    return Math.max(0, finalShippingFee.value);
  });

  const realFinalTotal = computed(() => {
    const baseTotal = total.value;
    const productDiscount = calculateDiscount(baseTotal);
    const shopDiscountsTotal = cartItems.value.reduce((sum, shop) => sum + (shop.discount || 0), 0);
    return Math.max(0, baseTotal - productDiscount - shopDiscountsTotal + realShippingFee.value);
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
      toast('error', 'Giỏ hàng trống hoặc chưa chọn sản phẩm.');
      return;
    }
    if (!selectedPaymentMethod.value) {
      toast('error', 'Vui lòng chọn phương thức thanh toán.');
      return;
    }
    if (!selectedShippingMethod.value || !selectedShippingMethod.value.service_id) {
      if (defaultShippingMethod.value?.service_id) {
        selectedShippingMethod.value = defaultShippingMethod.value;
        console.log(`Đã gán phương thức vận chuyển mặc định: ${defaultShippingMethod.value.service_id}`);
      } else {
        toast('error', 'Không có phương thức giao hàng khả dụng.');
        return;
      }
    }
    try {
        const token = localStorage.getItem('access_token');
        if (!token) {
            toast('error', 'Vui lòng đăng nhập để tiếp tục.');
            window.dispatchEvent(new CustomEvent('openLoginModal'));
            return;
        }

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

        const allItems = [];
        const storeShippingFees = {};
        const storeServiceIds = {};
        cartItems.value.forEach(store => {
            if (!store.district_id || !store.ward_code) {
                throw new Error(`Thiếu thông tin địa chỉ của cửa hàng ${store.store_name || store.seller_id}`);
            }
            if (store.items && Array.isArray(store.items)) {
                store.items.forEach(item => {
                    if (item.product && item.product.id) {
                        allItems.push({
                            product_id: item.product.id,
                            product_variant_id: item.productVariant?.id || null,
                            quantity: item.quantity,
                            price: parsePrice(item.sale_price || item.price),
                            seller_id: store.seller_id,
                            shipping_fee: store.shipping_fee || 0,
                        });
                    }
                });
                storeShippingFees[store.seller_id] = store.shipping_fee || 0;
                storeServiceIds[store.seller_id] = store.service_id || selectedShippingMethod.value.service_id || 53321;
            }
        });

        // Lấy phí cố định từ API shipping-methods
        const shippingMethodsResponse = await fetch(`${config.public.apiBaseUrl}/shipping-methods`, {
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token}`,
            },
        });
        if (!shippingMethodsResponse.ok) {
            throw new Error('Không thể lấy danh sách phương thức giao hàng');
        }
        const { data: shippingMethods } = await shippingMethodsResponse.json();

        // Áp dụng phí cố định
        Object.keys(storeServiceIds).forEach(sellerId => {
            const serviceId = storeServiceIds[sellerId];
            const method = shippingMethods.find(m => m.id === serviceId);
            if (method) {
                storeShippingFees[sellerId] = method.cost;
            } else {
                console.warn(`Không tìm thấy phương thức giao hàng ${serviceId} cho seller ${sellerId}`);
                storeShippingFees[sellerId] = 0;
            }
        });

        const orderData = {
            user_id: userData.id,
            address_id: selectedAddress.value?.id || null,
            address: selectedAddress.value?.detail || 'Chưa cung cấp địa chỉ',
            receiver_name: selectedAddress.value?.name || userData.name || 'Chưa cung cấp tên',
            receiver_phone: selectedAddress.value?.phone || 'Chưa cung cấp số điện thoại',
            payment_method: selectedPaymentMethod.value,
            service_id: selectedShippingMethod.value.service_id || 53321,
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
        };

        console.log('Dữ liệu đặt hàng:', JSON.stringify(orderData, null, 2));

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
      console.error('Lỗi khi đặt hàng:', err);
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
      console.error('Lỗi khi xóa mặt hàng đã đặt:', error);
    }
  };

  loadBuyNowData();
  checkCodEligibility();

  return {
    cartItems,
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
    removeOrderedItems,
    isBuyNow,
    buyNowData,
    updateShopDiscount,
    getShopDiscount,
    getShopDiscountId,
    canUseCod,
    isAccountBanned,
    checkCodEligibility,
    loadShippingFees,
    sellerAddresses,
    fetchDefaultAddress,
    fetchSellerAddress,
    fetchGHNServiceId,
    calculateShippingFee,
    shippingMethods,
    defaultShippingMethod,
    shopServiceIds, // Xuất shopServiceIds
  };
}