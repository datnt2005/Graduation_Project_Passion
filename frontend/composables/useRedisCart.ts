import { ref } from 'vue';
import { useRuntimeConfig } from '#app';

// Định nghĩa interface cho dữ liệu Redis cart
interface Attribute {
  attribute: string;
  value: string;
}

interface ProductVariant {
  id: number;
  sku: string;
  thumbnail: string;
  attributes: Attribute[];
}

interface Product {
  id: number;
  name: string;
  slug: string;
  images: string[];
}

interface RedisCartItem {
  id: number;
  quantity: number;
  price: string;
  sale_price: string | null;
  product_variant_id: number;
  stock: number;
  productVariant: ProductVariant;
  product: Product;
}

export const useRedisCart = () => {
  const redisCartItems = ref<RedisCartItem[]>([]);
  const redisCartTotal = ref<string>('0');
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl;

  // Tạo hoặc lấy redis_cart_id
  const getCartId = () => {
    let cartId = localStorage.getItem('redis_cart_id');
    if (!cartId) {
      cartId = 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('redis_cart_id', cartId);
    }
    return cartId;
  };

  // Lấy giỏ hàng Redis
  const fetchRedisCart = async () => {
    const cartId = getCartId();
    try {
      const res = await fetch(`${apiBaseUrl}/cart/redis/${cartId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      });

      if (!res.ok) {
        throw new Error('Lỗi khi lấy giỏ hàng Redis');
      }

      const data = await res.json();
      if (data.success) {
        redisCartItems.value = data.data.items || [];
        redisCartTotal.value = data.data.total || '0';
      } else {
        throw new Error(data.message || 'Lỗi khi lấy giỏ hàng Redis');
      }
    } catch (err: any) {
      console.error('Redis cart fetch error:', err);
      redisCartItems.value = [];
      redisCartTotal.value = '0';
    }
  };

  // Thêm sản phẩm vào giỏ hàng Redis
  const addToRedisCart = async (productVariantId: number, quantity: number) => {
    const cartId = getCartId();
    try {
      const res = await fetch(`${apiBaseUrl}/cart/redis/${cartId}/add`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_variant_id: productVariantId, quantity }),
      });

      if (!res.ok) {
        throw new Error('Lỗi khi thêm vào giỏ hàng Redis');
      }

      const data = await res.json();
      if (data.success) {
        await fetchRedisCart();
      } else {
        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng Redis');
      }
    } catch (err: any) {
      console.error('Redis cart add error:', err);
    }
  };

  // Cập nhật số lượng sản phẩm trong giỏ hàng Redis
  const updateRedisQuantity = async (itemId: number, quantity: number) => {
    const cartId = getCartId();
    try {
      const res = await fetch(`${apiBaseUrl}/cart/redis/${cartId}/items/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quantity }),
      });

      if (!res.ok) {
        throw new Error('Lỗi khi cập nhật số lượng Redis');
      }

      const data = await res.json();
      if (data.success) {
        await fetchRedisCart();
      } else {
        throw new Error(data.message || 'Lỗi khi cập nhật số lượng Redis');
      }
    } catch (err: any) {
      console.error('Redis cart update error:', err);
    }
  };

  // Xóa sản phẩm khỏi giỏ hàng Redis
  const removeFromRedisCart = async (itemId: number) => {
    const cartId = getCartId();
    try {
      const res = await fetch(`${apiBaseUrl}/cart/redis/${cartId}/items/${itemId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (!res.ok) {
        throw new Error('Lỗi khi xóa sản phẩm Redis');
      }

      const data = await res.json();
      if (data.success) {
        await fetchRedisCart();
      } else {
        throw new Error(data.message || 'Lỗi khi xóa sản phẩm Redis');
      }
    } catch (err: any) {
      console.error('Redis cart remove error:', err);
    }
  };

  // Xóa toàn bộ giỏ hàng Redis
  const clearRedisCart = async () => {
    const cartId = getCartId();
    try {
      const res = await fetch(`${apiBaseUrl}/cart/redis/${cartId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (!res.ok) {
        throw new Error('Lỗi khi xóa giỏ hàng Redis');
      }

      const data = await res.json();
      if (data.success) {
        redisCartItems.value = [];
        redisCartTotal.value = '0';
        localStorage.removeItem('redis_cart_id');
      } else {
        throw new Error(data.message || 'Lỗi khi xóa giỏ hàng Redis');
      }
    } catch (err: any) {
      console.error('Redis cart clear error:', err);
    }
  };

  // Hợp nhất giỏ hàng Redis với giỏ hàng người dùng
  const mergeWithUserCart = async () => {
    const cartId = getCartId();
    const token = localStorage.getItem('access_token');
    if (!token) return;

    try {
      const res = await fetch(`${apiBaseUrl}/cart/redis/${cartId}/merge`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ redis_cart_id: cartId }),
      });

      if (!res.ok) {
        throw new Error('Lỗi khi hợp nhất giỏ hàng');
      }

      const data = await res.json();
      if (data.success) {
        localStorage.removeItem('redis_cart_id');
        redisCartItems.value = [];
        redisCartTotal.value = '0';
      } else {
        throw new Error(data.message || 'Lỗi khi hợp nhất giỏ hàng');
      }
    } catch (err: any) {
      console.error('Redis cart merge error:', err);
    }
  };

  return {
    redisCartItems,
    redisCartTotal,
    fetchRedisCart,
    addToRedisCart,
    updateRedisQuantity,
    removeFromRedisCart,
    clearRedisCart,
    mergeWithUserCart,
  };
};