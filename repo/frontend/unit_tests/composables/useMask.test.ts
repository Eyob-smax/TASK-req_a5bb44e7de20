import { describe, it, expect } from 'vitest'
import { maskEmail, maskName, maskAmount, formatCents } from '../../src/composables/useMask'

describe('maskEmail', () => {
  it('masks the local part by default', () => {
    const result = maskEmail('alice@example.com')
    expect(result).toContain('@example.com')
    expect(result).not.toBe('alice@example.com')
    expect(result).toMatch(/^a•+e@/)
  })

  it('returns full email when reveal=true', () => {
    expect(maskEmail('alice@example.com', true)).toBe('alice@example.com')
  })

  it('handles single-char local part gracefully', () => {
    const result = maskEmail('a@b.com')
    expect(result).toContain('@b.com')
  })
})

describe('maskName', () => {
  it('masks surname by default', () => {
    const result = maskName('John Smith')
    expect(result).toContain('John')
    expect(result).toContain('S')
    expect(result).not.toBe('John Smith')
  })

  it('returns full name when reveal=true', () => {
    expect(maskName('John Smith', true)).toBe('John Smith')
  })
})

describe('maskAmount', () => {
  it('returns masked placeholder by default', () => {
    expect(maskAmount(1099)).toBe('••••')
  })

  it('returns formatted currency when reveal=true', () => {
    const result = maskAmount(1099, true)
    expect(result).toContain('10.99')
  })
})

describe('formatCents', () => {
  it('formats cents to currency string', () => {
    const result = formatCents(2599)
    expect(result).toContain('25.99')
  })

  it('handles zero', () => {
    expect(formatCents(0)).toContain('0')
  })
})
