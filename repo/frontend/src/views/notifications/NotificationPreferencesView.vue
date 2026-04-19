<template>
  <div class="notification-preferences">
    <RouterLink :to="{ name: 'notifications' }" class="back-link">← Back to notifications</RouterLink>
    <h1>Notification Preferences</h1>
    <p>Choose which categories you want to receive notifications for.</p>

    <LoadingSpinner v-if="loading" label="Loading preferences…" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load()" />

    <form v-else @submit.prevent="save()">
      <div class="preferences-list">
        <label v-for="cat in categories" :key="cat.key" class="preference-item">
          <input
            type="checkbox"
            :checked="store.preferences[cat.key] !== false"
            :disabled="saving"
            @change="toggle(cat.key, ($event.target as HTMLInputElement).checked)"
          />
          <div>
            <strong>{{ cat.label }}</strong>
            <p class="preference-item__description">{{ cat.description }}</p>
          </div>
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn--primary" :disabled="saving">
          {{ saving ? 'Saving…' : 'Save preferences' }}
        </button>
        <p v-if="saved" class="form-success">Preferences saved.</p>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState     from '@/components/ui/ErrorState.vue'

const store = useNotificationsStore()

const loading = ref(false)
const error   = ref('')
const saving  = ref(false)
const saved   = ref(false)

const categories = [
  { key: 'announcements', label: 'Announcements', description: 'Course and system announcements.' },
  { key: 'mentions',      label: 'Mentions',       description: 'When someone @mentions you in a discussion.' },
  { key: 'billing',       label: 'Billing',        description: 'Bill generation, payments, refunds, and penalties.' },
  { key: 'system',        label: 'System',          description: 'System-level alerts and status messages.' },
]

const pending = ref<Record<string, boolean>>({})

function toggle(key: string, value: boolean) {
  pending.value[key] = value
}

async function load() {
  loading.value = true
  error.value   = ''
  try {
    await store.fetchPreferences()
    pending.value = { ...store.preferences }
  } catch (e: any) {
    error.value = e?.message ?? 'Failed to load preferences.'
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  saved.value  = false
  try {
    await store.updatePreferences(pending.value)
    saved.value = true
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
