<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useAuth } from "../stores/auth";
import { usePrices } from "../composables/usePrices";
import { usePortfolio } from "../composables/usePortfolio";
import { useTrades } from "../composables/useTrades";
import { useSavingsPlans } from "../composables/useSavingsPlans";
import PriceCard from "../components/PriceCard.vue";
import TabNavigation from "../components/TabNavigation.vue";
import TradeForm from "../components/TradeForm.vue";
import PortfolioPanel from "../components/PortfolioPanel.vue";
import SavingsForm from "../components/SavingsForm.vue";
import SavingsPlansList from "../components/SavingsPlansList.vue";
import ChartsPanel from "../components/ChartsPanel.vue";

/**
 * -------------------------------------------------------
 * Composables
 * -------------------------------------------------------
 */
const auth = useAuth();
const { prices, loading } = usePrices();
const { portfolio, loadingPortfolio, loadPortfolio } = usePortfolio();
const {
  metalId,
  tradeAmount,
  tradeType,
  submitting,
  tradeError,
  tradeSuccess,
  activeMetals,
  createCalculation,
  submitTrade,
} = useTrades();

const {
  savingsPlans,
  loadingSavings,
  savingsError,
  savingsSuccess,
  planSubmitting,
  planName,
  planMetalId,
  planAmount,
  planFrequency,
  createSavingsPlan,
  deleteSavingsPlan,
  pauseSavingsPlan,
  resumeSavingsPlan,
  ensureSavingsLoaded,
} = useSavingsPlans();

/**
 * -------------------------------------------------------
 * Local state
 * -------------------------------------------------------
 */
const activeTab = ref<"trade" | "plans" | "charts">("trade");

// Create calculation based on current prices
const calculation = computed(() => createCalculation(prices.value).value);

/**
 * -------------------------------------------------------
 * Event handlers
 * -------------------------------------------------------
 */
const handleTradeSubmit = async () => {
  await submitTrade(loadPortfolio);
};

const handleTabChange = (tab: "trade" | "plans" | "charts") => {
  activeTab.value = tab;
  if (tab === "plans") {
    ensureSavingsLoaded();
  }
};

/**
 * -------------------------------------------------------
 * Setup and initialization
 * -------------------------------------------------------
 */
onMounted(() => {
  loadPortfolio();
});
</script>

<template>
  <div class="p-6 space-y-10">
    <h1 class="text-3xl font-bold">Live Metal Prices</h1>

    <div v-if="loading" class="text-gray-500">Connecting to live feed...</div>

    <!-- Price Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <PriceCard v-for="metal in prices" :key="metal.id" :metal="metal" />
    </div>

    <!-- Tabs -->
    <TabNavigation
      :active-tab="activeTab"
      @update:activeTab="handleTabChange"
    />

    <!-- Trade + Portfolio Section -->
    <div
      v-if="activeTab === 'trade'"
      class="grid grid-cols-1 md:grid-cols-2 gap-6"
    >
      <TradeForm
        :metal-id="metalId"
        :trade-amount="tradeAmount"
        :trade-type="tradeType"
        :submitting="submitting"
        :trade-error="tradeError"
        :trade-success="tradeSuccess"
        :calculation="calculation"
        :active-metals="activeMetals"
        :prices="prices"
        :is-authenticated="!!auth.user"
        @update:metalId="metalId = $event"
        @update:tradeAmount="tradeAmount = $event"
        @update:tradeType="tradeType = $event"
        @submitTrade="handleTradeSubmit"
      />

      <PortfolioPanel
        :portfolio="portfolio"
        :loading-portfolio="loadingPortfolio"
      />
    </div>

    <!-- Savings Plans Tab Content -->
    <div
      v-if="activeTab === 'plans'"
      class="grid grid-cols-1 lg:grid-cols-2 gap-6"
    >
      <SavingsForm
        :plan-name="planName"
        :plan-metal-id="planMetalId"
        :plan-amount="planAmount"
        :plan-frequency="planFrequency"
        :plan-submitting="planSubmitting"
        :savings-error="savingsError"
        :savings-success="savingsSuccess"
        :active-metals="activeMetals"
        :is-authenticated="!!auth.user"
        @update:planName="planName = $event"
        @update:planMetalId="planMetalId = $event"
        @update:planAmount="planAmount = $event"
        @update:planFrequency="planFrequency = $event"
        @createSavingsPlan="createSavingsPlan"
      />

      <SavingsPlansList
        :savings-plans="savingsPlans"
        :is-authenticated="!!auth.user"
        :loading="loadingSavings"
        @deleteSavingsPlan="deleteSavingsPlan"
        @pauseSavingsPlan="pauseSavingsPlan"
        @resumeSavingsPlan="resumeSavingsPlan"
      />
    </div>

    <!-- Charts Tab Content -->
    <div v-if="activeTab === 'charts'">
      <ChartsPanel :active-metals="activeMetals" />
    </div>
  </div>
</template>

<style>
body {
  background: #f4f5f7;
}
</style>
