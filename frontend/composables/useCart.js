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
    const total = cart.value.stores.reduce((total, store) => {
      return total + (store.items || []).reduce((storeTotal, item) => {
        if (item && selectedItems.value.has(item.id)) {
          const price = parsePrice(item.sale_price || item.price);
          const quantity = item.quantity || 1;
          return storeTotal + price * quantity;
        }
        return storeTotal;
      }, 0);
    }, 0);
    return total;
  });

  const parsePrice = (price) => {
    if (price == null) {
      return 0;
    }
    let clean = String(price).trim();

    // Handle Vietnamese number formats: "315.000" (315,000), "315.000,00" (315,000.00), "315000" (315,000)
    if (clean.includes(',') && clean.includes('.')) {
      // "315.000,00" → comma is decimal separator, dot is thousand separator
      if (clean.lastIndexOf(',') > clean.lastIndexOf('.')) {
        clean = clean.replace(/\./g, '').replace(',', '.');
      } else {
        // "315,000.00" → dot is decimal separator, comma is thousand separator
        clean = clean.replace(/,/g, '');
      }
    } else if (clean.includes('.')) {
      // "315.000" → dot is thousand separator in Vietnamese format
      clean = clean.replace(/\./g, '');
    } else if (clean.includes(',')) {
      // "315,000" → comma is thousand separator
      clean = clean.replace(/,/g, '');
    }

    const num = parseFloat(clean.replace(/[^\d.-]/g, '')) || 0;
    const result = Math.round(num * 100) / 100; // Round to 2 decimal places
    return result;
  };

  const formatPrice = (price) => {
    const parsed = parsePrice(price);
    const formatted = parsed.toLocaleString('vi-VN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    return `${formatted} đ`;
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

      const cartData = data.data;
      cartStore.setCart(cartData);
      selectedItems.value = new Set(data.valid_item_ids || []);
      updateSelections();
    } catch (err) {
      toast('error', 'Không thể đồng bộ sản phẩm đã chọn: ' + err.message);
      await fetchCart();
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

    // Kiểm tra trạng thái sellers trước khi chuyển đến checkout
    try {
      const sellerIds = [...new Set(cart.value.stores.map(store => store.seller_id).filter(id => id))];
      const bannedSellers = [];

      for (const sellerId of sellerIds) {
        try {
          const response = await fetch(`${apiBaseUrl}/sellers/${sellerId}/status`, {
            method: 'GET',
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: 'application/json',
            },
          });

          if (response.ok) {
            const { data } = await response.json();
            if (data.is_banned) {
              const store = cart.value.stores.find(s => s.seller_id === sellerId);
              bannedSellers.push({
                seller_id: sellerId,
                store_name: store?.store_name || 'Cửa hàng',
                ban_reason: data.ban_reason || ''
              });
            }
          }
        } catch (err) {
          console.error(`Error checking seller ${sellerId} status:`, err);
        }
      }

      if (bannedSellers.length > 0) {
        const bannedStoreNames = bannedSellers.map(s => s.store_name).join(', ');
        toast('error', `Không thể thanh toán vì các cửa hàng sau đã bị cấm: ${bannedStoreNames}`);
        return;
      }
    } catch (err) {
      console.error('Error checking sellers status:', err);
      // Nếu không thể kiểm tra, vẫn cho phép chuyển đến checkout
    }

    loading.value = true;
    try {
      await syncSelectedItemsToBackend();
      await navigateTo({
        path: '/checkout',
      });
    } catch (err) {
      toast('error', 'Không thể chuyển đến trang thanh toán: ' + err.message);
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

      await fetchCart();
      updateSelections();
    } catch (err) {
      toast('error', 'Không thể xóa sản phẩm: ' + err.message);
      await fetchCart();
    }
  };

  const clearCart = async (orderItems = []) => {
    const token = localStorage.getItem('access_token');

    if (!token) {
      toast('error', 'Vui lòng đăng nhập để xóa giỏ hàng');
      window.dispatchEvent(new CustomEvent('openLoginModal'));
      return;
    }

    // Nếu không có orderItems, xóa toàn bộ giỏ hàng
    if (!Array.isArray(orderItems) || orderItems.length === 0) {
      try {
        // Lấy tất cả items trong giỏ hàng hiện tại
        const allItems = cart.value.stores.flatMap(store => store.items || []);
        const allItemIds = allItems.map(item => item.id).filter(id => !!id);

        if (allItemIds.length === 0) {
          // Giỏ hàng đã trống
          cartStore.setCart({ stores: [], total: '0' });
          selectedItems.value.clear();
          updateSelections();
          return;
        }

        // Xóa từng item
        for (const id of allItemIds) {
          const res = await fetch(`${apiBaseUrl}/cart/items/${id}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` },
          });

          if (!res.ok) {
            console.warn(`Không thể xóa item ${id}`);
          }
        }

        // Cập nhật giỏ hàng sau khi xóa
        cartStore.setCart({ stores: [], total: '0' });
        selectedItems.value.clear();
        updateSelections();
        return;
      } catch (err) {
        toast('error', 'Không thể xóa giỏ hàng: ' + err.message);
        return;
      }
    }

    const itemIds = orderItems.map(i => i.id).filter(id => !!id);

    if (itemIds.length === 0) {
      toast('error', 'Không tìm thấy cart_item_id để xóa sản phẩm khỏi giỏ hàng');
      return;
    }

    try {
      for (const id of itemIds) {
        const res = await fetch(`${apiBaseUrl}/cart/items/${id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
        });

        if (!res.ok) {
          throw new Error(`Lỗi khi xóa sản phẩm có ID: ${id}`);
        }
      }

      await fetchCart();
      updateSelections();
    } catch (err) {
      toast('error', 'Không thể xóa giỏ hàng: ' + err.message);
      await fetchCart();
    }
  };

  const clearAllCart = async () => {
    const token = localStorage.getItem('access_token');

    if (!token) {
      console.warn('Không có token để xóa giỏ hàng');
      return;
    }

    try {
      // Lấy tất cả items trong giỏ hàng hiện tại
      const allItems = cart.value.stores.flatMap(store => store.items || []);
      const allItemIds = allItems.map(item => item.id).filter(id => !!id);

      if (allItemIds.length === 0) {
        // Giỏ hàng đã trống
        cartStore.setCart({ stores: [], total: '0' });
        selectedItems.value.clear();
        updateSelections();
        return;
      }

      // Xóa từng item
      for (const id of allItemIds) {
        const res = await fetch(`${apiBaseUrl}/cart/items/${id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
        });

        if (!res.ok) {
          console.warn(`Không thể xóa item ${id}`);
        }
      }

      // Cập nhật giỏ hàng sau khi xóa
      cartStore.setCart({ stores: [], total: '0' });
      selectedItems.value.clear();
      updateSelections();
    } catch (err) {
      console.error('Không thể xóa giỏ hàng:', err.message);
    }
  };

  // Hàm mới: chỉ xóa những sản phẩm đã được thanh toán
  const clearOrderedItems = async (orderedItems) => {
    const token = localStorage.getItem('access_token');

    if (!token) {
      console.warn('Không có token để xóa sản phẩm đã đặt');
      return;
    }

    console.log('Token hiện tại:', token.substring(0, 20) + '...');

    try {
      console.log('Đang xóa những sản phẩm đã được thanh toán:', orderedItems);
      
      // Lấy danh sách tất cả items trong giỏ hàng hiện tại
      const allItems = cart.value.stores.flatMap(store => store.items || []);
      console.log('Tất cả items trong giỏ hàng hiện tại:', allItems);
      
      // Tìm những items cần xóa dựa trên orderedItems
      const itemsToRemove = [];
      
      for (const orderedItem of orderedItems) {
        console.log('Tìm item cho orderedItem:', orderedItem);
        
        const matchingItem = allItems.find(item => {
          const productMatch = item.product?.id === orderedItem.product_id;
          
          // Logic matching cho variant:
          // 1. Nếu cả hai đều có variant và ID giống nhau
          // 2. Nếu cả hai đều không có variant (null/undefined)
          // 3. Nếu item không có variant nhưng orderedItem có variant (trường hợp này cần xử lý đặc biệt)
          let variantMatch = false;
          
          if (item.product_variant?.id === orderedItem.product_variant_id) {
            // Cả hai đều có variant và ID giống nhau
            variantMatch = true;
          } else if (!item.product_variant?.id && !orderedItem.product_variant_id) {
            // Cả hai đều không có variant
            variantMatch = true;
          } else if (!item.product_variant?.id && orderedItem.product_variant_id) {
            // Item không có variant nhưng orderedItem có variant
            // Trong trường hợp này, chúng ta vẫn match vì có thể là cùng sản phẩm
            variantMatch = true;
          }
          
          console.log('Checking item:', {
            item_id: item.id,
            item_product_id: item.product?.id,
            item_variant_id: item.product_variant?.id,
            ordered_product_id: orderedItem.product_id,
            ordered_variant_id: orderedItem.product_variant_id,
            productMatch,
            variantMatch
          });
          
          return productMatch && variantMatch;
        });
        
        if (matchingItem) {
          itemsToRemove.push(matchingItem);
          console.log('Tìm thấy item khớp:', matchingItem);
        } else {
          console.log('Không tìm thấy item khớp cho:', orderedItem);
        }
      }

      console.log('Items sẽ được xóa:', itemsToRemove);

      // Xóa từng item đã được thanh toán
      for (const item of itemsToRemove) {
        console.log(`Đang xóa item ${item.id} (product_id: ${item.product?.id}, variant_id: ${item.product_variant?.id})`);
        
        const url = `${apiBaseUrl}/cart/items/${item.id}`;
        console.log('Gọi API:', url);
        
        const res = await fetch(url, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
        });

        console.log(`Response status cho item ${item.id}:`, res.status);
        console.log(`Response headers cho item ${item.id}:`, Object.fromEntries(res.headers.entries()));

        if (!res.ok) {
          const errorText = await res.text();
          console.warn(`Không thể xóa item ${item.id}:`, res.status, errorText);
        } else {
          const responseText = await res.text();
          console.log(`Đã xóa item ${item.id} khỏi giỏ hàng thành công. Response:`, responseText);
          // Xóa khỏi selectedItems nếu có
          selectedItems.value.delete(item.id);
        }
      }

      // Cập nhật lại giỏ hàng
      console.log('Cập nhật lại giỏ hàng sau khi xóa...');
      await fetchCart();
      updateSelections();
      
      console.log('Hoàn thành xóa sản phẩm đã thanh toán');
      console.log('Giỏ hàng sau khi xóa:', cart.value);
    } catch (err) {
      console.error('Không thể xóa sản phẩm đã đặt:', err.message);
      console.error('Error stack:', err.stack);
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

      const data = await res.json();

      if (!res.ok) {
        if (res.status === 401) {
          localStorage.removeItem('access_token');
          window.dispatchEvent(new CustomEvent('openLoginModal'));
          return;
        }

        if (res.status === 400 && data.message.includes('tồn kho')) {
          toast('error', data.message);
          return;
        }

        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng');
      }

      if (data.success) {
        cartStore.setCart(data.data);
        const newItem = data.data.stores.flatMap(store => store.items || [])
          .find(item => item.product_variant_id === productVariantId);

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
        // Validate prices
        data.data.stores.forEach(store => {
          store.items.forEach(item => {
            if (!item.price || isNaN(parseFloat(item.price))) {
              item.price = '0';
            }
          });
        });
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
    clearAllCart,
    addItem,
    parsePrice,
    formatPrice,
    fetchSelectedItems,
    clearOrderedItems,
  };
}