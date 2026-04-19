import http from './http'
import type { Thread, Post, Comment } from '../types/api'
import type { ApiResponse, PaginatedResponse } from '../types'

export const threadsAdapter = {
  list: (params?: { section_id?: number }) =>
    http.get<PaginatedResponse<Thread>>('/threads', { params }),

  get: (id: number) =>
    http.get<ApiResponse<Thread>>(`/threads/${id}`),

  create: (data: { section_id: number; type: string; title: string; body: string }) =>
    http.post<ApiResponse<Thread>>('/threads', data),

  update: (id: number, data: { title?: string; body?: string }) =>
    http.patch<ApiResponse<Thread>>(`/threads/${id}`, data),

  listPosts: (threadId: number) =>
    http.get<PaginatedResponse<Post>>(`/threads/${threadId}/posts`),

  createPost: (threadId: number, data: { body: string }) =>
    http.post<ApiResponse<Post>>(`/threads/${threadId}/posts`, data),

  updatePost: (threadId: number, postId: number, data: { body: string }) =>
    http.patch<ApiResponse<Post>>(`/threads/${threadId}/posts/${postId}`, data),

  deletePost: (threadId: number, postId: number) =>
    http.delete(`/threads/${threadId}/posts/${postId}`),
}
