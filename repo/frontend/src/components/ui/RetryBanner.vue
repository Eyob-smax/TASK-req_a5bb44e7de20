<template>
  <div v-if="show" class="retry-banner" role="status">
    <span class="retry-banner__text">
      {{ count }} action{{ count !== 1 ? 's' : '' }} pending — will retry when connection is restored.
    </span>
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{ show: boolean; count: number }>()
</script>
