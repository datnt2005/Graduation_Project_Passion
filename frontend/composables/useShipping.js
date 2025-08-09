import { shippingPerformance } from '~/utils/performance';

export function useShipping({ cartItems, sellerAddresses, shopServiceIds, shippingFeeCache }) {
  const config = useRuntimeConfig();
  const { toast } = useToast();
  const { logout } = useAuth();

  const CACHE_TTL = 3600 * 1000; // 1 giờ

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

  const calculateShippingFee = async (sellerId, fromAddress, toAddress, services = null) => {
    const startTime = shippingPerformance.startCalculation();
    shippingPerformance.currentCalculation.sellerId = sellerId;

    try {
      if (!fromAddress?.district_id || !fromAddress?.ward_code || !toAddress?.district_id || !toAddress?.ward_code) {
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }

      const storeItems = cartItems.value.find(store => store.seller_id === sellerId)?.items || [];
      const totalWeight = storeItems.reduce((sum, item) => {
        const weight = item.productVariant?.weight || 1000;
        return sum + weight * item.quantity;
      }, 0);
      if (totalWeight < 50) {
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: null };
      }

      let service = services?.find(s => s.service_id === shopServiceIds.value[sellerId]);
      if (!service) {
        service = services?.find(s => [53321, 53322].includes(s.service_id)) || services?.[0];
        if (service) shopServiceIds.value[sellerId] = service.service_id;
      }

      const serviceId = service?.service_id || null;
      if (!serviceId || (serviceId === 100039 && totalWeight < 2000)) {
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
        shippingPerformance.endCalculation(startTime);
        return { fee: cachedFee, service_id: serviceId };
      }

      shippingPerformance.cacheMiss();
      shippingPerformance.apiCall();

      const token = localStorage.getItem('access_token');
      if (!token) {
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
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: serviceId };
      }

      const { data } = await response.json();
      const shippingFee = data?.total || 0;
      if (shippingFee < 1000) {
        shippingPerformance.error();
        shippingPerformance.endCalculation(startTime);
        return { fee: 0, service_id: serviceId };
      }

      setCachedFee(cacheKey, shippingFee);
      shippingPerformance.endCalculation(startTime);
      return { fee: shippingFee, service_id: serviceId };
    } catch (err) {
      console.error(`Lỗi khi tính phí vận chuyển cho seller ${sellerId}:`, err);
      shippingPerformance.error();
      shippingPerformance.endCalculation(startTime);
      return { fee: 0, service_id: null };
    }
  };

  return {
    calculateShippingFee,
    getCacheKey,
    getCachedFee,
    setCachedFee
  };
}
