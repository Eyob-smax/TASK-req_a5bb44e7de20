import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

// Mock offline primitives so tests don't need real IndexedDB
vi.mock('../../src/offline/cache', () => ({
  CacheStore: vi.fn().mockImplementation(() => ({
    set: vi.fn().mockResolvedValue(undefined),
    get: vi.fn().mockResolvedValue(null),
  })),
}))

vi.mock('../../src/offline/queue', () => ({
  PendingQueue: vi.fn().mockImplementation(() => ({
    enqueue:  vi.fn().mockResolvedValue(undefined),
    dequeue:  vi.fn().mockResolvedValue(undefined),
    getAll:   vi.fn().mockResolvedValue([]),
    update:   vi.fn().mockResolvedValue(undefined),
  })),
}))

import { useOfflineStore } from '../../src/stores/offline'

describe('OfflineStore', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('starts in non-read-only state with no pending actions', () => {
    const store = useOfflineStore()
    expect(store.isReadOnly).toBe(false)
    expect(store.pendingActions).toHaveLength(0)
    expect(store.retryBanner).toBe(false)
  })

  it('setReadOnly toggles read-only mode', () => {
    const store = useOfflineStore()
    store.setReadOnly(true)
    expect(store.isReadOnly).toBe(true)
    store.setReadOnly(false)
    expect(store.isReadOnly).toBe(false)
  })

  it('enqueueAction adds to pendingActions and shows retryBanner', async () => {
    const store  = useOfflineStore()
    const action = { id: 'test-1', endpoint: '/orders', method: 'POST', payload: {}, idempotencyKey: 'key-1' }
    await store.enqueueAction(action)
    expect(store.pendingActions).toHaveLength(1)
    expect(store.retryBanner).toBe(true)
  })

  it('removeAction removes from pendingActions', async () => {
    const store  = useOfflineStore()
    const action = { id: 'test-2', endpoint: '/orders', method: 'POST', payload: {}, idempotencyKey: 'key-2' }
    await store.enqueueAction(action)
    expect(store.pendingActions).toHaveLength(1)
    await store.removeAction('test-2')
    expect(store.pendingActions).toHaveLength(0)
    expect(store.retryBanner).toBe(false)
  })

  it('pendingCount reflects length of pendingActions', async () => {
    const store = useOfflineStore()
    expect(store.pendingCount).toBe(0)
    await store.enqueueAction({ id: 'a', endpoint: '/x', method: 'POST', payload: {}, idempotencyKey: 'k' })
    expect(store.pendingCount).toBe(1)
  })
})
