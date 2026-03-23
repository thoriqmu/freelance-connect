<template>
  <div>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold mb-2">Browse Jobs</h1>
        <p class="text-blue-100">Discover opportunities and start earning</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Filters -->
      <div class="card mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <BaseInput
            v-model="filters.search"
            type="text"
            placeholder="Search projects..."
            label="Search"
          />

          <!-- Budget Filter -->
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">Budget Range</label>
            <select v-model="filters.budget" class="input-base">
              <option value="">All Budgets</option>
              <option value="0-100">$0 - $100</option>
              <option value="100-500">$100 - $500</option>
              <option value="500-1000">$500 - $1,000</option>
              <option value="1000">$1,000+</option>
            </select>
          </div>

          <!-- Sort Filter -->
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">Sort By</label>
            <select v-model="filters.sort" class="input-base">
              <option value="created_at-desc">Newest First</option>
              <option value="created_at-asc">Oldest First</option>
              <option value="budget-desc">Budget: High to Low</option>
              <option value="budget-low">Budget: Low to High</option>
              <option value="title-asc">Title: A-Z</option>
            </select>
          </div>

          <!-- Filter Button -->
          <div class="flex items-end">
            <BaseButton
              label="Apply Filters"
              variant="primary"
              class="w-full"
              @click="applyFilters"
            />
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Available Jobs</p>
              <div v-if="isStatsLoading" class="h-9 w-16 bg-gray-200 rounded animate-pulse mt-1"></div>
              <p v-else class="text-3xl font-bold">{{ stats.available_jobs }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 13V7a2 2 0 00-2-2h-3V4a2 2 0 00-2-2h-2a2 2 0 00-2 2v1H6a2 2 0 00-2 2v6m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4">
                </path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">My Active Bids</p>
              <div v-if="isStatsLoading" class="h-9 w-16 bg-gray-200 rounded animate-pulse mt-1"></div>
              <p v-else class="text-3xl font-bold">{{ stats.active_bids }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z">
                </path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Saved Jobs</p>
              <div v-if="isStatsLoading" class="h-9 w-16 bg-gray-200 rounded animate-pulse mt-1"></div>
              <p v-else class="text-3xl font-bold">{{ stats.saved_jobs }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z">
                </path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Job Listings -->
      <div>
        <h2 class="text-2xl font-bold mb-4">Featured Projects</h2>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
          <div v-for="i in 5" :key="i" class="card animate-pulse">
            <div class="space-y-3">
              <div class="h-6 bg-gray-200 rounded w-1/2"></div>
              <div class="h-4 bg-gray-200 rounded w-full"></div>
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            </div>
          </div>
        </div>

        <!-- Job Cards -->
        <div v-else class="space-y-4">
          <JobCard
            v-for="job in jobs"
            :key="job.id"
            :job="job"
            :is-saved="savedJobs.includes(job.id)"
            @toggle-save="saveJob"
          />

          <!-- Pagination -->
          <div v-if="jobs.length > 0" class="mt-8">
            <BasePagination
              :current-page="pagination.currentPage"
              :last-page="pagination.lastPage"
              @update:current-page="pagination.currentPage = $event"
            />
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && jobs.length === 0" class="bg-gray-100 rounded-lg p-12 text-center">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <p class="text-gray-500 mb-4">No jobs found matching your criteria</p>
          <BaseButton
            label="Clear Filters"
            variant="outline"
            @click="clearFilters"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { projectService } from '@/services/projectService'
import { savedJobsService } from '@/services/savedJobsService'
import apiClient from '@/services/apiClient'
import JobCard from '@/components/shared/JobCard.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BasePagination from '@/components/ui/BasePagination.vue'

const authStore = useAuthStore()
const router = useRouter()

const isLoading = ref(false)
const savedJobs = ref<number[]>([])
const jobs = ref<any[]>([])
const isStatsLoading = ref(true)
const stats = ref({
  available_jobs: 0,
  active_bids: 0,
  saved_jobs: 0
})

const filters = reactive({
  search: '',
  budget: '',
  sort: 'created_at-desc',
})

const pagination = reactive({
  currentPage: 1,
  lastPage: 5,
  total: 20,
  perPage: 5,
})

onMounted(() => {
  if (!authStore.isFreelancer) {
    router.push('/client/dashboard')
  }
  loadProjects()
  checkSavedJobs()
  fetchStats()
})

// Watch pagination changes
watch(() => pagination.currentPage, () => {
  loadProjects()
})

async function loadProjects() {
  isLoading.value = true
  try {
    // Convert budget filter to min/max
    let budget_min: number | undefined
    let budget_max: number | undefined

    if (filters.budget) {
      const parts = filters.budget.split('-').map(Number)
      budget_min = parts[0]
      budget_max = parts[1] === 0 || isNaN(parts[1]) ? undefined : parts[1]
    }

    const [sort_by, sort_order] = filters.sort.split('-')

    const response = await projectService.getProjects({
      search: filters.search || undefined,
      status: 'open',
      min_budget: budget_min !== undefined ? budget_min : undefined,
      max_budget: budget_max !== undefined ? budget_max : undefined,
      sort_by,
      sort_order: sort_order === 'low' ? 'asc' : sort_order,
      page: pagination.currentPage,
      per_page: pagination.perPage,
    })

    jobs.value = response.data.data || []
    if (response.data.meta) {
      pagination.lastPage = response.data.meta.last_page
      pagination.total = response.data.meta.total
    }
  } catch (error) {
    console.error('Failed to load projects:', error)
  } finally {
    isLoading.value = false
  }
}

async function fetchStats() {
  isStatsLoading.value = true
  try {
    const res = await apiClient.get('/freelancer/stats')
    stats.value = res.data.data
  } catch (err) {
    console.error('Failed to fetch stats', err)
  } finally {
    isStatsLoading.value = false
  }
}

function applyFilters() {
  pagination.currentPage = 1
  loadProjects()
}

function clearFilters() {
  filters.search = ''
  filters.budget = ''
  filters.sort = 'created_at-desc'
  pagination.currentPage = 1
  loadProjects()
}

async function checkSavedJobs() {
  try {
    const res = await savedJobsService.getSavedJobs()
    const saved = res.data?.data || []
    savedJobs.value = saved.map((item: any) => item.project_id)
  } catch (error) {
    console.error('Failed to load saved jobs', error)
  }
}

async function saveJob(jobId: number) {
  try {
    const isSaved = savedJobs.value.includes(jobId)

    if (isSaved) {
      savedJobs.value = savedJobs.value.filter(id => id !== jobId)
      await savedJobsService.unsaveJob(jobId)
    } else {
      savedJobs.value.push(jobId)
      await savedJobsService.saveJob(jobId)
    }
  } catch (error) {
    console.error('Failed to toggle saved job', error)
    await checkSavedJobs()
  }
}
</script>
