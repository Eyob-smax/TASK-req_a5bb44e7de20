import http from './http'
import type { RosterImport } from '../types/api'
import type { ApiResponse, PaginatedResponse } from '../types'

export const rosterAdapter = {
  import: (termId: number, file: File) => {
    const form = new FormData()
    form.append('file', file)
    return http.post<ApiResponse<RosterImport>>(`/terms/${termId}/roster-imports`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  history: (termId: number) =>
    http.get<PaginatedResponse<RosterImport>>(`/terms/${termId}/roster-imports`),

  get: (id: number) =>
    http.get<ApiResponse<RosterImport>>(`/roster-imports/${id}`),
}
