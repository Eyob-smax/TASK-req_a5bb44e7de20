<template>
  <div class="catalog-view">
    <h1>Fee Catalog</h1>

    <LoadingSpinner v-if="store.loading" label="Loading catalog…" />
    <ErrorState v-else-if="store.error" :message="store.error" retryable @retry="store.fetchCatalog()" />
    <EmptyState v-else-if="store.catalog.length === 0" heading="No items available" />

    <div v-else class="catalog-grid">
      <BaseCard v-for="item in store.catalog" :key="item.id">
        <template #header>{{ item.name }}</template>
        <p class="catalog-card__desc">{{ item.description }}</p>
        <p class="catalog-card__price">{{ formatCents(item.unit_price_cents) }}</p>
        <div class="catalog-card__actions">
          <label class="catalog-card__qty-label">
            Qty
            <input
              type="number"
              min="1"
              max="99"
              :value="quantities[item.id] ?? 1"
              class="qty-input"
              @change="setQty(item.id, Number(($event.target as HTMLInputElement).value))"
            />
          </label>
          <button
            class="btn btn--primary btn--sm"
            :disabled="offline.isReadOnly"
            @click="addToCart(item.id)"
          >
            Add to order
          </button>
        </div>
      </BaseCard>
    </div>

    <!-- Cart summary -->
    <div v-if="cart.length > 0" class="cart-summary">
      <h2>Your Cart</h2>
      <ul>
        <li v-for="line in cart" :key="line.catalog_item_id">
          {{ itemName(line.catalog_item_id) }} × {{ line.quantity }}
        </li>
      </ul>
      <button
        class="btn btn--primary"
        :disabled="store.submitting || offline.isReadOnly"
        @click="placeOrder()"
      >
        {{ store.submitting ? 'Creating order…' : 'Place Order' }}
      </button>
      <p v-if="store.error" class="form-error">{{ store.error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrdersStore }   from '@/stores/orders'
import { useOfflineStore }  from '@/stores/offline'
import { formatCents }      from '@/composables/useMask'
import BaseCard       from '@/components/ui/BaseCard.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'
import EmptyState     from '@/components/ui/EmptyState.vue'

const store  = useOrdersStore()
const offline = useOfflineStore()
const router  = useRouter()

const quantities = ref<Record<number, number>>({})
const cart = ref<Array<{ catalog_item_id: number; quantity: number }>>([])

function setQty(itemId: number, qty: number) {
  quantities.value[itemId] = Math.max(1, qty)
}

function addToCart(itemId: number) {
  const qty      = quantities.value[itemId] ?? 1
  const existing = cart.value.find((l) => l.catalog_item_id === itemId)
  if (existing) existing.quantity += qty
  else cart.value.push({ catalog_item_id: itemId, quantity: qty })
}

function itemName(id: number): string {
  return store.catalog.find((c) => c.id === id)?.name ?? `Item ${id}`
}

async function placeOrder() {
  const order = await store.createOrder(cart.value)
  if (order) {
    cart.value = []
    router.push({ name: 'order-detail', params: { id: order.id } })
  }
}

onMounted(() => store.fetchCatalog())
</script>
