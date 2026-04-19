<template>
  <div class="empty-state">
    <p class="empty-state__heading">{{ heading }}</p>
    <p v-if="description" class="empty-state__description">{{ description }}</p>
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{ heading: string; description?: string }>()
</script>
