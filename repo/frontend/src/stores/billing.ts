import { defineStore } from 'pinia'
import { ref } from 'vue'
import { billsAdapter } from '@/adapters/bills'
import type { Bill, BillSchedule, Refund, RefundReasonCode } from '@/types/api'
import { generateIdempotencyKey } from '@/adapters/http'

export const useBillingStore = defineStore('billing', () => {
  const bills         = ref<Bill[]>([])
  const activeBill    = ref<Bill | null>(null)
  const schedules     = ref<BillSchedule[]>([])
  const refunds       = ref<Refund[]>([])
  const reasonCodes   = ref<RefundReasonCode[]>([])
  const loading       = ref(false)
  const error         = ref<string | null>(null)
  const submitting    = ref(false)
  const conflict      = ref<string | null>(null)

  async function fetchMyBills() {
    loading.value = true
    error.value   = null
    try {
      const res   = await billsAdapter.mine()
      bills.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load bills'
    } finally {
      loading.value = false
    }
  }

  async function fetchBill(id: number) {
    loading.value = true
    error.value   = null
    try {
      const res        = await billsAdapter.get(id)
      activeBill.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Bill not found'
    } finally {
      loading.value = false
    }
  }

  async function fetchAdminBills() {
    loading.value = true
    error.value   = null
    try {
      const res   = await billsAdapter.adminList()
      bills.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load bills'
    } finally {
      loading.value = false
    }
  }

  async function generateBill(data: {
    user_id: number; type: string; bill_schedule_id?: number; amount_cents?: number; reason?: string
  }) {
    submitting.value = true
    error.value      = null
    conflict.value   = null
    const key = generateIdempotencyKey()
    try {
      const res = await billsAdapter.adminGenerate(data, key)
      bills.value.unshift(res.data.data)
      return res.data.data
    } catch (e: any) {
      if (e?.code === 'IDEMPOTENCY_KEY_CONFLICT') {
        conflict.value = 'This bill was already generated. Refresh to see current state.'
      } else {
        error.value = e?.message ?? 'Failed to generate bill'
      }
      return null
    } finally {
      submitting.value = false
    }
  }

  async function fetchSchedules() {
    loading.value = true
    try {
      const res         = await billsAdapter.listSchedules()
      schedules.value   = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load schedules'
    } finally {
      loading.value = false
    }
  }

  async function fetchRefunds() {
    loading.value = true
    try {
      const res     = await billsAdapter.listRefunds()
      refunds.value = res.data.data
    } catch (e: any) {
      error.value = e?.message ?? 'Failed to load refunds'
    } finally {
      loading.value = false
    }
  }

  async function fetchReasonCodes() {
    try {
      const res           = await billsAdapter.reasonCodes()
      reasonCodes.value   = res.data.data
    } catch { /* non-blocking */ }
  }

  async function requestRefund(billId: number, data: { amount_cents: number; reason_code: string; notes?: string }) {
    submitting.value = true
    error.value      = null
    conflict.value   = null
    const key = generateIdempotencyKey()
    try {
      const res = await billsAdapter.createRefund(billId, data, key)
      refunds.value.unshift(res.data.data)
      return res.data.data
    } catch (e: any) {
      if (e?.code === 'REFUND_EXCEEDS_BALANCE') {
        conflict.value = 'Refund amount exceeds the bill balance.'
      } else if (e?.code === 'IDEMPOTENCY_KEY_CONFLICT') {
        conflict.value = 'This refund was already submitted.'
      } else {
        error.value = e?.message ?? 'Failed to request refund'
      }
      return null
    } finally {
      submitting.value = false
    }
  }

  function reset() {
    activeBill.value = null
    error.value      = null
    conflict.value   = null
  }

  return {
    bills, activeBill, schedules, refunds, reasonCodes,
    loading, error, submitting, conflict,
    fetchMyBills, fetchBill, fetchAdminBills, generateBill,
    fetchSchedules, fetchRefunds, fetchReasonCodes, requestRefund, reset,
  }
})
