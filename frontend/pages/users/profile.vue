<template>
  <div class="flex min-h-screen bg-gray-100 justify-center py-8">
    <div class="flex bg-white rounded-lg shadow-md max-w-5xl w-full">
      <SidebarProfile class="flex-shrink-0" />

      <main class="flex-1 p-8 overflow-y-auto">
        <div class="min-h-full">
          <h2 class="text-2xl font-bold text-center mb-2">Hồ sơ của tôi</h2>
          <p class="text-gray-600 text-center mb-8">Quản lí hồ sơ để thông tin tài khoản của bạn được bảo mật</p>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            <div class="md:col-span-2 space-y-5">
              <div class="flex items-center">
                <label for="username" class="w-32 text-right text-gray-700 text-sm mr-4">Tên đăng nhập</label>
                <input
                  type="text"
                  id="username"
                  value="Trần Tiến Phát"
                  class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                >
              </div>
              <div class="flex items-center">
                <label for="email" class="w-32 text-right text-gray-700 text-sm mr-4">Email</label>
                <input
                  type="email"
                  id="email"
                  value="phatttpk03754@gmail.com"
                  class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                >
              </div>
              <div class="flex items-center">
                <label for="phone" class="w-32 text-right text-gray-700 text-sm mr-4">Số điện thoại</label>
                <input
                  type="text"
                  id="phone"
                  value="094****548"
                  class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                >
              </div>
              <div class="flex items-center">
                <span class="w-32 text-right text-gray-700 text-sm mr-4">Giới tính</span>
                <div class="flex items-center space-x-6">
                  <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="Nam" class="form-radio text-blue-600 w-4 h-4">
                    <span class="ml-2 text-gray-700 text-sm">Nam</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="Nữ" class="form-radio text-blue-600 w-4 h-4">
                    <span class="ml-2 text-gray-700 text-sm">Nữ</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="Khác" class="form-radio text-blue-600 w-4 h-4">
                    <span class="ml-2 text-gray-700 text-sm">Khác</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="md:col-span-1 flex flex-col items-center justify-center border-l border-gray-200 pl-8">
              <div class="w-32 h-32 rounded-full overflow-hidden mb-4 border border-gray-300 flex-shrink-0">
                <img src="https://scontent.fsgn2-7.fna.fbcdn.net/v/t39.30808-6/488853217_1403799304327811_4217198369891756285_n.jpg?_nc_cat=108&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeHciyMx1dL8MLFQhCIgylsfLsjRN7AdrpsuyNE3sB2um3iQlFa9SsqBaHB5uBw24uXcaOfsSIYtmOsZuVdYivAC&_nc_ohc=MjCpNL29a28Q7kNvwGK-rkI&_nc_oc=Adl69t3p2kGP4bUs-mBcf2eki-o5rEmpb2AcvTLXr8MPn1JvuC_dLtVX9iU4IKwNQ5G84TZs_4VyYp_-jMmXlzm7&_nc_zt=23&_nc_ht=scontent.fsgn2-7.fna&_nc_gid=BsWXbAqMV2-h8kuJHig4Fw&oh=00_AfLvGZqvCnOkh-YfiDqxSd2yCMcvyyFQuUgixksjgcaAcA&oe=683D14D2" alt="Avatar" class="w-full h-full object-cover">
              </div>
              <button class="px-5 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-sm">
                Chọn ảnh
              </button>
              <p class="text-xs text-gray-500 mt-2 text-center">Dung lượng file tối đa 1 MB</p>
              <p class="text-xs text-gray-500 text-center">Định dạng: JPEG, PNG</p>
            </div>
          </div>

          <div class="mt-8 text-center">
            <button class="px-10 py-3 bg-blue-500 text-white font-semibold rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-lg">
              LƯU
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import SidebarProfile from '~/components/shared/Sidebar-profile.vue';
// Lấy ID người dùng từ route params
const route = useRoute();
const userId = route.params.id;

// Dữ liệu và hàm của form sẽ ở đây
</script>

<style scoped>
/* Scoped styles specific to this page */
</style>