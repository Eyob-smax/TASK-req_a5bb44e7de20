import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { gradeItemsAdapter } from '../../src/adapters/gradeItems'

describe('gradeItemsAdapter', () => {
  it('list calls GET /sections/{id}/grade-items', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await gradeItemsAdapter.list(3)
    expect(http.get).toHaveBeenCalledWith('/sections/3/grade-items')
  })

  it('create calls POST /sections/{id}/grade-items', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    await gradeItemsAdapter.create(3, { title: 'Midterm', max_score: 100 })
    expect(http.post).toHaveBeenCalledWith('/sections/3/grade-items', { title: 'Midterm', max_score: 100 })
  })

  it('publish calls POST /sections/{id}/grade-items/{itemId}/publish', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    await gradeItemsAdapter.publish(3, 12)
    expect(http.post).toHaveBeenCalledWith('/sections/3/grade-items/12/publish')
  })
})
