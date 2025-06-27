// composables/useAuthHeaders.js

export const useAuthHeaders = () => {
  const token = process.client ? localStorage.getItem('access_token') : null

  return {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  }
}
