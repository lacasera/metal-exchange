<script setup lang="ts">
interface SavingsPlan {
  id: number
  name: string
  metal_id?: number
  amount?: number
  amount_eur?: string | number
  frequency: 'daily' | 'weekly' | 'monthly'
  status: 'active' | 'paused'
  created_at: string
  updated_at: string
  metal?: {
    id: number
    name: string
    symbol: string
    created_at: string
    updated_at: string
  }
  executions?: any[]
  metal_name?: string
  metal_symbol?: string
  next_execution_date?: string | null
  last_execution_date?: string | null
}

interface Props {
  savingsPlans: SavingsPlan[]
  isAuthenticated: boolean
  loading?: boolean
}

interface Emits {
  (e: 'deleteSavingsPlan', id: number): void
  (e: 'pauseSavingsPlan', id: number): void
  (e: 'resumeSavingsPlan', id: number): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const formatDate = (dateString: string) => {
  try {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return 'Invalid Date'
    return date.toLocaleDateString()
  } catch (error) {
    return 'Invalid Date'
  }
}
</script>

<template>
  <div class="border p-6 rounded bg-white shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Your Savings Plans</h2>
    
    <div v-if="!isAuthenticated" class="text-center text-gray-600 py-8">
      <p>Please log in to view your savings plans.</p>
    </div>
    
    <div v-else-if="loading" class="text-center text-gray-600 py-8">
      <p>Loading your savings plans...</p>
    </div>
    
    <div v-else-if="savingsPlans.length === 0" class="text-center text-gray-600 py-8">
      <p>You don't have any savings plans yet.</p>
      <p class="text-sm text-gray-500 mt-2">Create your first savings plan to start investing automatically!</p>
    </div>
    
    <div v-else class="space-y-4">
      <div
        v-for="plan in savingsPlans"
        :key="plan.id"
        class="border rounded p-4 bg-gray-50"
      >
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <h3 class="font-semibold text-lg">{{ plan.name }}</h3>
            <p class="text-gray-600 text-sm">
              {{ plan.metal?.name || plan.metal_name || 'Unknown Metal' }} ({{ plan.metal?.symbol || plan.metal_symbol || 'N/A' }})
            </p>
            <div class="mt-2 grid grid-cols-3 gap-4 text-sm">
              <div>
                <span class="text-gray-500">Amount:</span>
                <span class="font-medium ml-1">€{{ Number(plan.amount_eur || plan.amount || 0).toFixed(2) }}</span>
              </div>
              <div>
                <span class="text-gray-500">Frequency:</span>
                <span class="font-medium ml-1 capitalize">{{ plan.frequency }}</span>
              </div>
              <div>
                <span class="text-gray-500">Status:</span>
                <span 
                  class="font-medium ml-1 capitalize"
                  :class="{
                    'text-green-600': plan.status === 'active',
                    'text-red-600': plan.status === 'paused'
                  }"
                >
                  {{ plan.status }}
                </span>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
              Created on {{ plan.created_at ? formatDate(plan.created_at) : 'Unknown' }}
            </p>
            <div v-if="plan.next_execution_date || plan.last_execution_date" class="text-xs text-gray-500 mt-1 space-y-1">
              <p v-if="plan.next_execution_date">
                Next execution: {{ formatDate(plan.next_execution_date) }}
              </p>
              <p v-if="plan.last_execution_date">
                Last execution: {{ formatDate(plan.last_execution_date) }}
              </p>
            </div>
          </div>
          
          <div class="ml-4 flex gap-2">
            <button
              v-if="plan.status === 'active'"
              @click="emit('pauseSavingsPlan', plan.id)"
              class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600"
            >
              Pause
            </button>
            <button
              v-else
              @click="emit('resumeSavingsPlan', plan.id)"
              class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600"
            >
              Resume
            </button>
            
            <button
              @click="emit('deleteSavingsPlan', plan.id)"
              class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>