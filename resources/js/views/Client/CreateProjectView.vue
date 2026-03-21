<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 pb-12">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Create New Project</h1>
        <p class="text-gray-500 mt-1">Post your project details to find the right talent.</p>
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

    <form @submit.prevent="handleSubmit" class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
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
           <!-- We use min-h-[16rem] for quill editor wrapper -->
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

      <!-- Attachments Section -->
      <div class="pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
          <BaseButton
            type="button"
            variant="outline"
            size="sm"
            @click="addAttachment"
            label="Add File"
          />
        </div>

        <div v-if="attachments.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
          <p class="text-gray-500 text-sm">No attachments added yet. Click "Add File" to upload specifications, designs, or other related documents.</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="(attachment, index) in attachments"
            :key="index"
            class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200"
          >
            <div class="flex-1 space-y-4">
              <BaseInput
                v-model="attachment.title"
                label="Attachment Title"
                placeholder="e.g. Project Requirements Document"
                required
              />
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Upload</label>
                <input
                  type="file"
                  @change="handleFileChange($event, index)"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  required
                />
              </div>
            </div>
            <button
              type="button"
              @click="removeAttachment(index)"
              class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
              title="Remove Attachment"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="pt-6 border-t border-gray-200 flex flex-col-reverse sm:flex-row justify-end gap-3">
        <BaseButton
          type="button"
          variant="outline"
          label="Cancel"
          @click="$router.push('/client/projects')"
          :disabled="isSubmitting"
          class="w-full sm:w-auto"
        />
        <BaseButton
          type="submit"
          variant="primary"
          :label="isSubmitting ? 'Creating Project...' : 'Create Project'"
          :disabled="isSubmitting"
          class="w-full sm:w-auto"
        />
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import { projectService } from '@/services/projectService'
import apiClient from '@/services/apiClient'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const router = useRouter()

const form = reactive({
  title: '',
  description: '',
  budget: '',
  timeline: '',
})

interface AttachmentItem {
  title: string
  file: File | null
}

const attachments = ref<AttachmentItem[]>([])
const isSubmitting = ref(false)
const descriptionError = ref('')

const alert = reactive({
  show: false,
  type: 'info' as 'success' | 'error' | 'warning' | 'info',
  message: '',
})

const addAttachment = () => {
  attachments.value.push({ title: '', file: null })
}

const removeAttachment = (index: number) => {
  attachments.value.splice(index, 1)
}

const handleFileChange = (event: Event, index: number) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    attachments.value[index].file = target.files[0]
  }
}

const handleSubmit = async () => {
  descriptionError.value = ''
  
  if (!form.description || form.description === '<p><br></p>') {
    descriptionError.value = 'Description is required'
    return
  }

  // Validate attachments
  const invalidAttachment = attachments.value.find(a => !a.file || !a.title)
  if (invalidAttachment) {
    alert.show = true
    alert.type = 'error'
    alert.message = 'Please provide both a title and a file for all attachments.'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  isSubmitting.value = true
  alert.show = false

  try {
    // 1. Create Project
    const response = await projectService.createProject({
      title: form.title,
      description: form.description,
      budget: Number(form.budget),
      timeline: Number(form.timeline),
    })

    const projectId = response.data.data?.id

    if (!projectId) {
      throw new Error('Project ID not returned from server')
    }

    // 2. Upload Attachments Process
    if (attachments.value.length > 0) {
      // Loop execution array to maintain sequence
      for (const attachment of attachments.value) {
        if (attachment.file) {
          const formData = new FormData()
          formData.append('title', attachment.title)
          formData.append('file', attachment.file)

           await apiClient.post(`/projects/${projectId}/attachments`, formData, {
             headers: {
               'Content-Type': 'multipart/form-data',
             }
           })
        }
      }
    }

    // Success
    alert.show = true
    alert.type = 'success'
    alert.message = 'Project created successfully! Redirecting...'
    window.scrollTo({ top: 0, behavior: 'smooth' })

    // Redirect after a short delay
    setTimeout(() => {
      router.push('/client/projects')
    }, 1500)

  } catch (error: any) {
    console.error('Create project error:', error)
    alert.show = true
    alert.type = 'error'
    alert.message = error.response?.data?.message || 'Gagal membuat project. Silakan coba lagi.'
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
