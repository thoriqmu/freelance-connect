import apiClient from './apiClient'

export const messageService = {
  getMessages(projectId: number, page: number = 1) {
    return apiClient.get(`/projects/${projectId}/messages?page=${page}`)
  },
  sendMessage(projectId: number, content: string) {
    return apiClient.post(`/projects/${projectId}/messages`, { content })
  }
}
