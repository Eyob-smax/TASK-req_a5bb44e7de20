<template>
  <div class="roster-import-view">
    <h1>Roster Import</h1>

    <!-- Permission denied -->
    <div v-if="!permission.isRegistrar.value && !permission.isAdmin.value" class="permission-denied" role="alert">
      <h2>Access Denied</h2>
      <p>Only registrars and administrators can import rosters.</p>
    </div>

    <template v-else>
      <AlertBanner
        v-if="offline.isReadOnly"
        type="error"
        message="System is in read-only mode. Roster imports are disabled."
      />

      <section class="roster-import__form">
        <h2>Upload Roster CSV</h2>
        <BaseField id="import-term" label="Term" required :error="errors.term">
          <select id="import-term" v-model="selectedTerm" class="field__input" :disabled="submitting">
            <option value="">Select a term…</option>
            <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </BaseField>

        <BaseField id="import-file" label="CSV File" required :error="errors.file">
          <input
            id="import-file"
            type="file"
            accept=".csv"
            :disabled="submitting || offline.isReadOnly"
            @change="onFileChange($event)"
          />
        </BaseField>

        <button
          class="btn btn--primary"
          :disabled="submitting || !selectedTerm || !csvFile || offline.isReadOnly"
          @click="submitImport()"
        >
          {{ submitting ? 'Uploading…' : 'Upload & Import' }}
        </button>
        <p v-if="submitError" class="form-error" role="alert">{{ submitError }}</p>
        <p v-if="submitSuccess" class="form-success" role="status">{{ submitSuccess }}</p>
      </section>

      <section class="roster-import__history">
        <h2>Import History</h2>
        <LoadingSpinner v-if="historyLoading" label="Loading history…" />
        <EmptyState v-else-if="imports.length === 0" heading="No imports yet" />
        <BaseTable v-else :columns="historyColumns">
          <tr v-for="imp in imports" :key="imp.id">
            <td>{{ imp.source_filename }}</td>
            <td>{{ termName(imp.term_id) }}</td>
            <td><StatusChip :status="imp.status" /></td>
            <td>{{ imp.success_count }} / {{ imp.row_count }}</td>
            <td>{{ imp.error_count }}</td>
            <td>{{ imp.completed_at ? formatDate(imp.completed_at) : '—' }}</td>
            <td>
              <button
                v-if="imp.error_count > 0"
                class="btn btn--sm btn--ghost"
                @click="viewErrors(imp)"
              >
                View errors
              </button>
            </td>
          </tr>
        </BaseTable>
      </section>

      <!-- Error detail modal -->
      <ConfirmModal
        :open="!!activeErrors"
        title="Import Errors"
        cancel-label="Close"
        :confirm-label="''"
        @cancel="activeErrors = null"
        @confirm="activeErrors = null"
      >
        <ul class="error-list">
          <li v-for="e in activeErrors" :key="`${e.row}-${e.field}`">
            Row {{ e.row }}, field <code>{{ e.field }}</code>: {{ e.message }}
          </li>
        </ul>
      </ConfirmModal>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import http from '@/adapters/http'
import { useOfflineStore } from '@/stores/offline'
import { usePermission }   from '@/composables/usePermission'
import type { RosterImport, Term } from '@/types/api'
import type { ApiResponse, PaginatedResponse } from '@/types'
import BaseField      from '@/components/ui/BaseField.vue'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import AlertBanner    from '@/components/ui/AlertBanner.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ConfirmModal   from '@/components/ui/ConfirmModal.vue'

const offline    = useOfflineStore()
const permission = usePermission()

const terms          = ref<Term[]>([])
const imports        = ref<RosterImport[]>([])
const selectedTerm   = ref<number | ''>('')
const csvFile        = ref<File | null>(null)
const submitting     = ref(false)
const historyLoading = ref(false)
const errors         = ref<{ term?: string; file?: string }>({})
const submitError    = ref('')
const submitSuccess  = ref('')
const activeErrors   = ref<Array<{ row: number; field: string; message: string }> | null>(null)

const historyColumns = [
  { key: 'file',     label: 'File' },
  { key: 'term',     label: 'Term' },
  { key: 'status',   label: 'Status' },
  { key: 'success',  label: 'Success/Total' },
  { key: 'errors',   label: 'Errors' },
  { key: 'completed',label: 'Completed' },
  { key: 'action',   label: '' },
]

function termName(id: number): string {
  return terms.value.find((t) => t.id === id)?.name ?? `Term ${id}`
}

function formatDate(iso: string) { return new Date(iso).toLocaleDateString() }

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  csvFile.value = input.files?.[0] ?? null
}

function viewErrors(imp: RosterImport) {
  activeErrors.value = imp.errors ?? []
}

async function submitImport() {
  errors.value     = {}
  submitError.value  = ''
  submitSuccess.value = ''
  if (!selectedTerm.value) { errors.value.term = 'Select a term.'; return }
  if (!csvFile.value)       { errors.value.file = 'Select a CSV file.'; return }

  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('file', csvFile.value)
    const res = await http.post<ApiResponse<RosterImport>>(
      `/terms/${selectedTerm.value}/roster-imports`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } },
    )
    submitSuccess.value = `Import started (ID ${res.data.data.id}). Status: ${res.data.data.status}.`
    csvFile.value       = null
    await loadHistory()
  } catch (e: any) {
    if (e?.code === 'FORBIDDEN') {
      submitError.value = 'You do not have permission to import roster for this term.'
    } else {
      submitError.value = e?.message ?? 'Import failed.'
    }
  } finally {
    submitting.value = false
  }
}

async function loadTerms() {
  try {
    const res   = await http.get<PaginatedResponse<Term>>('/terms')
    terms.value = res.data.data
  } catch { /* non-blocking */ }
}

async function loadHistory() {
  historyLoading.value = true
  try {
    const res      = await http.get<PaginatedResponse<RosterImport>>(`/terms/${selectedTerm.value}/roster-imports`)
    imports.value  = res.data.data
  } catch { /* non-blocking */ } finally {
    historyLoading.value = false
  }
}

onMounted(() => { loadTerms(); loadHistory() })
</script>
