<template>
  <div>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold mb-2">My Jobs</h1>
        <p class="text-blue-100">Manage your ongoing and completed projects</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Tabs -->
      <div class="flex gap-4 border-b border-gray-200 mb-6">
        <button
          @click="activeTab = 'in_progress'"
          :class="['px-4 py-2 font-medium text-sm transition-colors relative', activeTab === 'in_progress' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700']"
        >
          In Progress
          <div v-if="activeTab === 'in_progress'" class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600"></div>
        </button>
        <button
          @click="activeTab = 'completed'"
          :class="['px-4 py-2 font-medium text-sm transition-colors relative', activeTab === 'completed' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700']"
        >
          Completed
          <div v-if="activeTab === 'completed'" class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600"></div>
        </button>
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
          <div v-for="job in jobs" :key="job.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">
                  <RouterLink :to="`/freelancer/my-jobs/${job.id}`" class="hover:text-blue-600">
                    {{ job.title }}
                  </RouterLink>
                </h3>
                <p class="text-sm text-gray-500 flex items-center gap-2">
                  <span>Client: {{ job.client?.user?.name || 'Unknown' }}</span>
                  <span>&bull;</span>
                  <span>Budget: ${{ job.budget }}</span>
                </p>
              </div>
              <BaseBadge :variant="job.status === 'completed' ? 'success' : 'warning'">
                {{ job.status === 'completed' ? 'Completed' : 'In Progress' }}
              </BaseBadge>
            </div>
            
            <ExpandableHTML :content="job.description?.substring(0, 150) + '...'" class="text-gray-600 mb-4 text-sm" />

            <div class="flex items-center justify-end border-t border-gray-100 pt-4 mt-4">
              <BaseButton 
                label="View Workspace" 
                variant="primary" 
                size="sm" 
                @click="$router.push(`/freelancer/my-jobs/${job.id}`)"
              />
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="jobs.length > 0" class="mt-8 flex justify-center">
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
          <p class="text-gray-500 mb-4">No {{ activeTab.replace('_', ' ') }} jobs found.</p>
          <BaseButton 
            v-if="activeTab === 'in_progress'"
            label="Browse Available Jobs" 
            variant="outline" 
            @click="$router.push('/freelancer/jobs')"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { projectService } from '@/services/projectService'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BasePagination from '@/components/ui/BasePagination.vue'
import ExpandableHTML from '@/components/ui/ExpandableHTML.vue'

const router = useRouter()
const activeTab = ref<'in_progress'|'completed'>('in_progress')
const isLoading = ref(true)
const jobs = ref<any[]>([])

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
})

const fetchJobs = async () => {
  isLoading.value = true
  try {
    const response = await projectService.getFreelancerJobs({
      status: activeTab.value,
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

watch(activeTab, () => {
  pagination.value.currentPage = 1
  fetchJobs()
})

watch(() => pagination.value.currentPage, () => {
  fetchJobs()
})

onMounted(() => {
  fetchJobs()
})
</script>
