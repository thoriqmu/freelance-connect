import axios, { type AxiosInstance } from 'axios'
import { useAuthStore } from '@/stores/auth'

const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

const apiClient: AxiosInstance = axios.create({
  baseURL,
})

// Request interceptor - attach token
apiClient.interceptors.request.use((config) => {
  // Get token from store if available
  try {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  } catch (error) {
    // localStorage might not be available in some environments
  }
  return config
})

// Response interceptor - handle 401
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      try {
        const authStore = useAuthStore()
        authStore.logout()
        window.location.href = '/login'
      } catch (e) {
        // auth store might not be available
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient
