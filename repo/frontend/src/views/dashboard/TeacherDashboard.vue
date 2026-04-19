<template>
  <div class="dashboard-teacher">
    <h1 class="dashboard__title">Teacher Dashboard</h1>

    <LoadingSpinner v-if="store.loading" label="Loading…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetch()" />

    <template v-else-if="store.summary">
      <div class="dashboard-cards">
        <BaseCard>
          <template #header>Assigned Sections</template>
          <p class="dashboard-cards__value">{{ store.summary.assigned_sections ?? 0 }}</p>
          <RouterLink :to="{ name: 'grade-items' }">Manage grade items →</RouterLink>
        </BaseCard>

        <BaseCard :variant="draftVariant">
          <template #header>Draft Grade Items</template>
          <p class="dashboard-cards__value">{{ store.summary.draft_grade_items ?? 0 }}</p>
          <RouterLink :to="{ name: 'grade-items' }">Publish grade items →</RouterLink>
        </BaseCard>

        <BaseCard>
          <template #header>Unread Notifications</template>
          <p class="dashboard-cards__value">{{ store.summary.unread_notifications ?? 0 }}</p>
          <RouterLink :to="{ name: 'notifications' }">View notifications →</RouterLink>
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

const draftVariant = computed(() =>
  (store.summary?.draft_grade_items ?? 0) > 0 ? 'warning' : 'default',
)

onMounted(() => store.fetch())
</script>
