<template>
  <div class="refund-recon-view">
    <h1>Refunds &amp; Reconciliation</h1>

    <BaseTabs v-model="activeTab" :tabs="[{ key: 'refunds', label: 'Refunds' }, { key: 'flags', label: 'Reconciliation Flags' }]">
      <template #refunds>
        <LoadingSpinner v-if="store.loading" label="Loading refunds…" />
        <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetchRefunds()" />
        <EmptyState v-else-if="store.refunds.length === 0" heading="No refunds" />

        <BaseTable v-else :columns="refundColumns">
          <tr v-for="refund in store.refunds" :key="refund.id">
            <td>{{ refund.id }}</td>
            <td>{{ refund.bill_id }}</td>
            <td>{{ formatCents(refund.amount_cents) }}</td>
            <td><StatusChip :status="refund.status" /></td>
            <td>{{ refund.notes ?? '—' }}</td>
            <td>{{ refund.completed_at ? formatDate(refund.completed_at) : '—' }}</td>
          </tr>
        </BaseTable>
      </template>

      <template #flags>
        <LoadingSpinner v-if="flagsLoading" label="Loading flags…" />
        <EmptyState v-else-if="reconFlags.length === 0" heading="No open flags" />

        <BaseTable v-else :columns="flagColumns">
          <tr v-for="flag in reconFlags" :key="flag.id">
            <td>{{ flag.id }}</td>
            <td>{{ flag.source_type }} #{{ flag.source_id }}</td>
            <td><StatusChip :status="flag.status" /></td>
            <td>{{ flag.notes ?? '—' }}</td>
            <td>
              <button
                v-if="flag.status === 'open'"
                class="btn btn--sm btn--primary"
                :disabled="offline.isReadOnly"
                @click="resolveFlag(flag.id)"
              >
                Resolve
              </button>
            </td>
          </tr>
        </BaseTable>
      </template>
    </BaseTabs>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useBillingStore }   from '@/stores/billing'
import { useOfflineStore }   from '@/stores/offline'
import { useToastStore }     from '@/stores/toast'
import { formatCents }       from '@/composables/useMask'
import http from '@/adapters/http'
import type { ReconciliationFlag } from '@/types/api'
import type { PaginatedResponse }  from '@/types'
import BaseTabs       from '@/components/ui/BaseTabs.vue'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store   = useBillingStore()
const offline = useOfflineStore()
const toast   = useToastStore()

const activeTab   = ref('refunds')
const flagsLoading = ref(false)
const reconFlags   = ref<ReconciliationFlag[]>([])

const refundColumns = [
  { key: 'id',     label: 'Refund #' }, { key: 'bill',   label: 'Bill #' },
  { key: 'amount', label: 'Amount' },   { key: 'status', label: 'Status' },
  { key: 'notes',  label: 'Notes' },    { key: 'date',   label: 'Completed' },
]
const flagColumns = [
  { key: 'id',     label: '#' },    { key: 'source', label: 'Source' },
  { key: 'status', label: 'Status' }, { key: 'notes', label: 'Notes' },
  { key: 'action', label: '' },
]

function formatDate(iso: string) { return new Date(iso).toLocaleDateString() }

async function loadFlags() {
  flagsLoading.value = true
  try {
    const res      = await http.get<PaginatedResponse<ReconciliationFlag>>('/admin/reconciliation-flags')
    reconFlags.value = res.data.data
  } catch { /* non-blocking */ } finally {
    flagsLoading.value = false
  }
}

async function resolveFlag(id: number) {
  try {
    await http.post(`/admin/reconciliation-flags/${id}/resolve`, {})
    toast.success('Flag resolved.')
    await loadFlags()
  } catch (e: any) {
    toast.error(e?.message ?? 'Failed to resolve flag.')
  }
}

onMounted(() => {
  store.fetchRefunds()
  loadFlags()
})
</script>
