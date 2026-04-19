import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { threadsAdapter } from '../../src/adapters/threads'

describe('threadsAdapter', () => {
  it('list calls GET /threads', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await threadsAdapter.list()
    expect(http.get).toHaveBeenCalledWith('/threads', expect.anything())
  })

  it('create calls POST /threads with correct data', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    const payload = { section_id: 1, type: 'discussion', title: 'T', body: 'B' }
    await threadsAdapter.create(payload)
    expect(http.post).toHaveBeenCalledWith('/threads', payload)
  })

  it('update calls PATCH /threads/:id', async () => {
    vi.mocked(http.patch).mockResolvedValueOnce({ data: { data: {} } } as any)
    await threadsAdapter.update(5, { title: 'Updated' })
    expect(http.patch).toHaveBeenCalledWith('/threads/5', { title: 'Updated' })
  })

  it('deletePost calls DELETE with correct path', async () => {
    vi.mocked(http.delete).mockResolvedValueOnce({ data: {} } as any)
    await threadsAdapter.deletePost(1, 2)
    expect(http.delete).toHaveBeenCalledWith('/threads/1/posts/2')
  })
})
