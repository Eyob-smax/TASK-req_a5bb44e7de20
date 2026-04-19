import http from './http'
import type { Order, PaymentAttempt, Receipt } from '../types/api'
import type { ApiResponse, PaginatedResponse } from '../types'

export const ordersAdapter = {
  list: () =>
    http.get<PaginatedResponse<Order>>('/orders'),

  get: (id: number) =>
    http.get<ApiResponse<Order>>(`/orders/${id}`),

  create: (lines: Array<{ catalog_item_id: number; quantity: number }>) =>
    http.post<ApiResponse<Order>>('/orders', { lines }),

  cancel: (id: number) =>
    http.delete(`/orders/${id}`),

  timeline: (id: number) =>
    http.get<ApiResponse<unknown[]>>(`/orders/${id}/timeline`),

  initiatePayment: (orderId: number, data: { method: string; kiosk_id?: string }, idempotencyKey: string) =>
    http.post<ApiResponse<PaymentAttempt>>(`/orders/${orderId}/payment`, data, {
      headers: { 'Idempotency-Key': idempotencyKey },
    }),

  completePayment: (orderId: number, attemptId: number, idempotencyKey: string) =>
    http.post<ApiResponse<Order>>(`/orders/${orderId}/payment/complete`, { attempt_id: attemptId }, {
      headers: { 'Idempotency-Key': idempotencyKey },
    }),

  getReceipt: (orderId: number) =>
    http.get<ApiResponse<Receipt>>(`/orders/${orderId}/receipt`),
}
