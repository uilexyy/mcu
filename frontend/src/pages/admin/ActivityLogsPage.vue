<template>
  <div>
    <PageHeader title="Log Aktivitas" subtitle="Riwayat semua aktivitas dalam sistem" />

    <!-- Filters -->
    <BaseCard class="mb-6">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
          <SearchInput v-model="filters.search" placeholder="Cari user atau deskripsi..." />
        </div>
        <div class="w-48">
          <BaseSelect v-model="filters.action" label="Aksi" :options="actionOptions" @change="fetchLogs" />
        </div>
        <BaseButton variant="secondary" @click="resetFilters">Reset</BaseButton>
      </div>
    </BaseCard>

    <!-- Table -->
    <BaseCard>
      <LoadingSpinner v-if="loading" />
      <EmptyState v-else-if="logs.length === 0" title="Belum ada aktivitas" />
      <div v-else class="overflow-x-auto -mx-6">
        <table class="w-full text-sm table-striped table-sticky">
          <thead class="bg-gray-50 dark:bg-slate-700/50 border-y border-gray-100 dark:border-slate-700">
            <tr>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Waktu</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">User</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Aksi</th>
              <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Deskripsi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
            <template v-for="log in logs" :key="log.id">
              <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors" @click="toggleExpand(log.id)">
                <td class="px-6 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
                <td class="px-6 py-3">
                  <span class="font-medium text-gray-800 dark:text-slate-200">{{ log.user?.name || 'System' }}</span>
                  <span v-if="log.user" class="text-xs text-gray-400 dark:text-slate-500 ml-1">({{ log.user.role }})</span>
                </td>
                <td class="px-6 py-3">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="badgeClass(log.action)">
                    {{ labelAction(log.action) }}
                  </span>
                </td>
                <td class="px-6 py-3">
                  <div class="flex items-center gap-2">
                    <div>
                      <span class="text-gray-600 dark:text-slate-300">{{ log.description }}</span>
                      <div v-if="hasChanges(log)" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                        {{ changeSummary(log) }}
                      </div>
                    </div>
                    <button v-if="hasChanges(log)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors flex-shrink-0">
                      <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expanded[log.id] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="expanded[log.id] && hasChanges(log)">
                <td colspan="4" class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-b border-gray-100 dark:border-slate-700">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div v-if="log.old_values">
                      <p class="font-semibold text-red-600 mb-1">Sebelum</p>
                      <pre class="bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 p-2 rounded-lg overflow-auto max-h-40 whitespace-pre-wrap">{{ formatValues(log.old_values) }}</pre>
                    </div>
                    <div v-if="log.new_values">
                      <p class="font-semibold text-emerald-600 mb-1">Sesudah</p>
                      <pre class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 p-2 rounded-lg overflow-auto max-h-40 whitespace-pre-wrap">{{ formatValues(log.new_values) }}</pre>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <Pagination :meta="metaRaw" @page-change="goPage" />
    </BaseCard>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import api from '../../utils/axios'
import PageHeader from '../../components/PageHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import SearchInput from '../../components/SearchInput.vue'
import BaseButton from '../../components/BaseButton.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import EmptyState from '../../components/EmptyState.vue'
import Pagination from '../../components/Pagination.vue'

const loading = ref(false)
const logs = ref([])
const expanded = reactive({})
const metaRaw = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 15, prev_page_url: null, next_page_url: null })
const filters = reactive({ search: '', action: '' })

let searchTimer = null

const actionOptions = [
  { value: '', label: 'Semua Aksi' },
  { value: 'status_changed', label: 'Status Berubah' },
  { value: 'physical_exam_created', label: 'Pemeriksaan Fisik Dibuat' },
  { value: 'physical_exam_updated', label: 'Pemeriksaan Fisik Diubah' },
  { value: 'lab_results_created', label: 'Hasil Lab Dibuat' },
  { value: 'lab_results_updated', label: 'Hasil Lab Diubah' },
  { value: 'radiology_created', label: 'Hasil Radiologi Dibuat' },
  { value: 'radiology_updated', label: 'Hasil Radiologi Diubah' },
  { value: 'created', label: 'Dibuat' },
  { value: 'updated', label: 'Diubah' },
  { value: 'deleted', label: 'Dihapus' },
]

function labelAction(action) {
  const map = {
    status_changed: 'Status',
    physical_exam_created: 'Fisik',
    physical_exam_updated: 'Fisik',
    lab_results_created: 'Lab',
    lab_results_updated: 'Lab',
    radiology_created: 'Radio',
    radiology_updated: 'Radio',
    created: 'Buat',
    updated: 'Ubah',
    deleted: 'Hapus',
  }
  return map[action] || action
}

function badgeClass(action) {
  if (action.includes('created') || action === 'created') return 'bg-emerald-50 text-emerald-700'
  if (action.includes('updated') || action === 'updated') return 'bg-emerald-50 text-emerald-700'
  if (action === 'deleted') return 'bg-red-50 text-red-700'
  return 'bg-gray-50 text-gray-700'
}

function hasChanges(log) {
  return log.old_values || log.new_values
}

function toggleExpand(id) {
  expanded[id] = !expanded[id]
}

function formatValues(values) {
  if (!values) return ''
  const obj = typeof values === 'string' ? JSON.parse(values) : values
  return JSON.stringify(obj, null, 2)
}

function changeSummary(log) {
  const oldV = log.old_values ? (typeof log.old_values === 'string' ? JSON.parse(log.old_values) : log.old_values) : {}
  const newV = log.new_values ? (typeof log.new_values === 'string' ? JSON.parse(log.new_values) : log.new_values) : {}

  if (log.action === 'status_changed') {
    return `${oldV.status || '?'} → ${newV.status || '?'}`
  }

  if (log.action === 'physical_exam_created') {
    const parts = []
    if (newV.tekanan_darah) parts.push('TD: ' + newV.tekanan_darah)
    if (newV.berat_badan) parts.push('BB: ' + newV.berat_badan + ' kg')
    if (newV.tinggi_badan) parts.push('TB: ' + newV.tinggi_badan + ' cm')
    if (newV.imt) parts.push('IMT: ' + newV.imt)
    return parts.join(', ') || 'data baru'
  }

  if (log.action === 'physical_exam_updated') {
    const changed = []
    const fields = {tekanan_darah: 'TD', berat_badan: 'BB', tinggi_badan: 'TB', imt: 'IMT', anamnesis: 'Anamnesis', catatan: 'Catatan'}
    for (const [key, label] of Object.entries(fields)) {
      if (oldV[key] !== undefined && newV[key] !== undefined && String(oldV[key]) !== String(newV[key])) {
        changed.push(`${label}: ${oldV[key]} → ${newV[key]}`)
      }
    }
    return changed.join(', ') || 'data diubah'
  }

  if (log.action === 'lab_results_created' && newV.results) {
    return `${newV.results.length} item hasil lab`
  }

  if (log.action === 'lab_results_updated') {
    const oldR = oldV.results || []
    const newR = newV.results || []
    return `${oldR.length} item → ${newR.length} item`
  }

  if (log.action === 'radiology_created') {
    const parts = []
    if (newV.interpretasi) parts.push('Interpretasi: ' + (newV.interpretasi.length > 30 ? newV.interpretasi.substring(0, 30) + '...' : newV.interpretasi))
    if (newV.foto) parts.push('+ Foto')
    return parts.join(', ') || 'data baru'
  }

  if (log.action === 'radiology_updated') {
    const changed = []
    if (oldV.interpretasi !== newV.interpretasi) changed.push('Interpretasi diubah')
    if (oldV.foto !== newV.foto) changed.push('Foto ' + (newV.foto ? 'ditambahkan' : 'dihapus'))
    return changed.join(', ') || 'data diubah'
  }

  return ''
}

function formatDate(d) {
  if (!d) return '-'
  const date = new Date(d)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function goPage(page) {
  fetchLogs(page)
}

async function fetchLogs(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filters.search) params.search = filters.search
    if (filters.action) params.action = filters.action
    const res = await api.get('/admin/activity-logs', { params })
    logs.value = res.data.data
    metaRaw.current_page = res.data.meta?.current_page || res.data.current_page
    metaRaw.last_page = res.data.meta?.last_page || res.data.last_page
    metaRaw.total = res.data.meta?.total || res.data.total
    metaRaw.per_page = res.data.meta?.per_page || res.data.per_page || 15
    meta.prev_page_url = res.data.links?.prev || null
    meta.next_page_url = res.data.links?.next || null
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.search = ''
  filters.action = ''
  fetchLogs()
}

onMounted(() => fetchLogs())
</script>
