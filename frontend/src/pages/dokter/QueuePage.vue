<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-gray-800">Antrian Pasien Hari Ini</h1>
        <span v-if="isPolling" class="flex items-center gap-1.5 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full">
          <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse" />
          Live
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="refreshing = true; fetchQueue()" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors" title="Refresh">
          <svg class="w-4 h-4" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        </button>
        <router-link to="/dokter/riwayat"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
          Riwayat
        </router-link>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">Memuat...</div>

    <div v-else-if="queue.length === 0" class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
      Tidak ada pasien untuk hari ini.
    </div>

    <div v-else class="space-y-4">
      <div v-for="reg in queue" :key="reg.id" class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">{{ reg.user?.name }}</h3>
            <p class="text-sm text-gray-500">{{ reg.user?.nip || '-' }} | {{ reg.package?.nama_paket }}</p>
          </div>
          <router-link :to="`/dokter/exam/${reg.id}`"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
            Periksa
          </router-link>
        </div>
        <div class="text-xs text-gray-400">Terdaftar: {{ formatDate(reg.created_at) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../utils/axios'
import { usePolling } from '../../composables/usePolling'

const queue = ref([])
const loading = ref(true)
const refreshing = ref(false)

const { isPolling, start } = usePolling(fetchQueue, 10000)

function formatDate(d) { return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }

async function fetchQueue() {
  try { const res = await api.get('/dokter/queue'); queue.value = res.data.data }
  catch (e) { console.error(e) }
  finally { refreshing.value = false }
}

onMounted(async () => {
  await fetchQueue()
  loading.value = false
  start()
})
</script>
