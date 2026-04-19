<template>
  <div class="notification-center">
    <div class="notification-center__header">
      <h1>Notification Center</h1>
      <RouterLink :to="{ name: 'notification-preferences' }" class="btn btn--ghost btn--sm">
        Preferences
      </RouterLink>
    </div>

    <!-- Category filter tabs -->
    <BaseTabs v-model="activeTab" :tabs="tabs">
      <template v-for="tab in tabs" :key="tab.key" #[tab.key]>
        <!-- placeholder — list is shared below -->
      </template>
    </BaseTabs>

    <div class="notification-center__toolbar">
      <label class="checkbox-label">
        <input type="checkbox" v-model="showUnreadOnly" @change="load()" /> Unread only
      </label>
      <button
        class="btn btn--sm btn--secondary"
        :disabled="selectedIds.length === 0"
        @click="bulkMarkRead()"
      >
        Mark selected read
      </button>
      <button class="btn btn--sm btn--ghost" @click="markAllRead()">Mark all read</button>
    </div>

    <LoadingSpinner v-if="store.loading" label="Loading notifications…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="load()" />
    <EmptyState
      v-else-if="filteredItems.length === 0"
      heading="No notifications"
      description="You're all caught up."
    />

    <ul v-else class="notification-list">
      <li
        v-for="item in filteredItems"
        :key="item.id"
        :class="['notification-list__item', { 'notification-list__item--unread': !item.read_at }]"
      >
        <label class="notification-list__select">
          <input type="checkbox" :value="item.id" v-model="selectedIds" />
        </label>
        <div class="notification-list__content">
          <p class="notification-list__title">{{ item.title }}</p>
          <p class="notification-list__body">{{ item.body }}</p>
          <div class="notification-list__meta">
            <StatusChip :status="item.category" :label="categoryLabel(item.category)" />
            <time :datetime="item.created_at">{{ formatTime(item.created_at) }}</time>
          </div>
        </div>
        <button
          v-if="!item.read_at"
          class="btn btn--sm btn--ghost"
          @click="store.markRead([item.id])"
        >
          Mark read
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import type { NotificationCategory } from '@/types/api'
import BaseTabs       from '@/components/ui/BaseTabs.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store          = useNotificationsStore()
const activeTab      = ref('all')
const showUnreadOnly = ref(false)
const selectedIds    = ref<number[]>([])

const tabs = computed(() => [
  { key: 'all',           label: 'All',           badge: store.totalUnread },
  { key: 'announcements', label: 'Announcements',  badge: store.unreadCounts.announcements },
  { key: 'mentions',      label: 'Mentions',        badge: store.unreadCounts.mentions },
  { key: 'billing',       label: 'Billing',         badge: store.unreadCounts.billing },
  { key: 'system',        label: 'System',          badge: store.unreadCounts.system },
])

const filteredItems = computed(() =>
  store.items.filter((n) => {
    if (activeTab.value !== 'all' && n.category !== activeTab.value) return false
    if (showUnreadOnly.value && n.read_at) return false
    return true
  }),
)

function categoryLabel(cat: string): string {
  return { announcements: 'Announcement', mentions: 'Mention', billing: 'Billing', system: 'System' }[cat] ?? cat
}

function formatTime(iso: string) { return new Date(iso).toLocaleString() }

function load() {
  const cat = activeTab.value !== 'all' ? (activeTab.value as NotificationCategory) : undefined
  store.fetchList({ category: cat, unread_only: showUnreadOnly.value || undefined })
}

async function bulkMarkRead() {
  if (selectedIds.value.length === 0) return
  await store.markRead(selectedIds.value)
  selectedIds.value = []
}

async function markAllRead() {
  const cat = activeTab.value !== 'all' ? (activeTab.value as NotificationCategory) : undefined
  await store.markAllRead(cat)
}

watch(activeTab, () => { selectedIds.value = []; load() })

onMounted(() => {
  store.fetchUnreadCount()
  load()
})
</script>
