<template>
  <div class="session-expired-overlay" role="dialog" aria-modal="true" aria-labelledby="session-expired-title">
    <div class="session-expired-overlay__box">
      <h2 id="session-expired-title">Session Expired</h2>
      <p>Your session has expired. Please sign in again to continue.</p>
      <button class="btn btn--primary" @click="$emit('dismiss')">Sign in</button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineEmits<{ dismiss: [] }>()
</script>
