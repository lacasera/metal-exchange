import { ref } from 'vue'
import { useApi } from './useApi'

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

export function useSavingsPlans() {
  const api = useApi()

  // State
  const savingsPlans = ref<SavingsPlan[]>([])
  const loadingSavings = ref(true)
  const savingsError = ref('')
  const savingsSuccess = ref('')
  const planSubmitting = ref(false)

  // Form state
  const planName = ref('')
  const planMetalId = ref<number | null>(null)
  const planAmount = ref<number>(0)
  const planFrequency = ref<'daily' | 'weekly' | 'monthly'>('monthly')

  const loadSavingsPlans = async () => {
    if (!api.isAuthenticated()) {
      savingsPlans.value = []
      loadingSavings.value = false
      return
    }

    loadingSavings.value = true

    try {
      const response = await api.get<SavingsPlan[]>('/savings-plans')

      if (response.success) {
        const plans = response.data ?? []
        savingsPlans.value = Array.isArray(plans) ? plans : []
      } else {
        console.warn('Failed to load savings plans:', response.message)
        savingsPlans.value = []
      }
    } catch (error) {
      console.error('Error loading savings plans:', error)
      savingsPlans.value = []
    } finally {
      loadingSavings.value = false
    }
  }

  const createSavingsPlan = async () => {
    savingsError.value = ''
    savingsSuccess.value = ''

    if (!api.isAuthenticated()) {
      savingsError.value = 'You must be logged in to create savings plans.'
      return
    }

    if (!planMetalId.value || !planAmount.value || planAmount.value <= 0) {
      savingsError.value = 'Please select a metal and enter a valid amount.'
      return
    }

    if (!planName.value.trim()) {
      savingsError.value = 'Please enter a plan name.'
      return
    }

    planSubmitting.value = true

    try {
      const response = await api.post('/savings-plans', {
        name: planName.value.trim(),
        metal_id: planMetalId.value,
        amount_eur: planAmount.value,
        frequency: planFrequency.value
      })

      if (response.success) {
        savingsSuccess.value = 'Savings plan created successfully!'
        resetForm()
        await loadSavingsPlans()
      } else {
        savingsError.value = response.message || 'Failed to create savings plan.'
      }
    } catch (error) {
      console.error('Error creating savings plan:', error)
      savingsError.value = 'Failed to create savings plan. Please try again.'
    } finally {
      planSubmitting.value = false
    }
  }

  const deleteSavingsPlan = async (planId: number) => {
    if (!api.isAuthenticated()) {
      savingsError.value = 'You must be logged in to delete savings plans.'
      return
    }

    try {
      const response = await api.delete(`/savings-plans/${planId}`)

      if (response.success) {
        savingsSuccess.value = 'Savings plan deleted successfully!'
        await loadSavingsPlans()
      } else {
        savingsError.value = response.message || 'Failed to delete savings plan.'
      }
    } catch (error) {
      console.error('Failed to delete savings plan:', error)
      savingsError.value = 'Failed to delete savings plan. Please try again.'
    }
  }

  const pauseSavingsPlan = async (planId: number) => {
    if (!api.isAuthenticated()) {
      savingsError.value = 'You must be logged in to pause savings plans.'
      return
    }

    try {
      const response = await api.put(`/savings-plans/${planId}/pause`)

      if (response.success) {
        savingsSuccess.value = 'Savings plan paused successfully!'
        await loadSavingsPlans()
      } else {
        savingsError.value = response.message || 'Failed to pause savings plan.'
      }
    } catch (error) {
      console.error('Failed to pause savings plan:', error)
      savingsError.value = 'Failed to pause savings plan. Please try again.'
    }
  }

  const resumeSavingsPlan = async (planId: number) => {
    if (!api.isAuthenticated()) {
      savingsError.value = 'You must be logged in to resume savings plans.'
      return
    }

    try {
      const response = await api.put(`/savings-plans/${planId}/resume`)

      if (response.success) {
        savingsSuccess.value = 'Savings plan resumed successfully!'
        await loadSavingsPlans()
      } else {
        savingsError.value = response.message || 'Failed to resume savings plan.'
      }
    } catch (error) {
      console.error('Failed to resume savings plan:', error)
      savingsError.value = 'Failed to resume savings plan. Please try again.'
    }
  }

  const resetForm = () => {
    planName.value = ''
    planMetalId.value = null
    planAmount.value = 0
    planFrequency.value = 'monthly'
  }

  const clearMessages = () => {
    savingsError.value = ''
    savingsSuccess.value = ''
  }

  // Load savings plans when tab is first visited
  const ensureSavingsLoaded = async () => {
    if (!loadingSavings.value && savingsPlans.value.length) return
    loadingSavings.value = true
    await loadSavingsPlans()
  }

  return {
    // State
    savingsPlans,
    loadingSavings,
    savingsError,
    savingsSuccess,
    planSubmitting,

    // Form state
    planName,
    planMetalId,
    planAmount,
    planFrequency,

    // Methods
    loadSavingsPlans,
    createSavingsPlan,
    deleteSavingsPlan,
    pauseSavingsPlan,
    resumeSavingsPlan,
    resetForm,
    clearMessages,
    ensureSavingsLoaded
  }
}