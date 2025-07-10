import { useRuntimeConfig, useState } from '#imports'
import axios from 'axios'

const TTL = 60 * 60 * 1000 // 1 tiếng = 3600000ms

interface CachedData<T> {
  data: T
  timestamp: number
}

export async function useApiCache<T = any>(url: string): Promise<T> {
  const cache = useState<Record<string, CachedData<T>>>('api-cache', () => ({}))

  const cached = cache.value[url]

  // Nếu có cache và chưa hết hạn
  if (cached && Date.now() - cached.timestamp < TTL) {
    return cached.data
  }

  // Gọi API thật
  const config = useRuntimeConfig()
  const fullUrl = url.startsWith('http') ? url : `${config.public.apiBaseUrl}${url}`
  const { data } = await axios.get<T>(fullUrl)

  // Lưu cache
  cache.value[url] = {
    data,
    timestamp: Date.now(),
  }

  return data
}
