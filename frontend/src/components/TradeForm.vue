<script setup lang="ts">
import { computed } from 'vue'
import type { Metal } from '../types/metal'

interface Props {
  metalId: number | null
  tradeAmount: number
  tradeType: 'buy' | 'sell'
  submitting: boolean
  tradeError: string
  tradeSuccess: string
  activeMetals: Array<{ id: number; symbol: string; name: string }>
  prices: Metal[]
  isAuthenticated: boolean
}

interface Emits {
  (e: 'update:metalId', value: number | null): void
  (e: 'update:tradeAmount', value: number): void
  (e: 'update:tradeType', value: 'buy' | 'sell'): void
  (e: 'submitTrade'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const calculation = computed(() => {
  if (!props.metalId || !props.tradeAmount || props.tradeAmount <= 0) return null

  const selected = props.activeMetals.find((m) => m.id === props.metalId)
  if (!selected) return null

  const priceRow = props.prices.find((p) => p.symbol === selected.symbol)
  if (!priceRow) return null

  return (props.tradeAmount / priceRow.price_eur).toFixed(6)
})
</script>

<template>
  <div class="border p-6 rounded bg-white shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Execute Trade</h2>

    <div
      v-if="tradeSuccess"
      class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4"
    >
      {{ tradeSuccess }}
    </div>

    <div
      v-if="tradeError"
      class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4"
    >
      {{ tradeError }}
    </div>

    <label class="text-sm">Metal</label>
    <select 
      :value="metalId" 
      @change="emit('update:metalId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
      class="border rounded p-2 w-full mb-3"
    >
      <option :value="null">Select metal</option>
      <option
        v-for="metal in activeMetals"
        :key="metal.id"
        :value="metal.id"
      >
        {{ metal.name }}
      </option>
    </select>

    <label class="text-sm">Trade Type</label>
    <select 
      :value="tradeType" 
      @change="emit('update:tradeType', ($event.target as HTMLSelectElement).value as 'buy' | 'sell')"
      class="border rounded p-2 w-full mb-3"
    >
      <option value="buy">Buy</option>
      <option value="sell">Sell</option>
    </select>

    <label class="text-sm">Amount (EUR)</label>
    <input
      :value="tradeAmount"
      @input="emit('update:tradeAmount', Number(($event.target as HTMLInputElement).value))"
      type="number"
      step="0.01"
      min="0.01"
      class="border rounded p-2 w-full mb-4"
      placeholder="e.g., 150"
    />

    <div v-if="calculation" class="text-sm text-gray-500 mb-4">
      You will {{ tradeType === 'buy' ? 'receive' : 'sell' }}:
      <strong>{{ calculation }}</strong> units
    </div>

    <button
      @click="emit('submitTrade')"
      :disabled="submitting || !isAuthenticated"
      class="bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed"
    >
      {{ submitting ? 'Processing...' : 'Submit Trade' }}
    </button>

    <p v-if="!isAuthenticated" class="text-sm text-red-600 mt-2">
      You must be logged in to execute trades.
    </p>
  </div>
</template>