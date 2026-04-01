<template>
  <div class="max-w-4xl mx-auto px-4 py-8 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <p class="text-gray-500 mt-1">Manage your alerts and activity.</p>
      </div>
      <BaseButton 
        v-if="notifications.length > 0"
        label="Mark All as Read" 
        variant="outline" 
        @click="markAllAsRead"
        :disabled="isLoading"
      />
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <template v-if="isLoading && notifications.length === 0">
        <div v-for="i in 5" :key="i" class="p-6 border-b border-gray-100 flex gap-4 animate-pulse">
          <div class="w-12 h-12 bg-gray-100 rounded-full"></div>
          <div class="flex-1 space-y-3">
            <div class="h-4 bg-gray-100 rounded w-3/4"></div>
            <div class="h-3 bg-gray-50 rounded w-1/4"></div>
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
        
        <!-- Pagination -->
        <div v-if="totalPages > 1" class="p-6 bg-gray-50 border-t border-gray-100 flex justify-center">
          <BasePagination 
            :current-page="currentPage" 
            :last-page="totalPages" 
            @update:current-page="changePage" 
          />
        </div>
      </template>

      <div v-else class="py-20 text-center">
        <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
          <BellSlashIcon class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">No notifications yet</h3>
        <p class="text-gray-500">We'll notify you when something important happens.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { BellSlashIcon } from '@heroicons/vue/24/outline'
import { notificationService } from '@/services/notificationService'
import NotificationCard from '@/components/shared/NotificationCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BasePagination from '@/components/ui/BasePagination.vue'

const router = useRouter()
const notifications = ref<any[]>([])
const isLoading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)

const fetchNotifications = async () => {
  isLoading.value = true
  try {
    const res = await notificationService.getUserNotifications({ page: currentPage.value })
    notifications.value = res.data?.data || []
    if (res.data?.meta) {
      totalPages.value = res.data.meta.last_page || 1
    }
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
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const handleNotificationClick = async (notification: any) => {
  if (!notification.read_at) {
    try {
      await notificationService.markAsRead(notification.id)
      notification.read_at = new Date().toISOString()
    } catch (error) {
      console.error('Failed to mark as read:', error)
    }
  }

  // Navigate based on data
  if (notification.data?.project_id) {
    router.push(`/client/projects/${notification.data.project_id}`)
  }
}

const changePage = (page: number) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchNotifications()
}

onMounted(() => {
  fetchNotifications()
})
</script>
