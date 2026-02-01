<template>
  <div class="staff-page">
    <div class="page-header">
      <h1>Персонал</h1>
      <Button
        label="Добавить сотрудника"
        icon="pi pi-plus"
        @click="showEmployeeDialog = true"
      />
    </div>

    <Card class="staff-card">
      <template #content>
        <div class="table-toolbar">
          <InputText
            v-model="searchQuery"
            placeholder="Поиск по имени..."
            class="search-input"
          />
          <div class="filters">
            <Dropdown
              v-model="selectedRole"
              :options="roles"
              optionLabel="label"
              optionValue="value"
              placeholder="Все роли"
              showClear
              class="role-filter"
            />
            <SelectButton
              v-model="activeFilter"
              :options="activeOptions"
              optionLabel="label"
              optionValue="value"
            />
          </div>
        </div>

        <DataTable
          :value="filteredEmployees"
          :loading="loading"
          stripedRows
          paginator
          :rows="20"
        >
          <Column header="Сотрудник" style="min-width: 250px">
            <template #body="{ data }">
              <div class="employee-cell">
                <Avatar :label="getInitials(data.name)" shape="circle" />
                <div class="employee-info">
                  <span class="employee-name">{{ data.name }}</span>
                  <span class="employee-position">{{ data.position || '—' }}</span>
                </div>
              </div>
            </template>
          </Column>
          <Column field="phone" header="Телефон" style="width: 150px" />
          <Column field="role" header="Роль" style="width: 150px">
            <template #body="{ data }">
              <Tag :value="getRoleLabel(data.role)" :severity="getRoleSeverity(data.role)" />
            </template>
          </Column>
          <Column header="Статус" style="width: 120px">
            <template #body="{ data }">
              <Tag
                :value="data.is_active ? 'Активен' : 'Неактивен'"
                :severity="data.is_active ? 'success' : 'danger'"
              />
            </template>
          </Column>
          <Column header="Смена" style="width: 150px">
            <template #body="{ data }">
              <Tag
                v-if="data.current_shift"
                value="На смене"
                severity="info"
              />
              <span v-else class="text-muted">Не на смене</span>
            </template>
          </Column>
          <Column header="Действия" style="width: 150px">
            <template #body="{ data }">
              <Button
                icon="pi pi-clock"
                class="p-button-text p-button-sm"
                @click="viewShifts(data)"
                v-tooltip="'История смен'"
              />
              <Button
                icon="pi pi-pencil"
                class="p-button-text p-button-sm"
                @click="editEmployee(data)"
                v-tooltip="'Редактировать'"
              />
              <Button
                icon="pi pi-trash"
                class="p-button-text p-button-danger p-button-sm"
                @click="confirmDelete(data)"
                v-tooltip="'Удалить'"
              />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Employee Dialog -->
    <Dialog
      v-model:visible="showEmployeeDialog"
      :header="editingEmployee ? 'Редактировать сотрудника' : 'Новый сотрудник'"
      :modal="true"
      style="width: 600px"
    >
      <div class="dialog-content">
        <div class="grid">
          <div class="col-12 md:col-6">
            <div class="field">
              <label>ФИО *</label>
              <InputText v-model="employeeForm.name" class="w-full" />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Телефон *</label>
              <InputMask v-model="employeeForm.phone" mask="+999 99 999-99-99" class="w-full" />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Email</label>
              <InputText v-model="employeeForm.email" type="email" class="w-full" />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Должность</label>
              <InputText v-model="employeeForm.position" class="w-full" />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Роль *</label>
              <Dropdown
                v-model="employeeForm.role"
                :options="roles"
                optionLabel="label"
                optionValue="value"
                placeholder="Выберите роль"
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>PIN-код</label>
              <Password
                v-model="employeeForm.pin"
                :feedback="false"
                toggleMask
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Зарплата (фикс)</label>
              <InputNumber
                v-model="employeeForm.salary_fixed"
                mode="currency"
                currency="UZS"
                locale="ru-RU"
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12 md:col-6">
            <div class="field">
              <label>Почасовая ставка</label>
              <InputNumber
                v-model="employeeForm.salary_hourly"
                mode="currency"
                currency="UZS"
                locale="ru-RU"
                class="w-full"
              />
            </div>
          </div>
          <div class="col-12">
            <div class="field">
              <label>Дата приёма</label>
              <Calendar v-model="employeeForm.hired_at" dateFormat="dd.mm.yy" class="w-full" />
            </div>
          </div>
          <div class="col-6">
            <div class="field">
              <label class="flex align-items-center gap-2">
                <Checkbox v-model="employeeForm.is_active" :binary="true" />
                Активен
              </label>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Отмена" class="p-button-text" @click="closeEmployeeDialog" />
        <Button label="Сохранить" @click="saveEmployee" />
      </template>
    </Dialog>

    <!-- Shifts Dialog -->
    <Dialog
      v-model:visible="showShiftsDialog"
      :header="`Смены: ${selectedEmployee?.name || ''}`"
      :modal="true"
      style="width: 700px"
    >
      <DataTable :value="employeeShifts" :loading="loadingShifts">
        <Column field="started_at" header="Начало">
          <template #body="{ data }">
            {{ formatDateTime(data.started_at) }}
          </template>
        </Column>
        <Column field="ended_at" header="Окончание">
          <template #body="{ data }">
            {{ data.ended_at ? formatDateTime(data.ended_at) : 'В процессе' }}
          </template>
        </Column>
        <Column header="Длительность">
          <template #body="{ data }">
            {{ calculateDuration(data.started_at, data.ended_at) }}
          </template>
        </Column>
        <Column field="orders_count" header="Заказов" style="width: 100px" />
        <Column field="total_tips" header="Чаевые" style="width: 120px">
          <template #body="{ data }">
            {{ formatPrice(data.total_tips) }}
          </template>
        </Column>
      </DataTable>
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

const employees = ref([])
const loading = ref(false)
const searchQuery = ref('')
const selectedRole = ref(null)
const activeFilter = ref('all')
const showEmployeeDialog = ref(false)
const showShiftsDialog = ref(false)
const editingEmployee = ref(null)
const selectedEmployee = ref(null)
const employeeShifts = ref([])
const loadingShifts = ref(false)

const roles = [
  { label: 'Администратор', value: 'admin' },
  { label: 'Менеджер', value: 'manager' },
  { label: 'Кассир', value: 'cashier' },
  { label: 'Официант', value: 'waiter' },
  { label: 'Повар', value: 'cook' },
  { label: 'Курьер', value: 'courier' }
]

const activeOptions = [
  { label: 'Все', value: 'all' },
  { label: 'Активные', value: 'active' },
  { label: 'Неактивные', value: 'inactive' }
]

const employeeForm = ref({
  name: '',
  phone: '',
  email: '',
  position: '',
  role: null,
  pin: '',
  salary_fixed: null,
  salary_hourly: null,
  hired_at: null,
  is_active: true
})

const filteredEmployees = computed(() => {
  let result = employees.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(e => e.name.toLowerCase().includes(query))
  }

  if (selectedRole.value) {
    result = result.filter(e => e.role === selectedRole.value)
  }

  if (activeFilter.value === 'active') {
    result = result.filter(e => e.is_active)
  } else if (activeFilter.value === 'inactive') {
    result = result.filter(e => !e.is_active)
  }

  return result
})

const getInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getRoleLabel = (role) => {
  const found = roles.find(r => r.value === role)
  return found?.label || role
}

const getRoleSeverity = (role) => {
  const severities = {
    admin: 'danger',
    manager: 'warning',
    cashier: 'info',
    waiter: 'success',
    cook: 'secondary',
    courier: 'primary'
  }
  return severities[role] || 'info'
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0
  }).format(price || 0)
}

const formatDateTime = (date) => {
  if (!date) return null
  return new Date(date).toLocaleString('ru-RU')
}

const calculateDuration = (start, end) => {
  const startDate = new Date(start)
  const endDate = end ? new Date(end) : new Date()
  const diff = endDate - startDate
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  return `${hours}ч ${minutes}м`
}

const fetchEmployees = async () => {
  loading.value = true
  try {
    const response = await $api('/employees')
    employees.value = response.data
  } catch (e) {
    console.error('Failed to fetch employees:', e)
  } finally {
    loading.value = false
  }
}

const editEmployee = (employee) => {
  editingEmployee.value = employee
  employeeForm.value = {
    ...employee,
    hired_at: employee.hired_at ? new Date(employee.hired_at) : null,
    pin: ''
  }
  showEmployeeDialog.value = true
}

const closeEmployeeDialog = () => {
  showEmployeeDialog.value = false
  editingEmployee.value = null
  employeeForm.value = {
    name: '',
    phone: '',
    email: '',
    position: '',
    role: null,
    pin: '',
    salary_fixed: null,
    salary_hourly: null,
    hired_at: null,
    is_active: true
  }
}

const saveEmployee = async () => {
  try {
    const data = { ...employeeForm.value }
    if (data.hired_at) {
      data.hired_at = data.hired_at.toISOString().split('T')[0]
    }
    if (!data.pin) {
      delete data.pin
    }

    if (editingEmployee.value) {
      await $api(`/employees/${editingEmployee.value.id}`, {
        method: 'PUT',
        body: data
      })
    } else {
      await $api('/employees', {
        method: 'POST',
        body: data
      })
    }

    await fetchEmployees()
    closeEmployeeDialog()
  } catch (e) {
    console.error('Failed to save employee:', e)
  }
}

const confirmDelete = (employee) => {
  confirm.require({
    message: `Удалить сотрудника "${employee.name}"?`,
    header: 'Подтверждение',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      try {
        await $api(`/employees/${employee.id}`, { method: 'DELETE' })
        await fetchEmployees()
      } catch (e) {
        console.error('Failed to delete employee:', e)
      }
    }
  })
}

const viewShifts = async (employee) => {
  selectedEmployee.value = employee
  showShiftsDialog.value = true
  loadingShifts.value = true

  try {
    const response = await $api('/staff/shifts', {
      params: { employee_id: employee.id }
    })
    employeeShifts.value = response.data
  } catch (e) {
    console.error('Failed to fetch shifts:', e)
  } finally {
    loadingShifts.value = false
  }
}

onMounted(() => {
  fetchEmployees()
})
</script>

<style lang="scss" scoped>
.staff-page {
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

.staff-card {
  background: white;
  border-radius: 8px;
}

.table-toolbar {
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

    .role-filter {
      width: 180px;
    }
  }
}

.employee-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.employee-info {
  display: flex;
  flex-direction: column;
}

.employee-name {
  font-weight: 500;
}

.employee-position {
  font-size: 0.85rem;
  color: #6c757d;
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

.text-muted {
  color: #6c757d;
}
</style>
