<template>
  <div class="card">
    <div class="flex items-start justify-between">
      <h3 v-if="title" class="text-lg font-semibold">{{ title }}</h3>
      <button
        v-if="closeable"
        @click="$emit('close')"
        class="p-1 hover:bg-gray-100 rounded transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <div class="mt-4">
      <slot></slot>
    </div>
    <div v-if="$slots.actions" class="mt-6 flex gap-3 justify-end">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title?: string
  closeable?: boolean
}

withDefaults(defineProps<Props>(), {
  closeable: true,
})

defineEmits<{
  close: []
}>()
</script>
