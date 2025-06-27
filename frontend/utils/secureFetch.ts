// utils/secureFetch.ts

type SecureFetchOptions = RequestInit & {
  headers?: Record<string, string>
}

const apiBaseUrl = 'http://localhost:8000/api'  

export async function secureFetch(
  apiUrl: string,
  fetchOptions: SecureFetchOptions = {},
  allowedRoles: string[] = []
): Promise<Response> {
  const token = localStorage.getItem('access_token')
  if (!token) throw new Error('Chưa đăng nhập')

  // Gọi API /me
  const meRes = await fetch(`${apiBaseUrl}/me`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  })

  if (!meRes.ok) throw new Error('Không xác thực được người dùng')

  const meData = await meRes.json()
const role = meData?.data?.role
  if (!role) throw new Error('Không lấy được vai trò người dùng')

  if (allowedRoles.length && !allowedRoles.includes(role)) {
    throw new Error('Bạn không có quyền truy cập')
  }

  // Gọi API chính
  const response = await fetch(apiUrl, {
    ...fetchOptions,
    headers: {
      ...(fetchOptions.headers || {}),
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  })

  return response
}
