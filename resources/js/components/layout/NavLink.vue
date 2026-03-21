<template>
  <RouterLink
    :to="to"
    :class="[
      'flex items-center gap-3 px-4 py-2 rounded-lg transition-colors',
      isActive
        ? 'bg-blue-100 text-blue-700 font-semibold'
        : 'text-gray-700 hover:bg-gray-100',
    ]"
  >
    <component :is="iconComponent" class="w-5 h-5" />
    <span>{{ label }}</span>
  </RouterLink>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

interface Props {
  to: string
  label: string
  icon: string
}

const props = defineProps<Props>()
const route = useRoute()

const isActive = computed(() => {
  if (props.to === '/client/projects') {
    return route.path === '/client/projects' || /^\/client\/projects\/\d+/.test(route.path)
  }
  return route.path.startsWith(props.to)
})

const iconMap: Record<string, string> = {
  dashboard: 'dashboard-icon',
  plus: 'plus-icon',
  briefcase: 'briefcase-icon',
  user: 'user-icon',
  search: 'search-icon',
  bookmark: 'bookmark-icon',
  wallet: 'wallet-icon',
}

const iconComponent = computed(() => {
  // Return an inline SVG component based on the icon prop
  return {
    template: getIconSVG(props.icon),
  }
})

function getIconSVG(icon: string): string {
  const icons: Record<string, string> = {
    dashboard:
      '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4 4m0 0l4-4m-4 4V8"></path></svg>',
    plus: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>',
    briefcase:
      '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>',
    user: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
    search:
      '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
    bookmark:
      '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>',
    wallet:
      '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V5a3 3 0 00-3-3H6a3 3 0 00-3 3v14a3 3 0 003 3z"></path></svg>',
  }
  return icons[icon] || ''
}
</script>
