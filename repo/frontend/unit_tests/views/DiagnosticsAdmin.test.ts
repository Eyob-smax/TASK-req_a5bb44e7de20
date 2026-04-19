import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/admin', () => ({
  adminAdapter: {
    listExports:             vi.fn(),
    triggerDiagnosticExport: vi.fn(),
    exportDiagnostics:       vi.fn(),
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
import DiagnosticsAdminView from '../../src/views/admin/DiagnosticsAdminView.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', name: 'home', component: defineComponent({ template: '<div />' }) }],
})

const emptyExports = { data: { data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } } } }

describe('DiagnosticsAdminView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('shows "No exports found" when list is empty', async () => {
    vi.mocked(adminAdapter.listExports).mockResolvedValueOnce(emptyExports as any)
    const wrapper = mount(DiagnosticsAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('No exports found')
  })

  it('displays export history rows when exports exist', async () => {
    vi.mocked(adminAdapter.listExports).mockResolvedValueOnce({
      data: {
        data: {
          data: [{
            id: 1,
            status: 'completed',
            file_size_bytes: 4096,
            checksum_sha256: 'a'.repeat(64),
            completed_at: '2025-04-01T10:00:00Z',
            initiated_by: 1,
            file_path: '/diag/export.enc',
            generated_at: '2025-04-01T10:00:00Z',
            size_bytes: 4096,
          }],
          meta: { page: 1, per_page: 20, total: 1, last_page: 1 },
        },
      },
    } as any)
    const wrapper = mount(DiagnosticsAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('completed')
  })

  it('shows "Generate Diagnostic Export" button', async () => {
    vi.mocked(adminAdapter.listExports).mockResolvedValueOnce(emptyExports as any)
    const wrapper = mount(DiagnosticsAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Generate Diagnostic Export')
  })
})
