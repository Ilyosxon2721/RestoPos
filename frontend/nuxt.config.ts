export default defineNuxtConfig({
  devtools: { enabled: true },

  modules: [
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxtjs/i18n',
    '@primevue/nuxt-module',
  ],

  css: [
    'primeicons/primeicons.css',
    '~/assets/scss/main.scss',
  ],

  primevue: {
    autoImport: true,
    options: {
      ripple: true,
      theme: 'none',
    },
  },

  i18n: {
    locales: [
      { code: 'ru', file: 'ru.json', name: 'Русский' },
      { code: 'uz', file: 'uz.json', name: 'O\'zbekcha' },
      { code: 'en', file: 'en.json', name: 'English' },
    ],
    defaultLocale: 'ru',
    lazy: true,
    langDir: 'locales/',
    strategy: 'prefix_except_default',
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000/api/v1',
      wsHost: process.env.NUXT_PUBLIC_WS_HOST || 'localhost',
      wsPort: process.env.NUXT_PUBLIC_WS_PORT || '8085',
    },
  },

  app: {
    head: {
      title: 'RestoPOS',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'RestoPOS - Cloud POS System for Restaurants' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
      ],
    },
  },

  typescript: {
    strict: true,
  },

  compatibilityDate: '2024-01-01',
})
