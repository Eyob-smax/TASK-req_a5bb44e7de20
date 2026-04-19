<template>
  <div class="modal-backdrop" @click.self="$emit('cancel')">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="create-thread-title">
      <h2 id="create-thread-title">New Thread</h2>

      <div v-if="blockedTerms.length > 0" class="sensitive-word-alert" role="alert">
        <p>Your content contains blocked terms. Please rewrite before publishing:</p>
        <ul>
          <li v-for="t in blockedTerms" :key="t.term"><code>{{ t.term }}</code></li>
        </ul>
      </div>

      <form @submit.prevent="handleSubmit">
        <BaseField id="thread-type" label="Type" required>
          <select id="thread-type" v-model="form.type" required class="field__input">
            <option value="discussion">Discussion</option>
            <option value="announcement">Announcement</option>
            <option value="qa">Q&amp;A</option>
          </select>
        </BaseField>

        <BaseField id="thread-title" label="Title" :error="errors.title" required>
          <input id="thread-title" v-model="form.title" type="text" required class="field__input" :disabled="submitting" />
        </BaseField>

        <BaseField id="thread-body" label="Body" :error="errors.body" required>
          <textarea id="thread-body" v-model="form.body" rows="6" required class="field__input" :disabled="submitting" />
        </BaseField>

        <p v-if="serverError" class="form-error" role="alert">{{ serverError }}</p>

        <div class="modal__actions">
          <button type="button" class="btn btn--secondary" :disabled="submitting" @click="$emit('cancel')">Cancel</button>
          <button
            type="submit"
            class="btn btn--primary"
            :disabled="submitting || blockedTerms.length > 0"
          >
            {{ submitting ? 'Posting…' : 'Post Thread' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { threadsAdapter }    from '@/adapters/threads'
import { moderationAdapter } from '@/adapters/moderation'
import BaseField from '@/components/ui/BaseField.vue'

const props = defineProps<{ sectionId: number }>()
const emit  = defineEmits<{ created: []; cancel: [] }>()

const form = ref({ type: 'discussion', title: '', body: '' })
const errors      = ref<{ title?: string; body?: string }>({})
const serverError = ref('')
const submitting  = ref(false)
const blockedTerms = ref<Array<{ term: string; start: number; end: number }>>([])
let checkTimeout: ReturnType<typeof setTimeout> | null = null

// Check for sensitive words as body changes (debounced)
watch(() => form.value.body, (newBody) => {
  if (checkTimeout) clearTimeout(checkTimeout)
  if (!newBody.trim()) { blockedTerms.value = []; return }
  checkTimeout = setTimeout(async () => {
    try {
      const res = await moderationAdapter.checkContent(newBody)
      blockedTerms.value = res.data.data.blocked ? res.data.data.blocked_terms : []
    } catch {
      blockedTerms.value = []
    }
  }, 600)
})

async function handleSubmit() {
  errors.value      = {}
  serverError.value = ''

  if (!form.value.title.trim()) { errors.value.title = 'Title is required'; return }
  if (!form.value.body.trim())  { errors.value.body  = 'Body is required'; return }
  if (blockedTerms.value.length > 0) return // submit blocked

  submitting.value = true
  try {
    await threadsAdapter.create({
      section_id: props.sectionId,
      type:       form.value.type,
      title:      form.value.title,
      body:       form.value.body,
    })
    emit('created')
  } catch (e: any) {
    if (e?.code === 'SENSITIVE_WORDS_BLOCKED') {
      blockedTerms.value = e.blocked_terms ?? []
      serverError.value  = 'Your post contains blocked words. Please rewrite and try again.'
    } else {
      serverError.value = e?.message ?? 'Failed to create thread.'
    }
  } finally {
    submitting.value = false
  }
}
</script>
