import apiClient from './apiClient'
import type { ApiResponse } from '@/types/api.types'
import type { AuthLoginPayload, AuthRegisterPayload, AuthLoginResponse, AuthRegisterResponse } from '@/types/auth.types'
import type { User } from '@/types/user.types'

export const authService = {
  async register(payload: AuthRegisterPayload): Promise<ApiResponse<AuthRegisterResponse>> {
    const { data } = await apiClient.post('/auth/register', payload)
    return data
  },

  async login(payload: AuthLoginPayload): Promise<ApiResponse<AuthLoginResponse>> {
    const { data } = await apiClient.post('/auth/login', payload)
    return data
  },

  async logout(): Promise<ApiResponse<null>> {
    const { data } = await apiClient.post('/auth/logout')
    return data
  },

  async me(): Promise<ApiResponse<User>> {
    const { data } = await apiClient.get('/auth/me')
    return data
  },
}
