import apiClient from './apiClient'

export const submissionService = {
  getProjectSubmissions(projectId: number) {
    return apiClient.get(`/projects/${projectId}/submissions`)
  },
  
  submitSubmission(projectId: number, data: FormData) {
    return apiClient.post(`/projects/${projectId}/submissions`, data)
  },
  
  approveSubmission(projectId: number, submissionId: number) {
    return apiClient.patch(`/projects/${projectId}/submissions/${submissionId}/approve`)
  },

  requestRevision(projectId: number, submissionId: number, data: { feedback: string }) {
    return apiClient.patch(`/projects/${projectId}/submissions/${submissionId}/request-revision`, data)
  }
}
