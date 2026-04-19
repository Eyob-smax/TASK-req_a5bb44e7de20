import { IdbStore } from './db'

interface CacheEntry<T> {
  data: T
  cachedAt: number
  ttlMs: number
}

// IndexedDB-backed read-model cache with TTL support.
export class CacheStore {
  private store: IdbStore

  constructor(name: string) {
    this.store = new IdbStore(`${name}_cache`, 'entries')
  }

  async set<T>(key: string, data: T, ttlMs = 5 * 60 * 1000): Promise<void> {
    const entry: CacheEntry<T> = { data, cachedAt: Date.now(), ttlMs }
    await this.store.set(key, entry)
  }

  async get<T>(key: string): Promise<T | null> {
    const entry = await this.store.get<CacheEntry<T>>(key)
    if (!entry) return null
    if (Date.now() > entry.cachedAt + entry.ttlMs) {
      await this.store.delete(key)
      return null
    }
    return entry.data
  }

  async invalidate(key: string): Promise<void> {
    await this.store.delete(key)
  }

  async getStale<T>(key: string): Promise<T | null> {
    const entry = await this.store.get<CacheEntry<T>>(key)
    return entry ? entry.data : null
  }
}
