import apiClient from './apiClient'

export const proposalService = {
  getProjectProposals(projectId: number) {
    return apiClient.get(`/projects/${projectId}/proposals`)
  },

  acceptProposal(projectId: number, proposalId: number) {
    return apiClient.patch(`/projects/${projectId}/proposals/${proposalId}/accept`)
  },

  rejectProposal(projectId: number, proposalId: number) {
    return apiClient.patch(`/projects/${projectId}/proposals/${proposalId}/reject`)
  },

  getMyProposals(params?: Record<string, any>) {
    return apiClient.get('/proposals/my', { params })
  },

  getClientProposals(params?: Record<string, any>) {
    return apiClient.get('/proposals/client/my', { params })
  }
}
