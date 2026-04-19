import { describe, it, expect, vi, afterEach } from 'vitest'
import { isEditable, secondsLeft, EDIT_WINDOW_MS } from '../../src/composables/useEditWindow'

afterEach(() => vi.useRealTimers())

describe('useEditWindow', () => {
  it('isEditable returns true for post within 15-minute window', () => {
    vi.useFakeTimers()
    const now = Date.now()
    vi.setSystemTime(now)
    const createdAt = new Date(now - 5 * 60 * 1000).toISOString() // 5 minutes ago
    expect(isEditable(createdAt)).toBe(true)
  })

  it('isEditable returns false for post older than 15 minutes', () => {
    vi.useFakeTimers()
    const now = Date.now()
    vi.setSystemTime(now)
    const createdAt = new Date(now - 16 * 60 * 1000).toISOString() // 16 minutes ago
    expect(isEditable(createdAt)).toBe(false)
  })

  it('secondsLeft decrements correctly', () => {
    vi.useFakeTimers()
    const now = Date.now()
    vi.setSystemTime(now)
    const createdAt = new Date(now - 14 * 60 * 1000).toISOString() // 14 minutes ago
    const remaining = secondsLeft(createdAt)
    expect(remaining).toBe(60) // 1 minute left = 60 seconds
  })
})
