<script setup lang="ts">
import type { Metal } from '../types/metal'

interface Props {
  metal: Metal
}

defineProps<Props>()
</script>

<template>
  <div
    :class="[
      'border p-4 rounded bg-white shadow-sm',
      metal.direction === 'up' ? 'flash-up' : '',
      metal.direction === 'down' ? 'flash-down' : ''
    ]"
  >
    <h2 class="font-semibold">{{ metal.name }} ({{ metal.symbol }})</h2>
    <p class="text-2xl font-bold mt-2">€{{ metal.price_eur }}</p>

    <p class="text-sm mt-1">
      <span v-if="metal.direction === 'up'" class="text-green-600">↑ rising</span>
      <span v-else-if="metal.direction === 'down'" class="text-red-600">↓ falling</span>
      <span v-else class="text-gray-400">→ stable</span>
    </p>

    <small class="text-gray-500">{{ new Date(metal.timestamp).toLocaleString() }}</small>
  </div>
</template>

<style scoped>
.flash-up {
  animation: flashGreen 0.6s ease-out;
}

.flash-down {
  animation: flashRed 0.6s ease-out;
}

@keyframes flashGreen {
  0% { background-color: #d1fae5; }
  100% { background-color: transparent; }
}

@keyframes flashRed {
  0% { background-color: #fee2e2; }
  100% { background-color: transparent; }
}
</style>