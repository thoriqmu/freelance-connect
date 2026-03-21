<template>
  <div class="fixed bottom-4 right-4 z-50 flex flex-col items-end">
    <!-- Chat Window -->
    <Transition name="slide-up">
      <div v-if="isOpen" class="bg-white rounded-t-xl rounded-bl-xl shadow-2xl border border-gray-200 w-80 sm:w-96 flex flex-col mb-4 overflow-hidden" style="height: 500px; max-height: calc(100vh - 120px);">
        <!-- Header -->
        <div class="bg-blue-600 text-white p-3 flex justify-between items-center shadow-sm">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-green-400"></div>
            <h3 class="font-semibold text-sm">{{ receiverName }}</h3>
          </div>
          <button @click="isOpen = false" class="text-white hover:text-gray-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        
        <!-- Messages Area -->
        <div class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-3" ref="messagesContainer">
          <div v-if="isLoading" class="flex justify-center py-4">
             <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          </div>
          
          <template v-else>
            <div v-if="messages.length === 0" class="text-center text-gray-500 text-sm py-4">
              No messages yet. Start the conversation!
            </div>
            
            <div 
              v-for="msg in messages" 
              :key="msg.id"
              :class="['flex', msg.sender_id === currentUser.id ? 'justify-end' : 'justify-start']"
            >
              <div 
                :class="[
                  'max-w-[80%] rounded-lg px-4 py-2 text-sm shadow-sm',
                  msg.sender_id === currentUser.id ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white border border-gray-200 rounded-bl-none text-gray-800'
                ]"
              >
                <p>{{ msg.content }}</p>
                <span :class="['text-[10px] block mt-1', msg.sender_id === currentUser.id ? 'text-blue-200' : 'text-gray-400']">
                  {{ formatTime(msg.created_at) }}
                </span>
              </div>
            </div>
          </template>
        </div>
        
        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-200">
          <form @submit.prevent="sendMessage" class="flex items-center gap-2">
            <input 
              v-model="newMessage"
              type="text" 
              placeholder="Type a message..." 
              class="flex-1 bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 rounded-full py-2 px-4 text-sm focus:ring-0 outline-none transition-colors"
              :disabled="isSending"
            />
            <button 
              type="submit" 
              class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors disabled:opacity-50"
              :disabled="!newMessage.trim() || isSending"
            >
              <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </button>
          </form>
        </div>
      </div>
    </Transition>
    
    <!-- Floating Button -->
    <button 
      v-show="!isOpen"
      @click="openChat" 
      class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all flex items-center justify-center transform hover:scale-105"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { messageService } from '@/services/messageService'
import echo from '@/utils/echo'

const props = defineProps<{
  projectId: number
  currentUser: { id: number; [key: string]: any }
  receiverName?: string
}>()

const isOpen = ref(false)
const isLoading = ref(false)
const isSending = ref(false)
const messages = ref<any[]>([])
const newMessage = ref('')
const messagesContainer = ref<HTMLElement | null>(null)

let echoChannel: any = null

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const loadMessages = async () => {
  isLoading.value = true
  try {
    const res = await messageService.getMessages(props.projectId)
    messages.value = res.data?.data || []
    scrollToBottom()
  } catch (error) {
    console.error('Failed to load messages', error)
  } finally {
    isLoading.value = false
  }
}

const openChat = () => {
  isOpen.value = true
  if (messages.value.length === 0) {
    loadMessages()
  } else {
    scrollToBottom()
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return
  
  const content = newMessage.value.trim()
  newMessage.value = ''
  isSending.value = true
  
  // Optimistic UI update
  const fakeId = Date.now()
  messages.value.push({
    id: fakeId,
    sender_id: props.currentUser.id,
    content: content,
    created_at: new Date().toISOString()
  })
  scrollToBottom()
  
  try {
    await messageService.sendMessage(props.projectId, content)
    // Server will respond via websocket or we can reload
  } catch (error) {
    console.error('Failed to send message', error)
    // Remove fake message on error
    messages.value = messages.value.filter(m => m.id !== fakeId)
  } finally {
    isSending.value = false
  }
}

const setupWebSocket = () => {
  if (echo) {
    echoChannel = echo.private(`project.${props.projectId}`)
      .listen('MessageSent', (e: any) => {
        // Find existing fake message to avoid duplicates
        const existingFakeIndex = messages.value.findIndex(m => m.content === e.message.content && m.sender_id === e.message.sender_id && !m.updated_at)
        if (existingFakeIndex >= 0) {
           messages.value[existingFakeIndex] = e.message
        } else {
           messages.value.push(e.message)
        }
        scrollToBottom()
      })
  }
}

watch(isOpen, (val) => {
  if (val) {
    scrollToBottom()
  }
})

onMounted(() => {
  setupWebSocket()
})

onUnmounted(() => {
  if (echo && echoChannel) {
    echo.leave(`project.${props.projectId}`)
  }
})

const formatTime = (dateStr: string) => {
  const d = new Date(dateStr)
  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`
}
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: bottom right;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}
</style>
