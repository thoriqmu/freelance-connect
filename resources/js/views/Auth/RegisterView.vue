<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-3 mb-4">
          <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-lg">
            <span class="text-blue-600 font-bold">FC</span>
          </div>
          <h1 class="text-3xl font-bold text-white">Freelance Connect</h1>
        </div>
        <p class="text-blue-100">Create account and start earning or hiring today</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-xl shadow-2xl p-8">
        <!-- Error Alert -->
        <BaseAlert
          v-if="error"
          type="error"
          :message="error"
          closeable
          @close="error = null"
          class="mb-6"
        />

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="space-y-5">
          <!-- Name -->
          <BaseInput
            v-model="form.name"
            type="text"
            label="Full Name"
            placeholder="Enter your full name"
            :error="errors.name?.[0]"
            required
          />

          <!-- Email -->
          <BaseInput
            v-model="form.email"
            type="email"
            label="Email"
            placeholder="Enter your email"
            :error="errors.email?.[0]"
            required
          />

          <!-- Password -->
          <BaseInput
            v-model="form.password"
            type="password"
            label="Password"
            placeholder="Create a strong password"
            :hint="'At least 8 characters with uppercase and numbers'"
            :error="errors.password?.[0]"
            required
          />

          <!-- Password Confirmation -->
          <BaseInput
            v-model="form.password_confirmation"
            type="password"
            label="Confirm Password"
            placeholder="Confirm your password"
            :error="errors.password_confirmation?.[0]"
            required
          />

          <!-- Role -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700 block">I want to:</label>
            <div class="grid grid-cols-2 gap-3">
              <label class="relative flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-colors"
                :class="form.role === 'client' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
              >
                <input
                  v-model="form.role"
                  type="radio"
                  value="client"
                  class="w-4 h-4 text-blue-600 focus:ring-0"
                />
                <div>
                  <p class="font-semibold text-sm">Hire</p>
                  <p class="text-xs text-gray-500">Post projects</p>
                </div>
              </label>
              <label class="relative flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-colors"
                :class="form.role === 'freelancer' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
              >
                <input
                  v-model="form.role"
                  type="radio"
                  value="freelancer"
                  class="w-4 h-4 text-blue-600 focus:ring-0"
                />
                <div>
                  <p class="font-semibold text-sm">Work</p>
                  <p class="text-xs text-gray-500">Find projects</p>
                </div>
              </label>
            </div>
            <span v-if="errors.role?.[0]" class="text-sm text-red-500">{{ errors.role[0] }}</span>
          </div>

          <!-- Terms -->
          <label class="flex items-start gap-2 cursor-pointer">
            <input
              v-model="form.agreed_to_terms"
              type="checkbox"
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-0 mt-1"
            />
            <span class="text-xs text-gray-600">
              I agree to the
              <a href="#" class="text-blue-600 hover:underline">Terms of Service</a>
              and
              <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>
            </span>
          </label>
          <span v-if="errors.agreed_to_terms?.[0]" class="text-sm text-red-500 block">{{ errors.agreed_to_terms[0] }}</span>

          <!-- Submit Button -->
          <BaseButton
            label="Create Account"
            type="submit"
            :is-loading="isLoading"
            :disabled="isLoading"
            class="w-full"
          />
        </form>

        <!-- Sign In Link -->
        <p class="text-center text-sm text-gray-600 mt-6">
          Already have an account?
          <RouterLink to="/login" class="text-blue-600 hover:underline font-semibold">
            Sign in here
          </RouterLink>
        </p>
      </div>

      <!-- Footer -->
      <p class="text-center text-blue-100 text-xs mt-8">
        By creating an account, you agree to our Terms of Service and Privacy Policy
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'

const router = useRouter()
const authStore = useAuthStore()

const isLoading = ref(false)
const error = ref<string | null>(null)
const errors = reactive<Record<string, string[]>>({})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'freelancer' as 'client' | 'freelancer',
  agreed_to_terms: false,
})

async function handleRegister() {
  isLoading.value = true
  error.value = null
  Object.keys(errors).forEach((key) => delete errors[key])

  try {
    await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      role: form.role,
    })

    // Redirect based on role
    if (authStore.isClient) {
      await router.push('/client/dashboard')
    } else {
      await router.push('/freelancer/jobs')
    }
  } catch (err: any) {
    if (err.response?.status === 422) {
      Object.assign(errors, err.response.data.errors || {})
    } else {
      error.value = err.response?.data?.message || 'Registration failed. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
