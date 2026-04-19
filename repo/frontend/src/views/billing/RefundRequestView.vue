<template>
  <div class="refund-request-view">
    <RouterLink :to="{ name: 'bill-detail', params: { id } }" class="back-link">← Back to Bill</RouterLink>
    <h1>Request Refund — Bill #{{ id }}</h1>

    <AlertBanner
      v-if="store.conflict"
      type="warning"
      :message="store.conflict"
    />

    <form @submit.prevent="submit()">
      <BaseField id="refund-amount" label="Refund Amount ($)" :error="errors.amount" required>
        <input
          id="refund-amount"
          v-model="amountDollars"
          type="number"
          min="0.01"
          step="0.01"
          class="field__input"
          :disabled="store.submitting"
          required
        />
      </BaseField>

      <BaseField id="refund-reason" label="Reason Code" :error="errors.reason" required>
        <select id="refund-reason" v-model="reasonCode" class="field__input" :disabled="store.submitting" required>
          <option value="">Select reason…</option>
          <option v-for="rc in store.reasonCodes" :key="rc.code" :value="rc.code">{{ rc.label }}</option>
        </select>
      </BaseField>

      <BaseField id="refund-notes" label="Notes (optional)">
        <textarea id="refund-notes" v-model="notes" rows="3" class="field__input" :disabled="store.submitting" />
      </BaseField>

      <p v-if="store.error" class="form-error" role="alert">{{ store.error }}</p>
      <p v-if="successMsg" class="form-success">{{ successMsg }}</p>

      <div class="form-actions">
        <button type="submit" class="btn btn--primary" :disabled="store.submitting">
          {{ store.submitting ? 'Submitting…' : 'Submit Refund Request' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useBillingStore } from '@/stores/billing'
import AlertBanner from '@/components/ui/AlertBanner.vue'
import BaseField   from '@/components/ui/BaseField.vue'

const route  = useRoute()
const router = useRouter()
const id     = Number(route.params.id)
const store  = useBillingStore()

const amountDollars = ref('')
const reasonCode    = ref('')
const notes         = ref('')
const errors        = ref<{ amount?: string; reason?: string }>({})
const successMsg    = ref('')

async function submit() {
  errors.value  = {}
  successMsg.value = ''
  const amountCents = Math.round(parseFloat(amountDollars.value) * 100)
  if (!amountCents || amountCents <= 0) { errors.value.amount = 'Enter a valid amount.'; return }
  if (!reasonCode.value) { errors.value.reason = 'Select a reason code.'; return }

  const result = await store.requestRefund(id, {
    amount_cents: amountCents,
    reason_code:  reasonCode.value,
    notes:        notes.value || undefined,
  })

  if (result) {
    successMsg.value = `Refund request submitted (ID ${result.id}).`
    setTimeout(() => router.push({ name: 'bill-detail', params: { id } }), 1500)
  }
}

onMounted(() => store.fetchReasonCodes())
</script>
