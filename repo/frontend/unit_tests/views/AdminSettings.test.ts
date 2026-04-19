import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/admin', () => ({
  adminAdapter: {
    getSettings:   vi.fn(),
    updateSettings: vi.fn(),
    getAuditLog:   vi.fn(),
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
import AdminSettingsView from '../../src/views/admin/AdminSettingsView.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', name: 'home', component: defineComponent({ template: '<div />' }) }],
})

const baseSettings = {
  edit_window_minutes: 15,
  order_auto_close_minutes: 30,
  penalty_grace_days: 10,
  penalty_rate_bps: 500,
  fanout_batch_size: 50,
  backup_retention_days: 30,
  receipt_number_prefix: 'RC',
}

const emptyLog = { data: { data: { data: [], meta: { page: 1, per_page: 50, total: 0, last_page: 1 } } } }

describe('AdminSettingsView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('renders settings form fields', async () => {
    vi.mocked(adminAdapter.getSettings).mockResolvedValueOnce({ data: { data: baseSettings } } as any)
    vi.mocked(adminAdapter.getAuditLog).mockResolvedValueOnce(emptyLog as any)
    const wrapper = mount(AdminSettingsView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('Edit Window')
    expect(wrapper.text()).toContain('Save Settings')
  })

  it('shows audit log section heading', async () => {
    vi.mocked(adminAdapter.getSettings).mockResolvedValueOnce({ data: { data: baseSettings } } as any)
    vi.mocked(adminAdapter.getAuditLog).mockResolvedValueOnce(emptyLog as any)
    const wrapper = mount(AdminSettingsView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('Audit Log')
  })

  it('renders audit log entries', async () => {
    vi.mocked(adminAdapter.getSettings).mockResolvedValueOnce({ data: { data: baseSettings } } as any)
    vi.mocked(adminAdapter.getAuditLog).mockResolvedValueOnce({
      data: {
        data: {
          data: [{
            id: 1,
            actor_user_id: 1,
            action: 'admin_settings.updated',
            target_type: 'system_settings',
            target_id: null,
            payload: { keys: ['edit_window_minutes'] },
            correlation_id: 'abc-123',
            created_at: '2025-04-01T09:00:00Z',
          }],
          meta: { page: 1, per_page: 50, total: 1, last_page: 1 },
        },
      },
    } as any)
    const wrapper = mount(AdminSettingsView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('admin_settings.updated')
  })

  it('shows "No entries found" when audit log is empty', async () => {
    vi.mocked(adminAdapter.getSettings).mockResolvedValueOnce({ data: { data: baseSettings } } as any)
    vi.mocked(adminAdapter.getAuditLog).mockResolvedValueOnce(emptyLog as any)
    const wrapper = mount(AdminSettingsView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('No entries found')
  })
})
