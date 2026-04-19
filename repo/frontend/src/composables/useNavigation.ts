import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'

export interface NavItem {
  label: string
  to: { name: string }
  badge?: number
}

export function useNavigation() {
  const auth          = useAuthStore()
  const notifications = useNotificationsStore()

  const navItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [{ label: 'Dashboard', to: { name: 'home' } }]

    // All authenticated users see notifications
    items.push({
      label: 'Notifications',
      to: { name: 'notifications' },
      badge: notifications.totalUnread || undefined,
    })

    if (auth.isStudent || auth.isTeacher || auth.isRegistrar || auth.isAdmin) {
      items.push({ label: 'Orders', to: { name: 'orders' } })
      items.push({ label: 'Catalog', to: { name: 'catalog' } })
      items.push({ label: 'Bills', to: { name: 'bills' } })
    }

    if (auth.isTeacher || auth.isAdmin) {
      items.push({ label: 'Grade Items', to: { name: 'grade-items' } })
    }

    if (auth.isRegistrar || auth.isAdmin) {
      items.push({ label: 'Roster Import', to: { name: 'roster-import' } })
    }

    if (auth.isAdmin) {
      items.push({ label: 'Moderation', to: { name: 'admin-moderation' } })
      items.push({ label: 'Billing Admin', to: { name: 'admin-billing' } })
      items.push({ label: 'Refunds', to: { name: 'admin-refunds' } })
      items.push({ label: 'Health', to: { name: 'admin-health' } })
    } else if (auth.isRegistrar) {
      items.push({ label: 'Billing Admin', to: { name: 'admin-billing' } })
      items.push({ label: 'Refunds', to: { name: 'admin-refunds' } })
    }

    return items
  })

  return { navItems }
}
