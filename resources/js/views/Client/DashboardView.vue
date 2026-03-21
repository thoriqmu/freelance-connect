<template>
  <div>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold mb-2">Dashboard</h1>
        <p class="text-blue-100">Manage your projects and track progress</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Active Projects</p>
              <p class="text-3xl font-bold">{{ stats.activeProjects }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              <p class="text-gray-500 text-sm">Total Spent</p>
              <p class="text-3xl font-bold">${{ stats.totalSpent }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Proposals Received</p>
              <p class="text-3xl font-bold">{{ stats.proposalsReceived }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2-2H6l-2 2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5">
                </path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Completed</p>
              <p class="text-3xl font-bold">{{ stats.completed }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z">
                </path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <RouterLink to="/client/projects/create"
            class="card-hover"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold">Post New Project</h3>
                <p class="text-sm text-gray-500">Create and post a new project</p>
              </div>
            </div>
          </RouterLink>

          <RouterLink to="/client/projects"
            class="card-hover"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z">
                  </path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold">View All Projects</h3>
                <p class="text-sm text-gray-500">Browse your posted projects</p>
              </div>
            </div>
          </RouterLink>

          <div class="card-hover">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2-2H6l-2 2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5">
                  </path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold">View Proposals</h3>
                <p class="text-sm text-gray-500">Browse freelancer proposals</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Projects -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <h2 class="text-2xl font-bold mb-4">Recent Projects</h2>

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

          <!-- Projects List -->
          <div v-else-if="projects.length > 0" class="space-y-4">
            <div v-for="project in projects.slice(0, 3)" :key="project.id" class="card space-y-3">
              <div class="flex items-start justify-between">
                <h3 class="text-lg font-bold">{{ project.title }}</h3>
                <BaseBadge :type="getStatusType(project.status)">
                  {{ formatStatus(project.status) }}
                </BaseBadge>
              </div>
              <div class="text-gray-600 text-sm line-clamp-2 prose prose-sm max-w-none" v-html="project.description"></div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-gray-200 text-sm">
                <div>
                  <p class="text-gray-500">Budget</p>
                  <p class="font-semibold text-blue-600">${{ project.budget }}</p>
                </div>
                <div>
                  <p class="text-gray-500">Timeline</p>
                  <p class="font-semibold">
                    {{ project.timeline ? project.timeline + ' weeks' : '-' }}
                  </p>
                </div>
                <div>
                  <p class="text-gray-500">Posted</p>
                  <p class="font-semibold">
                    {{ new Date(project.created_at).toLocaleDateString() }}
                  </p>
                </div>
                <div class="flex justify-start md:justify-end">
                  <RouterLink :to="`/client/projects/${project.id}`">
                    <BaseButton
                      label="View Details"
                      variant="primary"
                    />
                  </RouterLink>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="bg-gray-100 rounded-lg p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500 mb-2">No projects yet</p>
            <RouterLink to="/client/projects/create"
              class="text-blue-600 hover:underline font-semibold"
            >
              Create your first project
            </RouterLink>
          </div>
        </div>

        <!-- Sidebar -->
        <div>
          <h2 class="text-2xl font-bold mb-4">Activity</h2>
          <div class="card">
            <div class="space-y-4">
              <div class="text-center py-8">
                <p class="text-gray-500">No activities yet</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { projectService } from '@/services/projectService'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

const authStore = useAuthStore()
const router = useRouter()

const isLoading = ref(false)
const stats = reactive({
  activeProjects: 0,
  totalSpent: 0,
  proposalsReceived: 0,
  completed: 0,
})
const projects = ref<any[]>([])

onMounted(async () => {
  if (!authStore.isClient) {
    router.push('/freelancer/jobs')
  }

  await loadProjects()
  await calculateStats()
})

async function loadProjects() {
  isLoading.value = true
  try {
    const response = await projectService.getMyProjects({
      per_page: 5,
    })

    projects.value = response.data.data || []
  } catch (error) {
    console.error('Failed to load projects:', error)
  } finally {
    isLoading.value = false
  }
}

async function calculateStats() {
  try {
    const response = await projectService.getMyProjects({
      per_page: 100,
    })

    const allProjects = response.data.data || []

    stats.activeProjects = allProjects.filter((p: any) => p.status === 'open' || p.status === 'in_progress').length
    stats.completed = allProjects.filter((p: any) => p.status === 'completed').length
    stats.totalSpent = allProjects.reduce((sum: number, p: any) => sum + (parseFloat(p.budget) || 0), 0)
    stats.proposalsReceived = allProjects.reduce((sum: number, p: any) => sum + (p.proposals_count || 0), 0)
  } catch (error) {
    console.error('Failed to calculate stats:', error)
  }
}

function getStatusType(status: string) {
  switch (status) {
    case 'open':
      return 'info'
    case 'in_progress':
      return 'warning'
    case 'completed':
      return 'success'
    case 'archived':
      return 'gray'
    default:
      return 'gray'
  }
}

function formatStatus(status: string) {
  return status
    .replace('_', ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}
</script>
