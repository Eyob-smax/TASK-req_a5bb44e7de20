<template>
  <div class="auth-layout">
    <ReadOnlyBanner v-if="isReadOnly" />
    <SessionExpiredOverlay v-if="sessionExpiredVisible" @dismiss="dismissSessionExpired" />

    <AppNav />

    <main class="auth-layout__main">
      <RouterView />
    </main>

    <GlobalToast />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { RouterView } from 'vue-router'
import AppNav from '@/components/AppNav.vue'
import ReadOnlyBanner from '@/components/ReadOnlyBanner.vue'
import SessionExpiredOverlay from '@/components/SessionExpiredOverlay.vue'
import GlobalToast from '@/components/GlobalToast.vue'
import { useAuthStore } from '@/stores/auth'
import { useOfflineStore } from '@/stores/offline'

const auth    = useAuthStore()
const offline = useOfflineStore()

const isReadOnly             = offline.isReadOnly
const sessionExpiredVisible  = ref(false)

watch(
  () => auth.isSessionExpired,
  (expired) => {
    if (expired) sessionExpiredVisible.value = true
  },
)

function dismissSessionExpired() {
  sessionExpiredVisible.value = false
  auth.clearSession()
}
</script>
