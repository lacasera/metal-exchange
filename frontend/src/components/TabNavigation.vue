<script setup lang="ts">
interface Props {
  activeTab: 'trade' | 'plans' | 'charts'
}

interface Emits {
  (e: 'update:activeTab', tab: 'trade' | 'plans' | 'charts'): void
  (e: 'ensureSavingsLoaded'): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()

function selectTab(tab: 'trade' | 'plans' | 'charts') {
  emit('update:activeTab', tab)
  if (tab === 'plans') {
    emit('ensureSavingsLoaded')
  }
}
</script>

<template>
  <div class="flex gap-6 border-b pb-2">
    <button
      @click="selectTab('trade')"
      :class="[
        'pb-2 px-1',
        activeTab === 'trade' ? 'border-b-2 border-blue-600 font-semibold' : 'text-gray-500'
      ]"
    >
      Trades
    </button>

    <button
      @click="selectTab('plans')"
      :class="[
        'pb-2 px-1',
        activeTab === 'plans' ? 'border-b-2 border-blue-600 font-semibold' : 'text-gray-500'
      ]"
    >
      Savings Plans
    </button>

    <button
      @click="selectTab('charts')"
      :class="[
        'pb-2 px-1',
        activeTab === 'charts' ? 'border-b-2 border-blue-600 font-semibold' : 'text-gray-500'
      ]"
    >
      Charts
    </button>
  </div>
</template>