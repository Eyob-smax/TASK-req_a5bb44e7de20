<template>
  <div class="admin-settings">
    <h1>System Settings</h1>

    <div v-if="loadError" class="error-banner">{{ loadError }}</div>
    <div v-if="saveSuccess" class="success-banner">Settings saved.</div>

    <form v-if="settings" class="settings-form" @submit.prevent="save">
      <fieldset>
        <legend>Moderation</legend>
        <div class="form-row">
          <label>Edit Window (minutes)</label>
          <input v-model.number="settings.edit_window_minutes" type="number" min="1" max="1440" />
        </div>
      </fieldset>

      <fieldset>
        <legend>Orders</legend>
        <div class="form-row">
          <label>Order Auto-Close (minutes)</label>
          <input v-model.number="settings.order_auto_close_minutes" type="number" min="1" max="1440" />
        </div>
      </fieldset>

      <fieldset>
        <legend>Billing</legend>
        <div class="form-row">
          <label>Penalty Grace Days</label>
          <input v-model.number="settings.penalty_grace_days" type="number" min="0" max="365" />
        </div>
        <div class="form-row">
          <label>Penalty Rate (basis points)</label>
          <input v-model.number="settings.penalty_rate_bps" type="number" min="0" max="10000" />
        </div>
        <div class="form-row">
          <label>Backup Retention Days</label>
          <input v-model.number="settings.backup_retention_days" type="number" min="1" max="365" />
        </div>
        <div class="form-row">
          <label>Receipt Number Prefix</label>
          <input v-model="settings.receipt_number_prefix" type="text" maxlength="10" />
        </div>
      </fieldset>

      <fieldset>
        <legend>Notifications</legend>
        <div class="form-row">
          <label>Fan-out Batch Size</label>
          <input v-model.number="settings.fanout_batch_size" type="number" min="1" max="500" />
        </div>
      </fieldset>

      <div class="form-actions">
        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving…' : 'Save Settings' }}
        </button>
      </div>
    </form>

    <section class="audit-log-section">
      <h2>Recent Audit Log</h2>
      <p v-if="!auditEntries.length && !auditLoading">No entries found.</p>
      <table v-if="auditEntries.length" class="data-table">
        <thead>
          <tr>
            <th>Time</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Target</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in auditEntries" :key="entry.id">
            <td>{{ entry.created_at }}</td>
            <td>{{ entry.actor_user_id ?? 'system' }}</td>
            <td>{{ entry.action }}</td>
            <td>{{ entry.target_type }}{{ entry.target_id ? `#${entry.target_id}` : '' }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminAdapter } from '@/adapters/admin'
import type { AdminSettings, AuditLogEntry } from '@/types/api'

const settings = ref<AdminSettings | null>(null)
const auditEntries = ref<AuditLogEntry[]>([])
const loadError = ref<string | null>(null)
const saveSuccess = ref(false)
const saving = ref(false)
const auditLoading = ref(false)

async function loadSettings() {
  try {
    const res = await adminAdapter.getSettings()
    settings.value = res.data.data
  } catch {
    loadError.value = 'Failed to load settings.'
  }
}

async function loadAuditLog() {
  auditLoading.value = true
  try {
    const res = await adminAdapter.getAuditLog()
    auditEntries.value = res.data.data.data
  } catch {
    // non-fatal
  } finally {
    auditLoading.value = false
  }
}

async function save() {
  if (!settings.value) return
  saving.value = true
  saveSuccess.value = false
  try {
    const res = await adminAdapter.updateSettings(settings.value)
    settings.value = res.data.data
    saveSuccess.value = true
    await loadAuditLog()
  } catch {
    loadError.value = 'Failed to save settings.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadSettings(), loadAuditLog()])
})
</script>
