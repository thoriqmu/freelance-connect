<template>
  <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 space-y-3">
    <div class="flex justify-between items-start">
      <div>
        <h4 class="font-bold text-gray-900">Submission #{{ submission.id }}</h4>
        <p class="text-xs text-gray-500">{{ new Date(submission.created_at).toLocaleString() }}</p>
      </div>
      <BaseBadge :type="getStatusBadge(submission.status)">
        {{ formatStatus(submission.status) }}
      </BaseBadge>
    </div>
    
    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-gray-100 whitespace-pre-line">
      {{ submission.note || 'No notes provided.' }}
    </div>

    <div v-if="submission.attachments && submission.attachments.length" class="space-y-2">
      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Attachments</p>
      <div class="flex flex-wrap gap-2">
        <a 
          v-for="att in submission.attachments" 
          :key="att.id"
          :href="getAttachmentUrl(att.file_path)"
          target="_blank"
          class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 rounded text-sm text-blue-600 hover:underline hover:bg-gray-50 transition-colors"
        >
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
          {{ att.title || 'File' }}
        </a>
      </div>
    </div>
    
    <div class="flex justify-end gap-2 pt-3" v-if="isClient && submission.status === 'PENDING_REVIEW'">
       <BaseButton label="Request Revision" variant="outline" size="sm" @click="$emit('request-revision', submission.id)" />
       <BaseButton label="Approve" variant="primary" size="sm" @click="$emit('approve', submission.id)" />
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

defineProps<{
  submission: any
  isClient?: boolean
}>()

defineEmits(['approve', 'request-revision'])

const getStatusBadge = (status: string) => {
  if (status === 'APPROVED') return 'success'
  if (status === 'REVISION_REQUESTED') return 'warning'
  return 'info'
}

const formatStatus = (status: string) => {
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const getAttachmentUrl = (path: string) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '')
  return `${baseUrl}/storage/${path}`
}
</script>
