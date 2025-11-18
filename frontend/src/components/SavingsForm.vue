<script setup lang="ts">
interface Props {
  planName: string
  planMetalId: number | null
  planAmount: number
  planFrequency: 'daily' | 'weekly' | 'monthly'
  planSubmitting: boolean
  savingsError: string
  savingsSuccess: string
  activeMetals: Array<{ id: number; symbol: string; name: string }>
  isAuthenticated: boolean
}

interface Emits {
  (e: 'update:planName', value: string): void
  (e: 'update:planMetalId', value: number | null): void
  (e: 'update:planAmount', value: number): void
  (e: 'update:planFrequency', value: 'daily' | 'weekly' | 'monthly'): void
  (e: 'createSavingsPlan'): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()
</script>

<template>
  <div class="border p-6 rounded bg-white shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Create Savings Plan</h2>

    <div
      v-if="savingsSuccess"
      class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4"
    >
      {{ savingsSuccess }}
    </div>

    <div
      v-if="savingsError"
      class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4"
    >
      {{ savingsError }}
    </div>

    <label class="text-sm">Plan Name</label>
    <input
      :value="planName"
      @input="emit('update:planName', ($event.target as HTMLInputElement).value)"
      type="text"
      class="border rounded p-2 w-full mb-3"
      placeholder="e.g., Monthly Gold Investment"
    />

    <label class="text-sm">Metal</label>
    <select 
      :value="planMetalId" 
      @change="emit('update:planMetalId', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
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

    <label class="text-sm">Amount (EUR)</label>
    <input
      :value="planAmount"
      @input="emit('update:planAmount', Number(($event.target as HTMLInputElement).value))"
      type="number"
      min="0.01"
      step="0.01"
      class="border rounded p-2 w-full mb-3"
      placeholder="e.g., 50"
    />

    <label class="text-sm">Frequency</label>
    <select 
      :value="planFrequency" 
      @change="emit('update:planFrequency', ($event.target as HTMLSelectElement).value as 'daily' | 'weekly' | 'monthly')"
      class="border rounded p-2 w-full mb-4"
    >
      <option value="daily">Daily</option>
      <option value="weekly">Weekly</option>
      <option value="monthly">Monthly</option>
    </select>

    <button
      @click="emit('createSavingsPlan')"
      :disabled="planSubmitting || !isAuthenticated"
      class="bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed"
    >
      {{ planSubmitting ? 'Creating...' : 'Create Savings Plan' }}
    </button>

    <p v-if="!isAuthenticated" class="text-sm text-red-600 mt-2">
      You must be logged in to create savings plans.
    </p>
  </div>
</template>