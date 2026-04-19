import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, RoleName } from '@/types'
import http from '@/adapters/http'

const TOKEN_KEY      = 'auth_token'
const EXPIRES_AT_KEY = 'auth_expires_at'

export const useAuthStore = defineStore('auth', () => {
  const token      = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const expiresAt  = ref<number | null>(
    Number(localStorage.getItem(EXPIRES_AT_KEY)) || null,
  )
  const user       = ref<User | null>(null)
  const loading    = ref(false)

  const isSessionExpired = computed(() => {
    if (!token.value || !expiresAt.value) return false
    return Date.now() > expiresAt.value
  })

  const isAuthenticated = computed(
    () => token.value !== null && !isSessionExpired.value,
  )

  const roles = computed<RoleName[]>(() =>
    (user.value?.roles ?? []).map((r) => r.name),
  )

  const isAdmin      = computed(() => roles.value.includes('administrator'))
  const isRegistrar  = computed(() => roles.value.includes('registrar'))
  const isTeacher    = computed(() => roles.value.includes('teacher'))
  const isStudent    = computed(() => roles.value.includes('student'))

  function hasRole(role: RoleName): boolean {
    return roles.value.includes(role)
  }

  function setSession(newToken: string, newExpiresAt: string) {
    const expiryMs = new Date(newExpiresAt).getTime()
    token.value     = newToken
    expiresAt.value = expiryMs
    localStorage.setItem(TOKEN_KEY, newToken)
    localStorage.setItem(EXPIRES_AT_KEY, String(expiryMs))
  }

  function setUser(newUser: User) {
    user.value = newUser
  }

  function clearSession() {
    token.value     = null
    expiresAt.value = null
    user.value      = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(EXPIRES_AT_KEY)
  }

  async function initSession(): Promise<boolean> {
    if (!token.value) return false
    if (isSessionExpired.value) {
      clearSession()
      return false
    }
    if (user.value) return true

    loading.value = true
    try {
      const response = await http.get<{ data: User }>('/auth/me')
      user.value = response.data.data
      return true
    } catch {
      clearSession()
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    token,
    expiresAt,
    user,
    loading,
    isAuthenticated,
    isSessionExpired,
    roles,
    isAdmin,
    isRegistrar,
    isTeacher,
    isStudent,
    hasRole,
    setSession,
    setUser,
    clearSession,
    initSession,
  }
})
