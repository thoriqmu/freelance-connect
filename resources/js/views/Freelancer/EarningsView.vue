<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-12 text-gray-900">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Earnings</h1>
        <p class="text-gray-500 mt-1 font-medium">Manage your income and withdraw funds to your bank account</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
        <div>
          <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Available Balance</p>
          <p class="text-3xl font-bold text-blue-600 mt-1">IDR {{ formatNumber(availableBalance) }}</p>
        </div>
        <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
        <div>
          <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total Withdrawn</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">IDR {{ formatNumber(totalWithdrawn) }}</p>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg text-gray-400">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <BaseAlert v-if="successMessage" type="success" :message="successMessage" @close="successMessage = ''" />
    <BaseAlert v-if="errorMessage" type="error" :message="errorMessage" @close="errorMessage = ''" />

    <!-- Earnings List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 underline decoration-blue-500 decoration-4 underline-offset-8">
          Earnings History
        </h3>
      </div>

      <div v-if="isLoading" class="p-12 flex flex-col items-center justify-center space-y-4">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 text-sm font-bold">Loading your earnings...</p>
      </div>

      <div v-else-if="earnings.length === 0" class="p-16 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900">No Earnings Yet</h3>
        <p class="text-gray-500 mt-1 font-medium">Complete project submissions to receive payments!</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-bold">
              <th class="px-6 py-4">Project</th>
              <th class="px-6 py-4">Amount</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Date</th>
              <th class="px-6 py-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="earning in earnings" :key="earning.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <p class="font-bold text-gray-900">{{ earning.project?.title }}</p>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-tight">Project #{{ earning.project?.id }}</p>
              </td>
              <td class="px-6 py-4 font-bold text-gray-800">IDR {{ formatNumber(earning.amount) }}</td>
              <td class="px-6 py-4">
                <BaseBadge :variant="earning.status === 'available' ? 'success' : 'primary'">
                  {{ earning.status === 'available' ? 'Available' : 'Withdrawn' }}
                </BaseBadge>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 font-medium whitespace-nowrap">
                {{ formatDate(earning.created_at) }}
              </td>
              <td class="px-6 py-4 text-right">
                <BaseButton 
                  v-if="earning.status === 'available'" 
                  label="Withdraw" 
                  size="sm" 
                  variant="primary" 
                  @click="openWithdrawModal(earning)"
                  class="shadow-sm"
                />
                <div v-else class="flex flex-col items-end">
                   <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Withdrawn</span>
                   <span class="text-[10px] text-gray-400 font-medium">{{ formatDate(earning.withdrawn_at) }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Withdraw Confirmation Modal -->
    <BaseModal 
      :is-open="isWithdrawModalOpen" 
      title="Confirm Withdrawal" 
      @close="isWithdrawModalOpen = false"
    >
      <div class="py-4 space-y-6">
        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
          <div class="mt-1 text-blue-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-sm text-blue-800 font-bold leading-relaxed">
            Funds will be transferred to your primary bank account. Please ensure your bank details are correct before proceeding.
          </p>
        </div>

        <div class="bg-gray-50 rounded-xl border border-gray-200 divide-y divide-gray-200">
          <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-bold text-gray-500">Withdrawal Amount</span>
            <span class="text-xl font-bold text-blue-600">IDR {{ formatNumber(selectedEarning?.amount) }}</span>
          </div>
          
          <div v-if="primaryBankAccount" class="p-4 flex justify-between items-start">
            <span class="text-sm font-bold text-gray-500">Destination Bank</span>
            <div class="text-right">
              <p class="font-bold text-gray-900">{{ primaryBankAccount.bank_code }}</p>
              <p class="text-sm text-gray-500 font-bold tracking-wider">{{ primaryBankAccount.account_number }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ primaryBankAccount.account_name }}</p>
            </div>
          </div>
          <div v-else class="p-4 bg-red-50 text-red-600 rounded-b-xl text-sm font-bold">
            No primary bank account found! Go to Bank Accounts to set one.
          </div>
        </div>
      </div>
      <template #actions>
        <BaseButton label="Cancel" variant="outline" @click="isWithdrawModalOpen = false" :disabled="isWithdrawing" class="font-bold" />
        <BaseButton 
          label="Confirm Withdrawal" 
          variant="primary" 
          @click="handleWithdraw" 
          :loading="isWithdrawing"
          :disabled="!primaryBankAccount"
          class="font-bold shadow-md shadow-blue-200"
        />
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import apiClient from '@/services/apiClient'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseModal from '@/components/ui/BaseModal.vue'

interface Earning {
  id: number
  project_id: number
  amount: number
  status: string
  created_at: string
  withdrawn_at: string | null
  project?: {
    id: number
    title: string
  }
}

interface BankAccount {
  id: number
  bank_code: string
  account_number: string
  account_name: string
  is_primary: boolean
}

const earnings = ref<Earning[]>([])
const availableBalance = ref(0)
const totalWithdrawn = ref(0)
const bankAccounts = ref<BankAccount[]>([])
const isLoading = ref(true)
const isWithdrawing = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const isWithdrawModalOpen = ref(false)
const selectedEarning = ref<Earning | null>(null)

const primaryBankAccount = computed(() => {
  return bankAccounts.value.find(acc => acc.is_primary)
})

const fetchEarnings = async () => {
  isLoading.value = true
  try {
    const res = await apiClient.get('/earnings')
    earnings.value = res.data.data.earnings
    availableBalance.value = res.data.data.available_balance
    totalWithdrawn.value = res.data.data.total_withdrawn
  } catch (err) {
    console.error('Failed to fetch earnings:', err)
    errorMessage.value = 'Failed to load earnings data.'
  } finally {
    isLoading.value = false
  }
}

const fetchBankAccounts = async () => {
  try {
    const res = await apiClient.get('/bank-accounts')
    bankAccounts.value = res.data.data
  } catch (err) {
    console.error('Failed to fetch bank accounts:', err)
  }
}

const openWithdrawModal = (earning: Earning) => {
  selectedEarning.value = earning
  isWithdrawModalOpen.value = true
}

const handleWithdraw = async () => {
  if (!selectedEarning.value) return
  
  isWithdrawing.value = true
  successMessage.value = ''
  errorMessage.value = ''
  
  try {
    await apiClient.post(`/earnings/${selectedEarning.value.id}/withdraw`)
    successMessage.value = 'Withdrawal request processed successfully!'
    isWithdrawModalOpen.value = false
    await fetchEarnings()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to process withdrawal.'
  } finally {
    isWithdrawing.value = false
  }
}

const formatNumber = (num: number | undefined) => {
  if (num === undefined) return '0'
  return new Intl.NumberFormat('id-ID').format(num)
}

const formatDate = (dateStr: string | null) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchEarnings()
  fetchBankAccounts()
})
</script>
