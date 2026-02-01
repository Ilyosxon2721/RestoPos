export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()

  // Initialize auth on first load
  if (process.client && !authStore.token) {
    authStore.initAuth()
  }

  // Public routes
  const publicRoutes = ['/login', '/register', '/pin-login']
  if (publicRoutes.includes(to.path)) {
    if (authStore.isAuthenticated) {
      return navigateTo('/')
    }
    return
  }

  // Protected routes
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }
})
