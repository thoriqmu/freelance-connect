<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 pb-12">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Project</h1>
        <p class="text-gray-500 mt-1">Update the details of your open project.</p>
      </div>
    </div>

    <!-- BaseAlert instance for success or error -->
    <BaseAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="alert.show = false"
      class="mb-6"
    />

    <!-- Loading State -->
    <div v-if="isLoadingContext" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-6 animate-pulse">
      <div class="space-y-2">
        <div class="h-4 bg-gray-200 rounded w-24"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
          <div class="h-4 bg-gray-200 rounded w-28"></div>
          <div class="h-10 bg-gray-200 rounded"></div>
        </div>
        <div class="space-y-2">
          <div class="h-4 bg-gray-200 rounded w-32"></div>
          <div class="h-10 bg-gray-200 rounded"></div>
        </div>
      </div>
      <div class="space-y-2">
        <div class="h-4 bg-gray-200 rounded w-24"></div>
        <div class="h-10 bg-gray-100 rounded-t-lg border border-gray-200"></div>
        <div class="h-64 bg-gray-200 rounded-b-lg"></div>
      </div>
      <div class="pt-6 border-t border-gray-200 flex justify-end gap-3">
        <div class="h-10 bg-gray-200 rounded-lg w-24"></div>
        <div class="h-10 bg-gray-200 rounded-lg w-32"></div>
      </div>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <!-- Project Details -->
      <div class="space-y-6">
        <BaseInput
          v-model="form.title"
          label="Project Title"
          type="text"
          placeholder="e.g. Build an E-commerce Website"
          required
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <BaseInput
            v-model="form.budget"
            label="Budget (USD)"
            type="number"
            min="1"
            placeholder="e.g. 1500"
            required
          />
          <BaseInput
            v-model="form.timeline"
            label="Timeline (Weeks)"
            type="number"
            min="1"
            placeholder="e.g. 4"
            required
          />
        </div>

        <div>
           <label class="block text-sm font-medium text-gray-700 mb-1">
             Description <span class="text-red-500">*</span>
           </label>
          <div class="border border-gray-300 rounded-lg overflow-hidden bg-white min-h-[16rem] flex flex-col">
            <QuillEditor
              v-model:content="form.description"
              contentType="html"
              theme="snow"
              class="flex-1"
            />
          </div>
          <p v-if="descriptionError" class="text-red-500 text-sm mt-1">{{ descriptionError }}</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="pt-6 border-t border-gray-200 flex flex-col-reverse sm:flex-row justify-end gap-3">
        <BaseButton
          type="button"
          variant="outline"
          label="Cancel"
          @click="$router.push(`/client/projects/${projectId}`)"
          :disabled="isSubmitting"
          class="w-full sm:w-auto"
        />
        <BaseButton
          type="submit"
          variant="primary"
          :label="isSubmitting ? 'Saving Changes...' : 'Save Changes'"
          :disabled="isSubmitting"
          class="w-full sm:w-auto"
        />
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import { projectService } from '@/services/projectService'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const router = useRouter()
const route = useRoute()
const projectId = Number(route.params.id)

const form = reactive({
  title: '',
  description: '',
  budget: '',
  timeline: '',
})

const isLoadingContext = ref(true)
const isSubmitting = ref(false)
const descriptionError = ref('')

const alert = reactive({
  show: false,
  type: 'info' as 'success' | 'error' | 'warning' | 'info',
  message: '',
})

const fetchProject = async () => {
  try {
    const res = await projectService.getProject(projectId)
    const project = res.data?.data || res.data
    
    // Proyek hanya boleh diedit jika statusnya open
    if (project.status !== 'open') {
        alert.show = true
        alert.type = 'error'
        alert.message = 'Only open projects can be edited. Redirecting...'
        setTimeout(() => {
            router.push(`/client/projects/${projectId}`)
        }, 2000)
        return
    }

    form.title = project.title
    form.description = project.description
    form.budget = String(project.budget)
    form.timeline = String(project.timeline)
  } catch (err) {
    console.error(err)
    alert.show = true
    alert.type = 'error'
    alert.message = 'Failed to load project details.'
  } finally {
    isLoadingContext.value = false
  }
}

onMounted(() => {
  fetchProject()
})

const handleSubmit = async () => {
  descriptionError.value = ''
  
  if (!form.description || form.description === '<p><br></p>') {
    descriptionError.value = 'Description is required'
    return
  }

  isSubmitting.value = true
  alert.show = false

  try {
    const updatePayload = {
      title: form.title,
      description: form.description,
      budget: Number(form.budget),
      timeline: Number(form.timeline),
    }

    await projectService.updateProject(projectId, updatePayload)

    alert.show = true
    alert.type = 'success'
    alert.message = 'Project updated successfully! Redirecting...'
    window.scrollTo({ top: 0, behavior: 'smooth' })

    setTimeout(() => {
      router.push(`/client/projects/${projectId}`)
    }, 1500)

  } catch (error: any) {
    console.error('Update project error:', error)
    alert.show = true
    alert.type = 'error'
    alert.message = error.response?.data?.message || 'Failed to update project. Please try again.'
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style>
/* Adjust vue-quill editor basic layout */
.ql-editor {
  min-height: 16rem;
  font-family: inherit;
  font-size: 0.875rem; 
}
</style>
