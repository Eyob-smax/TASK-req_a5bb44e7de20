import { test, expect } from '@playwright/test'

test.describe('CampusLearn application', () => {
  test('home page loads with correct title', async ({ page }) => {
    await page.goto('http://localhost:5173/')
    await expect(page).toHaveTitle(/CampusLearn/)
  })

  test('unauthenticated user is redirected to login', async ({ page }) => {
    await page.goto('http://localhost:5173/')
    await expect(page).toHaveURL(/\/login/)
  })
})
