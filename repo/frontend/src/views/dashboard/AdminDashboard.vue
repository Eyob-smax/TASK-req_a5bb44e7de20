<template>
  <div class="dashboard-admin">
    <h1 class="dashboard__title">Administrator Dashboard</h1>

    <LoadingSpinner v-if="store.loading" label="Loading…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetch()" />

    <template v-else-if="store.summary">
      <AlertBanner
        v-if="store.summary.circuit_status === 'open'"
        type="error"
        message="Circuit breaker is OPEN — system is in read-only mode. Backend health may be degraded."
      />

      <div class="dashboard-cards">
        <BaseCard :variant="moderationVariant">
          <template #header>Moderation Queue</template>
          <p class="dashboard-cards__value">{{ store.summary.moderation_queue_size ?? 0 }}</p>
          <RouterLink :to="{ name: 'admin-moderation' }">Review queue →</RouterLink>
        </BaseCard>

        <BaseCard :variant="reconciliationVariant">
          <template #header>Reconciliation Flags</template>
          <p class="dashboard-cards__value">{{ store.summary.reconciliation_flags ?? 0 }}</p>
          <RouterLink :to="{ name: 'admin-refunds' }">View flags →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Unread Notifications</template>
          <p class="dashboard-cards__value">{{ store.summary.unread_notifications ?? 0 }}</p>
          <RouterLink :to="{ name: 'notifications' }">View notifications →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Health &amp; Observability</template>
          <RouterLink :to="{ name: 'admin-health' }">View health dashboard →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Billing Admin</template>
          <RouterLink :to="{ name: 'admin-billing' }">Billing overview →</RouterLink>
        </BaseCard>
      </div>
    </template>

    <EmptyState v-else heading="No data" />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useDashboardStore } from '@/stores/dashboard'
import BaseCard       from '@/components/ui/BaseCard.vue'
import AlertBanner    from '@/components/ui/AlertBanner.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store = useDashboardStore()

const moderationVariant    = computed(() => (store.summary?.moderation_queue_size ?? 0) > 0 ? 'warning' : 'default')
const reconciliationVariant = computed(() => (store.summary?.reconciliation_flags ?? 0) > 0 ? 'error' : 'default')

onMounted(() => store.fetch())
</script>
