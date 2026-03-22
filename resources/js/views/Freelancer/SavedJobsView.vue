<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Saved Jobs</h1>
        <p class="text-gray-500 mt-1">Jobs you have bookmarked for later</p>
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
    <BaseAlert v-else-if="error" type="error" :message="error" />

    <!-- Job Cards -->
    <div v-else-if="jobs.length > 0" class="space-y-4">
      <JobCard
        v-for="job in jobs"
        :key="job.id"
        :job="job.project || job"
        :is-saved="true"
        @toggle-save="unsaveJob"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 bg-white rounded-xl border border-gray-100">
      <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900">No saved jobs</h3>
      <p class="text-gray-500 mt-1">You haven't saved any jobs yet. Browse available jobs and save the ones you're interested in.</p>
      <div class="mt-6">
        <RouterLink to="/freelancer/jobs">
          <BaseButton label="Browse Jobs" variant="primary" />
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import JobCard from '@/components/shared/JobCard.vue'
import { savedJobsService } from '@/services/savedJobsService'

const isLoading = ref(true)
const error = ref('')
const jobs = ref<any[]>([])

const loadSavedJobs = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await savedJobsService.getSavedJobs()
    jobs.value = res.data?.data || []
  } catch (err) {
    console.error('Failed to load saved jobs', err)
    error.value = 'Failed to load saved jobs. Please try again later.'
  } finally {
    isLoading.value = false
  }
}

const unsaveJob = async (jobId: number) => {
  const previousJobs = [...jobs.value]
  jobs.value = jobs.value.filter(j => (j.project?.id || j.id) !== jobId)
  
  try {
    await savedJobsService.unsaveJob(jobId)
  } catch (err) {
    console.error('Failed to unsave job', err)
    jobs.value = previousJobs
    alert('Failed to unsave job.')
  }
}

onMounted(() => {
  loadSavedJobs()
})
</script>
