<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="isOpen" class="fixed inset-0 z-50">
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/50"
          @click="handleBackdropClick"
        ></div>

        <!-- Modal -->
        <div class="relative z-10 flex items-center justify-center min-h-full">
          <div
            class="bg-white rounded-lg shadow-xl w-full mx-4 animate-slide-in"
            :style="{ maxWidth: `${width}px` }"
          >
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
              <h2 v-if="title" class="text-xl font-bold">{{ title }}</h2>
              <button
                v-if="showCloseButton"
                @click="$emit('close')"
                class="p-1 hover:bg-gray-100 rounded transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
  
            <!-- Content -->
            <div class="p-6">
              <slot></slot>
            </div>
  
            <!-- Footer -->
            <div v-if="$slots.actions" class="flex gap-3 justify-end p-6 border-t border-gray-200">
              <slot name="actions"></slot>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { watch } from 'vue'

interface Props {
  isOpen: boolean
  title?: string
  width?: number
  showCloseButton?: boolean
  closeOnBackdrop?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  width: 500,
  showCloseButton: true,
  closeOnBackdrop: true,
})

const emit = defineEmits<{
  close: []
}>()

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    emit('close')
  }
}

// Prevent body scroll when modal is open
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = 'auto'
    }
  },
)
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
