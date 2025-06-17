<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#eaf1fd] to-[#f4f7fd] px-6 py-1"
  >
    <div
      class="max-w-9xl w-full flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-[0_8px_40px_0_rgba(22,61,124,.10)] border border-gray-200 bg-white mx-auto"
    >
      <!-- Bên trái: Logo/brand -->
      <div
        class="hidden md:flex flex-col justify-center items-center w-1/2 p-8 bg-transparent"
      >
        <div class="mb-4 flex flex-col items-center">
          <img src="/images/SellerCenter2.png" alt="" />

          <h1 class="text-xl font-extrabold text-gray-900 mt-3 mb-0">
            Passion
          </h1>
          <div class="text-blue-600 font-bold text-sm py-2">Seller Center</div>
        </div>
      </div>
      <!-- Bên phải: Form -->
      <div
        class="w-full md:w-1/2 flex flex-col justify-center p-8 sm:p-16 bg-white"
      >
        <div class="mx-auto w-full md:w-[80%]">
          <div class="mb-8">
            <div class="text-3xl font-bold text-gray-900 mb-1">Đăng nhập</div>
            <div class="text-gray-500 text-base">
              Truy cập tài khoản bán hàng của bạn
            </div>
          </div>
          <form @submit.prevent="handleSubmit" class="space-y-5">
            <div>
              <label class="block font-semibold text-sm mb-2 text-gray-700"
                ><i class="fa-regular fa-envelope"></i> Địa chỉ email</label
              >
              <input
                type="email"
                v-model="form.email"
                class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                placeholder="Nhập địa chỉ email"
                autocomplete="username"
              />
              <p v-if="errors.email" class="text-red-500 text-sm mt-1">
                {{ errors.email }}
              </p>
            </div>
            <div>
              <label class="block font-semibold text-sm mb-2 text-gray-700"
                ><i class="fa-solid fa-lock"></i> Mật khẩu</label
              >
              <div class="relative">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  placeholder="Nhập mật khẩu"
                  autocomplete="current-password"
                />
                <p v-if="errors.password" class="text-red-500 text-sm mt-1">
                  {{ errors.password }}
                </p>
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-3 text-gray-400 hover:text-gray-700 focus:outline-none"
                  tabindex="-1"
                >
                  <svg
                    v-if="showPassword"
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    />
                  </svg>
                  <svg
                    v-else
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                </button>
              </div>
            </div>
            <div class="flex justify-end mb-2">
              <!-- Sửa lại đúng -->
              <NuxtLink
                to="/forgot-password"
                class="text-gray-500 hover:underline text-sm"
                >Quên mật khẩu?</NuxtLink
              >
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full text-white font-bold py-3 rounded-[11px] mt-2 text-lg transition-all duration-300 shadow disabled:opacity-60 disabled:cursor-not-allowed"
              :style="{
                background: loading
                  ? '#1BA0E2CC'
                  : isHover
                  ? '#1780B6'
                  : '#1BA0E2',
              }"
              @mouseenter="isHover = true"
              @mouseleave="isHover = false"
            >
              <svg
                v-if="loading"
                class="animate-spin h-5 w-5 mr-2 inline-block"
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
                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                ></path>
              </svg>
              {{ loading ? "Đang đăng nhập..." : "Đăng nhập" }}
            </button>
          </form>
          <div class="mt-6 text-center text-gray-500">
            <span class="text-sm">Bạn chưa có tài khoản? </span>
            <NuxtLink
              to="/seller/register"
              class="text-blue-600 hover:underline font-semibold"
              >Đăng ký ngay</NuxtLink
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from 'vue-router'
const router = useRouter()
const form = ref({
  email: "",
  password: "",
});

const showPassword = ref(false);
const loading = ref(false);
const isHover = ref(false);

const errors = ref({
  email: "",
  password: "",
  message: "",
});

async function handleSubmit() {
  loading.value = true;
  errors.value = { email: "", password: "", message: "" };

  try {
    const response = await axios.post(
      "http://localhost:8000/api/sellers/login",
      form.value
    );
    const token = response.data.token;
    const slug = response.data.store_slug; 
    console.log("Login response data:", response.data);
    localStorage.setItem("token", token);

    // 👉 Điều hướng tới trang cửa hàng theo slug
   if (slug) {
    router.push(`/seller/${slug}`);
    } else {
    console.error("Không tìm thấy store_slug trong response", response.data);
    }

  } catch (error) {
    if (error.response?.status === 422) {
      const resErrors = error.response.data.errors;
      errors.value.email = resErrors.email?.[0] || "";
      errors.value.password = resErrors.password?.[0] || "";
    } else if (
      error.response?.status === 401 ||
      error.response?.status === 403
    ) {
      errors.value.message = error.response.data.message;
    } else {
      errors.value.message = "Lỗi hệ thống. Vui lòng thử lại sau.";
    }
  } finally {
    loading.value = false;
  }
}

</script>
