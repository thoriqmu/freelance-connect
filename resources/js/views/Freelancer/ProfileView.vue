<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12">
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

    <div v-else-if="user" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Base Info Column -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm text-center space-y-4">
          <div class="relative w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-gray-50 shadow-inner group">
            <img :src="avatarPreview || getAvatarUrl(user.avatar) || '/img/avatar.png'" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-75" alt="Avatar" @error="handleAvatarError" />
            <label class="absolute inset-0 flex items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity duration-300">
              <span class="text-xs font-semibold">Change</span>
              <input type="file" class="hidden" accept="image/jpeg, image/png, image/webp" @change="handleAvatarChange" />
            </label>
          </div>
          
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ user.name }}</h2>
            <p class="text-sm text-gray-500 uppercase tracking-widest">{{ user.role }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4 py-2 border-y border-gray-50">
            <div class="text-center">
              <p class="text-lg font-bold text-blue-600">{{ Number(user.reviews_as_reviewee_avg_rating || 0).toFixed(1) }}</p>
              <p class="text-[10px] text-gray-400 uppercase font-bold">Avg Rating</p>
            </div>
            <div class="text-center border-l border-gray-50">
              <p class="text-lg font-bold text-gray-900">{{ user.completed_projects_count || 0 }}</p>
              <p class="text-[10px] text-gray-400 uppercase font-bold">Projects</p>
            </div>
          </div>

          <BaseBadge :type="availabilityBadge" class="w-full justify-center">{{ formatAvailability(user.freelancer_profile?.availability) }}</BaseBadge>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
          <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 mb-4">Edit General Info</h3>
          <form @submit.prevent="saveBaseProfile" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input type="text" v-model="baseForm.name" class="input-base" required />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" :value="user.email" disabled class="input-base bg-gray-50 text-gray-500 cursor-not-allowed" />
            </div>

            <div class="pt-2">
              <BaseButton type="submit" variant="primary" label="Save General Info" :loading="isSavingBase" class="w-full justify-center" />
            </div>
          </form>
        </div>
      </div>

      <!-- Freelancer Details Column -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
          <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">Professional Details</h2>
          
          <form @submit.prevent="saveFreelancerProfile" class="space-y-5">
            <!-- Hourly Rate and Availability -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (USD)</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">$</span>
                  <input type="number" step="0.01" min="0" v-model="freelancerForm.hourly_rate" class="input-base pl-8" placeholder="e.g. 25.00" />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                <select v-model="freelancerForm.availability" class="input-base">
                  <option value="available">Available for Work</option>
                  <option value="unavailable">Not Available</option>
                </select>
              </div>
            </div>

            <!-- Portfolio URL -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Portfolio or Website URL</label>
              <input type="url" v-model="freelancerForm.portfolio_url" class="input-base" placeholder="https://dribbble.com/yourname" />
            </div>

            <!-- Bio -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Professional Bio</label>
              <textarea v-model="freelancerForm.bio" rows="4" class="input-base" placeholder="Tell clients about your experience, background, and what you can do..."></textarea>
            </div>

            <!-- Skills Autocomplete -->
            <div class="relative pb-10">
              <label class="block text-sm font-bold text-gray-700 mb-1">Skills (Max 15)</label>
              <p class="text-xs text-gray-500 mb-2">Search and select skills. If not found, you can type your own and press Enter.</p>
              
              <div class="flex flex-wrap gap-2 p-2 px-3 bg-white border border-gray-300 rounded-lg min-h-[46px] focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-shadow">
                <span v-for="(skill, i) in freelancerForm.skills" :key="i" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 border border-blue-200 shadow-sm">
                  {{ skill }}
                  <button type="button" @click="removeSkill(i)" class="shrink-0 h-4 w-4 rounded-full inline-flex items-center justify-center text-blue-400 hover:bg-blue-300 hover:text-blue-800 focus:outline-none transition-colors">
                    <span class="sr-only">Remove skill</span>
                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                  </button>
                </span>
                
                <input 
                  type="text" 
                  v-model="skillQuery" 
                  @input="searchSkillsDebounced"
                  @keydown.down.prevent="moveSkillSelection(1)"
                  @keydown.up.prevent="moveSkillSelection(-1)"
                  @keydown.enter.prevent="selectCurrentSkill"
                  @focus="isSkillDropdownOpen = true"
                  @blur="hideDropdownDelayed"
                  placeholder="Type a skill..." 
                  class="flex-1 outline-none bg-transparent min-w-[150px] text-sm text-gray-700 mt-1"
                  :disabled="freelancerForm.skills.length >= 15"
                />
              </div>

              <!-- Dropdown autocomplete -->
              <ul v-if="skillResults.length > 0 && isSkillDropdownOpen" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                <li 
                  v-for="(res, idx) in skillResults" 
                  :key="res.id" 
                  @mousedown.prevent="addSkill(res.title)"
                  @mouseenter="skillSelectedIndex = idx"
                  :class="['px-4 py-2 cursor-pointer transition-colors text-sm', skillSelectedIndex === idx ? 'bg-blue-600 text-white font-semibold' : 'text-gray-700 hover:bg-gray-50']"
                >
                  {{ res.title }}
                </li>
              </ul>
              <div v-if="skillQuery.trim().length > 0 && skillResults.length === 0 && isSkillDropdownOpen && !isSearchingSkills" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-500 text-center">
                No matching skills found. Press <strong>Enter</strong> to add "{{ skillQuery }}".
              </div>
              <div v-if="freelancerForm.skills.length >= 15" class="mt-2 text-xs text-amber-600 font-medium">
                You have reached the maximum of 15 skills.
              </div>
            </div>

            <div class="pt-2 flex justify-end">
               <BaseButton type="submit" variant="primary" label="Save Professional Details" :loading="isSavingFreelancer" />
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { profileService } from '@/services/profileService'
import { skillService, type Skill } from '@/services/skillService'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

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

const freelancerForm = ref({
  skills: [] as string[],
  hourly_rate: null as number | null,
  portfolio_url: '',
  bio: '',
  availability: 'AVAILABLE'
})
const isSavingFreelancer = ref(false)

// Skills Autocomplete state
const skillQuery = ref('')
const skillResults = ref<Skill[]>([])
const isSkillDropdownOpen = ref(false)
const skillSelectedIndex = ref(0)
const isSearchingSkills = ref(false)
let searchTimeout: ReturnType<typeof setTimeout>

const fetchProfile = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await profileService.getMyProfile()
    user.value = res.data
    
    // Populate base
    baseForm.value.name = user.value.name
    
    // Populate freelancer
    if (user.value.freelancer_profile) {
      freelancerForm.value.skills = user.value.freelancer_profile.skills || []
      freelancerForm.value.hourly_rate = user.value.freelancer_profile.hourly_rate
      freelancerForm.value.portfolio_url = user.value.freelancer_profile.portfolio_url || ''
      freelancerForm.value.bio = user.value.freelancer_profile.bio || ''
      freelancerForm.value.availability = user.value.freelancer_profile.availability || 'AVAILABLE'
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
    await authStore.fetchMe() 
    await fetchProfile()      
    avatarFile.value = null
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update general profile.'
  } finally {
    isSavingBase.value = false
  }
}

const saveFreelancerProfile = async () => {
  isSavingFreelancer.value = true
  error.value = ''
  successMsg.value = ''
  try {
    await profileService.updateFreelancerProfile({
      skills: freelancerForm.value.skills,
      hourly_rate: freelancerForm.value.hourly_rate,
      bio: freelancerForm.value.bio,
      portfolio_url: freelancerForm.value.portfolio_url,
      availability: freelancerForm.value.availability
    })
    successMsg.value = 'Professional details updated successfully.'
    await fetchProfile()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update professional details.'
  } finally {
    isSavingFreelancer.value = false
  }
}

// ---- Autocomplete Logic ----
const searchSkillsDebounced = () => {
  isSkillDropdownOpen.value = true
  clearTimeout(searchTimeout)
  if (!skillQuery.value.trim()) {
    skillResults.value = []
    return
  }
  isSearchingSkills.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const res = await skillService.searchSkills(skillQuery.value)
      // Filter out already selected skills
      skillResults.value = res.data.filter((s: Skill) => !freelancerForm.value.skills.includes(s.title))
      skillSelectedIndex.value = 0
    } catch(err) {
      console.error(err)
    } finally {
      isSearchingSkills.value = false
    }
  }, 300)
}

const moveSkillSelection = (delta: number) => {
  if (skillResults.value.length === 0) return
  skillSelectedIndex.value = (skillSelectedIndex.value + delta + skillResults.value.length) % skillResults.value.length
}

const selectCurrentSkill = () => {
  if (isSkillDropdownOpen.value) {
    if (skillResults.value.length > 0) {
      addSkill(skillResults.value[skillSelectedIndex.value].title)
    } else if (skillQuery.value.trim().length > 0) {
      addSkill(skillQuery.value.trim())
    }
  }
}

const addSkill = (title: string) => {
  if (freelancerForm.value.skills.length >= 15) return
  
  if (!freelancerForm.value.skills.includes(title)) {
    freelancerForm.value.skills.push(title)
  }
  skillQuery.value = ''
  skillResults.value = []
  isSkillDropdownOpen.value = false
}

const removeSkill = (index: number) => {
  freelancerForm.value.skills.splice(index, 1)
}

const hideDropdownDelayed = () => {
  setTimeout(() => {
    isSkillDropdownOpen.value = false
  }, 200)
}

const availabilityBadge = computed(() => {
  const av = user.value?.freelancer_profile?.availability
  if (av === 'available') return 'success'
  if (av === 'unavailable') return 'danger'
})

const formatAvailability = (status?: string) => {
  if (!status) return 'Unknown'
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

onMounted(() => {
  fetchProfile()
})
</script>
