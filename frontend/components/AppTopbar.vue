<template>
  <header class="topbar">
    <div class="topbar-left">
      <Dropdown
        v-model="currentBranch"
        :options="branches"
        optionLabel="name"
        optionValue="id"
        placeholder="Выберите филиал"
        class="branch-select"
        @change="onBranchChange"
      />
    </div>

    <div class="topbar-right">
      <Button
        icon="pi pi-bell"
        class="p-button-text p-button-plain"
        badge="3"
        badgeClass="p-badge-danger"
      />

      <Dropdown
        v-model="locale"
        :options="locales"
        optionLabel="name"
        optionValue="code"
        class="locale-select"
        @change="setLocale($event.value)"
      >
        <template #value="{ value }">
          {{ locales.find(l => l.code === value)?.name }}
        </template>
      </Dropdown>

      <Button
        icon="pi pi-sign-out"
        class="p-button-text p-button-danger"
        @click="handleLogout"
      />
    </div>
  </header>
</template>

<script setup>
const authStore = useAuthStore()
const { locale, setLocale } = useI18n()
const router = useRouter()

const branches = ref([])
const currentBranch = ref(authStore.currentBranchId)

const locales = [
  { code: 'ru', name: 'RU' },
  { code: 'uz', name: 'UZ' },
  { code: 'en', name: 'EN' },
]

const onBranchChange = (e) => {
  authStore.setBranch(e.value)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  const { $api } = useNuxtApp()
  try {
    const response = await $api('/branches', { params: { active_only: true } })
    branches.value = response.data
    if (!currentBranch.value && branches.value.length) {
      currentBranch.value = branches.value[0].id
      authStore.setBranch(currentBranch.value)
    }
  } catch (e) {
    console.error('Failed to load branches:', e)
  }
})
</script>

<style lang="scss" scoped>
.topbar {
  height: 60px;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
}

.topbar-left, .topbar-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.branch-select {
  min-width: 200px;
}

.locale-select {
  width: 70px;
}
</style>
