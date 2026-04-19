import http from './http'
import type { Thread, Post, ModerationAction } from '@/types/api'
import type { ApiResponse, PaginatedResponse } from '@/types'

export const moderationAdapter = {
  // Admin: moderation queue
  queue: (params?: { state?: string; page?: number }) =>
    http.get<PaginatedResponse<Thread>>('/admin/moderation/queue', { params }),

  hideThread: (id: number, reason: string) =>
    http.post<ApiResponse<Thread>>(`/admin/threads/${id}/hide`, { reason }),

  restoreThread: (id: number) =>
    http.post<ApiResponse<Thread>>(`/admin/threads/${id}/restore`, {}),

  lockThread: (id: number, reason: string) =>
    http.post<ApiResponse<Thread>>(`/admin/threads/${id}/lock`, { reason }),

  hidePost: (threadId: number, postId: number, reason: string) =>
    http.post<ApiResponse<Post>>(`/admin/threads/${threadId}/posts/${postId}/hide`, { reason }),

  restorePost: (threadId: number, postId: number) =>
    http.post<ApiResponse<Post>>(`/admin/threads/${threadId}/posts/${postId}/restore`, {}),

  history: (targetType: string, targetId: number) =>
    http.get<ApiResponse<ModerationAction[]>>('/admin/moderation/history', {
      params: { target_type: targetType, target_id: targetId },
    }),

  // Submit-time sensitive-word check
  checkContent: (body: string) =>
    http.post<ApiResponse<{ blocked: boolean; blocked_terms: Array<{ term: string; start: number; end: number }> }>>(
      '/sensitive-words/check',
      { body },
    ),

  // User: report a post
  reportPost: (postId: number, reason: string) =>
    http.post<ApiResponse<{ reported: boolean }>>(`/posts/${postId}/reports`, { reason }),
}
