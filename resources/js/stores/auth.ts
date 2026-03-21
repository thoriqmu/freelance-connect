import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import type { AuthLoginPayload, AuthRegisterPayload } from '@/types/auth.types'
import type { User, UserRole } from '@/types/user.types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token') || null)

  const isLoggedIn = computed(() => !!token.value)
  const isClient = computed(() => user.value?.role === 'client')
  const isFreelancer = computed(() => user.value?.role === 'freelancer')

  async function register(payload: AuthRegisterPayload): Promise<void> {
    const response = await authService.register(payload)
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
  }

  async function login(email: string, password: string): Promise<void> {
    const response = await authService.login({ email, password })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
  }

  async function fetchMe(): Promise<void> {
    if (!token.value) return
    try {
      const response = await authService.me()
      user.value = response.data
    } catch (error) {
      // If me() fails, user likely logged out
      logout()
      throw error
    }
  }

  function logout(): void {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  return {
    user,
    token,
    isLoggedIn,
    isClient,
    isFreelancer,
    register,
    login,
    fetchMe,
    logout,
  }
})
