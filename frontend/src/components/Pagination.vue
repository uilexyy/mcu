<template>
  <div v-if="meta" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 dark:border-slate-700">
    <p class="text-sm text-gray-500 dark:text-slate-400">
      {{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} dari {{ meta.total }}
    </p>
    <div class="flex items-center gap-1">
      <button
        :disabled="meta.current_page <= 1"
        @click="go(meta.current_page - 1)"
        class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-gray-600 dark:text-slate-300"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <template v-for="p in pages" :key="p">
        <button
          v-if="p !== '...'"
          @click="go(p)"
          class="min-w-[36px] px-3 py-1.5 text-sm rounded-lg font-medium transition-colors"
          :class="p === meta.current_page
            ? 'bg-emerald-600 text-white shadow-sm'
            : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
        >{{ p }}</button>
        <span v-else class="px-2 text-gray-400">...</span>
      </template>
      <button
        :disabled="meta.current_page >= meta.last_page"
        @click="go(meta.current_page + 1)"
        class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-gray-600 dark:text-slate-300"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: { type: Object, default: null },
})
const emit = defineEmits(['page-change'])

function go(page) {
  if (page < 1 || page > (props.meta?.last_page || 1)) return
  emit('page-change', page)
}

const pages = computed(() => {
  if (!props.meta) return []
  const { current_page, last_page } = props.meta
  const range = 2
  const pages = []
  const ellipsis = '...'

  pages.push(1)
  if (current_page - range > 2) pages.push(ellipsis)

  for (let i = Math.max(2, current_page - range); i <= Math.min(last_page - 1, current_page + range); i++) {
    pages.push(i)
  }

  if (current_page + range < last_page - 1) pages.push(ellipsis)
  if (last_page > 1) pages.push(last_page)

  return pages
})
</script>
