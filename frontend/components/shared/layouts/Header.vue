<template>
  <div>
    <!-- Thanh trên cùng -->
    <header class="bg-[#1BA0E2] text-white text-sm py-2">
      <div class="container mx-auto flex justify-end items-center px-4">
        <div class="hidden sm:flex items-center space-x-4">
          <!-- THÔNG BÁO DROPDOWN -->
          <div class="relative group inline-block">
            <!-- Icon chuông -->
            <div
              class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center"
              @click="toggleNotificationDropdown">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M14.25 17.25a2.25 2.25 0 0 1-4.5 0m8.25-5.25v-1.5a6 6 0 1 0-12 0v1.5c0 .621-.252 1.216-.7 1.65L3.63 15.255A.75.75 0 0 0 4.14 16.5h15.72a.75.75 0 0 0 .51-1.245l-1.42-1.605a2.25 2.25 0 0 1-.7-1.65Z" />
              </svg>

              <!-- Badge hiển thị số lượng chưa đọc -->
              <span v-if="unreadCount > 0"
                class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white text-xs rounded-full px-1.5 min-w-[20px] text-center leading-tight shadow">
                {{ unreadCount }}
              </span>
            </div>

            <!-- Dropdown hiển thị thông báo -->
            <div v-if="notificationDropdownOpen"
              class="absolute right-0 top-full mt-2 w-96 bg-white border border-gray-200 rounded shadow-lg z-50 text-sm max-h-96 overflow-auto">

              <div v-if="notifications.length === 0" class="p-4 text-gray-500 text-center">
                Không có thông báo mới.
              </div>

              <ul v-else class="divide-y divide-gray-100">
                <li v-for="item in notifications" :key="item.id"
                  class="relative p-3 hover:bg-gray-50 flex gap-3 items-start transition group"
                  :class="{ 'opacity-60': item.is_read === 1, 'cursor-pointer': true }"
                  @click="item.link ? redirectToLink(item) : openNotificationModal(item)">

                  <!-- Hình ảnh -->
                  <img v-if="item.image_url" :src="item.image_url" alt="Hình thông báo"
                    class="w-12 h-12 object-cover rounded" />

                  <!-- Nội dung -->
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center">
                      <span class="text-gray-800 font-semibold truncate" :class="{ 'font-bold': item.is_read === 0 }">
                        {{ item.title }}
                      </span>
                      <span v-if="item.is_read === 0" class="w-2 h-2 bg-blue-500 rounded-full inline-block"></span>
                    </div>

                    <p class="text-gray-500 text-sm mt-1 break-words line-clamp-2">
                      {{ stripHTML(item.content) || 'Không có nội dung' }}
                    </p>

                    <p class="text-gray-500 text-xs mt-1 flex justify-between items-center">
                      <span>{{ item.time_ago || 'Vừa xong' }}</span>
                      <!-- Nút xem chi tiết nếu có link -->
                      <button v-if="item.link" @click.stop="openNotificationModal(item)"
                        class="text-blue-600 hover:underline text-xs font-medium transition duration-150">
                        Xem chi tiết
                      </button>
                    </p>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <template v-if="isLoggedIn">
            <span class="font-medium">Xin chào, <strong>{{ userName }}</strong></span>
            <button @click="logout" class="hover:underline inline-flex items-center gap-1 text-white">
              <i><font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" /></i>
              Đăng xuất
            </button>
          </template>

          <template v-else>
            <NuxtLink to="#" @click.prevent="openLogin" class="hover:underline inline-flex items-center gap-1">
              <i><font-awesome-icon :icon="['fas', 'right-to-bracket']" /></i>
              Đăng nhập
            </NuxtLink>
            <NuxtLink to="#" @click.prevent="openRegister" class="hover:underline inline-flex items-center gap-1">
              <i><font-awesome-icon :icon="['fas', 'plus']" /></i>
              Đăng ký
            </NuxtLink>
          </template>
          <Teleport to="body">
            <transition name="fade">
              <div v-if="showNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
                  <button @click="showNotificationModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                  </button>

                  <!-- Tiêu đề -->
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    {{ currentNotification?.title || 'Không có tiêu đề' }}
                  </h3>

                  <!-- Hình ảnh -->
                  <div v-if="currentNotification?.image_url" class="mb-4">
                    <img :src="currentNotification.image_url" alt="Ảnh thông báo"
                      class="w-full h-auto rounded-md border object-cover max-h-64 mx-auto" />
                  </div>

                  <!-- Nội dung -->
                  <div class="prose prose-sm text-gray-700 max-h-80 overflow-y-auto mb-4"
                    v-html="currentNotification?.content || 'Không có nội dung'"></div>

                  <!-- Thời gian -->
                  <div class="text-xs text-gray-400">
                    Gửi: {{ currentNotification?.time_ago || 'Không rõ thời gian' }}
                  </div>
                </div>
              </div>
            </transition>
          </Teleport>

        </div>
      </div>
    </header>

    <transition name="fade-slide">
      <div v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
        <div
          class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl flex relative overflow-hidden border border-gray-100">
          <div
            class="hidden md:flex w-1/2 flex-col items-center justify-center bg-gradient-to-br from-[#1BA0E2] to-[#1591cc] text-white p-8">
            <h2 class="text-3xl font-bold mb-4">Chào mừng bạn!</h2>
            <p class="text-sm text-center opacity-80">Tham gia ngay để trải nghiệm những tính năng tuyệt vời.</p>
            <img src="/images/img-form-removebg-preview.png" alt="Welcome Image"
              class="mt-6 w-3/4 max-w-[300px] object-contain" />
          </div>

          <div class="w-full md:w-1/2 p-6 sm:p-8 relative">
            <button @click="closeModal"
              class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl transition-transform duration-300 hover:scale-125">
              <i class="fas fa-times"></i>
            </button>

            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-[#1BA0E2] font-inter tracking-tight">
                {{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}
              </h2>
              <button @click="isLogin = !isLogin"
                class="text-sm text-[#1BA0E2] hover:text-[#1591cc] transition-colors duration-200 font-medium">
                {{ isLogin ? 'Chưa có tài khoản?' : 'Đã có tài khoản?' }}
              </button>
            </div>

            <form v-if="!showOtp && !showVerifyEmailForm && !showResetPassword && !isForgotMode && !isResetMode"
              @submit.prevent="submitForm" class="space-y-4">
              <div v-if="!isLogin" class="relative">
                <i
                  class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="form.name" type="text" placeholder="Họ và tên"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <div class="relative">
                <i
                  class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="form.email" type="email" placeholder="Email"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <div class="relative">
                <i
                  class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="form.password" type="password" placeholder="Mật khẩu"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <div v-if="!isLogin" class="relative">
                <i
                  class="fas fa-shield-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="form.confirmPassword" type="password" placeholder="Xác nhận mật khẩu"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <div v-if="!isLogin" class="relative">
                <i
                  class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="form.phone" type="text" placeholder="Số điện thoại"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>
              <div v-if="isLogin" class="flex justify-end">
                <button type="button" @click="isForgotMode = true"
                  class="text-sm text-[#1BA0E2] hover:text-[#1591cc] font-inter font-medium transition-colors duration-200 mb-1">
                  Quên mật khẩu?
                </button>
              </div>
              <button type="submit"
                class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02] focus:ring-2 focus:ring-[#1BA0E2] focus:ring-opacity-50 font-inter disabled:opacity-50"
                :disabled="isSubmitting">
                <span v-if="isSubmitting"><svg class="animate-spin h-5 w-5 mr-2 inline-block" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>Đang xử lý...</span>
                <span v-else>{{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}</span>
              </button>

              <!-- google -->
              <button @click="loginWithGoogle" type="button"
                class="flex justify-center w-full items-center gap-2 px-5 py-3 rounded-xl border border-gray-300 bg-white text-gray-800 font-semibold text-base shadow-sm hover:shadow-md hover:border-gray-500 transition-all duration-150 active:scale-95">
                <svg class="w-6 h-6" viewBox="0 0 48 48">
                  <g>
                    <path fill="#4285F4"
                      d="M24 9.5c3.8 0 7.2 1.34 9.81 3.55l7.27-7.27C36.66 2.05 30.71 0 24 0 14.8 0 6.4 4.96 1.44 12.44l8.58 6.67C12.21 13.45 17.61 9.5 24 9.5z" />
                    <path fill="#34A853"
                      d="M46.15 24.53c0-1.64-.16-3.22-.46-4.74H24v9h12.5c-.54 2.91-2.19 5.38-4.63 7.04l7.16 5.57C43.9 36.97 46.15 31.18 46.15 24.53z" />
                    <path fill="#FBBC05"
                      d="M9.42 28.9a14.2 14.2 0 0 1-.77-4.4c0-1.52.28-2.99.77-4.4l-8.58-6.67A24 24 0 0 0 0 24c0 3.81.93 7.41 2.58 10.56l8.84-6.66z" />
                    <path fill="#EA4335"
                      d="M24 48c6.71 0 12.33-2.2 16.44-5.98l-7.16-5.57c-2.01 1.35-4.62 2.16-9.28 2.16-6.39 0-11.79-3.95-13.98-9.61l-8.84 6.66C6.4 43.04 14.8 48 24 48z" />
                    <path fill="none" d="M0 0h48v48H0z" />
                  </g>
                </svg>
                Đăng nhập bằng Google
              </button>
            </form>

            <!-- FORM 2: XÁC MINH EMAIL -->
            <form v-if="showVerifyEmailForm && !showOtp" @submit.prevent="sendVerificationRequest"
              class="space-y-4 border-t pt-5 mt-6">
              <p class="text-sm text-gray-600">Bạn cần xác minh email để tiếp tục. Nhập email của bạn:</p>
              <div class="relative">
                <i
                  class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="verifyEmailInput" type="email" placeholder="Email"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <button type="submit"
                class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02] focus:ring-2 focus:ring-[#1BA0E2] focus:ring-opacity-50 font-inter disabled:opacity-50"
                :disabled="isSubmitting">
                <span v-if="isSubmitting"><svg class="animate-spin h-5 w-5 mr-2 inline-block" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>Đang gửi...</span>
                <span v-else>Gửi mã xác minh</span>
              </button>
              <button type="button"
                class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
                @click="cancelOtp">
                Quay lại đăng nhập
              </button>
            </form>

            <!-- FORM 3: NHẬP OTP -->
            <form v-if="showOtp" @submit.prevent="verifyOtp" class="space-y-5 border-t pt-5 mt-6">
              <h2 class="text-xl font-bold text-[#1BA0E2] font-inter">Xác minh OTP</h2>
              <p class="text-sm text-gray-600">Vui lòng kiểm tra email và nhập mã OTP để xác minh tài khoản.</p>

              <div class="relative">
                <i
                  class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
                <input v-model="otp" type="text" placeholder="Nhập mã OTP"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <button type="submit"
                class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02] focus:ring-2 focus:ring-[#1BA0E2] focus:ring-opacity-50 font-inter disabled:opacity-50"
                :disabled="isVerifying">
                <span v-if="isVerifying"><svg class="animate-spin h-5 w-5 mr-2 inline-block" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>Đang xác minh...</span>
                <span v-else>Xác minh</span>
              </button>

              <button type="button" @click="resendVerificationEmail"
                class="w-full border border-[#1BA0E2] text-[#1BA0E2] py-3 rounded-xl font-semibold hover:bg-[#1BA0E2] hover:text-white transition-all duration-300 font-inter disabled:opacity-50"
                :disabled="resendCountdown > 0">
                <template v-if="resendCountdown > 0">Gửi lại mã sau {{ resendCountdown }} giây</template>
                <template v-else>Gửi lại mã OTP</template>
              </button>

              <button type="button"
                class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
                @click="cancelOtp">
                Quay lại đăng nhập
              </button>
            </form>
            <!-- FORM 4: QUÊN MẬT KHẨU -->
            <form v-if="isForgotMode" @submit.prevent="sendForgotEmail" class="space-y-5 border-t pt-5 mt-6">
              <h2 class="text-xl font-bold text-[#1BA0E2] font-inter">Quên mật khẩu</h2>
              <p class="text-sm text-gray-600">Nhập email để nhận hướng dẫn đặt lại mật khẩu.</p>

              <div class="relative">
                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input v-model="forgotEmail" type="email" placeholder="Nhập email"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <button type="submit"
                class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02]"
                :disabled="isSending">
                <span v-if="isSending"><i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...</span>
                <span v-else>Gửi hướng dẫn</span>
              </button>

              <button type="button"
                class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
                @click="isForgotMode = false">
                Quay lại đăng nhập
              </button>
            </form>

            <form v-if="isResetMode" @submit.prevent="submitResetPassword" class="space-y-5 border-t pt-5 mt-6">
              <h2 class="text-xl font-bold text-[#1BA0E2] font-inter">Đặt lại mật khẩu</h2>
              <p class="text-sm text-gray-600">Vui lòng nhập mã OTP và mật khẩu mới.</p>

              <!-- OTP -->
              <div class="relative">
                <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input v-model="resetForm.otp" type="text" placeholder="Mã OTP" maxlength="6"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <!-- Mật khẩu mới -->
              <div class="relative">
                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input v-model="resetForm.password" type="password" placeholder="Mật khẩu mới"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <!-- Xác nhận -->
              <div class="relative">
                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input v-model="resetForm.password_confirmation" type="password" placeholder="Xác nhận mật khẩu mới"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
              </div>

              <!-- Gửi -->
              <button type="submit"
                class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02]"
                :disabled="isResetting">
                <span v-if="isResetting"><i class="fas fa-spinner fa-spin mr-2"></i>Đang đặt lại...</span>
                <span v-else>Đặt lại mật khẩu</span>
              </button>

              <!-- Quay lại -->
              <button type="button"
                class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
                @click="() => {
                  isResetMode = false;
                  isLogin = true;
                }">
                Quay lại trang đăng nhập
              </button>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Thanh giữa -->
    <div class="bg-white shadow-sm">
      <div class="container mx-auto flex items-center justify-between px-4 py-3">
        <!-- Logo -->
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 rounded text-center text-xs flex items-center justify-center">
            <NuxtLink to="/">
              <img src="/images/logo.png" alt="logo">
            </NuxtLink>
          </div>
        </div>

        <!-- NAVIGATION -->
        <div class="relative group hidden md:block">
          <a href="#" class="flex items-center gap-1 text-gray-700 hover:text-blue-600 font-semibold">
            Danh mục
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </a>
          <!-- MEGA MENU -->
          <div
            class="absolute left-0 mt-2 w-[1200px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-6 grid grid-cols-5 gap-6">
            <!-- Column 1: Static Links -->
            <div>
              <h4 class="font-bold mb-2 text-gray-800">Sản phẩm nổi bật</h4>
              <ul class="text-gray-700 space-y-1">
                <li>
                  <NuxtLink to="/shop/new" class="hover:underline">Sản phẩm mới</NuxtLink>
                </li>
                <li>
                  <NuxtLink to="/shop/sale" class="hover:underline">Khuyến mãi</NuxtLink>
                </li>
              </ul>
            </div>
            <!-- Column 2: Dynamic Categories -->
            <div>
              <h4 class="font-bold mb-2 text-gray-800">Danh mục sản phẩm</h4>
              <ul class="text-gray-700 space-y-2">
                <li v-for="(item, index) in categories" :key="index">
                  <NuxtLink :to="`/shop/${item.slug}`" class="flex items-center gap-2 hover:underline">
                    <img :src="`${mediaBase}${item.image}`" :alt="item.name"
                      class="w-6 h-6 object-contain rounded-full" />
                    <span class="text-sm font-medium">{{ item.name }}</span>
                  </NuxtLink>
                </li>
              </ul>
            </div>
            <!-- Column 3: Passion Link -->
            <div>
              <h4 class="font-bold mb-2 text-gray-800">Hợp tác</h4>
              <ul class="text-gray-700 space-y-2">
                <li>
                  <NuxtLink to="/sell-together-passion" class="flex items-center gap-2 hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#1BA0E2]" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.5 14.5l2.8 2.8c.6.6 1.4.9 2.2.9h.1c.9 0 1.8-.4 2.4-1.1l3.4-3.6a1.7 1.7 0 00-2.4-2.4l-.9.9-2.2-2.2a1.7 1.7 0 00-2.4 0 1.7 1.7 0 000 2.4l2.2 2.2-.9.9-1.7-1.7" />
                    </svg>
                    <span class="text-sm font-medium">Bán hàng cùng Passion</span>
                  </NuxtLink>
                </li>
              </ul>
            </div>
            <!-- Columns 4 & 5: Promotional Images -->
            <div class="col-span-2 grid grid-cols-2 gap-4">
              <img src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp" alt="Sản phẩm 1"
                class="rounded-md object-cover w-full h-48" />
              <img src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp" alt="Sản phẩm 2"
                class="rounded-md object-cover w-full h-48" />
            </div>
          </div>
        </div>

        <!-- Search Component -->
        <SearchBar />

        <!-- Menu PC -->
        <div class="hidden sm:flex items-center gap-x-6 text-sm font-medium text-gray-700">
          <NuxtLink href="/"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Trang chủ
          </NuxtLink>

          <!-- Tài khoản -->
          <div class="relative group inline-block">
            <div
              class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              Tài khoản
            </div>
            <ul
              class="absolute left-0 top-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded shadow-lg w-48 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-in-out z-50 text-sm text-gray-700">
              <li><a href="/users/profile" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
              <li><a href="/users/orders" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
              <li><a href="/support" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
     <li v-if="userRole === 'admin'">
  <a href="/admin/dashboard" class="block px-4 py-2 hover:bg-gray-100">Trang quản lý (Admin)</a>
</li>
<li v-if="userRole === 'seller'">
  <a href="/seller/dashboard" class="block px-4 py-2 hover:bg-gray-100">Trang quản lý (Seller)</a>
</li>
            </ul>

          </div>

          <!-- Giỏ hàng -->
          <NuxtLink href="/cart"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1 relative">
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
              </svg>
              <span v-if="cartStore.totalItems > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-semibold rounded-full w-4 h-4 flex items-center justify-center">
                {{ cartStore.totalItems }}
              </span>
            </div>
            Giỏ hàng
          </NuxtLink>



        </div>


        <!-- Icon menu mobile -->
        <div class="sm:hidden">
          <button @click="isMobileMenuOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Modal Menu -->
    <div v-if="isMobileMenuOpen"
      class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-start justify-end sm:hidden">
      <div class="w-3/4 bg-white shadow-md h-full p-4 relative">
        <!-- Close button -->
        <button @click="isMobileMenuOpen = false" class="absolute top-4 right-4 text-gray-600 hover:text-black">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <!-- Menu Items -->
        <div class="space-y-4 mt-10 text-sm">
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'house']" />
            Trang
            chủ</a>
          <div class="infor relative group inline-block">
            <a href="#" class="block text-gray-700 hover:text-blue-600 font-semibold">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              Tài khoản
            </a>
            <ul
              class="absolute left-0 top-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded shadow-lg w-48 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-in-out z-50">
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
            </ul>
          </div>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon
              :icon="['fas', 'cart-shopping']" />
            Giỏ hàng</a>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'bell']" />
            Thông
            báo</a>
          <NuxtLink to="/support" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon
              :icon="['fas', 'info']" /> Hỗ trợ</NuxtLink>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon
              :icon="['fas', 'right-to-bracket']" /> Đăng nhập</a>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'plus']" /> Đăng
            ký</a>
        </div>
      </div>
    </div>

    <Features />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import Features from '~/components/shared/Features.vue'
import SearchBar from '~/components/shared/filters/SearchBar.vue'
import { useToast } from '~/composables/useToast'
import { useCartStore } from '~/stores/cart'
import { useRuntimeConfig } from '#imports'

// Stores, config, toast
const cartStore = useCartStore() // từ dat_dev
const { toast } = useToast()
const config = useRuntimeConfig()
const api = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

// Modal và trạng thái đăng nhập
const showModal = ref(false)
const isLogin = ref(true)
const showOtp = ref(false)
const otp = ref('')
const tempUserId = ref(null)
const verifyUserId = ref(null)
const verificationEmail = ref('')
const verifyEmailInput = ref('')
const verificationPending = ref(false)
const isSubmitting = ref(false)
const isVerifying = ref(false)
const resendCountdown = ref(0)
const isLoggedIn = ref(false)
const showForgotPassword = ref(false)
const showResetPassword = ref(false)
const isForgotMode = ref(false)
const isResetMode = ref(false)
const isResetting = ref(false)
const userName = ref('')
const isMobileMenuOpen = ref(false)
const showVerifyEmailForm = ref(false)
const userRole = ref('');

const notifications = ref([])
const unreadCount = ref(0)
const notificationDropdownOpen = ref(false)
const currentNotification = ref(null)
const showNotificationModal = ref(false)


const redirectToLink = async (item) => {
  await markAsRead(item)
  window.location.href = item.link
}

const openNotificationModal = async (item) => {
  await markAsRead(item)
  currentNotification.value = item
  showNotificationModal.value = true
  notificationDropdownOpen.value = false
}



const stripHTML = (html) => {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}

const toggleNotificationDropdown = () => {
  notificationDropdownOpen.value = !notificationDropdownOpen.value

  if (notificationDropdownOpen.value) {
    fetchNotifications()
  }
}


const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      console.warn('Chưa có token, không gọi API')
      return
    }

    const res = await fetch(`${api}/my-notifications`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const data = await res.json()
    console.log('Kết quả trả về từ API:', data)

    if (Array.isArray(data?.data)) {
      notifications.value = data.data
      unreadCount.value = data.data.filter(n => !n.is_read).length
    } else {
      console.warn('Dữ liệu không hợp lệ:', data.data)
      notifications.value = []
      unreadCount.value = 0
    }
  } catch (e) {
    // console.error('Lỗi khi lấy thông báo:', e)
  }
}


const markAsRead = async (item) => {
  const token = localStorage.getItem('access_token')
  if (!token || item.is_read === 1) return

  try {
    await fetch(`${api}/notifications/${item.id}/read`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` }
    })

    item.is_read = 1
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
  } catch (err) {
    console.error('Lỗi đánh dấu đã đọc:', err)
  }
}


// NEW từ dat_dev: dùng cho categories động
const categories = ref([])

let resendTimer = null

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  phone: '',
});

const forgotEmail = ref('');
const isSending = ref(false);

const resetForm = ref({
  email: '',
  otp: '',
  password: '',
  password_confirmation: '',
});

// Fetch categories for mega menu
const fetchCategories = async () => {
  try {
    const response = await fetch(`${api}/categories/parents`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    const data = await response.json();
    categories.value = data.categories;
  } catch (error) {
    // console.error('Error fetching categories:', error);
    toast('error', 'Không thể tải danh mục sản phẩm.');
  }
};

const openForgotPassword = () => {
  openLogin.value = false;
  showForgotPassword.value = true;
  showResetPassword.value = false;
  showVerifyEmailForm.value = false;
  showOtp.value = false;
};

function loginWithGoogle() {
  const width = 500;
  const height = 600;
  const left = window.screen.width / 2 - width / 2;
  const top = window.screen.height / 2 - height / 2;

  const googleAuthUrl = 'http://localhost:8000/api/auth/google/redirect';
  const expectedOrigin = 'http://localhost:8000';
  const popup = window.open(
    googleAuthUrl,
    'Google Login',
    `width=${width},height=${height},top=${top},left=${left}`
  );

  const messageHandler = async (event) => {
    if (event.origin !== expectedOrigin) {
      // console.warn('Invalid origin:', event.origin);
      return;
    }

    // console.log('Received message from:', event.origin, event.data);

    if (event.data?.token) {
      localStorage.setItem('access_token', event.data.token);

      try {
        const res = await fetch('http://localhost:8000/api/me', {
          headers: {
            Authorization: `Bearer ${event.data.token}`,
          },
        });

        const data = await res.json();

        if (res.ok && data.data) {
          window.dispatchEvent(new CustomEvent('loginSuccess', { detail: data.data }));
          toast('success', 'Đăng nhập Google thành công!');
          showModal.value = false;
          fetchUserProfile();
          updateLoginState();
        } else {
          throw new Error(data.message || 'Không lấy được thông tin tài khoản!');
        }
      } catch (error) {
        // console.error('Login verification failed:', error);
        toast('error', 'Xác thực đăng nhập thất bại.');
        localStorage.removeItem('access_token');
      } finally {
        popup?.close();
        window.removeEventListener('message', messageHandler);
      }
    } else if (event.data?.error) {
      toast('error', event.data.error);
      popup?.close();
      window.removeEventListener('message', messageHandler);
    }
  };

  window.addEventListener('message', messageHandler, { once: true });
}

const cancelOtp = () => {
  showOtp.value = false;
  showVerifyEmailForm.value = false;
  otp.value = '';
  verifyEmailInput.value = '';
  form.value = {
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    phone: '',
  };
};

const openLogin = () => {
  isLogin.value = true;
  showModal.value = true;
  showOtp.value = false;
};

const openRegister = () => {
  isLogin.value = false;
  showModal.value = true;
  showOtp.value = false;
};

const closeModal = () => {
  showModal.value = false;
  showOtp.value = false;
  otp.value = '';
  verifyEmailInput.value = '';
  form.value = {
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    phone: '',
  };
};

const submitForm = async () => {
  isSubmitting.value = true;
  try {
    if (isLogin.value) {
      const res = await axios.post(`${api}/login`, {
        email: form.value.email,
        password: form.value.password,
      });

      localStorage.setItem('access_token', res.data.token);
      await fetchUserProfile();
      await updateLoginState();
      await showRoleBasedMenu();
      toast('success', 'Đăng nhập thành công!');
      closeModal();
    } else {
      const res = await axios.post(`${api}/register`, {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.confirmPassword,
        phone: form.value.phone,
      });

      tempUserId.value = res.data.user_id;
      showOtp.value = true;
      startResendCountdown();
      toast('success', 'Đăng ký thành công. Kiểm tra email để lấy mã OTP.');
    }
  } catch (err) {
    if (
      isLogin.value &&
      err.response?.status === 403 &&
      err.response?.data?.message?.includes('chưa được xác minh')
    ) {
      verificationEmail.value = form.value.email;
      verificationPending.value = true;
      toast('warning', 'Tài khoản chưa được xác minh, vui lòng xác minh trước khi đăng nhập');
      showVerifyEmailForm.value = true;
    } else {
      const msg = err.response?.data?.errors
        ? Object.values(err.response.data.errors)[0][0]
        : err.response?.data?.message || 'Đã xảy ra lỗi.';
      toast('error', msg);
    }
  } finally {
    isSubmitting.value = false;
  }
};

const verifyOtp = async () => {
  isVerifying.value = true;
  try {
    if (!/^[0-9]{6}$/.test(otp.value)) {
      toast('warning', 'Mã OTP phải gồm 6 chữ số.');
      return;
    }

    await axios.post(`${api}/verify-otp`, {
      email: form.value.email,
      otp: otp.value,
    });

    toast('success', 'Xác minh thành công! Bạn có thể đăng nhập.');
    showOtp.value = false;
    isLogin.value = true;
  } catch (err) {
    toast('error', err.response?.data?.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.');
  } finally {
    isVerifying.value = false;
  }
};

const sendVerificationRequest = async () => {
  isSubmitting.value = true;
  try {
    const res = await axios.post(`${api}/resend-otp-by-email`, {
      email: verifyEmailInput.value,
    });

    verificationEmail.value = verifyEmailInput.value;
    verifyUserId.value = res.data.user_id;
    tempUserId.value = res.data.user_id;
    showOtp.value = true;
    showVerifyEmailForm.value = false;
    startResendCountdown();
    toast('success', 'Mã xác minh đã được gửi. Vui lòng kiểm tra email!');
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi mã xác minh.');
  } finally {
    isSubmitting.value = false;
  }
};

const startResendCountdown = () => {
  resendCountdown.value = 60;
  clearInterval(resendTimer);
  resendTimer = setInterval(() => {
    resendCountdown.value--;
    if (resendCountdown.value <= 0) clearInterval(resendTimer);
  }, 1000);
};

const resendVerificationEmail = async () => {
  if (!verificationEmail.value) {
    toast('warning', 'Không tìm thấy email xác minh trước đó.');
    return;
  }

  try {
    await axios.post(`${api}/resend-otp-by-email`, {
      email: verificationEmail.value,
    });
    toast('success', 'Email xác minh đã được gửi lại!');
    startResendCountdown();
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi lại email.');
  }
};

const logout = async () => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('warning', 'Bạn chưa đăng nhập.');
      return;
    }
    await axios.post(`${api}/logout`, {}, {
      headers: { Authorization: `Bearer ${token}` },
    });
    localStorage.removeItem('access_token');
    updateLoginState();
    toast('success', 'Đăng xuất thành công!');
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể đăng xuất.');
    if (err?.response?.data?.trace) {
      // console.error('Trace:', err.response.data.trace);
    }
  }
};

const fetchUserProfile = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) return;
  try {
    const res = await axios.get(`${api}/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    userName.value = res.data.data.name;
    isLoggedIn.value = true;
  } catch (err) {
    isLoggedIn.value = false;
    userName.value = '';
    localStorage.removeItem('access_token');
  }
};

const updateLoginState = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    isLoggedIn.value = false;
    userName.value = '';
    return;
  }
  await fetchUserProfile();
};

const sendForgotEmail = async () => {
  isSending.value = true;
  try {
    const res = await axios.post(`${api}/send-forgot-password`, { email: forgotEmail.value });

    toast('success', 'Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư đến của bạn.');
    resetForm.value.email = forgotEmail.value;
    showOtp.value = false;
    showVerifyEmailForm.value = false;
    isLogin.value = false;
    isResetMode.value = true;
    isForgotMode.value = false;
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi email đặt lại mật khẩu.');
  } finally {
    isSending.value = false;
  }
};

const submitResetPassword = async () => {
  isResetting.value = true;
  try {
    await axios.post(`${api}/reset-password`, resetForm.value);
    toast('success', 'Mật khẩu đã được đặt lại thành công!');
    showResetPassword.value = false;
    isResetMode.value = false;
    isLogin.value = true;
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể đặt lại mật khẩu.');
  } finally {
    isResetting.value = false;
  }
};

const showRoleBasedMenu = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) return;

  try {
    const res = await axios.get(`${api}/me`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    userRole.value = res.data.data?.role || '';
  } catch (error) {
    console.error('Cannot fetch user role:', error);
  }
};



onMounted(() => {
  updateLoginState();
  fetchCategories(); // Fetch categories on mount
  fetchNotifications()
  updateLoginState()
  showRoleBasedMenu();
  window.addEventListener('storage', (e) => {
    if (e.key === 'access_token') updateLoginState();
  });

  window.addEventListener('openLoginModal', () => {
    openLogin();
  });
});

onUnmounted(() => {
  window.removeEventListener('openLoginModal', () => {
    openLogin();
  });
  clearInterval(resendTimer);
});
</script>

<style scoped>
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