import { useAuth } from '../stores/auth'

interface ApiRequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE'
  body?: any
  headers?: Record<string, string>
}

interface ApiResponse<T = any> {
  success: boolean
  message: string
  data?: T
  error?: string
}

export function useApi() {
  const auth = useAuth()
  const apiBase = import.meta.env.VITE_API_BASE_URL

  const createHeaders = (additionalHeaders: Record<string, string> = {}) => ({
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...(auth.token && { 'Authorization': `Bearer ${auth.token}` }),
    ...additionalHeaders
  })

  const makeRequest = async <T = any>(
    endpoint: string, 
    options: ApiRequestOptions = {}
  ): Promise<ApiResponse<T>> => {
    const { method = 'GET', body, headers: additionalHeaders = {} } = options

    try {
      const response = await fetch(`${apiBase}${endpoint}`, {
        method,
        credentials: 'include',
        headers: createHeaders(additionalHeaders),
        ...(body && { body: JSON.stringify(body) })
      })

      const json = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: json.message || json.error || 'Request failed',
          error: json.message || json.error
        }
      }

      return {
        success: true,
        message: json.message || 'Success',
        data: json.data ?? json
      }
    } catch (error) {
      console.error(`API request failed for ${endpoint}:`, error)
      return {
        success: false,
        message: 'Network error. Please try again.',
        error: error instanceof Error ? error.message : 'Unknown error'
      }
    }
  }

  // Convenience methods
  const get = <T = any>(endpoint: string, headers?: Record<string, string>) =>
    makeRequest<T>(endpoint, { method: 'GET', headers })

  const post = <T = any>(endpoint: string, body?: any, headers?: Record<string, string>) =>
    makeRequest<T>(endpoint, { method: 'POST', body, headers })

  const put = <T = any>(endpoint: string, body?: any, headers?: Record<string, string>) =>
    makeRequest<T>(endpoint, { method: 'PUT', body, headers })

  const del = <T = any>(endpoint: string, headers?: Record<string, string>) =>
    makeRequest<T>(endpoint, { method: 'DELETE', headers })

  return {
    makeRequest,
    get,
    post,
    put,
    delete: del,
    isAuthenticated: () => !!auth.token
  }
}