<template>
  <div class="loading-spinner" :class="{ 'loading-spinner--overlay': overlay }" role="status" aria-label="Loading">
    <span class="loading-spinner__icon" aria-hidden="true" />
    <span v-if="label" class="loading-spinner__label">{{ label }}</span>
  </div>
</template>

<script setup lang="ts">
defineProps<{ overlay?: boolean; label?: string }>()
</script>
