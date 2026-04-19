<template>
  <div class="order-detail-view">
    <RouterLink :to="{ name: 'orders' }" class="back-link">← My Orders</RouterLink>

    <LoadingSpinner v-if="store.loading && !store.activeOrder" label="Loading order…" />
    <ErrorState v-else-if="store.error && !store.activeOrder" :message="store.error" retryable @retry="load()" />

    <template v-else-if="store.activeOrder">
      <div class="order-detail__header">
        <h1>Order #{{ store.activeOrder.id }}</h1>
        <StatusChip :status="store.activeOrder.status" />
      </div>

      <AlertBanner
        v-if="store.conflict"
        type="warning"
        :message="store.conflict"
        dismissible
      />

      <!-- Auto-close countdown -->
      <div v-if="store.activeOrder.status === 'pending_payment' && store.activeOrder.auto_close_at" class="auto-close-warning">
        <span>⚠ This order will auto-close at {{ formatTime(store.activeOrder.auto_close_at) }} if payment is not completed.</span>
      </div>

      <!-- Order lines -->
      <section class="order-lines">
        <h2>Items</h2>
        <BaseTable v-if="store.activeOrder.lines?.length" :columns="lineColumns">
          <tr v-for="line in store.activeOrder.lines" :key="line.id">
            <td>{{ line.catalog_item_id }}</td>
            <td>{{ line.quantity }}</td>
            <td>{{ formatCents(line.unit_price_cents) }}</td>
            <td>{{ formatCents(line.line_total_cents) }}</td>
          </tr>
        </BaseTable>
        <div class="order-totals">
          <div>Subtotal: <strong>{{ formatCents(store.activeOrder.subtotal_cents) }}</strong></div>
          <div>Tax: <strong>{{ formatCents(store.activeOrder.tax_cents) }}</strong></div>
          <div>Total: <strong>{{ formatCents(store.activeOrder.total_cents) }}</strong></div>
        </div>
      </section>

      <!-- Timeline -->
      <section class="order-timeline">
        <h2>Timeline</h2>
        <ul class="timeline">
          <TimelineItem
            v-for="(event, idx) in store.timeline"
            :key="idx"
            :label="String((event as any).label ?? (event as any).status ?? 'Event')"
            :timestamp="String((event as any).timestamp ?? (event as any).created_at ?? '')"
          />
        </ul>
      </section>

      <!-- Action buttons -->
      <div class="order-detail__actions">
        <RouterLink
          v-if="store.activeOrder.status === 'pending_payment'"
          :to="{ name: 'order-payment', params: { id: store.activeOrder.id } }"
          class="btn btn--primary"
        >
          Complete Payment
        </RouterLink>

        <RouterLink
          v-if="store.activeOrder.status === 'paid'"
          :to="{ name: 'order-receipt', params: { id: store.activeOrder.id } }"
          class="btn btn--secondary"
        >
          View Receipt
        </RouterLink>

        <button
          v-if="store.activeOrder.status === 'pending_payment'"
          class="btn btn--danger"
          :disabled="store.submitting || offline.isReadOnly"
          @click="cancelOrder()"
        >
          Cancel Order
        </button>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useOrdersStore }  from '@/stores/orders'
import { useOfflineStore } from '@/stores/offline'
import { formatCents }     from '@/composables/useMask'
import { useToastStore }   from '@/stores/toast'
import http from '@/adapters/http'
import StatusChip    from '@/components/ui/StatusChip.vue'
import AlertBanner   from '@/components/ui/AlertBanner.vue'
import BaseTable     from '@/components/ui/BaseTable.vue'
import TimelineItem  from '@/components/ui/TimelineItem.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState    from '@/components/ui/ErrorState.vue'

const route  = useRoute()
const id     = Number(route.params.id)
const store  = useOrdersStore()
const offline = useOfflineStore()
const toast  = useToastStore()

const lineColumns = [
  { key: 'item', label: 'Item' }, { key: 'qty', label: 'Qty' },
  { key: 'unit', label: 'Unit Price' }, { key: 'total', label: 'Total' },
]

function formatTime(iso: string) { return new Date(iso).toLocaleString() }

async function load() {
  await store.fetchOrder(id)
  await store.fetchTimeline(id)
}

async function cancelOrder() {
  try {
    await http.delete(`/orders/${id}`)
    toast.success('Order cancelled.')
    await store.fetchOrder(id)
  } catch (e: any) {
    toast.error(e?.message ?? 'Failed to cancel order.')
  }
}

onMounted(load)
</script>
