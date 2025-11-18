export interface PricesUpdatedEvent {
  prices: Array<{
    id: number
    name: string
    symbol: string
    price_eur: number
    timestamp: string
  }>
}
