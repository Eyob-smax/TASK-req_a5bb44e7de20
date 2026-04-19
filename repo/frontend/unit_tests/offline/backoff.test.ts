import { describe, it, expect } from 'vitest'
import { computeBackoffDelay, shouldRetry } from '../../src/offline/backoff'

describe('computeBackoffDelay', () => {
  it('returns a non-negative value', () => {
    for (let attempt = 0; attempt < 5; attempt++) {
      const delay = computeBackoffDelay(attempt)
      expect(delay).toBeGreaterThanOrEqual(0)
    }
  })

  it('does not exceed maxMs with jitter disabled', () => {
    const maxMs = 10_000
    for (let attempt = 0; attempt < 10; attempt++) {
      const delay = computeBackoffDelay(attempt, { maxMs, jitter: false })
      expect(delay).toBeLessThanOrEqual(maxMs)
    }
  })

  it('grows with each attempt (no jitter)', () => {
    const delays = [0, 1, 2, 3].map((a) =>
      computeBackoffDelay(a, { baseMs: 100, factor: 2, jitter: false }),
    )
    // Each should be >= previous
    expect(delays[1]).toBeGreaterThanOrEqual(delays[0])
    expect(delays[2]).toBeGreaterThanOrEqual(delays[1])
    expect(delays[3]).toBeGreaterThanOrEqual(delays[2])
  })
})

describe('shouldRetry', () => {
  it('returns true for undefined status (network error)', () => {
    expect(shouldRetry(undefined)).toBe(true)
  })

  it('returns true for 503', () => {
    expect(shouldRetry(503)).toBe(true)
  })

  it('returns true for 429', () => {
    expect(shouldRetry(429)).toBe(true)
  })

  it('returns false for 401', () => {
    expect(shouldRetry(401)).toBe(false)
  })

  it('returns false for 403', () => {
    expect(shouldRetry(403)).toBe(false)
  })

  it('returns false for 422', () => {
    expect(shouldRetry(422)).toBe(false)
  })
})
