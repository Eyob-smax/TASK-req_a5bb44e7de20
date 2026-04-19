<template>
  <div class="error-state" role="alert">
    <p class="error-state__heading">{{ heading ?? 'Something went wrong' }}</p>
    <p v-if="message" class="error-state__message">{{ message }}</p>
    <button v-if="retryable" class="btn btn--secondary" @click="$emit('retry')">Try again</button>
  </div>
</template>

<script setup lang="ts">
defineProps<{ heading?: string; message?: string; retryable?: boolean }>()
defineEmits<{ retry: [] }>()
</script>
