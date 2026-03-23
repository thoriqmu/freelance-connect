<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12 relative">
    
    <!-- Breadcrumb & Actions -->
    <div class="flex items-center justify-between">
      <RouterLink to="/client/projects" class="text-blue-600 hover:underline flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to My Projects
      </RouterLink>
      
      <div v-if="project && (project.status === 'open' || project.status === 'archived')" class="relative">
        <BaseButton label="Action" variant="outline" size="sm" @click="isActionDropdownOpen = !isActionDropdownOpen" class="font-semibold" />
        
        <div v-if="isActionDropdownOpen" class="fixed inset-0 z-40" @click="isActionDropdownOpen = false"></div>
        
        <div v-if="isActionDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
          <button @click="handleEditClick" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
            Edit Project
          </button>
          
          <button v-if="project.status === 'open' && proposals.length === 0" @click="handleArchiveProject" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
            Archive Project
          </button>
          
          <button v-if="project.status === 'archived'" @click="handleUnarchiveProject" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
             <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
            Open Project
          </button>

          <button @click="openDeleteConfirm" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Delete Project
          </button>
        </div>
      </div>
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

      <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 pb-2">
          <div class="h-7 bg-gray-200 rounded w-48 animate-pulse"></div>
          <div class="h-7 bg-gray-200 rounded-full w-10 animate-pulse"></div>
        </div>
        <div v-for="i in 3" :key="i" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-gray-200 shrink-0"></div>
            <div class="space-y-2 flex-1">
              <div class="h-5 bg-gray-200 rounded w-36"></div>
              <div class="h-3 bg-gray-200 rounded w-24"></div>
            </div>
            <div class="h-6 bg-gray-200 rounded-full w-20"></div>
          </div>
          <div class="space-y-2">
            <div class="h-4 bg-gray-200 rounded w-full"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <div class="h-5 bg-gray-200 rounded w-24"></div>
            <div class="flex gap-2">
              <div class="h-9 bg-gray-200 rounded-lg w-24"></div>
              <div class="h-9 bg-gray-200 rounded-lg w-24"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <BaseAlert v-else-if="error" type="error" :message="error" />

    <template v-else-if="project">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- LEFT COLUMN -->
        <div :class="project.freelancer_id ? 'lg:col-span-2' : 'lg:col-span-3'" class="space-y-6">

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

          <!-- SECTION 2: Submissions -->
          <div v-if="project.freelancer_id" class="space-y-4">
            <h2 class="text-2xl font-bold text-gray-900 border-b border-gray-200 pb-2">Submissions</h2>

            <div v-if="isLoadingSubmissions" class="space-y-4">
              <div v-for="i in 2" :key="i" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gray-200 shrink-0"></div>
                  <div class="space-y-2 flex-1">
                    <div class="h-4 bg-gray-200 rounded w-36"></div>
                    <div class="h-3 bg-gray-200 rounded w-24"></div>
                  </div>
                  <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                </div>
                <div class="space-y-2">
                  <div class="h-4 bg-gray-200 rounded w-full"></div>
                  <div class="h-4 bg-gray-200 rounded w-4/5"></div>
                </div>
                <div class="flex gap-2 pt-2 border-t border-gray-100">
                  <div class="h-9 bg-gray-200 rounded-lg w-28"></div>
                  <div class="h-9 bg-gray-200 rounded-lg w-28"></div>
                </div>
              </div>
            </div>

            <div v-else-if="submissions.length > 0" class="space-y-4">
              <SubmissionCard v-for="sub in submissions" :key="sub.id" :submission="sub"
                @approve="handleApproveSubmission" @request-revision="handleRequestRevision" />
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

            <div v-if="isLoadingProposals" class="space-y-4">
              <div v-for="i in 3" :key="i" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-4">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-gray-200 shrink-0"></div>
                  <div class="space-y-2 flex-1">
                    <div class="h-5 bg-gray-200 rounded w-36"></div>
                    <div class="h-3 bg-gray-200 rounded w-24"></div>
                  </div>
                  <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                </div>
                <div class="space-y-2">
                  <div class="h-4 bg-gray-200 rounded w-full"></div>
                  <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                  <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                  <div class="h-5 bg-gray-200 rounded w-24"></div>
                  <div class="flex gap-2">
                    <div class="h-9 bg-gray-200 rounded-lg w-24"></div>
                    <div class="h-9 bg-gray-200 rounded-lg w-24"></div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="proposals.length > 0" class="space-y-4">
              <ProposalCard v-for="prop in proposals" :key="prop.id" :proposal="prop"
                :isProcessing="isProcessingProposal === prop.id"
                @accept="handleAcceptProposal" @reject="handleRejectProposal" />
            </div>
            <div v-else class="text-center py-12 bg-white rounded-xl border border-gray-100">
              <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">No Proposals Yet</h3>
              <p class="text-gray-500 mb-4">You haven't received any proposals for this project.</p>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN — hanya muncul jika ada freelancer -->
        <div v-if="project.freelancer_id" class="space-y-6">

          <!-- Hired Freelancer Card -->
          <div class="bg-white p-6 rounded-xl border border-blue-100 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-blue-600 uppercase tracking-wider">Hired Freelancer</h3>
            <div class="flex items-center gap-4">
              <img
                :src="project.freelancer?.user?.avatar || '/img/avatar.png'"
                @error="handleAvatarError"
                class="w-16 h-16 rounded-full object-cover border-2 border-blue-100 shadow-sm"
                alt="Avatar"
              />
              <div>
                <h4 class="text-lg font-bold text-gray-900">{{ project.freelancer?.user?.name || 'Freelancer Name'}}</h4>
                <p class="text-sm text-gray-500">{{ project.freelancer?.user?.email || 'Freelancer Email'}}</p>
              </div>
            </div>
          </div>

          <!-- ChatBox -->
          <ChatBox
            v-if="currentUser"
            :project-id="project.id"
            :current-user="currentUser"
            :receiver-name="project.freelancer?.user?.name || 'Freelancer'"
          />

        </div>

      </div>
    </template>
    
    <!-- Chat Widget -->
    <ChatBox 
      v-if="project && project.freelancer_id && currentUser"
      :project-id="project.id"
      :current-user="currentUser"
      :receiver-name="project.freelancer?.user?.name || 'Freelancer'"
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

    <!-- Delete Confirmation Modal -->
    <BaseModal
      :is-open="isDeleteConfirmOpen"
      title="Delete Project"
      @close="isDeleteConfirmOpen = false"
    >
      <div class="py-4">
        <p class="text-gray-700">Are you sure you want to delete this project? All proposals and submitted files will be permanently removed. This action cannot be undone.</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="outline" @click="isDeleteConfirmOpen = false" :disabled="isDeleting" />
        <BaseButton label="Delete Project" class="bg-red-600 hover:bg-red-700 text-white border-none" @click="handleDeleteProject" :loading="isDeleting" />
      </template>
    </BaseModal>

    <!-- Accept Proposal Confirmation Modal -->
    <BaseModal
      :is-open="isAcceptConfirmOpen"
      title="Accept Proposal"
      @close="isAcceptConfirmOpen = false"
    >
      <div class="py-4">
        <p class="text-gray-700">Are you sure you want to accept this proposal and hire this freelancer? This will move the project to "In Progress" status.</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="outline" @click="isAcceptConfirmOpen = false" :disabled="isProcessingProposal !== null" />
        <BaseButton label="Accept & Hire" class="bg-green-600 hover:bg-green-700 text-white border-none" @click="confirmAcceptProposal" :loading="isProcessingProposal !== null" />
      </template>
    </BaseModal>

    <!-- Reject Proposal Confirmation Modal -->
    <BaseModal
      :is-open="isRejectConfirmOpen"
      title="Reject Proposal"
      @close="isRejectConfirmOpen = false"
    >
      <div class="py-4">
        <p class="text-gray-700">Are you sure you want to reject this proposal? This action cannot be undone.</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="outline" @click="isRejectConfirmOpen = false" :disabled="isProcessingProposal !== null" />
        <BaseButton label="Reject Proposal" class="bg-red-600 hover:bg-red-700 text-white border-none" @click="confirmRejectProposal" :loading="isProcessingProposal !== null" />
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/services/apiClient'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'

import ProposalCard from '@/components/shared/ProposalCard.vue'
import SubmissionCard from '@/components/shared/SubmissionCard.vue'
import ChatBox from '@/components/shared/ChatBox.vue'
import BaseModal from '@/components/ui/BaseModal.vue'

import { projectService } from '@/services/projectService'
import { proposalService } from '@/services/proposalService'
import { submissionService } from '@/services/submissionService'

const route = useRoute()
const authStore = useAuthStore()

const projectId = Number(route.params.id)

const project = ref<any>(null)
const proposals = ref<any[]>([])
const submissions = ref<any[]>([])
const isPreviewOpen = ref(false)
const selectedAttachment = ref<any>(null)

const isLoading = ref(true)
const isLoadingProposals = ref(true)
const isLoadingSubmissions = ref(true)
const error = ref('')

const isActionDropdownOpen = ref(false)
const isDeleteConfirmOpen = ref(false)
const isAcceptConfirmOpen = ref(false)
const isRejectConfirmOpen = ref(false)
const selectedProposalId = ref<number | null>(null)
const isDeleting = ref(false)
const isProcessingProposal = ref<number | null>(null)

const currentUser = computed(() => authStore.user)
const router = useRouter()

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

const handleAcceptProposal = (proposalId: number) => {
  selectedProposalId.value = proposalId
  isAcceptConfirmOpen.value = true
}

const confirmAcceptProposal = async () => {
  if (!selectedProposalId.value) return
  
  isProcessingProposal.value = selectedProposalId.value
  try {
    await proposalService.acceptProposal(projectId, selectedProposalId.value)
    isAcceptConfirmOpen.value = false
    await fetchProject()
  } catch (err) {
    alert('Failed to accept proposal')
    console.error(err)
  } finally {
    isProcessingProposal.value = null
    selectedProposalId.value = null
  }
}

const handleRejectProposal = (proposalId: number) => {
  selectedProposalId.value = proposalId
  isRejectConfirmOpen.value = true
}

const confirmRejectProposal = async () => {
  if (!selectedProposalId.value) return
  
  isProcessingProposal.value = selectedProposalId.value
  try {
    await proposalService.rejectProposal(projectId, selectedProposalId.value)
    isRejectConfirmOpen.value = false
    await fetchProposals() // Refresh list
  } catch (err) {
    alert('Failed to reject proposal')
    console.error(err)
  } finally {
    isProcessingProposal.value = null
    selectedProposalId.value = null
  }
}

const handleEditClick = () => {
    isActionDropdownOpen.value = false
    router.push(`/client/projects/${project.value.id}/edit`)
}

const openDeleteConfirm = () => {
    isActionDropdownOpen.value = false
    isDeleteConfirmOpen.value = true
}

const handleArchiveProject = async () => {
    isActionDropdownOpen.value = false
    try {
        await projectService.archiveProject(projectId)
        await fetchProject()
    } catch (err: any) {
        alert(err.response?.data?.message || 'Failed to archive project')
    }
}

const handleUnarchiveProject = async () => {
    isActionDropdownOpen.value = false
    try {
        await projectService.unarchiveProject(projectId)
        await fetchProject()
    } catch (err: any) {
        alert(err.response?.data?.message || 'Failed to unarchive project')
    }
}

const handleDeleteProject = async () => {
  isDeleting.value = true
  try {
    await projectService.deleteProject(projectId)
    isDeleteConfirmOpen.value = false
    router.push('/client/dashboard')
  } catch (err: any) {
    alert(err.response?.data?.message || 'Failed to delete project')
    console.error(err)
  } finally {
    isDeleting.value = false
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

const handleAvatarError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = '/img/avatar.png'
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

onMounted(() => {
  fetchProject()
})
</script>

<style scoped>
:deep(.prose ol) {
  list-style-type: decimal !important;
  padding-left: 1.5rem !important;
  margin-top: 0.75rem !important;
  margin-bottom: 0.75rem !important;
}

:deep(.prose ul) {
  list-style-type: disc !important;
  padding-left: 1.5rem !important;
  margin-top: 0.75rem !important;
  margin-bottom: 0.75rem !important;
}

:deep(.prose li) {
  margin-top: 0.25rem !important;
  margin-bottom: 0.25rem !important;
}
</style>
