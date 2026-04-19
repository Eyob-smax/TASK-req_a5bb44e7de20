<template>
  <div class="health-view">
    <h1>Health &amp; Observability</h1>

    <LoadingSpinner v-if="store.loading" label="Loading health status…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetchHealth()" />

    <template v-else-if="store.health">
      <!-- Circuit breaker status -->
      <AlertBanner
        v-if="store.health.circuit === 'open'"
        type="error"
        message="Circuit breaker is OPEN — the system is in read-only mode. Backend services are degraded."
      />
      <AlertBanner
        v-else-if="store.health.circuit === 'half-open'"
        type="warning"
        message="Circuit breaker is HALF-OPEN — recovery in progress. Some writes may be unavailable."
      />

      <div class="health-grid">
        <BaseCard :variant="serviceVariant(store.health.status)">
          <template #header>Overall Status</template>
          <StatusChip :status="store.health.status" />
        </BaseCard>

        <BaseCard :variant="serviceVariant(store.health.database)">
          <template #header>Database</template>
          <StatusChip :status="store.health.database" />
        </BaseCard>

        <BaseCard :variant="serviceVariant(store.health.queue)">
          <template #header>Queue</template>
          <StatusChip :status="store.health.queue" />
        </BaseCard>

        <BaseCard :variant="errorRateVariant">
          <template #header>Error Rate</template>
          <p class="health-metric">{{ store.health.error_rate_pct.toFixed(2) }}%</p>
          <p v-if="store.health.error_rate_pct > 2" class="health-metric__alert">
            ⚠ Exceeds 2% threshold
          </p>
        </BaseCard>
      </div>

      <p class="health-view__timestamp">Last checked: {{ formatTime(store.health.timestamp) }}</p>

      <!-- Diagnostic export -->
      <section class="diagnostic-export">
        <h2>Diagnostic Export</h2>
        <p>Generate an AES-encrypted diagnostic export file for offline inspection.</p>
        <button
          class="btn btn--secondary"
          :disabled="store.exporting"
          @click="exportDiagnostics()"
        >
          {{ store.exporting ? 'Exporting…' : 'Export Diagnostics' }}
        </button>
        <p v-if="exportResult" class="form-success">Export generated: {{ exportResult.file_path }}</p>
        <p v-if="exportError" class="form-error">{{ exportError }}</p>
      </section>
    </template>

    <EmptyState v-else heading="No health data" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAdminStore }  from '@/stores/admin'
import type { ServiceStatus, DiagnosticExport } from '@/types/api'
import BaseCard       from '@/components/ui/BaseCard.vue'
import AlertBanner    from '@/components/ui/AlertBanner.vue'
import StatusChip     from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store = useAdminStore()

const exportResult = ref<DiagnosticExport | null>(null)
const exportError  = ref('')

function serviceVariant(s: ServiceStatus): 'success' | 'warning' | 'error' {
  if (s === 'healthy') return 'success'
  if (s === 'degraded') return 'warning'
  return 'error'
}

const errorRateVariant = computed(() =>
  (store.health?.error_rate_pct ?? 0) > 2 ? 'error' : 'success',
)

function formatTime(iso: string) { return new Date(iso).toLocaleString() }

async function exportDiagnostics() {
  exportResult.value = null
  exportError.value  = ''
  const result = await store.triggerDiagnosticExport()
  if (result) exportResult.value = result
  else exportError.value = store.error ?? 'Export failed.'
}

onMounted(() => store.fetchHealth())
</script>
