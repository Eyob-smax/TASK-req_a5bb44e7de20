import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/admin', () => ({
  adminAdapter: {
    listBackups:   vi.fn(),
    triggerBackup: vi.fn(),
    getBackup:     vi.fn(),
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
import BackupAdminView from '../../src/views/admin/BackupAdminView.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', name: 'home', component: defineComponent({ template: '<div />' }) }],
})

const emptyBackups = { data: { data: { data: [], meta: { page: 1, per_page: 20, total: 0, last_page: 1 } } } }

describe('BackupAdminView', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('shows "No backups found" when list is empty', async () => {
    vi.mocked(adminAdapter.listBackups).mockResolvedValueOnce(emptyBackups as any)
    const wrapper = mount(BackupAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('No backups found')
  })

  it('displays backup rows with status and retention date', async () => {
    vi.mocked(adminAdapter.listBackups).mockResolvedValueOnce({
      data: {
        data: {
          data: [{
            id: 1,
            scheduled_for: '2025-04-01T04:00:00Z',
            status: 'completed',
            file_size_bytes: 2048,
            checksum_sha256: 'b'.repeat(64),
            retention_expires_on: '2025-05-01',
            completed_at: '2025-04-01T04:01:10Z',
            file_path: '/backups/backup.enc',
          }],
          meta: { page: 1, per_page: 20, total: 1, last_page: 1 },
        },
      },
    } as any)
    const wrapper = mount(BackupAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 20))
    expect(wrapper.text()).toContain('completed')
    expect(wrapper.text()).toContain('2025-05-01')
  })

  it('shows trigger button', async () => {
    vi.mocked(adminAdapter.listBackups).mockResolvedValueOnce(emptyBackups as any)
    const wrapper = mount(BackupAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('Trigger Backup Now')
  })

  it('displays retention note', async () => {
    vi.mocked(adminAdapter.listBackups).mockResolvedValueOnce(emptyBackups as any)
    const wrapper = mount(BackupAdminView, { global: { plugins: [createPinia(), router] } })
    await new Promise((r) => setTimeout(r, 10))
    expect(wrapper.text()).toContain('30 days')
  })
})
