<script setup lang="ts">
interface PortfolioItem {
  metal: string
  metal_name: string
  holdings: number
  updated?: boolean
}

interface Props {
  portfolio: PortfolioItem[]
  loadingPortfolio: boolean
}

defineProps<Props>()
</script>

<template>
  <div class="border p-6 rounded bg-white shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Your Portfolio</h2>

    <div v-if="loadingPortfolio" class="text-gray-500">
      Loading portfolio...
    </div>

    <div v-else-if="portfolio.length === 0" class="text-gray-500">
      No holdings yet. Execute a trade to get started.
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="item in portfolio"
        :key="item.metal"
        :class="[
          'border rounded p-3 bg-gray-50 transition',
          item.updated ? 'portfolio-flash' : ''
        ]"
      >
        <div class="flex justify-between">
          <span class="font-semibold">{{ item.metal_name }} ({{ item.metal }})</span>
          <span class="font-mono">{{ item.holdings.toFixed(6) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.portfolio-flash {
  animation: flashPortfolio 0.6s ease-out;
}

@keyframes flashPortfolio {
  0% { background-color: #dbeafe; }
  100% { background-color: transparent; }
}
</style>