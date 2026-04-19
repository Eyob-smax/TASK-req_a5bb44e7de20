<template>
  <div class="receipt-view">
    <div class="receipt-view__toolbar no-print">
      <RouterLink :to="{ name: 'order-detail', params: { id } }" class="back-link">← Back to Order</RouterLink>
      <button class="btn btn--secondary" @click="window.print()">Print Receipt</button>
    </div>

    <LoadingSpinner v-if="store.loading" label="Loading receipt…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="load()" />

    <ReceiptPanel
      v-else-if="store.receipt && store.activeOrder"
      :receipt-number="store.receipt.receipt_number"
      :issued-at="store.receipt.issued_at"
      :lines="(store.activeOrder.lines ?? []).map((l) => ({
        id: l.id,
        description: `Item #${l.catalog_item_id}`,
        quantity: l.quantity,
        line_total_cents: l.line_total_cents,
      }))"
      :subtotal-cents="store.activeOrder.subtotal_cents"
      :tax-cents="store.activeOrder.tax_cents"
      :total-cents="store.activeOrder.total_cents"
    >
      <p class="receipt-panel__footer">Thank you for your payment. — CampusLearn</p>
    </ReceiptPanel>

    <EmptyState v-else heading="Receipt not available" />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'
import ReceiptPanel   from '@/components/ui/ReceiptPanel.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const route = useRoute()
const id    = Number(route.params.id)
const store = useOrdersStore()

const window = globalThis.window

async function load() {
  await store.fetchOrder(id)
  await store.fetchReceipt(id)
}

onMounted(load)
</script>
