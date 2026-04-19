<template>
  <span :class="['status-chip', `status-chip--${variant}`]">{{ label ?? status }}</span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type Status =
  | 'pending_payment' | 'paid' | 'canceled' | 'refunded' | 'redeemed'
  | 'open' | 'partial' | 'past_due' | 'void'
  | 'visible' | 'hidden' | 'locked'
  | 'pending' | 'approved' | 'rejected' | 'completed'
  | 'running' | 'failed' | 'draft' | 'published'
  | 'enrolled' | 'withdrawn'
  | string

const props = defineProps<{ status: Status; label?: string }>()

const VARIANT_MAP: Record<string, string> = {
  paid: 'success', completed: 'success', enrolled: 'success', published: 'success', approved: 'success',
  pending_payment: 'warning', pending: 'warning', partial: 'warning', past_due: 'warning', running: 'warning', open: 'warning',
  canceled: 'neutral', void: 'neutral', hidden: 'neutral', draft: 'neutral', withdrawn: 'neutral',
  refunded: 'info', redeemed: 'info',
  locked: 'error', rejected: 'error', failed: 'error',
}

const variant = computed(() => VARIANT_MAP[props.status] ?? 'neutral')
</script>
