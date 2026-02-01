import { defineStore } from 'pinia'

interface OrderItem {
  id?: number
  product_id: number
  name: string
  quantity: number
  unit_price: number
  total: number
  modifiers: any[]
  notes?: string
}

interface Order {
  id?: number
  uuid?: string
  order_number?: string
  table_id: number | null
  customer_id: number | null
  type: string
  items: OrderItem[]
  subtotal: number
  discount_amount: number
  discount_percent: number
  total: number
  notes?: string
}

export const useOrderStore = defineStore('order', {
  state: () => ({
    currentOrder: null as Order | null,
    orders: [] as Order[],
    loading: false,
  }),

  getters: {
    hasItems: (state) => state.currentOrder?.items.length ?? 0 > 0,
    itemsCount: (state) => state.currentOrder?.items.reduce((sum, item) => sum + item.quantity, 0) ?? 0,
  },

  actions: {
    initOrder(tableId: number | null = null, type: string = 'dine_in') {
      this.currentOrder = {
        table_id: tableId,
        customer_id: null,
        type,
        items: [],
        subtotal: 0,
        discount_amount: 0,
        discount_percent: 0,
        total: 0,
      }
    },

    addItem(product: any, quantity: number = 1, modifiers: any[] = []) {
      if (!this.currentOrder) {
        this.initOrder()
      }

      const modifiersTotal = modifiers.reduce((sum, m) => sum + (m.price * (m.quantity || 1)), 0)
      const unitPrice = product.price + modifiersTotal

      // Check if same product with same modifiers exists
      const existingIndex = this.currentOrder!.items.findIndex(
        item => item.product_id === product.id &&
        JSON.stringify(item.modifiers) === JSON.stringify(modifiers)
      )

      if (existingIndex >= 0) {
        this.currentOrder!.items[existingIndex].quantity += quantity
        this.currentOrder!.items[existingIndex].total =
          this.currentOrder!.items[existingIndex].quantity * unitPrice
      } else {
        this.currentOrder!.items.push({
          product_id: product.id,
          name: product.name,
          quantity,
          unit_price: unitPrice,
          total: unitPrice * quantity,
          modifiers,
        })
      }

      this.calculateTotals()
    },

    updateItemQuantity(index: number, quantity: number) {
      if (!this.currentOrder) return

      if (quantity <= 0) {
        this.removeItem(index)
        return
      }

      this.currentOrder.items[index].quantity = quantity
      this.currentOrder.items[index].total =
        this.currentOrder.items[index].unit_price * quantity

      this.calculateTotals()
    },

    removeItem(index: number) {
      if (!this.currentOrder) return

      this.currentOrder.items.splice(index, 1)
      this.calculateTotals()
    },

    calculateTotals() {
      if (!this.currentOrder) return

      this.currentOrder.subtotal = this.currentOrder.items.reduce(
        (sum, item) => sum + item.total, 0
      )

      let discountAmount = this.currentOrder.discount_amount
      if (this.currentOrder.discount_percent > 0) {
        discountAmount = this.currentOrder.subtotal * (this.currentOrder.discount_percent / 100)
      }

      this.currentOrder.total = this.currentOrder.subtotal - discountAmount
    },

    applyDiscount(amount: number = 0, percent: number = 0) {
      if (!this.currentOrder) return

      this.currentOrder.discount_amount = amount
      this.currentOrder.discount_percent = percent
      this.calculateTotals()
    },

    clearOrder() {
      this.currentOrder = null
    },

    async saveOrder() {
      if (!this.currentOrder || this.currentOrder.items.length === 0) return

      const { $api } = useNuxtApp()
      const authStore = useAuthStore()

      this.loading = true
      try {
        // Create order
        const orderResponse = await $api('/orders', {
          method: 'POST',
          body: {
            branch_id: authStore.currentBranchId,
            table_id: this.currentOrder.table_id,
            customer_id: this.currentOrder.customer_id,
            type: this.currentOrder.type,
            notes: this.currentOrder.notes,
          },
        })

        const orderId = orderResponse.data.id

        // Add items
        for (const item of this.currentOrder.items) {
          await $api(`/orders/${orderId}/items`, {
            method: 'POST',
            body: {
              product_id: item.product_id,
              quantity: item.quantity,
              modifiers: item.modifiers,
              notes: item.notes,
            },
          })
        }

        // Apply discount if any
        if (this.currentOrder.discount_amount > 0 || this.currentOrder.discount_percent > 0) {
          await $api(`/orders/${orderId}/discount`, {
            method: 'POST',
            body: {
              amount: this.currentOrder.discount_amount,
              percent: this.currentOrder.discount_percent,
            },
          })
        }

        this.clearOrder()
        return orderResponse.data
      } finally {
        this.loading = false
      }
    },
  },
})
