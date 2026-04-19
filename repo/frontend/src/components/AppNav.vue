<template>
  <nav class="app-nav" aria-label="Main navigation">
    <RouterLink class="app-nav__brand" :to="{ name: 'home' }">CampusLearn</RouterLink>

    <ul class="app-nav__links">
      <li v-for="item in navItems" :key="item.label">
        <RouterLink :to="item.to" class="app-nav__link">
          {{ item.label }}
          <span v-if="item.badge && item.badge > 0" class="app-nav__badge" aria-label="`${item.badge} unread`">
            {{ item.badge > 99 ? '99+' : item.badge }}
          </span>
        </RouterLink>
      </li>
    </ul>

    <div class="app-nav__user">
      <span class="app-nav__username">{{ auth.user?.name ?? '…' }}</span>
      <button class="btn btn--ghost btn--sm" :disabled="loggingOut" @click="handleLogout">Sign out</button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNavigation } from '@/composables/useNavigation'
import { useNotificationsStore } from '@/stores/notifications'
import http from '@/adapters/http'

const router        = useRouter()
const auth          = useAuthStore()
const notifications = useNotificationsStore()
const { navItems }  = useNavigation()
const loggingOut    = ref(false)

// Bootstrap unread count (non-blocking)
notifications.fetchUnreadCount()

async function handleLogout() {
  loggingOut.value = true
  try {
    await http.post('/auth/logout')
  } finally {
    auth.clearSession()
    router.push({ name: 'login' })
    loggingOut.value = false
  }
}
</script>
