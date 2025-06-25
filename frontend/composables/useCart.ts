import { ref, computed } from 'vue';
import { useRuntimeConfig } from '#app';
import { useRedisCart } from './useRedisCart';

// Define interfaces for cart data
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

interface CartItem {
  id: number;
  quantity: number;
  price: string;
  sale_price: string | null;
  product_variant_id: number;
  stock: number;
  productVariant: ProductVariant;
  product: Product;
}

interface Store {
  seller_id: number;
  store_name: string;
  store_url: string;
  items: CartItem[];
  store_total: string;
}

interface Cart {
  stores: Store[];
  total: string;
}

export const useCart = () => {
  const cart = ref<Cart>({ stores: [], total: '0' });
  const loading = ref<boolean>(false);
  const error = ref<string | null>(null);
  const selectedItems = ref<Set<number>>(new Set());
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl;

  // Use Redis cart composable
  const {
    redisCartItems,
    redisCartTotal,
    fetchRedisCart,
    addToRedisCart,
    updateRedisQuantity,
    removeFromRedisCart,
    clearRedisCart,
    mergeWithUserCart,
  } = useRedisCart();

  // Compute total number of items
  const totalItems = computed(() => {
    return cart.value.stores.reduce((sum, store) => sum + store.items.length, 0);
  });

  // Get all item IDs in the cart
  const allItemIds = computed(() => {
    return cart.value.stores.flatMap(store => store.items.map(item => item.id));
  });

  // Handle select all
  const selectAll = computed({
    get: () => {
      if (!totalItems.value) return false;
      return allItemIds.value.every(id => selectedItems.value.has(id));
    },
    set: (value: boolean) => {
      selectedItems.value = new Set(value ? allItemIds.value : []);
    },
  });

  // Sync selected items after cart updates
  const syncSelectedItems = () => {
    const validIds = new Set(allItemIds.value);
    const newSelected = new Set([...selectedItems.value].filter(id => validIds.has(id)));
    if (newSelected.size !== selectedItems.value.size) {
      selectedItems.value = newSelected;
    }
  };

  // Fetch cart data
  const fetchCart = async () => {
    const token = localStorage.getItem('access_token');
    if (!token) {
      await fetchRedisCart();
      cart.value = {
        stores: redisCartItems.value.length
          ? [{
              seller_id: 0,
              store_name: 'Khách',
              store_url: '/store/guest',
              items: redisCartItems.value,
              store_total: redisCartTotal.value,
            }]
          : [],
        total: redisCartTotal.value,
      };
      syncSelectedItems();
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const res = await fetch(`${apiBaseUrl}/cart`, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
          'X-Requested-With': 'XMLHttpRequest',
          Origin: window.location.origin,
        },
        credentials: 'include',
      });

      if (!res.ok) {
        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
          localStorage.removeItem('access_token');
          await fetchRedisCart();
          cart.value = {
            stores: redisCartItems.value.length
              ? [{
                  seller_id: 0,
                  store_name: 'Khách',
                  store_url: '/store/guest',
                  items: redisCartItems.value,
                  store_total: redisCartTotal.value,
                }]
              : [],
            total: redisCartTotal.value,
          };
          syncSelectedItems();
          return;
        }
        const errorData = await res.json().catch(() => ({}));
        throw new Error(errorData.message || 'Lỗi khi lấy giỏ hàng');
      }

      const data = await res.json();
      if (data.success) {
        cart.value = {
          stores: data.data.stores || [],
          total: data.data.total || '0',
        };
        syncSelectedItems();
      } else {
        throw new Error(data.message || 'Lỗi khi lấy giỏ hàng');
      }
    } catch (err: any) {
      error.value = err.message || 'Không thể lấy dữ liệu giỏ hàng';
      await fetchRedisCart();
      cart.value = {
        stores: redisCartItems.value.length
          ? [{
              seller_id: 0,
              store_name: 'Khách',
              store_url: '/store/guest',
              items: redisCartItems.value,
              store_total: redisCartTotal.value,
            }]
          : [],
        total: redisCartTotal.value,
      };
      syncSelectedItems();
    } finally {
      loading.value = false;
    }
  };

  // Update item quantity
  const updateQuantity = async (itemId: number, newQuantity: number) => {
    const token = localStorage.getItem('access_token');
    if (!token) {
      await updateRedisQuantity(itemId, newQuantity);
      await fetchCart();
      return;
    }

    try {
      const res = await fetch(`${apiBaseUrl}/cart/items/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ quantity: newQuantity }),
      });

      if (!res.ok) {
        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
          localStorage.removeItem('access_token');
          await updateRedisQuantity(itemId, newQuantity);
          await fetchCart();
          return;
        }
        throw new Error('Lỗi khi cập nhật số lượng');
      }

      const data = await res.json();
      if (!data.success) {
        throw new Error(data.message || 'Lỗi khi cập nhật số lượng');
      }

      await fetchCart();
    } catch (err: any) {
      error.value = err.message || 'Không thể cập nhật số lượng';
      setTimeout(() => {
        error.value = null;
      }, 3000);
    }
  };

  // Remove item
  const removeItem = async (itemId: number) => {
    const token = localStorage.getItem('access_token');
    selectedItems.value.delete(itemId);

    if (!token) {
      await removeFromRedisCart(itemId);
      await fetchCart();
      return;
    }

    try {
      const res = await fetch(`${apiBaseUrl}/cart/items/${itemId}`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!res.ok) {
        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
          localStorage.removeItem('access_token');
          await removeFromRedisCart(itemId);
          await fetchCart();
          return;
        }
        throw new Error('Lỗi khi xóa sản phẩm');
      }

      await fetchCart();
    } catch (err: any) {
      error.value = err.message || 'Không thể xóa sản phẩm';
      setTimeout(() => {
        error.value = null;
      }, 3000);
    }
  };

  // Clear cart
  const clearCart = async () => {
    const token = localStorage.getItem('access_token');
    selectedItems.value.clear();
    if (!token) {
      await clearRedisCart();
      cart.value = { stores: [], total: '0' };
      return;
    }

    try {
      const res = await fetch(`${apiBaseUrl}/cart`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!res.ok) {
        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
          localStorage.removeItem('access_token');
          await clearRedisCart();
          cart.value = { stores: [], total: '0' };
          return;
        }
        throw new Error('Lỗi khi xóa giỏ hàng');
      }

      await fetchCart();
    } catch (err: any) {
      error.value = err.message || 'Không thể xóa giỏ hàng';
      setTimeout(() => {
        error.value = null;
      }, 3000);
    }
  };

  // Add item to cart
  const addItem = async (productVariantId: number, quantity: number) => {
    const token = localStorage.getItem('access_token');
    if (!token) {
      await addToRedisCart(productVariantId, quantity);
      await fetchCart();
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const res = await fetch(`${apiBaseUrl}/cart/add`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ product_variant_id: productVariantId, quantity }),
      });

      if (!res.ok) {
        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
          localStorage.removeItem('access_token');
          await addToRedisCart(productVariantId, quantity);
          await fetchCart();
          return;
        }
        throw new Error('Lỗi khi thêm vào giỏ hàng');
      }

      const data = await res.json();
      if (data.success) {
        await fetchCart();
      } else {
        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng');
      }
    } catch (err: any) {
      error.value = err.message || 'Không thể thêm vào giỏ hàng';
      await addToRedisCart(productVariantId, quantity);
      await fetchCart();
    } finally {
      loading.value = false;
    }
  };

  // Merge Redis cart with user cart
  const mergeCart = async () => {
    await mergeWithUserCart();
    await fetchCart();
  };

  return {
    cart,
    loading,
    error,
    selectedItems,
    selectAll,
    totalItems,
    fetchCart,
    updateQuantity,
    removeItem,
    clearCart,
    addItem,
    mergeCart,
  };
};