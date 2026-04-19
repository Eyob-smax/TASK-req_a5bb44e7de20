<template>
  <div :class="['field', { 'field--error': !!error }]">
    <label v-if="label" :for="id" class="field__label">
      {{ label }}<span v-if="required" class="field__required" aria-hidden="true"> *</span>
    </label>
    <slot />
    <p v-if="error" :id="`${id}-error`" class="field__error" role="alert">{{ error }}</p>
    <p v-else-if="hint" class="field__hint">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ id: string; label?: string; error?: string; hint?: string; required?: boolean }>()
</script>
