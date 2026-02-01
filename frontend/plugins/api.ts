export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    onRequest({ options }) {
      if (authStore.token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${authStore.token}`,
        }
      }
      if (authStore.currentBranchId) {
        options.headers = {
          ...options.headers,
          'X-Branch-Id': authStore.currentBranchId.toString(),
        }
      }
    },
    onResponseError({ response }) {
      if (response.status === 401) {
        authStore.clearAuth()
        navigateTo('/login')
      }
    },
  })

  return {
    provide: {
      api,
    },
  }
})
