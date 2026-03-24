<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12 relative">
    
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
      <RouterLink to="/freelancer/my-jobs" class="text-blue-600 hover:underline flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to My Jobs
      </RouterLink>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6 animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-1/3"></div>
        <div class="h-6 bg-gray-200 rounded-full w-24"></div>
      </div>
    </div>

    <!-- Error State -->
    <BaseAlert v-else-if="error" type="error" :message="error" />

    <template v-else-if="project">
      <div class="space-y-8">
        
        <!-- LEFT COLUMN -->
        <div class="space-y-6">

          <!-- SECTION 1: Project Details -->
          <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
              <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ project.title }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Client: {{ project.client?.user?.name }}
                  </span>
                  <BaseBadge :type="project.status === 'completed' ? 'success' : 'warning'">
                    {{ formatStatus(project.status) }}
                  </BaseBadge>
                </div>
              </div>
              <div class="flex gap-4 p-4 bg-gray-50 rounded-lg shrink-0">
                <div>
                  <p class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Budget</p>
                  <p class="text-xl font-bold text-blue-600">${{ project.budget }}</p>
                </div>
                <div class="w-px bg-gray-200"></div>
                <div>
                  <p class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Timeline</p>
                  <p class="text-xl font-bold text-gray-900">{{ project.timeline ? project.timeline + ' weeks' : '-' }}</p>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-3">Project Description</h3>
              <ExpandableHTML :content="project.description" />
            </div>
          </div>

          <!-- SECTION 2: Submit Work -->
          <div v-if="project.status === 'in_progress'" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
            <h2 class="text-2xl font-bold text-gray-900 border-b border-gray-200 pb-2">Submit Work</h2>
            
            <BaseAlert v-if="submissionError" type="error" :message="submissionError" class="mb-4" />
            
            <form @submit.prevent="handleSubmitWork" class="space-y-4">
              <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Notes / Details</label>
                <textarea 
                  v-model="submissionNote" 
                  rows="3" 
                  class="input-base" 
                  placeholder="Explain what you have done..." 
                  required
                ></textarea>
              </div>

              <!-- Attachment Input -->
              <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Attachments (Optional)</label>
                <input 
                  type="file" 
                  multiple 
                  @change="handleFileUpload" 
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                />
              </div>
              
              <BaseButton type="submit" label="Submit Work" variant="primary" :loading="isSubmitting" />
            </form>
          </div>

          <!-- SECTION 3: Submissions History -->
          <div class="space-y-4">
            <h2 class="text-2xl font-bold text-gray-900 border-b border-gray-200 pb-2">Submission History</h2>

            <div v-if="isLoadingSubmissions" class="space-y-4">
              <div v-for="i in 2" :key="i" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-4">
                 <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                 <div class="h-12 bg-gray-200 rounded"></div>
              </div>
            </div>
            <div v-else-if="submissions.length > 0" class="space-y-4">
              <SubmissionCard v-for="sub in submissions" :key="sub.id" :submission="sub" />
            </div>
            <div v-else class="text-center py-10 bg-white rounded-xl border border-gray-100">
              <p class="text-gray-500 text-sm">You haven't submitted any work yet.</p>
            </div>
          </div>
        </div>

      </div>
    </template>
    
    <!-- Chat Widget -->
    <ChatBox 
      v-if="project && currentUser"
      :project-id="project.id"
      :current-user="currentUser"
      :receiver-name="project.client?.user?.name || 'Client'"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import ExpandableHTML from '@/components/ui/ExpandableHTML.vue'
import SubmissionCard from '@/components/shared/SubmissionCard.vue'
import ChatBox from '@/components/shared/ChatBox.vue'

import { projectService } from '@/services/projectService'
import { submissionService } from '@/services/submissionService'

const route = useRoute()
const authStore = useAuthStore()

const projectId = Number(route.params.id)

const project = ref<any>(null)
const submissions = ref<any[]>([])

const isLoading = ref(true)
const isLoadingSubmissions = ref(true)
const error = ref('')

// Submission Form
const submissionNote = ref('')
const submissionFiles = ref<File[]>([])
const isSubmitting = ref(false)
const submissionError = ref('')

const currentUser = computed(() => authStore.user)

const formatStatus = (status: string) => {
  if (!status) return ''
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const fetchProject = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await projectService.getProject(projectId)
    project.value = res.data?.data || res.data

    if (project.value.freelancer_id.user !== authStore.user?.freelancer_profile?.id) {
        error.value = 'You are not assigned to this project.'
        return
    }

    fetchSubmissions()
  } catch (err: any) {
    error.value = 'Failed to load project details.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const fetchSubmissions = async () => {
  isLoadingSubmissions.value = true
  try {
    const res = await submissionService.getProjectSubmissions(projectId)
    submissions.value = res.data?.data?.data || res.data?.data || []
  } catch (err) {
    console.error('Failed to load submissions', err)
  } finally {
    isLoadingSubmissions.value = false
  }
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    submissionFiles.value = Array.from(target.files)
  }
}

const handleSubmitWork = async () => {
  if (!submissionNote.value.trim()) {
    submissionError.value = 'Please provide some notes for your submission.'
    return
  }
  
  isSubmitting.value = true
  submissionError.value = ''
  
  try {
    const formData = new FormData()
    formData.append('note', submissionNote.value)
    submissionFiles.value.forEach((file, index) => {
      formData.append(`attachments[${index}]`, file)
    })
    
    await submissionService.submitWork(projectId, formData)
    
    // Reset form
    submissionNote.value = ''
    submissionFiles.value = []
    
    // Refresh submissions
    await fetchSubmissions()
  } catch (err: any) {
    submissionError.value = err.response?.data?.message || 'Failed to submit work.'
    console.error(err)
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchProject()
})
</script>
