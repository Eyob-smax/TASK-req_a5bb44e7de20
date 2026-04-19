<template>
  <div class="bill-list-view">
    <h1>My Bills</h1>

    <LoadingSpinner v-if="store.loading" label="Loading bills…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetchMyBills()" />
    <EmptyState v-else-if="store.bills.length === 0" heading="No bills" description="No bills have been issued to your account." />

    <BaseTable v-else :columns="columns">
      <tr v-for="bill in store.bills" :key="bill.id">
        <td>{{ bill.id }}</td>
        <td>{{ bill.type }}</td>
        <td><StatusChip :status="bill.status" /></td>
        <td>{{ formatCents(bill.total_cents) }}</td>
        <td>{{ bill.due_on ? formatDate(bill.due_on) : '—' }}</td>
        <td>{{ formatCents(bill.paid_cents) }}</td>
        <td>
          <RouterLink :to="{ name: 'bill-detail', params: { id: bill.id } }" class="btn btn--sm btn--ghost">
            View
          </RouterLink>
        </td>
      </tr>
    </BaseTable>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useBillingStore } from '@/stores/billing'
import { formatCents }     from '@/composables/useMask'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store = useBillingStore()

const columns = [
  { key: 'id',     label: 'Bill #' },
  { key: 'type',   label: 'Type' },
  { key: 'status', label: 'Status' },
  { key: 'total',  label: 'Total' },
  { key: 'due',    label: 'Due' },
  { key: 'paid',   label: 'Paid' },
  { key: 'action', label: '' },
]

function formatDate(iso: string) { return new Date(iso).toLocaleDateString() }

onMounted(() => store.fetchMyBills())
</script>
