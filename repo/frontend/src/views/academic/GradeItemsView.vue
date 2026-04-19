<template>
  <div class="grade-items-view">
    <h1>Grade Items</h1>

    <!-- Permission denied for non-teacher/admin -->
    <div v-if="!permission.isTeacher.value && !permission.isAdmin.value" class="permission-denied" role="alert">
      <h2>Access Denied</h2>
      <p>Only teachers and administrators can manage grade items.</p>
    </div>

    <template v-else>
      <div class="grade-items-view__filters">
        <select v-model="selectedSection" class="select" @change="load()">
          <option value="">All sections</option>
          <option v-for="s in courses.sections" :key="s.id" :value="s.id">Section {{ s.section_code }}</option>
        </select>
      </div>

      <LoadingSpinner v-if="loading" label="Loading grade items…" />
      <ErrorState v-else-if="error" :message="error" retryable @retry="load()" />
      <EmptyState
        v-else-if="gradeItems.length === 0"
        heading="No grade items"
        description="No grade items exist for the selected section."
      />

      <BaseTable v-else :columns="columns">
        <tr v-for="item in gradeItems" :key="item.id">
          <td>{{ item.title }}</td>
          <td>{{ item.max_score }}</td>
          <td>{{ (item.weight_bps / 100).toFixed(2) }}%</td>
          <td><StatusChip :status="item.state" /></td>
          <td>
            <button
              v-if="item.state === 'draft'"
              class="btn btn--sm btn--primary"
              :disabled="offline.isReadOnly || !canManageItem(item.id)"
              @click="publishItem(item.id)"
            >
              Publish
            </button>
            <span v-else>{{ item.published_at ? formatDate(item.published_at) : '—' }}</span>
          </td>
        </tr>
      </BaseTable>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import http from '@/adapters/http'
import { useCoursesStore }  from '@/stores/courses'
import { useOfflineStore }  from '@/stores/offline'
import { usePermission }    from '@/composables/usePermission'
import { useToastStore }    from '@/stores/toast'
import type { GradeItem }   from '@/types/api'
import type { PaginatedResponse } from '@/types'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const courses     = useCoursesStore()
const offline     = useOfflineStore()
const permission  = usePermission()
const toast       = useToastStore()

const loading         = ref(false)
const error           = ref('')
const gradeItems      = ref<GradeItem[]>([])
const selectedSection = ref<number | ''>('')

const columns = [
  { key: 'title',   label: 'Title' },
  { key: 'max',     label: 'Max Score' },
  { key: 'weight',  label: 'Weight' },
  { key: 'state',   label: 'State' },
  { key: 'action',  label: 'Action' },
]

function formatDate(iso: string) { return new Date(iso).toLocaleDateString() }

function canManageItem(itemId: number): boolean {
  // Teacher must have scope for this section; admin can manage all
  if (permission.isAdmin.value) return true
  if (!selectedSection.value) return false
  return permission.canManageSection(Number(selectedSection.value))
}

async function load() {
  loading.value = true
  error.value   = ''
  try {
    const params = selectedSection.value ? { section_id: selectedSection.value } : undefined
    const res = await http.get<PaginatedResponse<GradeItem>>('/grade-items', { params })
    gradeItems.value = res.data.data
  } catch (e: any) {
    error.value = e?.message ?? 'Failed to load grade items.'
  } finally {
    loading.value = false
  }
}

async function publishItem(id: number) {
  try {
    await http.post(`/grade-items/${id}/publish`, {})
    toast.success('Grade item published.')
    await load()
  } catch (e: any) {
    if (e?.code === 'FORBIDDEN') {
      toast.error('You do not have permission to publish this grade item.')
    } else {
      toast.error(e?.message ?? 'Failed to publish.')
    }
  }
}

onMounted(() => {
  courses.fetchSections(0) // load all accessible sections
  load()
})
</script>
