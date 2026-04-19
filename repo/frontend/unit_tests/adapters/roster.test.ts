import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { rosterAdapter } from '../../src/adapters/roster'

describe('rosterAdapter', () => {
  it('import calls POST /terms/{id}/roster-imports with FormData', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    const file = new File(['name,id\nAlice,1'], 'roster.csv', { type: 'text/csv' })
    await rosterAdapter.import(5, file)
    const [url, body, config] = vi.mocked(http.post).mock.calls[0]
    expect(url).toBe('/terms/5/roster-imports')
    expect(body).toBeInstanceOf(FormData)
    expect((config as any).headers['Content-Type']).toBe('multipart/form-data')
  })

  it('history calls GET /terms/{id}/roster-imports', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await rosterAdapter.history(5)
    expect(http.get).toHaveBeenCalledWith('/terms/5/roster-imports')
  })
})
