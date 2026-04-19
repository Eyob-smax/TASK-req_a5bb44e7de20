import http from './http'
import type { AdminSettings, AuditLogEntry, BackupJob, DiagnosticExport, DrDrillRecord, HealthStatus } from '@/types/api'
import type { ApiResponse, PaginatedResponse } from '@/types'

export const adminAdapter = {
  // Health / observability
  health: () =>
    http.get<ApiResponse<HealthStatus>>('/health'),

  metrics: () =>
    http.get<ApiResponse<Record<string, unknown>>>('/health/metrics'),

  circuitStatus: () =>
    http.get<ApiResponse<{ circuit: string; error_rate_pct: number }>>('/health/circuit'),

  // Diagnostics
  triggerDiagnosticExport: () =>
    http.post<ApiResponse<DiagnosticExport>>('/admin/diagnostics/export', {}),

  exportDiagnostics: () =>
    http.post<ApiResponse<DiagnosticExport>>('/admin/diagnostics/export', {}),

  listExports: () =>
    http.get<ApiResponse<PaginatedResponse<DiagnosticExport>>>('/admin/diagnostics/exports'),

  // Backups
  listBackups: () =>
    http.get<ApiResponse<PaginatedResponse<BackupJob>>>('/admin/backups'),

  triggerBackup: () =>
    http.post<ApiResponse<BackupJob>>('/admin/backups/trigger', {}),

  getBackup: (id: number) =>
    http.get<ApiResponse<BackupJob>>(`/admin/backups/${id}`),

  // DR Drills
  listDrills: () =>
    http.get<ApiResponse<PaginatedResponse<DrDrillRecord>>>('/admin/dr-drills'),

  recordDrill: (data: { drill_date: string; outcome: DrDrillRecord['outcome']; notes?: string }) =>
    http.post<ApiResponse<DrDrillRecord>>('/admin/dr-drills', data),

  // Admin settings
  getSettings: () =>
    http.get<ApiResponse<AdminSettings>>('/admin/settings'),

  updateSettings: (settings: Partial<AdminSettings>) =>
    http.patch<ApiResponse<AdminSettings>>('/admin/settings', { settings }),

  // Audit log
  getAuditLog: (params?: { action?: string; actor_id?: number; target_type?: string; from?: string; to?: string }) =>
    http.get<ApiResponse<PaginatedResponse<AuditLogEntry>>>('/admin/audit-log', { params }),
}
