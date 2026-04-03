<template>
  <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Rekening Bank</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola rekening bank Anda untuk menerima pembayaran (payout) dari proyek yang selesai.
      </p>
    </div>

    <BaseAlert v-if="successMessage" type="success" class="mb-6">
      {{ successMessage }}
    </BaseAlert>

    <BaseAlert v-if="errorMessage" type="danger" class="mb-6">
      {{ errorMessage }}
    </BaseAlert>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Form Tambah Rekening -->
      <div class="lg:col-span-1">
        <BaseCard shadow="sm">
          <template #header>
            <h3 class="text-lg font-medium">Tambah Rekening</h3>
          </template>
          
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
              <select 
                v-model="form.bank_code" 
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-10 border px-3"
                required
              >
                <option value="" disabled>Pilih Bank</option>
                <option v-for="bank in commonBanks" :key="bank.code" :value="bank.code">
                  {{ bank.name }}
                </option>
              </select>
            </div>

            <BaseInput
              label="Nomor Rekening"
              v-model="form.account_number"
              placeholder="Contoh: 123456789"
              required
            />

            <BaseInput
              label="Nama Pemilik Rekening"
              v-model="form.account_name"
              placeholder="Sesuai buku tabungan"
              required
            />

            <div class="flex items-center">
              <input
                id="is_primary"
                type="checkbox"
                v-model="form.is_primary"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_primary" class="ml-2 block text-sm text-gray-900">
                Jadikan utama
              </label>
            </div>

            <BaseButton 
              type="submit" 
              class="w-full justify-center"
              :loading="isSubmitting"
            >
              Simpan Rekening
            </BaseButton>
          </form>
        </BaseCard>
      </div>

      <!-- Daftar Rekening -->
      <div class="lg:col-span-2">
        <BaseCard shadow="sm">
          <template #header>
            <h3 class="text-lg font-medium text-gray-900">Rekening Tersedia</h3>
          </template>

          <div v-if="isLoading" class="py-12 flex justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>

          <div v-else-if="accounts.length === 0" class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500">Anda belum memiliki rekening terdaftar.</p>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div v-for="account in accounts" :key="account.id" class="py-4 flex items-center justify-between">
              <div>
                <div class="flex items-center">
                  <span class="text-sm font-semibold text-gray-900 mr-2">{{ getBankName(account.bank_code) }}</span>
                  <BaseBadge v-if="account.is_primary" variant="primary">Utama</BaseBadge>
                </div>
                <p class="text-sm text-gray-600">{{ account.account_number }}</p>
                <p class="text-xs text-gray-500 font-medium mt-1">{{ account.account_name }}</p>
              </div>
              
              <div class="flex space-x-2">
                <BaseButton 
                  v-if="!account.is_primary"
                  variant="outline" 
                  size="sm"
                  @click="handleSetPrimary(account.id)"
                  :loading="actionLoading === account.id"
                >
                  Jadikan Utama
                </BaseButton>
                
                <BaseButton 
                  variant="danger" 
                  size="sm"
                  @click="handleDelete(account.id)"
                  :loading="actionLoading === account.id"
                >
                  Hapus
                </BaseButton>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import apiClient from '@/services/apiClient'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

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
    console.error('Failed to fetch accounts', err)
    errorMessage.value = 'Gagal memuat daftar rekening.'
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
    successMessage.value = 'Rekening berhasil ditambahkan.'
    // Reset form
    form.bank_code = ''
    form.account_number = ''
    form.account_name = ''
    form.is_primary = false
    
    await fetchAccounts()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal menambahkan rekening.'
  } finally {
    isSubmitting.value = false
  }
}

const handleSetPrimary = async (id: number) => {
  actionLoading.value = id
  successMessage.value = ''
  errorMessage.value = ''
  
  try {
    await apiClient.patch(`/bank-accounts/${id}/primary`)
    successMessage.value = 'Rekening utama berhasil diperbarui.'
    await fetchAccounts()
  } catch (err) {
    errorMessage.value = 'Gagal memperbarui rekening utama.'
  } finally {
    actionLoading.value = null
  }
}

const handleDelete = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus rekening ini?')) return
  
  actionLoading.value = id
  successMessage.value = ''
  errorMessage.value = ''
  
  try {
    await apiClient.delete(`/bank-accounts/${id}`)
    successMessage.value = 'Rekening berhasil dihapus.'
    await fetchAccounts()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal menghapus rekening.'
  } finally {
    actionLoading.value = null
  }
}

onMounted(() => {
  fetchAccounts()
})
</script>
