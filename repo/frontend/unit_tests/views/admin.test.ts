import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/http', () => ({
  default: { get: vi.fn(), post: vi.fn(), patch: vi.fn(), delete: vi.fn() },
}))
vi.mock('../../src/adapters/moderation', () => ({
  moderationAdapter: {
    queue:         vi.fn(),
    hideThread:    vi.fn(),
    restoreThread: vi.fn(),
    lockThread:    vi.fn(),
  },
}))
vi.mock('../../src/adapters/admin', () => ({
  adminAdapter: {
    health:            vi.fn(),
    triggerDiagnosticExport: vi.fn(),
    exportDiagnostics: vi.fn(),
    listExports:       vi.fn(),
  },
}))
vi.mock('../../src/offline/cache', () => ({
  CacheStore: vi.fn().mockImplementation(() => ({ set: vi.fn(), get: vi.fn().mockResolvedValue(null) })),
}))
vi.mock('../../src/offline/queue', () => ({
  PendingQueue: vi.fn().mockImplementation(() => ({
    enqueue: vi.fn(), dequeue: vi.fn(), getAll: vi.fn().mockResolvedValue([]), update: vi.fn(),
  })),
}))
vi.mock('../../src/adapters/notifications', () => ({
  notificationsAdapter: { unreadCount: vi.fn().mockResolvedValue({ data: { data: {} } }) },
}))

import { moderationAdapter } from '../../src/adapters/moderation'
import { adminAdapter }      from '../../src/adapters/admin'
import { useAdminStore }     from '../../src/stores/admin'
import ModerationQueueView from '../../src/views/admin/ModerationQueueView.vue'
import HealthView          from '../../src/views/admin/HealthView.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', name: 'home', component: defineComponent({ template: '<div />' }) }],
})

describe('ModerationQueueView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('renders "Queue is empty" when no threads', async () => {
    vi.mocked(moderationAdapter.queue).mockResolvedValueOnce({
      data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } },
    } as any)
    const wrapper = mount(ModerationQueueView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Queue is empty')
  })

  it('shows Hide and Lock buttons for visible threads', async () => {
    vi.mocked(moderationAdapter.queue).mockResolvedValueOnce({
      data: {
        data: [{ id: 1, section_id: 1, course_id: 1, author_id: 1, thread_type: 'discussion', title: 'Test', body: 'B', state: 'visible', created_at: new Date().toISOString(), updated_at: new Date().toISOString() }],
        meta: { page: 1, per_page: 20, total: 1, last_page: 1 },
      },
    } as any)
    const wrapper = mount(ModerationQueueView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Hide')
    expect(wrapper.text()).toContain('Lock')
  })
})

describe('HealthView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('shows circuit-open alert when circuit breaker is open', async () => {
    vi.mocked(adminAdapter.health).mockResolvedValueOnce({
      data: { data: { status: 'degraded', database: 'healthy', queue: 'healthy', circuit: 'open', error_rate_pct: 5.5, timestamp: new Date().toISOString() } },
    } as any)
    const wrapper = mount(HealthView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Circuit breaker is OPEN')
  })

  it('shows error rate above threshold warning', async () => {
    vi.mocked(adminAdapter.health).mockResolvedValueOnce({
      data: { data: { status: 'degraded', database: 'healthy', queue: 'degraded', circuit: 'closed', error_rate_pct: 3.2, timestamp: new Date().toISOString() } },
    } as any)
    const wrapper = mount(HealthView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Exceeds 2% threshold')
  })
})
