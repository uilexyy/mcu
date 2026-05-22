<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', badgeClass]">
    <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', dotClass]"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, default: '' },
})

const statusMap = {
  pending: { label: 'Menunggu', bg: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20', dot: 'bg-yellow-400' },
  approved: { label: 'Disetujui', bg: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', dot: 'bg-emerald-400' },
  doctor_done: { label: 'Fisik Selesai', bg: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', dot: 'bg-emerald-400' },
  lab_done: { label: 'Lab Selesai', bg: 'bg-purple-50 text-purple-700 ring-purple-600/20', dot: 'bg-purple-400' },
  radiology_done: { label: 'Radio Selesai', bg: 'bg-pink-50 text-pink-700 ring-pink-600/20', dot: 'bg-pink-400' },
  completed: { label: 'Selesai', bg: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', dot: 'bg-emerald-400' },
  rejected: { label: 'Ditolak', bg: 'bg-red-50 text-red-700 ring-red-600/20', dot: 'bg-red-400' },
}

const config = computed(() => statusMap[props.status] || { label: props.status, bg: 'bg-gray-50 text-gray-700 ring-gray-500/20', dot: 'bg-gray-400' })
const label = computed(() => config.value.label)
const badgeClass = computed(() => `${config.value.bg} ring-1 ring-inset`)
const dotClass = computed(() => config.value.dot)
</script>
