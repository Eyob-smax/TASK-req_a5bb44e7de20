import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { ordersAdapter } from '../../src/adapters/orders'

describe('ordersAdapter', () => {
  it('list calls GET /orders', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await ordersAdapter.list()
    expect(http.get).toHaveBeenCalledWith('/orders')
  })

  it('create calls POST /orders with lines', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    const lines = [{ catalog_item_id: 1, quantity: 2 }]
    await ordersAdapter.create(lines)
    expect(http.post).toHaveBeenCalledWith('/orders', { lines })
  })

  it('initiatePayment includes Idempotency-Key header', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    await ordersAdapter.initiatePayment(5, { method: 'cash' }, 'idem-key-123')
    expect(http.post).toHaveBeenCalledWith(
      '/orders/5/payment',
      { method: 'cash' },
      expect.objectContaining({ headers: { 'Idempotency-Key': 'idem-key-123' } }),
    )
  })

  it('cancel calls DELETE /orders/:id', async () => {
    vi.mocked(http.delete).mockResolvedValueOnce({ data: {} } as any)
    await ordersAdapter.cancel(3)
    expect(http.delete).toHaveBeenCalledWith('/orders/3')
  })
})
