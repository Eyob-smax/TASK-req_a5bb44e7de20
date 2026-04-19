import http from './http'
import type { Appointment } from '../types/api'
import type { ApiResponse, PaginatedResponse } from '../types'

export const appointmentsAdapter = {
  list: () =>
    http.get<PaginatedResponse<Appointment>>('/appointments'),

  get: (id: number) =>
    http.get<ApiResponse<Appointment>>(`/appointments/${id}`),

  create: (data: {
    owner_user_id: number
    resource_type: string
    resource_ref?: string
    scheduled_start: string
    scheduled_end: string
    notes?: string
  }) =>
    http.post<ApiResponse<Appointment>>('/appointments', data),

  update: (id: number, data: Partial<{
    resource_type: string
    resource_ref: string
    scheduled_start: string
    scheduled_end: string
    notes: string
    status: string
  }>) =>
    http.patch<ApiResponse<Appointment>>(`/appointments/${id}`, data),

  cancel: (id: number) =>
    http.delete(`/appointments/${id}`),
}
