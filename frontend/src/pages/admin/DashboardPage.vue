<template>
  <div>
    <PageHeader title="Dashboard Admin" subtitle="Overview data Medical Check Up" />

    <template v-if="loading">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <SkeletonCard v-for="i in 8" :key="i" :lines="2" />
      </div>
    </template>

    <template v-else>
      <!-- Summary cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div v-for="card in summaryCards" :key="card.key"
          class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" :style="{ background: card.bg }">
              <span v-html="card.icon" class="w-5 h-5" :style="{ color: card.color }" />
            </div>
          </div>
          <p class="text-2xl font-bold" :style="{ color: card.color }">{{ summary[card.key] ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-0.5">{{ card.label }}</p>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <BaseCard>
          <template #header>
            <h2 class="font-semibold text-gray-800">Tren Pendaftaran (12 Bulan)</h2>
          </template>
          <div v-if="monthly.length === 0" class="text-gray-400 text-sm text-center py-8">Belum ada data</div>
          <div v-else class="relative" style="height: 220px">
            <canvas ref="chartRef" />
          </div>
        </BaseCard>

        <BaseCard>
          <template #header>
            <h2 class="font-semibold text-gray-800">Pendaftaran per Paket</h2>
          </template>
          <div v-if="byPackage.length === 0" class="text-gray-400 text-sm text-center py-8">Belum ada data</div>
          <div v-else class="space-y-4">
            <div v-for="(item, i) in byPackage" :key="i">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-700 truncate">{{ item.nama_paket }}</span>
                <span class="font-semibold">{{ item.total }}</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all duration-700" :style="{ width: pctBar(item.total) + '%', background: barColor(i) }" />
              </div>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Pipeline -->
      <BaseCard class="mb-8">
        <template #header>
          <h2 class="font-semibold text-gray-800">Pipeline Status</h2>
        </template>
        <div class="flex items-center flex-wrap gap-2 md:gap-0">
          <div v-for="(stage, i) in pipeline" :key="stage.key" class="flex items-center">
            <div class="px-3 py-2 rounded-lg text-sm font-medium text-center min-w-[100px]" :class="stage.activeClass">
              <div class="text-lg font-bold">{{ summary[stage.key] ?? 0 }}</div>
              <div class="text-xs">{{ stage.label }}</div>
            </div>
            <div v-if="i < pipeline.length - 1" class="text-gray-300 mx-1 hidden md:block">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Recent registrations -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Pendaftaran Terbaru</h2>
            <router-link to="/admin/registrations" class="text-sm text-emerald-600 hover:underline">Lihat Semua</router-link>
          </div>
        </template>
        <EmptyState v-if="recent.length === 0" title="Belum ada pendaftaran" />
          <div v-else class="overflow-x-auto -mx-6">
          <table class="w-full text-sm table-striped table-sticky">
            <thead class="bg-gray-50 dark:bg-slate-700/50 border-y border-gray-100 dark:border-slate-700">
              <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Nama</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Paket</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Tanggal</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
              <tr v-for="reg in recent" :key="reg.id" class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-6 py-3 font-medium text-gray-800 dark:text-slate-200">{{ reg.user_name || reg.user?.name }}</td>
                <td class="px-6 py-3 text-gray-600 dark:text-slate-300">{{ reg.package_name || reg.package?.nama_paket }}</td>
                <td class="px-6 py-3 text-gray-500 dark:text-slate-400">{{ formatDate(reg.created_at) }}</td>
                <td class="px-6 py-3"><BadgeStatus :status="reg.status" /></td>
              </tr>
            </tbody>
          </table>
        </div>
      </BaseCard>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import api from '../../utils/axios'
import PageHeader from '../../components/PageHeader.vue'
import BaseCard from '../../components/BaseCard.vue'
import BadgeStatus from '../../components/BadgeStatus.vue'
import EmptyState from '../../components/EmptyState.vue'
import SkeletonCard from '../../components/SkeletonCard.vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const loading = ref(true)
const summary = reactive({ total: 0, pending: 0, approved: 0, doctor_done: 0, lab_done: 0, radiology_done: 0, completed: 0, rejected: 0 })
const monthly = ref([])
const byPackage = ref([])
const recent = ref([])
const chartRef = ref(null)
let chartInstance = null

const colors = ['#059669', '#10b981', '#34d399', '#0d9488', '#14b8a6']

const icons = {
  total: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
  pending: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
  completed: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
  rejected: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
}

const summaryCards = [
  { key: 'total', label: 'Total Pendaftaran', color: '#065F46', bg: '#ECFDF5', icon: icons.total },
  { key: 'pending', label: 'Menunggu', color: '#B45309', bg: '#FEF3C7', icon: icons.pending },
  { key: 'approved', label: 'Disetujui', color: '#047857', bg: '#D1FAE5', icon: icons.pending },
  { key: 'doctor_done', label: 'Fisik Selesai', color: '#065F46', bg: '#ECFDF5', icon: icons.total },
  { key: 'lab_done', label: 'Lab Selesai', color: '#0D9488', bg: '#CCFBF1', icon: icons.total },
  { key: 'radiology_done', label: 'Radio Selesai', color: '#0F766E', bg: '#CCFBF1', icon: icons.total },
  { key: 'completed', label: 'Selesai', color: '#047857', bg: '#D1FAE5', icon: icons.completed },
  { key: 'rejected', label: 'Ditolak', color: '#DC2626', bg: '#FEE2E2', icon: icons.rejected },
]

const pipeline = [
  { key: 'pending', label: 'Mendaftar', activeClass: 'bg-yellow-50 text-yellow-700' },
  { key: 'approved', label: 'Disetujui', activeClass: 'bg-emerald-50 text-emerald-700' },
  { key: 'doctor_done', label: 'Fisik', activeClass: 'bg-emerald-50 text-emerald-600' },
  { key: 'lab_done', label: 'Lab', activeClass: 'bg-teal-50 text-teal-700' },
  { key: 'radiology_done', label: 'Radio', activeClass: 'bg-teal-50 text-teal-600' },
  { key: 'completed', label: 'Selesai', activeClass: 'bg-emerald-100 text-emerald-700' },
]

const maxPackage = computed(() => Math.max(...byPackage.value.map(m => m.total), 1))
function barColor(i) { return colors[i % colors.length] }
function pctBar(val) { return (val / maxPackage.value) * 100 }
function formatDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }

function buildChart() {
  if (!chartRef.value || monthly.value.length === 0) return
  if (chartInstance) chartInstance.destroy()

  const ctx = chartRef.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 220)
  gradient.addColorStop(0, '#059669')
  gradient.addColorStop(1, '#34d399')

  chartInstance = new Chart(chartRef.value, {
    type: 'bar',
    data: {
      labels: monthly.value.map(m => m.bulan?.substring(0, 3) || ''),
      datasets: [{
        label: 'Pendaftaran',
        data: monthly.value.map(m => m.total),
        backgroundColor: gradient,
        hoverBackgroundColor: '#047857',
        borderRadius: 6,
        borderSkipped: false,
        barPercentage: 0.6,
        categoryPercentage: 0.8,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 800, easing: 'easeOutQuart' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleColor: '#fff',
          bodyColor: '#fff',
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            label: ctx => `${ctx.parsed.y} pendaftaran`,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            color: '#94A3B8',
            font: { size: 11, family: 'Inter, sans-serif' },
            padding: 8,
          },
          grid: {
            color: '#F1F5F9',
            drawBorder: false,
          },
        },
        x: {
          ticks: {
            color: '#94A3B8',
            font: { size: 11, family: 'Inter, sans-serif' },
          },
          grid: { display: false },
        },
      },
    },
  })
}

onMounted(async () => {
  try {
    const res = await api.get('/admin/stats')
    const data = res.data.data
    Object.assign(summary, data.summary)
    monthly.value = data.monthly
    byPackage.value = data.by_package
    recent.value = data.recent
  } catch (e) { console.error(e) }
  finally {
    loading.value = false
    await nextTick()
    buildChart()
  }
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
})
</script>
