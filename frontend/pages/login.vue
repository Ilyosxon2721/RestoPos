<template>
  <div class="login-page">
    <div class="login-card">
      <div class="text-center mb-5">
        <img src="/logo.svg" alt="RestoPOS" height="50" class="mb-3" />
        <h2>{{ $t('auth.login') }}</h2>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="field">
          <label for="login">{{ $t('auth.email_or_phone') }}</label>
          <InputText
            id="login"
            v-model="form.login"
            class="w-full"
            :class="{ 'p-invalid': errors.login }"
          />
          <small v-if="errors.login" class="p-error">{{ errors.login }}</small>
        </div>

        <div class="field">
          <label for="password">{{ $t('auth.password') }}</label>
          <Password
            id="password"
            v-model="form.password"
            class="w-full"
            :class="{ 'p-invalid': errors.password }"
            :feedback="false"
            toggleMask
          />
          <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
        </div>

        <Button
          type="submit"
          :label="$t('auth.sign_in')"
          icon="pi pi-sign-in"
          class="w-full"
          :loading="loading"
        />

        <div class="text-center mt-4">
          <NuxtLink to="/pin-login" class="text-primary">
            {{ $t('auth.pin_login') }}
          </NuxtLink>
        </div>

        <Divider />

        <div class="text-center">
          <span>{{ $t('auth.no_account') }}</span>
          <NuxtLink to="/register" class="text-primary ml-2">
            {{ $t('auth.register') }}
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

const authStore = useAuthStore()
const router = useRouter()
const { t } = useI18n()

const form = ref({
  login: '',
  password: '',
})

const errors = ref<Record<string, string>>({})
const loading = ref(false)

const handleLogin = async () => {
  errors.value = {}
  loading.value = true

  try {
    await authStore.login(form.value)
    await authStore.fetchUser()
    router.push('/')
  } catch (e: any) {
    if (e.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(e.data.errors).map(([k, v]) => [k, (v as string[])[0]])
      )
    } else {
      errors.value.login = e.data?.message || t('auth.login_failed')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
}

.field {
  margin-bottom: 1.5rem;

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
  }
}
</style>
