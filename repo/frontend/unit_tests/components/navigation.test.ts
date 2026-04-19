import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../src/stores/auth'
import { useNavigation } from '../../src/composables/useNavigation'

vi.mock('../../src/adapters/http', () => ({ default: { get: vi.fn(), post: vi.fn() } }))
vi.mock('../../src/adapters/notifications', () => ({
  notificationsAdapter: { unreadCount: vi.fn().mockResolvedValue({ data: { data: {} } }) },
}))

function loginAs(roleName: string) {
  const auth = useAuthStore()
  auth.setSession('tok', new Date(Date.now() + 3_600_000).toISOString())
  auth.setUser({ id: 1, name: 'U', email: 'u@u.com', roles: [{ name: roleName as any }] })
}

describe('useNavigation role-aware items', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('student sees Dashboard and Notifications', () => {
    loginAs('student')
    const { navItems } = useNavigation()
    const labels = navItems.value.map((n) => n.label)
    expect(labels).toContain('Dashboard')
    expect(labels).toContain('Notifications')
    expect(labels).toContain('Orders')
  })

  it('teacher sees Grade Items but not Roster Import or Moderation', () => {
    loginAs('teacher')
    const { navItems } = useNavigation()
    const labels = navItems.value.map((n) => n.label)
    expect(labels).toContain('Grade Items')
    expect(labels).not.toContain('Roster Import')
    expect(labels).not.toContain('Moderation')
  })

  it('registrar sees Roster Import and Billing Admin but not Moderation', () => {
    loginAs('registrar')
    const { navItems } = useNavigation()
    const labels = navItems.value.map((n) => n.label)
    expect(labels).toContain('Roster Import')
    expect(labels).toContain('Billing Admin')
    expect(labels).not.toContain('Moderation')
  })

  it('admin sees all navigation sections including Health and Moderation', () => {
    loginAs('administrator')
    const { navItems } = useNavigation()
    const labels = navItems.value.map((n) => n.label)
    expect(labels).toContain('Moderation')
    expect(labels).toContain('Health')
    expect(labels).toContain('Billing Admin')
    expect(labels).toContain('Refunds')
  })
})
