<template>
  <div :class="['flex flex-col items-center justify-center', fullScreen ? 'fixed inset-0 bg-white/80 z-50' : 'py-12']">
    <svg class="animate-spin" :class="sizeClass" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <p v-if="text" class="mt-3 text-sm text-gray-500">{{ text }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: { type: String, default: 'Memuat...' },
  size: { type: String, default: 'md' },
  fullScreen: { type: Boolean, default: false },
})

const sizes = { sm: 'h-5 w-5 text-emerald-500', md: 'h-8 w-8 text-emerald-500', lg: 'h-12 w-12 text-emerald-600' }
const sizeClass = computed(() => sizes[props.size] || sizes.md)
</script>
