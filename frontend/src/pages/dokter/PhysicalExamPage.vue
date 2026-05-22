<template>
  <div>
    <router-link to="/dokter" class="inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700 mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Kembali ke Antrian
    </router-link>

    <LoadingSpinner v-if="loading" />
    <EmptyState v-else-if="error" :title="error" />

    <BaseCard v-else>
      <template #header>
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Pemeriksaan Fisik</h1>
            <span v-if="isEdit" class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Edit</span>
          </div>
          <p class="text-sm text-gray-500 mt-0.5">{{ registration.user?.name }} — {{ registration.package?.nama_paket }}</p>
        </div>
      </template>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <BaseInput v-model="form.tekanan_darah" label="Tekanan Darah" placeholder="120/80" />
          <BaseInput v-model.number="form.berat_badan" label="Berat Badan (kg)" type="number" step="0.01" />
          <BaseInput v-model.number="form.tinggi_badan" label="Tinggi Badan (cm)" type="number" step="0.01" />
          <BaseInput v-model.number="form.imt" label="IMT" type="number" step="0.01" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Anamnesis</label>
          <textarea v-model="form.anamnesis" rows="3" placeholder="Keluhan pasien..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label>
          <textarea v-model="form.catatan" rows="2" placeholder="Catatan dokter..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none" />
        </div>

        <div v-if="submitError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg border border-red-200">{{ submitError }}</div>
        <div v-if="success" class="bg-emerald-50 text-emerald-600 text-sm px-4 py-2.5 rounded-lg border border-emerald-200">{{ success }}</div>

        <BaseButton type="submit" variant="primary" class="w-full" :loading="submitting">
          {{ isEdit ? 'Perbarui Hasil Pemeriksaan' : 'Simpan Hasil Pemeriksaan' }}
        </BaseButton>
      </form>
    </BaseCard>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../utils/axios'
import BaseCard from '../../components/BaseCard.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseButton from '../../components/BaseButton.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import EmptyState from '../../components/EmptyState.vue'
import { useToastStore } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const registration = ref(null)
const loading = ref(true)
const error = ref('')
const submitting = ref(false)
const submitError = ref('')
const success = ref('')
const isEdit = ref(false)

const form = reactive({ tekanan_darah: '', berat_badan: '', tinggi_badan: '', imt: '', anamnesis: '', catatan: '' })

async function handleSubmit() {
  submitting.value = true; submitError.value = ''; success.value = ''
  try {
    if (isEdit.value) {
      await api.put(`/dokter/physical-exam/${route.params.id}`, { registration_id: Number(route.params.id), ...form })
      toast.success('Hasil pemeriksaan berhasil diperbarui!')
    } else {
      await api.post('/dokter/physical-exam', { registration_id: Number(route.params.id), ...form })
      toast.success('Hasil pemeriksaan berhasil disimpan!')
    }
    setTimeout(() => router.push('/dokter'), 1500)
  } catch (e) { submitError.value = e.response?.data?.message || 'Gagal menyimpan' }
  finally { submitting.value = false }
}

onMounted(async () => {
  try {
    const res = await api.get(`/dokter/registrations/${route.params.id}/history`)
    registration.value = res.data.data
    const exam = res.data.data.physical_exam
    if (exam) {
      isEdit.value = true
      form.tekanan_darah = exam.tekanan_darah || ''
      form.berat_badan = exam.berat_badan || ''
      form.tinggi_badan = exam.tinggi_badan || ''
      form.imt = exam.imt || ''
      form.anamnesis = exam.anamnesis || ''
      form.catatan = exam.catatan || ''
    }
  } catch (e) { error.value = 'Data tidak ditemukan' }
  finally { loading.value = false }
})
</script>
