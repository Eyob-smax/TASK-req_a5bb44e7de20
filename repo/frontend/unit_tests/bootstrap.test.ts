import { describe, it, expect } from 'vitest'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import App from '../src/App.vue'

describe('App bootstrap', () => {
  it('creates a Vue application instance without error', () => {
    const app = createApp(App)
    expect(app).toBeDefined()
  })

  it('mounts with Pinia and Router installed', () => {
    const app = createApp(App)
    const pinia = createPinia()
    const router = createRouter({ history: createMemoryHistory(), routes: [] })

    app.use(pinia)
    app.use(router)

    const container = document.createElement('div')
    document.body.appendChild(container)
    app.mount(container)

    expect(container.innerHTML).toBeDefined()
    app.unmount()
    document.body.removeChild(container)
  })
})
