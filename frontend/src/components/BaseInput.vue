<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <div class="relative">
      <div v-if="$slots.prepend" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
        <slot name="prepend" />
      </div>
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="[
          'w-full px-3 py-2 border rounded-lg text-sm transition-colors duration-150 outline-none',
          'focus:ring-2 focus:border-transparent',
          error ? 'border-red-300 focus:ring-red-500 animate-shake' : 'border-gray-300 focus:ring-emerald-500',
          $slots.prepend ? 'pl-10' : '',
          $slots.append ? 'pr-10' : '',
          disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white',
        ]"
      />
      <div v-if="$slots.append" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
        <slot name="append" />
      </div>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-xs text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  id: { type: String, default: () => `input-${Math.random().toString(36).substr(2, 9)}` },
})

defineEmits(['update:modelValue'])
</script>
