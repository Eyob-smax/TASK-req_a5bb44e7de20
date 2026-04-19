import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { catalogAdapter } from '../../src/adapters/catalog'

describe('catalogAdapter', () => {
  it('list calls GET /catalog', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await catalogAdapter.list()
    expect(http.get).toHaveBeenCalledWith('/catalog', expect.any(Object))
  })

  it('createItem calls POST /admin/catalog', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    const payload = { fee_category_id: 1, sku: 'SKU-01', name: 'Lab Kit', unit_price_cents: 5000 }
    await catalogAdapter.create(payload)
    expect(http.post).toHaveBeenCalledWith('/admin/catalog', payload)
  })
})
