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
        <p class="text-blue-100">Welcome back! Please login to your account</p>
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
        <form @submit.prevent="handleLogin" class="space-y-5">
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
            placeholder="Enter your password"
            :error="errors.password?.[0]"
            required
          />

          <!-- Remember Me -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.remember"
              type="checkbox"
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-0"
            />
            <span class="text-sm text-gray-700">Remember me for 30 days</span>
          </label>

          <!-- Submit Button -->
          <BaseButton
            label="Login"
            type="submit"
            :is-loading="isLoading"
            :disabled="isLoading"
            class="w-full"
          />
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Or continue with</span>
          </div>
        </div>

        <!-- Demo Accounts -->
        <div class="grid grid-cols-2 gap-3 mb-6">
          <button
            @click="loginAsClient"
            :disabled="isLoading"
            type="button"
            class="p-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors disabled:opacity-50"
          >
            <p class="font-semibold text-sm text-gray-700">Client</p>
            <p class="text-xs text-gray-500">Demo Account</p>
          </button>
          <button
            @click="loginAsFreelancer"
            :disabled="isLoading"
            type="button"
            class="p-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors disabled:opacity-50"
          >
            <p class="font-semibold text-sm text-gray-700">Freelancer</p>
            <p class="text-xs text-gray-500">Demo Account</p>
          </button>
        </div>

        <!-- Sign Up Link -->
        <p class="text-center text-sm text-gray-600">
          Don't have an account?
          <RouterLink to="/register" class="text-blue-600 hover:underline font-semibold">
            Sign up here
          </RouterLink>
        </p>
      </div>

      <!-- Footer -->
      <p class="text-center text-blue-100 text-xs mt-8">
        By logging in, you agree to our Terms of Service and Privacy Policy
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
  email: '',
  password: '',
  remember: false,
})

async function handleLogin() {
  isLoading.value = true
  error.value = null
  Object.keys(errors).forEach((key) => delete errors[key])

  try {
    await authStore.login(form.email, form.password)

    // Redirect based on role
    if (authStore.isClient) {
      await router.push('/client/dashboard')
    } else if (authStore.isFreelancer) {
      await router.push('/freelancer/jobs')
    }
  } catch (err: any) {
    if (err.response?.status === 422) {
      Object.assign(errors, err.response.data.errors || {})
    } else {
      error.value = err.response?.data?.message || 'Login failed. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

async function loginAsClient() {
  form.email = 'jane.doe@example.com'
  form.password = 'password123'
  await handleLogin()
}

async function loginAsFreelancer() {
  form.email = 'eva.turner@example.com'
  form.password = 'password123'
  await handleLogin()
}
</script>
