<template>
  <Teleport to="body">
    <div v-if="open" class="modal-backdrop" @click.self="$emit('cancel')">
      <div class="modal" role="dialog" aria-modal="true" :aria-labelledby="titleId">
        <h2 :id="titleId" class="modal__title">{{ title }}</h2>
        <p v-if="message" class="modal__message">{{ message }}</p>
        <slot />
        <div class="modal__actions">
          <button class="btn btn--secondary" :disabled="confirming" @click="$emit('cancel')">{{ cancelLabel }}</button>
          <button :class="['btn', `btn--${confirmVariant}`]" :disabled="confirming" @click="$emit('confirm')">
            {{ confirming ? 'Please wait…' : confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  open: boolean
  title: string
  message?: string
  confirmLabel?: string
  cancelLabel?: string
  confirmVariant?: 'primary' | 'danger'
  confirming?: boolean
}>()

defineEmits<{ confirm: []; cancel: [] }>()

const titleId       = computed(() => `modal-title-${Math.random().toString(36).slice(2)}`)
const confirmLabel  = computed(() => props.confirmLabel ?? 'Confirm')
const cancelLabel   = computed(() => props.cancelLabel ?? 'Cancel')
const confirmVariant = computed(() => props.confirmVariant ?? 'primary')
</script>
