import http from './http'
import type { CatalogItem, FeeCategory, TaxRule } from '../types/api'
import type { ApiResponse, PaginatedResponse } from '../types'

export const catalogAdapter = {
  list: (all?: boolean) =>
    http.get<PaginatedResponse<CatalogItem>>('/catalog', { params: all ? { all: true } : {} }),

  create: (data: {
    fee_category_id: number
    sku: string
    name: string
    description?: string
    unit_price_cents: number
    is_active?: boolean
  }) =>
    http.post<ApiResponse<CatalogItem>>('/admin/catalog', data),

  update: (id: number, data: Partial<{
    fee_category_id: number
    sku: string
    name: string
    description: string
    unit_price_cents: number
    is_active: boolean
  }>) =>
    http.patch<ApiResponse<CatalogItem>>(`/admin/catalog/${id}`, data),

  listFeeCategories: () =>
    http.get<ApiResponse<FeeCategory[]>>('/admin/fee-categories'),

  createFeeCategory: (data: { code: string; label: string; is_taxable?: boolean }) =>
    http.post<ApiResponse<FeeCategory>>('/admin/fee-categories', data),

  updateFeeCategory: (id: number, data: Partial<{ code: string; label: string; is_taxable: boolean }>) =>
    http.patch<ApiResponse<FeeCategory>>(`/admin/fee-categories/${id}`, data),

  createTaxRule: (feeCategoryId: number, data: { rate_bps: number; effective_from: string; effective_to?: string }) =>
    http.post<ApiResponse<TaxRule>>(`/admin/fee-categories/${feeCategoryId}/tax-rules`, data),

  updateTaxRule: (feeCategoryId: number, taxRuleId: number, data: Partial<{ rate_bps: number; effective_from: string; effective_to: string | null }>) =>
    http.patch<ApiResponse<TaxRule>>(`/admin/fee-categories/${feeCategoryId}/tax-rules/${taxRuleId}`, data),
}
