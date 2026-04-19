import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
  testDir: '.',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: 'list',

  use: {
    // In docker compose the app is served by the frontend nginx service.
    // BASE_URL is injected via the e2e service environment block.
    // For local dev outside docker, override with: BASE_URL=http://localhost:5173 npx playwright test
    baseURL: process.env.BASE_URL ?? 'http://localhost:5173',
    trace: 'on-first-retry',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // No webServer block: in docker compose the app is served by the frontend service.
  // For local dev, start the dev server manually before running playwright.
})
