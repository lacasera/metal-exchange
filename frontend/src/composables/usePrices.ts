import { ref, onMounted } from 'vue'
import { echo } from '../echo'
import type { Metal } from '../types/metal'
import type { PricesUpdatedEvent } from '../types'

export function usePrices() {
  const prices = ref<Metal[]>([])
  const previousPrices = ref<Record<string, number>>({})
  const loading = ref(true)

  const updatePrices = (newPrices: Metal[]) => {
    newPrices.forEach((p) => {
      const prev = previousPrices.value[p.symbol]
      previousPrices.value[p.symbol] = p.price_eur

      const direction =
        prev === undefined
          ? 'same'
          : p.price_eur > prev
          ? 'up'
          : p.price_eur < prev
          ? 'down'
          : 'same'

      // @ts-ignore - Adding direction property dynamically
      p.direction = direction
    })

    prices.value = newPrices
    loading.value = false
  }

  const initializeWebSocket = () => {
    echo
      .channel('metal-prices')
      .listen('.App\\Domain\\Prices\\Events\\MetalPricesUpdated', (event: PricesUpdatedEvent) => {
        updatePrices(event.prices)
      })
  }

  // Auto-initialize WebSocket on mount
  onMounted(() => {
    initializeWebSocket()
  })

  return {
    prices,
    previousPrices,
    loading,
    updatePrices,
    initializeWebSocket
  }
}