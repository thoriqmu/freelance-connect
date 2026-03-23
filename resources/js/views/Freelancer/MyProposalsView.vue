<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">My Proposals</h1>
        <p class="text-gray-500 mt-1">Track the status of your submitted proposals</p>
      </div>
    </div>

    <!-- Search Filter Removed as per user request -->

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="card animate-pulse">
        <div class="space-y-3">
          <div class="h-6 bg-gray-200 rounded w-1/2"></div>
          <div class="h-4 bg-gray-200 rounded w-full"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <BaseAlert v-else-if="error" type="error" :message="error" />

    <!-- Proposals List -->
    <template v-else-if="proposals.length > 0">
      <div class="space-y-4 mb-8">
        <div 
          v-for="proposal in proposals" 
          :key="proposal.id"
          class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 items-start md:items-center justify-between hover:shadow-md transition-shadow"
        >
          <div class="flex-1 w-full min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="text-xl font-bold text-gray-900 truncate">
                <RouterLink :to="`/freelancer/jobs/${proposal.project_id}`" class="hover:text-blue-600 transition-colors">
                  {{ proposal.project?.title || 'Unknown Project' }}
                </RouterLink>
              </h3>
              <BaseBadge :type="getStatusBadge(proposal.status)">
                {{ formatStatus(proposal.status) }}
              </BaseBadge>
            </div>
            
            <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ proposal.message }}</p>
            
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
               <span class="flex items-center gap-1 font-medium text-gray-900">
                 <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 Bid: <span class="text-blue-600">${{ proposal.bid_price }}</span>
               </span>
               <span class="flex items-center gap-1 font-medium text-gray-900">
                 <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 Est. Time: {{ proposal.estimated_time }} weeks
               </span>
            </div>
          </div>
          
          <div class="shrink-0 w-full md:w-auto mt-4 md:mt-0 pt-4 md:pt-0 border-t md:border-0 border-gray-100 flex flex-col items-end gap-2 text-sm text-gray-500">
            <span>Submitted on {{ new Date(proposal.created_at).toLocaleDateString() }}</span>
            <BaseButton 
              label="View Project" 
              variant="outline" 
              size="sm" 
              @click="$router.push(`/freelancer/jobs/${proposal.project_id}`)"
              class="flex items-center gap-1"
            />
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center" v-if="pagination.last_page > 1">
        <BasePagination 
          :current-page="pagination.current_page"
          :last-page="pagination.last_page"
          @update:current-page="handlePageChange"
        />
      </div>
    </template>

    <!-- Empty State -->
    <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-100 shadow-sm">
      <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">No proposals found</h3>
      <p class="text-gray-500 max-w-md mx-auto mb-6">You haven't submitted any proposals yet or none match your search criteria.</p>
      <BaseButton label="Browse Jobs" class="bg-blue-600 hover:bg-blue-700 text-white border-none" @click="$router.push('/freelancer/jobs')" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { proposalService } from '@/services/proposalService'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BasePagination from '@/components/ui/BasePagination.vue'

const isLoading = ref(true)
const error = ref('')
const proposals = ref<any[]>([])

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})

const filters = reactive({
  search: ''
})

onMounted(() => {
  fetchMyProposals()
})

const fetchMyProposals = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await proposalService.getMyProposals({
      page: pagination.current_page,
      per_page: pagination.per_page,
      search: filters.search || undefined
    })
    
    const resData = res.data?.data
    if (resData && resData.data) {
       proposals.value = resData.data
       pagination.current_page = resData.current_page
       pagination.last_page = resData.last_page
       pagination.total = resData.total
    } else {
       proposals.value = resData || []
    }
    
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to fetch proposals'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const handlePageChange = (page: number) => {
  pagination.current_page = page
  fetchMyProposals()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const getStatusBadge = (status: string) => {
  switch (status) {
    case 'pending': return 'warning'
    case 'accepted': return 'success'
    case 'rejected': return 'danger'
    default: return 'gray'
  }
}

const formatStatus = (status: string) => {
  if (!status) return ''
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}
</script>
