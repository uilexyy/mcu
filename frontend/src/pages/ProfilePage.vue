<template>
  <div class="max-w-2xl">
    <PageHeader title="Profil Saya" subtitle="Kelola data dan password akun Anda" />

    <LoadingSpinner v-if="loading" />

    <template v-else>
      <BaseCard class="mb-6">
        <template #header>
          <h2 class="font-semibold text-gray-800">Data Diri</h2>
        </template>

        <form @submit.prevent="saveProfile" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <BaseInput v-model="form.name" label="Nama Lengkap" required :error="fieldError(errors, 'name')" />
            <BaseInput v-model="form.email" label="Email" type="email" required :error="fieldError(errors, 'email')" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <BaseInput v-model="form.nip" label="NIK / NIP" :error="fieldError(errors, 'nip')" />
            <BaseInput v-model="form.departemen" label="Perusahaan" :error="fieldError(errors, 'departemen')" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir</label>
              <input v-model="form.tanggal_lahir" type="date" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 outline-none" :class="fieldError(errors, 'tanggal_lahir') ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-emerald-500'" />
              <p v-if="fieldError(errors, 'tanggal_lahir')" class="mt-1 text-xs text-red-500">{{ fieldError(errors, 'tanggal_lahir') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin</label>
              <select v-model="form.jenis_kelamin" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 outline-none" :class="fieldError(errors, 'jenis_kelamin') ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-emerald-500'">
                <option value="">Pilih</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
              <p v-if="fieldError(errors, 'jenis_kelamin')" class="mt-1 text-xs text-red-500">{{ fieldError(errors, 'jenis_kelamin') }}</p>
            </div>
          </div>

          <p v-if="profileError" class="text-red-500 text-sm">{{ profileError }}</p>
          <BaseButton type="submit" variant="primary" :loading="savingProfile">Simpan Profil</BaseButton>
        </form>
      </BaseCard>

      <BaseCard v-if="auth.userRole === 'dokter_umum'" class="mb-6">
        <template #header>
          <h2 class="font-semibold text-gray-800">Tanda Tangan</h2>
        </template>

        <p class="text-sm text-gray-500 mb-4">Tanda tangan akan muncul di hasil MCU.</p>

        <div v-if="signaturePreview" class="mb-4 p-4 border border-gray-200 rounded-lg bg-white inline-block">
          <img :src="signaturePreview" class="max-h-20" alt="Tanda tangan" />
        </div>

        <div class="flex items-center gap-3">
          <label class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
            {{ signaturePreview ? 'Ganti' : 'Unggah' }} Tanda Tangan
            <input type="file" accept="image/png,image/jpeg" class="hidden" @change="uploadSignature" />
          </label>
          <button v-if="signaturePreview" @click="removeSignature" class="text-sm text-red-600 hover:underline">Hapus</button>
          <p v-if="signatureUploading" class="text-sm text-emerald-600">Mengunggah...</p>
          <p v-if="signatureError" class="text-sm text-red-500">{{ signatureError }}</p>
        </div>
        <p class="mt-2 text-xs text-gray-400">Format: PNG/JPG, maks. 512KB</p>
      </BaseCard>

      <BaseCard>
        <template #header>
          <h2 class="font-semibold text-gray-800">Ubah Password</h2>
        </template>

        <form @submit.prevent="savePassword" class="space-y-4">
          <BaseInput v-model="passwordForm.current_password" label="Password Saat Ini" type="password" required />
          <BaseInput v-model="passwordForm.new_password" label="Password Baru" type="password" placeholder="Min. 8 karakter" required />

          <p v-if="passwordError" class="text-red-500 text-sm">{{ passwordError }}</p>
          <BaseButton type="submit" variant="primary" :loading="savingPassword">Ubah Password</BaseButton>
        </form>
      </BaseCard>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { parseErrors, fieldError } from '../utils/errors'
import api from '../utils/axios'
import PageHeader from '../components/PageHeader.vue'
import BaseCard from '../components/BaseCard.vue'
import BaseInput from '../components/BaseInput.vue'
import BaseButton from '../components/BaseButton.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const auth = useAuthStore()
const toast = useToastStore()
const loading = ref(true)
const savingProfile = ref(false)
const savingPassword = ref(false)
const profileError = ref('')
const passwordError = ref('')
const signaturePreview = ref('')
const signatureUploading = ref(false)
const signatureError = ref('')
const errors = ref({})

const form = reactive({
  name: '', email: '', nip: '', departemen: '', tanggal_lahir: '', jenis_kelamin: '',
})

const passwordForm = reactive({
  current_password: '', new_password: '',
})

async function saveProfile() {
  savingProfile.value = true
  profileError.value = ''
  errors.value = {}
  try {
    const res = await api.put('/profile', form)
    auth.user.value = res.data.data
    localStorage.setItem('user', JSON.stringify(res.data.data))
    toast.success('Profil berhasil diperbarui')
  } catch (e) {
    const parsed = parseErrors(e)
    if (Object.keys(parsed).length > 0) {
      errors.value = parsed
    } else {
      profileError.value = e.response?.data?.message || 'Gagal menyimpan profil'
    }
  } finally {
    savingProfile.value = false
  }
}

async function uploadSignature(e) {
  const file = e.target.files?.[0]
  if (!file) return
  signatureUploading.value = true
  signatureError.value = ''
  const fd = new FormData()
  fd.append('signature', file)
  try {
    const res = await api.post('/profile/signature', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    auth.user.value = res.data.data
    localStorage.setItem('user', JSON.stringify(res.data.data))
    signaturePreview.value = res.data.data.signature_url
    toast.success('Tanda tangan berhasil diunggah')
  } catch (e) {
    signatureError.value = e.response?.data?.message || 'Gagal mengunggah'
  } finally {
    signatureUploading.value = false
  }
}

async function removeSignature() {
  // For now, just clear preview; full delete would need a new endpoint
  signaturePreview.value = ''
}

async function savePassword() {
  savingPassword.value = true
  passwordError.value = ''
  try {
    await api.put('/profile/password', passwordForm)
    toast.success('Password berhasil diubah')
    passwordForm.current_password = ''
    passwordForm.new_password = ''
  } catch (e) {
    passwordError.value = e.response?.data?.message || 'Gagal mengubah password'
  } finally {
    savingPassword.value = false
  }
}

onMounted(async () => {
  try {
    const res = await api.get('/profile')
    const u = res.data.data
    Object.assign(form, {
      name: u.name || '',
      email: u.email || '',
      nip: u.nip || '',
      departemen: u.departemen || '',
      tanggal_lahir: u.tanggal_lahir || '',
      jenis_kelamin: u.jenis_kelamin || '',
    })
    signaturePreview.value = u.signature_url || ''
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>
