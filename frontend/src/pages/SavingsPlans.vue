<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '../stores/auth'
import type { Metal } from '../types/metal'

const auth = useAuth()
const apiBase = import.meta.env.VITE_API_BASE_URL

/**
 * -----------------------------------------------------
 * State
 * -----------------------------------------------------
 */
const loading = ref(true)
const metals = ref<Metal[]>([])
const plans = ref<any[]>([])

const creating = ref(false)
const createError = ref('')
const createSuccess = ref('')

const metalId = ref<number | null>(null)
const amountEur = ref<number>(0)
const frequency = ref<'daily' | 'weekly' | 'monthly'>('monthly')

/**
 * -----------------------------------------------------
 * Load Metals (from price feed)
 * -----------------------------------------------------
 */
async function loadMetals() {
  const res = await fetch(`${apiBase}/prices/latest`, {
    credentials: 'include',
     headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${auth.token}`
      },
  })

  const json = await res.json()
  metals.value = json.data
}

/**
 * -----------------------------------------------------
 * Load Savings Plans
 * -----------------------------------------------------
 */
async function loadPlans() {
  const res = await fetch(`${apiBase}/savings-plans`, {
    credentials: 'include',
     headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${auth.token}`
      },
  })

  const json = await res.json()
  plans.value = json.data
}

/**
 * -----------------------------------------------------
 * Create Savings Plan
 * -----------------------------------------------------
 */
async function createPlan() {
  createError.value = ''
  createSuccess.value = ''

  if (!metalId.value || amountEur.value <= 0) {
    createError.value = 'Please select a metal and enter a valid amount.'
    return
  }

  creating.value = true

  try {
    const res = await fetch(`${apiBase}/savings-plans`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        metal_id: metalId.value,
        amount_eur: amountEur.value,
        frequency: frequency.value
      })
    })

    const json = await res.json()

    if (!res.ok) {
      createError.value = json.message || 'Failed to create plan.'
      return
    }

    createSuccess.value = 'Savings Plan created!'
    metalId.value = null
    amountEur.value = 0
    frequency.value = 'monthly'

    await loadPlans()
  } catch (e) {
    createError.value = 'Error creating plan.'
  } finally {
    creating.value = false
  }
}

/**
 * -----------------------------------------------------
 * Delete Plan
 * -----------------------------------------------------
 */
async function deletePlan(id: number) {
  if (!confirm('Delete this savings plan?')) return

  await fetch(`${apiBase}/savings-plans/${id}`, {
    method: 'DELETE',
    credentials: 'include'
  })

  await loadPlans()
}

/**
 * -----------------------------------------------------
 * Init
 * -----------------------------------------------------
 */
onMounted(async () => {
  await loadMetals()
  await loadPlans()
  loading.value = false
})

/**
 * -----------------------------------------------------
 * Helpers
 * -----------------------------------------------------
 */
const activeMetals = computed(() =>
  metals.value.map((m: any) => ({
    id: m.id,
    name: m.name,
    symbol: m.symbol,
    price_eur: m.price_eur
  }))
)
</script>

<template>
  <div class="p-6 space-y-10">
    <h1 class="text-3xl font-bold">Savings Plans</h1>

    <div v-if="loading" class="text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Create Plan -->
      <div class="border p-6 rounded bg-white shadow-sm">
        <h2 class="text-xl font-semibold mb-4">Create Savings Plan</h2>

        <div
          v-if="createSuccess"
          class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4"
        >
          {{ createSuccess }}
        </div>

        <div
          v-if="createError"
          class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4"
        >
          {{ createError }}
        </div>

        <label class="text-sm">Metal</label>
        <select
          v-model="metalId"
          class="border rounded p-2 w-full mb-3"
        >
          <option :value="null">Select metal</option>
          <option
            v-for="metal in activeMetals"
            :key="metal.id"
            :value="metal.id"
          >
            {{ metal.name }} ({{ metal.symbol }})
          </option>
        </select>

        <label class="text-sm">Amount (EUR)</label>
        <input
          v-model.number="amountEur"
          type="number"
          min="1"
          class="border rounded p-2 w-full mb-3"
          placeholder="e.g., 50"
        />

        <label class="text-sm">Frequency</label>
        <select
          v-model="frequency"
          class="border rounded p-2 w-full mb-4"
        >
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly">Monthly</option>
        </select>

        <button
          @click="createPlan"
          :disabled="creating"
          class="bg-blue-600 text-white px-4 py-2 w-full rounded hover:bg-blue-700 disabled:bg-blue-300"
        >
          {{ creating ? 'Creating...' : 'Create Plan' }}
        </button>
      </div>

      <!-- Plans List -->
      <div class="border p-6 rounded bg-white shadow-sm">
        <h2 class="text-xl font-semibold mb-4">Active Savings Plans</h2>

        <div v-if="plans.length === 0" class="text-gray-500">
          No active plans. Create one to start automated investing.
        </div>

        <div class="space-y-4" v-else>
          <div
            v-for="plan in plans"
            :key="plan.id"
            class="border p-4 rounded bg-gray-50 flex justify-between items-center"
          >
            <div>
              <p class="font-semibold">
                {{ plan.metal.name }} ({{ plan.metal.symbol }})
              </p>
              <p class="text-sm text-gray-600">
                €{{ plan.amount_eur }} — {{ plan.frequency }}
              </p>
              <p class="text-xs text-gray-500">
                Next execution: {{ plan.next_execution_at }}
              </p>
            </div>

            <button
              class="text-red-600 text-sm hover:underline"
              @click="deletePlan(plan.id)"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>