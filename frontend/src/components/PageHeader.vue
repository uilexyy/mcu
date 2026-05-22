<template>
  <div class="mb-6">
    <div v-if="$slots.breadcrumb" class="mb-2">
      <slot name="breadcrumb" />
    </div>
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-3">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>
