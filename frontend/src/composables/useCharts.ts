import { ref } from 'vue'
import { useApi } from './useApi'

export interface ChartDataPoint {
  timestamp: string
  price: number
  volume?: number
}

export interface ChartData {
  symbol: string
  name: string
  data: ChartDataPoint[]
  currentPrice: number
  change: number
  changePercent: number
}

export function useCharts() {
  const api = useApi()
  
  const chartData = ref<Record<string, ChartData>>({})
  const loadingCharts = ref(false)
  const chartsError = ref('')
  
  const timeframes = [
    { label: '10 min', value: '10min' },
    { label: 'Hour', value: 'hour' },
    { label: 'Today', value: 'today' },
    { label: 'Week', value: 'week' },
    { label: 'Month', value: 'month' },
  ]

  const selectedTimeframe = ref('today')

  const loadChartData = async (symbols: string[] = ['XAU', 'XAG', 'XPT', 'XPD']) => {
    loadingCharts.value = true
    chartsError.value = ''

    try {
      // Calculate start date based on selected timeframe
      const getStartDate = (timeframe: string): string => {
        const now = new Date()
        const startDate = new Date(now)
        
        switch (timeframe) {
          case '10min':
            startDate.setMinutes(now.getMinutes() - 10)
            break
          case 'hour':
            startDate.setHours(now.getHours() - 1)
            break
          case 'today':
            startDate.setHours(0, 0, 0, 0)
            break
          case 'week':
            startDate.setDate(now.getDate() - 7)
            break
          case 'month':
            startDate.setMonth(now.getMonth() - 1)
            break
          case 'quarter':
            startDate.setMonth(now.getMonth() - 3)
            break
          case 'year_start':
            startDate.setMonth(0, 1)
            startDate.setHours(0, 0, 0, 0)
            break
          case '1year':
            startDate.setFullYear(now.getFullYear() - 1)
            break
          case '5years':
            startDate.setFullYear(now.getFullYear() - 5)
            break
          case 'max':
            startDate.setFullYear(2020)
            break
          default:
            startDate.setHours(0, 0, 0, 0)
        }
        
        // Format as Y-m-d\TH:i:s\Z (remove milliseconds for backend compatibility)
        return startDate.toISOString().replace(/\.\d{3}Z$/, 'Z')
      }

      // Map timeframe to interval
      const getInterval = (timeframe: string): string => {
        const intervalMap: Record<string, string> = {
          '10min': '1m',
          'hour': '5m',
          'today': '15m',
          'week': '1h',
          'month': '4h',
          'quarter': '1d',
          'year_start': '1d',
          '1year': '1w',
          '5years': '30d',
          'max': '30d'
        }
        return intervalMap[timeframe] || '15m'
      }

      const startDate = getStartDate(selectedTimeframe.value)
      const interval = getInterval(selectedTimeframe.value)

      const promises = symbols.map(async (symbol) => {
        const params = new URLSearchParams({
          symbol: symbol,
          start_date: startDate,
          interval: interval
        })
        
        console.log(`Loading chart data for ${symbol} with params:`, params.toString())
        const response = await api.get(`/metal-prices/chart?${params.toString()}`)

        console.log(`API response for ${symbol}:`, response)
        
        if (response.success && response.data) {
          const apiData = response.data
          console.log(`Chart data received for ${symbol}:`, apiData)
          
          // Extract chart_data array from the API response
          const chartDataArray = apiData.chart_data || []
          
          // Transform API response to our ChartDataPoint format
          const chartPoints: ChartDataPoint[] = chartDataArray.map((point: any) => ({
            timestamp: point.date || new Date(point.timestamp).toISOString(),
            price: parseFloat(point.close || point.average || point.open || 0),
            volume: point.volume || 0
          }))

          // Use summary data if available, otherwise calculate from chart points
          let currentPrice = 0
          let change = 0
          let changePercent = 0

          if (apiData.summary) {
            currentPrice = parseFloat(apiData.summary.last_price || 0)
            change = parseFloat(apiData.summary.change || 0)
            changePercent = parseFloat(apiData.summary.change_percent || 0)
          } else if (chartPoints.length > 0) {
            currentPrice = chartPoints[chartPoints.length - 1]?.price ?? 0
            const previousPrice = chartPoints.length > 1 ? chartPoints[chartPoints.length - 2]?.price ?? currentPrice : currentPrice
            change = currentPrice - previousPrice
            changePercent = previousPrice > 0 ? (change / previousPrice) * 100 : 0
          }

          return {
            symbol,
            name: getMetalName(symbol),
            data: chartPoints,
            currentPrice,
            change,
            changePercent
          }
        } else {
          console.error(`Failed to load chart data for ${symbol}:`, response.message || 'Unknown error')
        }
        return null
      })

      const results = await Promise.all(promises)
      
      // Filter out null results and populate chart data
      const validResults = results.filter(result => result !== null) as ChartData[]
      
      validResults.forEach((result) => {
        chartData.value[result.symbol] = result
      })
    } catch (error) {
      console.error('Failed to load chart data:', error)
      chartsError.value = 'Failed to load chart data. Please try again.'
    } finally {
      loadingCharts.value = false
    }
  }

  const getMetalName = (symbol: string): string => {
    const metalNames: Record<string, string> = {
      XAU: 'Gold',
      XAG: 'Silver', 
      XPT: 'Platinum',
      XPD: 'Palladium'
    }
    return metalNames[symbol] || symbol
  }



  const formatChartData = (data: ChartDataPoint[]) => {
    return data.map(point => [
      new Date(point.timestamp).getTime(),
      point.price
    ])
  }

  const getChartOption = (symbol: string) => {
    const data = chartData.value[symbol]
    if (!data) return null

    const formattedData = formatChartData(data.data)
    const isPositive = data.change >= 0

    return {
      animation: false,
      grid: {
        left: '3%',
        right: '3%',
        bottom: '10%',
        top: '15%',
        containLabel: true
      },
      tooltip: {
        trigger: 'axis',
        backgroundColor: 'rgba(50, 50, 50, 0.9)',
        borderColor: '#333',
        textStyle: {
          color: '#fff'
        },
        formatter: (params: any) => {
          const point = params[0]
          const date = new Date(point.data[0]).toLocaleString()
          const price = point.data[1].toFixed(2)
          return `${date}<br/>Price: €${price}`
        }
      },
      xAxis: {
        type: 'time',
        boundaryGap: false,
        axisLine: {
          lineStyle: {
            color: '#ccc'
          }
        },
        axisLabel: {
          color: '#666',
          fontSize: 12
        },
        splitLine: {
          show: false
        }
      },
      yAxis: {
        type: 'value',
        scale: true,
        axisLine: {
          lineStyle: {
            color: '#ccc'
          }
        },
        axisLabel: {
          color: '#666',
          fontSize: 12,
          formatter: (value: number) => `€${value.toFixed(0)}`
        },
        splitLine: {
          lineStyle: {
            color: '#f0f0f0',
            type: 'dashed'
          }
        }
      },
      series: [{
        type: 'line',
        data: formattedData,
        smooth: true,
        symbol: 'none',
        lineStyle: {
          width: 2,
          color: isPositive ? '#10B981' : '#EF4444'
        },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [{
              offset: 0,
              color: isPositive ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)'
            }, {
              offset: 1,
              color: isPositive ? 'rgba(16, 185, 129, 0.05)' : 'rgba(239, 68, 68, 0.05)'
            }]
          }
        }
      }]
    }
  }

  return {
    chartData,
    loadingCharts,
    chartsError,
    timeframes,
    selectedTimeframe,
    loadChartData,
    getChartOption,
    getMetalName
  }
}