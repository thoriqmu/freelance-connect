import apiClient from './apiClient'
import type { ApiResponse } from '@/types/api.types'
import type { UserWithProfile } from '@/types/user.types'

export const profileService = {
  async getMyProfile(): Promise<ApiResponse<UserWithProfile>> {
    const { data } = await apiClient.get('/profile')
    return data
  },

  async updateBaseProfile(payload: { name: string; avatar?: File }): Promise<ApiResponse<UserWithProfile>> {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('name', payload.name)
    if (payload.avatar) {
      formData.append('avatar', payload.avatar)
    }

    const { data } = await apiClient.post('/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return data
  },

  async updateClientProfile(payload: { company_name: string | null; company_field: string | null }): Promise<ApiResponse<any>> {
    const { data } = await apiClient.put('/profile/client', payload)
    return data
  },

  async updateFreelancerProfile(payload: { skills: string[]; hourly_rate: number | null; bio: string | null; portfolio_url: string | null; availability: string }): Promise<ApiResponse<any>> {
    const { data } = await apiClient.put('/profile/freelancer', payload)
    return data
  }
}
