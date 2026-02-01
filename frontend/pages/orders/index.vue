<template>
  <div class="orders-page">
    <div class="page-header">
      <h1>Заказы</h1>
      <Button
        label="Новый заказ"
        icon="pi pi-plus"
        @click="$router.push('/pos')"
      />
    </div>

    <Card class="orders-card">
      <template #content>
        <div class="table-toolbar">
          <div class="filters">
            <Calendar
              v-model="dateRange"
              selectionMode="range"
              dateFormat="dd.mm.yy"
              placeholder="Выберите период"
              showIcon
              class="date-filter"
            />
            <Dropdown
              v-model="selectedStatus"
              :options="statuses"
              optionLabel="label"
              optionValue="value"
              placeholder="Все статусы"
              showClear
              class="status-filter"
            />
            <Dropdown
              v-model="selectedType"
              :options="orderTypes"
              optionLabel="label"
              optionValue="value"
              placeholder="Все типы"
              showClear
              class="type-filter"
            />
          </div>
          <InputText
            v-model="searchQuery"
            placeholder="Поиск по номеру..."
            class="search-input"
          />
        </div>

        <DataTable
          :value="filteredOrders"
          :loading="loading"
          stripedRows
          paginator
          :rows="20"
          :rowsPerPageOptions="[10, 20, 50]"
          sortField="created_at"
          :sortOrder="-1"
        >
          <Column field="number" header="№ заказа" sortable style="width: 120px">
            <template #body="{ data }">
              <span class="order-number">#{{ data.number }}</span>
            </template>
          </Column>
          <Column field="created_at" header="Дата" sortable style="width: 150px">
            <template #body="{ data }">
              {{ formatDateTime(data.created_at) }}
            </template>
          </Column>
          <Column field="type" header="Тип" style="width: 120px">
            <template #body="{ data }">
              <Tag :value="getTypeLabel(data.type)" :severity="getTypeSeverity(data.type)" />
            </template>
          </Column>
          <Column header="Стол/Адрес" style="width: 150px">
            <template #body="{ data }">
              <span v-if="data.table">{{ data.table.name }}</span>
              <span v-else-if="data.delivery_address" class="text-muted">{{ data.delivery_address }}</span>
              <span v-else class="text-muted">—</span>
            </template>
          </Column>
          <Column field="items_count" header="Позиций" style="width: 100px" />
          <Column field="total" header="Сумма" sortable style="width: 130px">
            <template #body="{ data }">
              {{ formatPrice(data.total) }}
            </template>
          </Column>
          <Column field="status" header="Статус" style="width: 140px">
            <template #body="{ data }">
              <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>
          <Column field="payment_status" header="Оплата" style="width: 120px">
            <template #body="{ data }">
              <Tag
                :value="data.payment_status === 'paid' ? 'Оплачен' : 'Не оплачен'"
                :severity="data.payment_status === 'paid' ? 'success' : 'warning'"
              />
            </template>
          </Column>
          <Column header="Действия" style="width: 120px">
            <template #body="{ data }">
              <Button
                icon="pi pi-eye"
                class="p-button-text p-button-sm"
                @click="viewOrder(data)"
                v-tooltip="'Просмотр'"
              />
              <Button
                v-if="data.status !== 'closed' && data.status !== 'cancelled'"
                icon="pi pi-times"
                class="p-button-text p-button-danger p-button-sm"
                @click="confirmCancel(data)"
                v-tooltip="'Отменить'"
              />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Order Details Dialog -->
    <Dialog
      v-model:visible="showOrderDialog"
      :header="`Заказ #${selectedOrder?.number || ''}`"
      :modal="true"
      style="width: 700px"
    >
      <div v-if="selectedOrder" class="order-details">
        <div class="order-header-info">
          <div class="info-row">
            <span class="label">Дата:</span>
            <span class="value">{{ formatDateTime(selectedOrder.created_at) }}</span>
          </div>
          <div class="info-row">
            <span class="label">Тип:</span>
            <Tag :value="getTypeLabel(selectedOrder.type)" :severity="getTypeSeverity(selectedOrder.type)" />
          </div>
          <div class="info-row">
            <span class="label">Статус:</span>
            <Tag :value="getStatusLabel(selectedOrder.status)" :severity="getStatusSeverity(selectedOrder.status)" />
          </div>
          <div class="info-row" v-if="selectedOrder.table">
            <span class="label">Стол:</span>
            <span class="value">{{ selectedOrder.table.name }}</span>
          </div>
          <div class="info-row" v-if="selectedOrder.waiter">
            <span class="label">Официант:</span>
            <span class="value">{{ selectedOrder.waiter.name }}</span>
          </div>
        </div>

        <Divider />

        <h4>Позиции заказа</h4>
        <DataTable :value="selectedOrder.items" class="order-items-table">
          <Column field="product.name" header="Товар" />
          <Column field="quantity" header="Кол-во" style="width: 80px" />
          <Column field="price" header="Цена" style="width: 100px">
            <template #body="{ data }">
              {{ formatPrice(data.price) }}
            </template>
          </Column>
          <Column header="Сумма" style="width: 100px">
            <template #body="{ data }">
              {{ formatPrice(data.quantity * data.price) }}
            </template>
          </Column>
        </DataTable>

        <div class="order-totals">
          <div class="total-row">
            <span>Подитог:</span>
            <span>{{ formatPrice(selectedOrder.subtotal) }}</span>
          </div>
          <div class="total-row" v-if="selectedOrder.discount_amount > 0">
            <span>Скидка:</span>
            <span class="text-success">-{{ formatPrice(selectedOrder.discount_amount) }}</span>
          </div>
          <div class="total-row total-final">
            <span>Итого:</span>
            <span>{{ formatPrice(selectedOrder.total) }}</span>
          </div>
        </div>
      </div>
    </Dialog>

    <ConfirmDialog />
  </div>
</template>

<script setup>
import { useConfirm } from 'primevue/useconfirm'

definePageMeta({
  layout: 'default'
})

const confirm = useConfirm()
const { $api } = useNuxtApp()

const orders = ref([])
const loading = ref(false)
const searchQuery = ref('')
const dateRange = ref(null)
const selectedStatus = ref(null)
const selectedType = ref(null)
const showOrderDialog = ref(false)
const selectedOrder = ref(null)

const statuses = [
  { label: 'Новый', value: 'new' },
  { label: 'Готовится', value: 'preparing' },
  { label: 'Готов', value: 'ready' },
  { label: 'Закрыт', value: 'closed' },
  { label: 'Отменён', value: 'cancelled' }
]

const orderTypes = [
  { label: 'В зале', value: 'dine_in' },
  { label: 'На вынос', value: 'takeaway' },
  { label: 'Доставка', value: 'delivery' }
]

const filteredOrders = computed(() => {
  let result = orders.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(o => o.number.toString().includes(query))
  }

  if (selectedStatus.value) {
    result = result.filter(o => o.status === selectedStatus.value)
  }

  if (selectedType.value) {
    result = result.filter(o => o.type === selectedType.value)
  }

  if (dateRange.value && dateRange.value[0]) {
    const startDate = new Date(dateRange.value[0]).setHours(0, 0, 0, 0)
    const endDate = dateRange.value[1]
      ? new Date(dateRange.value[1]).setHours(23, 59, 59, 999)
      : new Date(dateRange.value[0]).setHours(23, 59, 59, 999)

    result = result.filter(o => {
      const orderDate = new Date(o.created_at).getTime()
      return orderDate >= startDate && orderDate <= endDate
    })
  }

  return result
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0
  }).format(price || 0)
}

const formatDateTime = (date) => {
  if (!date) return null
  return new Date(date).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusLabel = (status) => {
  const found = statuses.find(s => s.value === status)
  return found?.label || status
}

const getStatusSeverity = (status) => {
  const severities = {
    new: 'info',
    preparing: 'warning',
    ready: 'success',
    closed: 'secondary',
    cancelled: 'danger'
  }
  return severities[status] || 'info'
}

const getTypeLabel = (type) => {
  const found = orderTypes.find(t => t.value === type)
  return found?.label || type
}

const getTypeSeverity = (type) => {
  const severities = {
    dine_in: 'primary',
    takeaway: 'warning',
    delivery: 'info'
  }
  return severities[type] || 'info'
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const response = await $api('/orders')
    orders.value = response.data
  } catch (e) {
    console.error('Failed to fetch orders:', e)
  } finally {
    loading.value = false
  }
}

const viewOrder = async (order) => {
  try {
    const response = await $api(`/orders/${order.id}`)
    selectedOrder.value = response.data
    showOrderDialog.value = true
  } catch (e) {
    console.error('Failed to fetch order details:', e)
  }
}

const confirmCancel = (order) => {
  confirm.require({
    message: `Отменить заказ #${order.number}?`,
    header: 'Подтверждение',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      try {
        await $api(`/orders/${order.id}/cancel`, { method: 'POST' })
        await fetchOrders()
      } catch (e) {
        console.error('Failed to cancel order:', e)
      }
    }
  })
}

onMounted(() => {
  fetchOrders()
})
</script>

<style lang="scss" scoped>
.orders-page {
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
}

.orders-card {
  background: white;
  border-radius: 8px;
}

.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.5rem;

  .filters {
    display: flex;
    gap: 0.5rem;
  }

  .date-filter {
    width: 250px;
  }

  .status-filter,
  .type-filter {
    width: 150px;
  }

  .search-input {
    width: 200px;
  }
}

.order-number {
  font-weight: 600;
  color: #3b82f6;
}

.order-details {
  h4 {
    margin: 0 0 1rem;
  }
}

.order-header-info {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;

  .label {
    color: #6c757d;
    font-size: 0.9rem;
  }

  .value {
    font-weight: 500;
  }
}

.order-items-table {
  margin-bottom: 1rem;
}

.order-totals {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 4px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.25rem 0;

  &.total-final {
    border-top: 1px solid #dee2e6;
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    font-weight: 700;
    font-size: 1.1rem;
  }
}

.text-success {
  color: #22c55e;
}

.text-muted {
  color: #6c757d;
}
</style>
