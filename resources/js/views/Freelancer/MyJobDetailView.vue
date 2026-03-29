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
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <div class="space-y-3 flex-1">
            <div class="h-8 bg-gray-200 rounded w-2/3"></div>
            <div class="flex items-center gap-3">
              <div class="h-4 bg-gray-200 rounded w-32"></div>
              <div class="h-6 bg-gray-200 rounded-full w-20"></div>
            </div>
          </div>
          <div class="flex gap-4 p-4 bg-gray-50 rounded-lg shrink-0">
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-12"></div>
              <div class="h-7 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="w-px bg-gray-200"></div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-16"></div>
              <div class="h-7 bg-gray-200 rounded w-16"></div>
            </div>
          </div>
        </div>
        <div class="space-y-2">
          <div class="h-5 bg-gray-200 rounded w-40 mb-3"></div>
          <div class="h-4 bg-gray-200 rounded w-full"></div>
          <div class="h-4 bg-gray-200 rounded w-full"></div>
          <div class="h-4 bg-gray-200 rounded w-5/6"></div>
          <div class="h-4 bg-gray-200 rounded w-4/6"></div>
        </div>
      </div>

      <!-- Submit Work Skeleton -->
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6 animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-1/4 mb-4"></div>
        <div class="space-y-2">
          <div class="h-4 bg-gray-200 rounded w-20"></div>
          <div class="h-24 bg-gray-200 rounded w-full"></div>
        </div>
        <div class="space-y-2">
          <div class="h-4 bg-gray-200 rounded w-32"></div>
          <div class="h-10 bg-gray-200 rounded w-full"></div>
        </div>
        <div class="h-10 bg-gray-200 rounded w-32"></div>
      </div>

      <!-- Submission History Skeleton -->
      <div class="space-y-4">
        <div class="h-8 bg-gray-200 rounded w-1/4 animate-pulse"></div>
        <div v-for="i in 2" :key="i" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-4">
          <div class="h-4 bg-gray-200 rounded w-1/4"></div>
          <div class="h-12 bg-gray-200 rounded"></div>
        </div>
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

            <!-- Attachments -->
            <div v-if="project.project_attachments && project.project_attachments.length > 0">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">Attachments</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <a v-for="att in project.project_attachments" :key="att.id"
                  href="javascript:void(0)" @click="previewAttachment(att)"
                  class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                  <div class="w-10 h-10 rounded bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate" :title="att.title">{{ att.title }}</p>
                    <p class="text-xs text-gray-500 truncate">Click to view</p>
                  </div>
                </a>
              </div>
            </div>
          </div>

          <!-- SECTION 2: Submit Work -->
          <div v-if="project.status === 'in_progress' && canSubmitWork" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
            <h2 class="text-2xl font-bold text-gray-900 border-b border-gray-200 pb-2">Submit Work</h2>
            
            <BaseAlert v-if="submissionError" type="error" :message="submissionError" class="mb-4" />
            
            <form @submit.prevent="handleSubmitWork" class="space-y-6">
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

              <!-- Attachments Section -->
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <label class="block text-sm font-medium text-gray-700">Attachments</label>
                  <BaseButton
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addAttachmentSlot"
                    label="Add File"
                  />
                </div>

                <div v-if="submissionAttachments.length === 0" class="text-xs text-gray-500 pb-2">
                  No attachments added yet. Click "Add File" to upload your related working documents.
                </div>

                <div v-for="(slot, index) in submissionAttachments" :key="index" class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-3 relative">
                  <button 
                    type="button" 
                    @click="removeAttachmentSlot(index)"
                    class="absolute top-2 right-2 text-gray-400 text-red-500 hover:text-red-700 transition-colors"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ">
                    <div class="space-y-1">
                      <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">File Title</label>
                      <input 
                        v-model="slot.title" 
                        type="text" 
                        class="input-base text-sm" 
                        placeholder="e.g. Final Design Prototype"
                      />
                    </div>
                    <div class="space-y-1">
                      <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Select File</label>
                      <input 
                        type="file" 
                        @change="handleFileUpload($event, index)" 
                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                      />
                    </div>
                  </div>
                </div>
              </div>
              
              <BaseButton type="submit" label="Submit Work" variant="primary" :loading="isSubmitting" />
            </form>
          </div>
          
          <div v-else-if="project.status === 'in_progress' && !canSubmitWork" class="bg-blue-50 border border-blue-100 p-6 rounded-xl flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
            </div>
            <div>
              <h3 class="text-blue-900 font-bold mb-1">Work Submitted</h3>
              <p class="text-blue-700 text-sm">You have successfully submitted your work. Please wait for the client to review and approve or request revisions.</p>
            </div>
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
              <SubmissionCard v-for="sub in submissions" :key="sub.id" :submission="sub" @preview-attachment="previewAttachment"/>
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

    <!-- Attachment Preview Modal -->
    <BaseModal
      :is-open="isPreviewOpen"
      :title="selectedAttachment?.title || 'Attachment Preview'"
      @close="isPreviewOpen = false"
      :width="800"
    >
      <div class="flex flex-col items-center">
        <template v-if="selectedAttachment">
          <img
            v-if="isImage(selectedAttachment.file_path)"
            :src="getAttachmentUrl(selectedAttachment.file_path)"
            class="max-w-full h-auto rounded-lg shadow-sm"
            alt="Preview"
          />
          <iframe
            v-else-if="isPdf(selectedAttachment.file_path)"
            :src="getAttachmentUrl(selectedAttachment.file_path)"
            class="w-full h-[600px] rounded-lg border border-gray-200"
          ></iframe>
          <div v-else class="text-center py-8">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-gray-500 mb-4">No preview available for this file type.</p>
            <a
              :href="getAttachmentUrl(selectedAttachment.file_path)"
              target="_blank"
              class="text-blue-600 font-bold hover:underline"
            >
              Download File
            </a>
          </div>
        </template>
      </div>
      <template #actions>
        <BaseButton label="Close" variant="outline" @click="isPreviewOpen = false" />
        <a
          v-if="selectedAttachment"
          :href="getAttachmentUrl(selectedAttachment.file_path)"
          download
          target="_blank"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors"
        >
          Download
        </a>
      </template>
    </BaseModal>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
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
const isPreviewOpen = ref(false)
const selectedAttachment = ref<any>(null)

const isLoading = ref(true)
const isLoadingSubmissions = ref(true)
const error = ref('')

// Submission Form
const submissionNote = ref('')
const submissionAttachments = ref<{ title: string; file: File | null }[]>([])
const isSubmitting = ref(false)
const submissionError = ref('')

const currentUser = computed(() => authStore.user)

const canSubmitWork = computed(() => {
  if (!submissions.value || submissions.value.length === 0) return true
  // If no active/accepted submissions, allow. (Status "rejected" means revision requested)
  const hasActiveSubmission = submissions.value.some(sub => sub.status !== 'rejected')
  return !hasActiveSubmission
})

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

const addAttachmentSlot = () => {
  submissionAttachments.value.push({ title: '', file: null })
}

const removeAttachmentSlot = (index: number) => {
  submissionAttachments.value.splice(index, 1)
}

const handleFileUpload = (event: Event, index: number) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    submissionAttachments.value[index].file = target.files[0]
  }
}

const getAttachmentUrl = (path: string) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '')
  return `${baseUrl}/storage/${path}`
}

const previewAttachment = (att: any) => {
  selectedAttachment.value = att
  isPreviewOpen.value = true
}

const isImage = (path: string) => {
  return /\.(jpg|jpeg|png|webp|gif)$/i.test(path)
}

const isPdf = (path: string) => {
  return /\.pdf$/i.test(path)
}

const handleSubmitWork = async () => {
  if (submissionNote.value.trim().length < 10) {
    submissionError.value = 'Description must be at least 10 characters long.'
    return
  }

  // Filter out slots without files
  const validAttachments = submissionAttachments.value.filter(slot => slot.file)
  
  isSubmitting.value = true
  submissionError.value = ''
  
  try {
    const formData = new FormData()
    formData.append('note', submissionNote.value)
    
    validAttachments.forEach((item, index) => {
      if (item.file) {
        formData.append(`attachments[${index}][file]`, item.file)
        formData.append(`attachments[${index}][title]`, item.title || item.file.name)
      }
    })
    
    await submissionService.submitSubmission(projectId, formData)
    
    // Reset form
    submissionNote.value = ''
    submissionAttachments.value = []
    
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
