<template>
  <div class="menu-page">
    <div class="page-header">
      <h1>{{ $t('menu.title') || 'Меню' }}</h1>
      <div class="header-actions">
        <Button
          :label="$t('menu.add_category') || 'Добавить категорию'"
          icon="pi pi-folder-plus"
          class="p-button-outlined"
          @click="showCategoryDialog = true"
        />
        <Button
          :label="$t('menu.add_product') || 'Добавить товар'"
          icon="pi pi-plus"
          @click="showProductDialog = true"
        />
      </div>
    </div>

    <div class="menu-content">
      <div class="categories-panel">
        <div class="panel-header">
          <h3>{{ $t('menu.categories') || 'Категории' }}</h3>
        </div>
        <Tree
          v-model:selectionKeys="selectedCategoryKey"
          :value="categoryTree"
          selectionMode="single"
          class="category-tree"
          @node-select="onCategorySelect"
        >
          <template #default="{ node }">
            <div class="category-node">
              <span>{{ node.label }}</span>
              <div class="node-actions">
                <Button
                  icon="pi pi-pencil"
                  class="p-button-text p-button-sm"
                  @click.stop="editCategory(node.data)"
                />
                <Button
                  icon="pi pi-trash"
                  class="p-button-text p-button-danger p-button-sm"
                  @click.stop="confirmDeleteCategory(node.data)"
                />
              </div>
            </div>
          </template>
        </Tree>
      </div>

      <div class="products-panel">
        <div class="panel-header">
          <h3>{{ $t('menu.products') || 'Товары' }}</h3>
          <InputText
            v-model="searchQuery"
            :placeholder="$t('common.search') || 'Поиск...'"
            class="search-input"
          />
        </div>

        <DataTable
          :value="filteredProducts"
          :loading="menuStore.loading"
          stripedRows
          class="products-table"
        >
          <Column field="name" :header="$t('common.name') || 'Название'" sortable />
          <Column field="sku" header="SKU" sortable style="width: 120px" />
          <Column field="price" :header="$t('menu.price') || 'Цена'" sortable style="width: 120px">
            <template #body="{ data }">
              {{ formatPrice(data.price) }}
            </template>
          </Column>
          <Column field="is_available" :header="$t('menu.available') || 'Доступен'" style="width: 100px">
            <template #body="{ data }">
              <Tag
                :value="data.is_available ? 'Да' : 'Нет'"
                :severity="data.is_available ? 'success' : 'danger'"
              />
            </template>
          </Column>
          <Column :header="$t('common.actions') || 'Действия'" style="width: 120px">
            <template #body="{ data }">
              <Button
                icon="pi pi-pencil"
                class="p-button-text p-button-sm"
                @click="editProduct(data)"
              />
              <Button
                icon="pi pi-trash"
                class="p-button-text p-button-danger p-button-sm"
                @click="confirmDeleteProduct(data)"
              />
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <!-- Category Dialog -->
    <Dialog
      v-model:visible="showCategoryDialog"
      :header="editingCategory ? 'Редактировать категорию' : 'Новая категория'"
      :modal="true"
      style="width: 400px"
    >
      <div class="dialog-content">
        <div class="field">
          <label>{{ $t('common.name') || 'Название' }}</label>
          <InputText v-model="categoryForm.name" class="w-full" />
        </div>
        <div class="field">
          <label>Родительская категория</label>
          <TreeSelect
            v-model="categoryForm.parent_id"
            :options="categoryTree"
            placeholder="Без родителя"
            class="w-full"
          />
        </div>
        <div class="field">
          <label class="flex align-items-center gap-2">
            <Checkbox v-model="categoryForm.is_active" :binary="true" />
            Активна
          </label>
        </div>
      </div>
      <template #footer>
        <Button label="Отмена" class="p-button-text" @click="closeCategoryDialog" />
        <Button label="Сохранить" @click="saveCategory" />
      </template>
    </Dialog>

    <!-- Product Dialog -->
    <Dialog
      v-model:visible="showProductDialog"
      :header="editingProduct ? 'Редактировать товар' : 'Новый товар'"
      :modal="true"
      style="width: 600px"
    >
      <div class="dialog-content">
        <div class="grid">
          <div class="col-12 md:col-6">
            <div class="field">
              <label>{{ $t('common.name') || 'Название' }} *</label>
              <InputText v-model="productForm.name" class="w-full" />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>SKU</label>
              <InputText v-model="productForm.sku" class="w-full" />
            </div>
          </div>
          <div class="col-12">
            <div class="field">
              <label>Категория *</label>
              <TreeSelect
                v-model="productForm.category_id"
                :options="categoryTree"
                placeholder="Выберите категорию"
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>{{ $t('menu.price') || 'Цена' }} *</label>
              <InputNumber
                v-model="productForm.price"
                mode="currency"
                currency="UZS"
                locale="ru-RU"
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Себестоимость</label>
              <InputNumber
                v-model="productForm.cost_price"
                mode="currency"
                currency="UZS"
                locale="ru-RU"
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12">
            <div class="field">
              <label>Описание</label>
              <Textarea v-model="productForm.description" rows="3" class="w-full" />
            </div>
          </div>
          <div class="col-6">
            <div class="field">
              <label class="flex align-items-center gap-2">
                <Checkbox v-model="productForm.is_active" :binary="true" />
                Активен
              </label>
            </div>
          </div>
          <div class="col-6">
            <div class="field">
              <label class="flex align-items-center gap-2">
                <Checkbox v-model="productForm.is_available" :binary="true" />
                Доступен для продажи
              </label>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Отмена" class="p-button-text" @click="closeProductDialog" />
        <Button label="Сохранить" @click="saveProduct" />
      </template>
    </Dialog>

    <ConfirmDialog />
  </div>
</template>

<script setup>
import { useConfirm } from 'primevue/useconfirm'

definePageMeta({
  layout: 'default'
})

const { t } = useI18n()
const confirm = useConfirm()
const menuStore = useMenuStore()

const showCategoryDialog = ref(false)
const showProductDialog = ref(false)
const editingCategory = ref(null)
const editingProduct = ref(null)
const selectedCategoryKey = ref(null)
const searchQuery = ref('')

const categoryForm = ref({
  name: '',
  parent_id: null,
  is_active: true
})

const productForm = ref({
  name: '',
  sku: '',
  category_id: null,
  price: 0,
  cost_price: null,
  description: '',
  is_active: true,
  is_available: true
})

const categoryTree = computed(() => {
  const buildTree = (categories, parentId = null) => {
    return categories
      .filter(c => c.parent_id === parentId)
      .map(c => ({
        key: c.id,
        label: c.name,
        data: c,
        children: buildTree(categories, c.id)
      }))
  }
  return buildTree(menuStore.categories)
})

const filteredProducts = computed(() => {
  let products = menuStore.products

  if (selectedCategoryKey.value) {
    const categoryId = Object.keys(selectedCategoryKey.value)[0]
    products = products.filter(p => p.category_id === categoryId)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    products = products.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.sku?.toLowerCase().includes(query)
    )
  }

  return products
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0
  }).format(price)
}

const onCategorySelect = (node) => {
  menuStore.setCurrentCategory(node.data)
}

const editCategory = (category) => {
  editingCategory.value = category
  categoryForm.value = { ...category }
  showCategoryDialog.value = true
}

const closeCategoryDialog = () => {
  showCategoryDialog.value = false
  editingCategory.value = null
  categoryForm.value = { name: '', parent_id: null, is_active: true }
}

const saveCategory = async () => {
  try {
    if (editingCategory.value) {
      await menuStore.updateCategory(editingCategory.value.id, categoryForm.value)
    } else {
      await menuStore.createCategory(categoryForm.value)
    }
    closeCategoryDialog()
  } catch (e) {
    console.error('Failed to save category:', e)
  }
}

const confirmDeleteCategory = (category) => {
  confirm.require({
    message: `Удалить категорию "${category.name}"?`,
    header: 'Подтверждение',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      await menuStore.deleteCategory(category.id)
    }
  })
}

const editProduct = (product) => {
  editingProduct.value = product
  productForm.value = { ...product }
  showProductDialog.value = true
}

const closeProductDialog = () => {
  showProductDialog.value = false
  editingProduct.value = null
  productForm.value = {
    name: '',
    sku: '',
    category_id: null,
    price: 0,
    cost_price: null,
    description: '',
    is_active: true,
    is_available: true
  }
}

const saveProduct = async () => {
  try {
    if (editingProduct.value) {
      await menuStore.updateProduct(editingProduct.value.id, productForm.value)
    } else {
      await menuStore.createProduct(productForm.value)
    }
    closeProductDialog()
  } catch (e) {
    console.error('Failed to save product:', e)
  }
}

const confirmDeleteProduct = (product) => {
  confirm.require({
    message: `Удалить товар "${product.name}"?`,
    header: 'Подтверждение',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      await menuStore.deleteProduct(product.id)
    }
  })
}

onMounted(async () => {
  await Promise.all([
    menuStore.fetchCategories(),
    menuStore.fetchProducts()
  ])
})
</script>

<style lang="scss" scoped>
.menu-page {
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;

  h1 {
    margin: 0;
    font-size: 1.5rem;
  }

  .header-actions {
    display: flex;
    gap: 0.5rem;
  }
}

.menu-content {
  display: flex;
  gap: 1.5rem;
}

.categories-panel {
  width: 300px;
  flex-shrink: 0;
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.products-panel {
  flex: 1;
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;

  h3 {
    margin: 0;
  }

  .search-input {
    width: 250px;
  }
}

.category-node {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;

  .node-actions {
    display: none;
  }

  &:hover .node-actions {
    display: flex;
  }
}

.dialog-content {
  .field {
    margin-bottom: 1rem;

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
  }
}

.grid {
  display: flex;
  flex-wrap: wrap;
  margin: -0.5rem;

  .col-12 { flex: 0 0 100%; max-width: 100%; padding: 0.5rem; }
  .col-6 { flex: 0 0 50%; max-width: 50%; padding: 0.5rem; }

  @media (min-width: 768px) {
    .md\:col-6 { flex: 0 0 50%; max-width: 50%; }
  }
}
</style>
