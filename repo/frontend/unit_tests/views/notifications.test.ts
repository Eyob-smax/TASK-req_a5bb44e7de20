import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/http', () => ({ default: { get: vi.fn(), post: vi.fn() } }))
vi.mock('../../src/adapters/notifications', () => ({
  notificationsAdapter: {
    list:              vi.fn(),
    unreadCount:       vi.fn().mockResolvedValue({ data: { data: { announcements: 2, mentions: 1 } } }),
    markRead:          vi.fn().mockResolvedValue({ data: { data: { marked: true } } }),
    getPreferences:    vi.fn().mockResolvedValue({ data: { data: { announcements: true } } }),
    updatePreferences: vi.fn().mockResolvedValue({ data: { data: { updated: true } } }),
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

import { notificationsAdapter } from '../../src/adapters/notifications'
import { useNotificationsStore } from '../../src/stores/notifications'
import NotificationCenterView from '../../src/views/notifications/NotificationCenterView.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/notifications', name: 'notifications', component: defineComponent({ template: '<div />' }) },
    { path: '/notifications/preferences', name: 'notification-preferences', component: defineComponent({ template: '<div />' }) },
  ],
})

describe('NotificationCenterView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('renders heading', () => {
    vi.mocked(notificationsAdapter.list).mockResolvedValueOnce({
      data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } },
    } as any)
    const wrapper = mount(NotificationCenterView, { global: { plugins: [createPinia(), router] } })
    expect(wrapper.text()).toContain('Notification Center')
  })

  it('renders empty state when no notifications', async () => {
    vi.mocked(notificationsAdapter.list).mockResolvedValueOnce({
      data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } },
    } as any)
    const wrapper = mount(NotificationCenterView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain("You're all caught up")
  })

  it('renders notification items', async () => {
    vi.mocked(notificationsAdapter.list).mockResolvedValueOnce({
      data: {
        data: [{
          id: 1, user_id: 1, category: 'billing', type: 'bill.generated',
          title: 'New Bill', body: 'Your bill is ready.', payload: {},
          read_at: null, created_at: new Date().toISOString(),
        }],
        meta: { page: 1, per_page: 20, total: 1, last_page: 1 },
      },
    } as any)
    const wrapper = mount(NotificationCenterView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('New Bill')
  })

  it('bulk mark-read button is disabled when nothing selected', async () => {
    vi.mocked(notificationsAdapter.list).mockResolvedValueOnce({
      data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } },
    } as any)
    const wrapper = mount(NotificationCenterView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    const bulkBtn = wrapper.findAll('button').find((b) => b.text().includes('Mark selected read'))
    expect(bulkBtn?.attributes('disabled')).toBeDefined()
  })
})
