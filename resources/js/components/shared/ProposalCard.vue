<template>
  <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
    <div class="flex items-start gap-4 flex-1">
      <img
        :src="proposal.freelancer?.avatar_url || '/img/avatar.png'"
        @error="$event.target.src = '/img/avatar.png'"
        class="w-12 h-12 rounded-full object-cover border border-gray-100"
        alt="Avatar"
      />
      
      <div class="space-y-1">
        <h4 class="font-bold text-gray-900 text-lg">{{ proposal.freelancer?.name || 'Unknown Freelancer' }}</h4>
        <div class="text-sm text-gray-600 line-clamp-2">{{ proposal.message }}</div>
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
          <span class="font-semibold">{{ proposal.estimated_time }}</span>
        </div>
      </div>
      
      <div class="flex gap-2 w-full md:w-auto">
        <template v-if="proposal.status === 'PENDING'">
          <BaseButton label="Reject" variant="outline" size="sm" class="flex-1 md:flex-none text-red-600 hover:bg-red-50 border-gray-300" @click="$emit('reject', proposal.id)" :disabled="isProcessing" />
          <BaseButton label="Accept" variant="primary" size="sm" class="flex-1 md:flex-none bg-green-600 hover:bg-green-700 border-none" @click="$emit('accept', proposal.id)" :disabled="isProcessing" />
        </template>
        <template v-else>
          <BaseBadge :type="proposal.status === 'ACCEPTED' ? 'success' : 'danger'">
            {{ proposal.status }}
          </BaseBadge>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

defineProps<{
  proposal: any
  isProcessing?: boolean
}>()

defineEmits(['accept', 'reject'])
</script>
