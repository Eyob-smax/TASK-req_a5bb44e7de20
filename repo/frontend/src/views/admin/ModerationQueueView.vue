<template>
  <div class="moderation-queue-view">
    <h1>Moderation Queue</h1>

    <div class="moderation-queue__filters">
      <select v-model="stateFilter" class="select" @change="load()">
        <option value="">All states</option>
        <option value="visible">Visible</option>
        <option value="hidden">Hidden</option>
      </select>
    </div>

    <LoadingSpinner v-if="loading" label="Loading queue…" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load()" />
    <EmptyState v-else-if="threads.length === 0" heading="Queue is empty" description="No threads requiring moderation." />

    <BaseTable v-else :columns="columns">
      <tr v-for="thread in threads" :key="thread.id">
        <td>{{ thread.id }}</td>
        <td>{{ thread.title }}</td>
        <td>{{ thread.thread_type }}</td>
        <td><StatusChip :status="thread.state" /></td>
        <td class="moderation-queue__actions">
          <button
            v-if="thread.state === 'visible'"
            class="btn btn--sm btn--danger"
            :disabled="offline.isReadOnly"
            @click="openHide(thread.id)"
          >
            Hide
          </button>
          <button
            v-if="thread.state === 'hidden'"
            class="btn btn--sm btn--secondary"
            :disabled="offline.isReadOnly"
            @click="restore(thread.id)"
          >
            Restore
          </button>
          <button
            v-if="thread.state !== 'locked'"
            class="btn btn--sm btn--danger"
            :disabled="offline.isReadOnly"
            @click="openLock(thread.id)"
          >
            Lock
          </button>
        </td>
      </tr>
    </BaseTable>

    <!-- Modals -->
    <ConfirmModal
      :open="!!actionTarget && actionType === 'hide'"
      title="Hide Thread"
      message="This thread will be hidden from students."
      confirm-label="Hide"
      confirm-variant="danger"
      :confirming="acting"
      @confirm="executeHide()"
      @cancel="actionTarget = null"
    />
    <ConfirmModal
      :open="!!actionTarget && actionType === 'lock'"
      title="Lock Thread"
      message="No new replies will be allowed."
      confirm-label="Lock"
      confirm-variant="danger"
      :confirming="acting"
      @confirm="executeLock()"
      @cancel="actionTarget = null"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { moderationAdapter } from '@/adapters/moderation'
import { useOfflineStore }   from '@/stores/offline'
import { useToastStore }     from '@/stores/toast'
import type { Thread }       from '@/types/api'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import ConfirmModal   from '@/components/ui/ConfirmModal.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const offline = useOfflineStore()
const toast   = useToastStore()

const loading     = ref(false)
const error       = ref('')
const threads     = ref<Thread[]>([])
const stateFilter = ref('')
const actionTarget = ref<number | null>(null)
const actionType   = ref<'hide' | 'lock' | null>(null)
const acting       = ref(false)

const columns = [
  { key: 'id',     label: '#' },
  { key: 'title',  label: 'Title' },
  { key: 'type',   label: 'Type' },
  { key: 'state',  label: 'State' },
  { key: 'action', label: 'Actions' },
]

async function load() {
  loading.value = true
  error.value   = ''
  try {
    const res    = await moderationAdapter.queue({ state: stateFilter.value || undefined })
    threads.value = res.data.data
  } catch (e: any) {
    error.value = e?.message ?? 'Failed to load queue.'
  } finally {
    loading.value = false
  }
}

function openHide(id: number) { actionTarget.value = id; actionType.value = 'hide' }
function openLock(id: number) { actionTarget.value = id; actionType.value = 'lock' }

async function restore(id: number) {
  try {
    await moderationAdapter.restoreThread(id)
    toast.success('Thread restored.')
    await load()
  } catch (e: any) {
    toast.error(e?.message ?? 'Failed to restore.')
  }
}

async function executeHide() {
  if (!actionTarget.value) return
  acting.value = true
  try {
    await moderationAdapter.hideThread(actionTarget.value, 'Admin moderation action')
    toast.success('Thread hidden.')
    actionTarget.value = null
    await load()
  } catch (e: any) {
    toast.error(e?.message ?? 'Failed to hide.')
  } finally {
    acting.value = false
  }
}

async function executeLock() {
  if (!actionTarget.value) return
  acting.value = true
  try {
    await moderationAdapter.lockThread(actionTarget.value, 'Admin moderation action')
    toast.success('Thread locked.')
    actionTarget.value = null
    await load()
  } catch (e: any) {
    toast.error(e?.message ?? 'Failed to lock.')
  } finally {
    acting.value = false
  }
}

onMounted(load)
</script>
