<template>
  <div class="kds-screen">
    <div class="kds-header">
      <h1>{{ $t('kds.title') }}</h1>
      <div class="kds-stats">
        <span class="stat pending">
          <i class="pi pi-clock" />
          {{ stats.pending }} {{ $t('kds.pending') }}
        </span>
        <span class="stat preparing">
          <i class="pi pi-sync pi-spin" />
          {{ stats.preparing }} {{ $t('kds.preparing') }}
        </span>
        <span class="stat ready">
          <i class="pi pi-check" />
          {{ stats.ready }} {{ $t('kds.ready') }}
        </span>
      </div>
    </div>

    <div class="kds-orders">
      <div
        v-for="order in orders"
        :key="order.id"
        class="kds-order"
        :class="getOrderClass(order)"
      >
        <div class="order-header">
          <span class="order-number">#{{ order.order_number }}</span>
          <span class="order-table" v-if="order.table">{{ order.table.name }}</span>
          <span class="order-time">{{ formatTime(order.created_at) }}</span>
        </div>

        <div class="order-items">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="order-item"
            :class="getItemClass(item)"
            @click="toggleItem(item)"
          >
            <span class="item-qty">{{ item.quantity }}x</span>
            <span class="item-name">{{ item.name }}</span>
            <span class="item-status">
              <i :class="getStatusIcon(item.status)" />
            </span>
          </div>
        </div>

        <div class="order-actions">
          <Button
            v-if="hasReadyItems(order)"
            :label="$t('kds.serve_all')"
            icon="pi pi-check-circle"
            class="p-button-success p-button-sm"
            @click="serveAll(order)"
          />
        </div>
      </div>

      <div v-if="!orders.length" class="kds-empty">
        <i class="pi pi-inbox" />
        <p>{{ $t('kds.no_orders') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'kds',
  middleware: 'auth',
})

const { $api } = useNuxtApp()
const authStore = useAuthStore()

const orders = ref<any[]>([])
const stats = ref({ pending: 0, preparing: 0, ready: 0 })

const formatTime = (date: string) => {
  return new Date(date).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getOrderClass = (order: any) => {
  const hasReady = order.items.some((i: any) => i.status === 'ready')
  const allReady = order.items.every((i: any) => i.status === 'ready' || i.status === 'served')
  return {
    'order-ready': allReady,
    'order-partial-ready': hasReady && !allReady,
  }
}

const getItemClass = (item: any) => ({
  'item-sent': item.status === 'sent',
  'item-preparing': item.status === 'preparing',
  'item-ready': item.status === 'ready',
})

const getStatusIcon = (status: string) => ({
  'pi pi-clock': status === 'sent',
  'pi pi-sync pi-spin': status === 'preparing',
  'pi pi-check': status === 'ready',
})

const hasReadyItems = (order: any) => {
  return order.items.some((i: any) => i.status === 'ready')
}

const toggleItem = async (item: any) => {
  try {
    if (item.status === 'sent') {
      await $api(`/kds/items/${item.id}/start`, { method: 'POST' })
    } else if (item.status === 'preparing') {
      await $api(`/kds/items/${item.id}/ready`, { method: 'POST' })
    }
    await loadOrders()
  } catch (e) {
    console.error('Failed to update item:', e)
  }
}

const serveAll = async (order: any) => {
  try {
    for (const item of order.items.filter((i: any) => i.status === 'ready')) {
      await $api(`/kds/items/${item.id}/served`, { method: 'POST' })
    }
    await loadOrders()
  } catch (e) {
    console.error('Failed to serve items:', e)
  }
}

const loadOrders = async () => {
  try {
    const [ordersRes, statsRes] = await Promise.all([
      $api('/kds/orders', { params: { branch_id: authStore.currentBranchId } }),
      $api('/kds/statistics', { params: { branch_id: authStore.currentBranchId } }),
    ])
    orders.value = ordersRes.data
    stats.value = statsRes.data
  } catch (e) {
    console.error('Failed to load KDS:', e)
  }
}

onMounted(() => {
  loadOrders()
  // Auto-refresh every 10 seconds
  setInterval(loadOrders, 10000)
})
</script>

<style lang="scss" scoped>
.kds-screen {
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 1rem;
}

.kds-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;

  h1 {
    margin: 0;
    font-size: 1.5rem;
  }
}

.kds-stats {
  display: flex;
  gap: 1rem;

  .stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;

    &.pending {
      background: rgba(255, 193, 7, 0.2);
      color: #ffc107;
    }

    &.preparing {
      background: rgba(33, 150, 243, 0.2);
      color: #2196f3;
    }

    &.ready {
      background: rgba(76, 175, 80, 0.2);
      color: #4caf50;
    }
  }
}

.kds-orders {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  overflow-y: auto;
}

.kds-order {
  background: #2d2d44;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  flex-direction: column;

  &.order-ready {
    border: 2px solid #4caf50;
  }

  &.order-partial-ready {
    border: 2px solid #ff9800;
  }
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #3d3d5c;
}

.order-number {
  font-size: 1.25rem;
  font-weight: 700;
}

.order-table {
  background: #4a4a6a;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
}

.order-time {
  color: #9e9eb3;
}

.order-items {
  flex: 1;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  background: #3d3d5c;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: #4a4a6a;
  }

  &.item-sent {
    border-left: 3px solid #ffc107;
  }

  &.item-preparing {
    border-left: 3px solid #2196f3;
    animation: pulse 1.5s infinite;
  }

  &.item-ready {
    border-left: 3px solid #4caf50;
    background: rgba(76, 175, 80, 0.1);
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.item-qty {
  font-weight: 700;
  color: #9e9eb3;
}

.item-name {
  flex: 1;
}

.item-status i {
  font-size: 1.2rem;
}

.order-actions {
  margin-top: 1rem;
  padding-top: 0.5rem;
  border-top: 1px solid #3d3d5c;
}

.kds-empty {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #6c757d;

  i {
    font-size: 4rem;
    margin-bottom: 1rem;
  }
}
</style>
