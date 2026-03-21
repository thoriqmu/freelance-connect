import apiClient from './apiClient'

export const submissionService = {
  getProjectSubmissions(projectId: number) {
    return apiClient.get(`/projects/${projectId}/submissions`)
  },
  
  approveSubmission(projectId: number, submissionId: number) {
    return apiClient.patch(`/projects/${projectId}/submissions/${submissionId}/approve`)
  },

  requestRevision(projectId: number, submissionId: number, data: { feedback: string }) {
    return apiClient.patch(`/projects/${projectId}/submissions/${submissionId}/request-revision`, data)
  }
}
