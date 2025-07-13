```vue
<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đơn hàng</h1>
      </div>

      <!-- Cảnh báo đơn hàng bất thường -->
      <div v-if="hasAbnormalOrders" class="bg-yellow-100 p-4 mb-4 mx-4 rounded text-yellow-700">
        Có {{ abnormalOrdersCount }} đơn hàng ở trạng thái bất thường (thất bại, hủy, trả hàng hoặc thiếu thông tin
        payout). Vui lòng kiểm tra!
      </div>

      <!-- Nút chuyển đổi -->
      <div class="flex gap-2 mb-4 px-4 pt-4">
        <button @click="showPayoutList = false; showLogs = false; showRefunds = false"
          :class="['px-4 py-2 rounded', !showPayoutList && !showLogs && !showRefunds ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Đơn hàng
        </button>
        <button @click="showPayoutList = true; showLogs = false; showRefunds = false; fetchPayoutData()"
          :class="['px-4 py-2 rounded', showPayoutList && !showLogs && !showRefunds ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Thanh toán đã cập nhật
        </button>
        <button @click="showPayoutList = false; showLogs = true; showRefunds = false; fetchLogs()"
          :class="['px-4 py-2 rounded', showLogs && !showRefunds ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Nhật ký đồng bộ
        </button>
        <button @click="showPayoutList = false; showLogs = false; showRefunds = true; fetchRefunds()"
          :class="['px-4 py-2 rounded', showRefunds ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Yêu cầu hoàn tiền
        </button>
      </div>

      <!-- Tab Đơn hàng -->
      <div v-if="!showPayoutList && !showLogs && !showRefunds">
        <!-- Filter Bar -->
        <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
          <div class="flex items-center gap-2">
            <span class="font-bold">Tất cả</span>
            <span>({{ totalItems }})</span>
          </div>
          <div class="flex gap-2">
            <select v-model="filters.status"
              class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ xử lý</option>
              <option value="confirmed">Đã xác nhận</option>
              <option value="processing">Đang xử lý</option>
              <option value="shipping">Đang giao</option>
              <option value="delivered">Đã giao</option>
              <option value="cancelled">Đã hủy</option>
              <option value="refunded">Đã hoàn tiền</option>
              <option value="failed">Giao thất bại</option>
            </select>
            <select v-model="filters.payment_method"
              class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Tất cả phương thức</option>
              <option v-for="method in paymentMethods" :key="method.id" :value="method.name">{{
                getPaymentMethodText(method.name) }}</option>
            </select>
            <input type="date" v-model="filters.from_date"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Từ ngày">
            <input type="date" v-model="filters.to_date"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Đến ngày">
            <input type="text" v-model="filters.order_id" placeholder="Mã đơn hàng"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <input type="text" v-model="filters.tracking_code" placeholder="Mã vận đơn"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="ml-auto flex gap-2">
            <button @click="resetFilters" class="px-4 py-2 border rounded-md bg-white hover:bg-gray-50">Đặt lại</button>
            <button @click="fetchOrders" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Tìm
              kiếm</button>
          </div>
        </div>

        <!-- Table -->
        <table class="min-w-full border-collapse border border-gray-300 text-sm">
          <thead class="bg-white border-b border-gray-300">
            <tr>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Mã vận đơn</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Khách hàng</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tổng tiền</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phương thức thanh toán
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái đơn hàng
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái payout</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders" :key="order.id" :class="{ 'bg-gray-50': order.id % 2 === 0 }"
              class="border-b border-gray-300">
              <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">{{
                order.shipping?.tracking_code || 'Chưa có' }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                {{ order.user?.name || 'N/A' }}<br>
                <span class="text-xs">{{ order.user?.email || 'N/A' }}</span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ formatPrice(order.final_price) }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ getPaymentMethodText(order.payments[0]?.method)
                }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <span :class="getStatusClass(order.status)"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ getStatusText(order.status) }}
                </span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <span :class="payoutStatusClass(order.payout_status)"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ payoutStatusText(order.payout_status) || 'Chưa có' }}
                </span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ formatDate(order.created_at) }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <div class="relative inline-block text-left">
                  <button @click.stop="toggleDropdown(order.id)"
                    class="inline-flex items-center text-gray-600 hover:text-gray-800 focus:outline-none">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                  <div v-if="activeDropdown === order.id"
                    class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                      <button @click="showOrderDetails(order); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Xem chi tiết</button>
                      <button @click="updateOrderStatus(order)"
                        class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Đổi trạng
                        thái</button>
                      <button
                        v-if="order.status === 'delivered' && order.payout_status === 'pending' && order.payout_id"
                        @click="approvePayout(order); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50">Duyệt
                        payout</button>
                      <button v-if="order.status === 'delivered' && !order.payout_id"
                        @click="createPayout(order); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">Tạo payout</button>
                      <button v-if="order.shipping?.tracking_code"
                        @click="verifyGhnStatus(order); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-purple-50">Kiểm tra
                        GHN</button>
                      <button @click="deleteOrder(order.id); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Xóa</button>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Hiển thị {{ (currentPage - 1) * perPage + 1 }} đến {{ Math.min(currentPage * perPage, totalItems) }} trong
              tổng số {{ totalItems }} đơn hàng
            </div>
            <div class="flex space-x-2">
              <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                class="px-3 py-1 border rounded-md disabled:opacity-50">Trước</button>
              <button v-for="p in totalPages" :key="p" @click="changePage(p)"
                :class="['px-3 py-1 border rounded-md', currentPage === p ? 'bg-blue-600 text-white' : 'bg-white text-gray-700']">{{
                p }}</button>
              <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                class="px-3 py-1 border rounded-md disabled:opacity-50">Sau</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Nhật ký đồng bộ GHN -->
      <div v-if="showLogs" class="bg-white p-6 rounded shadow w-full overflow-x-auto">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
          <span>📜</span> Nhật ký đồng bộ GHN
        </h2>
        <div v-if="logLoading" class="text-center text-gray-400 py-10">Đang tải nhật ký...</div>
        <div v-else-if="logError" class="text-center text-red-500 py-10">{{ logError }}</div>
        <div v-else-if="!logs.length" class="text-center text-gray-400 py-10">Không có nhật ký nào</div>
        <div v-else class="mt-4">
          <table class="w-full table-auto divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã vận đơn</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Trạng thái GHN</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Thời gian</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Kết quả</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Chi tiết</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logs" :key="log.id" class="hover:bg-blue-50 transition">
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">{{ log.tracking_code }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span :class="ghnStatusMap[log.ghn_status]?.class || 'bg-gray-100 text-gray-800'">
                    {{ statusText(log.ghn_status) }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(log.created_at) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span :class="log.success ? 'text-green-600' : 'text-red-600'">{{ log.success ? 'Thành công' : 'Thất bại' }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ log.message }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab Payout -->
      <div v-if="showPayoutList">
        <div class="bg-white p-6 rounded shadow w-full overflow-x-auto">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span>💸</span> Danh sách thanh toán đã cập nhật
          </h2>
          <div class="flex flex-wrap gap-3 mb-4">
            <input v-model="payoutTrackingKeyword" type="text" placeholder="Tìm theo mã vận đơn (tracking_code)"
              class="border p-2 rounded flex-1 min-w-[180px] placeholder-gray-400">
            <select v-model="payoutSortOption" class="border p-2 rounded min-w-[160px]">
              <option value="transferred_desc">Mới nhất (ngày chuyển khoản)</option>
              <option value="created_desc">Gần đây nhất (ngày tạo)</option>
              <option value="created_asc">Cũ nhất</option>
            </select>
          </div>
          <div v-if="payoutLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
          <div v-else-if="payoutError" class="text-center text-red-500 py-10">{{ payoutError }}</div>
          <div v-else-if="!payoutTrackingFilteredData.length" class="text-center text-gray-400 py-10">Không có payout
            nào</div>
          <div v-else class="mt-4">
            <table class="w-full table-auto divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã payout</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã vận đơn</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Số tiền</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Ngày yêu cầu</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Ngày duyệt</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Trạng thái</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Ghi chú</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in payoutTrackingPaginatedData" :key="item.id" class="hover:bg-blue-50 transition">
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">{{ item.id }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">{{
                    getTrackingCode(item.order_id) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatPrice(item.amount) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.created_at) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.transferred_at) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span :class="payoutStatusClass(item.status)">{{ payoutStatusLabel(item.status) }}</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.note }}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="payoutTrackingTotalPages > 1" class="flex justify-center mt-4">
              <button @click="payoutTrackingPage--" :disabled="payoutTrackingPage === 1"
                class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">
                <
              </button>
              <button v-for="p in payoutTrackingTotalPages" :key="p" @click="payoutTrackingPage = p"
                :class="['px-3 py-1 mx-1 rounded border', payoutTrackingPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{
                p }}</button>
              <button @click="payoutTrackingPage++" :disabled="payoutTrackingPage === payoutTrackingTotalPages"
                class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Yêu cầu hoàn tiền -->
      <div v-if="showRefunds" class="bg-white p-6 rounded shadow w-full overflow-x-auto">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
          <span>💰</span> Danh sách yêu cầu hoàn tiền
        </h2>
        <div class="flex flex-wrap gap-3 mb-4">
          <input v-model="refundSearchKeyword" type="text" placeholder="Tìm theo mã đơn hàng hoặc mã vận đơn"
            class="border p-2 rounded flex-1 min-w-[180px] placeholder-gray-400">
          <select v-model="refundFilterStatus" class="border p-2 rounded min-w-[160px]">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="approved">Đã duyệt</option>
            <option value="rejected">Đã từ chối</option>
          </select>
        </div>
        <div v-if="refundLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
<div v-else-if="refundError" class="text-center text-red-500 py-10">{{ refundError }}</div>
<div v-else-if="!refundFilteredData.length" class="text-center text-gray-400 py-10">
  Không có yêu cầu hoàn tiền nào phù hợp với bộ lọc hiện tại.
</div>
        <div v-else class="mt-4">
          <table class="w-full table-auto divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã hoàn tiền</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã đơn hàng</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã vận đơn</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Số tiền</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Trạng thái</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Lý do</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Ngày tạo</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="refund in refundPaginatedData" :key="refund.id" class="hover:bg-blue-50 transition">
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">{{ refund.id }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">{{ refund.order_id }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">{{
                  refund.order?.shipping?.tracking_code || 'Chưa có' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatPrice(refund.amount) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <span :class="refundStatusClass(refund.status)">{{ refundStatusText(refund.status) }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ refund.reason }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(refund.created_at) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                  <div v-if="refund.status === 'pending'" class="flex gap-2">
                    <button @click="approveRefund(refund)" class="px-2 py-1 text-green-600 hover:bg-green-50 border rounded">
                      Duyệt
                    </button>
                    <button @click="rejectRefund(refund)" class="px-2 py-1 text-red-600 hover:bg-red-50 border rounded">
                      Từ chối
                    </button>
                    <button @click="editRefund(refund)" class="px-2 py-1 text-blue-600 hover:bg-blue-50 border rounded">
                      Sửa
                    </button>
                  </div>
                  <div v-else class="flex gap-2">
                    <button @click="editRefund(refund)" class="px-2 py-1 text-blue-600 hover:bg-blue-50 border rounded">
                      Sửa
                    </button>
                    <button @click="deleteRefund(refund.id)" class="px-2 py-1 text-red-600 hover:bg-red-50 border rounded">
                      Xóa
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="refundTotalPages > 1" class="flex justify-center mt-4">
            <button @click="refundPage--" :disabled="refundPage === 1"
              class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">
              <
            </button>
            <button v-for="p in refundTotalPages" :key="p" @click="refundPage = p"
              :class="['px-3 py-1 mx-1 rounded border', refundPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{
              p }}</button>
            <button @click="refundPage++" :disabled="refundPage === refundTotalPages"
              class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">></button>
          </div>
        </div>
      </div>

      <!-- Modal chi tiết đơn hàng -->
      <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 relative">
          <button @click="selectedOrder = null" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h2 class="text-lg font-semibold mb-4">Chi tiết đơn hàng #{{ selectedOrder.id }}</h2>
          <div class="space-y-4">
            <!-- Thông tin đơn hàng -->
            <div class="border border-gray-200 rounded-lg">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin đơn hàng</div>
              <div class="px-4 py-3 text-sm text-gray-700">
                <p><b>Mã đơn hàng:</b> {{ selectedOrder.id }}</p>
                <p><b>Mã vận đơn:</b> {{ selectedOrder.shipping?.tracking_code || 'Chưa có' }}</p>
                <p><b>Trạng thái đơn hàng:</b> <span :class="getStatusClass(selectedOrder.status)">{{
                  getStatusText(selectedOrder.status) }}</span></p>
                <p><b>Trạng thái GHN:</b> {{ statusText(selectedOrder.shipping?.status) || 'Chưa đồng bộ' }}</p>
                <p><b>Ngày tạo:</b> {{ formatDate(selectedOrder.created_at) }}</p>
                <p v-if="selectedOrder.shipping?.tracking_code" class="mt-2">
                  <button @click="verifyGhnStatus(selectedOrder)"
                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Kiểm tra trạng thái
                    GHN</button>
                </p>
              </div>
            </div>
            <!-- Thông tin thanh toán -->
            <div class="border border-gray-200 rounded-lg">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán</div>
              <div class="px-4 py-3 text-sm text-gray-700">
                <p><b>Tổng tiền hàng:</b> {{ formatPrice(selectedOrder.final_price) }}</p>
                <p v-if="selectedOrder.shipping?.shipping_fee > 0"><b>Phí vận chuyển:</b> {{
                  formatPrice(selectedOrder.shipping.shipping_fee) }}</p>
                <p v-if="selectedOrder.discount_price > 0"><b>Giảm giá:</b> {{ formatPrice(selectedOrder.discount_price)
                  }}</p>
                <p><b>Phương thức thanh toán:</b> {{ getPaymentMethodText(selectedOrder.payments[0]?.method) }}</p>
              </div>
            </div>
            <!-- Thông tin thanh toán cho shop -->
            <div class="border border-gray-200 rounded-lg">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán cho shop
              </div>
              <div class="px-4 py-3 text-sm text-gray-700">
                <p><b>Trạng thái thanh toán:</b> <span :class="payoutStatusClass(selectedOrder?.payout_status)">{{
                  payoutStatusText(selectedOrder?.payout_status) || 'Chưa có' }}</span></p>
                <p><b>Tổng tiền hàng:</b> {{ formatPrice(selectedOrder?.final_price) }}</p>
                <p v-if="selectedOrder?.shipping?.shipping_fee > 0"><b>Phí vận chuyển:</b> {{
                  formatPrice(selectedOrder.shipping.shipping_fee) }}</p>
                <p v-if="selectedOrder?.discount_price > 0"><b>Giảm giá:</b> {{
                  formatPrice(selectedOrder.discount_price) }}</p>
                <p><b>Chiết khấu admin (5%):</b> {{ formatPrice(Math.max((Number(selectedOrder?.final_price || 0) -
                  Number(selectedOrder?.shipping?.shipping_fee || 0)) * 0.05, 0)) }}</p>
                <p><b>Ước tính số tiền nhận được:</b> {{ formatPrice(Math.max((Number(selectedOrder?.final_price || 0) -
                  Number(selectedOrder?.shipping?.shipping_fee || 0)) * 0.95, 0)) }}</p>
                <p><b>Số tiền nhận được:</b> <span
                    v-if="selectedOrder?.payout_amount && selectedOrder.payout_status === 'completed'">{{
                      formatPrice(selectedOrder.payout_amount) }}</span><span v-else class="text-gray-500">---</span></p>
                <p><b>Thời gian chuyển khoản:</b> <span
                    v-if="selectedOrder?.transferred_at && selectedOrder.payout_status === 'completed'">{{
                      formatDate(selectedOrder.transferred_at) }}</span><span v-else class="text-gray-500">---</span></p>
                <p v-if="selectedOrder?.status === 'delivered' && !selectedOrder.payout_id" class="mt-2">
                  <button @click="createPayout(selectedOrder)"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tạo payout thủ công</button>
                </p>
                <p v-if="selectedOrder?.status === 'delivered' && selectedOrder.payout_status === 'pending' && selectedOrder.payout_id"
                  class="mt-2">
                  <button @click="approvePayout(selectedOrder)"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Duyệt payout</button>
                </p>
              </div>
            </div>
            <!-- Xử lý hoàn tiền -->
            <div v-if="['failed', 'cancelled', 'refunded', 'returned'].includes(selectedOrder?.status)"
              class="border border-gray-200 rounded-lg">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Xử lý hoàn tiền</div>
              <div class="px-4 py-3 text-sm text-gray-700">
                <p><b>Lý do hiện tại:</b> {{ selectedOrder?.note || 'Chưa có ghi chú' }}</p>
                <div v-if="!selectedOrder?.refund" class="mt-2">
                  <label class="block mb-1">Số tiền hoàn (VND):</label>
                  <input v-model.number="refundAmount" type="number" min="0" :max="maxRefundAmount"
                    class="w-full border rounded px-3 py-2" placeholder="Nhập số tiền hoàn">
                </div>
                <div v-if="!selectedOrder?.refund" class="mt-2">
                  <label class="block mb-1">Lý do hoàn tiền:</label>
                  <textarea v-model="refundReason" class="w-full border rounded px-3 py-2"
                    placeholder="Nhập lý do hoàn tiền"></textarea>
                </div>
                <div v-if="!selectedOrder?.refund" class="mt-2">
                  <button @click="processRefund(selectedOrder)"
                    class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700"
                    :disabled="!refundAmount || !refundReason || refundAmount <= 0 || refundAmount > maxRefundAmount">Xử
                    lý hoàn tiền</button>
                </div>
                <!-- Hiển thị thông tin hoàn tiền nếu có -->
                <div v-if="selectedOrder?.refund" class="mt-4">
                  <p><b>Thông tin hoàn tiền:</b></p>
                  <p><b>Mã hoàn tiền:</b> {{ selectedOrder.refund.id }}</p>
                  <p><b>Số tiền hoàn:</b> {{ formatPrice(selectedOrder.refund.amount) }}</p>
                  <p><b>Trạng thái:</b> <span :class="refundStatusClass(selectedOrder.refund.status)">{{
                    refundStatusText(selectedOrder.refund.status) }}</span></p>
                  <p><b>Lý do:</b> {{ selectedOrder.refund.reason }}</p>
                  <p><b>Thời gian tạo:</b> {{ formatDate(selectedOrder.refund.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal cập nhật trạng thái payout -->
      <div v-if="showUpdateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
          <button @click="closeUpdateModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h2 class="text-lg font-semibold mb-4">Cập nhật trạng thái payout</h2>
          <div class="mb-4">
            <div><b>Đơn hàng - Mã vận đơn:</b> {{ orderToUpdate?.shipping?.tracking_code || 'Chưa có' }}</div>
            <div><b>Số tiền payout:</b> {{ formatPrice(orderToUpdate?.payout_amount || orderToUpdate?.amount) }}</div>
            <div><b>Trạng thái hiện tại:</b> <span class="font-semibold">{{
              payoutStatusText(orderToUpdate?.payout_status) }}</span></div>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Chọn trạng thái payout mới:</label>
            <select v-model="newPayoutStatus" class="w-full border rounded px-3 py-2">
              <option value="pending">Chờ xử lý</option>
              <option value="completed">Đã chuyển khoản</option>
              <option value="failed">Thất bại</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button @click="closeUpdateModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Hủy</button>
            <button @click="confirmUpdatePayoutStatus"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" :disabled="loading">Cập nhật</button>
          </div>
        </div>
      </div>

      <!-- Modal chỉnh sửa hoàn tiền -->
      <div v-if="showEditRefundModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
          <button @click="closeEditRefundModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h2 class="text-lg font-semibold mb-4">Chỉnh sửa yêu cầu hoàn tiền</h2>
          <div class="mb-4">
            <div><b>Mã hoàn tiền:</b> {{ refundToEdit?.id }}</div>
            <div><b>Mã đơn hàng:</b> {{ refundToEdit?.order_id }}</div>
            <div><b>Mã vận đơn:</b> {{ refundToEdit?.order?.shipping?.tracking_code || 'Chưa có' }}</div>
            <div><b>Số tiền tối đa:</b> {{ formatPrice(maxRefundAmount) }}</div>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Số tiền hoàn (VND):</label>
            <input v-model.number="refundToEdit.amount" type="number" min="0" :max="maxRefundAmount"
              class="w-full border rounded px-3 py-2" placeholder="Nhập số tiền hoàn">
          </div>
          <div class="mb-4">
            <label class="block mb-1">Lý do hoàn tiền:</label>
            <textarea v-model="refundToEdit.reason" class="w-full border rounded px-3 py-2"
              placeholder="Nhập lý do hoàn tiền"></textarea>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Trạng thái:</label>
            <select v-model="refundToEdit.status" class="w-full border rounded px-3 py-2">
              <option value="pending">Chờ xử lý</option>
              <option value="approved">Đã duyệt</option>
              <option value="rejected">Đã từ chối</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button @click="closeEditRefundModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Hủy</button>
            <button @click="confirmEditRefund" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              :disabled="loading">Cập nhật</button>
          </div>
        </div>
      </div>

      <!-- Notification Popup -->
      <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200"
          enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-100" leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95">
          <div v-if="notification.show"
            class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
            <div class="flex-shrink-0">
              <svg v-if="notification.success" class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ notification.message }}</p>
            </div>
            <div class="flex-shrink-0">
              <button @click="closeNotification"
                class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>
```


<script setup>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue';
import { useRuntimeConfig } from '#app';
import Swal from 'sweetalert2';

definePageMeta({
  layout: 'default-admin'
});

// State
const orders = ref([]);
const selectedOrder = ref(null);
const showUpdateModal = ref(false);
const newPayoutStatus = ref('');
const orderToUpdate = ref(null);
const loading = ref(false);
const currentPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);
const totalPages = ref(1);
const activeDropdown = ref(null);
const showPayoutList = ref(false);
const showLogs = ref(false);
const showRefunds = ref(false);
const payoutLoading = ref(false);
const payoutError = ref('');
const payoutData = ref([]);
const payoutTrackingKeyword = ref('');
const payoutTrackingPage = ref(1);
const payoutTrackingPageSize = ref(10);
const payoutSortOption = ref('transferred_desc');
const logs = ref([]);
const logLoading = ref(false);
const logError = ref('');
const refundAmount = ref(0);
const refundReason = ref('');
const paymentMethods = ref([]);
const paymentLoading = ref(false);
const refunds = ref([]);
const refundLoading = ref(false);
const refundError = ref('');
const refundSearchKeyword = ref('');
const refundFilterStatus = ref('');
const refundPage = ref(1);
const refundPageSize = ref(10);
const showEditRefundModal = ref(false);
const refundToEdit = ref(null);
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

// Computed
const hasAbnormalOrders = computed(() => {
  return orders.value.some(o => ['failed', 'cancelled', 'refunded', 'returned'].includes(o.status) || (o.status === 'delivered' && !o.payout_id));
});

const abnormalOrdersCount = computed(() => {
  return orders.value.filter(o => ['failed', 'cancelled', 'refunded', 'returned'].includes(o.status) || (o.status === 'delivered' && !o.payout_id)).length;
});

const maxRefundAmount = computed(() => {
  if (!selectedOrder.value) return 0;
  return Math.max((Number(selectedOrder.value.final_price || 0) - Number(selectedOrder.value.shipping?.shipping_fee || 0)), 0);
});

const payoutTrackingFilteredData = computed(() => {
  let arr = payoutData.value;
  if (payoutTrackingKeyword.value) {
    const kw = payoutTrackingKeyword.value.toLowerCase();
    arr = arr.filter(item => {
      const code = getTrackingCode(item.order_id).toLowerCase();
      return code.includes(kw);
    });
  }
  return [...arr].sort((a, b) => {
    const dateA = a[payoutSortOption.value.includes('transferred') ? 'transferred_at' : 'created_at'] || '1970-01-01';
    const dateB = b[payoutSortOption.value.includes('transferred') ? 'transferred_at' : 'created_at'] || '1970-01-01';
    return payoutSortOption.value.includes('asc')
      ? new Date(dateA) - new Date(dateB)
      : new Date(dateB) - new Date(dateA);
  });
});

const payoutTrackingTotalPages = computed(() => Math.ceil(payoutTrackingFilteredData.value.length / payoutTrackingPageSize.value));

const payoutTrackingPaginatedData = computed(() => {
  const start = (payoutTrackingPage.value - 1) * payoutTrackingPageSize.value;
  return payoutTrackingFilteredData.value.slice(start, start + payoutTrackingPageSize.value);
});

const refundFilteredData = computed(() => {
  let arr = refunds.value;
  console.log('Raw refunds:', arr);
  if (refundSearchKeyword.value) {
    const kw = refundSearchKeyword.value.toLowerCase();
    arr = arr.filter(item => {
      const orderId = String(item.order_id || '').toLowerCase();
      const trackingCode = String(item.order?.shipping?.tracking_code || '').toLowerCase();
      return orderId.includes(kw) || trackingCode.includes(kw);
    });
  }
  if (refundFilterStatus.value) {
    arr = arr.filter(item => item.status === refundFilterStatus.value);
  }
  console.log('Filtered refunds:', arr);
  return arr.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const refundTotalPages = computed(() => Math.ceil(refundFilteredData.value.length / refundPageSize.value));

const refundPaginatedData = computed(() => {
  const start = (refundPage.value - 1) * refundPageSize.value;
  return refundFilteredData.value.slice(start, start + refundPageSize.value);
});

const notification = ref({
  show: false,
  message: '',
  success: true,
  timeout: null
});

const filters = ref({
  status: '',
  payment_method: '',
  from_date: '',
  to_date: '',
  order_id: '',
  tracking_code: ''
});

// Watch
watch([payoutTrackingKeyword, payoutSortOption], () => { payoutTrackingPage.value = 1; });
watch([refundSearchKeyword, refundFilterStatus], () => {
  refundPage.value = 1;
  console.log('Filter changed:', {
    keyword: refundSearchKeyword.value,
    status: refundFilterStatus.value
  });
});
watch(filters, () => { currentPage.value = 1; fetchOrders(); }, { deep: true });

// Methods
const formatDate = (dateStr) => {
  if (!dateStr) {
    console.warn('Empty date string received');
    return '-';
  }

  try {
    // Handle DD/MM/YYYY HH:mm:ss format from API
    const regex = /^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})$/;
    if (regex.test(dateStr)) {
      const match = dateStr.match(regex);
      const date = new Date(`${match[3]}-${match[2]}-${match[1]}T${match[4]}:${match[5]}:${match[6]}+07:00`);
      if (!isNaN(date.getTime())) {
        return date.toLocaleDateString('vi-VN', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          timeZone: 'Asia/Ho_Chi_Minh'
        });
      }
    }

    // Handle ISO 8601 or YYYY-MM-DD HH:mm:ss
    const date = new Date(dateStr);
    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Ho_Chi_Minh'
      });
    }

    console.warn('Invalid date format:', dateStr);
    return '-';
  } catch (e) {
    console.error('Date parsing error:', e.message, 'Input:', dateStr);
    return '-';
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0);
};

const formatNumber = (number) => {
  return new Intl.NumberFormat('vi-VN').format(number || 0);
};

const getStatusClass = (status) => statusMap[status]?.class || 'bg-gray-100 text-gray-800';
const getStatusText = (status) => statusMap[status]?.text || status || 'Không xác định';
const payoutStatusClass = (status) => {
  return {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800'
  }[status] || 'bg-gray-100 text-gray-800';
};
const payoutStatusText = (status) => {
  return {
    pending: 'Chờ xử lý',
    completed: 'Đã chuyển khoản',
    failed: 'Thất bại'
  }[status] || status || 'Không xác định';
};
const payoutStatusLabel = (status) => {
  return {
    pending: 'Chờ xử lý',
    completed: 'Đã chuyển khoản',
    failed: 'Thất bại'
  }[status] || status || 'Không xác định';
};
const refundStatusClass = (status) => refundStatusMap[status]?.class || 'bg-gray-100 text-gray-800';
const refundStatusText = (status) => refundStatusMap[status]?.text || status || 'Không xác định';
const getPaymentMethodText = (method) => paymentMethodMap[method] || method || 'Không xác định';
const getTrackingCode = (orderId) => {
  const order = orders.value.find(o => o.id === orderId);
  return order?.shipping?.tracking_code || 'Chưa có';
};

const showNotification = (message, success = true) => {
  if (notification.value.timeout) clearTimeout(notification.value.timeout);
  notification.value = { show: true, message, success };
  notification.value.timeout = setTimeout(() => {
    notification.value.show = false;
  }, 5000);
};

const closeNotification = () => {
  if (notification.value.timeout) clearTimeout(notification.value.timeout);
  notification.value.show = false;
};

const toggleDropdown = (orderId) => {
  activeDropdown.value = activeDropdown.value === orderId ? null : orderId;
};

const resetFilters = () => {
  filters.value = { status: '', payment_method: '', from_date: '', to_date: '', order_id: '', tracking_code: '' };
  fetchOrders();
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchOrders();
  }
};

const showOrderDetails = (order) => {
  selectedOrder.value = { ...order };
  activeDropdown.value = null;
};

const fetchOrders = async () => {
  try {
    loading.value = true;
    const params = {
      ...filters.value,
      page: currentPage.value,
      per_page: perPage.value
    };
    const url = `${apiBase}/orders?` + new URLSearchParams(params).toString();
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(url, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok) {
      orders.value = Array.isArray(data.data) ? data.data.map(order => ({
        ...order,
        id: order.id || null,
        created_at: order.created_at || null,
        transferred_at: order.transferred_at || null,
        payout_id: order.payout_id || null,
        payout_status: order.payout_status || null,
        payout_amount: order.payout_amount || null,
        final_price: Number(order.final_price) || 0,
        shipping: order.shipping || { tracking_code: null, shipping_fee: 0, status: null },
        payments: Array.isArray(order.payments) ? order.payments : [],
        user: order.user || { name: null, email: null },
        refund: order.refund || null
      })) : [];
      totalItems.value = data.meta?.total || 0;
      totalPages.value = data.meta?.last_page || 1;
    } else {
      throw new Error(data.message || `Lỗi ${response.status}: Không thể tải đơn hàng`);
    }
  } catch (error) {
    console.error('Error fetching orders:', error);
    showNotification(`Lỗi khi tải đơn hàng: ${error.message}`, false);
    orders.value = [];
  } finally {
    loading.value = false;
  }
};

const fetchPaymentMethods = async () => {
  paymentLoading.value = true;
  try {
    const response = await fetch(`${apiBase}/payment-methods`, {
      headers: { 'Accept': 'application/json' }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success) {
      paymentMethods.value = Array.isArray(data.data) ? data.data.filter(m => m.status === 'active') : [];
      if (!paymentMethods.value.length) {
        showNotification('Không tìm thấy phương thức thanh toán.', false);
      }
    } else {
      throw new Error(data.message || 'Lỗi khi tải phương thức thanh toán');
    }
  } catch (error) {
    console.error('Error fetching payment methods:', error.message);
    paymentMethods.value = [];
    showNotification(`Lỗi khi tải phương thức thanh toán: ${error.message}`, false);
  } finally {
    paymentLoading.value = false;
  }
};

const fetchPayoutData = async () => {
  payoutLoading.value = true;
  payoutError.value = '';
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const res = await fetch(`${apiBase}/payout/list-approved`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });

    const contentType = res.headers.get('Content-Type');
    let resData = {};
    if (contentType && contentType.includes('application/json')) {
      resData = await res.json();
    } else if (!res.ok) {
      const text = await res.text();
      throw new Error(`Phản hồi không phải JSON: ${text || 'Rỗng'}`);
    }

    if (!res.ok) throw new Error(resData.message || `Lỗi ${res.status}: Không lấy được dữ liệu payout`);

    payoutData.value = Array.isArray(resData.data) ? resData.data.map(item => ({
      ...item,
      created_at: item.created_at || null,
      transferred_at: item.transferred_at || null,
      amount: Number(item.amount) || 0
    })) : [];

    if (!payoutData.value.length) {
      payoutError.value = 'Không có payout đã duyệt nào.';
    }
  } catch (e) {
    console.error('Error fetching payout data:', e);
    payoutError.value = `Không thể tải dữ liệu payout: ${e.message}`;
    payoutData.value = [];
  } finally {
    payoutLoading.value = false;
  }
};

const fetchLogs = async () => {
    logLoading.value = true;
    logError.value = '';
    try {
        const token = localStorage.getItem('access_token');
        if (!token) {
            throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');
        }

        const response = await fetch(`${apiBase}/logs/ghn-sync`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });

        if (response.status === 401) {
            showNotification('Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.', false);
            window.location.href = '/login';
            return;
        }

        const contentType = response.headers.get('Content-Type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
        }

        const data = await response.json();
        if (response.ok && data.success) {
            logs.value = Array.isArray(data.data) ? data.data : [];
            if (!logs.value.length) {
                logError.value = 'Không có nhật ký đồng bộ nào.';
            }
        } else {
            throw new Error(data.message || 'Lỗi khi tải nhật ký đồng bộ');
        }
    } catch (error) {
        console.error('Error fetching logs:', error.message);
        logError.value = `Lỗi khi tải nhật ký đồng bộ: ${error.message}`;
        logs.value = [];
    } finally {
        logLoading.value = false;
    }
};

const fetchRefunds = async () => {
  refundLoading.value = true;
  refundError.value = '';
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/refunds`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    console.log('Refunds API Response:', response.status, await response.clone().text()); // Log phản hồi

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    console.log('Refunds API Data:', data); // Log dữ liệu JSON

    if (response.ok && data.success) {
      refunds.value = Array.isArray(data.data) ? data.data.map(refund => ({
        ...refund,
        amount: Number(refund.amount) * 1000 || 0,
        created_at: refund.created_at || null
      })) : [];
      if (!refunds.value.length) {
        refundError.value = 'Không có yêu cầu hoàn tiền nào.';
      }
    } else {
      throw new Error(data.message || 'Lỗi khi tải danh sách hoàn tiền');
    }
  } catch (error) {
    console.error('Error fetching refunds:', error.message);
    refundError.value = `Lỗi khi tải danh sách hoàn tiền: ${error.message}`;
    refunds.value = [];
  } finally {
    refundLoading.value = false;
  }
};

const editRefund = async (refund) => {
  refundToEdit.value = { ...refund, amount: Number(refund.amount) };
  showEditRefundModal.value = true;
};

const closeEditRefundModal = () => {
  showEditRefundModal.value = false;
  refundToEdit.value = null;
};

const confirmEditRefund = async () => {
  if (!refundToEdit.value || !refundToEdit.value.reason || !refundToEdit.value.amount) {
    showNotification('Vui lòng nhập đầy đủ thông tin!', false);
    return;
  }
  if (refundToEdit.value.amount > maxRefundAmount.value) {
    showNotification(`Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value)}!`, false);
    return;
  }

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/refunds/${refundToEdit.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        status: refundToEdit.value.status,
        reason: refundToEdit.value.reason,
        amount: Number(refundToEdit.value.amount)
      })
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success) {
      showNotification('Cập nhật yêu cầu hoàn tiền thành công', true);
      await Promise.all([fetchRefunds(), fetchOrders()]);
      if (selectedOrder.value?.id === refundToEdit.value.order_id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          status: data.data.status === 'approved' ? 'refunded' : selectedOrder.value.status,
          note: data.data.reason,
          refund: data.data
        };
      }
      closeEditRefundModal();
    } else {
      throw new Error(data.message || 'Lỗi khi cập nhật hoàn tiền');
    }
  } catch (error) {
    console.error('Error updating refund:', error.message);
    showNotification(`Lỗi khi cập nhật hoàn tiền: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

const deleteRefund = async (refundId) => {
  const result = await Swal.fire({
    title: 'Xác nhận xóa yêu cầu hoàn tiền',
    text: `Bạn có chắc chắn muốn xóa yêu cầu hoàn tiền #${refundId}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/refunds/${refundId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success) {
      showNotification('Xóa yêu cầu hoàn tiền thành công', true);
      await Promise.all([fetchRefunds(), fetchOrders()]);
      if (selectedOrder.value?.refund?.id === refundId) {
        selectedOrder.value = { ...selectedOrder.value, refund: null };
      }
    } else {
      throw new Error(data.message || 'Lỗi khi xóa yêu cầu hoàn tiền');
    }
  } catch (error) {
    console.error('Error deleting refund:', error.message);
    showNotification(`Lỗi khi xóa yêu cầu hoàn tiền: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

// Trong <script setup>

// Hàm duyệt yêu cầu hoàn tiền
const approveRefund = async (refund) => {
  const result = await Swal.fire({
    title: 'Xác nhận duyệt hoàn tiền',
    text: `Bạn có chắc chắn muốn duyệt yêu cầu hoàn tiền #${refund.id} cho đơn hàng ${refund.order_id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Duyệt',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#16a34a',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/refunds/${refund.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success) {
      showNotification('Duyệt yêu cầu hoàn tiền thành công', true);
      await Promise.all([fetchRefunds(), fetchOrders()]);
      if (selectedOrder.value?.id === refund.order_id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          status: 'refunded',
          note: refund.reason,
          refund: { ...refund, status: 'approved' }
        };
      }
    } else {
      throw new Error(data.message || 'Lỗi khi duyệt yêu cầu hoàn tiền');
    }
  } catch (error) {
    console.error('Error approving refund:', error.message);
    showNotification(`Lỗi khi duyệt yêu cầu hoàn tiền: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

// Hàm từ chối yêu cầu hoàn tiền
const rejectRefund = async (refund) => {
  const result = await Swal.fire({
    title: 'Xác nhận từ chối hoàn tiền',
    text: `Bạn có chắc chắn muốn từ chối yêu cầu hoàn tiền #${refund.id} cho đơn hàng ${refund.order_id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Từ chối',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/refunds/${refund.id}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success) {
      showNotification('Từ chối yêu cầu hoàn tiền thành công', true);
      await Promise.all([fetchRefunds(), fetchOrders()]);
      if (selectedOrder.value?.id === refund.order_id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          refund: { ...refund, status: 'rejected' }
        };
      }
    } else {
      throw new Error(data.message || 'Lỗi khi từ chối yêu cầu hoàn tiền');
    }
  } catch (error) {
    console.error('Error rejecting refund:', error.message);
    showNotification(`Lỗi khi từ chối yêu cầu hoàn tiền: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

// Status mapping
const statusMap = {
  pending: { text: 'Chờ xử lý', class: 'bg-yellow-100 text-yellow-800' },
  confirmed: { text: 'Đã xác nhận', class: 'bg-blue-100 text-blue-800' },
  processing: { text: 'Đang xử lý', class: 'bg-blue-100 text-blue-800' },
  shipping: { text: 'Đang giao', class: 'bg-purple-100 text-purple-800' },
  delivered: { text: 'Đã giao', class: 'bg-green-100 text-green-800' },
  failed: { text: 'Giao thất bại', class: 'bg-red-100 text-red-800' },
  cancelled: { text: 'Đã hủy', class: 'bg-red-100 text-red-800' },
  refunded: { text: 'Đã hoàn tiền', class: 'bg-orange-100 text-orange-800' },
  returned: { text: 'Đã trả hàng', class: 'bg-orange-100 text-orange-800' }
};

const ghnStatusMap = {
  ready_to_pick: { text: 'Sẵn sàng lấy hàng', class: 'bg-yellow-100 text-yellow-800' },
  picking: { text: 'Đang lấy hàng', class: 'bg-blue-100 text-blue-800' },
  shipping: { text: 'Đang giao hàng', class: 'bg-purple-100 text-purple-800' },
  delivered: { text: 'Đã giao', class: 'bg-green-100 text-green-800' },
  cancelled: { text: 'Đã hủy', class: 'bg-red-100 text-red-800' },
  returned: { text: 'Đã trả hàng', class: 'bg-orange-100 text-orange-800' },
  failed: { text: 'Giao thất bại', class: 'bg-red-100 text-red-800' }
};

const statusText = (status) => ghnStatusMap[status]?.text || status || 'Không xác định';

const paymentMethodMap = {
  cod: 'Thanh toán khi nhận hàng',
  banking: 'Chuyển khoản',
  momo: 'Ví MoMo'
};

const refundStatusMap = {
  pending: { text: 'Chờ xử lý', class: 'bg-yellow-100 text-yellow-800' },
  approved: { text: 'Đã duyệt', class: 'bg-green-100 text-green-800' },
  rejected: { text: 'Đã từ chối', class: 'bg-red-100 text-red-800' }
};

const processRefund = async (order) => {
  if (!refundReason.value) {
    showNotification('Vui lòng nhập lý do hoàn tiền!', false);
    return;
  }
  if (!refundAmount.value || refundAmount.value <= 0) {
    showNotification('Vui lòng nhập số tiền hoàn hợp lệ!', false);
    return;
  }
  if (refundAmount.value > maxRefundAmount.value) {
    showNotification(`Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value)}!`, false);
    return;
  }

  const result = await Swal.fire({
    title: 'Xác nhận hoàn tiền',
    text: `Bạn có chắc chắn muốn hoàn ${formatPrice(refundAmount.value)} cho đơn hàng ${order.shipping?.tracking_code || order.id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Hoàn tiền',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#f97316',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/orders/${order.id}/refund`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        reason: refundReason.value,
        amount: Number(refundAmount.value),
        status: 'pending'
      })
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      console.error('Non-JSON response:', text.slice(0, 200));
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success) {
      showNotification('Xử lý hoàn tiền thành công', true);
      await Promise.all([fetchOrders(), fetchRefunds()]);
      if (selectedOrder.value?.id === order.id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          status: data.data.status === 'approved' ? 'refunded' : selectedOrder.value.status,
          note: refundReason.value,
          refund: data.data
        };
      }
      refundAmount.value = 0;
      refundReason.value = '';
    } else {
      throw new Error(data.message || 'Lỗi khi xử lý hoàn tiền');
    }
  } catch (error) {
    console.error('Error processing refund:', error.message);
    let message = error.message;
    if (message.includes('Đơn hàng này đã có yêu cầu hoàn tiền')) {
      message = 'Đơn hàng này đã có yêu cầu hoàn tiền đang chờ xử lý!';
    } else if (message.includes('Số tiền hoàn không được vượt quá')) {
      message = `Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value)}!`;
    }
    showNotification(message, false);
  } finally {
    loading.value = false;
  }
};

const verifyGhnStatus = async (order) => {
  if (!order?.shipping?.tracking_code) {
    showNotification('Không có mã vận đơn để kiểm tra!', false);
    return;
  }
  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/orders/seller/${order.id}/sync-ghn`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ tracking_code: order.shipping.tracking_code })
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok && data.success !== false) {
      showNotification(
        `Trạng thái đơn hàng: ${statusMap[data.data?.status]?.text || data.data?.status || 'Không xác định'} | Trạng thái GHN: ${statusText(data.data?.shipping_status)}`,
        true
      );
      await fetchOrders();
    } else {
      throw new Error(data.message || 'Lỗi khi kiểm tra trạng thái GHN');
    }
  } catch (error) {
    console.error('Error verifying GHN status:', error);
    showNotification(`Lỗi khi kiểm tra trạng thái GHN: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

const createPayout = async (order) => {
  if (!order?.id || !order?.shop_id) {
    showNotification('Thông tin đơn hàng không hợp lệ!', false);
    return;
  }

  const result = await Swal.fire({
    title: 'Xác nhận tạo payout',
    text: `Bạn có chắc chắn muốn tạo payout thủ công cho đơn hàng ${order.shipping?.tracking_code || order.id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Tạo',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const payoutAmount = Math.max((Number(order.final_price || 0) - Number(order.shipping?.shipping_fee || 0)) * 0.95, 0);
    const response = await fetch(`${apiBase}/payouts`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        order_id: order.id,
        shop_id: order.shop_id,
        amount: payoutAmount,
        status: 'pending',
        note: `Payout thủ công cho đơn hàng ${order.shipping?.tracking_code || order.id}`
      })
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok) {
      showNotification('Tạo payout thành công', true);
      await Promise.all([fetchOrders(), fetchPayoutData()]);
      if (selectedOrder.value?.id === order.id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          payout_id: data.data.id,
          payout_status: data.data.status,
          payout_amount: data.data.amount,
          transferred_at: data.data.transferred_at || null
        };
      }
    } else {
      throw new Error(data.message || 'Lỗi khi tạo payout');
    }
  } catch (error) {
    console.error('Error creating payout:', error);
    showNotification(`Lỗi khi tạo payout: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

const approvePayout = async (order) => {
  const result = await Swal.fire({
    title: 'Xác nhận duyệt payout',
    text: `Bạn có chắc chắn muốn duyệt payout cho đơn hàng ${order.shipping?.tracking_code || order.id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Duyệt',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#16a34a',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/payouts/${order.payout_id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      console.error('Non-JSON response:', text.slice(0, 200));
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok) {
      showNotification('Duyệt payout thành công', true);
      await Promise.all([fetchOrders(), fetchPayoutData()]);
      if (selectedOrder.value?.id === order.id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          payout_status: 'completed',
          transferred_at: data.data.transferred_at || null
        };
      }
    } else {
      throw new Error(data.message || `Lỗi ${response.status}: Không thể duyệt payout`);
    }
  } catch (error) {
    console.error('Error approving payout:', error.message);
    showNotification(`Lỗi khi duyệt payout: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

const updateOrderStatus = (order) => {
  orderToUpdate.value = order;
  newPayoutStatus.value = order.payout_status || 'pending';
  showUpdateModal.value = true;
};

const closeUpdateModal = () => {
  showUpdateModal.value = false;
  orderToUpdate.value = null;
  newPayoutStatus.value = '';
};

const confirmUpdatePayoutStatus = async () => {
  if (!orderToUpdate.value || !newPayoutStatus.value) {
    showNotification('Thông tin không hợp lệ!', false);
    return;
  }

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/payouts/${orderToUpdate.value.payout_id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status: newPayoutStatus.value })
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok) {
      showNotification('Cập nhật trạng thái payout thành công', true);
      await Promise.all([fetchOrders(), fetchPayoutData()]);
      if (selectedOrder.value?.id === orderToUpdate.value.id) {
        selectedOrder.value = {
          ...selectedOrder.value,
          payout_status: newPayoutStatus.value,
          transferred_at: data.data.transferred_at || null
        };
      }
      closeUpdateModal();
    } else {
      throw new Error(data.message || 'Lỗi khi cập nhật trạng thái payout');
    }
  } catch (error) {
    console.error('Error updating payout status:', error);
    showNotification(`Lỗi khi cập nhật trạng thái payout: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

const deleteOrder = async (orderId) => {
  const result = await Swal.fire({
    title: 'Xác nhận xóa đơn hàng',
    text: `Bạn có chắc chắn muốn xóa đơn hàng ${orderId}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    const response = await fetch(`${apiBase}/orders/${orderId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Phản hồi không phải JSON: ${text.slice(0, 100)}...`);
    }

    const data = await response.json();
    if (response.ok) {
      showNotification('Xóa đơn hàng thành công', true);
      await fetchOrders();
      if (selectedOrder.value?.id === orderId) {
        selectedOrder.value = null;
      }
    } else {
      throw new Error(data.message || 'Lỗi khi xóa đơn hàng');
    }
  } catch (error) {
    console.error('Error deleting order:', error);
    showNotification(`Lỗi khi xóa đơn hàng: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchOrders();
  fetchPaymentMethods();
  fetchPayoutData();
  fetchLogs();
  fetchRefunds();
  const closeDropdown = (e) => {
    if (!e.target.closest('.relative')) {
      activeDropdown.value = null;
    }
  };
  document.addEventListener('click', closeDropdown);
  // Lưu tham chiếu để gỡ sự kiện
  onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    if (notification.value.timeout) clearTimeout(notification.value.timeout);
  });
});
</script>

<style scoped>
.object-cover {
  object-fit: cover;
}
</style>