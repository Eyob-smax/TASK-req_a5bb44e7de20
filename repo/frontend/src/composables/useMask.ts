// Masking helpers — sensitive data shown as masked by default.
// Reveal only when the caller explicitly passes `reveal: true`.

export function maskEmail(email: string, reveal = false): string {
  if (reveal) return email
  const [local, domain] = email.split('@')
  if (!domain) return '••••••••'
  const visible = local.length > 2 ? local[0] + '•'.repeat(local.length - 2) + local[local.length - 1] : '••'
  return `${visible}@${domain}`
}

export function maskName(name: string, reveal = false): string {
  if (reveal) return name
  const parts = name.trim().split(/\s+/)
  return parts.map((p, i) => (i === 0 ? p : p[0] + '•'.repeat(p.length - 1))).join(' ')
}

export function maskAmount(cents: number, reveal = false): string {
  if (reveal) return formatCents(cents)
  return '••••'
}

export function formatCents(cents: number): string {
  return (cents / 100).toLocaleString(undefined, { style: 'currency', currency: 'USD' })
}

export function useMask(defaultReveal = false) {
  return {
    maskEmail: (v: string) => maskEmail(v, defaultReveal),
    maskName:  (v: string) => maskName(v, defaultReveal),
    maskAmount:(v: number) => maskAmount(v, defaultReveal),
    formatCents,
  }
}
