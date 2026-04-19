import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { notificationsAdapter } from '../../src/adapters/notifications'

describe('notificationsAdapter', () => {
  it('list calls GET /notifications', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await notificationsAdapter.list()
    expect(http.get).toHaveBeenCalledWith('/notifications', expect.anything())
  })

  it('unreadCount calls GET /notifications/unread-count', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: {} } } as any)
    await notificationsAdapter.unreadCount()
    expect(http.get).toHaveBeenCalledWith('/notifications/unread-count')
  })

  it('markRead calls POST /notifications/mark-read', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: {} } as any)
    await notificationsAdapter.markRead({ ids: [1, 2] })
    expect(http.post).toHaveBeenCalledWith('/notifications/mark-read', { ids: [1, 2] })
  })

  it('updatePreferences calls PUT /notifications/preferences', async () => {
    vi.mocked(http.put).mockResolvedValueOnce({ data: {} } as any)
    await notificationsAdapter.updatePreferences({ billing: false })
    expect(http.put).toHaveBeenCalledWith('/notifications/preferences', { preferences: { billing: false } })
  })
})
