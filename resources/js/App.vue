<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <AppNavbar v-if="$route.meta.layout !== 'auth'" />

    <!-- Main Content -->
    <div class="flex-1 flex items-start">
      <!-- Sidebar -->
      <AppSidebar
        v-if="authStore.isLoggedIn && $route.meta.layout !== 'auth'"
        :is-open="sidebarOpen"
        @close="sidebarOpen = false"
      />

      <!-- Page Content -->
      <main class="flex-1 min-h-[calc(100vh-4rem)]">
        <RouterView />
      </main>
    </div>

    <!-- Footer -->
    <AppFooter v-if="$route.meta.layout !== 'auth'" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'

const authStore = useAuthStore()
const sidebarOpen = ref(false)

onMounted(async () => {
  // Try to restore user session on mount
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchMe()
    } catch (error) {
      console.error('Session restoration failed:', error)
    }
  }
})
</script>
