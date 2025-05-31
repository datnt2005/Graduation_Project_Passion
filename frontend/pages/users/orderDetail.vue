<template>
  <div class="flex min-h-screen bg-gray-100 justify-center py-8">
    <div class="flex bg-white rounded-lg shadow-xl max-w-6xl w-full overflow-hidden">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-8 overflow-y-auto">
        <div class="min-h-full">
          <div class="flex items-center mb-6">
            <NuxtLink to="/orders" class="text-[#1BA0E2] hover:text-[#167DBD] mr-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </NuxtLink>
            <h2 class="text-3xl font-extrabold text-gray-900">Chi tiết Đơn hàng #{{ orderId }}</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="md:col-span-1 bg-[#E6F5FD] p-6 rounded-lg shadow-sm border border-[#A8DDF7]">
              <h3 class="text-xl font-bold text-[#1BA0E2] mb-4">Thông tin Đơn hàng</h3>
              <div class="space-y-2 text-sm text-[#167DBD]">
                <p><strong>Mã đơn hàng:</strong> #{{ orderId }}</p>
                <p><strong>Ngày đặt hàng:</strong> 25/05/2025</p>
                <p><strong>Trạng thái:</strong>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Đã thanh toán
                  </span>
                </p>
                <p><strong>Tổng tiền:</strong> <span class="text-lg font-bold text-[#0D47A1]">$199.00</span></p>
              </div>
            </div>

            <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Địa chỉ Giao hàng</h3>
              <div class="space-y-2 text-sm text-gray-700">
                <p><strong>Người nhận:</strong> Nguyễn Văn A</p>
                <p><strong>Số điện thoại:</strong> 0987654321</p>
                <p><strong>Địa chỉ:</strong> 123 Đường ABC, Phường XYZ, Quận 1, TP. Hồ Chí Minh</p>
              </div>
            </div>

            <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Phương thức Thanh toán</h3>
              <div class="space-y-2 text-sm text-gray-700">
                <p><strong>Phương thức:</strong> Chuyển khoản ngân hàng</p>
                <p><strong>Trạng thái thanh toán:</strong> Đã thanh toán</p>
                <p><strong>Mã giao dịch:</strong> TXN123456789</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
            <h3 class="text-xl font-bold text-gray-800 p-6 border-b border-gray-200 bg-gray-50">Sản phẩm trong đơn hàng</h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                    <img src="https://didongmoi.com.vn/upload/images/product/iphone-15-chinh-hang-cu-2.jpg" alt="Product Image" class="w-10 h-10 rounded mr-3">
                    Sản phẩm A
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100.000đ</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100.000đ</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                    <img src="https://didongmoi.com.vn/upload/images/product/iphone-15-chinh-hang-cu-2.jpg" alt="Product Image" class="w-10 h-10 rounded mr-3">
                    Sản phẩm B
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100.000đ</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">200.000đ</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="bg-gray-50">
                  <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-700">Tạm tính:</td>
                  <td class="px-6 py-3 text-left text-sm font-bold text-gray-900">300.000đ</td>
                </tr>
                <tr class="bg-gray-50">
                  <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-700">Phí vận chuyển:</td>
                  <td class="px-6 py-3 text-left text-sm font-bold text-gray-900">30.000đ</td>
                </tr>
                <tr class="bg-gray-100">
                  <td colspan="3" class="px-6 py-3 text-right text-lg font-extrabold text-gray-900">Tổng cộng:</td>
                  <td class="px-6 py-3 text-left text-lg font-extrabold text-[#1BA0E2]">330.000đ</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="flex justify-end space-x-4">
            <button class="px-6 py-3 bg-red-500 text-white rounded-md shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
              Hủy đơn hàng
            </button>
            <button class="px-6 py-3 bg-[#1BA0E2] text-white rounded-md shadow-md hover:bg-[#167DBD] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1BA0E2] transition duration-150 ease-in-out">
              In hoá đơn
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import SidebarProfile from '~/components/shared/Sidebar-profile.vue';

const route = useRoute();
const orderId = computed(() => route.params.id);

// In a real application, you would fetch order details based on orderId
// const orderDetails = ref(null);
// onMounted(async () => {
//   orderDetails.value = await fetchOrderDetails(orderId.value); // Replace with your actual API call
// });
</script>

<style scoped>
/* Specific styles for order detail page */
</style>