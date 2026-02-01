<template>
  <div class="layout-wrapper">
    <AppSidebar v-if="isAuthenticated" />
    <div class="layout-main" :class="{ 'layout-main-full': !isAuthenticated }">
      <AppTopbar v-if="isAuthenticated" />
      <div class="layout-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)
</script>

<style lang="scss">
.layout-wrapper {
  display: flex;
  min-height: 100vh;
}

.layout-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: 250px;
  transition: margin-left 0.3s;

  &-full {
    margin-left: 0;
  }
}

.layout-content {
  flex: 1;
  padding: 1rem;
}
</style>
