<template>
  <div class="expandable-html-container">
    <div 
      ref="contentRef"
      class="prose prose-sm max-w-none text-gray-700 transition-all duration-300"
      :class="{ 'line-clamp-2': !isExpanded }"
      v-html="content"
    ></div>
    
    <button 
      v-if="showToggleButton"
      @click="toggleExpand" 
      class="mt-1 text-sm font-medium text-blue-600 hover:text-blue-700 focus:outline-none"
    >
      {{ isExpanded ? 'Show less' : 'Read more' }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue'

const props = defineProps<{
  content: string
}>()

const contentRef = ref<HTMLElement | null>(null)
const isExpanded = ref(false)
const showToggleButton = ref(false)

const checkOverflow = async () => {
  await nextTick()
  if (contentRef.value) {
    // Temporarily remove line-clamp to get full height if it was clamped, 
    // but a safer way is just checking scrollHeight > clientHeight when clamped.
    // Ensure we are initially clamped to test overflow:
    if (!isExpanded.value) {
        showToggleButton.value = contentRef.value.scrollHeight > contentRef.value.clientHeight
    }
  }
}

watch(() => props.content, () => {
  checkOverflow()
}, { immediate: true })

onMounted(() => {
  checkOverflow()
  // Add a resize listener in case window size changes breaking the clamp check
  window.addEventListener('resize', checkOverflow)
})

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}
</script>

<style>
/* Ensure lists and paragraphs sit nicely inside line-clamp */
.expandable-html-container .line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
.expandable-html-container .prose p {
  margin-bottom: 0.5rem;
}
.expandable-html-container .prose p:last-child {
  margin-bottom: 0;
}
</style>
