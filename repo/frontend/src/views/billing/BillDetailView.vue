<template>
  <div class="bill-detail-view">
    <RouterLink :to="{ name: 'bills' }" class="back-link">← My Bills</RouterLink>

    <LoadingSpinner v-if="store.loading && !store.activeBill" label="Loading bill…" />
    <ErrorState v-else-if="store.error && !store.activeBill" :message="store.error" retryable @retry="load()" />

    <template v-else-if="store.activeBill">
      <div class="bill-detail__header">
        <h1>Bill #{{ store.activeBill.id }}</h1>
        <StatusChip :status="store.activeBill.status" />
      </div>

      <div class="bill-detail__summary">
        <div>Type: <strong>{{ store.activeBill.type }}</strong></div>
        <div>Issued: <strong>{{ formatDate(store.activeBill.issued_on) }}</strong></div>
        <div v-if="store.activeBill.due_on">Due: <strong :class="{ 'text-danger': isPastDue }">{{ formatDate(store.activeBill.due_on) }}</strong></div>
      </div>

      <!-- Line items -->
      <section>
        <h2>Charges</h2>
        <BaseTable v-if="store.activeBill.lines?.length" :columns="lineColumns">
          <tr v-for="line in store.activeBill.lines" :key="line.id">
            <td>{{ line.description }}</td>
            <td>{{ line.quantity }}</td>
            <td>{{ formatCents(line.unit_price_cents) }}</td>
            <td>{{ formatCents(line.line_total_cents) }}</td>
          </tr>
        </BaseTable>

        <div class="bill-totals">
          <div>Subtotal: <strong>{{ formatCents(store.activeBill.subtotal_cents) }}</strong></div>
          <div>Tax: <strong>{{ formatCents(store.activeBill.tax_cents) }}</strong></div>
          <div>Total: <strong>{{ formatCents(store.activeBill.total_cents) }}</strong></div>
          <div>Paid: <strong>{{ formatCents(store.activeBill.paid_cents) }}</strong></div>
          <div v-if="store.activeBill.refunded_cents > 0">Refunded: <strong>{{ formatCents(store.activeBill.refunded_cents) }}</strong></div>
        </div>
      </section>

      <div class="bill-detail__actions" v-if="store.activeBill.status !== 'void'">
        <RouterLink
          v-if="canRequestRefund"
          :to="{ name: 'bill-refund', params: { id: store.activeBill.id } }"
          class="btn btn--secondary"
        >
          Request Refund
        </RouterLink>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useBillingStore }  from '@/stores/billing'
import { useAuthStore }     from '@/stores/auth'
import { formatCents }      from '@/composables/useMask'
import StatusChip     from '@/components/ui/StatusChip.vue'
import BaseTable      from '@/components/ui/BaseTable.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'

const route = useRoute()
const id    = Number(route.params.id)
const store = useBillingStore()
const auth  = useAuthStore()

const lineColumns = [
  { key: 'desc',  label: 'Description' },
  { key: 'qty',   label: 'Qty' },
  { key: 'unit',  label: 'Unit Price' },
  { key: 'total', label: 'Total' },
]

function formatDate(iso: string) { return new Date(iso).toLocaleDateString() }

const isPastDue = computed(() => {
  if (!store.activeBill?.due_on) return false
  return new Date(store.activeBill.due_on) < new Date()
})

const canRequestRefund = computed(() =>
  store.activeBill?.status === 'paid' && (auth.isRegistrar || auth.isAdmin),
)

function load() { store.fetchBill(id) }

onMounted(load)
</script>
