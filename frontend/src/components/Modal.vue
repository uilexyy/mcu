<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop" @click.self="$emit('close')">
        <div class="absolute inset-0 bg-black/50" @click="$emit('close')" />
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full modal-content max-h-[90vh] overflow-y-auto" :class="maxWidthClass">
          <div v-if="$slots.header || title" class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-slate-100">{{ title }}</h2>
            <button @click="$emit('close')" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="px-6 py-4">
            <slot />
          </div>
          <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-slate-700">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  size: { type: String, default: 'md' },
})
defineEmits(['close'])

const sizes = { sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-lg', xl: 'max-w-xl', '2xl': 'max-w-2xl' }
const maxWidthClass = computed(() => sizes[props.size] || sizes.md)
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
