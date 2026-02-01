<template>
  <div class="pos-terminal">
    <div class="pos-main">
      <!-- Categories & Products -->
      <div class="pos-menu">
        <div class="categories-bar">
          <Button
            v-for="cat in categories"
            :key="cat.id"
            :label="cat.name"
            :class="{ 'p-button-primary': selectedCategory === cat.id }"
            class="category-btn"
            @click="selectedCategory = cat.id"
          />
        </div>

        <div class="products-grid">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="product-card"
            @click="addToOrder(product)"
          >
            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.name"
              class="product-image"
            />
            <div v-else class="product-image-placeholder">
              <i class="pi pi-image" />
            </div>
            <div class="product-info">
              <span class="product-name">{{ product.name }}</span>
              <span class="product-price">{{ formatCurrency(product.price) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Panel -->
      <div class="pos-order">
        <div class="order-header">
          <h3>{{ $t('pos.current_order') }}</h3>
          <span v-if="currentTable" class="table-badge">
            {{ currentTable.name }}
          </span>
        </div>

        <div class="order-items">
          <div
            v-for="(item, index) in orderStore.currentOrder?.items"
            :key="index"
            class="order-item"
          >
            <div class="item-info">
              <span class="item-name">{{ item.name }}</span>
              <span class="item-modifiers" v-if="item.modifiers.length">
                {{ item.modifiers.map(m => m.name).join(', ') }}
              </span>
            </div>
            <div class="item-qty">
              <Button
                icon="pi pi-minus"
                class="p-button-text p-button-sm"
                @click="orderStore.updateItemQuantity(index, item.quantity - 1)"
              />
              <span>{{ item.quantity }}</span>
              <Button
                icon="pi pi-plus"
                class="p-button-text p-button-sm"
                @click="orderStore.updateItemQuantity(index, item.quantity + 1)"
              />
            </div>
            <div class="item-total">
              {{ formatCurrency(item.total) }}
            </div>
            <Button
              icon="pi pi-trash"
              class="p-button-text p-button-danger p-button-sm"
              @click="orderStore.removeItem(index)"
            />
          </div>

          <div v-if="!orderStore.currentOrder?.items.length" class="empty-order">
            <i class="pi pi-shopping-cart" />
            <p>{{ $t('pos.empty_order') }}</p>
          </div>
        </div>

        <div class="order-totals">
          <div class="total-row">
            <span>{{ $t('pos.subtotal') }}</span>
            <span>{{ formatCurrency(orderStore.currentOrder?.subtotal || 0) }}</span>
          </div>
          <div class="total-row" v-if="orderStore.currentOrder?.discount_amount">
            <span>{{ $t('pos.discount') }}</span>
            <span class="text-red-500">
              -{{ formatCurrency(orderStore.currentOrder.discount_amount) }}
            </span>
          </div>
          <div class="total-row total-final">
            <span>{{ $t('pos.total') }}</span>
            <span>{{ formatCurrency(orderStore.currentOrder?.total || 0) }}</span>
          </div>
        </div>

        <div class="order-actions">
          <Button
            :label="$t('pos.clear')"
            icon="pi pi-times"
            class="p-button-secondary"
            :disabled="!orderStore.hasItems"
            @click="orderStore.clearOrder()"
          />
          <Button
            :label="$t('pos.send_to_kitchen')"
            icon="pi pi-send"
            class="p-button-warning"
            :disabled="!orderStore.hasItems"
            @click="sendToKitchen"
          />
          <Button
            :label="$t('pos.pay')"
            icon="pi pi-credit-card"
            class="p-button-success"
            :disabled="!orderStore.hasItems"
            @click="showPaymentDialog = true"
          />
        </div>
      </div>
    </div>

    <!-- Payment Dialog -->
    <Dialog
      v-model:visible="showPaymentDialog"
      :header="$t('payment.title')"
      :style="{ width: '500px' }"
      modal
    >
      <div class="payment-dialog">
        <div class="payment-amount">
          <span>{{ $t('payment.total') }}:</span>
          <span class="amount">{{ formatCurrency(orderStore.currentOrder?.total || 0) }}</span>
        </div>

        <div class="payment-methods">
          <Button
            v-for="method in paymentMethods"
            :key="method.type"
            :label="method.name"
            :icon="method.icon"
            :class="{ 'p-button-primary': selectedPayment === method.type }"
            class="payment-method-btn"
            @click="selectedPayment = method.type"
          />
        </div>

        <div class="payment-input" v-if="selectedPayment === 'cash'">
          <label>{{ $t('payment.received') }}</label>
          <InputNumber
            v-model="receivedAmount"
            :min="0"
            mode="currency"
            currency="UZS"
            locale="ru-RU"
          />
          <div class="change" v-if="change > 0">
            {{ $t('payment.change') }}: {{ formatCurrency(change) }}
          </div>
        </div>
      </div>

      <template #footer>
        <Button
          :label="$t('common.cancel')"
          class="p-button-text"
          @click="showPaymentDialog = false"
        />
        <Button
          :label="$t('payment.confirm')"
          icon="pi pi-check"
          @click="processPayment"
          :loading="paymentLoading"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'pos',
  middleware: 'auth',
})

const { $api } = useNuxtApp()
const orderStore = useOrderStore()
const authStore = useAuthStore()
const { t } = useI18n()

const categories = ref<any[]>([])
const products = ref<any[]>([])
const selectedCategory = ref<number | null>(null)
const currentTable = ref<any>(null)

const showPaymentDialog = ref(false)
const selectedPayment = ref('cash')
const receivedAmount = ref(0)
const paymentLoading = ref(false)

const paymentMethods = [
  { type: 'cash', name: t('payment.cash'), icon: 'pi pi-wallet' },
  { type: 'card', name: t('payment.card'), icon: 'pi pi-credit-card' },
  { type: 'transfer', name: t('payment.transfer'), icon: 'pi pi-mobile' },
]

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value
  return products.value.filter(p => p.category_id === selectedCategory.value)
})

const change = computed(() => {
  return Math.max(0, receivedAmount.value - (orderStore.currentOrder?.total || 0))
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'UZS',
    minimumFractionDigits: 0,
  }).format(value)
}

const addToOrder = (product: any) => {
  orderStore.addItem(product)
}

const sendToKitchen = async () => {
  try {
    const order = await orderStore.saveOrder()
    if (order) {
      await $api(`/orders/${order.id}/send-to-kitchen`, { method: 'POST' })
    }
  } catch (e) {
    console.error('Failed to send to kitchen:', e)
  }
}

const processPayment = async () => {
  paymentLoading.value = true
  try {
    const order = await orderStore.saveOrder()
    if (order) {
      await $api('/payments/process', {
        method: 'POST',
        body: {
          order_id: order.id,
          method: selectedPayment.value,
          amount: order.total,
        },
      })
      await $api(`/orders/${order.id}/close`, { method: 'POST' })
      showPaymentDialog.value = false
      orderStore.clearOrder()
    }
  } catch (e) {
    console.error('Payment failed:', e)
  } finally {
    paymentLoading.value = false
  }
}

onMounted(async () => {
  orderStore.initOrder()

  try {
    const [catRes, prodRes] = await Promise.all([
      $api('/menu/categories', { params: { active_only: true } }),
      $api('/menu/products', {
        params: {
          active_only: true,
          branch_id: authStore.currentBranchId,
          paginate: false,
        }
      }),
    ])
    categories.value = catRes.data
    products.value = prodRes.data.map((p: any) => ({
      ...p,
      price: p.prices?.[0]?.price || 0,
    }))
    if (categories.value.length) {
      selectedCategory.value = categories.value[0].id
    }
  } catch (e) {
    console.error('Failed to load menu:', e)
  }
})
</script>

<style lang="scss" scoped>
.pos-terminal {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.pos-main {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.pos-menu {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.categories-bar {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  overflow-x: auto;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;

  .category-btn {
    white-space: nowrap;
  }
}

.products-grid {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  padding: 1rem;
  overflow-y: auto;
}

.product-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.2s;
  overflow: hidden;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }
}

.product-image {
  width: 100%;
  height: 100px;
  object-fit: cover;
}

.product-image-placeholder {
  width: 100%;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e9ecef;
  color: #adb5bd;
  font-size: 2rem;
}

.product-info {
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.product-name {
  font-weight: 500;
  font-size: 0.9rem;
}

.product-price {
  color: var(--primary-color);
  font-weight: 600;
}

.pos-order {
  width: 400px;
  display: flex;
  flex-direction: column;
  border-left: 1px solid #dee2e6;
  background: white;
}

.order-header {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;

  h3 {
    margin: 0;
  }
}

.table-badge {
  background: var(--primary-color);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
}

.order-items {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.item-name {
  font-weight: 500;
}

.item-modifiers {
  font-size: 0.8rem;
  color: #6c757d;
}

.item-qty {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.item-total {
  font-weight: 600;
  min-width: 80px;
  text-align: right;
}

.empty-order {
  text-align: center;
  padding: 3rem 1rem;
  color: #adb5bd;

  i {
    font-size: 3rem;
    margin-bottom: 1rem;
  }
}

.order-totals {
  padding: 1rem;
  border-top: 1px solid #dee2e6;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;

  &.total-final {
    font-size: 1.25rem;
    font-weight: 700;
    border-top: 2px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 0.5rem;
  }
}

.order-actions {
  padding: 1rem;
  display: flex;
  gap: 0.5rem;
  border-top: 1px solid #dee2e6;

  > * {
    flex: 1;
  }
}

.payment-dialog {
  .payment-amount {
    text-align: center;
    margin-bottom: 2rem;

    span:first-child {
      display: block;
      color: #6c757d;
      margin-bottom: 0.5rem;
    }

    .amount {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary-color);
    }
  }

  .payment-methods {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;

    .payment-method-btn {
      flex: 1;
    }
  }

  .payment-input {
    label {
      display: block;
      margin-bottom: 0.5rem;
    }

    .change {
      margin-top: 1rem;
      padding: 1rem;
      background: #e8f5e9;
      border-radius: 4px;
      text-align: center;
      font-weight: 600;
      color: #2e7d32;
    }
  }
}
</style>
