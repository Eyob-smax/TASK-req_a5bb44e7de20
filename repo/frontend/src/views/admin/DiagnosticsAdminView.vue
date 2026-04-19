<template>
  <div class="diagnostics-admin">
    <h1>Diagnostic Exports</h1>

    <div v-if="readOnly" class="read-only-notice">
      Read-only mode — export trigger disabled.
    </div>

    <div class="actions">
      <button
        :disabled="readOnly || triggering"
        class="btn-primary"
        @click="triggerExport"
      >
        {{ triggering ? 'Generating…' : 'Generate Diagnostic Export' }}
      </button>
    </div>

    <div v-if="error" class="error-banner">{{ error }}</div>

    <div class="exports-list">
      <h2>Export History</h2>
      <p v-if="!exports.length && !loading">No exports found.</p>
      <table v-if="exports.length" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Status</th>
            <th>Size</th>
            <th>Checksum</th>
            <th>Completed</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="exp in exports" :key="exp.id">
            <td>{{ exp.id }}</td>
            <td>
              <span :class="statusClass(exp.status)">{{ exp.status }}</span>
            </td>
            <td>{{ exp.file_size_bytes !== null ? formatSize(exp.file_size_bytes) : '—' }}</td>
            <td class="monospace">{{ exp.checksum_sha256 ? exp.checksum_sha256.slice(0, 12) + '…' : '—' }}</td>
            <td>{{ exp.completed_at ?? '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminAdapter } from '@/adapters/admin'
import { useOfflineStore } from '@/stores/offline'
import type { DiagnosticExport } from '@/types/api'

const offlineStore = useOfflineStore()
const readOnly = computed(() => offlineStore.isReadOnly)

const exports = ref<DiagnosticExport[]>([])
const loading = ref(false)
const triggering = ref(false)
const error = ref<string | null>(null)

import { computed } from 'vue'

async function loadExports() {
  loading.value = true
  try {
    const res = await adminAdapter.listExports()
    exports.value = res.data.data.data
  } catch {
    error.value = 'Failed to load export history.'
  } finally {
    loading.value = false
  }
}

async function triggerExport() {
  triggering.value = true
  error.value = null
  try {
    await adminAdapter.triggerDiagnosticExport()
    await loadExports()
  } catch {
    error.value = 'Export failed. Check server logs.'
  } finally {
    triggering.value = false
  }
}

function statusClass(status: string) {
  return {
    'status-ok': status === 'completed',
    'status-error': status === 'failed',
    'status-running': status === 'running',
  }
}

function formatSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

onMounted(loadExports)
</script>
