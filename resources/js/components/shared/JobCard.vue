<template>
  <div class="card-hover bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between mb-4">
      <div class="flex-1 pr-4">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <h3 class="text-xl font-bold text-gray-900">{{ job.title }}</h3>
          <BaseBadge :type="job.status === 'open' ? 'success' : 'warning'">
            {{ formatStatus(job.status) }}
          </BaseBadge>
        </div>
      </div>
      <button
        @click="$emit('toggle-save', job.id)"
        :class="[
          'p-2 rounded-lg transition-colors flex-shrink-0',
          isSaved
            ? 'bg-yellow-100 text-yellow-600'
            : 'bg-gray-100 text-gray-400 hover:bg-gray-200'
        ]"
      >
        <svg
          v-if="isSaved"
          class="w-5 h-5"
          fill="currentColor"
          viewBox="0 0 24 24"
        >
          <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"></path>
        </svg>
        <svg
          v-else
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z">
          </path>
        </svg>
      </button>
    </div>

    <div class="text-gray-600 mb-4 line-clamp-2 prose prose-sm max-w-none" v-html="job.description || 'No description provided'"></div>

    <div v-if="skills.length > 0" class="flex flex-wrap gap-2 mb-4">
      <BaseBadge v-for="skill in skills" :key="skill" type="info">
        {{ skill }}
      </BaseBadge>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-gray-100 gap-4 flex-wrap mt-auto">
      <div class="flex flex-col min-w-[100px]">
        <p class="text-gray-500 text-xs uppercase font-semibold tracking-wider">Budget</p>
        <p class="text-lg font-bold text-blue-600">${{ job.budget }}</p>
      </div>
      <div class="flex flex-col min-w-[100px]">
        <p class="text-gray-500 text-xs uppercase font-semibold tracking-wider">Timeline</p>
        <p class="text-base font-semibold text-gray-900">{{ job.timeline || '-' }} weeks</p>
      </div>
      <div class="flex flex-col min-w-[120px]">
        <p class="text-gray-500 text-xs uppercase font-semibold tracking-wider">Posted</p>
        <p class="text-base font-semibold text-gray-900">
          {{ new Date(job.created_at).toLocaleDateString() }}
        </p>
      </div>
      <div class="ml-auto mt-2 sm:mt-0">
        <RouterLink :to="`/freelancer/jobs/${job.id}`">
          <BaseButton label="View & Bid" variant="primary" />
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const props = defineProps<{
  job: any
  isSaved?: boolean
}>()

defineEmits(['toggle-save'])

const skills = computed(() => {
  if (!props.job.required_skills) return []
  return typeof props.job.required_skills === 'string' 
    ? JSON.parse(props.job.required_skills) 
    : props.job.required_skills
})

const formatStatus = (status: string) => {
  if (!status) return ''
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}
</script>
