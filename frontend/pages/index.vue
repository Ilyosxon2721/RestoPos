<template>
  <div class="dashboard">
    <h1>{{ $t('dashboard.title') }}</h1>

    <div class="grid">
      <div class="col-12 md:col-6 lg:col-3">
        <Card>
          <template #title>{{ $t('dashboard.today_orders') }}</template>
          <template #content>
            <div class="text-4xl font-bold text-primary">{{ stats.today?.orders || 0 }}</div>
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-3">
        <Card>
          <template #title>{{ $t('dashboard.today_revenue') }}</template>
          <template #content>
            <div class="text-4xl font-bold text-green-500">
              {{ formatCurrency(stats.today?.revenue || 0) }}
            </div>
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-3">
        <Card>
          <template #title>{{ $t('dashboard.avg_check') }}</template>
          <template #content>
            <div class="text-4xl font-bold text-blue-500">
              {{ formatCurrency(stats.today?.avg_check || 0) }}
            </div>
          </template>
        </Card>
      </div>

      <div class="col-12 md:col-6 lg:col-3">
        <Card>
          <template #title>{{ $t('dashboard.new_customers') }}</template>
          <template #content>
            <div class="text-4xl font-bold text-orange-500">{{ stats.today?.new_customers || 0 }}</div>
          </template>
        </Card>
      </div>
    </div>

    <div class="grid mt-4">
      <div class="col-12 lg:col-8">
        <Card>
          <template #title>{{ $t('dashboard.top_products') }}</template>
          <template #content>
            <DataTable :value="stats.top_products" :rows="5">
              <Column field="name" :header="$t('common.name')" />
              <Column field="qty" :header="$t('common.quantity')" />
              <Column field="revenue" :header="$t('common.revenue')">
                <template #body="{ data }">
                  {{ formatCurrency(data.revenue) }}
                </template>
              </Column>
            </DataTable>
          </template>
        </Card>
      </div>

      <div class="col-12 lg:col-4">
        <Card>
          <template #title>{{ $t('dashboard.quick_actions') }}</template>
          <template #content>
            <div class="flex flex-column gap-2">
              <Button
                :label="$t('pos.new_order')"
                icon="pi pi-plus"
                class="w-full"
                @click="$router.push('/pos')"
              />
              <Button
                :label="$t('reservations.title')"
                icon="pi pi-calendar"
                class="w-full"
                severity="secondary"
                @click="$router.push('/reservations')"
              />
              <Button
                :label="$t('reports.title')"
                icon="pi pi-chart-bar"
                class="w-full"
                severity="info"
                @click="$router.push('/reports')"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { $api } = useNuxtApp()
const authStore = useAuthStore()

const stats = ref<any>({})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0,
  }).format(value)
}

onMounted(async () => {
  try {
    const response = await $api('/reports/dashboard', {
      params: { branch_id: authStore.currentBranchId },
    })
    stats.value = response.data
  } catch (e) {
    console.error('Failed to load dashboard:', e)
  }
})
</script>
