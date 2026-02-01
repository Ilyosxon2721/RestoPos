import { defineStore } from 'pinia'

interface Category {
  id: string
  name: string
  parent_id: string | null
  sort_order: number
  is_active: boolean
  children?: Category[]
}

interface Product {
  id: string
  category_id: string
  name: string
  sku: string
  description: string | null
  image_url: string | null
  price: number
  cost_price: number | null
  is_active: boolean
  is_available: boolean
  modifiers?: ModifierGroup[]
}

interface ModifierGroup {
  id: string
  name: string
  is_required: boolean
  min_selections: number
  max_selections: number
  modifiers: Modifier[]
}

interface Modifier {
  id: string
  name: string
  price: number
  is_default: boolean
  is_active: boolean
}

interface MenuState {
  categories: Category[]
  products: Product[]
  currentCategory: Category | null
  loading: boolean
  error: string | null
}

export const useMenuStore = defineStore('menu', {
  state: (): MenuState => ({
    categories: [],
    products: [],
    currentCategory: null,
    loading: false,
    error: null,
  }),

  getters: {
    categoryTree(): Category[] {
      const buildTree = (parentId: string | null): Category[] => {
        return this.categories
          .filter(c => c.parent_id === parentId)
          .sort((a, b) => a.sort_order - b.sort_order)
          .map(c => ({
            ...c,
            children: buildTree(c.id)
          }))
      }
      return buildTree(null)
    },

    activeCategories(): Category[] {
      return this.categories.filter(c => c.is_active)
    },

    productsByCategory(): (categoryId: string) => Product[] {
      return (categoryId: string) =>
        this.products.filter(p => p.category_id === categoryId && p.is_active)
    },

    availableProducts(): Product[] {
      return this.products.filter(p => p.is_active && p.is_available)
    },

    getProductById(): (id: string) => Product | undefined {
      return (id: string) => this.products.find(p => p.id === id)
    },
  },

  actions: {
    async fetchCategories() {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const response = await $api('/menu/categories')
        this.categories = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки категорий'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchProducts(categoryId?: string) {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const params: Record<string, any> = { active_only: true }
        if (categoryId) {
          params.category_id = categoryId
        }
        const response = await $api('/menu/products', { params })
        this.products = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки продуктов'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchCategoryTree() {
      const { $api } = useNuxtApp()
      this.loading = true
      this.error = null

      try {
        const response = await $api('/menu/categories/tree')
        this.categories = response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка загрузки дерева категорий'
        throw e
      } finally {
        this.loading = false
      }
    },

    async createCategory(data: Partial<Category>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/menu/categories', {
          method: 'POST',
          body: data
        })
        this.categories.push(response.data)
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка создания категории'
        throw e
      }
    },

    async updateCategory(id: string, data: Partial<Category>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/menu/categories/${id}`, {
          method: 'PUT',
          body: data
        })
        const index = this.categories.findIndex(c => c.id === id)
        if (index !== -1) {
          this.categories[index] = response.data
        }
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка обновления категории'
        throw e
      }
    },

    async deleteCategory(id: string) {
      const { $api } = useNuxtApp()

      try {
        await $api(`/menu/categories/${id}`, { method: 'DELETE' })
        this.categories = this.categories.filter(c => c.id !== id)
      } catch (e: any) {
        this.error = e.message || 'Ошибка удаления категории'
        throw e
      }
    },

    async createProduct(data: Partial<Product>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api('/menu/products', {
          method: 'POST',
          body: data
        })
        this.products.push(response.data)
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка создания продукта'
        throw e
      }
    },

    async updateProduct(id: string, data: Partial<Product>) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/menu/products/${id}`, {
          method: 'PUT',
          body: data
        })
        const index = this.products.findIndex(p => p.id === id)
        if (index !== -1) {
          this.products[index] = response.data
        }
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка обновления продукта'
        throw e
      }
    },

    async deleteProduct(id: string) {
      const { $api } = useNuxtApp()

      try {
        await $api(`/menu/products/${id}`, { method: 'DELETE' })
        this.products = this.products.filter(p => p.id !== id)
      } catch (e: any) {
        this.error = e.message || 'Ошибка удаления продукта'
        throw e
      }
    },

    async updateProductPrice(id: string, price: number) {
      const { $api } = useNuxtApp()

      try {
        const response = await $api(`/menu/products/${id}/price`, {
          method: 'PATCH',
          body: { price }
        })
        const index = this.products.findIndex(p => p.id === id)
        if (index !== -1) {
          this.products[index] = response.data
        }
        return response.data
      } catch (e: any) {
        this.error = e.message || 'Ошибка обновления цены'
        throw e
      }
    },

    setCurrentCategory(category: Category | null) {
      this.currentCategory = category
    },

    clearError() {
      this.error = null
    }
  }
})
