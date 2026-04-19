<template>
  <div class="table-wrapper">
    <table class="base-table" v-bind="$attrs">
      <thead>
        <tr>
          <th v-for="col in columns" :key="col.key" :class="col.class">{{ col.label }}</th>
        </tr>
      </thead>
      <tbody>
        <slot />
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })
defineProps<{
  columns: Array<{ key: string; label: string; class?: string }>
}>()
</script>
