import { describe, it, expect } from 'vitest'
import { normalizeError, generateIdempotencyKey, withRetry } from '../../src/adapters/http'
import type { NormalizedError } from '../../src/adapters/http'

describe('generateIdempotencyKey', () => {
  it('generates a non-empty string', () => {
    const key = generateIdempotencyKey()
    expect(typeof key).toBe('string')
    expect(key.length).toBeGreaterThan(5)
  })

  it('generates unique keys on successive calls', () => {
    const keys = new Set(Array.from({ length: 50 }, () => generateIdempotencyKey()))
    expect(keys.size).toBe(50)
  })
})

describe('normalizeError', () => {
  it('extracts code/message from structured API error body', () => {
    const err = {
      response: {
        status: 422,
        data: { error: { code: 'VALIDATION_FAILED', message: 'Invalid input' } },
      },
    }
    const result = normalizeError(err) as NormalizedError
    expect(result.code).toBe('VALIDATION_FAILED')
    expect(result.message).toBe('Invalid input')
    expect(result.httpStatus).toBe(422)
  })

  it('maps 401 to UNAUTHENTICATED when no body code', () => {
    const result = normalizeError({ response: { status: 401, data: {} } }) as NormalizedError
    expect(result.code).toBe('UNAUTHENTICATED')
    expect(result.httpStatus).toBe(401)
  })

  it('maps 403 to FORBIDDEN', () => {
    const result = normalizeError({ response: { status: 403, data: {} } }) as NormalizedError
    expect(result.code).toBe('FORBIDDEN')
  })

  it('maps 404 to NOT_FOUND', () => {
    const result = normalizeError({ response: { status: 404, data: {} } }) as NormalizedError
    expect(result.code).toBe('NOT_FOUND')
  })

  it('maps 503 to SERVICE_UNAVAILABLE', () => {
    const result = normalizeError({ response: { status: 503, data: {} } }) as NormalizedError
    expect(result.code).toBe('SERVICE_UNAVAILABLE')
  })

  it('includes blocked_terms from API error body', () => {
    const blocked_terms = [{ term: 'badword', start: 0, end: 7 }]
    const err = {
      response: {
        status: 422,
        data: { error: { code: 'SENSITIVE_WORDS_BLOCKED', message: 'Blocked', blocked_terms } },
      },
    }
    const result = normalizeError(err) as NormalizedError
    expect(result.blocked_terms).toEqual(blocked_terms)
  })
})

describe('withRetry', () => {
  it('returns result on first success', async () => {
    const fn = vi.fn().mockResolvedValue('ok')
    const result = await withRetry(fn, 3)
    expect(result).toBe('ok')
    expect(fn).toHaveBeenCalledTimes(1)
  })

  it('retries on server error and succeeds on retry', async () => {
    const fn = vi.fn()
      .mockRejectedValueOnce({ httpStatus: 503, code: 'SERVICE_UNAVAILABLE', message: 'down' })
      .mockResolvedValueOnce('recovered')
    // Use baseDelayMs=0 to avoid real timer waits
    const result = await withRetry(fn, 3, 0)
    expect(result).toBe('recovered')
    expect(fn).toHaveBeenCalledTimes(2)
  })

  it('does not retry on 401', async () => {
    const fn = vi.fn().mockRejectedValue({ httpStatus: 401, code: 'UNAUTHENTICATED', message: 'Unauth' })
    await expect(withRetry(fn, 3, 0)).rejects.toMatchObject({ code: 'UNAUTHENTICATED' })
    expect(fn).toHaveBeenCalledTimes(1)
  })

  it('does not retry on 422', async () => {
    const fn = vi.fn().mockRejectedValue({ httpStatus: 422, code: 'VALIDATION_FAILED', message: 'Bad' })
    await expect(withRetry(fn, 3, 0)).rejects.toMatchObject({ code: 'VALIDATION_FAILED' })
    expect(fn).toHaveBeenCalledTimes(1)
  })
})
