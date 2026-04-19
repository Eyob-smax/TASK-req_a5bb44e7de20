<template>
  <div class="backup-admin">
    <h1>Backup Management</h1>

    <div v-if="readOnly" class="read-only-notice">
      Read-only mode — backup trigger disabled.
    </div>

    <div class="actions">
      <button
        :disabled="readOnly || triggering"
        class="btn-primary"
        @click="triggerBackup"
      >
        {{ triggering ? 'Triggering…' : 'Trigger Backup Now' }}
      </button>
    </div>

    <div v-if="error" class="error-banner">{{ error }}</div>

    <div class="retention-note">
      Backups are retained for {{ retentionDays }} days. Pruned entries are shown for audit trail.
    </div>

    <div class="backups-list">
      <h2>Backup History</h2>
      <p v-if="!backups.length && !loading">No backups found.</p>
      <table v-if="backups.length" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Scheduled</th>
            <th>Status</th>
            <th>Size</th>
            <th>Expires</th>
            <th>Completed</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="backup in backups" :key="backup.id">
            <td>{{ backup.id }}</td>
            <td>{{ backup.scheduled_for }}</td>
            <td>
              <span :class="statusClass(backup.status)">{{ backup.status }}</span>
            </td>
            <td>{{ backup.file_size_bytes !== null ? formatSize(backup.file_size_bytes) : '—' }}</td>
            <td :class="{ expired: isExpired(backup.retention_expires_on) }">
              {{ backup.retention_expires_on }}
            </td>
            <td>{{ backup.completed_at ?? '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { adminAdapter } from '@/adapters/admin'
import { useOfflineStore } from '@/stores/offline'
import type { BackupJob } from '@/types/api'

const offlineStore = useOfflineStore()
const readOnly = computed(() => offlineStore.isReadOnly)

const backups = ref<BackupJob[]>([])
const loading = ref(false)
const triggering = ref(false)
const error = ref<string | null>(null)
const retentionDays = 30

async function loadBackups() {
  loading.value = true
  try {
    const res = await adminAdapter.listBackups()
    backups.value = res.data.data.data
  } catch {
    error.value = 'Failed to load backup history.'
  } finally {
    loading.value = false
  }
}

async function triggerBackup() {
  triggering.value = true
  error.value = null
  try {
    await adminAdapter.triggerBackup()
    await loadBackups()
  } catch {
    error.value = 'Backup trigger failed. Check server logs.'
  } finally {
    triggering.value = false
  }
}

function statusClass(status: string) {
  return {
    'status-ok': status === 'completed',
    'status-error': status === 'failed',
    'status-running': status === 'running',
    'status-pruned': status === 'pruned',
  }
}

function isExpired(date: string): boolean {
  return new Date(date) < new Date()
}

function formatSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

onMounted(loadBackups)
</script>
