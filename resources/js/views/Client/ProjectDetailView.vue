<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12 relative">
    
    <!-- Breadcrumb & Actions -->
    <div class="flex items-center justify-between">
      <RouterLink to="/client/projects" class="text-blue-600 hover:underline flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to My Projects
      </RouterLink>
      
      <!-- Placeholder Action (e.g Edit) -->
      <div v-if="project && project.status === 'open'" class="flex gap-2">
         <BaseButton label="Edit Project" variant="outline" size="sm" @click="$router.push(`/client/projects/${project.id}/edit`)" />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="animate-pulse bg-white p-6 rounded-xl border border-gray-200 h-48"></div>
      <div class="animate-pulse bg-white p-6 rounded-xl border border-gray-200 h-64"></div>
    </div>

    <!-- Error State -->
    <BaseAlert v-else-if="error" type="error" :message="error" />

    <template v-else-if="project">
      
      <!-- SECTION 1: Project Details -->
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ project.title }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Posted {{ new Date(project.created_at).toLocaleDateString() }}
              </span>
              <BaseBadge :type="getStatusBadge(project.status)">{{ formatStatus(project.status) }}</BaseBadge>
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
          <div class="text-gray-700 prose prose-sm max-w-none" v-html="project.description"></div>
        </div>
        
        <!-- Attachments -->
        <div v-if="project.attachments && project.attachments.length > 0">
           <h3 class="text-lg font-semibold text-gray-900 mb-3">Attachments</h3>
           <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
             <a 
               v-for="att in project.attachments" 
               :key="att.id"
               :href="getAttachmentUrl(att.file_path)"
               target="_blank"
               class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
             >
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
      
      <!-- Hired Freelancer Info (if applicable) -->
      <div v-if="project.freelancer" class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex items-center gap-4">
        <img
          :src="project.freelancer.avatar_url || '/img/avatar.png'"
          @error="$event.target.src = '/img/avatar.png'"
          class="w-16 h-16 rounded-full object-cover border border-white shadow-sm"
          alt="Avatar"
        />
        <div>
           <p class="text-sm text-blue-600 font-semibold uppercase tracking-wider">Hired Freelancer</p>
           <h3 class="text-xl font-bold text-gray-900">{{ project.freelancer.name }}</h3>
        </div>
      </div>

      <!-- SECTION 2: Submissions -->
      <div v-if="project.freelancer_id" class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 border-b border-gray-200 pb-2">Submissions</h2>
        
        <div v-if="isLoadingSubmissions" class="animate-pulse bg-gray-50 p-6 rounded-xl h-32"></div>
        <div v-else-if="submissions.length > 0" class="space-y-4">
          <SubmissionCard 
            v-for="sub in submissions" 
            :key="sub.id" 
            :submission="sub"
            @approve="handleApproveSubmission"
            @request-revision="handleRequestRevision"
          />
        </div>
        <div v-else class="text-center py-10 bg-white rounded-xl border border-gray-100">
           <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
           <p class="text-gray-500">No submissions from the freelancer yet.</p>
        </div>
      </div>

      <!-- SECTION 3: Proposals -->
      <div v-else class="space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 pb-2">
           <h2 class="text-2xl font-bold text-gray-900">Proposals Received</h2>
           <span class="bg-blue-100 text-blue-700 font-bold px-3 py-1 rounded-full text-sm">{{ proposals.length }}</span>
        </div>
        
        <div v-if="isLoadingProposals" class="animate-pulse bg-gray-50 p-6 rounded-xl h-32"></div>
        <div v-else-if="proposals.length > 0" class="space-y-4">
          <ProposalCard 
            v-for="prop in proposals" 
            :key="prop.id" 
            :proposal="prop"
            :isProcessing="isProcessingProposal === prop.id"
            @accept="handleAcceptProposal"
            @reject="handleRejectProposal"
          />
        </div>
        <div v-else class="text-center py-12 bg-white rounded-xl border border-gray-100">
           <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
             <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
           </div>
           <h3 class="text-lg font-medium text-gray-900">No Proposals Yet</h3>
           <p class="text-gray-500 mb-4">You haven't received any proposals for this project.</p>
        </div>
      </div>
      
    </template>
    
    <!-- Chat Widget -->
    <ChatBox 
      v-if="project && project.freelancer_id && currentUser"
      :project-id="project.id"
      :current-user="currentUser"
      :receiver-name="project.freelancer?.name || 'Freelancer'"
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

import ProposalCard from '@/components/shared/ProposalCard.vue'
import SubmissionCard from '@/components/shared/SubmissionCard.vue'
import ChatBox from '@/components/shared/ChatBox.vue'

import { projectService } from '@/services/projectService'
import { proposalService } from '@/services/proposalService'
import { submissionService } from '@/services/submissionService'

const route = useRoute()
const authStore = useAuthStore()

const projectId = Number(route.params.id)

const project = ref<any>(null)
const proposals = ref<any[]>([])
const submissions = ref<any[]>([])

const isLoading = ref(true)
const isLoadingProposals = ref(false)
const isLoadingSubmissions = ref(false)
const error = ref('')

const isProcessingProposal = ref<number | null>(null)

const currentUser = computed(() => authStore.user)

const fetchProject = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await projectService.getProject(projectId)
    project.value = res.data?.data || res.data
    
    if (project.value.freelancer_id) {
       fetchSubmissions()
    } else {
       fetchProposals()
    }
  } catch (err: any) {
    error.value = 'Failed to load project details.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const fetchProposals = async () => {
  isLoadingProposals.value = true
  try {
    const res = await proposalService.getProjectProposals(projectId)
    proposals.value = res.data?.data?.data || res.data?.data || []
  } catch (err) {
    console.error('Failed to load proposals', err)
  } finally {
    isLoadingProposals.value = false
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

const handleAcceptProposal = async (proposalId: number) => {
  if (!confirm('Are you sure you want to accept this proposal and hire the freelancer?')) return
  
  isProcessingProposal.value = proposalId
  try {
    await proposalService.acceptProposal(projectId, proposalId)
    // Refresh project details to see assigned freelancer
    await fetchProject()
  } catch (err) {
    alert('Failed to accept proposal')
    console.error(err)
  } finally {
    isProcessingProposal.value = null
  }
}

const handleRejectProposal = async (proposalId: number) => {
  if (!confirm('Are you sure you want to reject this proposal?')) return
  
  isProcessingProposal.value = proposalId
  try {
    await proposalService.rejectProposal(projectId, proposalId)
    await fetchProposals() // Refresh list
  } catch (err) {
    alert('Failed to reject proposal')
    console.error(err)
  } finally {
    isProcessingProposal.value = null
  }
}

const handleApproveSubmission = async (submissionId: number) => {
  if (!confirm('Are you sure you want to approve this submission? This might trigger payment processing.')) return
  
  try {
    await submissionService.approveSubmission(projectId, submissionId)
    await fetchSubmissions()
  } catch (err) {
    alert('Failed to approve submission')
  }
}

const handleRequestRevision = async (submissionId: number) => {
  const note = prompt('Please enter the revision feedback:')
  if (!note) return
  
  try {
    await submissionService.requestRevision(projectId, submissionId, { feedback: note })
    await fetchSubmissions()
  } catch (err) {
    alert('Failed to request revision')
  }
}

const getStatusBadge = (status: string) => {
  switch (status) {
    case 'open': return 'info'
    case 'in_progress': return 'warning'
    case 'completed': return 'success'
    case 'archived': return 'gray'
    default: return 'gray'
  }
}

const formatStatus = (status: string) => {
  if (!status) return ''
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const getAttachmentUrl = (path: string) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '')
  return `${baseUrl}/storage/${path}`
}

onMounted(() => {
  fetchProject()
})
</script>
