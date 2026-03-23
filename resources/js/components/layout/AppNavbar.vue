<template>
  <nav class="bg-white shadow sticky top-0 z-40 h-16">
    <div class="max-w-7xl mx-auto px-4 h-full">
      <div class="flex items-center justify-between h-full">
        <div class="flex items-center gap-2">
          <!-- Mobile Sidebar Toggle -->
          <button
            v-if="authStore.isLoggedIn"
            @click="$emit('toggle-sidebar')"
            class="md:hidden p-2 hover:bg-gray-100 rounded transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

          <!-- Logo -->
          <RouterLink to="/" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">FC</span>
            </div>
            <span class="font-bold text-lg hidden sm:inline">Freelance Connect</span>
          </RouterLink>
        </div>

        <!-- Desktop Menu (Empty - Navigation moved to Sidebar) -->

        <!-- Right Side -->
        <div v-if="authStore.isLoggedIn" class="flex items-center gap-4">
          <!-- Notifications -->
          <button class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>

          <!-- User Menu -->
          <div class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <img
                :src="avatarUrl"
                class="w-8 h-8 rounded-full object-cover"
                @error="onAvatarError"
                alt="Avatar"
              />
              <svg
                :class="['w-4 h-4 transition-transform', showUserMenu && 'rotate-180']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <Transition name="fade">
              <div
                v-if="showUserMenu"
                @click.outside="showUserMenu = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
              >
                <div class="px-4 py-2 border-b border-gray-200">
                  <p class="font-semibold">{{ authStore.user?.name }}</p>
                  <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
                </div>
                <button
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 transition-colors"
                >
                  Logout
                </button>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Right Side for Guests -->
        <div v-else class="flex items-center gap-4">
          <!-- Mobile Menu Button -->
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="md:hidden p-2 hover:bg-gray-100 rounded transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

          <!-- Desktop Auth Links -->
          <div class="hidden md:flex items-center gap-4">
            <RouterLink to="/login" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
              Login
            </RouterLink>
            <RouterLink to="/register" class="btn-primary text-sm px-4 py-2">
              Sign Up
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <Transition name="slide">
        <div v-if="showMobileMenu && !authStore.isLoggedIn" class="md:hidden pb-4 border-t border-gray-200">
          <RouterLink to="/login" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
            Login
          </RouterLink>
          <RouterLink to="/register" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
            Register
          </RouterLink>
        </div>
      </Transition>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const showUserMenu = ref(false)
const showMobileMenu = ref(false)

defineEmits(['toggle-sidebar'])

const avatarUrl = computed(() => {
  return authStore.user?.avatar || '/img/avatar.png'
})

function onAvatarError(e: Event) {
  (e.target as HTMLImageElement).src = '/img/avatar.png'
}

async function handleLogout() {
  await authStore.logout()
  showUserMenu.value = false
  router.push('/login')
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: max-height 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  overflow: hidden;
}
</style>
