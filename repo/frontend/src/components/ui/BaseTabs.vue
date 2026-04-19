<template>
  <div class="tabs">
    <div class="tabs__nav" role="tablist">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :id="`tab-${tab.key}`"
        role="tab"
        :aria-selected="modelValue === tab.key"
        :aria-controls="`tabpanel-${tab.key}`"
        :class="['tabs__tab', { 'tabs__tab--active': modelValue === tab.key }]"
        @click="$emit('update:modelValue', tab.key)"
      >
        {{ tab.label }}
        <span v-if="tab.badge" class="tabs__badge">{{ tab.badge }}</span>
      </button>
    </div>
    <div
      v-for="tab in tabs"
      :key="tab.key"
      :id="`tabpanel-${tab.key}`"
      role="tabpanel"
      :aria-labelledby="`tab-${tab.key}`"
      v-show="modelValue === tab.key"
      class="tabs__panel"
    >
      <slot :name="tab.key" />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  tabs: Array<{ key: string; label: string; badge?: number }>
  modelValue: string
}>()
defineEmits<{ 'update:modelValue': [key: string] }>()
</script>
