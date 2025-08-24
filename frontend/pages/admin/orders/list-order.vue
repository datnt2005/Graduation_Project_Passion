<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đơn hàng</h1>
        <button @click="showPaymentNoteModal = true" 
          class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
          Lưu ý !
        </button>
      </div>

      <!-- Cảnh báo đơn hàng bất thường -->
      <div v-if="hasAbnormalOrders" class="bg-yellow-100 p-4 mb-4 mx-4 rounded text-yellow-700">
        Có {{ abnormalOrdersCount }} đơn hàng ở trạng thái bất thường (thất bại, hủy, trả hàng hoặc thiếu thông tin
        thanh toán). Vui lòng kiểm tra!
      </div>

      <!-- Nút chuyển đổi -->
      <div class="flex gap-2 mb-4 px-4 pt-4">
        <button @click="activeTab = 'orders'"
          :class="['px-4 py-2 rounded', activeTab === 'orders' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Đơn hàng
        </button>
        <button @click="activeTab = 'payouts'; fetchPayoutData()"
          :class="['px-4 py-2 rounded', activeTab === 'payouts' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Thanh toán đã cập nhật
        </button>
        <!-- <button @click="activeTab = 'logs'; fetchLogs()"
          :class="['px-4 py-2 rounded', activeTab === 'logs' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Nhật ký đồng bộ
        </button> -->
        <button @click="activeTab = 'refunds'; fetchRefunds()"
          :class="['px-4 py-2 rounded', activeTab === 'refunds' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Yêu cầu hoàn tiền
        </button>
        <div class="relative inline-block">
        <button @click="activeTab = 'withdraw'; fetchWithdrawList()"
          :class="['px-4 py-2 rounded', activeTab === 'withdraw' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Yêu cầu rút tiền
        </button>
          <span v-if="withdrawPendingCount > 0"
            class="absolute -right-2 -top-2 inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 rounded-full shadow">
            +{{ withdrawPendingCount }}
          </span>
        </div>
      </div>

      <!-- Tab Đơn hàng -->
      <div v-if="activeTab === 'orders'">
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
              <option value="refunded">Đã hoàn trả</option>
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

        <!-- Action Bar -->
        <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
          <select
            v-model="selectedAction"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Hành động hàng loạt</option>
            <option value="update_payout_status">Cập nhật trạng thái thanh toán</option>
            <option value="create_payout">Tạo thanh toán</option>
            <option value="delete">Xóa</option>
          </select>
          <button
            @click="applyBulkAction"
            :disabled="!selectedAction || selectedOrders.length === 0 || loading"
            :class="[
              'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
              (!selectedAction || selectedOrders.length === 0 || loading) 
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-blue-600 text-white hover:bg-blue-700'
            ]"
          >
            {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
          </button>
          <div class="ml-auto text-sm text-gray-600">
            {{ selectedOrders.length }} đơn hàng được chọn / {{ orders.length }} đơn hàng
          </div>
        </div>

        <!-- Table -->
        <table class="min-w-full border-collapse border border-gray-300 text-sm">
          <thead class="bg-white border-b border-gray-300">
            <tr>
              <th class="border border-gray-300 px-3 py-2 text-left w-10">
                <input 
                  type="checkbox" 
                  v-model="selectAll"
                  @change="toggleSelectAll"
                />
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Mã vận đơn</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Khách hàng</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tổng tiền</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phương thức thanh toán
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái đơn hàng
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái thanh toán</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders" :key="order.id" :class="{ 'bg-gray-50': order.id % 2 === 0 }"
              class="border-b border-gray-300">
              <td class="border border-gray-300 px-3 py-2 text-left w-10">
                <input 
                  type="checkbox" 
                  v-model="selectedOrders" 
                  :value="order.id"
                />
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">{{
                order.shipping?.tracking_code || 'Chưa có' }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                {{ order.user?.name || 'N/A' }}<br>
                <span class="text-xs">{{ order.user?.email || 'N/A' }}</span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ formatPrice(order.final_price) }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ getPaymentMethodText(order.payment_method)
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
                        thanh toán</button>
                      <button v-if="order.status === 'delivered' && !order.payout_id"
                        @click="createPayout(order); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">Tạo thanh toán</button>
                      <!-- <button v-if="order.shipping?.tracking_code"
                        @click="verifyGhnStatus(order); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-purple-50">Kiểm tra
                        GHN</button> -->
                      <button @click="deleteOrder(order.id); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Xóa</button>
                        <button @click.prevent="openInvoicePrinter(order)"
                          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" title="In hóa đơn">
                            In hóa đơn
                        </button>
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
      <div v-else-if="activeTab === 'logs'" class="bg-white p-6 rounded shadow w-full">
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
                <!-- <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Trạng thái GHN</th> -->
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
      <div v-else-if="activeTab === 'payouts'" class="bg-white p-6 rounded shadow w-full">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
          <span>💸</span> Danh sách thanh toán đã cập nhật
        </h2>
        <div class="flex flex-wrap gap-3 mb-4">
          <input v-model="payoutTrackingKeyword" type="text" placeholder="Tìm theo mã vận đơn"
            class="border p-2 rounded flex-1 min-w-[180px] placeholder-gray-400">
          <select v-model="payoutSortOption" class="border p-2 rounded min-w-[160px]">
            <option value="transferred_desc">Mới nhất (ngày chuyển khoản)</option>
            <option value="created_desc">Gần đây nhất (ngày tạo)</option>
            <option value="created_asc">Cũ nhất</option>
          </select>
        </div>
        <div v-if="payoutLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
        <div v-else-if="payoutError" class="text-center text-red-500 py-10">{{ payoutError }}</div>
        <div v-else-if="!payoutTrackingFilteredData.length" class="text-center text-gray-400 py-10">Không có thanh toán
          nào</div>
        <div v-else class="mt-4">
          <table class="w-full table-auto divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Mã thanh toán</th>
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
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">
                  {{ item.order?.shipping?.tracking_code || '-' }}
                </td>
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

      <!-- Tab Yêu cầu hoàn tiền -->
      <div v-else-if="activeTab === 'refunds'" class="bg-white p-6 rounded shadow w-full">
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
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Ngân hàng</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">Số tài khoản</th>
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
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ refund.bank_name || 'Chưa có' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ refund.bank_account_number || 'Chưa có' }}</td>
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
                  </div>
                  <div v-else class="flex gap-2">
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

      <!-- Tab Yêu cầu rút tiền -->
      <div v-else-if="activeTab === 'withdraw'" class="bg-white p-6 rounded shadow w-full">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
          <span>🏦</span> Danh sách yêu cầu rút tiền
        </h2>
        <!-- Thanh filter đặt ở trên -->
        <div class="bg-gray-100 p-4 rounded-lg mb-4">
          <h3 class="text-lg font-semibold mb-3 text-gray-800">Bộ lọc tìm kiếm</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            <!-- Tìm kiếm theo số tiền -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Số tiền</label>
              <input v-model="withdrawSearch" placeholder="Tìm kiếm theo số tiền" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            </div>
            
            <!-- Lọc theo ngân hàng -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ngân hàng</label>
              <select v-model="withdrawFilters.bank_name" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả ngân hàng</option>
                <option v-for="bank in uniqueBanks" :key="bank" :value="bank">{{ bank }}</option>
              </select>
            </div>
            
            <!-- Lọc theo tên cửa hàng -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tên cửa hàng</label>
              <select v-model="withdrawFilters.shop_name" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả cửa hàng</option>
                <option v-for="shop in uniqueShops" :key="shop" :value="shop">{{ shop }}</option>
              </select>
            </div>
            
            <!-- Lọc theo trạng thái -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
              <select v-model="withdrawFilters.status" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xử lý</option>
                <option value="approved">Đã duyệt</option>
                <option value="rejected">Đã từ chối</option>
              </select>
            </div>
            
            <!-- Lọc từ ngày -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
              <input type="date" v-model="withdrawFilters.from_date" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            </div>
            
            <!-- Lọc đến ngày -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
              <input type="date" v-model="withdrawFilters.to_date" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            </div>
          </div>
          
          <!-- Thanh sắp xếp -->
          <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-200">
            <select v-model="withdrawSortDate" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="desc">Mới nhất</option>
              <option value="asc">Cũ nhất</option>
            </select>
            <select v-model="withdrawSortAmount" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="desc">Giá cao → thấp</option>
              <option value="asc">Giá thấp → cao</option>
            </select>
            <button @click="resetWithdrawFilters" 
              class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors text-sm">
              Đặt lại
            </button>
            <button @click="applyWithdrawFilters" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
              Áp dụng
            </button>
          </div>
        </div>
        <div v-if="withdrawLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
        <div v-else-if="withdrawError" class="text-center text-red-500 py-10">{{ withdrawError }}</div>
        <div v-else-if="!withdrawList.length" class="text-center text-gray-400 py-10">Không có yêu cầu rút tiền nào</div>
        <div v-else class="mt-4">
          <table class="w-full table-auto divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Số tiền</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Tên cửa hàng</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Ngân hàng</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Số tài khoản</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Tên chủ tài khoản</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Trạng thái</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Ngày gửi</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Ngày duyệt</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Ghi chú</th>
                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase whitespace-normal break-words">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in withdrawListFiltered" :key="item.id" class="hover:bg-blue-50 transition">
                <td class="px-4 py-3 text-sm text-gray-900 break-words whitespace-normal">{{ formatPrice(item.amount) }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal font-medium text-blue-600">{{ item.seller?.shop_name || 'N/A' }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">{{ item.bank_name }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">{{ item.bank_account }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">{{ item.bank_account_name }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">
                  <span :class="withdrawStatusClass(item.status)">{{ withdrawStatusLabel(item.status) }}</span>
                </td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">{{ formatDate(item.created_at) }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">{{ item.approved_at ? formatDate(item.approved_at) : '-' }}</td>
                <td class="px-4 py-3 text-sm break-words whitespace-normal">{{ item.note || '-' }}</td>
                <td class="px-4 py-3 text-sm relative break-words whitespace-normal">
                  <button @click="toggleWithdrawDropdown(item.id)" class="p-2 rounded hover:bg-gray-100 focus:outline-none">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                  </button>
                  <div v-if="activeWithdrawDropdown === item.id" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50" style="overflow-y: visible; max-height: none;">
                    <div class="py-1">
                      <button v-if="item.status === 'pending'" @click="approveWithdraw(item); closeWithdrawDropdown()" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50">Duyệt rút tiền</button>
                      <button @click="openWithdrawDetail(item)" class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Xem chi tiết</button>
                      <button v-if="item.status === 'pending'" @click="openRejectWithdraw(item)" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Từ chối</button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="withdrawTotalPages > 1" class="flex justify-center mt-4">
          <button @click="withdrawPage--" :disabled="withdrawPage === 1"
            class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">
            <
          </button>
          <button v-for="p in withdrawTotalPages" :key="p" @click="withdrawPage = p"
            :class="['px-3 py-1 mx-1 rounded border', withdrawPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{
            p }}</button>
          <button @click="withdrawPage++" :disabled="withdrawPage === withdrawTotalPages"
            class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">></button>
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
                <!-- <p><b>Trạng thái GHN:</b> {{ selectedOrder.shipping?.status ? statusText(selectedOrder.shipping.status) : 'Chờ GHN lấy hàng' }}</p> -->
                <p><b>Ngày tạo:</b> {{ formatDate(selectedOrder.created_at) }}</p>
                <!-- <p v-if="selectedOrder.shipping?.tracking_code" class="mt-2">
                  <button @click="verifyGhnStatus(selectedOrder)"
                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Kiểm tra trạng thái
                    GHN</button>
                </p> -->
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
                <p><b>Phương thức thanh toán:</b> {{ getPaymentMethodText(selectedOrder.payment_method) }}</p>
              </div>
            </div>
            <!-- Thông tin thanh toán cho shop -->
            <div class="border border-gray-200 rounded-lg mt-4">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán cho shop</div>
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
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tạo thanh toán thủ công</button>
                </p>
                <p v-if="selectedOrder?.status === 'delivered' && selectedOrder.payout_status === 'pending' && selectedOrder.payout_id"
                  class="mt-2">
                  <button @click="approvePayout(selectedOrder)"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Duyệt thanh toán</button>
                </p>
                <p class="text-xs text-gray-500 mt-2">
                  Lưu ý: Số tiền nhận được là 95% tổng giá trị đơn hàng (bao gồm phí vận chuyển, đã trừ chiết khấu 5% cho admin và giảm giá nếu có).
                  <span v-if="selectedOrder.payments?.[0]?.method === 'COD'" class="text-red-500 font-semibold">
                    Đơn hàng COD có thể bị trừ thêm phí thu hộ, phí chuyển khoản của đơn vị vận chuyển. Số tiền thực nhận sẽ được đối soát theo thực tế.
                  </span>
                  <span v-else-if="selectedOrder.payments?.[0]?.method === 'VNPAY' || selectedOrder.payments?.[0]?.method === 'MOMO'" class="text-green-600 font-semibold">
                    Đơn hàng thanh toán online (VNPAY/MOMO) shop sẽ nhận đúng số tiền như hệ thống ước tính.
                  </span>
                  Nếu có điều chỉnh khác, admin sẽ ghi chú riêng.
                </p>
              </div>
            </div>
            <!-- Xử lý hoàn tiền -->
            <div v-if="['failed', 'cancelled', 'refunded', 'returned'].includes(selectedOrder?.status)"
              class="border border-gray-200 rounded-lg">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Xử lý hoàn tiền</div>
              <div class="px-4 py-3 text-sm text-gray-700">
                <p><b>Lý do hiện tại:</b> {{ selectedOrder?.note || 'Chưa có ghi chú' }}</p>
                <!-- Hiển thị thông tin hoàn tiền nếu có -->
                <div v-if="selectedOrder?.refund" class="mt-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                  <p class="font-semibold text-gray-800 mb-3">Thông tin hoàn tiền:</p>
                  <p><b>Mã hoàn tiền:</b> {{ selectedOrder.refund.id }}</p>
                  <p><b>Số tiền hoàn:</b> {{ formatPrice(selectedOrder.refund.amount) }}</p>
                  <p><b>Trạng thái:</b> <span :class="refundStatusClass(selectedOrder.refund.status)">{{
                    refundStatusText(selectedOrder.refund.status) }}</span></p>
                  <p><b>Lý do:</b> {{ selectedOrder.refund.reason || 'Không có lý do' }}</p>
                  <p><b>Thời gian tạo:</b> {{ formatDate(selectedOrder.refund.created_at) }}</p>
                </div>
                <!-- Thông báo khi không có yêu cầu hoàn tiền -->
                <div v-else class="mt-4 bg-yellow-50 p-4 rounded-md border border-yellow-200 text-yellow-700">
                  <p><b>Thông báo:</b> Không có yêu cầu hoàn tiền nào cho đơn hàng này.</p>
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
          <h2 class="text-lg font-semibold mb-4">Cập nhật trạng thái thanh toán</h2>
          <div class="mb-4">
            <div><b>Đơn hàng - Mã vận đơn:</b> {{ orderToUpdate?.shipping?.tracking_code || 'Chưa có' }}</div>
            <div><b>Số tiền thanh toán:</b> {{ formatPrice(orderToUpdate?.payout_amount || orderToUpdate?.amount) }}</div>
            <div><b>Trạng thái hiện tại:</b> <span class="font-semibold">{{
              payoutStatusText(orderToUpdate?.payout_status) }}</span></div>
          </div>
          <div class="mb-4">
            <label class="block mb-1">Chọn trạng thái thanh toán mới:</label>
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

      <!-- Teleport/modal xem chi tiết: -->
      <Teleport to="body">
        <div v-if="showWithdrawDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
            <button @click="closeWithdrawDetail" class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg">✕</button>
            <h2 class="text-xl font-bold mb-4 text-gray-800">Chi tiết yêu cầu rút tiền</h2>
            <div v-if="withdrawDetailItem">
              <p><b>Số tiền:</b> {{ formatPrice(withdrawDetailItem.amount) }}</p>
              <p><b>Ngân hàng:</b> {{ withdrawDetailItem.bank_name }}</p>
              <p><b>Số tài khoản:</b> {{ withdrawDetailItem.bank_account }}</p>
              <p><b>Tên chủ tài khoản:</b> {{ withdrawDetailItem.bank_account_name }}</p>
              <p><b>Trạng thái:</b> <span :class="withdrawStatusClass(withdrawDetailItem.status)">{{ withdrawStatusLabel(withdrawDetailItem.status) }}</span></p>
              <p><b>Ngày gửi:</b> {{ formatDate(withdrawDetailItem.created_at) }}</p>
              <p><b>Ngày duyệt:</b> {{ withdrawDetailItem.approved_at ? formatDate(withdrawDetailItem.approved_at) : '-' }}</p>
              <p><b>Ghi chú:</b> {{ withdrawDetailItem.note || '-' }}</p>
              <div v-if="withdrawDetailItem.seller">
                <hr class="my-3" />
                <h3 class="font-semibold mb-2">Thông tin cửa hàng</h3>
                <p v-if="withdrawDetailItem.seller.shop_name"><b>Tên shop:</b> {{ withdrawDetailItem.seller.shop_name }}</p>
                <p v-if="withdrawDetailItem.seller.name"><b>Tên tài khoản:</b> {{ withdrawDetailItem.seller.name }}</p>
                <p v-if="withdrawDetailItem.seller.email"><b>Email:</b> {{ withdrawDetailItem.seller.email }}</p>
                <p v-if="withdrawDetailItem.seller.phone"><b>Số điện thoại:</b> {{ withdrawDetailItem.seller.phone }}</p>
              </div>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Teleport/modal từ chối: -->
      <Teleport to="body">
        <div v-if="showRejectWithdrawModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
            <button @click="closeRejectWithdraw" class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg">✕</button>
            <h2 class="text-xl font-bold mb-4 text-gray-800">Từ chối yêu cầu rút tiền</h2>
            <div v-if="rejectWithdrawItem">
              <p><b>Số tiền:</b> {{ formatPrice(rejectWithdrawItem.amount) }}</p>
              <p><b>Ngân hàng:</b> {{ rejectWithdrawItem.bank_name }}</p>
              <p><b>Số tài khoản:</b> {{ rejectWithdrawItem.bank_account }}</p>
              <p><b>Tên chủ tài khoản:</b> {{ rejectWithdrawItem.bank_account_name }}</p>
            </div>
            <div class="mb-4 mt-4">
              <label class="block mb-1 font-medium">Lý do từ chối</label>
              <textarea v-model="rejectWithdrawReason" class="w-full border rounded px-3 py-2" rows="2" placeholder="Nhập lý do từ chối"></textarea>
            </div>
            <div class="flex gap-2 justify-end">
              <button type="button" @click="closeRejectWithdraw" class="px-4 py-2 bg-gray-200 rounded">Huỷ</button>
              <button type="button" @click="submitRejectWithdraw" :disabled="rejectWithdrawLoading" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Xác nhận từ chối</button>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Modal Lưu ý thanh toán -->
      <Teleport to="body">
        <div v-if="showPaymentNoteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto payment-note-modal">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-yellow-500 to-orange-500 text-white p-6 rounded-t-2xl">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                  <h2 class="text-2xl font-bold">Lưu ý quan trọng khi duyệt thanh toán</h2>
                </div>
                <button @click="showPaymentNoteModal = false" class="text-white hover:text-gray-200 transition-colors">
                  <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
              <!-- Cảnh báo chung -->
              <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg warning-box">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                  <h3 class="text-lg font-semibold text-red-700">CẢNH BÁO QUAN TRỌNG</h3>
                </div>
                <p class="text-red-700 font-medium">
                  Vui lòng đọc kỹ và thực hiện đầy đủ các bước dưới đây trước khi duyệt thanh toán cho seller. 
                  Việc thanh toán sai có thể gây thiệt hại nghiêm trọng cho hệ thống!
                </p>
              </div>

              <!-- Các bước cần thực hiện -->
              <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-800 border-b border-gray-200 pb-2">
                  📋 Các bước bắt buộc cần kiểm tra:
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Bước 1 -->
                  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold step-number">1</div>
                      <h4 class="text-lg font-semibold text-blue-800">Kiểm tra trạng thái đơn hàng</h4>
                    </div>
                    <ul class="text-sm text-blue-700 space-y-1 ml-10">
                      <li>✓ Đơn hàng phải ở trạng thái "Đã giao" (delivered)</li>
                      <li>✓ Khách hàng đã nhận hàng và xác nhận</li>
                      <li>✓ Không có khiếu nại hoặc trả hàng</li>
                      <li>✓ Thời gian giao hàng hợp lý (3-7 ngày)</li>
                    </ul>
                  </div>

                  <!-- Bước 2 -->
                  <div class="bg-green-50 border border-green-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold step-number">2</div>
                      <h4 class="text-lg font-semibold text-green-800">Xác minh thông tin seller</h4>
                    </div>
                    <ul class="text-sm text-green-700 space-y-1 ml-10">
                      <li>✓ Thông tin ngân hàng chính xác</li>
                      <li>✓ Số tài khoản và tên chủ tài khoản khớp</li>
                      <li>✓ Seller đã được xác thực và hoạt động</li>
                      <li>✓ Không có lịch sử vi phạm</li>
                    </ul>
                  </div>

                  <!-- Bước 3 -->
                  <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-yellow-500 text-white rounded-full flex items-center justify-center font-bold step-number">3</div>
                      <h4 class="text-lg font-semibold text-yellow-800">Kiểm tra số tiền thanh toán</h4>
                    </div>
                    <ul class="text-sm text-yellow-700 space-y-1 ml-10">
                      <li>✓ Số tiền khớp với giá trị đơn hàng</li>
                      <li>✓ Đã trừ phí vận chuyển và hoa hồng</li>
                      <li>✓ Không có khoản khấu trừ bất thường</li>
                      <li>✓ Tính toán lại để đảm bảo chính xác</li>
                    </ul>
                  </div>

                  <!-- Bước 4 -->
                  <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold step-number">4</div>
                      <h4 class="text-lg font-semibold text-purple-800">Xác nhận thời gian</h4>
                    </div>
                    <ul class="text-sm text-purple-700 space-y-1 ml-10">
                      <li>✓ Đã đủ thời gian chờ (tối thiểu 7 ngày)</li>
                      <li>✓ Không có yêu cầu hoàn tiền từ khách</li>
                      <li>✓ Không có tranh chấp đang xử lý</li>
                      <li>✓ Đơn hàng đã ổn định</li>
                    </ul>
                  </div>
                </div>

                <!-- Lưu ý đặc biệt -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                  <div class="flex items-center gap-2 mb-3">
                    <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h4 class="text-lg font-semibold text-orange-800">Lưu ý đặc biệt</h4>
                  </div>
                  <ul class="text-sm text-orange-700 space-y-2">
                    <li>• <strong>KHÔNG BAO GIỜ</strong> thanh toán cho đơn hàng chưa giao thành công</li>
                    <li>• <strong>KIỂM TRA KỸ</strong> thông tin ngân hàng trước khi chuyển tiền</li>
                    <li>• <strong>GHI CHÉP</strong> lại mọi giao dịch thanh toán</li>
                    <li>• <strong>BÁO CÁO NGAY</strong> nếu phát hiện bất thường</li>
                    <li>• <strong>XÁC NHẬN</strong> với seller trước khi thực hiện thanh toán</li>
                  </ul>
                </div>

                <!-- Quy trình thanh toán -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                  <h4 class="text-lg font-semibold text-gray-800 mb-3">🔄 Quy trình thanh toán chuẩn:</h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="bg-white p-3 rounded border">
                      <div class="font-semibold text-blue-600 mb-1">Bước 1: Chuẩn bị</div>
                      <div class="text-gray-600">• Kiểm tra danh sách đơn hàng đủ điều kiện</div>
                      <div class="text-gray-600">• Tính toán tổng số tiền cần thanh toán</div>
                      <div class="text-gray-600">• Chuẩn bị thông tin ngân hàng</div>
                    </div>
                    <div class="bg-white p-3 rounded border">
                      <div class="font-semibold text-green-600 mb-1">Bước 2: Xác nhận</div>
                      <div class="text-gray-600">• Gọi điện xác nhận với seller</div>
                      <div class="text-gray-600">• Kiểm tra lại thông tin tài khoản</div>
                      <div class="text-gray-600">• Thông báo số tiền sẽ chuyển</div>
                    </div>
                    <div class="bg-white p-3 rounded border">
                      <div class="font-semibold text-purple-600 mb-1">Bước 3: Thực hiện</div>
                      <div class="text-gray-600">• Chuyển tiền qua ngân hàng</div>
                      <div class="text-gray-600">• Lưu lại biên lai chuyển tiền</div>
                      <div class="text-gray-600">• Cập nhật trạng thái trong hệ thống</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="bg-gray-100 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="hasReadPaymentNote" id="readNote" class="w-4 h-4">
                    <label for="readNote" class="text-sm font-medium text-gray-700">
                      Tôi đã đọc và hiểu rõ các lưu ý trên
                    </label>
                  </div>
                  <button @click="showPaymentNoteModal = false" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Đã hiểu
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Modal Thông báo cập nhật trạng thái payout -->
      <Teleport to="body">
        <div v-if="showPayoutStatusNoteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto payment-note-modal">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-6 rounded-t-2xl">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                  <h2 class="text-2xl font-bold">Lưu ý khi cập nhật trạng thái thanh toán</h2>
                </div>
                <button @click="showPayoutStatusNoteModal = false" class="text-white hover:text-gray-200 transition-colors">
                  <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
              <!-- Cảnh báo chung -->
              <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                  <h3 class="text-lg font-semibold text-blue-700">THÔNG BÁO QUAN TRỌNG</h3>
                </div>
                <p class="text-blue-700 font-medium">
                  Vui lòng đọc kỹ các lưu ý dưới đây trước khi cập nhật trạng thái thanh toán. 
                  Việc cập nhật sai trạng thái có thể ảnh hưởng đến quy trình thanh toán!
                </p>
              </div>

              <!-- Các lưu ý cần thực hiện -->
              <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-800 border-b border-gray-200 pb-2">
                  📋 Các lưu ý khi cập nhật trạng thái thanh toán:
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Lưu ý 1 -->
                  <div class="bg-green-50 border border-green-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold step-number">1</div>
                      <h4 class="text-lg font-semibold text-green-800">Trạng thái "Đã chuyển khoản"</h4>
                    </div>
                    <ul class="text-sm text-green-700 space-y-1 ml-10">
                      <li>✓ Chỉ cập nhật khi đã thực sự chuyển tiền</li>
                      <li>✓ Có biên lai chuyển tiền xác nhận</li>
                      <li>✓ Seller đã xác nhận nhận được tiền</li>
                      <li>✓ Không thể hoàn tác sau khi cập nhật</li>
                    </ul>
                  </div>

                  <!-- Lưu ý 2 -->
                  <div class="bg-red-50 border border-red-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center font-bold step-number">2</div>
                      <h4 class="text-lg font-semibold text-red-800">Trạng thái "Thất bại"</h4>
                    </div>
                    <ul class="text-sm text-red-700 space-y-1 ml-10">
                      <li>✓ Chỉ cập nhật khi chuyển tiền thất bại</li>
                      <li>✓ Có lý do cụ thể cho việc thất bại</li>
                      <li>✓ Cần liên hệ lại với seller</li>
                      <li>✓ Có thể thử lại sau khi khắc phục</li>
                    </ul>
                  </div>

                  <!-- Lưu ý 3 -->
                  <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-yellow-500 text-white rounded-full flex items-center justify-center font-bold step-number">3</div>
                      <h4 class="text-lg font-semibold text-yellow-800">Trạng thái "Chờ xử lý"</h4>
                    </div>
                    <ul class="text-sm text-yellow-700 space-y-1 ml-10">
                      <li>✓ Trạng thái mặc định khi tạo thanh toán</li>
                      <li>✓ Có thể cập nhật thành trạng thái khác</li>
                      <li>✓ Chưa thực hiện chuyển tiền</li>
                      <li>✓ Cần theo dõi để xử lý tiếp</li>
                    </ul>
                  </div>

                  <!-- Lưu ý 4 -->
                  <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 step-card">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold step-number">4</div>
                      <h4 class="text-lg font-semibold text-purple-800">Quy trình cập nhật</h4>
                    </div>
                    <ul class="text-sm text-purple-700 space-y-1 ml-10">
                      <li>✓ Kiểm tra kỹ trước khi cập nhật</li>
                      <li>✓ Ghi chép lại mọi thay đổi</li>
                      <li>✓ Thông báo cho seller khi cần</li>
                      <li>✓ Theo dõi lịch sử thay đổi</li>
                    </ul>
                  </div>
                </div>

                <!-- Lưu ý đặc biệt -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                  <div class="flex items-center gap-2 mb-3">
                    <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h4 class="text-lg font-semibold text-orange-800">Lưu ý đặc biệt</h4>
                  </div>
                  <ul class="text-sm text-orange-700 space-y-2">
                    <li>• <strong>KHÔNG BAO GIỜ</strong> cập nhật "Đã chuyển khoản" khi chưa thực sự chuyển tiền</li>
                    <li>• <strong>KIỂM TRA KỸ</strong> thông tin trước khi cập nhật</li>
                    <li>• <strong>GHI CHÉP</strong> lại mọi thay đổi trạng thái</li>
                    <li>• <strong>BÁO CÁO NGAY</strong> nếu phát hiện bất thường</li>
                    <li>• <strong>XÁC NHẬN</strong> với seller khi cần thiết</li>
                  </ul>
                </div>
              </div>

              <!-- Footer -->
              <div class="bg-gray-100 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="hasReadPayoutStatusNote" id="readPayoutStatusNote" class="w-4 h-4">
                    <label for="readPayoutStatusNote" class="text-sm font-medium text-gray-700">
                      Tôi đã đọc và hiểu rõ các lưu ý trên
                    </label>
                  </div>
                  <div class="flex gap-2">
                    <button @click="showPayoutStatusNoteModal = false" 
                      class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                      Hủy
                    </button>
                    <button @click="confirmPayoutStatusUpdate" 
                      :disabled="!hasReadPayoutStatusNote"
                      :class="[
                        'px-6 py-2 rounded-lg transition-colors font-medium',
                        hasReadPayoutStatusNote 
                          ? 'bg-blue-600 text-white hover:bg-blue-700' 
                          : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                      ]">
                      Tiếp tục
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </div>
    <Teleport to="body">
      <InvoicePrinter v-if="showInvoiceModal" :order-id="orderForInvoice.id" @close="showInvoiceModal = false" />
    </Teleport>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '~/stores/auth';
import { useRuntimeConfig } from '#app';
import Swal from 'sweetalert2';
import InvoicePrinter from '@/components/shared/InvoicePrinter.vue'; 

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
const activeTab = ref('orders');
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
const showWithdrawTab = ref(false);
const withdrawLoading = ref(false);
const withdrawError = ref('');
const withdrawList = ref([]);
const activeWithdrawDropdown = ref(null);
const showWithdrawDetailModal = ref(false);
const withdrawDetailItem = ref(null);
const showRejectWithdrawModal = ref(false);
const rejectWithdrawItem = ref(null);
const rejectWithdrawReason = ref('');
const rejectWithdrawLoading = ref(false);
// Thêm biến filter cho danh sách yêu cầu rút tiền
const withdrawSearch = ref('');
const withdrawSortDate = ref('desc'); // 'desc' = mới nhất, 'asc' = cũ nhất
const withdrawSortAmount = ref('desc'); // 'desc' = cao->thấp, 'asc' = thấp->cao

// Các bộ lọc mới cho withdraw
const withdrawFilters = ref({
  bank_name: '', // Lọc theo ngân hàng
  shop_name: '', // Lọc theo tên cửa hàng
  status: '', // Lọc theo trạng thái
  from_date: '', // Lọc từ ngày
  to_date: '' // Lọc đến ngày
});

// Modal lưu ý thanh toán
const showPaymentNoteModal = ref(false);
const hasReadPaymentNote = ref(false);

// Checkbox và hành động hàng loạt
const selectedOrders = ref([]);
const selectAll = ref(false);
const selectedAction = ref('');

// Biến cho modal thông báo cập nhật payout status
const showPayoutStatusNoteModal = ref(false);
const hasReadPayoutStatusNote = ref(false);
const pendingPayoutStatusUpdate = ref(null);

const router = useRouter();
const authStore = useAuthStore();

// Computed properties cho withdraw filters
const uniqueBanks = computed(() => {
  const banks = withdrawList.value
    .map(item => item.bank_name)
    .filter((bank, index, arr) => bank && arr.indexOf(bank) === index)
    .sort();
  return banks;
});

const uniqueShops = computed(() => {
  const shops = withdrawList.value
    .map(item => item.seller?.shop_name)
    .filter((shop, index, arr) => shop && arr.indexOf(shop) === index)
    .sort();
  return shops;
});

// Số lượng yêu cầu rút tiền đang chờ → hiển thị badge trên nút
const withdrawPendingCount = computed(() => {
  return withdrawList.value.filter(item => item.status === 'pending').length;
});

const withdrawListFiltered = computed(() => {
  let arr = [...withdrawList.value];
  
  // Lọc theo số tiền
  if (withdrawSearch.value) {
    const kw = withdrawSearch.value.replace(/\D/g, '');
    arr = arr.filter(item => String(item.amount).includes(kw));
  }
  
  // Lọc theo ngân hàng
  if (withdrawFilters.value.bank_name) {
    arr = arr.filter(item => item.bank_name === withdrawFilters.value.bank_name);
  }
  
  // Lọc theo tên cửa hàng
  if (withdrawFilters.value.shop_name) {
    arr = arr.filter(item => item.seller?.shop_name === withdrawFilters.value.shop_name);
  }
  
  // Lọc theo trạng thái
  if (withdrawFilters.value.status) {
    arr = arr.filter(item => item.status === withdrawFilters.value.status);
  }
  
  // Lọc theo ngày từ
  if (withdrawFilters.value.from_date) {
    const fromDate = new Date(withdrawFilters.value.from_date);
    arr = arr.filter(item => new Date(item.created_at) >= fromDate);
  }
  
  // Lọc theo ngày đến
  if (withdrawFilters.value.to_date) {
    const toDate = new Date(withdrawFilters.value.to_date);
    toDate.setHours(23, 59, 59, 999); // Đặt thời gian cuối ngày
    arr = arr.filter(item => new Date(item.created_at) <= toDate);
  }
  
  // Sắp xếp theo ngày
  arr = arr.sort((a, b) => {
    const da = new Date(a.created_at), db = new Date(b.created_at);
    return withdrawSortDate.value === 'desc' ? db - da : da - db;
  });
  
  // Sắp xếp theo số tiền
  arr = arr.sort((a, b) => {
    return withdrawSortAmount.value === 'desc' ? b.amount - a.amount : a.amount - b.amount;
  });
  
  return arr;
});

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

// Watch cho checkbox
watch(selectedOrders, (newSelected) => {
  if (newSelected.length === 0) {
    selectAll.value = false;
  } else if (newSelected.length === orders.value.length) {
    selectAll.value = true;
  } else {
    selectAll.value = false;
  }
}, { deep: true });

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
  if (!status) return 'Chờ xử lý';
  return {
    pending: 'Chờ xử lý',
    completed: 'Đã chuyển khoản',
    failed: 'Thất bại'
  }[status] || status;
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

// Checkbox và hành động hàng loạt
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedOrders.value = orders.value.map(order => order.id);
  } else {
    selectedOrders.value = [];
  }
};

const applyBulkAction = async () => {
  if (!selectedAction.value || selectedOrders.value.length === 0) {
    showNotification('Vui lòng chọn hành động và ít nhất một đơn hàng!', false);
    return;
  }

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    if (selectedAction.value === 'delete') {
      const result = await Swal.fire({
        title: 'Xác nhận xóa hàng loạt',
        text: `Bạn có chắc chắn muốn xóa ${selectedOrders.value.length} đơn hàng đã chọn?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280'
      });

      if (!result.isConfirmed) return;

      const deletePromises = selectedOrders.value.map(id => 
        fetch(`${apiBase}/admin/orders/${id}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        })
      );

      await Promise.all(deletePromises);
      showNotification('Xóa các đơn hàng thành công!', true);
      selectedOrders.value = [];
      selectAll.value = false;
      selectedAction.value = '';
      await fetchOrders();
    } else if (selectedAction.value === 'create_payout') {
      const eligibleOrders = orders.value.filter(order => 
        selectedOrders.value.includes(order.id) && 
        order.status === 'delivered' && 
        !order.payout_id
      );

      if (eligibleOrders.length === 0) {
        showNotification('Không có đơn hàng nào đủ điều kiện để tạo payout!', false);
        return;
      }

      const result = await Swal.fire({
        title: 'Xác nhận tạo payout hàng loạt',
        text: `Bạn có chắc chắn muốn tạo payout cho ${eligibleOrders.length} đơn hàng đã chọn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Tạo',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280'
      });

      if (!result.isConfirmed) return;

      const createPromises = eligibleOrders.map(order => {
        const payoutAmount = Math.max((Number(order.final_price || 0) - Number(order.shipping?.shipping_fee || 0)) * 0.95, 0);
        return fetch(`${apiBase}/payouts`, {
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
            note: `Payout hàng loạt cho đơn hàng ${order.shipping?.tracking_code || order.id}`
          })
        });
      });

      await Promise.all(createPromises);
      showNotification('Tạo payout hàng loạt thành công!', true);
      selectedOrders.value = [];
      selectAll.value = false;
      selectedAction.value = '';
      await Promise.all([fetchOrders(), fetchPayoutData()]);
    } else if (selectedAction.value === 'approve_payout') {
      const eligibleOrders = orders.value.filter(order => 
        selectedOrders.value.includes(order.id) && 
        order.status === 'delivered' && 
        order.payout_status === 'pending' && 
        order.payout_id
      );

      if (eligibleOrders.length === 0) {
        showNotification('Không có đơn hàng nào đủ điều kiện để duyệt payout!', false);
        return;
      }

      const result = await Swal.fire({
        title: 'Xác nhận duyệt payout hàng loạt',
        text: `Bạn có chắc chắn muốn duyệt payout cho ${eligibleOrders.length} đơn hàng đã chọn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duyệt',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280'
      });

      if (!result.isConfirmed) return;

      const approvePromises = eligibleOrders.map(order => 
        fetch(`${apiBase}/payouts/${order.payout_id}/approve`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        })
      );

      await Promise.all(approvePromises);
      showNotification('Duyệt payout hàng loạt thành công!', true);
      selectedOrders.value = [];
      selectAll.value = false;
      selectedAction.value = '';
      await Promise.all([fetchOrders(), fetchPayoutData()]);
    } else if (selectedAction.value === 'update_payout_status') {
      // Hiển thị modal thông báo trước khi cập nhật
      showPayoutStatusNoteModal.value = true;
      hasReadPayoutStatusNote.value = false;
      
      // Lưu trữ thông tin cập nhật để sử dụng sau khi xác nhận
      pendingPayoutStatusUpdate.value = {
        action: 'bulk_update_payout_status',
        selectedOrders: [...selectedOrders.value]
      };
      return;
    }
  } catch (error) {
    console.error('Error applying bulk action:', error);
    showNotification(`Lỗi khi thực hiện hành động hàng loạt: ${error.message}`, false);
  } finally {
    loading.value = false;
  }
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
    let params = [];
    if (filters.value.status) params.push(`status=${encodeURIComponent(filters.value.status)}`);
    if (filters.value.payment_method) params.push(`payment_method=${encodeURIComponent(filters.value.payment_method)}`);
    if (filters.value.from_date) params.push(`from_date=${encodeURIComponent(filters.value.from_date)}`);
    if (filters.value.to_date) params.push(`to_date=${encodeURIComponent(filters.value.to_date)}`);
    if (filters.value.order_id) params.push(`order_id=${encodeURIComponent(filters.value.order_id)}`);
    if (filters.value.tracking_code) params.push(`tracking_code=${encodeURIComponent(filters.value.tracking_code)}`);
    params.push(`page=${currentPage.value}`);
    params.push(`per_page=${perPage.value}`);
    const url = `${apiBase}/admin/orders?${params.join('&')}`;
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
      // Reset checkbox khi fetch lại dữ liệu
      selectedOrders.value = [];
      selectAll.value = false;
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

    const res = await fetch(`${apiBase}/admin/payouts/approved`, {
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
        amount: Number(refund.amount) || 0,
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
        amount: refundToEdit.value.amount
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
  refunded: { text: 'Đã hoàn trả', class: 'bg-orange-100 text-orange-800' },
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

// Nhãn tiếng Việt cho trạng thái rút tiền
const withdrawStatusLabel = (status) => {
  return {
    pending: 'Chờ xử lý',
    approved: 'Đã duyệt',
    rejected: 'Đã từ chối',
    completed: 'Đã chuyển khoản'
  }[status] || status || 'Không xác định';
};
const withdrawStatusClass = (status) => {
  return {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    completed: 'bg-green-100 text-green-800'
  }[status] || 'bg-gray-100 text-gray-800';
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
  try {
    const token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBase}/seller/payouts/${order.payout_id}/approve`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await res.json();
    if (data.success) {
      showNotification('Duyệt payout thành công!');
      order.payout_status = 'completed';
      order.transferred_at = data.data.transferred_at;
    } else {
      showNotification(data.message || 'Lỗi duyệt payout', false);
    }
  } catch (e) {
    showNotification('Lỗi kết nối server', false);
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

const confirmPayoutStatusUpdate = async () => {
  if (!hasReadPayoutStatusNote.value) {
    showNotification('Vui lòng đọc và xác nhận các lưu ý trước khi tiếp tục!', false);
    return;
  }

  // Đóng modal thông báo
  showPayoutStatusNoteModal.value = false;
  hasReadPayoutStatusNote.value = false;

  // Hiển thị dialog chọn trạng thái
  const { value: newStatus } = await Swal.fire({
    title: 'Cập nhật trạng thái payout',
    text: 'Chọn trạng thái payout mới:',
    input: 'select',
    inputOptions: {
      'pending': 'Chờ xử lý',
      'completed': 'Đã chuyển khoản',
      'failed': 'Thất bại'
    },
    inputPlaceholder: 'Chọn trạng thái',
    showCancelButton: true,
    confirmButtonText: 'Cập nhật',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#6b7280',
    inputValidator: (value) => {
      if (!value) {
        return 'Vui lòng chọn trạng thái!';
      }
    }
  });

  if (!newStatus) return;

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');

    // Xử lý cập nhật hàng loạt
    if (pendingPayoutStatusUpdate.value?.action === 'bulk_update_payout_status') {
      const eligibleOrders = orders.value.filter(order => 
        pendingPayoutStatusUpdate.value.selectedOrders.includes(order.id) && 
        order.payout_id
      );

      if (eligibleOrders.length === 0) {
        showNotification('Không có đơn hàng nào có payout để cập nhật!', false);
        return;
      }

      const updatePromises = eligibleOrders.map(order => 
        fetch(`${apiBase}/admin/payouts/${order.payout_id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ status: newStatus })
        })
      );

      await Promise.all(updatePromises);
      showNotification('Cập nhật trạng thái payout hàng loạt thành công!', true);
      selectedOrders.value = [];
      selectAll.value = false;
      selectedAction.value = '';
      pendingPayoutStatusUpdate.value = null;
      await Promise.all([fetchOrders(), fetchPayoutData()]);
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

    const response = await fetch(`${apiBase}/admin/orders/${orderId}`, {
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

const fetchWithdrawList = async () => {
  withdrawLoading.value = true;
  withdrawError.value = '';
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');
    const res = await fetch(`${apiBase}/admin/withdraw-requests`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (res.ok) {
      withdrawList.value = Array.isArray(data.data) ? data.data : [];
    } else {
      throw new Error(data.message || 'Lỗi khi tải danh sách rút tiền');
    }
  } catch (e) {
    withdrawError.value = e.message;
    withdrawList.value = [];
  } finally {
    withdrawLoading.value = false;
  }
};

const approveWithdraw = async (item) => {
  const result = await Swal.fire({
    title: 'Xác nhận duyệt rút tiền',
    text: `Bạn có chắc chắn muốn duyệt yêu cầu rút ${formatPrice(item.amount)} cho ${item.bank_account_name}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Duyệt',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#16a34a',
    cancelButtonColor: '#6b7280'
  });
  if (!result.isConfirmed) return;
  try {
    withdrawLoading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');
    const res = await fetch(`${apiBase}/admin/withdraw-requests/${item.id}/approve`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (res.ok && data.success) {
      showNotification('Duyệt rút tiền thành công', true);
      await fetchWithdrawList();
    } else {
      throw new Error(data.message || 'Lỗi khi duyệt rút tiền');
    }
  } catch (e) {
    showNotification(`Lỗi khi duyệt rút tiền: ${e.message}`, false);
  } finally {
    withdrawLoading.value = false;
  }
};

function toggleWithdrawDropdown(id) {
  activeWithdrawDropdown.value = activeWithdrawDropdown.value === id ? null : id;
}

function closeWithdrawDropdown() {
  activeWithdrawDropdown.value = null;
}

function openWithdrawDetail(item) {
  withdrawDetailItem.value = item;
  showWithdrawDetailModal.value = true;
  closeWithdrawDropdown();
}

function closeWithdrawDetail() {
  showWithdrawDetailModal.value = false;
  withdrawDetailItem.value = null;
}

function openRejectWithdraw(item) {
  rejectWithdrawItem.value = item;
  rejectWithdrawReason.value = '';
  showRejectWithdrawModal.value = true;
  closeWithdrawDropdown();
}

function closeRejectWithdraw() {
  showRejectWithdrawModal.value = false;
  rejectWithdrawItem.value = null;
  rejectWithdrawReason.value = '';
}

async function submitRejectWithdraw() {
  if (!rejectWithdrawItem.value || !rejectWithdrawReason.value) {
    showNotification('Vui lòng nhập lý do từ chối!', false);
    return;
  }
  try {
    rejectWithdrawLoading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy access token. Vui lòng đăng nhập lại.');
    const res = await fetch(`${apiBase}/admin/withdraw-requests/${rejectWithdrawItem.value.id}/reject`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ note: rejectWithdrawReason.value })
    });
    const data = await res.json();
    if (res.ok && data.success) {
      showNotification('Đã từ chối yêu cầu rút tiền', true);
      await fetchWithdrawList();
      closeRejectWithdraw();
    } else {
      throw new Error(data.message || 'Lỗi khi từ chối rút tiền');
    }
  } catch (e) {
    showNotification(`Lỗi khi từ chối rút tiền: ${e.message}`, false);
  } finally {
    rejectWithdrawLoading.value = false;
  }
}

// Hàm xử lý filters cho withdraw
function resetWithdrawFilters() {
  withdrawFilters.value = {
    bank_name: '',
    shop_name: '',
    status: '',
    from_date: '',
    to_date: ''
  };
  withdrawSearch.value = '';
  withdrawSortDate.value = 'desc';
  withdrawSortAmount.value = 'desc';
  showNotification('Đã đặt lại tất cả bộ lọc', true);
}

function applyWithdrawFilters() {
  // Logic này sẽ được xử lý tự động bởi computed property withdrawListFiltered
  showNotification('Đã áp dụng bộ lọc', true);
}
const showInvoiceModal = ref(false);
const orderForInvoice = ref(null);

const openInvoicePrinter = (order) => {
  orderForInvoice.value = order;
  showInvoiceModal.value = true;
};
// Lifecycle hooks
onMounted(async () => {
  await authStore.fetchUser?.();
  if (!authStore.currentUser || authStore.currentUser.role !== 'admin') {
    router.replace('/');
    return;
  }
  fetchOrders();
  fetchPaymentMethods();
  fetchPayoutData();
  fetchLogs();
  fetchRefunds();
  fetchWithdrawList();
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

/* CSS tùy chỉnh cho modal lưu ý thanh toán */
.payment-note-modal {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-card {
  transition: all 0.3s ease;
}

.step-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.warning-box {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
  }
}

/* Hiệu ứng cho các bước */
.step-number {
  transition: all 0.3s ease;
}

.step-card:hover .step-number {
  transform: scale(1.1);
}

/* Hiệu ứng cho checkbox */
input[type="checkbox"]:checked {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

/* Responsive cho modal */
@media (max-width: 768px) {
  .modal-content {
    margin: 1rem;
    max-width: calc(100vw - 2rem);
  }
}
</style>