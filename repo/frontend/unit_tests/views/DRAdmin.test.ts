import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/admin', () => ({
  adminAdapter: {
    listDrills:  vi.fn(),
    recordDrill: vi.fn(),
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

import { adminAdapter } from '../../src/adapters/admin'
import DRAdminView from '../../src/views/admin/DRAdminView.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', name: 'home', component: defineComponent({ template: '<div />' }) }],
})

const emptyDrills = { data: { data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } } } }

describe('DRAdminView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('shows "No drills recorded yet" when empty', async () => {
    vi.mocked(adminAdapter.listDrills).mockResolvedValueOnce(emptyDrills as any)
    const wrapper = mount(DRAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('No drills recorded yet')
  })

  it('renders drill history with outcome', async () => {
    vi.mocked(adminAdapter.listDrills).mockResolvedValueOnce({
      data: {
        data: {
          data: [{
            id: 1,
            drill_date: '2025-01-15',
            operator_user_id: 1,
            outcome: 'passed',
            notes: 'All good.',
            created_at: '2025-01-15T10:00:00Z',
          }],
          meta: { page: 1, per_page: 20, total: 1, last_page: 1 },
        },
      },
    } as any)
    const wrapper = mount(DRAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('passed')
    expect(wrapper.text()).toContain('2025-01-15')
  })

  it('shows the Record Drill form', async () => {
    vi.mocked(adminAdapter.listDrills).mockResolvedValueOnce(emptyDrills as any)
    const wrapper = mount(DRAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Record Drill')
    expect(wrapper.find('select#outcome').exists()).toBe(true)
  })

  it('shows quarterly drill reminder note', async () => {
    vi.mocked(adminAdapter.listDrills).mockResolvedValueOnce(emptyDrills as any)
    const wrapper = mount(DRAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Quarterly')
  })
})
