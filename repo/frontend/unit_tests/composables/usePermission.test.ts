import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../src/stores/auth'

vi.mock('../../src/adapters/http', () => ({
  default: { get: vi.fn(), post: vi.fn() },
}))

import { usePermission } from '../../src/composables/usePermission'

function setUser(roles: Array<{ name: string; scope_type?: string; scope_id?: number | null }>) {
  const auth = useAuthStore()
  auth.setSession('tok', new Date(Date.now() + 3_600_000).toISOString())
  auth.setUser({ id: 1, name: 'Test', email: 't@t.com', roles: roles as any })
}

describe('usePermission', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('returns false for unauthenticated user', () => {
    const { can } = usePermission()
    expect(can('administrator')).toBe(false)
  })

  it('can() returns true for exact role match', () => {
    setUser([{ name: 'teacher', scope_type: 'section', scope_id: 5 }])
    const { can } = usePermission()
    expect(can('teacher')).toBe(true)
    expect(can('student')).toBe(false)
  })

  it('can() returns false when scope_id does not match', () => {
    setUser([{ name: 'teacher', scope_type: 'section', scope_id: 5 }])
    const { can } = usePermission()
    expect(can('teacher', 'section', 5)).toBe(true)
    expect(can('teacher', 'section', 99)).toBe(false)
  })

  it('isAdmin computed reflects administrator role', () => {
    setUser([{ name: 'administrator', scope_type: 'global', scope_id: null }])
    const { isAdmin } = usePermission()
    expect(isAdmin.value).toBe(true)
  })

  it('canManageSection: admin can manage any section', () => {
    setUser([{ name: 'administrator', scope_type: 'global', scope_id: null }])
    const { canManageSection } = usePermission()
    expect(canManageSection(999)).toBe(true)
  })

  it('canManageSection: teacher can only manage assigned section', () => {
    setUser([{ name: 'teacher', scope_type: 'section', scope_id: 7 }])
    const { canManageSection } = usePermission()
    expect(canManageSection(7)).toBe(true)
    expect(canManageSection(8)).toBe(false)
  })

  it('canModerate: true for administrator and registrar', () => {
    setUser([{ name: 'administrator' }])
    const { canModerate } = usePermission()
    expect(canModerate()).toBe(true)
  })

  it('canModerate: false for student', () => {
    setUser([{ name: 'student' }])
    const { canModerate } = usePermission()
    expect(canModerate()).toBe(false)
  })
})
