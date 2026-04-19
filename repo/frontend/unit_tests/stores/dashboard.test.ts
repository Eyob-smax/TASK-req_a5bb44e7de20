import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useDashboardStore } from '../../src/stores/dashboard'

vi.mock('../../src/adapters/dashboard', () => ({
  dashboardAdapter: {
    summary: vi.fn(),
  },
}))

import { dashboardAdapter } from '../../src/adapters/dashboard'

describe('DashboardStore', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('starts with null summary and no error', () => {
    const store = useDashboardStore()
    expect(store.summary).toBeNull()
    expect(store.error).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('fetch sets summary on success', async () => {
    const mockData = { enrolled_sections: 3, open_bills: 1, unread_notifications: 5 }
    vi.mocked(dashboardAdapter.summary).mockResolvedValueOnce({ data: { data: mockData } } as any)

    const store = useDashboardStore()
    await store.fetch()

    expect(store.summary).toEqual(mockData)
    expect(store.error).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('fetch sets error message on failure', async () => {
    vi.mocked(dashboardAdapter.summary).mockRejectedValueOnce({ message: 'Server error' })

    const store = useDashboardStore()
    await store.fetch()

    expect(store.summary).toBeNull()
    expect(store.error).toBe('Server error')
  })

  it('reset clears summary and error', async () => {
    vi.mocked(dashboardAdapter.summary).mockResolvedValueOnce({ data: { data: { open_bills: 2 } } } as any)
    const store = useDashboardStore()
    await store.fetch()
    store.reset()
    expect(store.summary).toBeNull()
    expect(store.error).toBeNull()
  })
})
