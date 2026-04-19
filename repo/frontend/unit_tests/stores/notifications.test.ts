import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useNotificationsStore } from '../../src/stores/notifications'

vi.mock('../../src/adapters/notifications', () => ({
  notificationsAdapter: {
    list:              vi.fn(),
    unreadCount:       vi.fn(),
    markRead:          vi.fn(),
    getPreferences:    vi.fn(),
    updatePreferences: vi.fn(),
  },
}))

import { notificationsAdapter } from '../../src/adapters/notifications'

const mockNotif = (id: number, read = false) => ({
  id,
  user_id: 1,
  category: 'system' as const,
  type: 'test',
  title: 'Test',
  body: 'Body',
  payload: {},
  read_at: read ? new Date().toISOString() : null,
  created_at: new Date().toISOString(),
})

describe('NotificationsStore', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('starts with empty items', () => {
    const store = useNotificationsStore()
    expect(store.items).toHaveLength(0)
    expect(store.totalUnread).toBe(0)
  })

  it('fetchList populates items on success', async () => {
    vi.mocked(notificationsAdapter.list).mockResolvedValueOnce({
      data: { data: [mockNotif(1), mockNotif(2)], meta: { page: 1, per_page: 20, total: 2, last_page: 1 } },
    } as any)

    const store = useNotificationsStore()
    await store.fetchList()
    expect(store.items).toHaveLength(2)
    expect(store.error).toBeNull()
  })

  it('fetchList sets error on failure', async () => {
    vi.mocked(notificationsAdapter.list).mockRejectedValueOnce({ message: 'Network error' })
    const store = useNotificationsStore()
    await store.fetchList()
    expect(store.error).toBe('Network error')
  })

  it('totalUnread sums unreadCounts record', () => {
    const store = useNotificationsStore()
    store.unreadCounts = { announcements: 3, mentions: 1, billing: 0, system: 2 }
    expect(store.totalUnread).toBe(6)
  })

  it('markRead sets read_at on matching items', async () => {
    vi.mocked(notificationsAdapter.markRead).mockResolvedValueOnce({ data: { data: { marked: true } } } as any)
    vi.mocked(notificationsAdapter.unreadCount).mockResolvedValueOnce({ data: { data: {} } } as any)

    const store = useNotificationsStore()
    store.items = [mockNotif(1), mockNotif(2)]
    await store.markRead([1])
    expect(store.items[0].read_at).not.toBeNull()
    expect(store.items[1].read_at).toBeNull()
  })

  it('updatePreferences merges into existing preferences', async () => {
    vi.mocked(notificationsAdapter.updatePreferences).mockResolvedValueOnce({ data: { data: { updated: true } } } as any)
    const store = useNotificationsStore()
    store.preferences = { announcements: true, billing: false }
    await store.updatePreferences({ billing: true })
    expect(store.preferences.billing).toBe(true)
    expect(store.preferences.announcements).toBe(true)
  })
})
