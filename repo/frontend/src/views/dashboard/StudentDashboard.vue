<template>
  <div class="dashboard-student">
    <h1 class="dashboard__title">My Dashboard</h1>

    <LoadingSpinner v-if="store.loading" label="Loading your dashboard…" />
    <ErrorState
      v-else-if="store.error"
      :message="store.error"
      retryable
      @retry="store.fetch()"
    />

    <template v-else-if="store.summary">
      <div class="dashboard-cards">
        <BaseCard>
          <template #header>Enrolled Sections</template>
          <p class="dashboard-cards__value">{{ store.summary.enrolled_sections ?? 0 }}</p>
          <RouterLink :to="{ name: 'home' }">View my courses →</RouterLink>
        </BaseCard>

        <BaseCard :variant="openBillsVariant">
          <template #header>Open Bills</template>
          <p class="dashboard-cards__value">{{ store.summary.open_bills ?? 0 }}</p>
          <RouterLink :to="{ name: 'bills' }">View bills →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Unread Notifications</template>
          <p class="dashboard-cards__value">{{ store.summary.unread_notifications ?? 0 }}</p>
          <RouterLink :to="{ name: 'notifications' }">View notifications →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Pending Orders</template>
          <p class="dashboard-cards__value">{{ store.summary.pending_orders ?? 0 }}</p>
          <RouterLink :to="{ name: 'orders' }">View orders →</RouterLink>
        </BaseCard>
      </div>

      <RetryBanner :show="offline.retryBanner" :count="offline.pendingCount" />
    </template>

    <EmptyState v-else heading="No data available" description="Your dashboard information could not be loaded." />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useDashboardStore } from '@/stores/dashboard'
import { useOfflineStore }   from '@/stores/offline'
import BaseCard        from '@/components/ui/BaseCard.vue'
import LoadingSpinner  from '@/components/ui/LoadingSpinner.vue'
import ErrorState      from '@/components/ui/ErrorState.vue'
import EmptyState      from '@/components/ui/EmptyState.vue'
import RetryBanner     from '@/components/ui/RetryBanner.vue'

const store   = useDashboardStore()
const offline = useOfflineStore()

const openBillsVariant = computed(() =>
  (store.summary?.open_bills ?? 0) > 0 ? 'warning' : 'default',
)

onMounted(() => store.fetch())
</script>
