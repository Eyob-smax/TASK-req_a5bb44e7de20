<template>
  <div class="thread-list-view">
    <div class="thread-list-view__header">
      <h1>Discussions — Section {{ sectionId }}</h1>
      <button
        v-if="permission.canManageSection(Number(sectionId)) || auth.isStudent"
        class="btn btn--primary"
        :disabled="offline.isReadOnly"
        @click="showCreate = true"
      >
        New Thread
      </button>
    </div>

    <div class="thread-list-view__filters">
      <SearchInput id="thread-search" v-model="search" placeholder="Search threads…" />
      <select v-model="threadType" class="select">
        <option value="">All types</option>
        <option value="discussion">Discussion</option>
        <option value="announcement">Announcement</option>
        <option value="qa">Q&amp;A</option>
      </select>
    </div>

    <LoadingSpinner v-if="store.loading" label="Loading discussions…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="load()" />
    <EmptyState
      v-else-if="store.threads.length === 0"
      heading="No threads yet"
      description="Be the first to start a discussion."
    />

    <ul v-else class="thread-list">
      <li v-for="thread in filteredThreads" :key="thread.id" class="thread-list__item">
        <RouterLink :to="{ name: 'thread-detail', params: { sectionId, threadId: thread.id } }" class="thread-list__link">
          <div class="thread-list__meta">
            <StatusChip :status="thread.state" />
            <span class="thread-list__type">{{ thread.thread_type }}</span>
          </div>
          <h2 class="thread-list__title">{{ thread.title }}</h2>
          <p class="thread-list__excerpt">{{ thread.body.slice(0, 120) }}{{ thread.body.length > 120 ? '…' : '' }}</p>
          <time class="thread-list__time" :datetime="thread.created_at">{{ formatTime(thread.created_at) }}</time>
        </RouterLink>
      </li>
    </ul>

    <!-- Create thread modal -->
    <CreateThreadModal
      v-if="showCreate"
      :section-id="Number(sectionId)"
      @created="onCreated"
      @cancel="showCreate = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useCoursesStore }  from '@/stores/courses'
import { useOfflineStore }  from '@/stores/offline'
import { useAuthStore }     from '@/stores/auth'
import { usePermission }    from '@/composables/usePermission'
import SearchInput    from '@/components/ui/SearchInput.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'
import CreateThreadModal from './CreateThreadModal.vue'
import type { Thread } from '@/types/api'

const route     = useRoute()
const sectionId = route.params.sectionId as string

const store      = useCoursesStore()
const offline    = useOfflineStore()
const auth       = useAuthStore()
const permission = usePermission()

const showCreate = ref(false)
const search     = ref('')
const threadType = ref('')

const filteredThreads = computed<Thread[]>(() => {
  return store.threads.filter((t) => {
    const matchSearch = !search.value || t.title.toLowerCase().includes(search.value.toLowerCase())
    const matchType   = !threadType.value || t.thread_type === threadType.value
    return matchSearch && matchType && t.state !== 'hidden'
  })
})

function formatTime(iso: string) {
  return new Date(iso).toLocaleDateString()
}

function load() {
  store.fetchThreads(Number(sectionId), {
    thread_type: threadType.value || undefined,
  })
}

function onCreated() {
  showCreate.value = false
  load()
}

onMounted(load)
</script>
