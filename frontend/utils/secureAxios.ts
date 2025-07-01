import axios from 'axios'
import type { AxiosRequestConfig, AxiosResponse } from 'axios'
import { navigateTo } from '#app'

const apiBaseUrl = 'http://localhost:8000/api'

export async function secureAxios<T = any>(
  url: string,
  config: AxiosRequestConfig = {},
  allowedRoles: string[] = []
): Promise<AxiosResponse<T>> {
  const token = localStorage.getItem('access_token')
  if (!token) throw new Error('Chưa đăng nhập')

  // Gọi /me để lấy role
  const meRes = await axios.get(`${apiBaseUrl}/me`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  })

  const role = meRes.data?.data?.role || meRes.data?.role
  if (!role) throw new Error('Không lấy được vai trò người dùng')

  if (allowedRoles.length && !allowedRoles.includes(role)) {
    navigateTo('/unauthorized') //  
    throw new Error('Bạn không có quyền truy cập')
  }

  // Gọi API chính
  return axios({
    ...config,
    url,
    headers: {
      ...(config.headers || {}),
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  })
}
