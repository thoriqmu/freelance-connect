<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Manage Proposals</h1>
        <p class="text-gray-500 mt-1">Review all proposals from freelancers for your projects.</p>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="w-full md:w-96">
        <BaseInput
          v-model="filters.search"
          placeholder="Search by project title or freelancer name..."
          type="text"
          @input="handleSearch"
        />
      </div>

      <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
        <button
          v-for="statusOption in statusOptions"
          :key="statusOption.value"
          @click="setStatus(statusOption.value)"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors',
            filters.status === statusOption.value
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          ]"
        >
          {{ statusOption.label }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-4 text-sm">
        <div class="flex justify-between items-start mb-4 border-b border-gray-50 pb-3">
          <div class="h-5 bg-gray-200 rounded w-1/3"></div>
          <div class="h-5 bg-gray-200 rounded w-16"></div>
        </div>
        <div class="space-y-3">
          <div class="h-4 bg-gray-200 rounded w-1/4"></div>
          <div class="h-10 bg-gray-200 rounded w-full"></div>
          <div class="flex gap-4">
             <div class="h-12 bg-gray-100 rounded w-24"></div>
             <div class="h-12 bg-gray-100 rounded w-24"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-lg border border-red-100">
      {{ error }}
    </div>

    <!-- Proposals List -->
    <div v-else-if="proposals.length > 0" class="space-y-4">
      <div 
        v-for="prop in proposals" 
        :key="prop.id"
        class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 items-start md:items-center justify-between hover:shadow-md transition-shadow"
      >
        <div class="flex-1 w-full min-w-0">
          <!-- Project Title + Status Badge -->
          <div class="flex items-center gap-3 mb-2">
            <h3 class="text-xl font-bold text-gray-900 truncate">
              <RouterLink :to="`/client/projects/${prop.project_id}`" class="hover:text-blue-600 transition-colors">
                {{ prop.project?.title || 'Unknown Project' }}
              </RouterLink>
            </h3>
            <BaseBadge :type="getStatusBadge(prop.status)">
              {{ formatStatus(prop.status) }}
            </BaseBadge>
          </div>

          <!-- Freelancer Name -->
          <p class="text-md font-bold text-gray-700 mb-2">
            {{ prop.freelancer?.user?.name || 'Unknown Freelancer' }}
          </p>

          <ExpandableHTML :content="prop.message" class="text-sm text-gray-600" />

          <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mt-3">
            <span class="flex items-center gap-1 font-medium text-gray-900">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Bid: <span class="text-blue-600">${{ prop.bid_price }}</span>
            </span>
            <span class="flex items-center gap-1 font-medium text-gray-900">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Est. Time: {{ prop.estimated_time }} weeks
            </span>
          </div>
        </div>

        <div class="shrink-0 w-full md:w-auto md:mt-0 pt-4 md:pt-0 border-t md:border-0 border-gray-100 flex flex-col items-end gap-2 text-sm text-gray-500">
          <span>Submitted on {{ new Date(prop.created_at).toLocaleDateString() }}</span>
          <BaseButton 
            label="View Project" 
            variant="outline" 
            size="sm"
            @click.stop="goToProjectDetail(prop.project_id)"
            class="flex items-center gap-1"
          />
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-8 flex justify-center">
        <BasePagination
          :current-page="currentPage"
          :last-page="totalPages"
          @update:current-page="changePage"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
      <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">No Proposals Found</h3>
      <p class="text-gray-500 max-w-sm mx-auto mb-8">
        {{ hasActiveFilters ? 'Try adjusting your filters to find what you are looking for.' : 'Freelancers haven\'t submitted any proposals for your projects yet.' }}
      </p>
      <BaseButton v-if="hasActiveFilters" label="Clear All Filters" variant="outline" @click="resetFilters" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { proposalService } from '@/services/proposalService'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BasePagination from '@/components/ui/BasePagination.vue'
import ExpandableHTML from '@/components/ui/ExpandableHTML.vue'

const router = useRouter()
const proposals = ref<any[]>([])
const isLoading = ref(true)
const error = ref('')

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = ref(10)

// Filters
const filters = reactive({
  search: '',
  status: 'all'
})

const statusOptions = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Accepted', value: 'accepted' },
  { label: 'Rejected', value: 'rejected' },
]

let searchTimeout: any = null

const hasActiveFilters = computed(() => {
  return filters.search !== '' || filters.status !== 'all'
})

const fetchProposals = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: filters.search,
      status: filters.status !== 'all' ? filters.status : undefined
    }
    const res = await proposalService.getClientProposals(params)
    
    // Fix: Access res.data.data directly based on ApiResponse trait
    proposals.value = res.data?.data || []
    
    // Fix: Access res.data.meta for pagination
    if (res.data?.meta) {
        currentPage.value = res.data.meta.current_page || 1
        totalPages.value = res.data.meta.last_page || 1
    }
  } catch (err: any) {
    error.value = 'Failed to load proposals. Please try again later.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchProposals()
  }, 500)
}

const setStatus = (status: string) => {
  filters.status = status
  currentPage.value = 1
  fetchProposals()
}

const resetFilters = () => {
  filters.search = ''
  filters.status = 'all'
  currentPage.value = 1
  fetchProposals()
}

const changePage = (page: number) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchProposals()
}

const goToProjectDetail = (projectId: number) => {
  router.push(`/client/projects/${projectId}`)
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

onMounted(() => {
  fetchProposals()
})
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: .5; }
}
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
