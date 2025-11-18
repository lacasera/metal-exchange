<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { use } from "echarts/core";
import { LineChart } from "echarts/charts";
import { GridComponent, TooltipComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import VChart from "vue-echarts";
import { useCharts } from "../composables/useCharts";

// Register ECharts components
use([LineChart, GridComponent, TooltipComponent, CanvasRenderer]);

interface Props {
  activeMetals: Array<{ id: number; symbol: string; name: string }>;
}

defineProps<Props>();

const {
  chartData,
  loadingCharts,
  chartsError,
  timeframes,
  selectedTimeframe,
  loadChartData,
  getChartOption,
} = useCharts();

// Track which metals have their charts enabled (all enabled by default)
const enabledCharts = ref<Record<string, boolean>>({
  XAU: true,
  XAG: true,
  XPT: true,
  XPD: true,
});

// Load chart data when component mounts or timeframe changes
onMounted(() => {
  loadChartData();
});

watch(selectedTimeframe, () => {
  loadChartData();
});

const handleTimeframeChange = (timeframe: string) => {
  selectedTimeframe.value = timeframe;
};

const toggleChart = (symbol: string) => {
  enabledCharts.value[symbol] = !enabledCharts.value[symbol];
};

const formatChange = (change: number, changePercent: number) => {
  const sign = change >= 0 ? "+" : "";
  return `${sign}€${change.toFixed(2)} (${sign}${changePercent.toFixed(2)}%)`;
};

const getMetalIcon = (symbol: string) => {
  const icons: Record<string, string> = {
    XAU: "🥇",
    XAG: "🥈",
    XPT: "⚪",
    XPD: "🔘",
  };
  return icons[symbol] || "💎";
};

const getMetalColor = (symbol: string) => {
  const colors: Record<
    string,
    { primary: string; secondary: string; gradient: string }
  > = {
    XAU: {
      primary:
        "border-yellow-500 bg-gradient-to-br from-yellow-50 to-yellow-100",
      secondary: "text-yellow-700",
      gradient: "from-yellow-400 to-yellow-600",
    },
    XAG: {
      primary: "border-gray-400 bg-gradient-to-br from-gray-50 to-gray-100",
      secondary: "text-gray-700",
      gradient: "from-gray-400 to-gray-600",
    },
    XPT: {
      primary: "border-slate-500 bg-gradient-to-br from-slate-50 to-slate-100",
      secondary: "text-slate-700",
      gradient: "from-slate-400 to-slate-600",
    },
    XPD: {
      primary: "border-zinc-500 bg-gradient-to-br from-zinc-50 to-zinc-100",
      secondary: "text-zinc-700",
      gradient: "from-zinc-400 to-zinc-600",
    },
  };
  return colors[symbol] || colors.XAU;
};
</script>

<template>
  <div class="space-y-8">
    <!-- Error Message -->
    <div
      v-if="chartsError"
      class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg"
    >
      <div class="flex">
        <div class="shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700">{{ chartsError }}</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loadingCharts" class="text-center py-12">
      <div
        class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white transition ease-in-out duration-150"
      >
        <svg
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        Loading precious metals data...
      </div>
    </div>

    <!-- Charts Content -->
    <div v-else class="space-y-8">
      <!-- Timeframe Selection -->
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Precious Metals Charts</h2>
        <div class="flex bg-gray-100 p-1 rounded-xl shadow-inner">
          <button
            v-for="timeframe in timeframes"
            :key="timeframe.value"
            @click="handleTimeframeChange(timeframe.value)"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
              selectedTimeframe === timeframe.value
                ? 'bg-white text-blue-600 shadow-md transform scale-105'
                : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50',
            ]"
          >
            {{ timeframe.label }}
          </button>
        </div>
      </div>

      <div
        class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 shadow-inner"
      >
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
          Market Overview
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div
            v-for="metal in activeMetals"
            :key="`summary-${metal.symbol}`"
            class="text-center p-3 bg-white rounded-lg shadow-sm"
          >
            <div class="text-xs text-gray-500 mb-1">{{ metal.symbol }}</div>
            <div v-if="chartData[metal.symbol]" class="space-y-1">
              <div class="font-bold text-sm">
                €{{ chartData[metal.symbol]?.currentPrice?.toFixed(0) ?? "0" }}
              </div>
              <div
                :class="[
                  'text-xs',
                  (chartData[metal.symbol]?.change ?? 0) >= 0
                    ? 'text-green-600'
                    : 'text-red-600',
                ]"
              >
                {{ (chartData[metal.symbol]?.changePercent ?? 0).toFixed(1) }}%
              </div>
            </div>
            <div v-else class="text-xs text-gray-400">--</div>
          </div>
        </div>
      </div>

      <!-- Metal Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="metal in activeMetals"
          :key="metal.symbol"
          :class="[
            'relative rounded-2xl shadow-lg border-2 transition-all duration-300 overflow-hidden',
            getMetalColor(metal.symbol)?.primary || '',
            enabledCharts[metal.symbol]
              ? 'transform hover:scale-105 hover:shadow-xl'
              : 'opacity-60 hover:opacity-80',
          ]"
        >
          <!-- Card Header -->
          <div class="p-6 pb-4">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="text-3xl">{{ getMetalIcon(metal.symbol) }}</div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">
                    {{ metal.name }}
                  </h3>
                  <p class="text-sm text-gray-600">{{ metal.symbol }}</p>
                </div>
              </div>

              <!-- Toggle Button -->
              <button
                @click="toggleChart(metal.symbol)"
                :class="[
                  'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                  enabledCharts[metal.symbol] ? 'bg-blue-600' : 'bg-gray-300',
                ]"
              >
                <span
                  :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    enabledCharts[metal.symbol]
                      ? 'translate-x-6'
                      : 'translate-x-1',
                  ]"
                />
              </button>
            </div>

            <!-- Price Display -->
            <div v-if="chartData[metal.symbol]" class="space-y-2">
              <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-bold text-gray-900">
                  €{{
                    chartData[metal.symbol]?.currentPrice?.toFixed(2) ?? "0.00"
                  }}
                </span>
                <span class="text-sm text-gray-600">EUR/oz</span>
              </div>

              <div
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold',
                  (chartData[metal.symbol]?.change ?? 0) >= 0
                    ? 'bg-green-100 text-green-800 border border-green-300'
                    : 'bg-red-100 text-red-800 border border-red-300',
                ]"
              >
                <svg
                  :class="[
                    'w-4 h-4 mr-1',
                    (chartData[metal.symbol]?.change ?? 0) >= 0
                      ? 'transform rotate-0'
                      : 'transform rotate-180',
                  ]"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
                {{
                  formatChange(
                    chartData[metal.symbol]?.change ?? 0,
                    chartData[metal.symbol]?.changePercent ?? 0
                  )
                }}
              </div>
            </div>

            <div v-else class="animate-pulse">
              <div class="h-8 bg-gray-200 rounded mb-2"></div>
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            </div>
          </div>

          <!-- Chart Area -->
          <div v-if="enabledCharts[metal.symbol]" class="px-6 pb-6">
            <div
              v-if="chartData[metal.symbol]"
              class="bg-white/70 backdrop-blur-sm rounded-xl p-4 shadow-inner"
            >
              <div class="h-64">
                <VChart
                  :option="getChartOption(metal.symbol) || {}"
                  :autoresize="true"
                  class="w-full h-full"
                />
              </div>
            </div>

            <div
              v-else
              class="bg-white/70 backdrop-blur-sm rounded-xl p-8 shadow-inner text-center"
            >
              <div class="text-gray-500 text-sm">No chart data available</div>
            </div>
          </div>

          <!-- Collapsed State -->
          <div v-else class="px-6 pb-6">
            <div
              class="bg-white/50 backdrop-blur-sm rounded-xl p-8 text-center"
            >
              <div class="text-gray-400 text-sm">
                Chart disabled - toggle to view
              </div>
            </div>
          </div>

          <!-- Gradient Overlay -->
          <div
            :class="[
              'absolute inset-x-0 top-0 h-1 bg-gradient-to-r',
              getMetalColor(metal.symbol)?.gradient || '',
            ]"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom styling for charts */
.v-chart {
  min-height: 200px;
}
</style>
