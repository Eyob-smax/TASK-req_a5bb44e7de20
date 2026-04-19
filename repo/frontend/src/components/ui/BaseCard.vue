<template>
  <div :class="['card', variant && `card--${variant}`, { 'card--clickable': clickable }]" v-bind="$attrs">
    <div v-if="$slots.header" class="card__header"><slot name="header" /></div>
    <div class="card__body"><slot /></div>
    <div v-if="$slots.footer" class="card__footer"><slot name="footer" /></div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ variant?: 'default' | 'warning' | 'error' | 'success'; clickable?: boolean }>()
defineOptions({ inheritAttrs: false })
</script>
