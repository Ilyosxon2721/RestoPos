import { defineStore } from 'pinia'

interface CashShift {
  id: string
  terminal_id: string
  user_id: string
  user: {
    id: string
    name: string
  }
  opened_at: string
  closed_at: string | null
  opening_cash: number
  closing_cash: number | null
  expected_cash: number | null
  cash_difference: number | null
  status: 'open' | 'closed'
  notes: string | null
}

interface CashOperation {
  id: string
  cash_shift_id: string
  type: 'deposit' | 'withdrawal'
  amount: number
  reason: string
  created_at: string
}

interface ShiftReport {
  total_sales: number
  cash_sales: number
  card_sales: number
  orders_count: number
  average_check: number
  deposits: number
  withdrawals: number
  expected_cash: number
  operations: CashOperation[]
}

interface CashShiftState {
  shifts: CashShift[]
  currentShift: CashShift | null
  currentReport: ShiftReport | null
  loading: boolean
  error: string | null
}

export const useCashShiftStore = defineStore('cashShift', {
  state: (): CashShiftState => ({
    shifts: [],
    currentShift: null,
    currentReport: null,
    loading: false,
    error: null
  }),

  getters: {
    isShiftOpen(): boolean {
      return this.currentShift !== null && this.currentShift.status === 'open'
    },

    openShifts(): CashShift[] {
      return this.shifts.filter(s => s.status === 'open')
    },

    closedShifts(): CashShift[] {
      return this.shifts.filter(s => s.status === 'closed')
    }
  },

  actions: {
    async fetchShifts(params: Record<string, any> = {}) {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const response = await $api('/cash-shifts', { params })
        this.shifts = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки смен'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchCurrentShift() {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api('/cash-shifts/current')
        this.currentShift = response.data
      } catch (e: any) {
        if (e.response?.status !== 404) {
          this.error = e.message || 'Ошибка загрузки текущей смены'
        }
        this.currentShift = null
      } finally {
        this.loading = false
      }
    },

    async openShift(openingCash: number) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/cash-shifts/open', {
          method: 'POST',
          body: { opening_cash: openingCash }
        })
        this.currentShift = response.data
        this.shifts.unshift(response.data)
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка открытия смены'
        throw e
      }
    },

    async closeShift(shiftId: string, data: {
      closing_cash: number
      notes?: string
    }) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/cash-shifts/${shiftId}/close`, {
          method: 'POST',
          body: data
        })

        const index = this.shifts.findIndex(s => s.id === shiftId)
        if (index !== -1) {
          this.shifts[index] = response.data
        }

        if (this.currentShift?.id === shiftId) {
          this.currentShift = null
        }

        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка закрытия смены'
        throw e
      }
    },

    async addCashOperation(shiftId: string, data: {
      type: 'deposit' | 'withdrawal'
      amount: number
      reason: string
    }) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/cash-shifts/${shiftId}/cash-operation`, {
          method: 'POST',
          body: data
        })
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка операции с кассой'
        throw e
      }
    },

    async fetchShiftReport(shiftId: string) {
      const { $api } = useNuxtApp()
      this.loading = true

      try {
        const response = await $api(`/cash-shifts/${shiftId}/report`)
        this.currentReport = response.data
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки отчёта'
        throw e
      } finally {
        this.loading = false
      }
    },

    clearCurrentShift() {
      this.currentShift = null
      this.currentReport = null
    },

    clearError() {
      this.error = null
    }
  }
})
