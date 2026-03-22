import apiClient from './apiClient'

export const savedJobsService = {
  getSavedJobs() {
    return apiClient.get('/saved-jobs')
  },

  saveJob(projectId: number) {
    return apiClient.post('/saved-jobs', { project_id: projectId })
  },

  unsaveJob(projectId: number) {
    return apiClient.delete(`/saved-jobs/${projectId}`)
  },

  checkIfSaved(projectId: number) {
    return apiClient.get(`/saved-jobs/${projectId}/check`)
  }
}