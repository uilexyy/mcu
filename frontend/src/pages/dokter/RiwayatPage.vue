<template>
  <div>
    <PageHeader title="Riwayat Pemeriksaan">
      <template #actions>
        <BaseInput v-model="search" placeholder="Cari pasien..." @input="fetchData" size="sm">
          <template #prepend>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </template>
        </BaseInput>
        <BaseSelect v-model="filterStatus" @change="page = 1; fetchData()" :options="statusOptions" placeholder="Semua Status" />
      </template>
    </PageHeader>

    <div v-if="loading" class="space-y-4">
      <SkeletonCard v-for="i in 3" :key="i" :lines="3" has-header />
    </div>

    <BaseCard v-else-if="registrations.length === 0" no-padding>
      <EmptyState title="Belum ada riwayat" description="Anda belum melakukan pemeriksaan terhadap pasien." />
    </BaseCard>

    <div v-else class="space-y-4">
      <BaseCard v-for="reg in registrations" :key="reg.id">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-gray-800">{{ reg.user?.name }}</h3>
            <p class="text-sm text-gray-500">{{ reg.user?.nip || '-' }} | {{ reg.package?.nama_paket }}</p>
          </div>
          <BadgeStatus :status="reg.status" />
        </div>

        <div class="mt-3 flex gap-2">
          <router-link :to="`/dokter/exam/${reg.id}`"
            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
            Detail & Edit
          </router-link>
        </div>

        <div class="mt-2 text-xs text-gray-400">Diperiksa: {{ formatDate(reg.physical_exam?.created_at || reg.created_at) }}</div>
      </BaseCard>

      <div v-if="pagination" class="flex items-center justify-between pt-4 border-t border-gray-100 text-sm text-gray-500">
        <p>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</p>
        <div class="flex gap-2">
          <BaseButton variant="secondary" size="sm" :disabled="!pagination.prev_page_url" @click="page = pagination.current_page - 1; fetchData()">Prev</BaseButton>
          <BaseButton variant="secondary" size="sm" :disabled="!pagination.next_page_url" @click="page = pagination.current_page + 1; fetchData()">Next</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../utils/axios'
import PageHeader from '../../components/PageHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import BaseButton from '../../components/BaseButton.vue'
import BadgeStatus from '../../components/BadgeStatus.vue'
import SkeletonCard from '../../components/SkeletonCard.vue'
import EmptyState from '../../components/EmptyState.vue'

const registrations = ref([])
const search = ref('')
const filterStatus = ref('')
const loading = ref(true)
const page = ref(1)
const pagination = ref(null)

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'doctor_done', label: 'Fisik Selesai' },
  { value: 'lab_done', label: 'Lab Selesai' },
  { value: 'radiology_done', label: 'Radiologi Selesai' },
  { value: 'completed', label: 'Selesai' },
]

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchData() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (filterStatus.value) params.status = filterStatus.value
    if (search.value) params.search = search.value
    const res = await api.get('/dokter/riwayat', { params })
    registrations.value = res.data.data
    pagination.value = res.data.meta
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>