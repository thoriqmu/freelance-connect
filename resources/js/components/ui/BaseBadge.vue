<template>
  <span :class="['badge', badgeClasses]">
    <slot></slot>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  type?: 'success' | 'warning' | 'danger' | 'info' | 'gray'
}

const props = withDefaults(defineProps<Props>(), {
  type: 'gray',
})

const badgeClasses = computed(() => {
  const typeMap: Record<string, string> = {
    success: 'badge-success',
    warning: 'badge-warning',
    danger: 'badge-danger',
    info: 'badge-info',
    gray: 'badge-gray',
  }
  return typeMap[props.type]
})
</script>
