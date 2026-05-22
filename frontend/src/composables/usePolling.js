import { ref, onUnmounted } from 'vue'

export function usePolling(callback, interval = 10000) {
  const isPolling = ref(false)
  let timer = null

  function start() {
    if (timer) return
    isPolling.value = true
    timer = setInterval(callback, interval)
  }

  function stop() {
    if (timer) {
      clearInterval(timer)
      timer = null
      isPolling.value = false
    }
  }

  function restart() {
    stop()
    start()
  }

  onUnmounted(stop)

  return { isPolling, start, stop, restart }
}
