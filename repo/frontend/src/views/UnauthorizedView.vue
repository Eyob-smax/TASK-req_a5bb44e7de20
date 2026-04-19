<template>
  <main class="error-page">
    <h1>Access Denied</h1>
    <p>You do not have permission to view this page.</p>
    <router-link :to="{ name: 'home' }">Return to dashboard</router-link>
  </main>
</template>

<script setup lang="ts">
</script>
