import { defineStore } from 'pinia'
import { ref } from 'vue'
import { adminAdapter } from '@/adapters/admin'
import type { HealthStatus } from '@/types/api'

export const useAdminStore = defineStore('admin', () => {
  const health      = ref<HealthStatus | null>(null)
  const loading     = ref(false)
  const error       = ref<string | null>(null)
  const exporting   = ref(false)

  async function fetchHealth() {
    loading.value = true
    error.value   = null
    try {
      const res   = await adminAdapter.health()
      health.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load health status'
    } finally {
      loading.value = false
    }
  }

  async function triggerDiagnosticExport() {
    exporting.value = true
    error.value     = null
    try {
      const res = await adminAdapter.exportDiagnostics()
      return res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Export failed'
      return null
    } finally {
      exporting.value = false
    }
  }

  return { health, loading, error, exporting, fetchHealth, triggerDiagnosticExport }
})
