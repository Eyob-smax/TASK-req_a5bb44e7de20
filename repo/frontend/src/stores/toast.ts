import { defineStore } from 'pinia'
import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface Toast {
  id: number
  message: string
  type: ToastType
}

let _nextId = 0

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<Toast[]>([])

  function add(message: string, type: ToastType = 'info', durationMs = 4000) {
    const id = ++_nextId
    toasts.value.push({ id, message, type })
    if (durationMs > 0) setTimeout(() => remove(id), durationMs)
    return id
  }

  function remove(id: number) {
    const idx = toasts.value.findIndex((t) => t.id === id)
    if (idx !== -1) toasts.value.splice(idx, 1)
  }

  function success(message: string) { return add(message, 'success') }
  function error(message: string)   { return add(message, 'error', 6000) }
  function warning(message: string) { return add(message, 'warning') }
  function info(message: string)    { return add(message, 'info') }

  return { toasts, add, remove, success, error, warning, info }
})
