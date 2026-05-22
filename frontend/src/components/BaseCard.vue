<template>
  <div :class="[
    'bg-white dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700',
    paddingClass,
    hoverClass,
  ]">
    <div v-if="$slots.header" class="border-b border-gray-100 dark:border-slate-700 px-6 py-4">
      <slot name="header" />
    </div>
    <div :class="{ 'p-6': !noPadding }">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  noPadding: { type: Boolean, default: false },
  hover: { type: Boolean, default: true },
})

const paddingClass = computed(() => props.noPadding ? '' : '')
const hoverClass = computed(() => props.hover
  ? 'shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200'
  : 'shadow-sm')
</script>
