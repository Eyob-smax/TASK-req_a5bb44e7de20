<template>
  <div v-if="visible" :class="['alert-banner', `alert-banner--${type}`]" role="alert">
    <span class="alert-banner__message">{{ message }}</span>
    <button v-if="dismissible" class="alert-banner__dismiss" aria-label="Dismiss" @click="visible = false">×</button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  message: string
  type?: 'error' | 'warning' | 'success' | 'info'
  dismissible?: boolean
}>()

const visible = ref(true)
</script>
