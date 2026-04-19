import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { CacheStore } from '@/offline/cache'
import { PendingQueue } from '@/offline/queue'

export interface PendingAction {
  id: string
  endpoint: string
  method: string
  payload: unknown
  idempotencyKey: string
  retries: number
  lastAttempt: number | null
  error: string | null
}

const cache = new CacheStore('campuslearn')
const queue = new PendingQueue('campuslearn_queue')

export const useOfflineStore = defineStore('offline', () => {
  const isReadOnly     = ref(false)
  const pendingActions = ref<PendingAction[]>([])
  const retryBanner    = ref(false)

  const pendingCount = computed(() => pendingActions.value.length)

  function setReadOnly(val: boolean) {
    isReadOnly.value = val
    retryBanner.value = val && pendingActions.value.length > 0
  }

  async function enqueueAction(action: Omit<PendingAction, 'retries' | 'lastAttempt' | 'error'>) {
    const entry: PendingAction = { ...action, retries: 0, lastAttempt: null, error: null }
    pendingActions.value.push(entry)
    await queue.enqueue(entry)
    retryBanner.value = true
  }

  async function removeAction(id: string) {
    pendingActions.value = pendingActions.value.filter((a) => a.id !== id)
    await queue.dequeue(id)
    if (pendingActions.value.length === 0) retryBanner.value = false
  }

  async function loadQueue() {
    const stored = await queue.getAll()
    pendingActions.value = stored
    retryBanner.value = stored.length > 0
  }

  async function cacheRead<T>(key: string, data: T) {
    await cache.set(key, data)
  }

  async function getCached<T>(key: string): Promise<T | null> {
    return cache.get<T>(key)
  }

  return {
    isReadOnly, pendingActions, retryBanner, pendingCount,
    setReadOnly, enqueueAction, removeAction, loadQueue,
    cacheRead, getCached,
  }
})
