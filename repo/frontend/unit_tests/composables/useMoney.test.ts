import { describe, it, expect } from 'vitest'
import { formatCents } from '../../src/composables/useMask'

describe('formatCents', () => {
  it('renders 1000 as "10.00"', () => {
    expect(formatCents(1000)).toContain('10.00')
  })

  it('renders 0 as "0.00"', () => {
    expect(formatCents(0)).toContain('0.00')
  })

  it('renders 10050 as "100.50"', () => {
    expect(formatCents(10050)).toContain('100.50')
  })
})
