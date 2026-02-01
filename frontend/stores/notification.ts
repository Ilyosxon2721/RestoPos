import { defineStore } from 'pinia'

interface Notification {
  id: string
  type: string
  title: string
  message: string
  data: Record<string, any>
  read_at: string | null
  created_at: string
}

interface NotificationState {
  notifications: Notification[]
  unreadCount: number
  loading: boolean
  error: string | null
  pagination: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export const useNotificationStore = defineStore('notification', {
  state: (): NotificationState => ({
    notifications: [],
    unreadCount: 0,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    }
  }),

  getters: {
    unreadNotifications(): Notification[] {
      return this.notifications.filter(n => n.read_at === null)
    },

    readNotifications(): Notification[] {
      return this.notifications.filter(n => n.read_at !== null)
    },

    hasUnread(): boolean {
      return this.unreadCount > 0
    },

    getByType(): (type: string) => Notification[] {
      return (type: string) => this.notifications.filter(n => n.type === type)
    }
  },

  actions: {
    async fetchNotifications(params: Record<string, any> = {}) {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const response = await $api('/notifications', { params })
        this.notifications = response.data
        if (response.meta) {
          this.pagination = {
            current_page: response.meta.current_page,
            last_page: response.meta.last_page,
            per_page: response.meta.per_page,
            total: response.meta.total
          }
        }
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки уведомлений'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchUnreadCount() {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/notifications/unread-count')
        this.unreadCount = response.count
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки счётчика'
      }
    },

    async markAsRead(notificationId: string) {
      const { $api } = useNuxtApp()

      try {
        await $api(`/notifications/${notificationId}/read`, {
          method: 'POST'
        })

        const notification = this.notifications.find(n => n.id === notificationId)
        if (notification && !notification.read_at) {
          notification.read_at = new Date().toISOString()
          this.unreadCount = Math.max(0, this.unreadCount - 1)
        }
      } catch (e: any) {
        this.error = e.message || 'Ошибка отметки прочитанным'
        throw e
      }
    },

    async markAllAsRead() {
      const { $api } = useNuxtApp()

      try {
        await $api('/notifications/mark-all-read', {
          method: 'POST'
        })

        this.notifications.forEach(n => {
          if (!n.read_at) {
            n.read_at = new Date().toISOString()
          }
        })
        this.unreadCount = 0
      } catch (e: any) {
        this.error = e.message || 'Ошибка отметки всех прочитанными'
        throw e
      }
    },

    async deleteNotification(notificationId: string) {
      const { $api } = useNuxtApp()

      try {
        await $api(`/notifications/${notificationId}`, {
          method: 'DELETE'
        })

        const notification = this.notifications.find(n => n.id === notificationId)
        if (notification && !notification.read_at) {
          this.unreadCount = Math.max(0, this.unreadCount - 1)
        }

        this.notifications = this.notifications.filter(n => n.id !== notificationId)
      } catch (e: any) {
        this.error = e.message || 'Ошибка удаления уведомления'
        throw e
      }
    },

    async deleteAll() {
      const { $api } = useNuxtApp()

      try {
        await $api('/notifications/all', {
          method: 'DELETE'
        })

        this.notifications = []
        this.unreadCount = 0
      } catch (e: any) {
        this.error = e.message || 'Ошибка удаления уведомлений'
        throw e
      }
    },

    addNotification(notification: Notification) {
      this.notifications.unshift(notification)
      if (!notification.read_at) {
        this.unreadCount++
      }
    },

    clearError() {
      this.error = null
    }
  }
})
