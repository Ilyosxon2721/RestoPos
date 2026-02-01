<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="/logo.svg" alt="RestoPOS" class="logo" />
      <span class="brand-name">RestoPOS</span>
    </div>

    <nav class="sidebar-nav">
      <NuxtLink to="/" class="nav-item">
        <i class="pi pi-home" />
        <span>{{ $t('dashboard.title') }}</span>
      </NuxtLink>

      <NuxtLink to="/pos" class="nav-item">
        <i class="pi pi-shopping-cart" />
        <span>POS</span>
      </NuxtLink>

      <NuxtLink to="/orders" class="nav-item">
        <i class="pi pi-list" />
        <span>Заказы</span>
      </NuxtLink>

      <NuxtLink to="/kds" class="nav-item">
        <i class="pi pi-th-large" />
        <span>{{ $t('kds.title') }}</span>
      </NuxtLink>

      <NuxtLink to="/reservations" class="nav-item">
        <i class="pi pi-calendar" />
        <span>{{ $t('reservations.title') }}</span>
      </NuxtLink>

      <div class="nav-divider" />

      <NuxtLink to="/menu" class="nav-item">
        <i class="pi pi-book" />
        <span>Меню</span>
      </NuxtLink>

      <NuxtLink to="/customers" class="nav-item">
        <i class="pi pi-users" />
        <span>Клиенты</span>
      </NuxtLink>

      <NuxtLink to="/warehouse" class="nav-item">
        <i class="pi pi-box" />
        <span>Склад</span>
      </NuxtLink>

      <NuxtLink to="/staff" class="nav-item">
        <i class="pi pi-id-card" />
        <span>Персонал</span>
      </NuxtLink>

      <div class="nav-divider" />

      <NuxtLink to="/reports" class="nav-item">
        <i class="pi pi-chart-bar" />
        <span>{{ $t('reports.title') }}</span>
      </NuxtLink>

      <NuxtLink to="/settings" class="nav-item">
        <i class="pi pi-cog" />
        <span>Настройки</span>
      </NuxtLink>
    </nav>

    <div class="sidebar-footer">
      <div class="user-info">
        <Avatar :label="authStore.user?.initials" shape="circle" />
        <div class="user-details">
          <span class="user-name">{{ authStore.user?.full_name }}</span>
          <span class="user-org">{{ authStore.organization?.name }}</span>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
const authStore = useAuthStore()
</script>

<style lang="scss" scoped>
.sidebar {
  width: 250px;
  height: 100vh;
  background: #1e293b;
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-bottom: 1px solid #334155;

  .logo {
    width: 32px;
    height: 32px;
  }

  .brand-name {
    font-size: 1.25rem;
    font-weight: 700;
  }
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: #94a3b8;
  transition: all 0.2s;
  text-decoration: none;

  &:hover {
    background: #334155;
    color: white;
    text-decoration: none;
  }

  &.router-link-active {
    background: #3b82f6;
    color: white;
  }

  i {
    font-size: 1.1rem;
  }
}

.nav-divider {
  height: 1px;
  background: #334155;
  margin: 0.5rem 1.5rem;
}

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #334155;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 500;
}

.user-org {
  font-size: 0.8rem;
  color: #94a3b8;
}
</style>
