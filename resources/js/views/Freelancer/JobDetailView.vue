<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 pb-12 relative">
    
    <!-- Breadcrumb & Actions -->
    <div class="flex items-center justify-between">
      <RouterLink to="/freelancer/jobs" class="text-blue-600 hover:underline flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to Browse Jobs
      </RouterLink>
      
      <button
        v-if="project"
        @click="toggleSave"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-colors border',
          isSaved
            ? 'bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100'
            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
        ]"
      >
        <span class="flex items-center gap-2">
          <svg v-if="isSaved" class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path></svg>
          <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path></svg>
          {{ isSaved ? 'Saved' : 'Save Job' }}
        </span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="animate-pulse bg-white p-6 rounded-xl border border-gray-200 h-48"></div>
    </div>

    <!-- Error State -->
    <BaseAlert v-else-if="error" type="error" :message="error" />

    <div v-else-if="project" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Main Content (Left Column) -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Job Details Card -->
        <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm space-y-6">
          <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ project.title }}</h1>
               <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Posted {{ new Date(project.created_at).toLocaleDateString() }}
                </span>
                <BaseBadge :type="project.status === 'open' ? 'success' : 'warning'">
                  {{ formatStatus(project.status) }}
                </BaseBadge>
              </div>
            </div>
            
            <div class="text-right shrink-0 bg-blue-50 p-4 rounded-lg">
              <p class="text-xs uppercase font-bold text-gray-500 tracking-wider">Project Budget</p>
              <p class="text-2xl font-bold text-blue-600">${{ project.budget }}</p>
            </div>
          </div>

          <div class="prose prose-blue max-w-none text-gray-700" v-html="project.description"></div>

          <!-- Attachments -->
          <div v-if="project.attachments && project.attachments.length > 0" class="pt-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Attachments</h3>
            <div class="flex flex-wrap gap-3">
              <a 
                v-for="att in project.attachments" 
                :key="att.id"
                :href="getAttachmentUrl(att.file_path)"
                target="_blank"
                class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                {{ att.title }}
              </a>
            </div>
          </div>
        </div>

        <!-- Submit Proposal Section -->
         <div id="proposal-form" class="bg-gray-50 p-6 md:p-8 rounded-xl border border-gray-200">
            <div v-if="project.status !== 'open'">
                <div class="text-center py-6">
                <BaseBadge type="warning" class="mb-2">Project Closed / Assigned</BaseBadge>
                <h3 class="text-xl font-bold text-gray-900 mt-2">This project is no longer accepting proposals.</h3>
                </div>
            </div>

            <!-- Skeleton saat mengecek proposal -->
            <div v-else-if="isCheckingProposal" class="animate-pulse space-y-4">
                <div class="h-6 bg-gray-200 rounded w-1/3"></div>
                <div class="grid grid-cols-2 gap-4">
                <div class="h-10 bg-gray-200 rounded"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
                </div>
                <div class="h-32 bg-gray-200 rounded"></div>
                <div class="h-10 bg-gray-200 rounded w-32"></div>
            </div>
           <!-- Check if user has already submitted -->
           <div v-else-if="existingProposal">
              <div class="bg-white p-8 rounded-xl text-center border border-blue-100 shadow-sm">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">You've already submitted a proposal</h3>
                <p class="text-gray-600 mt-2 mb-4">Your bid: <span class="font-bold text-gray-900">${{ existingProposal.bid_price }}</span> | Estimated time: <span class="font-bold text-gray-900">{{ existingProposal.estimated_time }} week</span></p>
                
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-full border border-gray-100">
                  <span class="text-sm text-gray-500 font-medium">Proposal Status:</span>
                  <BaseBadge :type="getStatusBadgeType(existingProposal.status)">
                    {{ formatStatus(existingProposal.status) }}
                  </BaseBadge>
                </div>
                
                <p v-if="existingProposal.status === 'pending'" class="text-blue-600 text-sm mt-4 font-medium italic">
                  * Waiting for response from client.
                </p>
              </div>
           </div>
           <div v-else>
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Submit a Proposal</h2>
              <form @submit.prevent="submitProposal" class="space-y-5">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700">Bid Amount ($)</label>
                    <input v-model.number="proposalForm.bid_price" type="number" required min="1" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 500">
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700">Estimated Timeline (Days/Weeks)</label>
                    <input v-model="proposalForm.estimated_time" type="text" required class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 2 weeks">
                  </div>
                </div>

                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-700">Cover Letter</label>
                  <textarea v-model="proposalForm.message" required rows="5" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full" placeholder="Tell the client why you're a good fit for this project..."></textarea>
                </div>
                
                <div class="pt-4">
                  <BaseButton 
                    type="submit" 
                    label="Submit Proposal" 
                    variant="primary" 
                    class="w-full md:w-auto px-8"
                    :disabled="isSubmittingProposal" 
                  />
                </div>
              </form>
           </div>
         </div>
      </div>
      
      <!-- Right Sidebar (Client Info & Quick Actions) -->
      <div class="space-y-6">
        
        <!-- About the Client -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
          <h3 class="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">About the Client</h3>
          <div class="flex items-center gap-3 mb-4">
            <img
              :src="project.client?.user?.avatar_url || '/img/avatar.png'"
              @error="$event.target.src = '/img/avatar.png'"
              class="w-12 h-12 rounded-full object-cover bg-gray-100"
              alt="Client Avatar"
            />
            <div>
              <p class="font-semibold text-gray-900">{{ project.client?.user?.name || 'Unknown Client' }}</p>
              <p class="text-xs text-gray-500">{{ project.client?.company_name }}</p>
              <p class="text-xs text-gray-400">Member since {{ new Date(project.client?.user?.created_at).getFullYear() }}</p>
            </div>
          </div>
        </div>

        <!-- Project Overview Widget -->
        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 space-y-4">
           <div>
             <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Project Length</p>
             <p class="font-medium text-gray-900">{{ project.timeline }} weeks</p>
           </div>
           <div>
             <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Required Skills</p>
             <div class="flex flex-wrap gap-2 mt-2">
               <BaseBadge v-for="skill in parsedSkills" :key="skill" type="info">{{ skill }}</BaseBadge>
             </div>
           </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, reactive } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/services/apiClient'
import { projectService } from '@/services/projectService'
import { savedJobsService } from '@/services/savedJobsService'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

const route = useRoute()
const projectId = Number(route.params.id)

const project = ref<any>(null)
const isLoading = ref(true)
const error = ref('')
const isSaved = ref(false)

const existingProposal = ref<any>(null)
const isSubmittingProposal = ref(false)

const proposalForm = reactive({
  bid_price: '',
  estimated_time: '',
  message: ''
})

const fetchJobDetail = async () => {
  isLoading.value = true
  try {
    const res = await projectService.getProject(projectId)
    project.value = res.data?.data || res.data
  } catch (err) {
    error.value = 'Failed to load project details.'
  } finally {
    isLoading.value = false
  }
}

const checkSavedStatus = async () => {
  try {
    const res = await savedJobsService.checkIfSaved(projectId)
    isSaved.value = res.data?.data?.saved || false
  } catch (err) {
    console.error(err)
  }
}

const toggleSave = async () => {
  const previousState = isSaved.value
  isSaved.value = !isSaved.value
  try {
    if (previousState) {
      await savedJobsService.unsaveJob(projectId)
    } else {
      await savedJobsService.saveJob(projectId)
    }
  } catch (err) {
    isSaved.value = previousState
    alert('Failed to toggle save state.')
  }
}

const submitProposal = async () => {
  if (!proposalForm.bid_price || !proposalForm.estimated_time || !proposalForm.message) return
  
  isSubmittingProposal.value = true
  try {
    const res = await apiClient.post(`/projects/${projectId}/proposals`, proposalForm)
    existingProposal.value = proposalForm // optimistic local update or server response
    alert('Proposal submitted successfully!')
  } catch (err) {
    console.error(err)
    alert('Failed to submit proposal.')
  } finally {
    isSubmittingProposal.value = false
  }
}

// Check if current freelancer already submitted a form (requires backend endpoint to check)
const isCheckingProposal = ref(true)

const checkExistingProposal = async () => {
  isCheckingProposal.value = true
  try {
    const res = await apiClient.get(`/projects/${projectId}/proposals/check`)
    if (res.data && res.data.data) {
      existingProposal.value = res.data.data
    }
  } catch(e) {
    // quietly ignore
  } finally {
    isCheckingProposal.value = false
  }
}

const parsedSkills = computed(() => {
  if (!project.value?.required_skills) return []
  return typeof project.value.required_skills === 'string' 
    ? JSON.parse(project.value.required_skills) 
    : project.value.required_skills
})

const formatStatus = (status: string) => {
  if (!status) return ''
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const getStatusBadgeType = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'accepted': return 'success'
    case 'rejected': return 'danger'
    case 'pending': return 'warning'
    default: return 'info'
  }
}

const getAttachmentUrl = (path: string) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '')
  return `${baseUrl}/storage/${path}`
}

onMounted(() => {
  fetchJobDetail()
  checkSavedStatus()
  checkExistingProposal()
})
</script>
