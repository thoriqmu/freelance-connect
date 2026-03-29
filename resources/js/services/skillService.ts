import apiClient from './apiClient'
import type { ApiResponse } from '@/types/api.types'

export interface Skill {
  id: number
  title: string
  created_at?: string
  updated_at?: string
}

export const skillService = {
  async searchSkills(query: string = ''): Promise<ApiResponse<Skill[]>> {
    const { data } = await apiClient.get('/skills', {
      params: { search: query }
    })
    return data
  }
}
