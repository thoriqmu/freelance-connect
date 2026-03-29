<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
    </div>

    <BaseAlert v-if="error" type="error" :message="error" />
    <BaseAlert v-if="successMsg" type="success" :message="successMsg" />

    <div v-if="isLoading" class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-pulse space-y-6">
      <div class="flex items-center gap-6">
        <div class="w-24 h-24 rounded-full bg-gray-200"></div>
        <div class="space-y-3 flex-1">
          <div class="h-6 bg-gray-200 rounded w-1/3"></div>
          <div class="h-4 bg-gray-200 rounded w-1/4"></div>
        </div>
      </div>
    </div>

    <div v-else-if="user" class="space-y-6">
      <!-- General Info -->
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">General Information</h2>
        
        <form @submit.prevent="saveBaseProfile" class="flex flex-col md:flex-row gap-8">
          <!-- Avatar Section -->
          <div class="flex flex-col items-center gap-3 shrink-0">
            <div class="relative w-32 h-32 rounded-full overflow-hidden border-4 border-gray-50 shadow-inner group">
              <img :src="avatarPreview || getAvatarUrl(user.avatar) || '/img/avatar.png'" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-75" alt="Avatar" @error="handleAvatarError" />
              <label class="absolute inset-0 flex items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity duration-300">
                <span class="text-xs font-semibold">Change</span>
                <input type="file" class="hidden" accept="image/jpeg, image/png, image/webp" @change="handleAvatarChange" />
              </label>
            </div>
            <p class="text-xs text-gray-500 text-center max-w-[150px]">Allowed: JPG, PNG, WEBP. Max size: 2MB.</p>
          </div>

          <!-- Base Form Fields -->
          <div class="flex-1 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input type="text" v-model="baseForm.name" class="input-base" required />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address (Read-only)</label>
              <input type="email" :value="user.email" disabled class="input-base bg-gray-50 text-gray-500 cursor-not-allowed" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <input type="text" :value="user.role" disabled class="input-base bg-gray-50 text-gray-500 cursor-not-allowed uppercase" />
            </div>

            <div class="pt-2 flex justify-end">
              <BaseButton type="submit" variant="primary" label="Save General Info" :loading="isSavingBase" />
            </div>
          </div>
        </form>
      </div>

      <!-- Client Profile -->
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">Client Details</h2>
        
        <form @submit.prevent="saveClientProfile" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
              <input type="text" v-model="clientForm.company_name" class="input-base" placeholder="e.g. Acme Corporation" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Field</label>
              <input type="text" v-model="clientForm.company_field" class="input-base" placeholder="e.g. Technology, Retail, Healthcare" />
            </div>
          </div>

          <div class="pt-4 flex justify-end">
             <BaseButton type="submit" variant="primary" label="Save Client Details" :loading="isSavingClient" />
          </div>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { profileService } from '@/services/profileService'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'

const authStore = useAuthStore()
const user = ref<any>(null)
const isLoading = ref(true)
const error = ref('')
const successMsg = ref('')

// Focus states and data
const baseForm = ref({
  name: '',
})
const avatarFile = ref<File | null>(null)
const avatarPreview = ref<string | null>(null)
const isSavingBase = ref(false)

const clientForm = ref({
  company_name: '',
  company_field: '',
})
const isSavingClient = ref(false)

const fetchProfile = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await profileService.getMyProfile()
    user.value = res.data
    
    // Populate base
    baseForm.value.name = user.value.name
    
    // Populate client
    if (user.value.client_profile) {
      clientForm.value.company_name = user.value.client_profile.company_name || ''
      clientForm.value.company_field = user.value.client_profile.company_field || ''
    }
  } catch (err: any) {
    error.value = 'Failed to load profile details.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const handleAvatarChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    // 2MB validation
    if (file.size > 2 * 1024 * 1024) {
      alert('File too large. Max size is 2MB.')
      return
    }
    avatarFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

const getAvatarUrl = (path: string) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '')
  return `${baseUrl}/storage/${path}` || '/img/avatar.png'
}

const handleAvatarError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = '/img/avatar.png'
}

const saveBaseProfile = async () => {
  isSavingBase.value = true
  error.value = ''
  successMsg.value = ''
  try {
    await profileService.updateBaseProfile({
      name: baseForm.value.name,
      avatar: avatarFile.value || undefined
    })
    successMsg.value = 'General profile updated successfully.'
    await authStore.fetchMe() // refresh global state
    await fetchProfile()      // refresh local
    
    // Reset avatar file state
    avatarFile.value = null
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update general profile.'
  } finally {
    isSavingBase.value = false
  }
}

const saveClientProfile = async () => {
  isSavingClient.value = true
  error.value = ''
  successMsg.value = ''
  try {
    await profileService.updateClientProfile({
      company_name: clientForm.value.company_name,
      company_field: clientForm.value.company_field
    })
    successMsg.value = 'Client details updated successfully.'
    await fetchProfile()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update client details.'
  } finally {
    isSavingClient.value = false
  }
}

onMounted(() => {
  fetchProfile()
})
</script>
