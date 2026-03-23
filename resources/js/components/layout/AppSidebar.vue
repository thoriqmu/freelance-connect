<template>
  <aside
    :class="[
      'fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white shadow-md transition-transform duration-300 z-30 overflow-y-auto',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      'md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:translate-x-0 md:self-start md:shadow-none md:border-r md:border-gray-200',
    ]"
  >
    <nav class="p-4 space-y-2">
      <template v-if="authStore.isClient">
        <NavLink
          to="/client/dashboard"
          icon="dashboard"
          label="Dashboard"
          @click="emitClose"
        />
        <NavLink
          to="/client/projects/create"
          icon="plus"
          label="Create Project"
          @click="emitClose"
        />
        <NavLink
          to="/client/projects"
          icon="briefcase"
          label="My Projects"
          @click="emitClose"
        />
        <NavLink
          to="/client/profile"
          icon="user"
          label="Profile"
          @click="emitClose"
        />
      </template>

      <template v-else-if="authStore.isFreelancer">
        <NavLink
          to="/freelancer/jobs"
          icon="search"
          label="Browse Jobs"
          @click="emitClose"
        />
        <NavLink
          to="/freelancer/my-proposals"
          icon="briefcase"
          label="My Proposals"
          @click="emitClose"
        />
        <NavLink
          to="/freelancer/saved-jobs"
          icon="bookmark"
          label="Saved Jobs"
          @click="emitClose"
        />
        <NavLink
          to="/freelancer/my-jobs"
          icon="briefcase"
          label="My Jobs"
          @click="emitClose"
        />
        <NavLink
          to="/freelancer/earnings"
          icon="wallet"
          label="Earnings"
          @click="emitClose"
        />
        <NavLink
          to="/freelancer/profile"
          icon="user"
          label="Profile"
          @click="emitClose"
        />
      </template>
    </nav>
  </aside>

  <!-- Mobile Overlay -->
  <Transition name="fade">
    <div
      v-if="isOpen && isMobile"
      @click="emitClose"
      class="fixed inset-0 top-16 bg-gray-900/50 md:hidden z-20"
    ></div>
  </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import NavLink from './NavLink.vue'

interface Props {
  isOpen?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
})

const emit = defineEmits<{
  close: []
}>()

const authStore = useAuthStore()
const isMobile = computed(() => window.innerWidth < 768)

const emitClose = () => {
  if (isMobile.value) {
    emit('close')
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
