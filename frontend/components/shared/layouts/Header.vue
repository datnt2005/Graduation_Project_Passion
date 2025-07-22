<template>
  <div>
    <!-- Thanh trên cùng (Responsive) -->
    <header class="bg-[#1BA0E2] text-white text-sm py-2">
      <!-- DESKTOP & MOBILE -->
      <div
        class="flex flex-wrap sm:flex-nowrap justify-between items-center px-6 gap-2"
      >
        <!-- BÊN TRÁI - Desktop only -->
        <div
          class="hidden sm:flex items-center flex-wrap gap-x-6 text-white text-sm"
        >
          <div class="flex items-center gap-1">
            <i class="fa-solid fa-phone"></i>
            <span
              >Hỗ trợ: <strong>{{ contactHotline }}</strong></span
            >
          </div>
          <div class="flex items-center gap-1">
            <i class="fa-solid fa-handshake"></i>
            <nuxt-link to="/sell-together-passion" class="hover:underline">Bán hàng cùng Passion</nuxt-link>
          </div>
        </div>

        <!-- BÊN PHẢI -->
        <div class="flex items-center flex-wrap gap-4 ml-auto">
          <!-- THÔNG BÁO -->
          <div class="relative group hidden sm:block">
            <div
              class="cursor-pointer hover:text-white transition-colors duration-200 flex items-center"
              @click="toggleNotificationDropdown"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-7"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M14.25 17.25a2.25 2.25 0 0 1-4.5 0m8.25-5.25v-1.5a6 6 0 1 0-12 0v1.5c0 .621-.252 1.216-.7 1.65L3.63 15.255A.75.75 0 0 0 4.14 16.5h15.72a.75.75 0 0 0 .51-1.245l-1.42-1.605a2.25 2.25 0 0 1-.7-1.65Z"
                />
              </svg>
              <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 min-w-[20px] text-center shadow"
              >
                {{ unreadCount }}
              </span>
            </div>

            <!-- DROPDOWN -->
            <div
              v-if="notificationDropdownOpen"
              class="absolute right-0 top-full mt-2 w-96 max-w-[90vw] bg-white border border-gray-200 rounded shadow-lg z-50 text-sm max-h-96 overflow-auto"
            >
              <div
                v-if="notifications.length === 0"
                class="p-4 text-gray-500 text-center"
              >
                Không có thông báo mới.
              </div>
              <ul v-else class="divide-y divide-gray-100">
                <li
                  v-for="item in notifications"
                  :key="item.id"
                  class="p-3 hover:bg-gray-50 flex gap-3 items-start transition group"
                  :class="{
                    'opacity-60': item.is_read === 1,
                    'cursor-pointer': true,
                  }"
                  @click="
                    item.link
                      ? redirectToLink(item)
                      : openNotificationModal(item)
                  "
                >
                  <img
                    v-if="item.image_url"
                    :src="item.image_url"
                    alt="Ảnh"
                    class="w-12 h-12 object-cover rounded"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center">
                      <span
                        class="text-gray-800 font-semibold truncate"
                        :class="{ 'font-bold': item.is_read === 0 }"
                      >
                        {{ item.title }}
                      </span>
                      <span
                        v-if="item.is_read === 0"
                        class="w-2 h-2 bg-blue-500 rounded-full inline-block"
                      ></span>
                    </div>
                    <p
                      class="text-gray-500 text-sm mt-1 break-words line-clamp-2"
                    >
                      {{ stripHTML(item.content) || "Không có nội dung" }}
                    </p>
                    <p
                      class="text-gray-500 text-xs mt-1 flex justify-between items-center"
                    >
                      <span>{{ item.time_ago || "Vừa xong" }}</span>
                      <button
                        v-if="item.link"
                        @click.stop="openNotificationModal(item)"
                        class="text-blue-600 hover:underline text-xs font-medium"
                      >
                        Xem chi tiết
                      </button>
                    </p>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- NGƯỜI DÙNG - Desktop only -->
          <template v-if="isLoggedIn" class="hidden sm:inline-flex">
            <span class="font-medium whitespace-nowrap hidden sm:inline"
              >Xin chào, <strong>{{ userName }}</strong></span
            >
            <button
              @click="logout"
              class="hover:underline inline-flex items-center gap-1 text-white hidden sm:inline"
            >
              <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" />
              Đăng xuất
            </button>
          </template>
          <template v-else>
            <NuxtLink
              to="#"
              @click.prevent="openLogin"
              class="hover:underline inline-flex items-center gap-1 hidden sm:inline"
            >
              <font-awesome-icon :icon="['fas', 'right-to-bracket']" />
              Đăng nhập
            </NuxtLink>
            <NuxtLink
              to="#"
              @click.prevent="openRegister"
              class="hover:underline inline-flex items-center gap-1 hidden sm:inline"
            >
              <font-awesome-icon :icon="['fas', 'plus']" />
              Đăng ký
            </NuxtLink>
          </template>
        </div>
      </div>

      <!-- MOBILE / IPAD DÒNG BỔ SUNG -->
      <div
        class="sm:hidden relative px-4 text-white text-sm mt-2 h-8 flex items-center justify-center"
      >
        <!-- Ưu đãi: luôn hiển thị và căn giữa -->
        <div class="flex items-center gap-1 justify-center">
          <i class="fa-solid fa-handshake"></i>
          <nuxt-link to="/sell-together-passion" class="hover:underline">Bán hàng cùng Passion</nuxt-link>
        </div>
      </div>

      <!-- MODAL -->
      <Teleport to="body">
        <transition name="fade">
          <div
            v-if="showNotificationModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-2"
          >
            <div
              class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative"
            >
              <button
                @click="showNotificationModal = false"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
              >
                ✕
              </button>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">
                {{ currentNotification?.title || "Không có tiêu đề" }}
              </h3>
              <div v-if="currentNotification?.image_url" class="mb-4">
                <img
                  :src="currentNotification.image_url"
                  alt="Ảnh"
                  class="w-full h-auto rounded-md border object-cover max-h-64 mx-auto"
                />
              </div>
              <div
                class="prose prose-sm text-gray-700 max-h-80 overflow-y-auto mb-4"
                v-html="currentNotification?.content || 'Không có nội dung'"
              ></div>
              <div class="text-xs text-gray-400">
                Gửi: {{ currentNotification?.time_ago || "Không rõ thời gian" }}
              </div>
            </div>
          </div>
        </transition>
      </Teleport>
    </header>

    <!-- Auth Modal -->
    <AuthModal
      :show="showModal"
      :initial-mode="modalMode"
      @close="showModal = false"
      @login-success="handleLoginSuccess"
    />

    <!-- Thanh giữa -->
    <div class="bg-white shadow-sm">
      <div
        class="max-w-7xl mx-auto px-2 flex items-center gap-6 justify-between px-2 py-3"
      >
        <!-- Logo + Danh mục -->
        <div class="flex items-center gap-3 w-[250px]">
          <NuxtLink to="/" class="flex-shrink-0">
            <img
              :src="getLogoUrl()"
              alt="Logo"
              class="h-12 w-12 object-contain"
            />
          </NuxtLink>
          <!-- Danh mục (ẩn dropdown trên mobile) -->
          <div class="relative group hidden sm:block">
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

            <!-- Dropdown danh mục (ẩn trên mobile) -->
            <div
              class="absolute left-0 mt-2 w-[90vw] max-w-[1000px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-3 sm:p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5 overflow-hidden"
            >
              <!-- Cột 1 -->
              <div>
                <h4 class="font-bold mb-2 text-gray-800 text-sm sm:text-base">
                  Sản phẩm nổi bật
                </h4>
                <ul class="text-gray-700 space-y-1">
                  <li>
                    <NuxtLink to="/shop/new" class="hover:underline text-sm"
                      >Sản phẩm mới</NuxtLink
                    >
                  </li>
                  <li>
                    <NuxtLink to="/shop/sale" class="hover:underline text-sm"
                      >Khuyến mãi</NuxtLink
                    >
                  </li>
                </ul>
              </div>

              <!-- Cột 2 -->
              <div>
                <h4 class="font-bold mb-2 text-gray-800 text-sm sm:text-base">
                  Danh mục sản phẩm
                </h4>
                <ul class="text-gray-700 space-y-2">
                  <li v-for="(item, index) in categories" :key="index">
                    <NuxtLink
                      :to="`/shop/${item.slug}`"
                      class="flex items-center gap-2 hover:underline"
                    >
                      <img
                        :src="`${mediaBase}${item.image}`"
                        :alt="item.name"
                        class="w-6 h-6 object-contain rounded-full"
                      />
                      <span class="text-sm font-medium">{{ item.name }}</span>
                    </NuxtLink>
                  </li>
                </ul>
              </div>

              <!-- Cột 3 -->
              <div>
                <h4 class="font-bold mb-2 text-gray-800 text-sm sm:text-base">
                  Hợp tác
                </h4>
                <ul class="text-gray-700 space-y-2">
                  <li>
                    <NuxtLink
                      to="/sell-together-passion"
                      class="flex items-center gap-2 hover:underline"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-[#1BA0E2]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M6.5 14.5l2.8 2.8c.6.6 1.4.9 2.2.9h.1c.9 0 1.8-.4 2.4-1.1l3.4-3.6a1.7 1.7 0 00-2.4-2.4l-.9.9-2.2-2.2a1.7 1.7 0 00-2.4 0 1.7 1.7 0 000 2.4l2.2 2.2-.9.9-1.7-1.7"
                        />
                      </svg>
                      <span class="text-sm font-medium"
                        >Bán hàng cùng Passion</span
                      >
                    </NuxtLink>
                  </li>
                </ul>
              </div>

              <!-- Cột 4-5 hình ảnh -->
              <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-4">
                <img
                  src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp"
                  alt="Sản phẩm 1"
                  class="rounded-md object-cover w-full h-32 sm:h-36 md:h-48"
                />
                <img
                  src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp"
                  alt="Sản phẩm 2"
                  class="rounded-md object-cover w-full h-32 sm:h-36 md:h-48"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Search bar -->
        <div class="hidden md:flex flex-grow max-w-xl">
          <SearchBar />
        </div>

        <div class="md:hidden flex justify-center">
          <div class="w-full max-w-sm px-2">
            <SearchBar />
          </div>
        </div>
        <!-- Biểu tượng bên phải -->
        <div
          class="hidden sm:flex items-center gap-x-6 text-sm font-medium text-gray-700 flex-shrink-0"
        >
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

          <!-- Tài khoản dropdown -->
          <div class="relative group inline-block">
            <div
              class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1"
              :class="{ 'pointer-events-none': !isLoggedIn }"
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
            <ul
              v-if="isLoggedIn"
              class="absolute left-0 top-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded shadow-lg w-48 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-in-out z-50 text-sm text-gray-700"
            >
              <li>
                <NuxtLink
                  to="/users/profile"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Thông tin tài khoản</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/users/orders"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Đơn hàng của tôi</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/support"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Trung tâm hỗ trợ</NuxtLink
                >
              </li>
              <li v-if="userRole === 'seller'">
                <NuxtLink
                  to="/seller/dashboard"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Quản lý cửa hàng</NuxtLink
                >
              </li>
              <li v-if="userRole === 'admin'">
                <NuxtLink
                  to="/admin/dashboard"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Quản lý hệ thống</NuxtLink
                >
              </li>
            </ul>
          </div>

          <!-- Giỏ hàng -->
          <NuxtLink
            href="/cart"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1 relative"
          >
            <div class="relative">
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
              <span
                v-if="cartStore.totalItems > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-semibold rounded-full w-4 h-4 flex items-center justify-center"
              >
                {{ cartStore.totalItems }}
              </span>
            </div>
            Giỏ hàng
          </NuxtLink>
        </div>

        <!-- Nút menu mobile -->
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
    </div>
    <hr />

    <div
      v-if="isMobileMenuOpen"
      class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-start justify-end sm:hidden"
    >
      <div
        class="w-[80%] sm:w-[400px] h-full bg-white shadow-md p-4 relative animate-slide-in-right"
      >
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
        <div class="space-y-4 mt-10 text-sm">
          <NuxtLink to="/" class="block text-gray-700 hover:text-blue-600"
            ><font-awesome-icon :icon="['fas', 'house']" /> Trang chủ</NuxtLink
          >
          <div class="infor relative group inline-block">
            <div
              class="flex items-center gap-1 text-gray-700 hover:text-blue-600 font-semibold"
              :class="{ 'pointer-events-none': !isLoggedIn }"
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
            <ul
              v-if="isLoggedIn"
              class="absolute left-0 top-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded shadow-lg w-48 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-in-out z-50"
            >
              <li>
                <NuxtLink
                  to="/users/profile"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Thông tin tài khoản</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/users/orders"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Đơn hàng của tôi</NuxtLink
                >
              </li>
              <li>
                <NuxtLink
                  to="/support"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Trung tâm hỗ trợ</NuxtLink
                >
              </li>
              <li v-if="userRole === 'seller'">
                <NuxtLink
                  to="/seller/dashboard"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Quản lý cửa hàng</NuxtLink
                >
              </li>
              <li v-if="userRole === 'admin'">
                <NuxtLink
                  to="/admin/dashboard"
                  class="block px-4 py-2 hover:bg-gray-100"
                  >Quản lý hệ thống</NuxtLink
                >
              </li>
            </ul>
          </div>
          <NuxtLink to="/cart" class="block text-gray-700 hover:text-blue-600"
            ><font-awesome-icon :icon="['fas', 'cart-shopping']" /> Giỏ hàng</NuxtLink
          >
          <NuxtLink to="/notifications" class="block text-gray-700 hover:text-blue-600"
            ><font-awesome-icon :icon="['fas', 'bell']" /> Thông báo</NuxtLink
          >
          <NuxtLink
            to="/support"
            class="block text-gray-700 hover:text-blue-600"
            ><font-awesome-icon :icon="['fas', 'info']" /> Hỗ trợ</NuxtLink
          >
          <a
            href="#"
            class="block text-gray-700 hover:text-blue-600"
            @click.prevent="openLogin"
            ><font-awesome-icon :icon="['fas', 'right-to-bracket']" /> Đăng
            nhập</a
          >
          <a
            href="#"
            class="block text-gray-700 hover:text-blue-600"
            @click.prevent="openRegister"
            ><font-awesome-icon :icon="['fas', 'plus']" /> Đăng ký</a
          >
        </div>
      </div>
    </div>

    <Features />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import Features from "~/components/shared/Features.vue";
import SearchBar from "~/components/shared/filters/SearchBar.vue";
import AuthModal from "~/components/shared/AuthModal.vue";
import { useToast } from "~/composables/useToast";
import { useCartStore } from "~/stores/cart";
import { useRuntimeConfig } from "#imports";
import { useSettings } from "~/composables/useSettings";

const cartStore = useCartStore();
const { toast } = useToast();
const config = useRuntimeConfig();
const api = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const showModal = ref(false);
const modalMode = ref("login");
const isLoggedIn = ref(false);
const userName = ref("");
const isMobileMenuOpen = ref(false);
const showVerifyEmailForm = ref(false);
const userRole = ref("");

const notifications = ref([]);
const unreadCount = ref(0);
const notificationDropdownOpen = ref(false);
const currentNotification = ref(null);
const showNotificationModal = ref(false);

const redirectToLink = async (item) => {
  await markAsRead(item);
  window.location.href = item.link;
};

const openNotificationModal = async (item) => {
  await markAsRead(item);
  currentNotification.value = item;
  showNotificationModal.value = true;
  notificationDropdownOpen.value = false;
};

const stripHTML = (html) => {
  const div = document.createElement("div");
  div.innerHTML = html;
  return div.textContent || div.innerText || "";
};

const toggleNotificationDropdown = () => {
  notificationDropdownOpen.value = !notificationDropdownOpen.value;

  if (notificationDropdownOpen.value) {
    fetchNotifications();
  }
};

const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem("access_token");
    if (!token) {
      console.warn("Chưa có token, không gọi API");
      return;
    }

    const res = await fetch(`${api}/my-notifications`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const data = await res.json();
    console.log("Kết quả trả về từ API:", data);

    if (Array.isArray(data?.data)) {
      notifications.value = data.data;
      unreadCount.value = data.data.filter((n) => !n.is_read).length;
    } else {
      console.warn("Dữ liệu không hợp lệ:", data.data);
      notifications.value = [];
      unreadCount.value = 0;
    }
  } catch (e) {
    // console.error('Lỗi khi lấy thông báo:', e)
  }
};
const markAsRead = async (item) => {
  const token = localStorage.getItem("access_token");
  if (!token || item.is_read === 1) return;

  try {
    await fetch(`${api}/notifications/${item.id}/read`, {
      method: "POST",
      headers: { Authorization: `Bearer ${token}` },
    });

    item.is_read = 1;
    unreadCount.value = notifications.value.filter((n) => !n.is_read).length;
  } catch (err) {
    console.error("Lỗi đánh dấu đã đọc:", err);
  }
};

// NEW từ dat_dev: dùng cho categories động
const categories = ref([]);

let resendTimer = null;

const form = ref({
  name: "",
  email: "",
  password: "",
  confirmPassword: "",
  phone: "",
});

const forgotEmail = ref("");
const isSending = ref(false);

const resetForm = ref({
  email: "",
  otp: "",
  password: "",
  password_confirmation: "",
});

const { settings } = useSettings();

const getLogoUrl = () => {
  const config = useRuntimeConfig();
  const logoPath = settings.value?.general?.find(
    (s) => s.key === "site_logo"
  )?.value;

  return logoPath
    ? `${config.public.mediaBaseUrl}${logoPath}`
    : "/default-logo.png";
};

const contactHotline = computed(() => {
  return (
    settings.value?.contact?.find((s) => s.key === "hotline_number")?.value ||
    "Chưa cấu hình"
  );
});

// Fetch categories for mega menu
const fetchCategories = async () => {
  try {
    const response = await fetch(`${api}/categories/parents`, {
      method: "GET",
      headers: { "Content-Type": "application/json" },
    });
    const data = await response.json();
    categories.value = data.categories;
  } catch (error) {
    console.error("Error fetching categories:", error);
    toast("error", "Không thể tải danh mục sản phẩm.");
  }
};

const openLogin = () => {
  modalMode.value = "login";
  showModal.value = true;
};

const openRegister = () => {
  modalMode.value = "register";
  showModal.value = true;
};
const logout = async () => {
  try {
    const token = localStorage.getItem("access_token");
    if (!token) {
      toast("warning", "Bạn chưa đăng nhập.");
      return;
    }
    await axios.post(
      `${api}/logout`,
      {},
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    localStorage.removeItem("access_token");
    updateLoginState();
    toast("success", "Đăng xuất thành công!");
    setTimeout(() => {
      window.location.reload();
    }, 1500);
  } catch (err) {
    toast("error", err.response?.data?.message || "Không thể đăng xuất.");
    if (err?.response?.data?.trace) {
      console.error("Trace:", err.response.data.trace);
    }
  }
};

const fetchUserProfile = async () => {
  const token = localStorage.getItem("access_token");
  if (!token) return;
  try {
    const res = await axios.get(`${api}/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    userName.value = res.data.data.name;
    userRole.value = res.data.data.role; // Update userRole
    isLoggedIn.value = true;
  } catch (err) {
    isLoggedIn.value = false;
    userName.value = "";
    userRole.value = "";
    localStorage.removeItem("access_token");
  }
};

const updateLoginState = async () => {
  const token = localStorage.getItem("access_token");
  if (!token) {
    isLoggedIn.value = false;
    userName.value = "";
    userRole.value = "";
    return;
  }
  await fetchUserProfile();
};

const handleLoginSuccess = (userData) => {
  isLoggedIn.value = true;
  userName.value = userData.name;
  userRole.value = userData.role; // Set role on login
  showModal.value = false;
};

onMounted(() => {
  updateLoginState();
  fetchCategories();
  fetchNotifications();
});

onUnmounted(() => {
  window.removeEventListener("openLoginModal", () => {
    openLogin();
  });
});
</script>

<style scoped>
details > summary {
  list-style: none;
}
details > summary::-webkit-details-marker {
  display: none;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(50px) scale(0.95);
}
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.relative {
  position: relative;
}

.prose {
  scrollbar-width: thin;
  scrollbar-color: #ccc transparent;
}

.prose::-webkit-scrollbar {
  width: 6px;
}

.prose::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}
</style>