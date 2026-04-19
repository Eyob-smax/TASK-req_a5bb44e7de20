<template>
  <div class="thread-detail-view">
    <RouterLink :to="{ name: 'thread-list', params: { sectionId } }" class="back-link">← Back to discussions</RouterLink>

    <LoadingSpinner v-if="store.loading && !store.activeThread" label="Loading thread…" />
    <ErrorState v-else-if="store.error && !store.activeThread" :message="store.error" retryable @retry="loadThread()" />

    <template v-else-if="store.activeThread">
      <div class="thread-detail__header">
        <div class="thread-detail__meta">
          <StatusChip :status="store.activeThread.state" />
          <span class="thread-detail__type">{{ store.activeThread.thread_type }}</span>
        </div>
        <h1 class="thread-detail__title">{{ store.activeThread.title }}</h1>
        <time :datetime="store.activeThread.created_at">{{ formatTime(store.activeThread.created_at) }}</time>

        <!-- Moderation controls (admin/registrar only) -->
        <div v-if="permission.canModerate()" class="thread-detail__mod-controls">
          <button
            v-if="store.activeThread.state === 'visible'"
            class="btn btn--sm btn--danger"
            :disabled="offline.isReadOnly"
            @click="showHideModal = true"
          >
            Hide
          </button>
          <button
            v-if="store.activeThread.state === 'hidden'"
            class="btn btn--sm btn--secondary"
            :disabled="offline.isReadOnly"
            @click="handleRestore()"
          >
            Restore
          </button>
          <button
            v-if="store.activeThread.state !== 'locked'"
            class="btn btn--sm btn--danger"
            :disabled="offline.isReadOnly"
            @click="showLockModal = true"
          >
            Lock
          </button>
        </div>
      </div>

      <!-- Thread body -->
      <div class="thread-detail__body">{{ store.activeThread.body }}</div>

      <!-- Post list -->
      <section class="posts" aria-label="Replies">
        <h2>Replies</h2>

        <LoadingSpinner v-if="store.loading" label="Loading replies…" />
        <EmptyState v-else-if="store.posts.length === 0" heading="No replies yet" />

        <ul v-else class="post-list">
          <li v-for="post in visiblePosts" :key="post.id" class="post-list__item">
            <PostItem
              :post="post"
              :thread-id="store.activeThread.id"
              :can-edit="canEditPost(post)"
              :can-moderate="permission.canModerate()"
              :read-only="offline.isReadOnly"
              @edited="loadPosts()"
              @reported="loadPosts()"
              @hidden="loadPosts()"
              @restored="loadPosts()"
            />
          </li>
        </ul>
      </section>

      <!-- Reply form (only if thread is not locked) -->
      <div v-if="store.activeThread.state !== 'locked'" class="reply-form">
        <h3>Add Reply</h3>

        <div v-if="replyBlockedTerms.length > 0" class="sensitive-word-alert" role="alert">
          <p>Blocked terms detected — rewrite before posting:</p>
          <ul><li v-for="t in replyBlockedTerms" :key="t.term"><code>{{ t.term }}</code></li></ul>
        </div>

        <BaseField id="reply-body" label="Your reply" :error="replyError">
          <textarea
            id="reply-body"
            v-model="replyBody"
            rows="4"
            class="field__input"
            :disabled="submittingReply || offline.isReadOnly"
            @input="checkReplyContent"
          />
        </BaseField>
        <p class="mention-hint">Tip: use <code>@username</code> to mention someone.</p>

        <button
          class="btn btn--primary"
          :disabled="submittingReply || replyBlockedTerms.length > 0 || offline.isReadOnly"
          @click="submitReply()"
        >
          {{ submittingReply ? 'Posting…' : 'Post Reply' }}
        </button>
        <p v-if="replyServerError" class="form-error" role="alert">{{ replyServerError }}</p>
      </div>
      <AlertBanner v-else type="warning" message="This thread is locked. No new replies can be posted." />
    </template>

    <!-- Moderation modals -->
    <ConfirmModal
      :open="showHideModal"
      title="Hide Thread"
      message="This thread will no longer be visible to students."
      confirm-label="Hide"
      confirm-variant="danger"
      @confirm="handleHide()"
      @cancel="showHideModal = false"
    />
    <ConfirmModal
      :open="showLockModal"
      title="Lock Thread"
      message="This thread will be locked. No new replies will be allowed."
      confirm-label="Lock"
      confirm-variant="danger"
      @confirm="handleLock()"
      @cancel="showLockModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute }      from 'vue-router'
import { useCoursesStore }           from '@/stores/courses'
import { useAuthStore }              from '@/stores/auth'
import { useOfflineStore }           from '@/stores/offline'
import { usePermission }             from '@/composables/usePermission'
import { threadsAdapter }            from '@/adapters/threads'
import { moderationAdapter }         from '@/adapters/moderation'
import type { Post }                 from '@/types/api'

import StatusChip    from '@/components/ui/StatusChip.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorState    from '@/components/ui/ErrorState.vue'
import EmptyState    from '@/components/ui/EmptyState.vue'
import AlertBanner   from '@/components/ui/AlertBanner.vue'
import ConfirmModal  from '@/components/ui/ConfirmModal.vue'
import BaseField     from '@/components/ui/BaseField.vue'
import PostItem      from './PostItem.vue'

const route     = useRoute()
const sectionId = route.params.sectionId as string
const threadId  = Number(route.params.threadId)

const store      = useCoursesStore()
const auth       = useAuthStore()
const offline    = useOfflineStore()
const permission = usePermission()

const showHideModal = ref(false)
const showLockModal = ref(false)
const replyBody     = ref('')
const replyError    = ref('')
const replyServerError = ref('')
const submittingReply  = ref(false)
const replyBlockedTerms = ref<Array<{ term: string; start: number; end: number }>>([])
let checkTimeout: ReturnType<typeof setTimeout> | null = null

const visiblePosts = computed(() => store.posts.filter((p) => p.state !== 'hidden' || permission.canModerate()))

function canEditPost(post: Post): boolean {
  if (post.author_id !== auth.user?.id) return false
  if (!post.created_at) return false
  const createdMs  = new Date(post.created_at).getTime()
  const windowMs   = 15 * 60 * 1000
  return Date.now() - createdMs < windowMs
}

function formatTime(iso: string) { return new Date(iso).toLocaleString() }

async function loadThread() { await store.fetchThread(threadId) }
async function loadPosts()  { await store.fetchPosts(threadId) }

function checkReplyContent() {
  if (checkTimeout) clearTimeout(checkTimeout)
  if (!replyBody.value.trim()) { replyBlockedTerms.value = []; return }
  checkTimeout = setTimeout(async () => {
    try {
      const res = await moderationAdapter.checkContent(replyBody.value)
      replyBlockedTerms.value = res.data.data.blocked ? res.data.data.blocked_terms : []
    } catch { replyBlockedTerms.value = [] }
  }, 600)
}

async function submitReply() {
  replyError.value       = ''
  replyServerError.value = ''
  if (!replyBody.value.trim()) { replyError.value = 'Reply cannot be empty.'; return }
  if (replyBlockedTerms.value.length > 0) return

  submittingReply.value = true
  try {
    await threadsAdapter.createPost(threadId, { body: replyBody.value })
    replyBody.value = ''
    await loadPosts()
  } catch (e: any) {
    if (e?.code === 'SENSITIVE_WORDS_BLOCKED') {
      replyBlockedTerms.value = e.blocked_terms ?? []
      replyServerError.value  = 'Blocked words detected. Rewrite and try again.'
    } else {
      replyServerError.value = e?.message ?? 'Failed to post reply.'
    }
  } finally {
    submittingReply.value = false
  }
}

async function handleHide() {
  showHideModal.value = false
  await moderationAdapter.hideThread(threadId, 'Admin action')
  await loadThread()
}

async function handleRestore() {
  await moderationAdapter.restoreThread(threadId)
  await loadThread()
}

async function handleLock() {
  showLockModal.value = false
  await moderationAdapter.lockThread(threadId, 'Admin action')
  await loadThread()
}

onMounted(async () => {
  await loadThread()
  await loadPosts()
})
</script>
