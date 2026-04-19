import http from './http'
import type { Bill, BillSchedule, Refund, RefundReasonCode } from '../types/api'
import type { ApiResponse, PaginatedResponse } from '../types'

export const billsAdapter = {
  mine: () =>
    http.get<PaginatedResponse<Bill>>('/bills'),

  get: (id: number) =>
    http.get<ApiResponse<Bill>>(`/bills/${id}`),

  adminList: () =>
    http.get<PaginatedResponse<Bill>>('/admin/bills'),

  adminGenerate: (
    data: { user_id: number; type: string; bill_schedule_id?: number; amount_cents?: number; reason?: string },
    idempotencyKey: string,
  ) =>
    http.post<ApiResponse<Bill>>('/admin/bills/generate', data, {
      headers: { 'Idempotency-Key': idempotencyKey },
    }),

  listSchedules: () =>
    http.get<ApiResponse<BillSchedule[]>>('/billing-schedules'),

  updateSchedule: (id: number, data: { status?: string; end_on?: string | null }) =>
    http.patch<ApiResponse<BillSchedule>>(`/billing-schedules/${id}`, data),

  createRefund: (billId: number, data: { amount_cents: number; reason_code: string; notes?: string }, idempotencyKey: string) =>
    http.post<ApiResponse<Refund>>(`/bills/${billId}/refunds`, data, {
      headers: { 'Idempotency-Key': idempotencyKey },
    }),

  listRefunds: () =>
    http.get<PaginatedResponse<Refund>>('/refunds'),

  getRefund: (id: number) =>
    http.get<ApiResponse<Refund>>(`/refunds/${id}`),

  reasonCodes: () =>
    http.get<ApiResponse<RefundReasonCode[]>>('/refund-reason-codes'),
}
