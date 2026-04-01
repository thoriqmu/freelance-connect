<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <BaseAlert v-if="error" type="error" :message="error" class="mb-6" @close="error = ''" />
    <BaseAlert v-if="successMsg" type="success" :message="successMsg" class="mb-6" @close="successMsg = ''" />
    
    <div v-if="isLoading" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 animate-pulse space-y-8">
       <div class="flex flex-col items-center gap-4">
         <div class="w-32 h-32 rounded-full bg-gray-100"></div>
         <div class="h-8 bg-gray-100 rounded w-48"></div>
         <div class="h-4 bg-gray-100 rounded w-32"></div>
       </div>
       <div class="space-y-3">
         <div class="h-4 bg-gray-100 rounded w-full"></div>
         <div class="h-4 bg-gray-100 rounded w-5/6"></div>
       </div>
    </div>

    <div v-else-if="freelancer" class="space-y-8">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-700"></div>
        <div class="px-8 pb-8">
          <div class="relative flex flex-col items-center -mt-16 mb-6">
            <div class="p-1 bg-white rounded-full border-4 border-white shadow-lg">
              <img 
                :src="getAvatarUrl(freelancer.avatar) || '/img/avatar.png'" 
                class="w-32 h-32 rounded-full object-cover" 
              />
            </div>
            <BaseBadge :type="availabilityBadge" class="mt-4">
              {{ formatAvailability(freelancer.freelancer_profile?.availability) }}
            </BaseBadge>
          </div>

          <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold text-gray-900">
              {{ freelancer.name }}
            </h1>

            <p class="text-gray-500 text-sm">
              {{ freelancer.email }}
            </p>

            <div class="flex flex-wrap items-center justify-center gap-6 mt-4">
              <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                  ${{ freelancer.freelancer_profile?.hourly_rate || '0' }}
                </p>
                <p class="text-xs text-gray-400 uppercase">Hourly Rate</p>
              </div>

              <div class="text-center">
                <p class="text-2xl font-bold text-yellow-500">
                  {{ Number(freelancer.reviews_as_reviewee_avg_rating || 0).toFixed(1) }}
                </p>
                <p class="text-xs text-gray-400 uppercase">Rating</p>
              </div>

              <div class="text-center">
                <p class="text-2xl font-bold text-gray-900">
                  {{ freelancer.completed_projects_count || 0 }}
                </p>
                <p class="text-xs text-gray-400 uppercase">Completed</p>
              </div>
            </div>
          </div>

          <div v-if="freelancer.freelancer_profile?.portfolio_url" class="mt-6 flex justify-center">
            <a 
              :href="freelancer.freelancer_profile.portfolio_url" 
              target="_blank" 
              class="inline-flex items-center gap-2 px-6 py-2 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-all active:scale-95 shadow-md"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              View Portfolio
            </a>
          </div>

          <!-- Accept/Reject Proposal Buttons -->
          <div v-if="proposal && proposal.status === 'pending'" class="mt-8 pt-6 border-t border-gray-100 flex flex-col items-center gap-4">
             <p class="text-sm text-gray-500 font-medium italic">This freelancer has a pending proposal for your project.</p>
             <div class="flex flex-col sm:flex-row gap-3 w-full max-w-md">
               <BaseButton 
                 label="Reject Proposal" 
                 variant="outline" 
                 class="flex-1 h-12 text-red-600 border-red-200 hover:bg-red-50 text-base"
                 @click="confirmReject"
                 :disabled="isProcessing"
               />
               <BaseButton 
                 label="Accept & Hire" 
                 variant="primary" 
                 class="flex-1 h-12 bg-green-600 hover:bg-green-700 border-none shadow-lg shadow-green-100 text-base"
                 @click="confirmAccept"
                 :disabled="isProcessing"
               />
             </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white p-8 rounded-2xl border border-gray-200 shadow-sm space-y-4">
          <h3 class="text-xl font-bold text-gray-900">
            Professional Summary
          </h3>
          <p class="text-gray-600 leading-relaxed whitespace-pre-line">
            {{ freelancer.freelancer_profile?.bio || 'This freelancer hasn\'t provided a bio yet.' }}
          </p>
        </div>

        <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
          <h4 class="text-lg font-bold text-gray-900">
            Expertise & Skills
          </h4>
          <div class="flex flex-wrap gap-2">
            <span 
              v-for="(skill, i) in freelancer.freelancer_profile?.skills" 
              :key="i"
              class="px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg font-bold text-xs"
            >
              {{ skill }}
            </span>
            <p v-if="!freelancer.freelancer_profile?.skills?.length" class="text-gray-400 italic text-sm">
              No skills listed.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Accept Modal -->
    <BaseModal
      :is-open="isAcceptModalOpen"
      title="Accept Proposal"
      @close="isAcceptModalOpen = false"
    >
      <div class="py-4">
        <p class="text-gray-700">Are you sure you want to accept this proposal and hire <strong>{{ freelancer?.name }}</strong>? This will mark the project as "In Progress".</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="outline" @click="isAcceptModalOpen = false" :disabled="isProcessing" />
        <BaseButton label="Accept & Hire" class="bg-green-600 hover:bg-green-700 text-white border-none" @click="handleAcceptProposal" :loading="isProcessing" />
      </template>
    </BaseModal>

    <!-- Confirm Reject Modal -->
    <BaseModal
      :is-open="isRejectModalOpen"
      title="Reject Proposal"
      @close="isRejectModalOpen = false"
    >
      <div class="py-4">
        <p class="text-gray-700">Are you sure you want to reject <strong>{{ freelancer?.name }}</strong>'s proposal? This action cannot be undone.</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="outline" @click="isRejectModalOpen = false" :disabled="isProcessing" />
        <BaseButton label="Reject Proposal" class="bg-red-600 hover:bg-red-700 text-white border-none" @click="handleRejectProposal" :loading="isProcessing" />
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/services/apiClient'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import { proposalService } from '@/services/proposalService'
import { useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const freelancer = ref<any>(null)
const isLoading = ref(true)
const error = ref('')
const successMsg = ref('')

const projectId = computed(() => route.query.project_id ? Number(route.query.project_id) : null)
const proposal = ref<any>(null)
const isProcessing = ref(false)

const isAcceptModalOpen = ref(false)
const isRejectModalOpen = ref(false)

const fetchFreelancer = async () => {
  const id = route.params.id
  isLoading.value = true
  error.value = ''
  try {
    const { data } = await apiClient.get(`/profile/${id}`)
    freelancer.value = data.data
    
    if (projectId.value) {
      fetchProposal()
    }
  } catch (err: any) {
    if (err.response?.status === 403) {
      error.value = 'Access Denied: You can only view profiles of freelancers who have submitted proposals to your projects.'
    } else {
      error.value = 'Failed to load freelancer profile or user not found.'
    }
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const getAvatarUrl = (path: string) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '')
  return `${baseUrl}/storage/${path}` || '/img/avatar.png'
}

const availabilityBadge = computed(() => {
  const av = freelancer.value?.freelancer_profile?.availability
  if (av === 'available') return 'success'
  if (av === 'unavailable') return 'warning'
  return 'danger'
})

const formatAvailability = (status?: string) => {
  if (!status) return 'Unknown'
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const formatDate = (dateString?: string) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long'
  })
}

const fetchProposal = async () => {
    if (!projectId.value || !freelancer.value) return
    try {
        const res = await proposalService.getProjectProposals(projectId.value)
        const proposals = res.data?.data?.data || res.data?.data || []
        // Find the proposal from this freelancer
        proposal.value = proposals.find((p: any) => p.freelancer?.user?.id === freelancer.value?.id)
    } catch(err) {
        console.error('Failed to fetch proposal', err)
    }
}

const handleAcceptProposal = async () => {
  if (!projectId.value || !proposal.value) return
  
  isProcessing.value = true
  try {
    await proposalService.acceptProposal(projectId.value, proposal.value.id)
    successMsg.value = 'Freelancer hired successfully!'
    isAcceptModalOpen.value = false
    setTimeout(() => {
      router.push(`/client/projects/${projectId.value}`)
    }, 1500)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to accept proposal'
  } finally {
    isProcessing.value = false
  }
}

const handleRejectProposal = async () => {
  if (!projectId.value || !proposal.value) return
  
  isProcessing.value = true
  try {
    await proposalService.rejectProposal(projectId.value, proposal.value.id)
    successMsg.value = 'Proposal rejected.'
    isRejectModalOpen.value = false
    // Refresh proposal state
    fetchProposal()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to reject proposal'
  } finally {
    isProcessing.value = false
  }
}

const confirmAccept = () => {
  isAcceptModalOpen.value = true
}

const confirmReject = () => {
  isRejectModalOpen.value = true
}

onMounted(() => {
  fetchFreelancer()
})
</script>
