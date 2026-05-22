<template>
  <div>
    <PageHeader title="Pendaftaran MCU">
      <template #actions>
        <div class="flex gap-2">
          <SearchInput v-model="search" placeholder="Cari nama/NIP..." />
          <BaseSelect v-model="filterStatus" @change="fetchData" :options="statusOptions" placeholder="Semua Status" />
          <button @click="exportCsv" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors" :disabled="exporting">
            <svg class="w-4 h-4" :class="{ 'animate-spin': exporting }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            {{ exporting ? 'Mengunduh...' : 'Export Excel' }}
          </button>
        </div>
      </template>
    </PageHeader>

    <SkeletonTable v-if="loading" />

    <BaseCard v-else-if="registrations.length === 0" no-padding>
      <EmptyState title="Belum ada pendaftaran" description="Belum ada karyawan yang mendaftar MCU." />
    </BaseCard>

    <BaseCard v-else no-padding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm table-striped table-sticky">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50 dark:bg-slate-700/50">
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Karyawan</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Paket</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Tanggal Daftar</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Status</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
            <tr v-for="reg in registrations" :key="reg.id" class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-800 dark:text-slate-200">{{ reg.user?.name }}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400">{{ reg.user?.nip || '-' }}</p>
              </td>
              <td class="px-6 py-4 text-gray-600 dark:text-slate-300">{{ reg.package?.nama_paket }}</td>
              <td class="px-6 py-4 text-gray-500 dark:text-slate-400 text-xs whitespace-nowrap">{{ formatDate(reg.created_at) }}</td>
              <td class="px-6 py-4"><BadgeStatus :status="reg.status" /></td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <router-link :to="`/admin/registrations/${reg.id}`" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">Detail</router-link>
                  <button v-if="reg.status === 'completed'" @click="downloadPdf(reg)" class="text-blue-600 hover:text-blue-700 font-medium text-sm">PDF</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :meta="pagination" @page-change="page = $event; fetchData()" />
    </BaseCard>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../utils/axios'
import PageHeader from '../../components/PageHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import SearchInput from '../../components/SearchInput.vue'
import BadgeStatus from '../../components/BadgeStatus.vue'
import SkeletonTable from '../../components/SkeletonTable.vue'
import EmptyState from '../../components/EmptyState.vue'
import Pagination from '../../components/Pagination.vue'

const registrations = ref([])
const search = ref('')
const filterStatus = ref('')
const page = ref(1)
const loading = ref(true)
const pagination = ref(null)
const exporting = ref(false)

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'pending', label: 'Menunggu' },
  { value: 'approved', label: 'Disetujui' },
  { value: 'doctor_done', label: 'Fisik' },
  { value: 'lab_done', label: 'Lab' },
  { value: 'radiology_done', label: 'Radiologi' },
  { value: 'completed', label: 'Selesai' },
  { value: 'rejected', label: 'Ditolak' },
]

function formatDate(d) { return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }

async function downloadPdf(reg) {
  try {
    const res = await api.get(`/admin/registrations/${reg.id}/download`)
    if (res.data.pdf_url) window.open(res.data.pdf_url, '_blank')
  } catch (e) { console.error(e) }
}

async function exportCsv() {
  exporting.value = true
  try {
    const params = {}
    if (filterStatus.value) params.status = filterStatus.value
    if (search.value) params.search = search.value
    const res = await api.get('/admin/export/registrations', { params, responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = 'rekap-mcu.xlsx'
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) { console.error(e) }
  finally { exporting.value = false }
}

async function fetchData() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 15 }
    if (filterStatus.value) params.status = filterStatus.value
    if (search.value) params.search = search.value
    const res = await api.get('/admin/registrations', { params })
    registrations.value = res.data.data
    pagination.value = res.data.meta
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

onMounted(fetchData)
</script>
