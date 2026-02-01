<template>
  <div class="customers-page">
    <div class="page-header">
      <h1>{{ $t('customers.title') || 'Клиенты' }}</h1>
      <Button
        :label="$t('customers.add') || 'Добавить клиента'"
        icon="pi pi-plus"
        @click="showCustomerDialog = true"
      />
    </div>

    <Card class="customers-card">
      <template #content>
        <div class="table-header">
          <InputText
            v-model="searchQuery"
            :placeholder="$t('common.search') || 'Поиск по имени или телефону...'"
            class="search-input"
            @input="debouncedSearch"
          />
          <div class="filters">
            <Dropdown
              v-model="selectedGroup"
              :options="customerStore.groups"
              optionLabel="name"
              optionValue="id"
              placeholder="Все группы"
              showClear
              class="group-filter"
            />
          </div>
        </div>

        <DataTable
          :value="filteredCustomers"
          :loading="customerStore.loading"
          stripedRows
          paginator
          :rows="20"
          :rowsPerPageOptions="[10, 20, 50]"
        >
          <Column field="name" :header="$t('common.name') || 'Имя'" sortable />
          <Column field="phone" :header="$t('customers.phone') || 'Телефон'" sortable />
          <Column field="bonus_balance" :header="$t('customers.bonus') || 'Бонусы'" sortable style="width: 120px">
            <template #body="{ data }">
              <Tag :value="formatNumber(data.bonus_balance)" severity="success" />
            </template>
          </Column>
          <Column field="total_orders" :header="$t('customers.orders') || 'Заказов'" sortable style="width: 100px" />
          <Column field="total_spent" :header="$t('customers.spent') || 'Потрачено'" sortable style="width: 150px">
            <template #body="{ data }">
              {{ formatPrice(data.total_spent) }}
            </template>
          </Column>
          <Column field="customer_group" header="Группа" style="width: 150px">
            <template #body="{ data }">
              <Tag v-if="data.customer_group" :value="data.customer_group.name" />
              <span v-else class="text-muted">—</span>
            </template>
          </Column>
          <Column :header="$t('common.actions') || 'Действия'" style="width: 150px">
            <template #body="{ data }">
              <Button
                icon="pi pi-eye"
                class="p-button-text p-button-sm"
                @click="viewCustomer(data)"
                v-tooltip="'Просмотр'"
              />
              <Button
                icon="pi pi-gift"
                class="p-button-text p-button-success p-button-sm"
                @click="openBonusDialog(data)"
                v-tooltip="'Начислить бонусы'"
              />
              <Button
                icon="pi pi-pencil"
                class="p-button-text p-button-sm"
                @click="editCustomer(data)"
                v-tooltip="'Редактировать'"
              />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Customer Dialog -->
    <Dialog
      v-model:visible="showCustomerDialog"
      :header="editingCustomer ? 'Редактировать клиента' : 'Новый клиент'"
      :modal="true"
      style="width: 500px"
    >
      <div class="dialog-content">
        <div class="field">
          <label>{{ $t('common.name') || 'Имя' }} *</label>
          <InputText v-model="customerForm.name" class="w-full" />
        </div>
        <div class="field">
          <label>{{ $t('customers.phone') || 'Телефон' }} *</label>
          <InputMask v-model="customerForm.phone" mask="+999 99 999-99-99" class="w-full" />
        </div>
        <div class="field">
          <label>Email</label>
          <InputText v-model="customerForm.email" type="email" class="w-full" />
        </div>
        <div class="field">
          <label>Дата рождения</label>
          <Calendar v-model="customerForm.birth_date" dateFormat="dd.mm.yy" class="w-full" />
        </div>
        <div class="field">
          <label>Группа клиента</label>
          <Dropdown
            v-model="customerForm.customer_group_id"
            :options="customerStore.groups"
            optionLabel="name"
            optionValue="id"
            placeholder="Выберите группу"
            showClear
            class="w-full"
          />
        </div>
        <div class="field">
          <label>Заметки</label>
          <Textarea v-model="customerForm.notes" rows="3" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Отмена" class="p-button-text" @click="closeCustomerDialog" />
        <Button label="Сохранить" @click="saveCustomer" />
      </template>
    </Dialog>

    <!-- Bonus Dialog -->
    <Dialog
      v-model:visible="showBonusDialog"
      header="Начислить бонусы"
      :modal="true"
      style="width: 400px"
    >
      <div class="dialog-content" v-if="selectedCustomer">
        <div class="customer-info mb-3">
          <strong>{{ selectedCustomer.name }}</strong>
          <div class="text-muted">Текущий баланс: {{ formatNumber(selectedCustomer.bonus_balance) }} бонусов</div>
        </div>
        <div class="field">
          <label>Сумма бонусов *</label>
          <InputNumber v-model="bonusForm.amount" class="w-full" />
        </div>
        <div class="field">
          <label>Причина *</label>
          <InputText v-model="bonusForm.description" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Отмена" class="p-button-text" @click="closeBonusDialog" />
        <Button label="Начислить" @click="addBonus" />
      </template>
    </Dialog>

    <!-- Customer View Dialog -->
    <Dialog
      v-model:visible="showViewDialog"
      :header="selectedCustomer?.name"
      :modal="true"
      style="width: 700px"
    >
      <div v-if="selectedCustomer" class="customer-view">
        <TabView>
          <TabPanel header="Информация">
            <div class="info-grid">
              <div class="info-item">
                <span class="label">Телефон</span>
                <span class="value">{{ selectedCustomer.phone }}</span>
              </div>
              <div class="info-item">
                <span class="label">Email</span>
                <span class="value">{{ selectedCustomer.email || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Дата рождения</span>
                <span class="value">{{ formatDate(selectedCustomer.birth_date) || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Группа</span>
                <span class="value">{{ selectedCustomer.customer_group?.name || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Бонусный баланс</span>
                <span class="value bonus">{{ formatNumber(selectedCustomer.bonus_balance) }}</span>
              </div>
              <div class="info-item">
                <span class="label">Всего заказов</span>
                <span class="value">{{ selectedCustomer.total_orders }}</span>
              </div>
              <div class="info-item">
                <span class="label">Общая сумма</span>
                <span class="value">{{ formatPrice(selectedCustomer.total_spent) }}</span>
              </div>
              <div class="info-item">
                <span class="label">Дата регистрации</span>
                <span class="value">{{ formatDate(selectedCustomer.created_at) }}</span>
              </div>
            </div>
          </TabPanel>
          <TabPanel header="История заказов">
            <DataTable :value="customerHistory" :loading="loadingHistory">
              <Column field="number" header="№ заказа" />
              <Column field="created_at" header="Дата">
                <template #body="{ data }">
                  {{ formatDateTime(data.created_at) }}
                </template>
              </Column>
              <Column field="total" header="Сумма">
                <template #body="{ data }">
                  {{ formatPrice(data.total) }}
                </template>
              </Column>
              <Column field="status" header="Статус">
                <template #body="{ data }">
                  <Tag :value="data.status" />
                </template>
              </Column>
            </DataTable>
          </TabPanel>
        </TabView>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { useDebounceFn } from '@vueuse/core'

definePageMeta({
  layout: 'default'
})

const customerStore = useCustomerStore()

const showCustomerDialog = ref(false)
const showBonusDialog = ref(false)
const showViewDialog = ref(false)
const editingCustomer = ref(null)
const selectedCustomer = ref(null)
const searchQuery = ref('')
const selectedGroup = ref(null)
const customerHistory = ref([])
const loadingHistory = ref(false)

const customerForm = ref({
  name: '',
  phone: '',
  email: '',
  birth_date: null,
  customer_group_id: null,
  notes: ''
})

const bonusForm = ref({
  amount: 0,
  description: ''
})

const filteredCustomers = computed(() => {
  let customers = customerStore.customers

  if (selectedGroup.value) {
    customers = customers.filter(c => c.customer_group_id === selectedGroup.value)
  }

  return customers
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0
  }).format(price || 0)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('ru-RU').format(num || 0)
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('ru-RU')
}

const formatDateTime = (date) => {
  if (!date) return null
  return new Date(date).toLocaleString('ru-RU')
}

const debouncedSearch = useDebounceFn(async () => {
  if (searchQuery.value.length >= 2) {
    await customerStore.searchCustomers(searchQuery.value)
  } else if (searchQuery.value.length === 0) {
    await customerStore.fetchCustomers()
  }
}, 300)

const viewCustomer = async (customer) => {
  selectedCustomer.value = customer
  showViewDialog.value = true
  loadingHistory.value = true

  try {
    const history = await customerStore.fetchCustomerHistory(customer.id)
    customerHistory.value = history
  } catch (e) {
    console.error('Failed to load history:', e)
  } finally {
    loadingHistory.value = false
  }
}

const editCustomer = (customer) => {
  editingCustomer.value = customer
  customerForm.value = {
    ...customer,
    birth_date: customer.birth_date ? new Date(customer.birth_date) : null
  }
  showCustomerDialog.value = true
}

const closeCustomerDialog = () => {
  showCustomerDialog.value = false
  editingCustomer.value = null
  customerForm.value = {
    name: '',
    phone: '',
    email: '',
    birth_date: null,
    customer_group_id: null,
    notes: ''
  }
}

const saveCustomer = async () => {
  try {
    const data = { ...customerForm.value }
    if (data.birth_date) {
      data.birth_date = data.birth_date.toISOString().split('T')[0]
    }

    if (editingCustomer.value) {
      await customerStore.updateCustomer(editingCustomer.value.id, data)
    } else {
      await customerStore.createCustomer(data)
    }
    closeCustomerDialog()
  } catch (e) {
    console.error('Failed to save customer:', e)
  }
}

const openBonusDialog = (customer) => {
  selectedCustomer.value = customer
  bonusForm.value = { amount: 0, description: '' }
  showBonusDialog.value = true
}

const closeBonusDialog = () => {
  showBonusDialog.value = false
  selectedCustomer.value = null
}

const addBonus = async () => {
  if (!selectedCustomer.value || !bonusForm.value.amount) return

  try {
    await customerStore.addBonus(
      selectedCustomer.value.id,
      bonusForm.value.amount,
      bonusForm.value.description
    )
    closeBonusDialog()
  } catch (e) {
    console.error('Failed to add bonus:', e)
  }
}

onMounted(async () => {
  await Promise.all([
    customerStore.fetchCustomers(),
    customerStore.fetchGroups()
  ])
})
</script>

<style lang="scss" scoped>
.customers-page {
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

.customers-card {
  background: white;
  border-radius: 8px;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;

  .search-input {
    width: 300px;
  }

  .filters {
    display: flex;
    gap: 0.5rem;

    .group-filter {
      width: 200px;
    }
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

.customer-info {
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.info-item {
  .label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
  }

  .value {
    font-weight: 500;

    &.bonus {
      color: #22c55e;
      font-size: 1.2rem;
    }
  }
}

.text-muted {
  color: #6c757d;
}
</style>
