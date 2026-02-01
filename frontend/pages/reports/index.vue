<template>
  <div class="reports-page">
    <div class="page-header">
      <h1>{{ $t('reports.title') || 'Отчёты' }}</h1>
      <div class="header-actions">
        <Calendar
          v-model="dateRange"
          selectionMode="range"
          dateFormat="dd.mm.yy"
          placeholder="Выберите период"
          showIcon
          class="date-picker"
        />
        <Button
          label="Экспорт"
          icon="pi pi-download"
          class="p-button-outlined"
          @click="showExportMenu"
        />
        <Menu ref="exportMenu" :model="exportItems" popup />
      </div>
    </div>

    <div class="report-tabs">
      <TabView v-model:activeIndex="activeTab">
        <TabPanel header="Продажи">
          <div class="report-content">
            <div class="stats-row">
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Выручка</span>
                      <span class="stat-value">{{ formatPrice(salesReport.total_revenue) }}</span>
                    </div>
                    <i class="pi pi-wallet stat-icon text-green-500"></i>
                  </div>
                </template>
              </Card>
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Заказов</span>
                      <span class="stat-value">{{ salesReport.orders_count }}</span>
                    </div>
                    <i class="pi pi-shopping-cart stat-icon text-blue-500"></i>
                  </div>
                </template>
              </Card>
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Средний чек</span>
                      <span class="stat-value">{{ formatPrice(salesReport.average_check) }}</span>
                    </div>
                    <i class="pi pi-chart-line stat-icon text-orange-500"></i>
                  </div>
                </template>
              </Card>
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Скидки</span>
                      <span class="stat-value">{{ formatPrice(salesReport.total_discounts) }}</span>
                    </div>
                    <i class="pi pi-percentage stat-icon text-red-500"></i>
                  </div>
                </template>
              </Card>
            </div>

            <Card class="chart-card">
              <template #title>Динамика продаж</template>
              <template #content>
                <div class="chart-placeholder">
                  <i class="pi pi-chart-line"></i>
                  <p>График продаж за выбранный период</p>
                </div>
              </template>
            </Card>

            <Card class="table-card">
              <template #title>Продажи по дням</template>
              <template #content>
                <DataTable :value="salesReport.daily_sales" stripedRows>
                  <Column field="date" header="Дата">
                    <template #body="{ data }">
                      {{ formatDate(data.date) }}
                    </template>
                  </Column>
                  <Column field="orders_count" header="Заказов" />
                  <Column field="revenue" header="Выручка">
                    <template #body="{ data }">
                      {{ formatPrice(data.revenue) }}
                    </template>
                  </Column>
                  <Column field="average_check" header="Средний чек">
                    <template #body="{ data }">
                      {{ formatPrice(data.average_check) }}
                    </template>
                  </Column>
                </DataTable>
              </template>
            </Card>
          </div>
        </TabPanel>

        <TabPanel header="Товары">
          <div class="report-content">
            <Card class="table-card">
              <template #title>Топ продаваемых товаров</template>
              <template #content>
                <DataTable :value="productsReport.top_products" stripedRows>
                  <Column field="rank" header="#" style="width: 50px">
                    <template #body="{ index }">
                      {{ index + 1 }}
                    </template>
                  </Column>
                  <Column field="name" header="Товар" />
                  <Column field="quantity" header="Количество" style="width: 120px" />
                  <Column field="revenue" header="Выручка" style="width: 150px">
                    <template #body="{ data }">
                      {{ formatPrice(data.revenue) }}
                    </template>
                  </Column>
                  <Column field="share" header="Доля" style="width: 100px">
                    <template #body="{ data }">
                      {{ data.share }}%
                    </template>
                  </Column>
                </DataTable>
              </template>
            </Card>

            <Card class="table-card">
              <template #title>Продажи по категориям</template>
              <template #content>
                <DataTable :value="productsReport.by_category" stripedRows>
                  <Column field="category" header="Категория" />
                  <Column field="items_sold" header="Продано" style="width: 120px" />
                  <Column field="revenue" header="Выручка" style="width: 150px">
                    <template #body="{ data }">
                      {{ formatPrice(data.revenue) }}
                    </template>
                  </Column>
                </DataTable>
              </template>
            </Card>
          </div>
        </TabPanel>

        <TabPanel header="Сотрудники">
          <div class="report-content">
            <Card class="table-card">
              <template #title>Эффективность сотрудников</template>
              <template #content>
                <DataTable :value="employeesReport.employees" stripedRows>
                  <Column field="name" header="Сотрудник" />
                  <Column field="orders_count" header="Заказов" style="width: 100px" />
                  <Column field="total_sales" header="Продажи" style="width: 150px">
                    <template #body="{ data }">
                      {{ formatPrice(data.total_sales) }}
                    </template>
                  </Column>
                  <Column field="average_check" header="Средний чек" style="width: 130px">
                    <template #body="{ data }">
                      {{ formatPrice(data.average_check) }}
                    </template>
                  </Column>
                  <Column field="hours_worked" header="Часов" style="width: 100px" />
                  <Column field="tips" header="Чаевые" style="width: 120px">
                    <template #body="{ data }">
                      {{ formatPrice(data.tips) }}
                    </template>
                  </Column>
                </DataTable>
              </template>
            </Card>
          </div>
        </TabPanel>

        <TabPanel header="Оплаты">
          <div class="report-content">
            <div class="stats-row">
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Наличные</span>
                      <span class="stat-value">{{ formatPrice(paymentsReport.cash) }}</span>
                    </div>
                    <i class="pi pi-money-bill stat-icon text-green-500"></i>
                  </div>
                </template>
              </Card>
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Карта</span>
                      <span class="stat-value">{{ formatPrice(paymentsReport.card) }}</span>
                    </div>
                    <i class="pi pi-credit-card stat-icon text-blue-500"></i>
                  </div>
                </template>
              </Card>
              <Card class="stat-card">
                <template #content>
                  <div class="stat-content">
                    <div class="stat-info">
                      <span class="stat-label">Перевод</span>
                      <span class="stat-value">{{ formatPrice(paymentsReport.transfer) }}</span>
                    </div>
                    <i class="pi pi-send stat-icon text-purple-500"></i>
                  </div>
                </template>
              </Card>
            </div>

            <Card class="chart-card">
              <template #title>Распределение по способам оплаты</template>
              <template #content>
                <div class="chart-placeholder">
                  <i class="pi pi-chart-pie"></i>
                  <p>Диаграмма способов оплаты</p>
                </div>
              </template>
            </Card>
          </div>
        </TabPanel>
      </TabView>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default'
})

const { $api } = useNuxtApp()

const activeTab = ref(0)
const exportMenu = ref()
const dateRange = ref([
  new Date(new Date().setDate(new Date().getDate() - 7)),
  new Date()
])

const salesReport = ref({
  total_revenue: 0,
  orders_count: 0,
  average_check: 0,
  total_discounts: 0,
  daily_sales: []
})

const productsReport = ref({
  top_products: [],
  by_category: []
})

const employeesReport = ref({
  employees: []
})

const paymentsReport = ref({
  cash: 0,
  card: 0,
  transfer: 0
})

const exportItems = [
  {
    label: 'Excel',
    icon: 'pi pi-file-excel',
    command: () => exportReport('excel')
  },
  {
    label: 'PDF',
    icon: 'pi pi-file-pdf',
    command: () => exportReport('pdf')
  }
]

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

const showExportMenu = (event) => {
  exportMenu.value.toggle(event)
}

const exportReport = async (format) => {
  const reportTypes = ['sales', 'products', 'employees', 'payments']
  const type = reportTypes[activeTab.value]

  try {
    window.open(`/api/v1/reports/export/${type}?format=${format}`, '_blank')
  } catch (e) {
    console.error('Export failed:', e)
  }
}

const fetchReports = async () => {
  const params = {}

  if (dateRange.value && dateRange.value[0]) {
    params.from = dateRange.value[0].toISOString().split('T')[0]
    if (dateRange.value[1]) {
      params.to = dateRange.value[1].toISOString().split('T')[0]
    }
  }

  try {
    const [sales, products, employees] = await Promise.all([
      $api('/reports/sales', { params }),
      $api('/reports/products', { params }),
      $api('/reports/employees', { params })
    ])

    salesReport.value = sales.data || salesReport.value
    productsReport.value = products.data || productsReport.value
    employeesReport.value = employees.data || employeesReport.value

    // Calculate payments from sales
    if (sales.data?.by_payment_method) {
      paymentsReport.value = sales.data.by_payment_method
    }
  } catch (e) {
    console.error('Failed to fetch reports:', e)
  }
}

watch(dateRange, () => {
  fetchReports()
}, { deep: true })

onMounted(() => {
  fetchReports()
})
</script>

<style lang="scss" scoped>
.reports-page {
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
    align-items: center;
  }

  .date-picker {
    width: 280px;
  }
}

.report-content {
  padding: 1rem 0;
}

.stats-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  flex: 1;
}

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-label {
  color: #6c757d;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-icon {
  font-size: 2rem;
  opacity: 0.7;
}

.chart-card,
.table-card {
  margin-bottom: 1.5rem;
}

.chart-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: #f8f9fa;
  border-radius: 8px;
  color: #6c757d;

  i {
    font-size: 3rem;
    margin-bottom: 1rem;
  }

  p {
    margin: 0;
  }
}

.text-green-500 { color: #22c55e; }
.text-blue-500 { color: #3b82f6; }
.text-orange-500 { color: #f97316; }
.text-red-500 { color: #ef4444; }
.text-purple-500 { color: #a855f7; }
</style>
