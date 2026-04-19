<template>
  <div class="search-input">
    <label :for="id" class="sr-only">{{ label ?? 'Search' }}</label>
    <input
      :id="id"
      type="search"
      :placeholder="placeholder ?? 'Search…'"
      :value="modelValue"
      :disabled="disabled"
      class="search-input__field"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
  </div>
</template>

<script setup lang="ts">
defineProps<{ id: string; modelValue: string; label?: string; placeholder?: string; disabled?: boolean }>()
defineEmits<{ 'update:modelValue': [value: string] }>()
</script>
