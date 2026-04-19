import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../src/stores/auth'

// Mock the http adapter to avoid real network calls
vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

describe('AuthStore', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('starts unauthenticated when localStorage is empty', () => {
    const auth = useAuthStore()
    expect(auth.isAuthenticated).toBe(false)
    expect(auth.token).toBeNull()
  })

  it('setSession persists token and expiry to localStorage', () => {
    const auth = useAuthStore()
    const futureIso = new Date(Date.now() + 3_600_000).toISOString()

    auth.setSession('tok_abc', futureIso)

    expect(auth.token).toBe('tok_abc')
    expect(auth.isAuthenticated).toBe(true)
    expect(localStorage.getItem('auth_token')).toBe('tok_abc')
  })

  it('isSessionExpired is true when expiry is in the past', () => {
    const auth = useAuthStore()
    const pastIso = new Date(Date.now() - 1000).toISOString()

    auth.setSession('tok_old', pastIso)

    expect(auth.isSessionExpired).toBe(true)
    expect(auth.isAuthenticated).toBe(false)
  })

  it('clearSession removes all auth state from localStorage', () => {
    const auth = useAuthStore()
    auth.setSession('tok_x', new Date(Date.now() + 3_600_000).toISOString())
    auth.setUser({ id: 1, name: 'Alice', email: 'alice@example.com', roles: [] })

    auth.clearSession()

    expect(auth.token).toBeNull()
    expect(auth.user).toBeNull()
    expect(localStorage.getItem('auth_token')).toBeNull()
    expect(localStorage.getItem('auth_expires_at')).toBeNull()
  })

  it('role helpers reflect user roles correctly', () => {
    const auth = useAuthStore()
    auth.setUser({
      id: 1,
      name: 'Bob',
      email: 'bob@example.com',
      roles: [{ name: 'teacher', scope_type: 'section', scope_id: 5 }],
    })

    expect(auth.isTeacher).toBe(true)
    expect(auth.isAdmin).toBe(false)
    expect(auth.hasRole('teacher')).toBe(true)
    expect(auth.hasRole('administrator')).toBe(false)
  })

  it('isAdmin is true when user holds administrator role', () => {
    const auth = useAuthStore()
    auth.setUser({
      id: 2,
      name: 'Admin',
      email: 'admin@example.com',
      roles: [{ name: 'administrator', scope_type: 'global', scope_id: null }],
    })

    expect(auth.isAdmin).toBe(true)
  })

  it('initSession returns false and clears when session is expired', async () => {
    const auth = useAuthStore()
    const pastIso = new Date(Date.now() - 1000).toISOString()
    auth.setSession('old_token', pastIso)

    const result = await auth.initSession()

    expect(result).toBe(false)
    expect(auth.token).toBeNull()
  })
})
