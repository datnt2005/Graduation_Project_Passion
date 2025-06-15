import { ref } from 'vue'
import { useRedisCart } from './useRedisCart'

interface CartItem {
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

export const useCart = () => {
  const cartItems = ref<CartItem[]>([])
  const cartTotal = ref<number>(0)
  const loading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const selectedItems = ref<Set<number>>(new Set())
  const selectAll = ref<boolean>(false)
  const config = useRuntimeConfig()
  const mediaBaseUrl = config.public.mediaBaseUrl

  // Use Redis cart composable
  const {
    redisCartItems,
    redisCartTotal,
    fetchRedisCart,
    addToRedisCart,
    updateRedisQuantity,
    removeFromRedisCart,
    clearRedisCart,
    mergeWithUserCart
  } = useRedisCart()

  // Calculate total based on current items
  const calculateTotal = () => {
    cartTotal.value = cartItems.value.reduce((total, item) => {
      return total + (item.price * item.quantity)
    }, 0)
  }

  // Toggle select all items
  const toggleSelectAll = () => {
    selectAll.value = !selectAll.value
    if (selectAll.value) {
      cartItems.value.forEach(item => selectedItems.value.add(item.id))
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
      selectAll.value = cartItems.value.every(item => selectedItems.value.has(item.id))
    }
  }

  const fetchCart = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      // Use Redis cart for non-authenticated users
      await fetchRedisCart()
      cartItems.value = redisCartItems.value
      cartTotal.value = redisCartTotal.value
      return
    }

    loading.value = true
    error.value = null

    try {
      console.log('Fetching cart from:', `${config.public.apiBaseUrl}/cart`)
      const res = await fetch(`${config.public.apiBaseUrl}/cart`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
          'X-Requested-With': 'XMLHttpRequest',
          'Origin': window.location.origin
        },
        credentials: 'include'
      })

      console.log('Cart response status:', res.status)
      
      if (!res.ok) {
        if (res.status === 401) {
          console.log('Unauthorized: Token expired or invalid')
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
          localStorage.removeItem('access_token')
          // Fallback to Redis cart when session expires
          await fetchRedisCart()
          cartItems.value = redisCartItems.value
          cartTotal.value = redisCartTotal.value
          return
        }
        
        const errorData = await res.json().catch(() => ({}))
        console.error('API Error:', errorData)
        throw new Error(errorData.message || 'Lỗi khi lấy giỏ hàng')
      }

      const data = await res.json()
      console.log('Cart data:', data)
      
      if (data.success) {
        cartItems.value = data.data.items || []
        calculateTotal()
      } else {
        throw new Error(data.message || 'Lỗi khi lấy giỏ hàng')
      }
    } catch (err: any) {
      console.error('Cart fetch error:', err)
      error.value = err.message || 'Không thể lấy dữ liệu giỏ hàng'
      // Fallback to Redis cart on error
      await fetchRedisCart()
      cartItems.value = redisCartItems.value
      cartTotal.value = redisCartTotal.value
    } finally {
      loading.value = false
    }
  }

  const updateQuantity = async (itemId: number, newQuantity: number) => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      // Use Redis cart for non-authenticated users
      await updateRedisQuantity(itemId, newQuantity)
      cartItems.value = redisCartItems.value
      cartTotal.value = redisCartTotal.value
      return
    }

    // Find the item
    const itemIndex = cartItems.value.findIndex(item => item.id === itemId)
    if (itemIndex === -1) return

    // Store old quantity for rollback if needed
    const oldQuantity = cartItems.value[itemIndex].quantity

    // Optimistic update
    cartItems.value[itemIndex].quantity = newQuantity
    calculateTotal()

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/items/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ quantity: newQuantity })
      })

      if (!res.ok) {
        // Rollback on error
        cartItems.value[itemIndex].quantity = oldQuantity
        calculateTotal()

        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
          localStorage.removeItem('access_token')
          // Fallback to Redis cart when session expires
          await updateRedisQuantity(itemId, newQuantity)
          cartItems.value = redisCartItems.value
          cartTotal.value = redisCartTotal.value
          return
        }
        throw new Error('Lỗi khi cập nhật số lượng')
      }

      const data = await res.json()
      if (!data.success) {
        // Rollback on error
        cartItems.value[itemIndex].quantity = oldQuantity
        calculateTotal()
        throw new Error(data.message || 'Lỗi khi cập nhật số lượng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể cập nhật số lượng'
      setTimeout(() => {
        error.value = null
      }, 3000)
    }
  }

  const removeItem = async (itemId: number) => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      // Use Redis cart for non-authenticated users
      await removeFromRedisCart(itemId)
      cartItems.value = redisCartItems.value
      cartTotal.value = redisCartTotal.value
      return
    }

    // Find the item
    const itemIndex = cartItems.value.findIndex(item => item.id === itemId)
    if (itemIndex === -1) return

    // Store item for rollback if needed
    const removedItem = cartItems.value[itemIndex]

    // Optimistic update
    cartItems.value.splice(itemIndex, 1)
    calculateTotal()

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/items/${itemId}`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${token}`
        }
      })

      if (!res.ok) {
        // Rollback on error
        cartItems.value.splice(itemIndex, 0, removedItem)
        calculateTotal()

        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
          localStorage.removeItem('access_token')
          // Fallback to Redis cart when session expires
          await removeFromRedisCart(itemId)
          cartItems.value = redisCartItems.value
          cartTotal.value = redisCartTotal.value
          return
        }
        throw new Error('Lỗi khi xóa sản phẩm')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể xóa sản phẩm'
      setTimeout(() => {
        error.value = null
      }, 3000)
    }
  }

  const clearCart = async () => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      // Use Redis cart for non-authenticated users
      await clearRedisCart()
      cartItems.value = []
      cartTotal.value = 0
      return
    }

    // Store items for rollback if needed
    const oldItems = [...cartItems.value]

    // Optimistic update
    cartItems.value = []
    cartTotal.value = 0

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${token}`
        }
      })

      if (!res.ok) {
        // Rollback on error
        cartItems.value = oldItems
        calculateTotal()

        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
          localStorage.removeItem('access_token')
          // Fallback to Redis cart when session expires
          await clearRedisCart()
          cartItems.value = []
          cartTotal.value = 0
          return
        }
        throw new Error('Lỗi khi xóa giỏ hàng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể xóa giỏ hàng'
      setTimeout(() => {
        error.value = null
      }, 3000)
    }
  }

  // Add item to cart
  const addItem = async (productVariantId: number, quantity: number) => {
    const token = localStorage.getItem('access_token')
    if (!token) {
      // Use Redis cart for non-authenticated users
      await addToRedisCart(productVariantId, quantity)
      cartItems.value = redisCartItems.value
      cartTotal.value = redisCartTotal.value
      return
    }

    loading.value = true
    error.value = null

    try {
      const res = await fetch(`${config.public.apiBaseUrl}/cart/add`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ product_variant_id: productVariantId, quantity })
      })

      if (!res.ok) {
        if (res.status === 401) {
          error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
          localStorage.removeItem('access_token')
          // Fallback to Redis cart when session expires
          await addToRedisCart(productVariantId, quantity)
          cartItems.value = redisCartItems.value
          cartTotal.value = redisCartTotal.value
          return
        }
        throw new Error('Lỗi khi thêm vào giỏ hàng')
      }

      const data = await res.json()
      if (data.success) {
        await fetchCart()
      } else {
        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng')
      }
    } catch (err: any) {
      console.error('Lỗi:', err)
      error.value = err.message || 'Không thể thêm vào giỏ hàng'
      // Fallback to Redis cart on error
      await addToRedisCart(productVariantId, quantity)
      cartItems.value = redisCartItems.value
      cartTotal.value = redisCartTotal.value
    } finally {
      loading.value = false
    }
  }

  // Merge Redis cart with user cart after login
  const mergeCart = async () => {
    await mergeWithUserCart()
    await fetchCart()
  }

  return {
    cartItems,
    cartTotal,
    loading,
    error,
    selectedItems,
    selectAll,
    fetchCart,
    updateQuantity,
    removeItem,
    clearCart,
    toggleSelectAll,
    toggleSelectItem,
    addItem,
    mergeCart
  }
}