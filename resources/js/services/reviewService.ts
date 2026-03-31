import apiClient from './apiClient'
import type { ApiResponse } from '@/types/api.types'

export interface Review {
  id: number
  project_id: number
  reviewer_id: number
  reviewee_id: number
  rating: number
  comment: string | null
  created_at: string
}

export const reviewService = {
  async createReview(projectId: number, payload: { reviewee_id: number; rating: number; comment?: string }): Promise<ApiResponse<Review>> {
    const { data } = await apiClient.post(`/projects/${projectId}/reviews`, payload)
    return data
  },

  async getUserReviews(userId: number, perPage: number = 10): Promise<ApiResponse<any>> {
    const { data } = await apiClient.get(`/reviews/users/${userId}`, {
      params: { per_page: perPage }
    })
    return data
  }
}
