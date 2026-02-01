import { defineStore } from 'pinia'

interface User {
  id: number
  uuid: string
  email: string
  phone: string | null
  first_name: string
  last_name: string | null
  full_name: string
  avatar: string | null
  locale: string
}

interface Organization {
  id: number
  uuid: string
  name: string
  logo: string | null
}

interface AuthState {
  user: User | null
  organization: Organization | null
  token: string | null
  permissions: string[]
  accessibleBranches: number[]
  currentBranchId: number | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    organization: null,
    token: null,
    permissions: [],
    accessibleBranches: [],
    currentBranchId: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    hasPermission: (state) => (permission: string) => state.permissions.includes(permission),
    canAccessBranch: (state) => (branchId: number) => state.accessibleBranches.includes(branchId),
  },

  actions: {
    async login(credentials: { login: string; password: string }) {
      const { $api } = useNuxtApp()
      const response = await $api('/auth/login', {
        method: 'POST',
        body: credentials,
      })

      this.setAuth(response.data)
      return response
    },

    async pinLogin(pinCode: string, branchId: number) {
      const { $api } = useNuxtApp()
      const response = await $api('/auth/pin-login', {
        method: 'POST',
        body: { pin_code: pinCode, branch_id: branchId },
      })

      this.setAuth(response.data)
      this.currentBranchId = branchId
      return response
    },

    async fetchUser() {
      const { $api } = useNuxtApp()
      const response = await $api('/auth/me')

      this.user = response.data.user
      this.organization = response.data.organization
      this.permissions = response.data.permissions
      this.accessibleBranches = response.data.accessible_branches
    },

    setAuth(data: any) {
      this.user = data.user
      this.token = data.token
      if (data.organization) {
        this.organization = data.organization
      }
      if (data.permissions) {
        this.permissions = data.permissions
      }

      // Save token to localStorage
      if (process.client) {
        localStorage.setItem('auth_token', data.token)
      }
    },

    async logout() {
      const { $api } = useNuxtApp()
      try {
        await $api('/auth/logout', { method: 'POST' })
      } finally {
        this.clearAuth()
      }
    },

    clearAuth() {
      this.user = null
      this.organization = null
      this.token = null
      this.permissions = []
      this.accessibleBranches = []
      this.currentBranchId = null

      if (process.client) {
        localStorage.removeItem('auth_token')
      }
    },

    initAuth() {
      if (process.client) {
        const token = localStorage.getItem('auth_token')
        if (token) {
          this.token = token
          this.fetchUser().catch(() => this.clearAuth())
        }
      }
    },

    setBranch(branchId: number) {
      this.currentBranchId = branchId
    },
  },
})
