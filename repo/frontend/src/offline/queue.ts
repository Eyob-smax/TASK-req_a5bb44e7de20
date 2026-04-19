import { IdbStore } from './db'
import type { PendingAction } from '@/stores/offline'

// IndexedDB-backed pending-action queue for retryable offline operations.
export class PendingQueue {
  private store: IdbStore

  constructor(name: string) {
    this.store = new IdbStore(`${name}_queue`, 'actions')
  }

  async enqueue(action: PendingAction): Promise<void> {
    await this.store.set(action.id, action)
  }

  async dequeue(id: string): Promise<void> {
    await this.store.delete(id)
  }

  async getAll(): Promise<PendingAction[]> {
    const all = await this.store.getAll<PendingAction>()
    return all.map((entry) => entry.value)
  }

  async update(id: string, patch: Partial<PendingAction>): Promise<void> {
    const existing = await this.store.get<PendingAction>(id)
    if (!existing) return
    await this.store.set(id, { ...existing, ...patch })
  }
}
