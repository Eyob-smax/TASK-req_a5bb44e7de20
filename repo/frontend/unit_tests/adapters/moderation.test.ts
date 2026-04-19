import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { moderationAdapter } from '../../src/adapters/moderation'

describe('moderationAdapter', () => {
  it('queue calls GET /admin/moderation/queue', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await moderationAdapter.queue()
    expect(http.get).toHaveBeenCalledWith('/admin/moderation/queue', expect.objectContaining({ params: undefined }))
  })

  it('hideThread calls POST /admin/threads/{id}/hide', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    await moderationAdapter.hideThread(10, 'violates policy')
    expect(http.post).toHaveBeenCalledWith('/admin/threads/10/hide', { reason: 'violates policy' })
  })

  it('lockThread calls POST /admin/threads/{id}/lock', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    await moderationAdapter.lockThread(10, 'spam')
    expect(http.post).toHaveBeenCalledWith('/admin/threads/10/lock', { reason: 'spam' })
  })
})
