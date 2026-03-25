<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">My Jobs</h1>
        <p class="text-gray-500 mt-1">Manage your ongoing and completed projects</p>
      </div>
    </div>
    
    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-center justify-between mb-6">
      <div class="w-full md:w-96">
        <BaseInput
          v-model="filters.search"
          placeholder="Search projects..."
          type="text"
          @update:model-value="handleSearch"
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

    <!-- Job Listings -->
    <div class="space-y-4">
      <!-- Loading State -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-pulse">
          <div class="space-y-3">
            <div class="h-6 bg-gray-200 rounded w-1/3"></div>
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="h-4 bg-gray-200 rounded w-full"></div>
          </div>
        </div>
      </div>

      <!-- Job Cards -->
      <div v-else-if="jobs.length > 0" class="space-y-4">
        <MyJobCard
          v-for="job in jobs"
          :key="job.id"
          :job="job"
        />

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
          <BasePagination
            :current-page="pagination.currentPage"
            :last-page="pagination.lastPage"
            @update:current-page="pagination.currentPage = $event"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-gray-50 rounded-lg p-12 text-center border border-dashed border-gray-300">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-500 mb-4">No {{ filters.status === 'all' ? '' : filters.status.replace('_', ' ') }} jobs found.</p>
        <BaseButton 
          v-if="filters.status === 'in_progress'"
          label="Browse Available Jobs" 
          variant="outline" 
          @click="$router.push('/freelancer/jobs')"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { projectService } from '@/services/projectService'
import BaseButton from '@/components/ui/BaseButton.vue'
import BasePagination from '@/components/ui/BasePagination.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import MyJobCard from '@/components/shared/MyJobCard.vue'

const router = useRouter()
const isLoading = ref(true)
const jobs = ref<any[]>([])

const statusOptions = [
  { label: 'All', value: 'all' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Completed', value: 'completed' },
]

const filters = ref({
  search: '',
  status: 'all',
})

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
})

const fetchJobs = async () => {
  isLoading.value = true
  try {
    const response = await projectService.getFreelancerJobs({
      status: filters.value.status !== 'all' ? filters.value.status : undefined,
      search: filters.value.search || undefined,
      page: pagination.value.currentPage,
    })
    jobs.value = response.data?.data?.data || response.data?.data || []
    if (response.data?.data?.last_page) {
      pagination.value.lastPage = response.data.data.last_page
    } else if (response.data?.meta?.last_page) {
      pagination.value.lastPage = response.data.meta.last_page
    }
  } catch (err) {
    console.error('Failed to load my jobs:', err)
  } finally {
    isLoading.value = false
  }
}

const setStatus = (value: string) => {
  filters.value.status = value
  pagination.value.currentPage = 1
  fetchJobs()
}

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.currentPage = 1
    fetchJobs()
  }, 400)
}

watch(() => pagination.value.currentPage, () => {
  fetchJobs()
})

onMounted(() => {
  fetchJobs()
})
</script>