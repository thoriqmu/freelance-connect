<template>
  <div class="flex items-center justify-center gap-2">
    <button
      :disabled="currentPage === 1"
      @click="$emit('update:currentPage', currentPage - 1)"
      class="btn-outline btn-sm"
    >
      Previous
    </button>

    <div class="flex gap-1">
      <button
        v-for="page in paginationRange"
        :key="page"
        @click="$emit('update:currentPage', page)"
        :class="[
          'btn-sm px-3 py-1 rounded-lg transition-colors',
          currentPage === page
            ? 'bg-blue-600 text-white'
            : 'bg-gray-200 text-gray-800 hover:bg-gray-300',
        ]"
      >
        {{ page }}
      </button>
    </div>

    <button
      :disabled="currentPage === lastPage"
      @click="$emit('update:currentPage', currentPage + 1)"
      class="btn-outline btn-sm"
    >
      Next
    </button>

    <span class="text-sm text-gray-600 ml-4">
      Page {{ currentPage }} of {{ lastPage }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  currentPage: number
  lastPage: number
  maxVisible?: number
}

const props = withDefaults(defineProps<Props>(), {
  maxVisible: 5,
})

const paginationRange = computed(() => {
  const range: number[] = []
  const start = Math.max(1, props.currentPage - Math.floor(props.maxVisible / 2))
  const end = Math.min(props.lastPage, start + props.maxVisible - 1)

  for (let i = start; i <= end; i++) {
    range.push(i)
  }
  return range
})

defineEmits<{
  'update:currentPage': [page: number]
}>()
</script>
