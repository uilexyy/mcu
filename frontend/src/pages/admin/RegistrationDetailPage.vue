<template>
  <div>
    <router-link to="/admin/registrations" class="inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700 mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Kembali
    </router-link>

    <LoadingSpinner v-if="loading" />

    <div v-else-if="registration">
      <div class="flex items-center gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Pendaftaran</h1>
        <BadgeStatus :status="registration.status" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Data -->
        <div class="lg:col-span-2 space-y-6">
          <BaseCard>
            <template #header>
              <h2 class="font-semibold text-gray-800 dark:text-white">Data Karyawan</h2>
            </template>
            <table class="text-sm w-full">
              <tr class="border-b border-gray-50 dark:border-slate-700"><td class="pr-4 py-2.5 text-gray-500 dark:text-slate-400 w-32">Nama</td><td class="font-medium text-gray-800 dark:text-slate-200">{{ registration.user?.name }}</td></tr>
              <tr class="border-b border-gray-50 dark:border-slate-700"><td class="pr-4 py-2.5 text-gray-500 dark:text-slate-400">NIP</td><td class="font-medium text-gray-800 dark:text-slate-200">{{ registration.user?.nip || '-' }}</td></tr>
              <tr class="border-b border-gray-50 dark:border-slate-700"><td class="pr-4 py-2.5 text-gray-500 dark:text-slate-400">Perusahaan</td><td class="font-medium text-gray-800 dark:text-slate-200">{{ registration.user?.departemen || '-' }}</td></tr>
              <tr><td class="pr-4 py-2.5 text-gray-500 dark:text-slate-400">Paket MCU</td><td class="font-medium text-gray-800 dark:text-slate-200">{{ registration.package?.nama_paket }}</td></tr>
            </table>
          </BaseCard>

          <BaseCard v-if="registration.status === 'pending'">
            <template #header>
              <h2 class="font-semibold text-gray-800 dark:text-white">Proses Pendaftaran</h2>
            </template>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tanggal Jadwal MCU</label>
                <input v-model="jadwal" type="date" class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Catatan</label>
                <textarea v-model="catatan" rows="2" class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
              </div>
              <div class="flex space-x-3">
                <button @click="approve" :disabled="approving" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 text-sm font-medium transition-colors">
                  {{ approving ? 'Memproses...' : 'Setujui' }}
                </button>
                <button @click="reject" :disabled="rejecting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 text-sm font-medium transition-colors">
                  {{ rejecting ? 'Memproses...' : 'Tolak' }}
                </button>
              </div>
              <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
              <p v-if="success" class="text-emerald-600 text-sm">{{ success }}</p>
            </div>
          </BaseCard>
        </div>

        <!-- Right: Timeline -->
        <div class="lg:col-span-1">
          <BaseCard>
            <template #header>
              <h2 class="font-semibold text-gray-800 dark:text-white">Status MCU</h2>
            </template>
            <div class="space-y-0">
              <div v-for="(step, i) in timelineSteps" :key="i" class="relative flex gap-4 pb-6 last:pb-0">
                <!-- Line -->
                <div v-if="i < timelineSteps.length - 1"
                  class="absolute left-[15px] top-8 bottom-0 w-0.5"
                  :class="step.done ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-slate-600'"
                />
                <!-- Dot -->
                <div class="relative z-10 flex-shrink-0">
                  <div v-if="step.done" class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <div v-else-if="step.active" class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center ring-4 ring-emerald-50 dark:ring-emerald-900/20">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div v-else class="w-8 h-8 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                    <span class="text-xs font-semibold text-gray-400 dark:text-slate-500">{{ i + 1 }}</span>
                  </div>
                </div>
                <!-- Content -->
                <div class="flex-1 min-w-0 pt-1">
                  <p class="text-sm font-medium" :class="step.done ? 'text-emerald-700 dark:text-emerald-400' : step.active ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-400 dark:text-slate-500'">
                    {{ step.label }}
                  </p>
                  <p v-if="step.detail" class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ step.detail }}</p>
                </div>
              </div>
            </div>
          </BaseCard>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../utils/axios'
import BaseCard from '../../components/BaseCard.vue'
import BadgeStatus from '../../components/BadgeStatus.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'

const route = useRoute()
const registration = ref(null)
const loading = ref(true)
const jadwal = ref('')
const catatan = ref('')
const approving = ref(false)
const rejecting = ref(false)
const error = ref('')
const success = ref('')

const statusFlow = ['pending', 'approved', 'doctor_done', 'lab_done', 'radiology_done', 'completed']

const flowLabels = {
  pending: { label: 'Mendaftar', desc: 'Menunggu persetujuan admin' },
  approved: { label: 'Disetujui', desc: 'Jadwal sudah ditentukan' },
  doctor_done: { label: 'Pemeriksaan Dokter', desc: 'Pemeriksaan fisik selesai' },
  lab_done: { label: 'Laboratorium', desc: 'Hasil lab sudah diinput' },
  radiology_done: { label: 'Radiologi', desc: 'Hasil radiologi sudah diinput' },
  completed: { label: 'Selesai', desc: 'PDF hasil MCU tersedia' },
}

const timelineSteps = computed(() => {
  const current = registration.value?.status
  const isRejected = current === 'rejected'

  return statusFlow.map((s, i) => {
    const idx = statusFlow.indexOf(current)
    return {
      label: isRejected && s === 'rejected' ? 'Ditolak' : flowLabels[s]?.label || s,
      detail: isRejected && s === 'rejected' ? registration.value?.catatan_admin : flowLabels[s]?.desc,
      done: !isRejected ? i < idx : (i < idx && s !== current),
      active: !isRejected ? s === current : false,
    }
  })
})

async function approve() {
  if (!jadwal.value) { error.value = 'Tanggal jadwal harus diisi'; return }
  approving.value = true; error.value = ''; success.value = ''
  try {
    const res = await api.put(`/admin/registrations/${route.params.id}/approve`, { tanggal_jadwal: jadwal.value, catatan_admin: catatan.value })
    registration.value = res.data.data
    success.value = 'Pendaftaran telah disetujui'
  } catch (e) { error.value = e.response?.data?.message || 'Gagal menyetujui' }
  finally { approving.value = false }
}

async function reject() {
  if (!catatan.value) { error.value = 'Catatan penolakan harus diisi'; return }
  rejecting.value = true; error.value = ''; success.value = ''
  try {
    const res = await api.put(`/admin/registrations/${route.params.id}/reject`, { catatan_admin: catatan.value })
    registration.value = res.data.data
    success.value = 'Pendaftaran ditolak'
  } catch (e) { error.value = e.response?.data?.message || 'Gagal menolak' }
  finally { rejecting.value = false }
}

onMounted(async () => {
  try {
    const res = await api.get(`/registrations/${route.params.id}`)
    registration.value = res.data.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>
