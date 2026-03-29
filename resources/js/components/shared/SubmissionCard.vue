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

    <div v-if="(submission.submission_attachments || submission.attachments) && (submission.submission_attachments?.length || submission.attachments?.length)" class="space-y-2">
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Attachments</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <a v-for="att in (submission.submission_attachments || submission.attachments)" :key="att.id"
          href="javascript:void(0)" @click="$emit('preview-attachment', att)"
          class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
          <div class="w-10 h-10 rounded bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate" :title="att.title">{{ att.title || 'File' }}</p>
            <p class="text-xs text-gray-500 truncate">Click to view</p>
          </div>
        </a>
      </div>
    </div>
    
    <div 
      v-if="submission.feedback && submission.status === 'rejected'"
      class="text-sm text-yellow-800 bg-yellow-50 p-3 rounded border border-yellow-200 whitespace-pre-line"
    >
      <p class="text-sm font-semibold mb-1 text-yellow-600">
        Client Feedback
      </p>
      {{ submission.feedback }}
    </div>
    
    <div class="flex justify-end gap-2 pt-3" v-if="isClient && submission.status === 'pending'">
       <BaseButton label="Request Revision" variant="outline" size="sm" @click="$emit('request-revision', submission.id, submission.feedback)" />
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

defineEmits(['approve', 'request-revision', 'preview-attachment'])

const getStatusBadge = (status: string) => {
  if (status === 'approved') return 'success'
  if (status === 'rejected') return 'danger'
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
