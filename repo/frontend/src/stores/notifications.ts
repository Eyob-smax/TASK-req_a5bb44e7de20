import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { notificationsAdapter } from '@/adapters/notifications'
import type { Notification, NotificationCategory } from '@/types/api'

export const useNotificationsStore = defineStore('notifications', () => {
  const items       = ref<Notification[]>([])
  const unreadCounts = ref<Record<string, number>>({})
  const preferences  = ref<Record<string, boolean>>({})
  const loading     = ref(false)
  const error       = ref<string | null>(null)
  const activeCategory = ref<NotificationCategory | null>(null)

  const totalUnread = computed(() =>
    Object.values(unreadCounts.value).reduce((s, n) => s + n, 0),
  )

  async function fetchList(params?: { category?: NotificationCategory; unread_only?: boolean }) {
    loading.value = true
    error.value   = null
    try {
      const res   = await notificationsAdapter.list(params)
      items.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load notifications'
    } finally {
      loading.value = false
    }
  }

  async function fetchUnreadCount() {
    try {
      const res         = await notificationsAdapter.unreadCount()
      unreadCounts.value = res.data.data
    } catch { /* non-blocking */ }
  }

  async function markRead(ids: number[]) {
    try {
      await notificationsAdapter.markRead({ ids })
      ids.forEach((id) => {
        const item = items.value.find((n) => n.id === id)
        if (item) item.read_at = new Date().toISOString()
      })
      await fetchUnreadCount()
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to mark notifications read'
    }
  }

  async function markAllRead(category?: NotificationCategory) {
    try {
      await notificationsAdapter.markRead({ category })
      items.value.forEach((n) => {
        if (!category || n.category === category) n.read_at = new Date().toISOString()
      })
      await fetchUnreadCount()
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to mark all read'
    }
  }

  async function fetchPreferences() {
    try {
      const res         = await notificationsAdapter.getPreferences()
      preferences.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load preferences'
    }
  }

  async function updatePreferences(prefs: Record<string, boolean>) {
    try {
      await notificationsAdapter.updatePreferences(prefs)
      preferences.value = { ...preferences.value, ...prefs }
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to save preferences'
    }
  }

  function setCategory(cat: NotificationCategory | null) {
    activeCategory.value = cat
  }

  return {
    items, unreadCounts, preferences, loading, error, activeCategory, totalUnread,
    fetchList, fetchUnreadCount, markRead, markAllRead,
    fetchPreferences, updatePreferences, setCategory,
  }
})
