<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-5">
    <div class="space-y-3">
      <div v-if="hasHeader" class="h-5 shimmer rounded w-1/3" />
      <div class="h-8 shimmer rounded w-1/4" />
      <div class="h-3 shimmer rounded w-1/2" />
      <div v-if="lines > 1" v-for="i in lines - 1" :key="i" class="h-3 shimmer rounded" :class="i % 2 === 0 ? 'w-3/4' : 'w-2/3'" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  lines: { type: Number, default: 3 },
  hasHeader: { type: Boolean, default: false },
})
</script>
