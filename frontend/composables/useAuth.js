// frontend/composables/useAuth.js
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from './useToast';

export function useAuth() {
  const router = useRouter();
  const { toast } = useToast();
  const isAuthenticated = ref(!!localStorage.getItem('access_token'));

  const logout = async () => {
    try {
      localStorage.removeItem('access_token');
      isAuthenticated.value = false;
      toast('success', 'Đã đăng xuất thành công');
      await router.push('/login');
    } catch (error) {
      console.error('Lỗi khi đăng xuất:', error);
      toast('error', 'Có lỗi xảy ra khi đăng xuất');
    }
  };

  return {
    isAuthenticated,
    logout,
  };
}