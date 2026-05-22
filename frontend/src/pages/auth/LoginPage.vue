<template>
  <div>
    <h2 class="text-xl font-bold text-gray-900 mb-1">Selamat Datang</h2>
    <p class="text-sm text-gray-500 mb-6">Silakan login untuk melanjutkan</p>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <BaseInput
        v-model="email"
        label="Email"
        type="email"
        placeholder="nama@email.com"
        required
        :error="error"
      >
        <template #prepend>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
          </svg>
        </template>
      </BaseInput>

      <BaseInput
        v-model="password"
        label="Password"
        type="password"
        placeholder="••••••••"
        required
      >
        <template #prepend>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </template>
      </BaseInput>

      <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg border border-red-200">
        {{ error }}
      </div>

      <BaseButton type="submit" variant="primary" class="w-full" :loading="loading">
        Login
      </BaseButton>

      <p class="text-center text-sm text-gray-500">
        Belum punya akun?
        <router-link to="/register" class="text-emerald-600 hover:underline font-medium">Daftar</router-link>
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'
import BaseInput from '../../components/BaseInput.vue'
import BaseButton from '../../components/BaseButton.vue'

const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    toast.success('Login berhasil!')
    const role = auth.userRole
    const routes = { karyawan: '/', admin: '/admin', dokter_umum: '/dokter', laboratorium: '/lab', radiologi: '/radiologi' }
    router.push(routes[role] || '/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Email atau password salah'
  } finally {
    loading.value = false
  }
}
</script>
