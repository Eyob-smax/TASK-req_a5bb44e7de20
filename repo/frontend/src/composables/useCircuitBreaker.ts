import { computed } from 'vue'
import { useOfflineStore } from '@/stores/offline'

export function useCircuitBreaker() {
  const offline = useOfflineStore()

  const isReadOnly = computed(() => offline.isReadOnly)
  const hasPending = computed(() => offline.pendingCount > 0)

  function guardWrite(fn: () => void | Promise<void>): void | Promise<void> {
    if (offline.isReadOnly) {
      console.warn('[circuit-breaker] Write blocked: system is in read-only mode.')
      return
    }
    return fn()
  }

  return { isReadOnly, hasPending, guardWrite }
}
