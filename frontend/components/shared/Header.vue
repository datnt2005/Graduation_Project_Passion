<template>
  <div>
    <!-- Thanh trên cùng -->
    <header class="bg-[#1BA0E2] text-white text-sm py-2">
      <div class="container mx-auto flex justify-between items-center px-4">
        <div class="space-x-2">
          <NuxtLink
            to="/Seller"
            class="hover:underline inline-flex items-center gap-1"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 10h4l3 10h8l3-10h4"
              />
            </svg>
            Đăng ký bán hàng
          </NuxtLink>

          <a href="#" class="hover:underline inline-flex items-center gap-1">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5"
              />
            </svg>
            Kết nối
            <!-- Facebook icon -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="w-4 h-4"
            >
              <path
                d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"
              />
            </svg>
            <!-- Instagram icon -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="w-4 h-4"
            >
              <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
              <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
            </svg>
          </a>
        </div>

        <div class="hidden sm:flex space-x-4">
          <a href="#" class="hover:underline inline-flex items-center gap-1">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.437L4 17h5"
              />
            </svg>
            Thông báo
          </a>
          <NuxtLink
            to="/support"
            class="hover:underline inline-flex items-center gap-1"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 1.105-1.12 2-2.5 2h-13C4.12 14 3 13.105 3 12s1.12-2 2.5-2h13c1.38 0 2.5.895 2.5 2z"
              />
            </svg>
            Hỗ trợ
          </NuxtLink>
          <NuxtLink
            to="/auth/login"
            class="hover:underline inline-flex items-center gap-1"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
            Đăng nhập
          </NuxtLink>
          <NuxtLink
            to="/auth/register"
            class="hover:underline inline-flex items-center gap-1"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              />
            </svg>
            Đăng ký
          </NuxtLink>
          <!-- Thêm liên kết Quên mật khẩu nếu muốn -->
        </div>
      </div>
    </header>

    <!-- Thanh giữa -->
    <div class="bg-white shadow-sm">
      <div
        class="container mx-auto flex items-center justify-between px-4 py-3"
      >
        <!-- Logo -->
        <div class="flex items-center space-x-4">
          <div
            class="w-10 h-10 bg-gray-300 rounded text-center text-xs flex items-center justify-center"
          >
            <NuxtLink to="/">Logo</NuxtLink>
          </div>
        </div>
        <!-- NAVIGATION -->
        <div class="relative group ml-20 hidden md:block">
          <a
            href="#"
            class="flex items-center gap-1 text-gray-700 hover:text-blue-600 font-semibold"
          >
            Danh mục
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="w-5 h-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
              />
            </svg>
          </a>

          <!-- MEGA MENU -->
          <div
            class="absolute left-0 mt-6 w-[1200px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-6 grid grid-cols-5 gap-6"
          >
            <!-- Cột 1 -->
            <div>
              <h4 class="font-bold mb-2">Sản phẩm mới</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Giá tốt</a></li>
                <li><a href="#" class="hover:underline">Sale</a></li>
              </ul>
            </div>

            <!-- Cột 2 -->
            <div>
              <h4 class="font-bold mb-2">Danh mục sản phẩm</h4>
              <ul class="text-gray-700 space-y-1">
                <li>
                  <a href="#" class="hover:underline">Áo phông/ Áo thun</a>
                </li>
                <li><a href="#" class="hover:underline">Áo polo</a></li>
                <li>
                  <a href="#" class="hover:underline">Áo sơ mi & Áo kiểu</a>
                </li>
                <li><a href="#" class="hover:underline">Bộ quần áo</a></li>
                <li><a href="#" class="hover:underline">Canifa Active</a></li>
                <li><a href="#" class="hover:underline">Đồ ngủ</a></li>
                <li><a href="#" class="hover:underline">Chống nắng</a></li>
              </ul>
            </div>

            <!-- Cột 3 -->
            <div>
              <h4 class="font-bold mb-2">Phụ kiện</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Chăn</a></li>
                <li><a href="#" class="hover:underline">Khăn mặt</a></li>
                <li><a href="#" class="hover:underline">Khăn tắm</a></li>
                <li><a href="#" class="hover:underline">Khăn quàng cổ</a></li>
              </ul>
              <h4 class="font-bold mt-4 mb-2">Bộ sưu tập</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Disney</a></li>
                <li><a href="#" class="hover:underline">Doraemon</a></li>
              </ul>
            </div>

            <!-- Cột 4 & 5 (Hình ảnh) -->
            <div class="col-span-2 grid grid-cols-2 gap-4">
              <img
                src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp"
                alt="Nữ 1"
                class="rounded-md object-cover w-full h-48"
              />
              <img
                src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp"
                alt="Nữ 2"
                class="rounded-md object-cover w-full h-48"
              />
            </div>
          </div>
        </div>

      <!-- Tìm kiếm (desktop) -->
      <div class="flex-1 mx-4 hidden sm:flex justify-center">
        <form @submit.prevent="handleSearch" class="flex w-full max-w-[500px] border border-gray-300 rounded-full overflow-hidden bg-white shadow-sm">
          <!-- Icon tìm kiếm -->
          <div class="flex items-center px-3 text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          
          <!-- Ô nhập -->
          <input
            type="text"
            v-model="input"
            placeholder="Tìm kiếm sản phẩm..."
            class="flex-grow px-2 py-2 text-sm focus:outline-none"
          />

          <!-- Nút tìm -->
          <button
            type="submit"
            class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 transition whitespace-nowrap"
          >
            Tìm kiếm
          </button>
        </form>
      </div>



        <!-- Menu PC -->
        <div
          class="hidden sm:flex items-center gap-x-6 text-sm font-medium text-gray-700"
        >
          <!-- Trang chủ -->
          <NuxtLink
            href="/"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
              />
            </svg>

            Trang chủ
          </NuxtLink>

          <!-- Tài khoản -->
          <div class="relative group inline-block">
            <div
              class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                />
              </svg>

              Tài khoản
            </div>

            <!-- Dropdown nếu có -->
            <ul
              class="absolute left-0 top-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded shadow-lg w-48 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-in-out z-50 text-sm text-gray-700"
            >
              <li>
                <a
                  href="/users/profile"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Thông tin tài khoản</a
                >
              </li>
              <li>
                <a
                  href="/users/orders"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Đơn hàng của tôi</a
                >
              </li>
              <li>
                <a href="/support" class="block px-4 py-2 hover:bg-gray-100"
                  >Trung tâm hỗ trợ</a
                >
              </li>
              <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100"
                  >Đăng xuất</a
                >
              </li>
            </ul>
          </div>

          <!-- Giỏ hàng -->
          <NuxtLink
            href="/cart"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"
              />
            </svg>

            Giỏ hàng
          </NuxtLink>
        </div>

        <!-- Icon menu mobile -->
        <div class="sm:hidden">
          <button @click="isMobileMenuOpen = true">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 text-gray-700"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Tìm kiếm (mobile) -->
      <div class="px-4 pb-3 sm:hidden">
        <div class="relative">
          <input
            type="text"
            v-model="input"
            @input="updateSearch"
            placeholder="Tìm kiếm"
            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-full focus:outline-none"
          />
          <button
            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#1BA0E2] p-2 rounded-full hover:bg-blue-600"
          >
            <!-- Icon kính lúp SVG nhỏ gọn -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4 text-white"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Modal Menu -->
    <div
      v-if="isMobileMenuOpen"
      class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-start justify-end sm:hidden"
    >
      <div class="w-3/4 bg-white shadow-md h-full p-4 relative">
        <!-- Close button -->
        <button
          @click="isMobileMenuOpen = false"
          class="absolute top-4 right-4 text-gray-600 hover:text-black"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <!-- Menu Items -->
        <div class="space-y-4 mt-10 text-sm">
          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'house']" /> Trang chủ
          </a>
          <div class="infor relative group inline-block">
            <!-- Nút tài khoản -->
            <a
              href="#"
              class="block text-gray-700 hover:text-blue-600 font-semibold"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                />
              </svg>
              Tài khoản
            </a>

            <!-- Dropdown mượt mà -->
            <ul
              class="absolute left-0 top-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded shadow-lg w-48 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-in-out z-50"
            >
              <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100"
                  >Thông tin tài khoản</a
                >
              </li>
              <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100"
                  >Đơn hàng của tôi</a
                >
              </li>
              <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100"
                  >Trung tâm hỗ trợ</a
                >
              </li>
              <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100"
                  >Đăng xuất</a
                >
              </li>
            </ul>
          </div>

          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'cart-shopping']" /> Giỏ hàng
          </a>
          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'bell']" /> Thông báo
          </a>
          <NuxtLink
            to="/support"
            class="block text-gray-700 hover:text-blue-600"
          >
            <font-awesome-icon :icon="['fas', 'info']" /> Hỗ trợ
          </NuxtLink>
          <a href="#" class="block text-gray-700 hover:text-blue-600"
            ><font-awesome-icon :icon="['fas', 'right-to-bracket']" /> Đăng
            nhập</a
          >
          <a href="#" class="block text-gray-700 hover:text-blue-600"
            ><font-awesome-icon :icon="['fas', 'plus']" /> Đăng ký</a
          >
        </div>
      </div>
    </div>
  </div>
  <Features />
</template>

<script setup>
import { ref } from "vue";
import { useSearchStore } from "~/stores/search";
import { useRouter } from "vue-router";

const input = ref("");
const searchStore = useSearchStore();
const router = useRouter();

function handleSearch() {
  if (input.value.trim() === "") return; // gọi .trim() như một hàm
  searchStore.updateSearch(input.value);
  router.push("/product_search");
}

const isMobileMenuOpen = ref(false);
import Features from "~/components/shared/Features.vue";
</script>
