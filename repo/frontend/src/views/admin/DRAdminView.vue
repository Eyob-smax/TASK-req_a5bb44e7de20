<template>
  <div class="dr-admin">
    <h1>Disaster Recovery Drills</h1>

    <p class="runbook-note">
      Quarterly DR drills are required per the restore runbook. Record each drill outcome below.
    </p>

    <div v-if="error" class="error-banner">{{ error }}</div>

    <form class="drill-form" @submit.prevent="submitDrill">
      <div class="form-row">
        <label for="drill-date">Drill Date</label>
        <input id="drill-date" v-model="form.drill_date" type="date" required />
      </div>
      <div class="form-row">
        <label for="outcome">Outcome</label>
        <select id="outcome" v-model="form.outcome" required>
          <option value="passed">Passed</option>
          <option value="partial">Partial</option>
          <option value="failed">Failed</option>
        </select>
      </div>
      <div class="form-row">
        <label for="notes">Notes</label>
        <textarea id="notes" v-model="form.notes" rows="3" maxlength="2000" />
      </div>
      <button type="submit" :disabled="submitting" class="btn-primary">
        {{ submitting ? 'Recording…' : 'Record Drill' }}
      </button>
    </form>

    <div class="drills-list">
      <h2>Drill History</h2>
      <p v-if="!drills.length && !loading">No drills recorded yet.</p>
      <table v-if="drills.length" class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Outcome</th>
            <th>Recorded By</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="drill in drills" :key="drill.id">
            <td>{{ drill.drill_date }}</td>
            <td>
              <span :class="outcomeClass(drill.outcome)">{{ drill.outcome }}</span>
            </td>
            <td>{{ drill.operator_user_id }}</td>
            <td>{{ drill.notes ?? '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminAdapter } from '@/adapters/admin'
import type { DrDrillRecord } from '@/types/api'

const drills = ref<DrDrillRecord[]>([])
const loading = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)

const form = ref({
  drill_date: '',
  outcome: 'passed' as DrDrillRecord['outcome'],
  notes: '',
})

async function loadDrills() {
  loading.value = true
  try {
    const res = await adminAdapter.listDrills()
    drills.value = res.data.data.data
  } catch {
    error.value = 'Failed to load drill history.'
  } finally {
    loading.value = false
  }
}

async function submitDrill() {
  submitting.value = true
  error.value = null
  try {
    await adminAdapter.recordDrill({
      drill_date: form.value.drill_date,
      outcome: form.value.outcome,
      notes: form.value.notes || undefined,
    })
    form.value = { drill_date: '', outcome: 'passed', notes: '' }
    await loadDrills()
  } catch {
    error.value = 'Failed to record drill.'
  } finally {
    submitting.value = false
  }
}

function outcomeClass(outcome: string) {
  return {
    'status-ok': outcome === 'passed',
    'status-warning': outcome === 'partial',
    'status-error': outcome === 'failed',
  }
}

onMounted(loadDrills)
</script>
