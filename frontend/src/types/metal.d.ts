
export interface Metal {
  id: number
  name: string
  symbol: string
  price_eur: number
  timestamp: string
  direction?: 'up' | 'down' | 'same'
}