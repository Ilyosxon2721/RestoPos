import { defineStore } from 'pinia'

interface Customer {
  id: string
  name: string
  phone: string
  email: string | null
  birth_date: string | null
  bonus_balance: number
  total_orders: number
  total_spent: number
  customer_group_id: string | null
  customer_group?: CustomerGroup
  notes: string | null
  is_active: boolean
  created_at: string
}

interface CustomerGroup {
  id: string
  name: string
  discount_percent: number
  bonus_percent: number
}

interface BonusTransaction {
  id: string
  customer_id: string
  type: 'accrual' | 'redemption' | 'adjustment' | 'expiration'
  amount: number
  balance_after: number
  description: string
  created_at: string
}

interface CustomerState {
  customers: Customer[]
  currentCustomer: Customer | null
  groups: CustomerGroup[]
  bonusHistory: BonusTransaction[]
  loading: boolean
  error: string | null
  pagination: {
    total: number
    current_page: number
    per_page: number
    last_page: number
  }
}

export const useCustomerStore = defineStore('customer', {
  state: (): CustomerState => ({
    customers: [],
    currentCustomer: null,
    groups: [],
    bonusHistory: [],
    loading: false,
    error: null,
    pagination: {
      total: 0,
      current_page: 1,
      per_page: 20,
      last_page: 1
    }
  }),

  getters: {
    activeCustomers(): Customer[] {
      return this.customers.filter(c => c.is_active)
    },

    getCustomerById(): (id: string) => Customer | undefined {
      return (id: string) => this.customers.find(c => c.id === id)
    },

    customersWithBonus(): Customer[] {
      return this.customers.filter(c => c.bonus_balance > 0)
    }
  },

  actions: {
    async fetchCustomers(params: Record<string, any> = {}) {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const response = await $api('/customers', { params })
        this.customers = response.data
        if (response.meta) {
          this.pagination = {
            total: response.meta.total,
            current_page: response.meta.current_page,
            per_page: response.meta.per_page,
            last_page: response.meta.last_page
          }
        }
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки клиентов'
        throw e
      } finally {
        this.loading = false
      }
    },

    async searchCustomers(query: string) {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api('/customers/search', {
          params: { query }
        })
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка поиска клиентов'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchCustomer(id: string) {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api(`/customers/${id}`)
        this.currentCustomer = response.data
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки клиента'
        throw e
      } finally {
        this.loading = false
      }
    },

    async createCustomer(data: Partial<Customer>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/customers', {
          method: 'POST',
          body: data
        })
        this.customers.push(response.data)
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка создания клиента'
        throw e
      }
    },

    async updateCustomer(id: string, data: Partial<Customer>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/customers/${id}`, {
          method: 'PUT',
          body: data
        })
        const index = this.customers.findIndex(c => c.id === id)
        if (index !== -1) {
          this.customers[index] = response.data
        }
        if (this.currentCustomer?.id === id) {
          this.currentCustomer = response.data
        }
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка обновления клиента'
        throw e
      }
    },

    async deleteCustomer(id: string) {
      const { $api } = useNuxtApp()

      try {
        await $api(`/customers/${id}`, { method: 'DELETE' })
        this.customers = this.customers.filter(c => c.id !== id)
        if (this.currentCustomer?.id === id) {
          this.currentCustomer = null
        }
      } catch (e: any) {
        this.error = e.message || 'Ошибка удаления клиента'
        throw e
      }
    },

    async fetchCustomerHistory(customerId: string) {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api(`/customers/${customerId}/history`)
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки истории'
        throw e
      } finally {
        this.loading = false
      }
    },

    async addBonus(customerId: string, amount: number, description: string) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/customers/${customerId}/bonus`, {
          method: 'POST',
          body: { amount, description }
        })

        // Update customer's bonus balance
        const index = this.customers.findIndex(c => c.id === customerId)
        if (index !== -1) {
          this.customers[index].bonus_balance += amount
        }
        if (this.currentCustomer?.id === customerId) {
          this.currentCustomer.bonus_balance += amount
        }

        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка начисления бонусов'
        throw e
      }
    },

    async fetchGroups() {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/customer-groups')
        this.groups = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки групп'
        throw e
      }
    },

    setCurrentCustomer(customer: Customer | null) {
      this.currentCustomer = customer
    },

    clearError() {
      this.error = null
    }
  }
})
