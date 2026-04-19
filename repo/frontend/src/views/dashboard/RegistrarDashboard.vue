<template>
  <div class="dashboard-registrar">
    <h1 class="dashboard__title">Registrar Dashboard</h1>

    <LoadingSpinner v-if="store.loading" label="Loading…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetch()" />

    <template v-else-if="store.summary">
      <div class="dashboard-cards">
        <BaseCard :variant="importVariant">
          <template #header>Pending Roster Imports</template>
          <p class="dashboard-cards__value">{{ store.summary.pending_roster_imports ?? 0 }}</p>
          <RouterLink :to="{ name: 'roster-import' }">Manage roster imports →</RouterLink>
        </BaseCard>

        <BaseCard :variant="enrollmentVariant">
          <template #header>Pending Enrollments</template>
          <p class="dashboard-cards__value">{{ store.summary.pending_enrollments ?? 0 }}</p>
        </BaseCard>

        <BaseCard>
          <template #header>Unread Notifications</template>
          <p class="dashboard-cards__value">{{ store.summary.unread_notifications ?? 0 }}</p>
          <RouterLink :to="{ name: 'notifications' }">View notifications →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Billing Admin</template>
          <RouterLink :to="{ name: 'admin-billing' }">View billing overview →</RouterLink>
          <RouterLink :to="{ name: 'admin-refunds' }" style="display:block;margin-top:.5rem">View refunds →</RouterLink>
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
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store = useDashboardStore()

const importVariant     = computed(() => (store.summary?.pending_roster_imports ?? 0) > 0 ? 'warning' : 'default')
const enrollmentVariant = computed(() => (store.summary?.pending_enrollments ?? 0) > 0 ? 'warning' : 'default')

onMounted(() => store.fetch())
</script>
