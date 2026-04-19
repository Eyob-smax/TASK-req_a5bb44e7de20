<template>
  <li class="timeline-item">
    <div class="timeline-item__dot" :class="`timeline-item__dot--${variant}`" aria-hidden="true" />
    <div class="timeline-item__content">
      <p class="timeline-item__label">{{ label }}</p>
      <time v-if="timestamp" class="timeline-item__time" :datetime="timestamp">{{ formatTime(timestamp) }}</time>
      <slot />
    </div>
  </li>
</template>

<script setup lang="ts">
defineProps<{ label: string; timestamp?: string; variant?: 'default' | 'success' | 'warning' | 'error' }>()

function formatTime(iso: string): string {
  return new Date(iso).toLocaleString()
}
</script>
