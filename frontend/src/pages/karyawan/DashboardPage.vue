<template>
  <div>
    <PageHeader title="Dashboard Karyawan" :subtitle="'Selamat datang, ' + (auth.user?.name || '')" />

    <template v-if="loading">
      <SkeletonCard :lines="2" class="mb-4" />
      <SkeletonCard :lines="4" has-header />
    </template>

    <template v-else>
      <BaseCard class="mb-6">
        <p class="text-gray-600 mb-1">Selamat datang, <strong>{{ auth.user?.name }}</strong></p>
        <p class="text-sm text-gray-500">Perusahaan: {{ auth.user?.departemen || '-' }}</p>
      </BaseCard>

      <BaseCard v-if="latestRegistration" class="mb-6">
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Status Pendaftaran Terkini</h2>
            <BadgeStatus :status="latestRegistration.status" />
          </div>
        </template>

        <div class="space-y-2 text-sm text-gray-600 dark:text-slate-300">
          <p>Paket: <strong class="text-gray-800 dark:text-slate-100">{{ latestRegistration.package?.nama_paket }}</strong></p>
          <p v-if="latestRegistration.tanggal_jadwal">Jadwal: <strong class="text-gray-800 dark:text-slate-100">{{ formatDate(latestRegistration.tanggal_jadwal) }}</strong></p>
          <p v-if="latestRegistration.catatan_admin" class="text-gray-500 dark:text-slate-400">Catatan: {{ latestRegistration.catatan_admin }}</p>
        </div>

        <div v-if="latestRegistration.status === 'completed' && latestRegistration.result?.pdf_url" class="mt-4">
          <a :href="latestRegistration.result.pdf_url" target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Hasil MCU (PDF)
          </a>
        </div>
      </BaseCard>

      <BaseCard v-else>
        <EmptyState title="Belum ada pendaftaran" description="Anda belum mendaftar Medical Check Up.">
          <router-link to="/daftar-mcu" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
            Daftar MCU Sekarang
          </router-link>
        </EmptyState>
      </BaseCard>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../utils/axios'
import PageHeader from '../../components/PageHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BadgeStatus from '../../components/BadgeStatus.vue'
import SkeletonCard from '../../components/SkeletonCard.vue'
import EmptyState from '../../components/EmptyState.vue'

const auth = useAuthStore()
const registrations = ref([])
const loading = ref(true)
const latestRegistration = computed(() => registrations.value[0] || null)

function formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' }

onMounted(async () => {
  try {
    const res = await api.get('/karyawan/registrations')
    registrations.value = res.data.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>
