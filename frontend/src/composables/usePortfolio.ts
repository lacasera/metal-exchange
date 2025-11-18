import { ref } from 'vue'
import { useApi } from './useApi'

interface PortfolioItem {
  metal: string
  metal_name: string
  holdings: number
  updated?: boolean
}

export function usePortfolio() {
  const api = useApi()
  
  const portfolio = ref<PortfolioItem[]>([])
  const loadingPortfolio = ref(true)

  const loadPortfolio = async () => {
    if (!api.isAuthenticated()) {
      portfolio.value = []
      loadingPortfolio.value = false
      return
    }

    loadingPortfolio.value = true

    try {
      const response = await api.get<PortfolioItem[]>('/trades/portfolio')

      if (response.success && response.data) {
        portfolio.value = response.data.map((p: any) => ({
          metal: p.metal,
          metal_name: p.metal_name,
          holdings: p.holdings,
          updated: true
        }))

        // Remove the updated flag after animation
        setTimeout(() => {
          portfolio.value.forEach((p) => (p.updated = false))
        }, 600)
      } else {
        portfolio.value = []
      }
    } catch (error) {
      console.error('Failed to load portfolio:', error)
      portfolio.value = []
    } finally {
      loadingPortfolio.value = false
    }
  }

  return {
    portfolio,
    loadingPortfolio,
    loadPortfolio
  }
}