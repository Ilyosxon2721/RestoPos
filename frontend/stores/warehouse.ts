import { defineStore } from 'pinia'

interface Stock {
  id: string
  ingredient_id: string
  ingredient: Ingredient
  warehouse_id: string
  warehouse: Warehouse
  quantity: number
  min_quantity: number
  unit: string
  last_movement_at: string
}

interface Ingredient {
  id: string
  name: string
  sku: string
  unit: string
  category: string
  is_active: boolean
}

interface Warehouse {
  id: string
  name: string
  address: string
  is_default: boolean
  is_active: boolean
}

interface Supply {
  id: string
  supplier_id: string
  supplier: Supplier
  warehouse_id: string
  warehouse: Warehouse
  number: string
  status: 'draft' | 'pending' | 'received' | 'cancelled'
  total_amount: number
  items: SupplyItem[]
  notes: string | null
  expected_at: string | null
  received_at: string | null
  created_at: string
}

interface Supplier {
  id: string
  name: string
  phone: string
  email: string | null
  address: string | null
  is_active: boolean
}

interface SupplyItem {
  id: string
  ingredient_id: string
  ingredient: Ingredient
  quantity: number
  unit_price: number
  total_price: number
}

interface StockMovement {
  id: string
  ingredient_id: string
  ingredient: Ingredient
  warehouse_id: string
  type: 'in' | 'out' | 'transfer' | 'adjustment' | 'write_off'
  quantity: number
  reason: string
  created_at: string
}

interface WarehouseState {
  stocks: Stock[]
  lowStockItems: Stock[]
  supplies: Supply[]
  suppliers: Supplier[]
  warehouses: Warehouse[]
  ingredients: Ingredient[]
  movements: StockMovement[]
  loading: boolean
  error: string | null
}

export const useWarehouseStore = defineStore('warehouse', {
  state: (): WarehouseState => ({
    stocks: [],
    lowStockItems: [],
    supplies: [],
    suppliers: [],
    warehouses: [],
    ingredients: [],
    movements: [],
    loading: false,
    error: null
  }),

  getters: {
    stocksByWarehouse(): (warehouseId: string) => Stock[] {
      return (warehouseId: string) =>
        this.stocks.filter(s => s.warehouse_id === warehouseId)
    },

    lowStockCount(): number {
      return this.lowStockItems.length
    },

    pendingSupplies(): Supply[] {
      return this.supplies.filter(s => s.status === 'pending')
    },

    activeWarehouses(): Warehouse[] {
      return this.warehouses.filter(w => w.is_active)
    },

    activeSuppliers(): Supplier[] {
      return this.suppliers.filter(s => s.is_active)
    },

    getIngredientById(): (id: string) => Ingredient | undefined {
      return (id: string) => this.ingredients.find(i => i.id === id)
    }
  },

  actions: {
    async fetchStocks(params: Record<string, any> = {}) {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const response = await $api('/warehouse/stock', { params })
        this.stocks = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки остатков'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchLowStock() {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api('/warehouse/stock/low')
        this.lowStockItems = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки данных'
        throw e
      } finally {
        this.loading = false
      }
    },

    async adjustStock(data: {
      ingredient_id: string
      warehouse_id: string
      quantity: number
      reason: string
    }) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/warehouse/stock/adjust', {
          method: 'POST',
          body: data
        })
        await this.fetchStocks()
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка корректировки остатков'
        throw e
      }
    },

    async fetchSupplies(params: Record<string, any> = {}) {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api('/warehouse/supplies', { params })
        this.supplies = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки поставок'
        throw e
      } finally {
        this.loading = false
      }
    },

    async createSupply(data: Partial<Supply>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/warehouse/supplies', {
          method: 'POST',
          body: data
        })
        this.supplies.unshift(response.data)
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка создания поставки'
        throw e
      }
    },

    async updateSupply(id: string, data: Partial<Supply>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/warehouse/supplies/${id}`, {
          method: 'PUT',
          body: data
        })
        const index = this.supplies.findIndex(s => s.id === id)
        if (index !== -1) {
          this.supplies[index] = response.data
        }
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка обновления поставки'
        throw e
      }
    },

    async receiveSupply(id: string) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/warehouse/supplies/${id}/receive`, {
          method: 'POST'
        })
        const index = this.supplies.findIndex(s => s.id === id)
        if (index !== -1) {
          this.supplies[index] = response.data
        }
        // Refresh stocks after receiving
        await this.fetchStocks()
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка приёмки поставки'
        throw e
      }
    },

    async deleteSupply(id: string) {
      const { $api } = useNuxtApp()

      try {
        await $api(`/warehouse/supplies/${id}`, { method: 'DELETE' })
        this.supplies = this.supplies.filter(s => s.id !== id)
      } catch (e: any) {
        this.error = e.message || 'Ошибка удаления поставки'
        throw e
      }
    },

    async fetchWarehouses() {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/warehouses')
        this.warehouses = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки складов'
        throw e
      }
    },

    async fetchSuppliers() {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/suppliers')
        this.suppliers = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки поставщиков'
        throw e
      }
    },

    async fetchIngredients() {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/ingredients')
        this.ingredients = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки ингредиентов'
        throw e
      }
    },

    clearError() {
      this.error = null
    }
  }
})
