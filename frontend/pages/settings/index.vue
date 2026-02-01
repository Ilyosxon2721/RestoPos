<template>
  <div class="settings-page">
    <div class="page-header">
      <h1>Настройки</h1>
    </div>

    <div class="settings-content">
      <div class="settings-menu">
        <div
          v-for="item in menuItems"
          :key="item.key"
          class="menu-item"
          :class="{ active: activeSection === item.key }"
          @click="activeSection = item.key"
        >
          <i :class="item.icon"></i>
          <span>{{ item.label }}</span>
        </div>
      </div>

      <Card class="settings-panel">
        <template #content>
          <!-- Organization Settings -->
          <div v-if="activeSection === 'organization'" class="section-content">
            <h3>Данные организации</h3>
            <div class="form-grid">
              <div class="field">
                <label>Название</label>
                <InputText v-model="orgSettings.name" class="w-full" />
              </div>
              <div class="field">
                <label>ИНН</label>
                <InputText v-model="orgSettings.inn" class="w-full" />
              </div>
              <div class="field">
                <label>Адрес</label>
                <InputText v-model="orgSettings.address" class="w-full" />
              </div>
              <div class="field">
                <label>Телефон</label>
                <InputText v-model="orgSettings.phone" class="w-full" />
              </div>
              <div class="field col-span-2">
                <label>Описание</label>
                <Textarea v-model="orgSettings.description" rows="3" class="w-full" />
              </div>
            </div>
            <Button label="Сохранить" @click="saveOrgSettings" />
          </div>

          <!-- Branch Settings -->
          <div v-if="activeSection === 'branches'" class="section-content">
            <div class="section-header">
              <h3>Филиалы</h3>
              <Button label="Добавить филиал" icon="pi pi-plus" @click="showBranchDialog = true" />
            </div>
            <DataTable :value="branches" stripedRows>
              <Column field="name" header="Название" />
              <Column field="address" header="Адрес" />
              <Column field="phone" header="Телефон" style="width: 150px" />
              <Column header="Статус" style="width: 100px">
                <template #body="{ data }">
                  <Tag :value="data.is_active ? 'Активен' : 'Неактивен'" :severity="data.is_active ? 'success' : 'danger'" />
                </template>
              </Column>
              <Column header="Действия" style="width: 100px">
                <template #body="{ data }">
                  <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editBranch(data)" />
                </template>
              </Column>
            </DataTable>
          </div>

          <!-- Receipt Settings -->
          <div v-if="activeSection === 'receipt'" class="section-content">
            <h3>Настройки чека</h3>
            <div class="form-grid">
              <div class="field col-span-2">
                <label>Заголовок чека</label>
                <InputText v-model="receiptSettings.header" class="w-full" />
              </div>
              <div class="field col-span-2">
                <label>Нижний текст</label>
                <Textarea v-model="receiptSettings.footer" rows="2" class="w-full" />
              </div>
              <div class="field">
                <label class="flex align-items-center gap-2">
                  <Checkbox v-model="receiptSettings.show_logo" :binary="true" />
                  Показывать логотип
                </label>
              </div>
              <div class="field">
                <label class="flex align-items-center gap-2">
                  <Checkbox v-model="receiptSettings.print_auto" :binary="true" />
                  Автоматическая печать
                </label>
              </div>
            </div>
            <Button label="Сохранить" @click="saveReceiptSettings" />
          </div>

          <!-- Payment Methods -->
          <div v-if="activeSection === 'payments'" class="section-content">
            <h3>Способы оплаты</h3>
            <div class="payment-methods">
              <div v-for="method in paymentMethods" :key="method.id" class="payment-method-item">
                <div class="method-info">
                  <i :class="method.icon"></i>
                  <span>{{ method.name }}</span>
                </div>
                <InputSwitch v-model="method.is_active" @change="togglePaymentMethod(method)" />
              </div>
            </div>
          </div>

          <!-- Printers -->
          <div v-if="activeSection === 'printers'" class="section-content">
            <div class="section-header">
              <h3>Принтеры</h3>
              <Button label="Добавить принтер" icon="pi pi-plus" @click="showPrinterDialog = true" />
            </div>
            <DataTable :value="printers" stripedRows>
              <Column field="name" header="Название" />
              <Column field="type" header="Тип" style="width: 120px">
                <template #body="{ data }">
                  {{ getPrinterType(data.type) }}
                </template>
              </Column>
              <Column field="connection_type" header="Подключение" style="width: 120px" />
              <Column header="Статус" style="width: 100px">
                <template #body="{ data }">
                  <Tag :value="data.is_active ? 'Активен' : 'Неактивен'" :severity="data.is_active ? 'success' : 'danger'" />
                </template>
              </Column>
              <Column header="Действия" style="width: 150px">
                <template #body="{ data }">
                  <Button icon="pi pi-play" class="p-button-text p-button-sm" @click="testPrinter(data)" v-tooltip="'Тест'" />
                  <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editPrinter(data)" />
                </template>
              </Column>
            </DataTable>
          </div>

          <!-- Users -->
          <div v-if="activeSection === 'users'" class="section-content">
            <div class="section-header">
              <h3>Пользователи</h3>
              <Button label="Добавить пользователя" icon="pi pi-plus" @click="showUserDialog = true" />
            </div>
            <DataTable :value="users" stripedRows>
              <Column field="name" header="Имя" />
              <Column field="email" header="Email" />
              <Column field="role" header="Роль" style="width: 150px">
                <template #body="{ data }">
                  <Tag :value="data.role" />
                </template>
              </Column>
              <Column header="Статус" style="width: 100px">
                <template #body="{ data }">
                  <Tag :value="data.is_active ? 'Активен' : 'Неактивен'" :severity="data.is_active ? 'success' : 'danger'" />
                </template>
              </Column>
              <Column header="Действия" style="width: 100px">
                <template #body="{ data }">
                  <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editUser(data)" />
                </template>
              </Column>
            </DataTable>
          </div>

          <!-- Integrations -->
          <div v-if="activeSection === 'integrations'" class="section-content">
            <h3>Интеграции</h3>
            <div class="integrations-list">
              <Card v-for="integration in integrations" :key="integration.id" class="integration-card">
                <template #content>
                  <div class="integration-content">
                    <div class="integration-info">
                      <img :src="integration.logo" :alt="integration.name" class="integration-logo" />
                      <div>
                        <h4>{{ integration.name }}</h4>
                        <p>{{ integration.description }}</p>
                      </div>
                    </div>
                    <Button
                      :label="integration.connected ? 'Настроить' : 'Подключить'"
                      :class="integration.connected ? 'p-button-outlined' : ''"
                    />
                  </div>
                </template>
              </Card>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default'
})

const { $api } = useNuxtApp()

const activeSection = ref('organization')

const menuItems = [
  { key: 'organization', label: 'Организация', icon: 'pi pi-building' },
  { key: 'branches', label: 'Филиалы', icon: 'pi pi-sitemap' },
  { key: 'receipt', label: 'Чек', icon: 'pi pi-file' },
  { key: 'payments', label: 'Оплата', icon: 'pi pi-credit-card' },
  { key: 'printers', label: 'Принтеры', icon: 'pi pi-print' },
  { key: 'users', label: 'Пользователи', icon: 'pi pi-users' },
  { key: 'integrations', label: 'Интеграции', icon: 'pi pi-link' }
]

const orgSettings = ref({
  name: '',
  inn: '',
  address: '',
  phone: '',
  description: ''
})

const receiptSettings = ref({
  header: '',
  footer: '',
  show_logo: true,
  print_auto: false
})

const branches = ref([])
const printers = ref([])
const users = ref([])
const paymentMethods = ref([
  { id: 1, name: 'Наличные', icon: 'pi pi-money-bill', is_active: true },
  { id: 2, name: 'Банковская карта', icon: 'pi pi-credit-card', is_active: true },
  { id: 3, name: 'Перевод', icon: 'pi pi-send', is_active: true },
  { id: 4, name: 'Бонусы', icon: 'pi pi-gift', is_active: false }
])

const integrations = ref([
  { id: 1, name: 'Telegram Bot', logo: '/integrations/telegram.svg', description: 'Уведомления и приём заказов', connected: false },
  { id: 2, name: 'Click', logo: '/integrations/click.svg', description: 'Онлайн оплата', connected: false },
  { id: 3, name: 'Payme', logo: '/integrations/payme.svg', description: 'Онлайн оплата', connected: false }
])

const showBranchDialog = ref(false)
const showPrinterDialog = ref(false)
const showUserDialog = ref(false)

const getPrinterType = (type) => {
  const types = {
    receipt: 'Чек',
    kitchen: 'Кухня',
    report: 'Отчёты'
  }
  return types[type] || type
}

const saveOrgSettings = async () => {
  // Save organization settings
  console.log('Saving org settings:', orgSettings.value)
}

const saveReceiptSettings = async () => {
  // Save receipt settings
  console.log('Saving receipt settings:', receiptSettings.value)
}

const togglePaymentMethod = (method) => {
  console.log('Toggle payment method:', method)
}

const testPrinter = async (printer) => {
  try {
    await $api(`/printers/${printer.id}/test`, { method: 'POST' })
  } catch (e) {
    console.error('Printer test failed:', e)
  }
}

const editBranch = (branch) => {
  console.log('Edit branch:', branch)
}

const editPrinter = (printer) => {
  console.log('Edit printer:', printer)
}

const editUser = (user) => {
  console.log('Edit user:', user)
}

onMounted(async () => {
  // Load settings data
  try {
    const [branchesRes, printersRes] = await Promise.all([
      $api('/branches'),
      $api('/printers')
    ])
    branches.value = branchesRes.data || []
    printers.value = printersRes.data || []
  } catch (e) {
    console.error('Failed to load settings:', e)
  }
})
</script>

<style lang="scss" scoped>
.settings-page {
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 1.5rem;

  h1 {
    margin: 0;
    font-size: 1.5rem;
  }
}

.settings-content {
  display: flex;
  gap: 1.5rem;
}

.settings-menu {
  width: 220px;
  flex-shrink: 0;
  background: white;
  border-radius: 8px;
  padding: 0.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.2s;

  &:hover {
    background: #f3f4f6;
  }

  &.active {
    background: #3b82f6;
    color: white;
  }

  i {
    font-size: 1.1rem;
  }
}

.settings-panel {
  flex: 1;
  min-height: 500px;
}

.section-content {
  h3 {
    margin: 0 0 1.5rem;
    font-size: 1.25rem;
  }
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;

  h3 {
    margin: 0;
  }
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;

  .field {
    &.col-span-2 {
      grid-column: span 2;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
  }
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.payment-method-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;

  .method-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;

    i {
      font-size: 1.25rem;
    }
  }
}

.integrations-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.integration-card {
  border: 1px solid #e5e7eb;
}

.integration-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.integration-info {
  display: flex;
  align-items: center;
  gap: 1rem;

  .integration-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
  }

  h4 {
    margin: 0 0 0.25rem;
  }

  p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
  }
}
</style>
