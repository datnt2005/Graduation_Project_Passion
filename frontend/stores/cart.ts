// stores/cart.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

interface CartItem {
  id: number;
  quantity: number;
  price: string;
  sale_price?: string | null;
  stock: number;
  product: any;
  productVariant: any;
}

interface CartStore {
  seller_id: number;
  store_name: string;
  store_url: string;
  items: CartItem[];
  store_total: string;
}

interface CartData {
  stores: CartStore[];
  total: string;
}

export const useCartStore = defineStore('cart', () => {
  const cart = ref<CartData>({
    stores: [],
    total: '0',
  });

  // Thêm state cho prevSelectedItems
  const prevSelectedItems = ref<number[]>([]);

  const totalItems = computed(() =>
    cart.value.stores.reduce((sum, store) => sum + store.items.length, 0)
  );

  const setCart = (newCart: Partial<CartData>) => {
    cart.value = {
      stores: (newCart.stores || []).map(store => ({
        ...store,
        items: store.items || [],
      })),
      total: String(newCart.total || '0'),
    };
  };

  // Thêm hàm setPrevSelectedItems
  const setPrevSelectedItems = (items: number[]) => {
    prevSelectedItems.value = items;
  };

  return {
    cart,
    totalItems,
    setCart,
    prevSelectedItems,
    setPrevSelectedItems, // export hàm này ra ngoài
  };
});