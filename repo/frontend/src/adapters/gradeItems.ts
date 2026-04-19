import http from './http'
import type { GradeItem } from '../types/api'
import type { ApiResponse } from '../types'

export const gradeItemsAdapter = {
  list: (sectionId: number) =>
    http.get<ApiResponse<GradeItem[]>>(`/sections/${sectionId}/grade-items`),

  create: (sectionId: number, data: { title: string; max_score: number; weight_bps?: number }) =>
    http.post<ApiResponse<GradeItem>>(`/sections/${sectionId}/grade-items`, data),

  update: (sectionId: number, id: number, data: { title?: string; max_score?: number; weight_bps?: number }) =>
    http.patch<ApiResponse<GradeItem>>(`/sections/${sectionId}/grade-items/${id}`, data),

  publish: (sectionId: number, id: number) =>
    http.post<ApiResponse<GradeItem>>(`/sections/${sectionId}/grade-items/${id}/publish`),
}
