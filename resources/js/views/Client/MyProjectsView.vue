<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">My Projects</h1>
        <p class="text-gray-500 mt-1">Manage and track your posted projects.</p>
      </div>
      <RouterLink to="/client/projects/create">
        <BaseButton label="Create Project" variant="primary" />
      </RouterLink>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="w-full md:w-96">
        <BaseInput
          v-model="filters.search"
          placeholder="Search projects..."
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
      <div v-for="i in 3" :key="i" class="card animate-pulse">
        <div class="space-y-3">
          <div class="h-6 bg-gray-200 rounded w-1/2"></div>
          <div class="h-4 bg-gray-200 rounded w-full"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-lg">
      {{ error }}
    </div>

    <!-- Projects List -->
    <div v-else-if="projects?.length > 0" class="space-y-4">
      <div v-for="project in projects" :key="project.id" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4">
        <div class="flex items-start justify-between">
          <div>
             <h3 class="text-lg font-bold text-gray-900 mb-1 hover:text-blue-600 transition-colors">
               <RouterLink :to="`/client/projects/${project.id}`">{{ project.title }}</RouterLink>
             </h3>
             <p class="text-sm text-gray-500">Posted on {{ new Date(project.created_at).toLocaleDateString() }}</p>
          </div>
          <BaseBadge :type="getStatusType(project.status)">
            {{ formatStatus(project.status) }}
          </BaseBadge>
        </div>
        
        <div class="text-gray-600 text-sm line-clamp-2 md:line-clamp-3 prose prose-sm max-w-none" v-html="project.description"></div>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100 text-sm">
          <div class="flex items-center justify-between lg:block bg-gray-50 lg:bg-transparent p-3 lg:p-0 rounded-lg">
            <p class="text-gray-500">Budget</p>
            <p class="font-semibold text-blue-600">${{ project.budget }}</p>
          </div>
          <div class="flex items-center justify-between lg:block bg-gray-50 lg:bg-transparent p-3 lg:p-0 rounded-lg">
            <p class="text-gray-500">Timeline</p>
            <p class="font-semibold text-gray-900">{{ project.timeline ? project.timeline + ' weeks' : '-' }}</p>
          </div>
          <div class="flex items-center justify-between lg:block bg-gray-50 lg:bg-transparent p-3 lg:p-0 rounded-lg">
            <p class="text-gray-500">Proposals</p>
            <p class="font-semibold text-gray-900">{{ project.proposals_count || 0 }}</p>
          </div>
          <div class="flex items-center justify-between lg:block bg-gray-50 lg:bg-transparent p-3 lg:p-0 rounded-lg lg:text-right">
             <RouterLink :to="`/client/projects/${project.id}`" class="w-full">
              <BaseButton
                label="Manage Project"
                variant="primary"
              />
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8 flex justify-center">
        <BasePagination
          v-if="pagination.lastPage > 1"
          :current-page="pagination.currentPage"
          :last-page="pagination.lastPage"
          @page-change="handlePageChange"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2-2H6l-2 2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-1">No Projects Found</h3>
      <p class="text-gray-500 mb-6">You haven't posted any projects matching the criteria.</p>
      <RouterLink to="/client/projects/create" v-if="filters.search === '' && filters.status === ''">
        <BaseButton label="Create Your First Project" variant="primary" />
      </RouterLink>
      <BaseButton v-else label="Clear Filters" variant="outline" @click="resetFilters" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BasePagination from '@/components/ui/BasePagination.vue'
import { projectService } from '@/services/projectService'

const router = useRouter()

const projects = ref<any[]>([])
const isLoading = ref(true)
const error = ref('')

const filters = reactive({
  search: '',
  status: '', // empty means all
  page: 1,
})

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
})

const statusOptions = [
  { label: 'All', value: '' },
  { label: 'Open', value: 'open' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Completed', value: 'completed' },
  { label: 'Archived', value: 'archived' },
]

let searchTimeout: any = null

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filters.page = 1
    fetchProjects()
  }, 500)
}

const setStatus = (status: string) => {
  filters.status = status
  filters.page = 1
  fetchProjects()
}

const resetFilters = () => {
    filters.search = ''
    filters.status = ''
    filters.page = 1
    fetchProjects()
}

const handlePageChange = (page: number) => {
  filters.page = page
  fetchProjects()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const fetchProjects = async () => {
  isLoading.value = true
  error.value = ''
  
  try {
    const params: Record<string, any> = {
      page: filters.page,
      per_page: 10,
    }
    
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status
    const response = await projectService.getMyProjects(params)
    
    projects.value = response?.data?.data || []
    
    // Perhatikan ApiResponse yang dipisah ke meta
    if (response?.data?.meta) {
      pagination.currentPage = response.data.meta.current_page || 1
      pagination.lastPage = response.data.meta.last_page || 1
    } else {
      pagination.currentPage = 1
      pagination.lastPage = 1
    }
  } catch (err: any) {
    error.value = 'Failed to load projects. Please try again later.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const getStatusType = (status: string) => {
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

onMounted(() => {
  fetchProjects()
})
</script>

<style>
/* Safe simple formatting for rich text from Quill inside MyProjects */
.prose p {
  margin-bottom: 0.5rem;
}
.prose strong, .prose b {
  font-weight: 600;
}
.prose em, .prose i {
  font-style: italic;
}
.prose ul {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 0.5rem;
}
.prose ol {
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin-bottom: 0.5rem;
}
</style>
