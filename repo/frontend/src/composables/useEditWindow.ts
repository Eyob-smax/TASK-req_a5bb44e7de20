export const EDIT_WINDOW_MS = 15 * 60 * 1000

export function isEditable(createdAt: string): boolean {
  return Date.now() - new Date(createdAt).getTime() < EDIT_WINDOW_MS
}

export function secondsLeft(createdAt: string): number {
  const remaining = EDIT_WINDOW_MS - (Date.now() - new Date(createdAt).getTime())
  return Math.max(0, Math.floor(remaining / 1000))
}
