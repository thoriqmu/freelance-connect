import apiClient from './apiClient'

export const messageService = {
  getMessages(projectId: number) {
    return apiClient.get(`/projects/${projectId}/messages`)
  },
  sendMessage(projectId: number, content: string) {
    return apiClient.post(`/projects/${projectId}/messages`, { content })
  }
}
