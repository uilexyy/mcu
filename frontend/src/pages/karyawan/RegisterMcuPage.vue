<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar MCU Baru</h1>

    <div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Paket MCU</label>
          <select v-model="form.package_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            <option value="">Pilih Paket</option>
            <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
              {{ pkg.nama_paket }} - Rp {{ formatPrice(pkg.harga) }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Foto KTP (opsional)</label>
          <input type="file" @change="onFileChange" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
        </div>

        <div v-if="selectedPackage && selectedPackage.items" class="bg-gray-50 rounded-lg p-4">
          <h3 class="font-semibold text-gray-700 mb-2">Pemeriksaan dalam paket ini:</h3>
          <ul class="space-y-1">
            <li v-for="item in selectedPackage.items" :key="item.id" class="text-sm text-gray-600 flex items-center">
              <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ item.nama_pemeriksaan }}
            </li>
          </ul>
        </div>

        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <p v-if="success" class="text-green-600 text-sm">{{ success }}</p>

        <button type="submit" :disabled="loading"
          class="w-full bg-emerald-600 text-white py-2 rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50">
          {{ loading ? 'Mengirim...' : 'Daftar MCU' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../utils/axios'

const packages = ref([])
const selectedPackage = computed(() => packages.value.find(p => p.id === form.package_id))
const loading = ref(false)
const error = ref('')
const success = ref('')
const form = reactive({ package_id: '', foto: null })

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID').format(price)
}

function onFileChange(e) {
  form.foto = e.target.files[0]
}

async function handleSubmit() {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const fd = new FormData()
    fd.append('package_id', form.package_id)
    if (form.foto) fd.append('foto_ktp', form.foto)
    await api.post('/karyawan/registrations', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    success.value = 'Pendaftaran MCU berhasil dikirim! Silakan tunggu verifikasi admin.'
    form.package_id = ''
    form.foto = null
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mendaftar MCU'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const res = await api.get('/packages')
    packages.value = res.data.data
  } catch (e) {
    console.error(e)
  }
})
</script>
