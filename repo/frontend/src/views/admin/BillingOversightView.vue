<template>
  <div class="billing-oversight-view">
    <h1>Billing Oversight</h1>

    <div class="billing-oversight__actions">
      <button class="btn btn--primary" :disabled="offline.isReadOnly" @click="showGenerate = true">
        Generate Bill
      </button>
    </div>

    <AlertBanner v-if="store.conflict" type="warning" :message="store.conflict" />

    <LoadingSpinner v-if="store.loading" label="Loading bills…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetchAdminBills()" />
    <EmptyState v-else-if="store.bills.length === 0" heading="No bills" />

    <BaseTable v-else :columns="columns">
      <tr v-for="bill in store.bills" :key="bill.id">
        <td>{{ bill.id }}</td>
        <td>{{ bill.user_id }}</td>
        <td>{{ bill.type }}</td>
        <td><StatusChip :status="bill.status" /></td>
        <td>{{ formatCents(bill.total_cents) }}</td>
        <td>{{ bill.due_on ? formatDate(bill.due_on) : '—' }}</td>
      </tr>
    </BaseTable>

    <!-- Generate Bill Modal -->
    <div v-if="showGenerate" class="modal-backdrop" @click.self="showGenerate = false">
      <div class="modal" role="dialog" aria-modal="true">
        <h2>Generate Bill</h2>
        <BaseField id="gen-user" label="User ID" required>
          <input id="gen-user" v-model="genForm.user_id" type="number" class="field__input" />
        </BaseField>
        <BaseField id="gen-type" label="Bill Type" required>
          <select id="gen-type" v-model="genForm.type" class="field__input">
            <option value="initial">Initial</option>
            <option value="supplemental">Supplemental</option>
            <option value="recurring">Recurring</option>
          </select>
        </BaseField>
        <BaseField id="gen-amount" label="Amount (cents)" required>
          <input id="gen-amount" v-model="genForm.amount_cents" type="number" min="1" class="field__input" />
        </BaseField>
        <BaseField id="gen-reason" label="Reason">
          <input id="gen-reason" v-model="genForm.reason" type="text" class="field__input" />
        </BaseField>
        <div class="modal__actions">
          <button class="btn btn--secondary" @click="showGenerate = false">Cancel</button>
          <button class="btn btn--primary" :disabled="store.submitting" @click="generate()">
            {{ store.submitting ? 'Generating…' : 'Generate' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useBillingStore } from '@/stores/billing'
import { useOfflineStore } from '@/stores/offline'
import { formatCents }     from '@/composables/useMask'
import { useToastStore }   from '@/stores/toast'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import AlertBanner    from '@/components/ui/AlertBanner.vue'
import BaseField      from '@/components/ui/BaseField.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store   = useBillingStore()
const offline = useOfflineStore()
const toast   = useToastStore()

const showGenerate = ref(false)
const genForm = ref({ user_id: 0, type: 'initial', amount_cents: 0, reason: '' })

const columns = [
  { key: 'id',      label: 'Bill #' },
  { key: 'user',    label: 'User ID' },
  { key: 'type',    label: 'Type' },
  { key: 'status',  label: 'Status' },
  { key: 'total',   label: 'Total' },
  { key: 'due',     label: 'Due' },
]

function formatDate(iso: string) { return new Date(iso).toLocaleDateString() }

async function generate() {
  const result = await store.generateBill({
    user_id:      genForm.value.user_id,
    type:         genForm.value.type,
    amount_cents: genForm.value.amount_cents,
    reason:       genForm.value.reason || undefined,
  })
  if (result) {
    showGenerate.value = false
    toast.success(`Bill #${result.id} generated.`)
  }
}

onMounted(() => store.fetchAdminBills())
</script>
