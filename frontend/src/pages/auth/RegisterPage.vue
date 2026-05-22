<template>
  <div>
    <h2 class="text-xl font-bold text-gray-900 mb-1">Daftar Akun Baru</h2>
    <p class="text-sm text-gray-500 mb-6">Isi data diri untuk mendaftar sebagai karyawan</p>

    <form @submit.prevent="handleRegister" class="space-y-3">
      <BaseInput v-model="form.name" label="Nama Lengkap" placeholder="Nama lengkap" required :error="fieldError(errors, 'name')" />
      <BaseInput v-model="form.email" label="Email" type="email" placeholder="nama@email.com" required :error="fieldError(errors, 'email')" />
      <BaseInput v-model="form.password" label="Password" type="password" placeholder="Min. 8 karakter" required :error="fieldError(errors, 'password')" />

      <div class="grid grid-cols-2 gap-3">
        <BaseInput v-model="form.nip" label="NIK / NIP" placeholder="NIP" :error="fieldError(errors, 'nip')" />
        <BaseInput v-model="form.departemen" label="Perusahaan" placeholder="Perusahaan" :error="fieldError(errors, 'departemen')" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <BaseInput v-model="form.tanggal_lahir" label="Tanggal Lahir" type="date" :error="fieldError(errors, 'tanggal_lahir')" />
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin</label>
          <select v-model="form.jenis_kelamin" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none" :class="fieldError(errors, 'jenis_kelamin') ? 'border-red-300 focus:ring-red-500' : 'border-gray-300'">
            <option value="">Pilih</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
          </select>
          <p v-if="fieldError(errors, 'jenis_kelamin')" class="mt-1 text-xs text-red-500">{{ fieldError(errors, 'jenis_kelamin') }}</p>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg border border-red-200">{{ error }}</div>

      <BaseButton type="submit" variant="primary" class="w-full" :loading="loading">Daftar</BaseButton>

      <p class="text-center text-sm text-gray-500">
        Sudah punya akun?
        <router-link to="/login" class="text-emerald-600 hover:underline font-medium">Login</router-link>
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'
import { parseErrors, fieldError } from '../../utils/errors'
import BaseInput from '../../components/BaseInput.vue'
import BaseButton from '../../components/BaseButton.vue'

const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()
const loading = ref(false)
const error = ref('')
const errors = ref({})
const form = reactive({
  name: '', email: '', password: '', nip: '', departemen: '', tanggal_lahir: '', jenis_kelamin: '',
})

async function handleRegister() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    await auth.register({ ...form })
    toast.success('Registrasi berhasil!')
    router.push('/')
  } catch (e) {
    const parsed = parseErrors(e)
    if (Object.keys(parsed).length > 0) {
      errors.value = parsed
    } else {
      error.value = e.response?.data?.message || 'Registrasi gagal'
    }
  } finally {
    loading.value = false
  }
}
</script>
