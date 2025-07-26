import { navigateTo } from '#app'

type SecureFetchOptions = RequestInit & {
  headers?: Record<string, string>
}

export async function secureFetch(
  apiUrl: string,
  fetchOptions: SecureFetchOptions = {},
  allowedRoles: string[] = []
): Promise<any> {
  const config = useRuntimeConfig() // ✅ Gọi ở đây
  const apiBaseUrl = config.public.apiBaseUrl

  const token = localStorage.getItem('access_token')
  if (!token) {
    await navigateTo('/unauthorized', { replace: true })
    throw new Error('Chưa đăng nhập')
  }

  // Gọi API /me
  const meRes = await fetch(`${apiBaseUrl}/me`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  })

  if (!meRes.ok) {
    await navigateTo('/unauthorized', { replace: true })
    throw new Error('Không xác thực được người dùng')
  }

  const meData = await meRes.json()
  const role = meData?.data?.role
  if (!role) {
    await navigateTo('/unauthorized', { replace: true })
    throw new Error('Không lấy được vai trò người dùng')
  }

  if (allowedRoles.length && !allowedRoles.includes(role)) {
    await navigateTo('/unauthorized', { replace: true })
    throw new Error('Bạn không có quyền truy cập')
  }

  // Gọi API chính
  console.log('secureFetch calling API:', apiUrl);
  const response = await fetch(apiUrl, {
    ...fetchOptions,
    headers: {
      ...(fetchOptions.headers || {}),
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  })

  console.log('secureFetch response status:', response.status, response.statusText);
  if (!response.ok) {
    console.error('secureFetch HTTP error:', response.status, response.statusText);
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const data = await response.json(); // Đảm bảo parse JSON
  console.log('Raw response data from secureFetch (before return):', data); // Log trước khi return
  return data; // Trả về toàn bộ dữ liệu JSON
}