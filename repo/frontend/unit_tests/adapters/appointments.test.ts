import { describe, it, expect, vi } from 'vitest'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}))

import http from '../../src/adapters/http'
import { appointmentsAdapter } from '../../src/adapters/appointments'

describe('appointmentsAdapter', () => {
  it('list calls GET /appointments', async () => {
    vi.mocked(http.get).mockResolvedValueOnce({ data: { data: [] } } as any)
    await appointmentsAdapter.list()
    expect(http.get).toHaveBeenCalledWith('/appointments')
  })

  it('create calls POST /appointments', async () => {
    vi.mocked(http.post).mockResolvedValueOnce({ data: { data: {} } } as any)
    const payload = {
      owner_user_id: 1,
      resource_type: 'course',
      scheduled_start: '2025-09-01T09:00:00Z',
      scheduled_end: '2025-09-01T10:00:00Z',
    }
    await appointmentsAdapter.create(payload)
    expect(http.post).toHaveBeenCalledWith('/appointments', payload)
  })

  it('cancel calls DELETE /appointments/{id}', async () => {
    vi.mocked(http.delete).mockResolvedValueOnce({ data: {} } as any)
    await appointmentsAdapter.cancel(7)
    expect(http.delete).toHaveBeenCalledWith('/appointments/7')
  })
})
