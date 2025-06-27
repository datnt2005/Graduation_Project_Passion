import { ref, computed, watch, onMounted } from 'vue';
import { navigateTo, useRuntimeConfig } from '#app';
import { useToast } from '~/composables/useToast';
import { useCartStore } from '~/stores/cart';

export function useCart() {
  const { toast } = useToast();
  const config = useRuntimeConfig();
  const mediaBaseUrl = config.public.mediaBaseUrl;
  const apiBaseUrl = config.public.apiBaseUrl;
  const isCartReady = ref(false);
  const cartStore = useCartStore();
  const cart = computed(() => cartStore.cart || { stores: [], total: '0' });

  const loading = ref(false);
  const error = ref(null);
  const selectedItems = ref(new Set());
  const selectAll = ref(false);
  const storeSelections = ref({});

  const totalItems = computed(() => {
    return cart.value.stores.reduce((sum, store) => sum + (store.items?.length || 0), 0);
  });

  const allItemIds = computed(() => {
    return cart.value.stores.flatMap(store => (store.items || []).map(item => item?.id).filter(id => id));
  });

  const selectedTotal = computed(() => {
    return cart.value.stores.reduce((total, store) => {
      return total + (store.items || []).reduce((storeTotal, item) => {
        if (item && selectedItems.value.has(item.id)) {
          return storeTotal + (parsePrice(item.sale_price) || parsePrice(item.price)) * (item.quantity || 1);
        }
        return storeTotal;
      }, 0);
    }, 0);
  });

  const parsePrice = (price) => {
    if (price == null) return 0;
    let clean = String(price).trim();
    if (clean.includes(',') && clean.includes('.')) {
      clean = clean.replace(/\./g, '').replace(',', '.');
    } else {
      clean = clean.replace(/[,.]/g, '');
    }
    const num = Number(clean.replace(/[^\d.-]/g, ''));
    return isNaN(num) ? 0 : num;
  };

  const syncSelectedItemsToBackend = async () => {
    if (!isCartReady.value) return;
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để đồng bộ giỏ hàng');
      return;
    }

    const itemIds = [...selectedItems.value].filter(id => !isNaN(id));
    loading.value = true;
    try {
      const res = await fetch(`${apiBaseUrl}/cart/select-items`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ item_ids: itemIds, select_all: selectAll.value }),
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Lỗi khi đồng bộ sản phẩm đã chọn');
      }

      // Update cart with backend response
      const cartData = data.data;
      cartStore.setCart(cartData);
      selectedItems.value = new Set(data.valid_item_ids || []);
      updateSelections();
    } catch (err) {
      toast('error', 'Không thể đồng bộ sản phẩm đã chọn: ' + err.message);
      console.error('Sync selected items error:', err);
      await fetchCart(); // Fetch latest cart state on error
    } finally {
      loading.value = false;
    }
  };

  const updateSelections = () => {
    const newSelections = {};
    cart.value.stores.forEach(store => {
      newSelections[store.seller_id] = (store.items || []).every(item => item && selectedItems.value.has(item.id));
    });
    storeSelections.value = newSelections;
    selectAll.value = allItemIds.value.length > 0 && allItemIds.value.every(id => selectedItems.value.has(id));
  };

  watch(
    () => cart.value.stores,
    () => {
      updateSelections();
    },
    { deep: true }
  );

  const handleItemSelection = async (itemId, event) => {
    if (!isCartReady.value) return;

    const itemExists = cart.value.stores.some(store =>
      (store.items || []).some(item => item?.id === itemId)
    );
    if (!itemExists) {
      toast('error', 'Sản phẩm không tồn tại trong giỏ hàng');
      return;
    }

    const isChecked = event.target.checked;
    if (isChecked) {
      selectedItems.value.add(itemId);
    } else {
      selectedItems.value.delete(itemId);
    }
    selectedItems.value = new Set(selectedItems.value);
    updateSelections();
    await syncSelectedItemsToBackend();
  };

  const handleSelectAll = async (event) => {
    if (!isCartReady.value || allItemIds.value.length === 0) {
      selectAll.value = false;
      toast('error', 'Giỏ hàng trống hoặc chưa sẵn sàng');
      return;
    }

    const isChecked = event.target.checked;
    selectAll.value = isChecked;
    selectedItems.value.clear();
    if (isChecked) {
      allItemIds.value.forEach(id => selectedItems.value.add(id));
    }
    cart.value.stores.forEach(store => {
      storeSelections.value[store.seller_id] = isChecked;
    });
    selectedItems.value = new Set(selectedItems.value);
    updateSelections();
    await syncSelectedItemsToBackend();
  };

  const handleStoreSelection = async (store, event) => {
    if (!isCartReady.value) return;

    const isChecked = event.target.checked;
    (store.items || []).forEach(item => {
      if (item?.id) {
        if (isChecked) {
          selectedItems.value.add(item.id);
        } else {
          selectedItems.value.delete(item.id);
        }
      }
    });
    selectedItems.value = new Set(selectedItems.value);
    storeSelections.value[store.seller_id] = isChecked;
    updateSelections();
    await syncSelectedItemsToBackend();
  };

  const selectStoreItems = async (store) => {
    if (!isCartReady.value) return;

    (store.items || []).forEach(item => {
      if (item?.id) selectedItems.value.add(item.id);
    });
    selectedItems.value = new Set(selectedItems.value);
    storeSelections.value[store.seller_id] = true;
    updateSelections();
    await syncSelectedItemsToBackend();
  };

  const navigateToCheckout = async () => {
    if (!isCartReady.value || selectedItems.value.size === 0) {
      toast('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán');
      return;
    }
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để tiếp tục');
      window.dispatchEvent(new CustomEvent('openLoginModal'));
      return;
    }

    loading.value = true;
    try {
      await syncSelectedItemsToBackend();
      await navigateTo({
        path: '/checkout',
      });
    } catch (err) {
      toast('error', 'Không thể chuyển đến trang thanh toán: ' + err.message);
      console.error('Checkout navigation error:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchSelectedItems = async () => {
    const token = localStorage.getItem('access_token');
    if (!token) {
      cartStore.setCart({ stores: [], total: '0' });
      isCartReady.value = true;
      return;
    }

    loading.value = true;
    try {
      const res = await fetch(`${apiBaseUrl}/cart/selected-items`, {
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
          localStorage.removeItem('access_token');
          cartStore.setCart({ stores: [], total: '0' });
          isCartReady.value = true;
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          return;
        }
        throw new Error('Lỗi khi lấy danh sách sản phẩm đã chọn');
      }

      const data = await res.json();
      if (data.success) {
        cartStore.setCart(data.data);
        selectedItems.value = new Set(data.valid_item_ids || []);
        updateSelections();
      } else {
        throw new Error(data.message || 'Lỗi khi lấy danh sách sản phẩm đã chọn');
      }
    } catch (err) {
      cartStore.setCart({ stores: [], total: '0' });
      toast('error', 'Không thể lấy danh sách sản phẩm đã chọn: ' + err.message);
      console.error('Fetch selected items error:', err);
    } finally {
      loading.value = false;
      isCartReady.value = true;
    }
  };

  const updateQuantityWithValidation = async (itemId, quantity) => {
    if (!isCartReady.value) return;
    const item = cart.value.stores.flatMap(store => store.items || []).find(item => item?.id === itemId);
    if (!item) return;

    const originalQuantity = item.quantity;
    const validatedQuantity = Math.max(1, Math.min(Math.floor(Number(quantity) || 1), item.stock));

    if (quantity > item.stock) {
      toast('error', `Số lượng không được vượt quá ${item.stock} sản phẩm`);
      item.quantity = item.stock;
      return;
    }
    if (quantity < 1) {
      toast('error', 'Số lượng phải lớn hơn 0');
      item.quantity = 1;
      return;
    }

    if (originalQuantity === validatedQuantity) return;

    item.quantity = validatedQuantity;
    loading.value = true;
    try {
      const token = localStorage.getItem('access_token');
      if (!token) {
        toast('error', 'Vui lòng đăng nhập để cập nhật số lượng');
        window.dispatchEvent(new CustomEvent('openLoginModal'));
        throw new Error('Chưa đăng nhập');
      }

      const res = await fetch(`${apiBaseUrl}/cart/items/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ quantity: validatedQuantity }),
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Lỗi khi cập nhật số lượng');
      }

      cartStore.setCart(data.data);
      selectedItems.value = new Set(data.valid_item_ids || []);
      updateSelections();
    } catch (error) {
      item.quantity = originalQuantity;
      toast('error', 'Không thể cập nhật số lượng: ' + error.message);
      console.error('Update quantity error:', error);
      await fetchCart();
    } finally {
      loading.value = false;
    }
  };

  const removeItem = async (itemId) => {
    const token = localStorage.getItem('access_token');
    selectedItems.value.delete(itemId);
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để xóa sản phẩm');
      window.dispatchEvent(new CustomEvent('openLoginModal'));
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
          localStorage.removeItem('access_token');
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          throw new Error('Chưa đăng nhập');
        }
        throw new Error('Lỗi khi xóa sản phẩm');
      }

      const data = await res.json();
      if (!data.success) {
        throw new Error(data.message || 'Lỗi khi xóa sản phẩm');
      }

      await fetchCart(); // Fetch updated cart
      updateSelections();
    } catch (err) {
      toast('error', 'Không thể xóa sản phẩm: ' + err.message);
      console.error('Remove item error:', err);
      await fetchCart();
    }
  };

  const clearCart = async () => {
    const token = localStorage.getItem('access_token');
    selectedItems.value.clear();
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để xóa giỏ hàng');
      window.dispatchEvent(new CustomEvent('openLoginModal'));
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
          localStorage.removeItem('access_token');
          cartStore.setCart({ stores: [], total: '0' });
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          return;
        }
        throw new Error('Lỗi khi xóa giỏ hàng');
      }

      cartStore.setCart({ stores: [], total: '0' });
      updateSelections();
    } catch (err) {
      toast('error', 'Không thể xóa giỏ hàng: ' + err.message);
      console.error('Clear cart error:', err);
      await fetchCart();
    }
  };

  const addItem = async (productVariantId, quantity) => {
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để thêm vào giỏ hàng');
      window.dispatchEvent(new CustomEvent('openLoginModal'));
      return;
    }

    try {
      loading.value = true;
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
          localStorage.removeItem('access_token');
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          return;
        }
        throw new Error('Lỗi khi thêm vào giỏ hàng');
      }

      const data = await res.json();
      if (data.success) {
        cartStore.setCart(data.data);
        const newItem = data.data.stores.flatMap(store => store.items || []).find(item => item.product_variant_id === productVariantId);
        if (newItem?.id) {
          selectedItems.value.add(newItem.id);
          await syncSelectedItemsToBackend();
        }
        toast('success', 'Đã thêm sản phẩm vào giỏ hàng');
      } else {
        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng');
      }
    } catch (err) {
      toast('error', 'Không thể thêm vào giỏ hàng: ' + err.message);
      console.error('Add item error:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchCart = async () => {
    const token = localStorage.getItem('access_token');
    if (!token) {
      cartStore.setCart({ stores: [], total: '0' });
      isCartReady.value = true;
      return;
    }

    loading.value = true;
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
          localStorage.removeItem('access_token');
          cartStore.setCart({ stores: [], total: '0' });
          isCartReady.value = true;
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          return;
        }
        throw new Error('Lỗi khi lấy giỏ hàng');
      }

      const data = await res.json();
      if (data.success) {
        cartStore.setCart(data.data);
        selectedItems.value = new Set(
          data.data.stores.flatMap(store => (store.items || []).filter(item => item?.is_selected).map(item => item.id))
        );
        updateSelections();
      } else {
        throw new Error(data.message || 'Lỗi khi lấy giỏ hàng');
      }
    } catch (err) {
      cartStore.setCart({ stores: [], total: '0' });
      toast('error', 'Không thể lấy dữ liệu giỏ hàng: ' + err.message);
      console.error('Fetch cart error:', err);
    } finally {
      loading.value = false;
      isCartReady.value = true;
    }
  };

  onMounted(async () => {
    await fetchCart();
    window.addEventListener('loginSuccess', () => {
      fetchCart();
    });
  });

  return {
    cart,
    loading,
    error,
    selectedItems,
    selectAll,
    storeSelections,
    totalItems,
    isCartReady,
    selectedTotal,
    mediaBaseUrl,
    fetchCart,
    navigateToCheckout,
    handleSelectAll,
    handleStoreSelection,
    handleItemSelection,
    selectStoreItems,
    updateQuantityWithValidation,
    removeItem,
    clearCart,
    addItem,
    parsePrice,
    fetchSelectedItems,
  };
}