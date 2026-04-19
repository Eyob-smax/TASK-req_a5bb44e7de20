<template>
  <div class="receipt-panel" aria-label="Receipt">
    <header class="receipt-panel__header">
      <h2 class="receipt-panel__title">Receipt</h2>
      <p class="receipt-panel__number">{{ receiptNumber }}</p>
      <p class="receipt-panel__date">Issued: {{ formatDate(issuedAt) }}</p>
    </header>

    <table class="receipt-panel__lines">
      <thead>
        <tr><th>Item</th><th>Qty</th><th class="text-right">Amount</th></tr>
      </thead>
      <tbody>
        <tr v-for="line in lines" :key="line.id">
          <td>{{ line.description }}</td>
          <td>{{ line.quantity }}</td>
          <td class="text-right">{{ formatCents(line.line_total_cents) }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr><td colspan="2">Subtotal</td><td class="text-right">{{ formatCents(subtotalCents) }}</td></tr>
        <tr v-if="taxCents > 0"><td colspan="2">Tax</td><td class="text-right">{{ formatCents(taxCents) }}</td></tr>
        <tr class="receipt-panel__total"><td colspan="2"><strong>Total</strong></td><td class="text-right"><strong>{{ formatCents(totalCents) }}</strong></td></tr>
      </tfoot>
    </table>

    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  receiptNumber: string
  issuedAt: string
  lines: Array<{ id: number; description: string; quantity: number; line_total_cents: number }>
  subtotalCents: number
  taxCents: number
  totalCents: number
}>()

function formatCents(cents: number): string {
  return (cents / 100).toLocaleString(undefined, { style: 'currency', currency: 'USD' })
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString()
}
</script>
