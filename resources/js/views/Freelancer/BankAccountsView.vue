<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12 text-gray-900">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Bank Accounts</h1>
        <p class="text-gray-500 mt-1">Manage your bank accounts for secure and automatic payouts</p>
      </div>
    </div>

    <!-- Alerts -->
    <div v-if="successMessage || errorMessage" class="space-y-4">
      <BaseAlert v-if="successMessage" type="success" :message="successMessage" class="mb-6" @close="successMessage = ''" />
      <BaseAlert v-if="errorMessage" type="error" :message="errorMessage" class="mb-6" @close="errorMessage = ''" />
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 text-gray-900">
      <!-- Add Account Form -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6 underline decoration-blue-500 decoration-4 underline-offset-8">
            Add Account
          </h3>
          
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank</label>
              <select 
                v-model="form.bank_code" 
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm h-11 border px-4 transition-colors"
                required
              >
                <option value="" disabled>Select a bank...</option>
                <option v-for="bank in commonBanks" :key="bank.code" :value="bank.code">
                  {{ bank.name }}
                </option>
              </select>
            </div>

            <BaseInput label="Account Number" v-model="form.account_number" placeholder="e.g. 123456789" required />
            <BaseInput label="Account Holder Name" v-model="form.account_name" placeholder="Name as it appears on bank records" required />

            <div class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-100">
              <input id="is_primary" type="checkbox" v-model="form.is_primary" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer" />
              <label for="is_primary" class="ml-3 block text-sm font-medium text-blue-900 cursor-pointer">
                Set as primary payout account
              </label>
            </div>

            <BaseButton type="submit" class="w-full justify-center py-3" :loading="isSubmitting" label="Save Bank Account" variant="primary" />
          </form>
        </div>
      </div>

      <!-- Accounts List -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
          <h3 class="text-lg font-bold text-gray-900 mb-6 underline decoration-blue-500 decoration-4 underline-offset-8">
            Your Bank Accounts
          </h3>

          <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="p-5 rounded-xl border border-gray-200 flex items-center justify-between animate-pulse">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-gray-200"></div>
                <div class="space-y-2">
                  <div class="h-4 w-40 bg-gray-200 rounded"></div>
                  <div class="h-4 w-32 bg-gray-200 rounded"></div>
                  <div class="h-3 w-24 bg-gray-200 rounded"></div>
                </div>
              </div>
              <div class="flex gap-2">
                <div class="h-8 w-24 bg-gray-200 rounded-lg"></div>
                <div class="h-8 w-16 bg-gray-200 rounded-lg"></div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="accounts.length === 0" class="py-20 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <p class="text-gray-500 font-medium">You haven't added any bank accounts yet.</p>
            <p class="text-gray-400 text-sm mt-1">Add an account to receive project payouts.</p>
          </div>

          <!-- Account Cards -->
          <div v-else class="space-y-4">
            <div 
              v-for="account in accounts" 
              :key="account.id" 
              class="group p-5 rounded-xl border border-blue-200 flex items-center justify-between"
            >
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-blue-100 text-blue-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900">{{ getBankName(account.bank_code) }}</span>
                    <BaseBadge v-if="account.is_primary" variant="primary">Primary</BaseBadge>
                  </div>
                  <p class="text-lg font-mono text-gray-700 mt-1 tracking-wider">{{ account.account_number }}</p>
                  <p class="text-sm text-gray-500 font-medium">{{ account.account_name }}</p>
                </div>
              </div>
              
              <div class="flex flex-col sm:flex-row gap-2">
                <BaseButton 
                  v-if="!account.is_primary"
                  variant="outline" 
                  size="sm"
                  @click="openSetPrimaryModal(account.id)"
                  :loading="actionLoading === account.id"
                  label="Set as Primary"
                />
                <BaseButton 
                  variant="danger" 
                  size="sm"
                  @click="openDeleteModal(account.id)"
                  :loading="actionLoading === account.id"
                  label="Delete"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BaseModal :is-open="isDeleteModalOpen" title="Delete Bank Account" @close="isDeleteModalOpen = false">
      <div class="py-4">
        <p class="text-gray-700 text-base">Are you sure you want to delete this bank account? This action cannot be undone.</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="secondary" @click="isDeleteModalOpen = false" :disabled="actionLoading !== null" />
        <BaseButton label="Delete Account" variant="danger" @click="confirmDelete" :loading="actionLoading !== null" />
      </template>
    </BaseModal>

    <BaseModal :is-open="isSetPrimaryModalOpen" title="Set as Primary Account" @close="isSetPrimaryModalOpen = false">
      <div class="py-4">
        <p class="text-gray-700 text-base">Are you sure you want to set this as your primary payout account? Your current primary account will be replaced.</p>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="secondary" @click="isSetPrimaryModalOpen = false" :disabled="actionLoading !== null" />
        <BaseButton label="Set as Primary" variant="primary" @click="confirmSetPrimary" :loading="actionLoading !== null" />
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import apiClient from '@/services/apiClient'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseModal from '@/components/ui/BaseModal.vue'

interface BankAccount {
  id: number
  bank_code: string
  account_number: string
  account_name: string
  is_primary: boolean
}

const accounts = ref<BankAccount[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const actionLoading = ref<number | null>(null)
const successMessage = ref('')
const errorMessage = ref('')

const isDeleteModalOpen = ref(false)
const accountToDeleteId = ref<number | null>(null)

const isSetPrimaryModalOpen = ref(false)
const accountToSetPrimaryId = ref<number | null>(null)

const form = reactive({
  bank_code: '',
  account_number: '',
  account_name: '',
  is_primary: false
})

const commonBanks = [
  { code: 'ID_BCA', name: 'Bank Central Asia (BCA)' },
  { code: 'ID_BNI', name: 'Bank Negara Indonesia (BNI)' },
  { code: 'ID_BRI', name: 'Bank Rakyat Indonesia (BRI)' },
  { code: 'ID_MANDIRI', name: 'Bank Mandiri' },
  { code: 'ID_PERMATA', name: 'Bank Permata' },
  { code: 'ID_CIMB', name: 'CIMB Niaga' },
  { code: 'ID_GOPAY', name: 'GoPay' },
  { code: 'ID_OVO', name: 'OVO' },
  { code: 'ID_DANA', name: 'DANA' },
]

const getBankName = (code: string) => {
  const bank = commonBanks.find(b => b.code === code)
  return bank ? bank.name : code
}

const fetchAccounts = async () => {
  isLoading.value = true
  try {
    const res = await apiClient.get('/bank-accounts')
    accounts.value = res.data.data
  } catch (err) {
    errorMessage.value = 'Failed to load bank accounts. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  successMessage.value = ''
  errorMessage.value = ''
  try {
    await apiClient.post('/bank-accounts', form)
    successMessage.value = 'Bank account saved successfully.'
    form.bank_code = ''
    form.account_number = ''
    form.account_name = ''
    form.is_primary = false
    await fetchAccounts()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to save bank account.'
  } finally {
    isSubmitting.value = false
  }
}

const openSetPrimaryModal = (id: number) => {
  accountToSetPrimaryId.value = id
  isSetPrimaryModalOpen.value = true
}

const confirmSetPrimary = async () => {
  if (accountToSetPrimaryId.value === null) return
  const id = accountToSetPrimaryId.value
  isSetPrimaryModalOpen.value = false
  actionLoading.value = id
  successMessage.value = ''
  errorMessage.value = ''
  try {
    await apiClient.patch(`/bank-accounts/${id}/primary`)
    successMessage.value = 'Primary account updated successfully.'
    await fetchAccounts()
  } catch (err) {
    errorMessage.value = 'Failed to set primary account.'
  } finally {
    actionLoading.value = null
    accountToSetPrimaryId.value = null
  }
}

const openDeleteModal = (id: number) => {
  accountToDeleteId.value = id
  isDeleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (accountToDeleteId.value === null) return
  const id = accountToDeleteId.value
  isDeleteModalOpen.value = false
  actionLoading.value = id
  successMessage.value = ''
  errorMessage.value = ''
  try {
    await apiClient.delete(`/bank-accounts/${id}`)
    successMessage.value = 'Bank account deleted successfully.'
    await fetchAccounts()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to delete bank account.'
  } finally {
    actionLoading.value = null
    accountToDeleteId.value = null
  }
}

onMounted(() => {
  fetchAccounts()
})
</script>