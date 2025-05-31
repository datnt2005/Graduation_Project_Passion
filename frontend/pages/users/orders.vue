<template>
  <div class="flex min-h-screen bg-gray-100 justify-center py-8">
    <div class="flex bg-white rounded-lg shadow-xl max-w-6xl w-full overflow-hidden">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-8 overflow-y-auto">
        <div class="min-h-full">
          <h2 class="text-3xl font-extrabold text-gray-900 mb-2 text-center">Quản lý Đơn hàng</h2>
          <p class="text-gray-500 text-lg mb-8 text-center">Tất cả các đơn hàng bạn đã đặt</p>

          <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center space-x-4">
              <label for="items-per-page" class="text-gray-700 text-sm font-medium">Hiển thị</label>
              <select
                id="items-per-page"
                class="border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
              >
                <option>5</option>
                <option>10</option>
                <option>20</option>
                <option>50</option>
              </select>
              <span class="text-gray-700 text-sm">mục trên mỗi trang</span>
            </div>
            <button
              class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
            >
              <svg class="w-4 h-4 mr-2 -ml-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path></svg>
              Lọc
            </button>
          </div>

          <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn hàng</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">1</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">#DH333</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Đã thanh toán
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">$199.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-center space-x-2">
                      <NuxtLink :to="`/orders/333`" class="text-blue-600 hover:text-blue-900 px-3 py-1 border border-blue-600 rounded-md text-xs transition duration-150 ease-in-out">
                        Chi tiết
                      </NuxtLink>
                      <button class="text-red-600 hover:text-red-900 px-3 py-1 border border-red-600 rounded-md text-xs transition duration-150 ease-in-out">
                        Hủy đơn
                      </button>
                      <button class="text-gray-600 hover:text-gray-900 px-3 py-1 border border-gray-400 rounded-md text-xs transition duration-150 ease-in-out">
                        In hoá đơn
                      </button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">2</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">#DH334</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      Chưa thanh toán
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">$199.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-center space-x-2">
                      <NuxtLink :to="`/orders/334`" class="text-blue-600 hover:text-blue-900 px-3 py-1 border border-blue-600 rounded-md text-xs transition duration-150 ease-in-out">
                        Chi tiết
                      </NuxtLink>
                      <button class="text-red-600 hover:text-red-900 px-3 py-1 border border-red-600 rounded-md text-xs transition duration-150 ease-in-out">
                        Hủy đơn
                      </button>
                      <button class="text-gray-600 hover:text-gray-900 px-3 py-1 border border-gray-400 rounded-md text-xs transition duration-150 ease-in-out">
                        In hoá đơn
                      </button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">3</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">#DH335</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                      Đang xử lý
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">$199.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-center space-x-2">
                      <NuxtLink :to="`/orders/335`" class="text-blue-600 hover:text-blue-900 px-3 py-1 border border-blue-600 rounded-md text-xs transition duration-150 ease-in-out">
                        Chi tiết
                      </NuxtLink>
                      <button class="text-red-600 hover:text-red-900 px-3 py-1 border border-red-600 rounded-md text-xs transition duration-150 ease-in-out">
                        Hủy đơn
                      </button>
                      <button class="text-gray-600 hover:text-gray-900 px-3 py-1 border border-gray-400 rounded-md text-xs transition duration-150 ease-in-out">
                        In hoá đơn
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-center items-center mt-8 space-x-2">
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">&laquo;</button>
            <button class="px-4 py-2 border border-blue-600 bg-blue-600 text-white rounded-md shadow-sm">1</button>
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">2</button>
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">3</button>
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">4</button>
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">5</button>
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">&raquo;</button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
// Import SidebarProfile component (make sure the path is correct)
import SidebarProfile from '~/components/shared/Sidebar-profile.vue';

// For a real application, you would fetch orders here
// const orders = ref([]);
// onMounted(async () => {
//   orders.value = await fetchOrders(); // Replace with your actual API call
// });
</script>

<style scoped>
/* Add any specific styles if needed */
</style>