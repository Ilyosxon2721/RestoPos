<template>
  <div class="warehouse-page">
    <div class="page-header">
      <h1>Склад</h1>
      <div class="header-actions">
        <Button
          label="Новая поставка"
          icon="pi pi-truck"
          class="p-button-outlined"
          @click="$router.push('/warehouse/supplies/new')"
        />
        <Button
          label="Инвентаризация"
          icon="pi pi-list-check"
          @click="showAdjustDialog = true"
        />
      </div>
    </div>

    <div class="stats-row">
      <Card class="stat-card">
        <template #content>
          <div class="stat-content">
            <i class="pi pi-box stat-icon"></i>
            <div class="stat-info">
              <span class="stat-value">{{ warehouseStore.stocks.length }}</span>
              <span class="stat-label">Позиций на складе</span>
            </div>
          </div>
        </template>
      </Card>
      <Card class="stat-card warning" @click="activeTab = 'low-stock'">
        <template #content>
          <div class="stat-content">
            <i class="pi pi-exclamation-triangle stat-icon"></i>
            <div class="stat-info">
              <span class="stat-value">{{ warehouseStore.lowStockCount }}</span>
              <span class="stat-label">Заканчивается</span>
            </div>
          </div>
        </template>
      </Card>
      <Card class="stat-card info">
        <template #content>
          <div class="stat-content">
            <i class="pi pi-clock stat-icon"></i>
            <div class="stat-info">
              <span class="stat-value">{{ warehouseStore.pendingSupplies.length }}</span>
              <span class="stat-label">Ожидает поставок</span>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <Card class="main-card">
      <template #content>
        <TabView v-model:activeIndex="activeTabIndex">
          <TabPanel header="Остатки">
            <div class="table-toolbar">
              <InputText
                v-model="searchQuery"
                placeholder="Поиск по названию..."
                class="search-input"
              />
              <Dropdown
                v-model="selectedWarehouse"
                :options="warehouseStore.warehouses"
                optionLabel="name"
                optionValue="id"
                placeholder="Все склады"
                showClear
                class="warehouse-filter"
              />
            </div>

            <DataTable
              :value="filteredStocks"
              :loading="warehouseStore.loading"
              stripedRows
              paginator
              :rows="20"
            >
              <Column field="ingredient.name" header="Ингредиент" sortable />
              <Column field="ingredient.sku" header="SKU" style="width: 120px" />
              <Column field="quantity" header="Количество" sortable style="width: 150px">
                <template #body="{ data }">
                  <span :class="{ 'text-danger': data.quantity <= data.min_quantity }">
                    {{ data.quantity }} {{ data.unit }}
                  </span>
                </template>
              </Column>
              <Column field="min_quantity" header="Минимум" style="width: 120px">
                <template #body="{ data }">
                  {{ data.min_quantity }} {{ data.unit }}
                </template>
              </Column>
              <Column field="warehouse.name" header="Склад" style="width: 150px" />
              <Column header="Статус" style="width: 120px">
                <template #body="{ data }">
                  <Tag
                    v-if="data.quantity <= data.min_quantity"
                    value="Мало"
                    severity="danger"
                  />
                  <Tag v-else value="Норма" severity="success" />
                </template>
              </Column>
              <Column header="Действия" style="width: 100px">
                <template #body="{ data }">
                  <Button
                    icon="pi pi-pencil"
                    class="p-button-text p-button-sm"
                    @click="openAdjust(data)"
                    v-tooltip="'Корректировка'"
                  />
                </template>
              </Column>
            </DataTable>
          </TabPanel>

          <TabPanel header="Заканчивающиеся">
            <DataTable
              :value="warehouseStore.lowStockItems"
              :loading="warehouseStore.loading"
              stripedRows
            >
              <Column field="ingredient.name" header="Ингредиент" />
              <Column field="quantity" header="Остаток">
                <template #body="{ data }">
                  <span class="text-danger font-bold">
                    {{ data.quantity }} {{ data.unit }}
                  </span>
                </template>
              </Column>
              <Column field="min_quantity" header="Минимум">
                <template #body="{ data }">
                  {{ data.min_quantity }} {{ data.unit }}
                </template>
              </Column>
              <Column field="warehouse.name" header="Склад" />
              <Column header="Действия" style="width: 150px">
                <template #body="{ data }">
                  <Button
                    label="Заказать"
                    icon="pi pi-shopping-cart"
                    class="p-button-sm p-button-outlined"
                    @click="createSupplyFor(data)"
                  />
                </template>
              </Column>
            </DataTable>
          </TabPanel>

          <TabPanel header="Поставки">
            <div class="table-toolbar">
              <Dropdown
                v-model="supplyStatus"
                :options="supplyStatuses"
                optionLabel="label"
                optionValue="value"
                placeholder="Все статусы"
                showClear
                class="status-filter"
              />
            </div>

            <DataTable
              :value="filteredSupplies"
              :loading="warehouseStore.loading"
              stripedRows
              paginator
              :rows="20"
            >
              <Column field="number" header="Номер" style="width: 120px" />
              <Column field="supplier.name" header="Поставщик" />
              <Column field="total_amount" header="Сумма" style="width: 150px">
                <template #body="{ data }">
                  {{ formatPrice(data.total_amount) }}
                </template>
              </Column>
              <Column field="status" header="Статус" style="width: 130px">
                <template #body="{ data }">
                  <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                </template>
              </Column>
              <Column field="expected_at" header="Ожидается" style="width: 130px">
                <template #body="{ data }">
                  {{ formatDate(data.expected_at) || '—' }}
                </template>
              </Column>
              <Column header="Действия" style="width: 150px">
                <template #body="{ data }">
                  <Button
                    v-if="data.status === 'pending'"
                    icon="pi pi-check"
                    class="p-button-text p-button-success p-button-sm"
                    @click="receiveSupply(data)"
                    v-tooltip="'Принять'"
                  />
                  <Button
                    icon="pi pi-eye"
                    class="p-button-text p-button-sm"
                    @click="viewSupply(data)"
                    v-tooltip="'Просмотр'"
                  />
                </template>
              </Column>
            </DataTable>
          </TabPanel>
        </TabView>
      </template>
    </Card>

    <!-- Adjust Dialog -->
    <Dialog
      v-model:visible="showAdjustDialog"
      header="Корректировка остатков"
      :modal="true"
      style="width: 450px"
    >
      <div class="dialog-content">
        <div class="field" v-if="!adjustingStock">
          <label>Ингредиент</label>
          <Dropdown
            v-model="adjustForm.ingredient_id"
            :options="warehouseStore.ingredients"
            optionLabel="name"
            optionValue="id"
            placeholder="Выберите ингредиент"
            filter
            class="w-full"
          />
        </div>
        <div v-else class="mb-3">
          <strong>{{ adjustingStock.ingredient?.name }}</strong>
          <div class="text-muted">Текущий остаток: {{ adjustingStock.quantity }} {{ adjustingStock.unit }}</div>
        </div>
        <div class="field" v-if="!adjustingStock">
          <label>Склад</label>
          <Dropdown
            v-model="adjustForm.warehouse_id"
            :options="warehouseStore.warehouses"
            optionLabel="name"
            optionValue="id"
            placeholder="Выберите склад"
            class="w-full"
          />
        </div>
        <div class="field">
          <label>Новое количество</label>
          <InputNumber v-model="adjustForm.quantity" class="w-full" />
        </div>
        <div class="field">
          <label>Причина корректировки</label>
          <InputText v-model="adjustForm.reason" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Отмена" class="p-button-text" @click="closeAdjustDialog" />
        <Button label="Применить" @click="applyAdjustment" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default'
})

const warehouseStore = useWarehouseStore()
const router = useRouter()

const activeTabIndex = ref(0)
const searchQuery = ref('')
const selectedWarehouse = ref(null)
const supplyStatus = ref(null)
const showAdjustDialog = ref(false)
const adjustingStock = ref(null)

const supplyStatuses = [
  { label: 'Черновик', value: 'draft' },
  { label: 'Ожидается', value: 'pending' },
  { label: 'Получена', value: 'received' },
  { label: 'Отменена', value: 'cancelled' }
]

const adjustForm = ref({
  ingredient_id: null,
  warehouse_id: null,
  quantity: 0,
  reason: ''
})

const filteredStocks = computed(() => {
  let stocks = warehouseStore.stocks

  if (selectedWarehouse.value) {
    stocks = stocks.filter(s => s.warehouse_id === selectedWarehouse.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    stocks = stocks.filter(s =>
      s.ingredient?.name?.toLowerCase().includes(query) ||
      s.ingredient?.sku?.toLowerCase().includes(query)
    )
  }

  return stocks
})

const filteredSupplies = computed(() => {
  let supplies = warehouseStore.supplies

  if (supplyStatus.value) {
    supplies = supplies.filter(s => s.status === supplyStatus.value)
  }

  return supplies
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0
  }).format(price || 0)
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('ru-RU')
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Черновик',
    pending: 'Ожидается',
    received: 'Получена',
    cancelled: 'Отменена'
  }
  return labels[status] || status
}

const getStatusSeverity = (status) => {
  const severities = {
    draft: 'secondary',
    pending: 'warning',
    received: 'success',
    cancelled: 'danger'
  }
  return severities[status] || 'info'
}

const openAdjust = (stock) => {
  adjustingStock.value = stock
  adjustForm.value = {
    ingredient_id: stock.ingredient_id,
    warehouse_id: stock.warehouse_id,
    quantity: stock.quantity,
    reason: ''
  }
  showAdjustDialog.value = true
}

const closeAdjustDialog = () => {
  showAdjustDialog.value = false
  adjustingStock.value = null
  adjustForm.value = {
    ingredient_id: null,
    warehouse_id: null,
    quantity: 0,
    reason: ''
  }
}

const applyAdjustment = async () => {
  try {
    await warehouseStore.adjustStock(adjustForm.value)
    closeAdjustDialog()
  } catch (e) {
    console.error('Failed to adjust stock:', e)
  }
}

const receiveSupply = async (supply) => {
  try {
    await warehouseStore.receiveSupply(supply.id)
  } catch (e) {
    console.error('Failed to receive supply:', e)
  }
}

const viewSupply = (supply) => {
  router.push(`/warehouse/supplies/${supply.id}`)
}

const createSupplyFor = (stock) => {
  router.push({
    path: '/warehouse/supplies/new',
    query: { ingredient_id: stock.ingredient_id }
  })
}

onMounted(async () => {
  await Promise.all([
    warehouseStore.fetchStocks(),
    warehouseStore.fetchLowStock(),
    warehouseStore.fetchSupplies(),
    warehouseStore.fetchWarehouses(),
    warehouseStore.fetchIngredients()
  ])
})
</script>

<style lang="scss" scoped>
.warehouse-page {
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

.stats-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  flex: 1;
  cursor: pointer;
  transition: transform 0.2s;

  &:hover {
    transform: translateY(-2px);
  }

  &.warning {
    border-left: 4px solid #f97316;
  }

  &.info {
    border-left: 4px solid #3b82f6;
  }
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 2rem;
  color: #6c757d;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-label {
  color: #6c757d;
  font-size: 0.9rem;
}

.main-card {
  background: white;
  border-radius: 8px;
}

.table-toolbar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;

  .search-input {
    width: 300px;
  }

  .warehouse-filter,
  .status-filter {
    width: 200px;
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

.text-danger {
  color: #ef4444;
}

.text-muted {
  color: #6c757d;
}

.font-bold {
  font-weight: 700;
}
</style>
