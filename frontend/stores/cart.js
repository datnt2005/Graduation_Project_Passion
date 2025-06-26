import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),
  actions: {
    addToCart(product) {
      const existing = this.items.find(
        (i) => i.product_id === product.product_id && i.variant === product.variant
      )
      if (existing) {
        existing.quantity += product.quantity
      } else {
        this.items.push(product)
      }
    },
  },
})
