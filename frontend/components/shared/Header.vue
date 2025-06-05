<template>
  <div>
    <!-- Thanh trên cùng -->
    <header class="bg-[#1BA0E2] text-white text-sm py-2">
      <div class="container mx-auto flex justify-between items-center px-4">
        <div class="space-x-2">
          <NuxtLink to="/Seller" class="hover:underline inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 10h8l3-10h4" />
            </svg>
            Đăng ký bán hàng
          </NuxtLink>
          <a href="#" class="hover:underline inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5" />
            </svg>
            Kết nối <font-awesome-icon :icon="['fab', 'facebook']" />
            <font-awesome-icon :icon="['fab', 'instagram']" />
          </a>
        </div>
        <div class="hidden sm:flex items-center space-x-4">
          <a href="#" class="hover:underline inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.437L4 17h5" />
            </svg>
            Thông báo
          </a>

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

            <form v-if="!showOtp && !showVerifyEmailForm && !showResetPassword && !isForgotMode && !isResetMode "
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
                <button @click="loginWithGoogle" type="button" class="btn-google">
             <i class="fab fa-google"></i> Đăng nhập bằng Google
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
          <div class="w-10 h-10 bg-gray-300 rounded text-center text-xs flex items-center justify-center">
            <NuxtLink to="/">Logo</NuxtLink>
          </div>
        </div>
        <!-- NAVIGATION -->
        <div class="relative group ml-20 hidden md:block">
          <a href="#" class="text-gray-700 hover:text-blue-600 font-semibold">
            Danh mục <font-awesome-icon :icon="['fas', 'bars']" />
          </a>

          <!-- MEGA MENU -->
          <div
            class="absolute left-0 mt-6 w-[1200px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-6 grid grid-cols-5 gap-6">

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
                <li><a href="#" class="hover:underline">Áo phông/ Áo thun</a></li>
                <li><a href="#" class="hover:underline">Áo polo</a></li>
                <li><a href="#" class="hover:underline">Áo sơ mi & Áo kiểu</a></li>
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
              <img src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp" alt="Nữ 1"
                class="rounded-md object-cover w-full h-48">
              <img src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp" alt="Nữ 2"
                class="rounded-md object-cover w-full h-48">
            </div>
          </div>
        </div>



        <!-- Tìm kiếm (desktop) -->
        <div class="flex-1 mx-4 hidden sm:flex justify-center">
          <input type="text" placeholder="Tìm kiếm"
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-full focus:outline-none" />
        </div>

        <!-- Menu PC -->
        <div class="hidden sm:flex items-center gap-x-6 text-sm font-medium text-gray-700">
          <!-- Trang chủ -->
          <NuxtLink href="/"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
            <font-awesome-icon :icon="['fas', 'house']" />
            Trang chủ
          </NuxtLink>

          <!-- Tài khoản -->
          <div class="relative group inline-block">
            <div
              class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
              <font-awesome-icon :icon="['fas', 'user']" />
              Tài khoản
            </div>

            <!-- Dropdown nếu có -->
            <ul class="absolute left-0 top-full hidden group-hover:flex flex-col 
                  bg-white border border-gray-200 rounded shadow-lg w-48 
                  opacity-0 group-hover:opacity-100 
                  translate-y-2 group-hover:translate-y-0 
                  transition-all duration-300 ease-in-out z-50 text-sm text-gray-700">
              <li><a href="/users/profile" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
              <li><a href="/users/order" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
              <li><a href="/support" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đăng xuất</a></li>
            </ul>
          </div>

          <!-- Giỏ hàng -->
          <NuxtLink href="/cart"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
            <font-awesome-icon :icon="['fas', 'cart-shopping']" />
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

      <!-- Tìm kiếm (mobile) -->
      <div class="px-4 pb-3 sm:hidden">
        <div class="relative">
          <input type="text" placeholder="Tìm kiếm"
            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-full focus:outline-none" />
          <button
            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#1BA0E2] p-2 rounded-full hover:bg-blue-600">
            <!-- Icon kính lúp SVG nhỏ gọn -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
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
          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'house']" /> Trang chủ
          </a>
          <div class="infor relative group inline-block">
            <!-- Nút tài khoản -->
            <a href="#" class="block text-gray-700 hover:text-blue-600 font-semibold">
              <font-awesome-icon :icon="['fas', 'user']" /> Tài khoản
            </a>

            <!-- Dropdown mượt mà -->
            <ul class="absolute left-0 top-full hidden group-hover:flex flex-col 
              bg-white border border-gray-200 rounded shadow-lg w-48 
              opacity-0 group-hover:opacity-100 
              translate-y-2 group-hover:translate-y-0 
              transition-all duration-300 ease-in-out z-50">
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đăng xuất</a></li>
            </ul>
          </div>




          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'cart-shopping']" /> Giỏ hàng
          </a>
          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'bell']" /> Thông báo
          </a>
          <NuxtLink to="/support" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'info']" /> Hỗ trợ
          </NuxtLink>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon
              :icon="['fas', 'right-to-bracket']" /> Đăng nhập</a>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'plus']" /> Đăng
            ký</a>
        </div>
      </div>
    </div>
  </div>
  <Features />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import Features from '~/components/shared/Features.vue'

const config = useRuntimeConfig()
const api = config.public.apiBaseUrl

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
 const emit = defineEmits(['loginSuccess'])

const isResetting = ref(false)
const userName = ref('')

let resendTimer = null

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  phone: '',
})
const openForgotPassword = () => {
  openLogin.value = false
  showForgotPassword.value = true
  showResetPassword.value = false
  showVerifyEmailForm.value = false
  showOtp.value = false
}

// google login
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

  // Hàm xử lý nhận thông báo từ popup
  const messageHandler = async (event) => {
    if (event.origin !== expectedOrigin) {
      console.warn('Invalid origin:', event.origin);
      return;
    }

    console.log('Received message from:', event.origin, event.data);

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
        console.error('Login verification failed:', error);
        toast('error', 'Xác thực đăng nhập thất bại.');
        localStorage.removeItem('access_token');
      } finally {
        popup?.close();
        window.removeEventListener('message', messageHandler);
      }
    }
    else if (event.data?.error) {
      toast('error', event.data.error);
      popup?.close();
      window.removeEventListener('message', messageHandler);
    }
  };

  window.addEventListener('message', messageHandler, { once: true });
}



const cancelOtp = () => {
  showOtp.value = false
  showVerifyEmailForm.value = false
  otp.value = ''
  verifyEmailInput.value = ''
  form.value = {
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    phone: '',
  }
}
const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    width: '350px',
    padding: '10px 20px',
    customClass: { popup: 'text-sm rounded-md shadow-md' },
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toastEl) => {
      toastEl.addEventListener('mouseenter', () => Swal.stopTimer())
      toastEl.addEventListener('mouseleave', () => Swal.resumeTimer())
    }
  })
}

const openLogin = () => {
  isLogin.value = true
  showModal.value = true
  showOtp.value = false
}

const openRegister = () => {
  isLogin.value = false
  showModal.value = true
  showOtp.value = false
}

const closeModal = () => {
  showModal.value = false
  showOtp.value = false
  otp.value = ''
  verifyEmailInput.value = ''
  form.value = {
    name: '', email: '', password: '', confirmPassword: '', phone: '',
  }
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (isLogin.value) {
      const res = await axios.post(`${api}/login`, {
        email: form.value.email,
        password: form.value.password,
      })

      localStorage.setItem('access_token', res.data.token)
      await fetchUserProfile()
      updateLoginState()
      toast('success', 'Đăng nhập thành công!')
      closeModal()
    } else {
      const res = await axios.post(`${api}/register`, {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.confirmPassword,
        phone: form.value.phone,
      })

      tempUserId.value = res.data.user_id
      showOtp.value = true
      startResendCountdown()
      toast('success', 'Đăng ký thành công. Kiểm tra email để lấy mã OTP.')
    }
  } catch (err) {
    if (
      isLogin.value &&
      err.response?.status === 403 &&
      err.response?.data?.message?.includes('chưa được xác minh')
    ) {
      verificationEmail.value = form.value.email
      verificationPending.value = true
      toast('warning', 'Tài khoản chưa được xác minh, vui lòng xác minh trước khi đăng nhập')
      showVerifyEmailForm.value = true
    } else {
      const msg = err.response?.data?.errors
        ? Object.values(err.response.data.errors)[0][0]
        : err.response?.data?.message || 'Đã xảy ra lỗi.'
      toast('error', msg)
    }
  } finally {
    isSubmitting.value = false
  }
}

const verifyOtp = async () => {
  isVerifying.value = true
  try {
    if (!/^[0-9]{6}$/.test(otp.value)) {
      toast('warning', 'Mã OTP phải gồm 6 chữ số.')
      return
    }

    await axios.post(`${api}/verify-otp`, {
      email: form.value.email,
      otp: otp.value,
    })

    toast('success', 'Xác minh thành công! Bạn có thể đăng nhập.')
    showOtp.value = false
    isLogin.value = true
  } catch (err) {
    toast('error', err.response?.data?.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.')
  } finally {
    isVerifying.value = false
  }
}

const sendVerificationRequest = async () => {
  isSubmitting.value = true
  try {
    const res = await axios.post(`${api}/resend-otp-by-email`, {
      email: verifyEmailInput.value,
    })

    verificationEmail.value = verifyEmailInput.value
    verifyUserId.value = res.data.user_id
    tempUserId.value = res.data.user_id
    showOtp.value = true
    showVerifyEmailForm.value = false
    startResendCountdown()
    toast('success', 'Mã xác minh đã được gửi. Vui lòng kiểm tra email!')
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi mã xác minh.')
  } finally {
    isSubmitting.value = false
  }
}

const startResendCountdown = () => {
  resendCountdown.value = 60
  clearInterval(resendTimer)
  resendTimer = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) clearInterval(resendTimer)
  }, 1000)
}

const resendVerificationEmail = async () => {
  if (!verificationEmail.value) {
    toast('warning', 'Không tìm thấy email xác minh trước đó.')
    return
  }

  try {
    await axios.post(`${api}/resend-otp-by-email`, {
      email: verificationEmail.value,
    })
    toast('success', 'Email xác minh đã được gửi lại!')
    startResendCountdown()
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi lại email.')
  }
}

const logout = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      toast('warning', 'Bạn chưa đăng nhập.')
      return
    }
    await axios.post(`${api}/logout`, {}, {
      headers: { Authorization: `Bearer ${token}` },
    })
    localStorage.removeItem('access_token')
    updateLoginState()
    toast('success', 'Đăng xuất thành công!')
  } catch (err) {
    toast('error', 'Không thể đăng xuất.')
  }
}

const fetchUserProfile = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return
  try {
    const res = await axios.get(`${api}/me`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    userName.value = res.data.data.name
    isLoggedIn.value = true
  } catch (err) {
    isLoggedIn.value = false
    userName.value = ''
    localStorage.removeItem('access_token')
  }
}

const updateLoginState = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    isLoggedIn.value = false
    userName.value = ''
    return
  }
  await fetchUserProfile()
}

const forgotEmail = ref('')
const isSending = ref(false)

const resetForm = ref({
  email: '',
  otp: '',
  password: '',
  password_confirmation: ''
})

 

const sendForgotEmail = async () => {
  isSending.value = true
  try {
    const res = await axios.post(`${api}/send-forgot-password`, { email: forgotEmail.value })

    toast('success', 'Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư đến của bạn.')

    resetForm.value.email = forgotEmail.value
    showOtp.value = false
    showVerifyEmailForm.value = false
    isLogin.value = false
    isResetMode.value = true        
    isForgotMode.value = false     
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi email đặt lại mật khẩu.')
  } finally {
    isSending.value = false
  }
}


const submitResetPassword = async () => {
  isResetting.value = true
  try {
    await axios.post(`${api}/reset-password`, resetForm.value)
    toast('success', 'Mật khẩu đã được đặt lại thành công!')
    showResetPassword.value = false
    isResetMode.value = false
    isLogin.value = true
  } catch (err) {
toast ('error', err.response?.data?.message || 'Không thể đặt lại mật khẩu.') 
  } finally {
    isResetting.value = false
  }
}

onMounted(() => {
  updateLoginState()
  window.addEventListener('storage', (e) => {
    if (e.key === 'access_token') updateLoginState()
  })
})

const isMobileMenuOpen = ref(false)
const showVerifyEmailForm = ref(false)
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
</style>