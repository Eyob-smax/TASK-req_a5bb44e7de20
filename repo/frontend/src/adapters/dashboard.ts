import http from './http'
import type { DashboardSummary } from '@/types/api'
import type { ApiResponse } from '@/types'

export const dashboardAdapter = {
  summary: () =>
    http.get<ApiResponse<DashboardSummary>>('/dashboard'),
}
