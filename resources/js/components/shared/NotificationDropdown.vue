<template>
  <div class="relative" v-click-outside="closeDropdown">
    <button 
      @click="toggleDropdown"
      class="p-2 text-gray-500 hover:text-blue-600 rounded-full hover:bg-blue-50 transition-colors relative"
    >
      <BellIcon class="w-6 h-6" />
      <span 
        v-if="unreadCount > 0"
        class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"
      ></span>
    </button>

    <!-- Dropdown -->
    <div 
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden"
    >
      <div class="p-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-bold text-gray-900">Notifications</h3>
        <button 
          @click="markAllAsRead"
          class="text-xs text-blue-600 hover:underline"
          v-if="unreadCount > 0"
        >
          Mark all as read
        </button>
      </div>

      <div class="max-h-[350px] overflow-y-auto">
        <template v-if="isLoading">
          <div v-for="i in 3" :key="i" class="p-4 border-b border-gray-50 flex gap-4 animate-pulse">
            <div class="w-10 h-10 bg-gray-100 rounded-full"></div>
            <div class="flex-1 space-y-2">
              <div class="h-3 bg-gray-100 rounded w-full"></div>
              <div class="h-2 bg-gray-50 rounded w-1/3"></div>
            </div>
          </div>
        </template>
        
        <template v-else-if="notifications.length > 0">
          <NotificationCard 
            v-for="notification in notifications" 
            :key="notification.id" 
            :notification="notification"
            @click="handleNotificationClick"
          />
        </template>

        <div v-else class="p-8 text-center">
          <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
            <BellSlashIcon class="w-6 h-6 text-gray-300" />
          </div>
          <p class="text-sm text-gray-400">No notifications yet</p>
        </div>
      </div>

      <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
        <RouterLink 
          to="/notifications" 
          class="text-sm font-semibold text-blue-600 hover:text-blue-700"
          @click="isOpen = false"
        >
          Show All Notifications
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { BellIcon, BellSlashIcon } from '@heroicons/vue/24/outline'
import { notificationService } from '@/services/notificationService'
import NotificationCard from './NotificationCard.vue'

const router = useRouter()
const isOpen = ref(false)
const isLoading = ref(false)
const notifications = ref<any[]>([])
const unreadCount = ref(0)
let pollInterval: any = null

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchNotifications()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const fetchNotifications = async () => {
  if (isLoading.value) return
  isLoading.value = true
  try {
    const res = await notificationService.getUserNotifications({ per_page: 5 })
    notifications.value = res.data?.data || []
    
    // We would ideally have a separate endpoint for unread count
    // For now we'll calculate from the latest or we could add a specific count endpoint
    unreadCount.value = notifications.value.filter(n => !n.read_at).length
    // Note: Since this only gets 5, the true unreadCount might be higher.
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    isLoading.value = false
  }
}

const markAllAsRead = async () => {
  try {
    await notificationService.markAllAsRead()
    notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }))
    unreadCount.value = 0
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const handleNotificationClick = async (notification: any) => {
  if (!notification.read_at) {
    try {
      await notificationService.markAsRead(notification.id)
      notification.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Failed to mark as read:', error)
    }
  }

  isOpen.value = false

  // Navigate based on data
  if (notification.data?.project_id) {
    router.push(`/client/projects/${notification.data.project_id}`)
  } else if (notification.type === 'new_message' && notification.data?.project_id) {
     // Go to chat tab if possible
     router.push(`/client/projects/${notification.data.project_id}?tab=messages`)
  }
}

// Simple click outside directive implementation
const vClickOutside = {
  mounted(el: any, binding: any) {
    el.clickOutsideEvent = (event: Event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el: any) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

onMounted(() => {
  fetchNotifications()
  // Poll every 1 minute for basic real-time
  pollInterval = setInterval(fetchNotifications, 60000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>
