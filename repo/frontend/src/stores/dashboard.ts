import { defineStore } from 'pinia'
import { ref } from 'vue'
import { dashboardAdapter } from '@/adapters/dashboard'
import type { DashboardSummary } from '@/types/api'

export const useDashboardStore = defineStore('dashboard', () => {
  const summary = ref<DashboardSummary | null>(null)
  const loading = ref(false)
  const error   = ref<string | null>(null)

  async function fetch() {
    loading.value = true
    error.value   = null
    try {
      const res  = await dashboardAdapter.summary()
      summary.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load dashboard'
    } finally {
      loading.value = false
    }
  }

  function reset() {
    summary.value = null
    error.value   = null
  }

  return { summary, loading, error, fetch, reset }
})
