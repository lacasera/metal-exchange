import { ref, computed } from 'vue'
import { useApi } from './useApi'
import type { Metal } from '../types/metal'

export function useTrades() {
  const api = useApi()

  // Trade form state
  const metalId = ref<number | null>(null)
  const tradeAmount = ref<number>(0)
  const tradeType = ref<'buy' | 'sell'>('buy')

  // UI state
  const submitting = ref(false)
  const tradeError = ref('')
  const tradeSuccess = ref('')

  // Available metals
  const activeMetals = [
    { id: 1, symbol: 'XAU', name: 'Gold' },
    { id: 2, symbol: 'XAG', name: 'Silver' },
    { id: 3, symbol: 'XPT', name: 'Platinum' },
    { id: 4, symbol: 'XPD', name: 'Palladium' }
  ]

  // Live calculation
  const createCalculation = (prices: Metal[]) => computed(() => {
    if (!metalId.value || !tradeAmount.value || tradeAmount.value <= 0) return null

    const selected = activeMetals.find((m) => m.id === metalId.value)
    if (!selected) return null

    const priceRow = prices.find((p) => p.symbol === selected.symbol)
    if (!priceRow) return null

    return (tradeAmount.value / priceRow.price_eur).toFixed(6)
  })

  const submitTrade = async (onPortfolioUpdate?: () => Promise<void>) => {
    tradeError.value = ''
    tradeSuccess.value = ''

    if (!api.isAuthenticated()) {
      tradeError.value = 'You must be logged in to execute trades.'
      return
    }

    if (!metalId.value || !tradeAmount.value) {
      tradeError.value = 'Please select a metal and enter a valid amount.'
      return
    }

    submitting.value = true

    const endpoint = tradeType.value === 'buy' ? '/trades/buy' : '/trades/sell'

    try {
      const response = await api.post(endpoint, {
        metal_id: metalId.value,
        amount: tradeAmount.value
      })

      if (response.success) {
        tradeSuccess.value = 'Trade executed successfully!'
        tradeAmount.value = 0
        metalId.value = null

        // Reload portfolio if callback provided
        if (onPortfolioUpdate) {
          await onPortfolioUpdate()
        }
      } else {
        tradeError.value = response.message || 'Trade failed.'
      }
    } catch (error) {
      console.error('Trade failed:', error)
      tradeError.value = 'Trade failed. Try again.'
    } finally {
      submitting.value = false
    }
  }

  const resetForm = () => {
    metalId.value = null
    tradeAmount.value = 0
    tradeType.value = 'buy'
    tradeError.value = ''
    tradeSuccess.value = ''
  }

  return {
    // State
    metalId,
    tradeAmount,
    tradeType,
    submitting,
    tradeError,
    tradeSuccess,
    activeMetals,

    // Methods
    createCalculation,
    submitTrade,
    resetForm
  }
}