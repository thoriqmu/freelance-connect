<template>
  <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
    <div class="flex items-start gap-4 flex-1">
      <img
        :src="proposal.freelancer?.user?.avatar || '/img/avatar.png'"
        @error="($event.target as HTMLImageElement).src = '/img/avatar.png'"
        class="w-12 h-12 rounded-full object-cover border border-gray-100"
        alt="Avatar"
      />

      <div class="space-y-1">
        <h4 class="font-bold text-gray-900 text-lg">{{ proposal.freelancer?.user?.name || 'Unknown Freelancer' }}</h4>
        <ExpandableHTML :content="proposal.message" />
      </div>
    </div>
    
    <div class="flex flex-col md:items-end gap-2 w-full md:w-auto mt-4 md:mt-0 pt-4 md:pt-0 border-t md:border-0 border-gray-100">
      <div class="flex gap-4 mb-2 text-sm">
        <div>
          <span class="text-gray-500">Bid: </span>
          <span class="font-bold text-blue-600">${{ proposal.bid_price }}</span>
        </div>
        <div>
          <span class="text-gray-500">Time: </span>
          <span class="font-semibold">{{ proposal.estimated_time }} week</span>
        </div>
      </div>
      
      <div class="flex gap-3 w-full md:w-auto items-center">
        <BaseButton 
          v-if="proposal.freelancer_id" 
          label="View Profile" 
          variant="outline" 
          size="sm" 
          class="text-blue-600 border-gray-200"
          @click="$router.push(`/client/freelancers/${proposal.freelancer?.user?.id}?project_id=${proposal.project_id}`)" 
        />
        <template v-if="proposal.status === 'pending'">
          <BaseButton label="Reject" variant="outline" size="sm" class="flex-1 md:flex-none text-red-600 hover:bg-red-50 border-gray-300" @click="$emit('reject', proposal.id)" :disabled="isProcessing" />
          <BaseButton label="Accept" variant="primary" size="sm" class="flex-1 md:flex-none bg-green-600 hover:bg-green-700 border-none" @click="$emit('accept', proposal.id)" :disabled="isProcessing" />
        </template>
        <template v-else>
          <BaseBadge :type="proposal.status === 'accepted' ? 'success' : 'danger'">
            {{ formatStatus(proposal.status) }}
          </BaseBadge>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import ExpandableHTML from '@/components/ui/ExpandableHTML.vue'

defineProps<{
  proposal: any
  isProcessing?: boolean
}>()

defineEmits(['accept', 'reject'])

const formatStatus = (status: string) => {
  if (!status) return ''
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}
</script>
