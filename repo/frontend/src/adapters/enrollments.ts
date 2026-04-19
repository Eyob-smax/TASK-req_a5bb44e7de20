import http from './http'
import type { Enrollment } from '@/types/api'
import type { ApiResponse } from '@/types'

export const enrollmentsAdapter = {
  approve: (id: number) =>
    http.post<ApiResponse<Enrollment>>(`/enrollments/${id}/approve`, {}),

  deny: (id: number) =>
    http.post<ApiResponse<Enrollment>>(`/enrollments/${id}/deny`, {}),
}
