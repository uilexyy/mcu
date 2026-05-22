<template>
  <div>
    <router-link to="/radiologi" class="inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700 mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Kembali
    </router-link>

    <LoadingSpinner v-if="loading" />
    <EmptyState v-else-if="!registration" title="Data tidak ditemukan" />

    <BaseCard v-else>
      <template #header>
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Hasil Radiologi</h1>
            <span v-if="isEdit" class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Edit</span>
          </div>
          <p class="text-sm text-gray-500 mt-0.5">{{ registration.user?.name }} — {{ registration.package?.nama_paket }}</p>
          <p v-if="existingFileUrl" class="text-xs text-gray-400 mt-1">
            Foto saat ini: <a :href="existingFileUrl" target="_blank" class="text-pink-600 underline">lihat foto</a>
          </p>
        </div>
      </template>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Interpretasi</label>
          <textarea v-model="form.interpretasi" rows="5" required placeholder="Hasil interpretasi rontgen..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Foto Rontgen (opsional)</label>
          <input type="file" @change="onFileChange" accept="image/*"
            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 cursor-pointer" />
        </div>

        <div v-if="previewUrl" class="mt-2">
          <img :src="previewUrl" class="max-w-xs rounded-lg border shadow-sm" />
        </div>

        <div v-if="submitError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg border border-red-200">{{ submitError }}</div>
        <div v-if="success" class="bg-emerald-50 text-emerald-600 text-sm px-4 py-2.5 rounded-lg border border-emerald-200">{{ success }}</div>

        <BaseButton type="submit" variant="danger" class="w-full" :loading="submitting">
          {{ isEdit ? 'Perbarui & Generate Ulang PDF' : 'Simpan & Selesaikan MCU' }}
        </BaseButton>
      </form>
    </BaseCard>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../utils/axios'
import BaseCard from '../../components/BaseCard.vue'
import BaseButton from '../../components/BaseButton.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import EmptyState from '../../components/EmptyState.vue'
import { useToastStore } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()
const registration = ref(null)
const loading = ref(true)
const submitting = ref(false)
const submitError = ref('')
const success = ref('')
const previewUrl = ref('')
const isEdit = ref(false)
const form = reactive({ interpretasi: '', foto: null })

const existingFileUrl = computed(() => registration.value?.radiology_result?.file_url || null)

function onFileChange(e) { const file = e.target.files[0]; form.foto = file; if (file) previewUrl.value = URL.createObjectURL(file) }

async function handleSubmit() {
  submitting.value = true; submitError.value = ''; success.value = ''
  try {
    const fd = new FormData()
    fd.append('registration_id', route.params.id)
    fd.append('interpretasi', form.interpretasi)
    if (form.foto) fd.append('foto', form.foto)

    if (isEdit.value) {
      fd.append('_method', 'PUT')
      await api.post(`/radiologi/results/${route.params.id}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      toast.success('Hasil radiologi diperbarui, PDF telah digenerate ulang!')
    } else {
      await api.post('/radiologi/results', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      toast.success('Hasil radiologi disimpan, PDF telah digenerate!')
    }
    setTimeout(() => router.push('/radiologi'), 2000)
  } catch (e) { submitError.value = e.response?.data?.message || 'Gagal menyimpan' }
  finally { submitting.value = false }
}

onMounted(async () => {
  try {
    const res = await api.get(`/registrations/${route.params.id}`)
    registration.value = res.data.data
    const radResult = res.data.data.radiology_result
    if (radResult) {
      isEdit.value = true
      form.interpretasi = radResult.interpretasi || ''
    }
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>
