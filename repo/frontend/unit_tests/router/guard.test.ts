import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useAuthStore } from '../../src/stores/auth'
import { defineComponent } from 'vue'

vi.mock('../../src/adapters/http', () => ({
  default: {
    get: vi.fn().mockRejectedValue(new Error('network')),
    post: vi.fn(),
  },
}))

const Placeholder = defineComponent({ template: '<div />' })

function buildRouter() {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: '/',
        name: 'home',
        component: Placeholder,
        meta: { requiresAuth: true },
      },
      {
        path: '/login',
        name: 'login',
        component: Placeholder,
        meta: { requiresAuth: false },
      },
      {
        path: '/unauthorized',
        name: 'unauthorized',
        component: Placeholder,
        meta: { requiresAuth: false },
      },
    ],
  })
}

describe('Router guard', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('redirects unauthenticated user from guarded route to login', async () => {
    const router = buildRouter()
    const auth   = useAuthStore()

    router.beforeEach(async (to) => {
      if (auth.isSessionExpired) {
        auth.clearSession()
        return { name: 'login' }
      }
      if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' }
      }
      if (to.name === 'login' && auth.isAuthenticated) {
        return { name: 'home' }
      }
    })

    await router.push('/')
    expect(router.currentRoute.value.name).toBe('login')
  })

  it('authenticated user can access guarded route', async () => {
    const router = buildRouter()
    const auth   = useAuthStore()

    auth.setSession('tok', new Date(Date.now() + 3_600_000).toISOString())
    auth.setUser({ id: 1, name: 'U', email: 'u@u.com', roles: [] })

    router.beforeEach(async (to) => {
      if (auth.isSessionExpired) {
        auth.clearSession()
        return { name: 'login' }
      }
      if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' }
      }
      if (to.name === 'login' && auth.isAuthenticated) {
        return { name: 'home' }
      }
    })

    await router.push('/')
    expect(router.currentRoute.value.name).toBe('home')
  })

  it('authenticated user is redirected away from login to home', async () => {
    const router = buildRouter()
    const auth   = useAuthStore()

    auth.setSession('tok', new Date(Date.now() + 3_600_000).toISOString())
    auth.setUser({ id: 1, name: 'U', email: 'u@u.com', roles: [] })

    router.beforeEach(async (to) => {
      if (auth.isSessionExpired) {
        auth.clearSession()
        return { name: 'login' }
      }
      if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' }
      }
      if (to.name === 'login' && auth.isAuthenticated) {
        return { name: 'home' }
      }
    })

    await router.push('/login')
    expect(router.currentRoute.value.name).toBe('home')
  })

  it('expired session clears auth and redirects to login', async () => {
    const router = buildRouter()
    const auth   = useAuthStore()

    // Set an expired session
    auth.setSession('expired_tok', new Date(Date.now() - 1000).toISOString())

    router.beforeEach(async (to) => {
      if (auth.isSessionExpired) {
        auth.clearSession()
        return { name: 'login' }
      }
      if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' }
      }
    })

    await router.push('/')
    expect(router.currentRoute.value.name).toBe('login')
    expect(auth.token).toBeNull()
  })
})
