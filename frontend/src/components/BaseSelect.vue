<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <select
      :id="id"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :required="required"
      :disabled="disabled"
      :class="[
        'w-full px-3 py-2 border rounded-lg text-sm transition-colors duration-150 outline-none bg-white',
        'focus:ring-2 focus:border-transparent',
        error ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-emerald-500',
        disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '',
      ]"
    >
      <option v-if="placeholder" value="">{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value" :disabled="opt.disabled">
        {{ opt.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  id: { type: String, default: () => `select-${Math.random().toString(36).substr(2, 9)}` },
})

defineEmits(['update:modelValue'])
</script>
