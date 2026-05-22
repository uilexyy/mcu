<template>
  <div>
    <PageHeader title="Riwayat MCU">
      <template #actions>
        <div class="flex gap-2">
          <BaseInput v-model="search" placeholder="Cari paket..." @input="page = 1; fetchData()" size="sm">
            <template #prepend>
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </template>
          </BaseInput>
          <BaseSelect v-model="filterStatus" @change="page = 1; fetchData()" :options="statusOptions" placeholder="Semua Status" />
        </div>
      </template>
    </PageHeader>

    <div v-if="loading" class="space-y-4">
      <SkeletonCard v-for="i in 3" :key="i" :lines="3" has-header />
    </div>

    <BaseCard v-else-if="registrations.length === 0" no-padding>
      <EmptyState title="Belum ada riwayat" description="Anda belum mendaftar Medical Check Up.">
        <router-link to="/daftar-mcu" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
          Daftar MCU Sekarang
        </router-link>
      </EmptyState>
    </BaseCard>

        <div v-else class="space-y-4">
      <BaseCard v-for="reg in registrations" :key="reg.id">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">{{ reg.package?.nama_paket }}</h3>
            <p class="text-sm text-gray-500">{{ formatDate(reg.created_at) }}</p>
          </div>
          <BadgeStatus :status="reg.status" />
        </div>

        <div v-if="reg.tanggal_jadwal" class="text-sm text-gray-600 mb-2">
          Jadwal: {{ formatDate(reg.tanggal_jadwal) }}
        </div>

        <div v-if="reg.status === 'completed' && reg.result?.pdf_url" class="mt-3">
          <a :href="reg.result.pdf_url" target="_blank"
            class="inline-flex items-center text-sm text-green-600 hover:text-green-700 font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download PDF
          </a>
        </div>
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
  { value: 'pending', label: 'Menunggu' },
  { value: 'approved', label: 'Disetujui' },
  { value: 'doctor_done', label: 'Fisik Selesai' },
  { value: 'lab_done', label: 'Lab Selesai' },
  { value: 'radiology_done', label: 'Radiologi Selesai' },
  { value: 'completed', label: 'Selesai' },
  { value: 'rejected', label: 'Ditolak' },
]

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

async function fetchData() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (filterStatus.value) params.status = filterStatus.value
    if (search.value) params.search = search.value
    const res = await api.get('/karyawan/registrations', { params })
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
