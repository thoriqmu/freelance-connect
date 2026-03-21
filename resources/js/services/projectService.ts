import apiClient from './apiClient'
import type { PaginatedResponse, ApiResponse } from '@/types/api.types'
import type { Project } from '@/types/project.types'

export interface ProjectFilters {
  search?: string
  status?: string
  budget_min?: number
  budget_max?: number
  page?: number
  per_page?: number
}

export const projectService = {
  // Untuk freelancer: browse semua project yang OPEN
  getProjects(filters: ProjectFilters = {}) {
    return apiClient.get<PaginatedResponse<Project>>('/projects', { params: filters })
  },

  // Detail satu project
  getProject(id: number) {
    return apiClient.get<ApiResponse<Project>>(`/projects/${id}`)
  },

  // Untuk client: project milik sendiri
  getMyProjects(filters: ProjectFilters = {}) {
    return apiClient.get<PaginatedResponse<Project>>('/projects/my', { params: filters })
  },

  createProject(data: Partial<Project>) {
    return apiClient.post<ApiResponse<Project>>('/projects', data)
  },

  updateProject(id: number, data: Partial<Project>) {
    return apiClient.put<ApiResponse<Project>>(`/projects/${id}`, data)
  },

  deleteProject(id: number) {
    return apiClient.delete<ApiResponse<null>>(`/projects/${id}`)
  },
}
