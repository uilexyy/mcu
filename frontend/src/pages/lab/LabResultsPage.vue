<template>
  <div>
    <router-link to="/lab" class="inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700 mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Kembali
    </router-link>

    <LoadingSpinner v-if="loading" />
    <EmptyState v-else-if="!registration" title="Data tidak ditemukan" />

    <BaseCard v-else>
      <template #header>
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Hasil Laboratorium</h1>
            <span v-if="isEdit" class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Edit</span>
          </div>
          <p class="text-sm text-gray-500 mt-0.5">{{ registration.user?.name }} — {{ registration.package?.nama_paket }}</p>
        </div>
      </template>

      <form @submit.prevent="handleSubmit" class="space-y-3">
        <div v-for="item in packageItems" :key="item.id" class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-700">{{ item.nama_pemeriksaan }}</p>
            <p class="text-xs text-gray-400">Normal: {{ item.nilai_normal || '-' }}</p>
          </div>
          <input v-model="results[item.id].nilai" placeholder="Hasil"
            class="w-20 px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-purple-500 outline-none" />
          <select v-model="results[item.id].keterangan"
            class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 outline-none bg-white">
            <option value="">-</option>
            <option value="Normal">Normal</option>
            <option value="Tinggi">Tinggi</option>
            <option value="Rendah">Rendah</option>
          </select>
        </div>

        <div v-if="submitError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg border border-red-200">{{ submitError }}</div>
        <div v-if="success" class="bg-emerald-50 text-emerald-600 text-sm px-4 py-2.5 rounded-lg border border-emerald-200">{{ success }}</div>

        <BaseButton type="submit" variant="success" class="w-full" :loading="submitting">
          {{ isEdit ? 'Perbarui Hasil Lab' : 'Simpan Hasil Lab' }}
        </BaseButton>
      </form>
    </BaseCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
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
const results = ref({})
const isEdit = ref(false)

const packageItems = computed(() => registration.value?.package?.items || [])

async function handleSubmit() {
  submitting.value = true; submitError.value = ''; success.value = ''
  try {
    const payload = {
      registration_id: Number(route.params.id),
      results: Object.entries(results.value).map(([item_id, val]) => ({ item_id: Number(item_id), nilai: val.nilai || null, keterangan: val.keterangan || null })),
    }
    if (isEdit.value) {
      await api.put(`/lab/results/${route.params.id}`, payload)
      toast.success('Hasil laboratorium berhasil diperbarui!')
    } else {
      await api.post('/lab/results', payload)
      toast.success('Hasil laboratorium berhasil disimpan!')
    }
    setTimeout(() => router.push('/lab'), 1500)
  } catch (e) { submitError.value = e.response?.data?.message || 'Gagal menyimpan' }
  finally { submitting.value = false }
}

onMounted(async () => {
  try {
    const res = await api.get(`/registrations/${route.params.id}`)
    registration.value = res.data.data
    const init = {}
    for (const item of (registration.value.package?.items || [])) {
      const existing = res.data.data.lab_results?.find(r => r.item?.id === item.id)
      init[item.id] = {
        nilai: existing?.nilai || '',
        keterangan: existing?.keterangan || '',
      }
    }
    results.value = init
    if (res.data.data.lab_results?.length > 0) {
      isEdit.value = true
    }
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>
