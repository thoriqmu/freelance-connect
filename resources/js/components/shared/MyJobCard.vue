<template>
  <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
    <!-- Header: Title + Status Badge -->
    <div class="flex items-start justify-between mb-3">
      <h3 class="text-lg font-bold text-gray-900 pr-4">
        <RouterLink :to="`/freelancer/my-jobs/${job.id}`" class="hover:text-blue-600">
          {{ job.title }}
        </RouterLink>
      </h3>
      <BaseBadge :variant="job.status === 'completed' ? 'success' : 'warning'" class="flex-shrink-0">
        {{ job.status === 'completed' ? 'Completed' : 'In Progress' }}
      </BaseBadge>
    </div>

    <!-- Description -->
    <div class="text-gray-600 mb-4 line-clamp-2 prose prose-sm max-w-none text-sm" v-html="job.description || 'No description provided'"></div>

    <!-- Skills -->
    <div v-if="skills.length > 0" class="flex flex-wrap gap-2 mb-4">
      <BaseBadge v-for="skill in skills" :key="skill" type="info">
        {{ skill }}
      </BaseBadge>
    </div>

    <!-- Footer: Client, Budget, Button -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-100 gap-4 flex-wrap mt-auto">
      <div class="flex gap-6">
        <div class="flex flex-col min-w-[100px]">
          <p class="text-gray-500 text-xs uppercase font-semibold tracking-wider">Client</p>
          <p class="text-base font-semibold text-gray-900">{{ job.client?.user?.name || 'Unknown' }}</p>
        </div>
        <div class="flex flex-col min-w-[100px]">
          <p class="text-gray-500 text-xs uppercase font-semibold tracking-wider">Budget</p>
          <p class="text-lg font-bold text-blue-600">${{ job.budget }}</p>
        </div>
      </div>
      <div class="ml-auto mt-2 sm:mt-0">
        <BaseButton
          label="View Workspace"
          variant="primary"
          @click="$router.push(`/freelancer/my-jobs/${job.id}`)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const router = useRouter()

const props = defineProps<{
  job: {
    id: number | string
    title: string
    description?: string
    status: string
    budget: number | string
    skills?: string[] | string
    client?: {
      user?: {
        name?: string
      }
    }
  }
}>()

const skills = computed(() => {
  if (!props.job.skills) return []
  if (Array.isArray(props.job.skills)) return props.job.skills
  try {
    return JSON.parse(props.job.skills)
  } catch {
    return []
  }
})
</script>