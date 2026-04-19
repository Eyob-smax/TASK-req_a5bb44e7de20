<template>
  <div class="order-list-view">
    <div class="order-list-view__header">
      <h1>My Orders</h1>
      <RouterLink :to="{ name: 'catalog' }" class="btn btn--primary">Browse Catalog</RouterLink>
    </div>

    <LoadingSpinner v-if="store.loading" label="Loading orders…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetchOrders()" />
    <EmptyState v-else-if="store.orders.length === 0" heading="No orders" description="Browse the catalog to place your first order." />

    <BaseTable v-else :columns="columns">
      <tr v-for="order in store.orders" :key="order.id">
        <td>{{ order.id }}</td>
        <td><StatusChip :status="order.status" /></td>
        <td>{{ formatCents(order.total_cents) }}</td>
        <td>{{ order.auto_close_at ? formatTime(order.auto_close_at) : '—' }}</td>
        <td>
          <RouterLink :to="{ name: 'order-detail', params: { id: order.id } }" class="btn btn--sm btn--ghost">
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
import { useOrdersStore } from '@/stores/orders'
import { formatCents }    from '@/composables/useMask'
import BaseTable      from '@/components/ui/BaseTable.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store = useOrdersStore()

const columns = [
  { key: 'id',     label: 'Order #' },
  { key: 'status', label: 'Status' },
  { key: 'total',  label: 'Total' },
  { key: 'closes', label: 'Auto-closes' },
  { key: 'action', label: '' },
]

function formatTime(iso: string) { return new Date(iso).toLocaleString() }

onMounted(() => store.fetchOrders())
</script>
