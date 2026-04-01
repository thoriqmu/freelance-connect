<template>
  <div 
    :class="[
      'p-4 border-b border-gray-100 transition-colors cursor-pointer',
      !notification.read_at ? 'bg-blue-50/50 hover:bg-blue-50' : 'bg-white hover:bg-gray-50'
    ]"
    @click="$emit('click', notification)"
  >
    <div class="flex gap-4">
      <!-- Icon based on type -->
      <div :class="['w-10 h-10 rounded-full flex items-center justify-center shrink-0', iconBgClass]">
        <component :is="iconComponent" class="w-5 h-5" />
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-start mb-1">
          <p :class="['text-sm line-clamp-2', !notification.read_at ? 'font-bold text-gray-900' : 'text-gray-600']">
            {{ notification.message }}
          </p>
          <span v-if="!notification.read_at" class="w-2 h-2 bg-blue-600 rounded-full shrink-0 mt-1.5 ml-2"></span>
        </div>
        <p class="text-xs text-gray-400">
          {{ timeAgo }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { 
  BellIcon, 
  ChatBubbleLeftEllipsisIcon, 
  DocumentCheckIcon, 
  UserPlusIcon, 
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps<{
  notification: any
}>()

defineEmits(['click'])

const iconComponent = computed(() => {
  switch (props.notification.type) {
    case 'new_proposal': return UserPlusIcon
    case 'proposal_accepted': return CheckCircleIcon
    case 'proposal_rejected': return XCircleIcon
    case 'new_submission': return DocumentCheckIcon
    case 'submission_approved': return CheckCircleIcon
    case 'revision_requested': return ArrowPathIcon
    case 'new_message': return ChatBubbleLeftEllipsisIcon
    case 'project_completed': return BellIcon
    default: return BellIcon
  }
})

const iconBgClass = computed(() => {
  switch (props.notification.type) {
    case 'proposal_accepted':
    case 'submission_approved':
    case 'project_completed':
      return 'bg-green-100 text-green-600'
    case 'proposal_rejected':
    case 'revision_requested':
      return 'bg-red-100 text-red-600'
    case 'new_proposal':
    case 'new_submission':
    case 'new_message':
      return 'bg-blue-100 text-blue-600'
    default:
      return 'bg-gray-100 text-gray-600'
  }
})

const timeAgo = computed(() => {
  const date = new Date(props.notification.created_at)
  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  
  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`
  
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}h ago`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays}d ago`
  
  return date.toLocaleDateString()
})
</script>
