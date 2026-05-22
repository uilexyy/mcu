<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50 px-6 py-4">
      <div class="flex gap-8">
        <div v-for="i in cols" :key="i" class="h-3 shimmer rounded" :class="i === 1 ? 'w-1/4' : 'w-1/6'" />
      </div>
    </div>
    <div v-for="r in rows" :key="r" class="border-b border-gray-50 dark:border-slate-700/50 px-6 py-4">
      <div class="flex gap-8">
        <div v-for="c in cols" :key="c" class="h-4 shimmer rounded" :class="c === 1 ? 'w-1/4' : 'w-1/6'" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: { type: Number, default: 5 },
  cols: { type: Number, default: 4 },
})
</script>
