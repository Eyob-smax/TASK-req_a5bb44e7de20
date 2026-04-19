// Exponential-backoff delay calculation with jitter.

export interface BackoffOptions {
  baseMs?: number
  maxMs?: number
  factor?: number
  jitter?: boolean
}

export function computeBackoffDelay(attempt: number, opts: BackoffOptions = {}): number {
  const { baseMs = 500, maxMs = 30_000, factor = 2, jitter = true } = opts
  const exponential = Math.min(baseMs * Math.pow(factor, attempt), maxMs)
  if (!jitter) return exponential
  // Full jitter: random in [0, exponential]
  return Math.random() * exponential
}

export async function sleepBackoff(attempt: number, opts?: BackoffOptions): Promise<void> {
  const delay = computeBackoffDelay(attempt, opts)
  return new Promise((resolve) => setTimeout(resolve, delay))
}

export function shouldRetry(httpStatus: number | undefined): boolean {
  if (!httpStatus) return true // network error — retry
  // Retry on server errors and rate-limit; never on auth/validation/conflict
  return httpStatus >= 500 || httpStatus === 429
}
