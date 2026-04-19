import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { billsAdapter } from '../../src/adapters/bills'

describe('billsAdapter', () => {
  it('mine calls GET /bills', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await billsAdapter.mine()
    expect(http.get).toHaveBeenCalledWith('/bills')
  })

  it('adminGenerate calls POST /admin/bills/generate with idempotency key', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    const data = { user_id: 1, type: 'supplemental', amount_cents: 5000, reason: 'lab fee' }
    await billsAdapter.adminGenerate(data, 'idem-key-abc')
    expect(http.post).toHaveBeenCalledWith(
      '/admin/bills/generate',
      data,
      expect.objectContaining({ headers: { 'Idempotency-Key': 'idem-key-abc' } }),
    )
  })

  it('createRefund calls POST /bills/:id/refunds with idempotency key', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    await billsAdapter.createRefund(7, { amount_cents: 1000, reason_code: 'duplicate' }, 'idem-xyz')
    expect(http.post).toHaveBeenCalledWith(
      '/bills/7/refunds',
      expect.objectContaining({ amount_cents: 1000 }),
      expect.objectContaining({ headers: { 'Idempotency-Key': 'idem-xyz' } }),
    )
  })

  it('reasonCodes calls GET /refund-reason-codes', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await billsAdapter.reasonCodes()
    expect(http.get).toHaveBeenCalledWith('/refund-reason-codes')
  })
})
