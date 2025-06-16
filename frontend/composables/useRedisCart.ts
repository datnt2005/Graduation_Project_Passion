import { ref } from 'vue'

interface RedisCartItem {
  id: number
  quantity: number
  price: number
  product_variant_id: number
  productVariant: {
    id: number
    thumbnail: string
    product: {
      id: number
      name: string
    }
  }
}

export const useRedisCart = () => {
  const redisCartItems = ref<RedisCartItem[]>([])
  const redisCartTotal = ref<number>(0)
  const loading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const selectedItems = ref<Set<number>>(new Set())
  const selectAll = ref<boolean>(false)
  const config = useRuntimeConfig()

  // Generate a unique cart ID for anonymous users
  const getCartId = () => {
    let cartId = localStorage.getItem('redis_cart_id')
    if (!cartId) {
      cartId = 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
      localStorage.setItem('redis_cart_id', cartId)
    }
    return cartId
  }

  // Calculate total based on current items
  const calculateTotal = () => {
    redisCartTotal.value = redisCartItems.value.reduce((total, item) => {
      return total + (item.price * item.quantity)
    }, 0)
  }

  // Toggle select all items
  const toggleSelectAll = () => {
    selectAll.value = !selectAll.value
    if (selectAll.value) {
      redisCartItems.value.forEach(item => selectedItems.value.add(item.id))
    } else {
      selectedItems.value.clear()
    }
  }

  // Toggle select single item
  const toggleSelectItem = (itemId: number) => {
    if (selectedItems.value.has(itemId)) {
      selectedItems.value.delete(itemId)
      selectAll.value = false
    } else {
      selectedItems.value.add(itemId)
      selectAll.value = redisCartItems.value.every(item => selectedItems.value.has(item.id))
    }
  }

  // Fetch cart from Redis
  const fetchRedisCart = async () => {
    const cartId = getCartId()
    loading.value = true
    error.value = null

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/redis/${cartId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      if (!res.ok) {
        throw new Error('Lỗi khi lấy giỏ hàng')
      }

      const data = await res.json()
      if (data.success) {
        redisCartItems.value = data.data.items || []
        calculateTotal()
      } else {
        throw new Error(data.message || 'Lỗi khi lấy giỏ hàng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể lấy dữ liệu giỏ hàng'
    } finally {
      loading.value = false
    }
  }

  // Add item to Redis cart
  const addToRedisCart = async (productVariantId: number, quantity: number) => {
    const cartId = getCartId()
    loading.value = true
    error.value = null

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/redis/${cartId}/add`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_variant_id: productVariantId, quantity })
      })

      if (!res.ok) {
        throw new Error('Lỗi khi thêm vào giỏ hàng')
      }

      const data = await res.json()
      if (data.success) {
        await fetchRedisCart()
      } else {
        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể thêm vào giỏ hàng'
    } finally {
      loading.value = false
    }
  }

  // Update item quantity in Redis cart
  const updateRedisQuantity = async (itemId: number, newQuantity: number) => {
    const cartId = getCartId()
    loading.value = true
    error.value = null

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/redis/${cartId}/items/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ quantity: newQuantity })
      })

      if (!res.ok) {
        throw new Error('Lỗi khi cập nhật số lượng')
      }

      const data = await res.json()
      if (data.success) {
        await fetchRedisCart()
      } else {
        throw new Error(data.message || 'Lỗi khi cập nhật số lượng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể cập nhật số lượng'
    } finally {
      loading.value = false
    }
  }

  // Remove item from Redis cart
  const removeFromRedisCart = async (itemId: number) => {
    const cartId = getCartId()
    loading.value = true
    error.value = null

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/redis/${cartId}/items/${itemId}`, {
        method: 'DELETE'
      })

      if (!res.ok) {
        throw new Error('Lỗi khi xóa sản phẩm')
      }

      const data = await res.json()
      if (data.success) {
        await fetchRedisCart()
      } else {
        throw new Error(data.message || 'Lỗi khi xóa sản phẩm')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể xóa sản phẩm'
    } finally {
      loading.value = false
    }
  }

  // Clear Redis cart
  const clearRedisCart = async () => {
    const cartId = getCartId()
    loading.value = true
    error.value = null

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/redis/${cartId}`, {
        method: 'DELETE'
      })

      if (!res.ok) {
        throw new Error('Lỗi khi xóa giỏ hàng')
      }

      const data = await res.json()
      if (data.success) {
        redisCartItems.value = []
        redisCartTotal.value = 0
        selectedItems.value.clear()
        selectAll.value = false
      } else {
        throw new Error(data.message || 'Lỗi khi xóa giỏ hàng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể xóa giỏ hàng'
    } finally {
      loading.value = false
    }
  }

  // Merge Redis cart with user cart after login
  const mergeWithUserCart = async () => {
    const cartId = getCartId()
    const token = localStorage.getItem('access_token')
    if (!token) return

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/redis/${cartId}/merge`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      if (!res.ok) {
        throw new Error('Lỗi khi đồng bộ giỏ hàng')
      }

      const data = await res.json()
      if (data.success) {
        localStorage.removeItem('redis_cart_id')
        redisCartItems.value = []
        redisCartTotal.value = 0
        selectedItems.value.clear()
        selectAll.value = false
      } else {
        throw new Error(data.message || 'Lỗi khi đồng bộ giỏ hàng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể đồng bộ giỏ hàng'
    }
  }

  return {
    redisCartItems,
    redisCartTotal,
    loading,
    error,
    selectedItems,
    selectAll,
    fetchRedisCart,
    addToRedisCart,
    updateRedisQuantity,
    removeFromRedisCart,
    clearRedisCart,
    toggleSelectAll,
    toggleSelectItem,
    mergeWithUserCart
  }
} 