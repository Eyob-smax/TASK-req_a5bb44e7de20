// Thin IndexedDB wrapper — opens one database per name, exposes typed get/set/delete/getAll.

const DB_VERSION = 1

export class IdbStore {
  private dbName: string
  private storeName: string
  private _db: IDBDatabase | null = null

  constructor(dbName: string, storeName: string) {
    this.dbName    = dbName
    this.storeName = storeName
  }

  private open(): Promise<IDBDatabase> {
    if (this._db) return Promise.resolve(this._db)

    return new Promise((resolve, reject) => {
      const req = indexedDB.open(this.dbName, DB_VERSION)

      req.onupgradeneeded = (e) => {
        const db = (e.target as IDBOpenDBRequest).result
        if (!db.objectStoreNames.contains(this.storeName)) {
          db.createObjectStore(this.storeName, { keyPath: 'key' })
        }
      }

      req.onsuccess = (e) => {
        this._db = (e.target as IDBOpenDBRequest).result
        resolve(this._db)
      }

      req.onerror = () => reject(req.error)
    })
  }

  async get<T>(key: string): Promise<T | null> {
    const db  = await this.open()
    return new Promise((resolve, reject) => {
      const tx  = db.transaction(this.storeName, 'readonly')
      const req = tx.objectStore(this.storeName).get(key)
      req.onsuccess = () => resolve(req.result ? (req.result as { key: string; value: T }).value : null)
      req.onerror   = () => reject(req.error)
    })
  }

  async set<T>(key: string, value: T): Promise<void> {
    const db  = await this.open()
    return new Promise((resolve, reject) => {
      const tx  = db.transaction(this.storeName, 'readwrite')
      const req = tx.objectStore(this.storeName).put({ key, value })
      req.onsuccess = () => resolve()
      req.onerror   = () => reject(req.error)
    })
  }

  async delete(key: string): Promise<void> {
    const db  = await this.open()
    return new Promise((resolve, reject) => {
      const tx  = db.transaction(this.storeName, 'readwrite')
      const req = tx.objectStore(this.storeName).delete(key)
      req.onsuccess = () => resolve()
      req.onerror   = () => reject(req.error)
    })
  }

  async getAll<T>(): Promise<Array<{ key: string; value: T }>> {
    const db  = await this.open()
    return new Promise((resolve, reject) => {
      const tx  = db.transaction(this.storeName, 'readonly')
      const req = tx.objectStore(this.storeName).getAll()
      req.onsuccess = () => resolve(req.result as Array<{ key: string; value: T }>)
      req.onerror   = () => reject(req.error)
    })
  }
}
