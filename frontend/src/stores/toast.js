import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  let counter = 0

  function add(type, message, duration = 4000) {
    const id = ++counter
    toasts.value.push({ id, type, message, duration })
    setTimeout(() => remove(id), duration)
  }

  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  function success(message) { add('success', message) }
  function error(message) { add('error', message) }
  function info(message) { add('info', message) }
  function warning(message) { add('warning', message) }

  return { toasts, add, remove, success, error, info, warning }
})
