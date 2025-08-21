<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đơn hàng của shop</h1>
      </div>
      <!-- Nút chuyển đổi -->
      <div class="flex gap-2 mb-4 px-4 pt-4">
        <button @click="activeTab = 'orders'"
          :class="['px-4 py-2 rounded', activeTab === 'orders' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">Đơn
          hàng</button>
        <button @click="activeTab = 'payouts'"
          :class="['px-4 py-2 rounded', activeTab === 'payouts' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">Thanh
          toán đã duyệt</button>
        <button @click="activeTab = 'withdraw'"
          :class="['px-4 py-2 rounded', activeTab === 'withdraw' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">Rút
          tiền</button>
      </div>
      <div v-if="activeTab === 'orders'">
        <!-- Filter Bar -->
        <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
          <div class="flex items-center gap-2">
            <span class="font-bold">Tất cả</span>
            <span>({{ orders.length }} đơn hàng)</span>
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
              <option value="failed_delivery">Giao không thành công</option>
              <option value="rejected_by_customer">Khách từ chối nhận</option>
            </select>
            <input type="date" v-model="filters.from_date"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Từ ngày">
            <input type="date" v-model="filters.to_date"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Đến ngày">
            <input type="text" v-model="filters.order_id" placeholder="Mã đơn hàng"
              class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="ml-auto flex gap-2 items-center">
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-600">Hiển thị:</span>
              <select v-model="orderPageSize" @change="orderPage = 1; fetchOrders()"
                class="border border-gray-300 rounded px-2 py-1 text-sm">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="text-sm text-gray-600">đơn hàng/trang</span>
            </div>
            <button @click="resetFilters" class="px-4 py-2 border rounded-md bg-white hover:bg-gray-50">Đặt lại</button>
            <button @click="fetchOrders" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Tìm
              kiếm</button>
          </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div v-if="selectedOrders.length > 0"
          class="bg-blue-50 px-4 py-3 flex items-center justify-between border-b border-blue-200">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-blue-800">
              Đã chọn {{ selectedOrders.length }} đơn hàng
            </span>
            <button @click="clearSelection" class="text-sm text-blue-600 hover:text-blue-800 underline">
              Bỏ chọn tất cả
            </button>
          </div>
          <div class="flex gap-2">
            <button v-if="hasCancelledOrdersSelected" @click="bulkDeleteOrders" :disabled="bulkDeleteLoading"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
              <svg v-if="bulkDeleteLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
              <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Xóa đơn hàng đã hủy ({{ cancelledOrdersSelectedCount }})
            </button>
          </div>
        </div>

        <!-- Table -->
        <div v-if="ordersLoading" class="flex justify-center items-center py-8">
          <div class="flex items-center gap-2">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-gray-600">Đang tải danh sách đơn hàng...</span>
          </div>
        </div>
        <table v-else class="min-w-full border-collapse border border-gray-300 text-sm">
          <thead class="bg-white border-b border-gray-300">
            <tr>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
                <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Mã vận đơn</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Khách hàng</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tổng tiền</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phương thức thanh toán
              </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="orderPaginatedData.length === 0" class="border-b border-gray-300">
              <td colspan="8" class="border border-gray-300 px-3 py-4 text-center text-gray-500">
                Không có đơn hàng nào
              </td>
            </tr>
            <tr v-for="order in orderPaginatedData" :key="order.id" class="border-b border-gray-300">
              <td class="border border-gray-300 px-3 py-2 text-center">
                <input type="checkbox" :value="order.id" v-model="selectedOrders"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">{{
                order.shipping?.tracking_code || 'Chưa có' }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                {{ order.user?.name }}<br>
                <span class="text-xs">{{ order.user?.email }}</span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <div>{{ formatPrice(order.final_price) }}</div>
                <div v-if="order.discount_price > 0" class="text-xs text-gray-500">
                  Đã áp dụng mã giảm giá: {{ formatPrice(order.discount_price) }}
                </div>
                <div v-if="order.shipping && order.shipping.shipping_fee > 0" class="text-xs text-gray-500">
                  Phí vận chuyển: {{ formatPrice(order.shipping.shipping_fee) }}
                </div>


              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                {{ order.payments?.[0]?.method || '---' }}
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <span :class="statusClass(order.status)"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ statusText(order.status) }}
                </span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ formatDate(order.created_at) }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <div class="relative inline-block text-left">
                  <button @click="(e) => toggleDropdown(order.id, e)"
                    class="inline-flex items-center text-gray-600 hover:text-gray-800 focus:outline-none">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Dropdown Portal -->
        <Teleport to="body">
          <Transition enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
              <div v-for="order in filteredOrders" :key="order.id" v-show="activeDropdown === order.id"
                class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
                :style="dropdownPosition">
                <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                  <button @click="showOrderDetails(order); activeDropdown = null"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Xem chi tiết</button>
                  <button @click="openUpdateStatusModal(order); activeDropdown = null"
                    class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Cập nhật trạng
                    thái</button>
                  <button @click.prevent="openInvoicePrinter(order)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" title="In hóa đơn">
                    In hóa đơn
                  </button>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>

        <!-- Modal xem chi tiết đơn hàng -->
        <Teleport to="body">
          <div v-if="selectedOrder"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-start overflow-y-auto py-8">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 relative">
              <!-- Nút đóng -->
              <button @click="selectedOrder = null"
                class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg">
                ✕
              </button>
              <!-- Step bar trạng thái đơn hàng -->
              <div class="flex items-center justify-center gap-4 mb-6">
                <!-- Chờ xử lý -->
                <div class="flex flex-col items-center">
                  <svg class="w-7 h-7"
                    :class="selectedOrder.status === 'pending' ? 'text-blue-600' : (['confirmed', 'processing', 'shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400')"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5h6a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2a2 2 0 002 2h2a2 2 0 002-2V3" />
                  </svg>
                  <span class="text-xs mt-1"
                    :class="selectedOrder.status === 'pending' ? 'text-blue-600 font-semibold' : (['confirmed', 'processing', 'shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400')">Chờ
                    xử lý</span>
                </div>
                <div class="h-1 w-8 bg-gray-300 rounded"></div>
                <!-- Đã xác nhận -->
                <div class="flex flex-col items-center">
                  <svg class="w-7 h-7"
                    :class="['confirmed', 'processing', 'shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400'"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-xs mt-1"
                    :class="['confirmed', 'processing', 'shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đã
                    xác nhận</span>
                </div>
                <div class="h-1 w-8 bg-gray-300 rounded"></div>
                <!-- Đang xử lý -->
                <div class="flex flex-col items-center">
                  <svg class="w-7 h-7"
                    :class="['processing', 'shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400'"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.75 17L8.5 21m7-4l1.25 4m-7-4h7m-7 0a2.25 2.25 0 01-2.25-2.25V11.5a2.25 2.25 0 012.25-2.25h7A2.25 2.25 0 0117 11.5v3.25A2.25 2.25 0 0114.75 17h-7z" />
                  </svg>
                  <span class="text-xs mt-1"
                    :class="['processing', 'shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đang
                    xử lý</span>
                </div>
                <div class="h-1 w-8 bg-gray-300 rounded"></div>
                <!-- Đang giao -->
                <div class="flex flex-col items-center">
                  <svg class="w-7 h-7"
                    :class="['shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400'"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 13l2-2m0 0l7-7 7 7M5 11v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                  </svg>
                  <span class="text-xs mt-1"
                    :class="['shipping', 'delivered'].includes(selectedOrder.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đang
                    giao</span>
                </div>
                <div class="h-1 w-8 bg-gray-300 rounded"></div>
                <!-- Đã giao -->
                <div class="flex flex-col items-center">
                  <svg class="w-7 h-7" :class="selectedOrder.status === 'delivered' ? 'text-blue-600' : 'text-gray-400'"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-xs mt-1"
                    :class="selectedOrder.status === 'delivered' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đã
                    giao</span>
                </div>
              </div>
              <!-- Tiêu đề -->
              <h2 class="text-xl font-semibold mb-6 text-gray-900">Chi tiết đơn hàng</h2>
              <!-- Thông tin -->
              <div class="flex flex-col md:flex-row gap-4 mb-6 items-stretch text-sm text-gray-700">
                <!-- Box 1: Thông tin đơn hàng -->
                <div class="flex-1 border border-gray-200 rounded-lg p-4 space-y-1 flex flex-col justify-between">
                  <div class="flex items-center gap-2 text-gray-500 mb-1">
                    <span class="font-medium text-gray-900">Thông tin đơn hàng</span>
                  </div>
                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Mã vận đơn:</span>
                    <span class="text-black">{{ selectedOrder.shipping?.tracking_code || '-' }}</span>
                  </p>
                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Ngày đặt:</span>
                    <span class="text-black">{{ formatDate(selectedOrder.created_at) }}</span>
                  </p>
                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Trạng thái:</span>
                    <span :class="statusClass(selectedOrder.status)" class="text-xs px-2 py-1 rounded-full">
                      {{ statusText(selectedOrder.status) }}
                    </span>
                  </p>
                  <p v-if="selectedOrder.shipping?.status" class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Trạng thái giao hàng:</span>
                    <span :class="statusClass(selectedOrder.shipping.status)" class="text-xs px-2 py-1 rounded-full">
                      {{ statusText(selectedOrder.shipping.status) }}
                    </span>
                  </p>
                  <p v-if="['failed', 'failed_delivery', 'rejected_by_customer'].includes(selectedOrder.status)"
                    class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Lý do thất bại:</span>
                    <span class="text-black">{{ selectedOrder.failure_reason || '-' }}</span>
                  </p>
                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Tổng tiền:</span>
                    <span class="text-black">{{ formatPrice(selectedOrder.final_price) }}</span>
                  </p>
                  <p v-if="selectedOrder.shipping && selectedOrder.shipping.shipping_fee > 0"
                    class="flex gap-1 pb-2 text-xs text-gray-500">
                    <span class="min-w-[90px]">Phí vận chuyển:</span>
                    <span>{{ formatPrice(selectedOrder.shipping.shipping_fee) }}</span>
                  </p>
                  <p v-if="selectedOrder.discount_price > 0" class="flex gap-1 pb-2 text-xs text-gray-500">
                    <span class="min-w-[90px]">Mã giảm giá đã dùng:</span>
                    <span>{{ formatPrice(selectedOrder.discount_price) }}</span>
                  </p>
                  <p v-if="selectedOrder.note" class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Ghi chú:</span>
                    <span class="text-black">{{ selectedOrder.note }}</span>
                  </p>
                </div>
                <!-- Box 2: Thông tin khách hàng -->
                <div class="flex-1 border border-gray-200 rounded-lg p-4 flex flex-col space-y-2 text-sm text-gray-700">
                  <div class="flex items-center gap-2 text-gray-500">
                    <span class="font-medium text-gray-900">Thông tin khách hàng</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.232 17.578A6 6 0 006 21h12a6 6 0 00-6.768-3.422z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    <span class="text-black">{{ selectedOrder.user?.name || '-' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 12l-4-4-4 4m8 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6" />
                    </svg>
                    <span class="text-black">{{ selectedOrder.user?.email || '-' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 10a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2zm8-5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V10zm0 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="text-black">{{ selectedOrder.address?.phone || '-' }}</span>
                  </div>
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v10" />
                    </svg>
                    <span class="text-black">
                      {{ selectedOrder.address?.detail || '-' }},
                      {{ selectedOrder.address?.ward_name || '-' }},
                      {{ selectedOrder.address?.district_name || '-' }},
                      {{ selectedOrder.address?.province_name || '-' }}
                    </span>
                  </div>

                </div>
              </div>
              <!-- Danh sách sản phẩm -->
              <div class="border border-gray-200 rounded-lg mb-6">
                <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Sản phẩm đã đặt</div>
                <div v-for="item in selectedOrder.order_items || []" :key="item.id"
                  class="flex items-start justify-between p-4 border-b last:border-0">
                  <div class="flex gap-3">
                    <img :src="getProductImage(item.product?.thumbnail)" :alt="item.product?.name || 'Ảnh sản phẩm'"
                      class="w-12 h-12 object-cover rounded-md border" width="60"
                      @error="(e) => { e.target.src = '/images/no-image.png' }" />
                    <div class="space-y-1">
                      <p class="text-gray-800">{{ item.product?.name || '-' }}</p>
                      <p class="text-xs text-gray-500" v-if="item.variant && item.variant.name">
                        Phân loại: {{ item.variant.name }}
                      </p>
                      <p class="text-xs text-gray-500">{{ formatPrice(item.price) }} × {{ item.quantity || 0 }}</p>
                    </div>
                  </div>
                  <div class="text-right text-gray-900 font-semibold whitespace-nowrap">
                    {{ formatPrice(item.total) }}
                  </div>
                </div>
              </div>
              <!-- Thông tin thanh toán -->
              <div v-if="selectedOrder.payments?.length" class="border border-gray-200 rounded-lg">
                <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán</div>
                <div
                  v-if="selectedOrder.payments.length > 1 || (selectedOrder.payments.length === 1 && selectedOrder.payments[0].amount != selectedOrder.final_price)"
                  class="px-4 pt-2 pb-0 text-xs text-gray-500">
                  Lưu ý: Số tiền từng lần thanh toán có thể chưa bao gồm phí vận chuyển hoặc giảm giá. Số tiền thực tế
                  cần
                  đối soát là <b>Tổng tiền đơn hàng</b> phía trên.
                </div>
                <div v-for="payment in selectedOrder.payments" :key="payment.created_at"
                  class="px-4 py-3 text-sm text-gray-700 space-y-1">
                  <p>Phương thức: <span class="text-black">{{ payment.method || '-' }}</span></p>
                  <p>Số tiền: <span class="text-black">{{ formatPrice(payment.amount) }}</span></p>
                </div>
              </div>
              <!-- Thông tin payout -->
              <div class="border border-gray-200 rounded-lg mt-4">
                <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán cho
                  shop
                </div>
                <div class="px-4 py-3 text-sm text-gray-700">
                  <p>
                    <b>Trạng thái thanh toán:</b>
                    <span v-if="selectedOrder.payout_status === 'completed'"
                      class="text-green-600 font-semibold ml-2">Đã
                      chuyển khoản</span>
                    <span v-else-if="selectedOrder.payout_status === 'pending'"
                      class="text-yellow-600 font-semibold ml-2">Chưa thanh toán</span>
                    <span v-else-if="selectedOrder.payout_status === 'failed'"
                      class="text-red-600 font-semibold ml-2">Thanh toán thất bại</span>
                    <span v-else class="text-gray-500 font-semibold ml-2">Chưa thanh toán</span>
                  </p>
                  <p>
                    <b>Tổng tiền hàng:</b>
                    <span class="ml-2">{{ formatPrice(selectedOrder.total_price) }}</span>
                  </p>
                  <p v-if="selectedOrder.shipping && selectedOrder.shipping.shipping_fee > 0">
                    <b>Phí vận chuyển:</b>
                    <span class="ml-2">{{ formatPrice(selectedOrder.shipping.shipping_fee) }}</span>
                  </p>
                  <p v-if="selectedOrder.discount_price > 0">
                    <b>Giảm giá:</b>
                    <span class="ml-2">{{ formatPrice(selectedOrder.discount_price) }}</span>
                  </p>
                  <p>
                    <b>Chiết khấu admin (5%):</b>
                    <span class="ml-2">
                      {{ formatPrice(Math.max((Number(selectedOrder.total_price || 0) -
                        Number(selectedOrder.discount_price || 0)) * 0.05, 0)) }}
                    </span>
                  </p>
                  <p>
                    <b>Ước tính số tiền nhận được:</b>
                    <span class="ml-2">
                      {{ formatPrice(Math.max((Number(selectedOrder.total_price || 0) -
                        Number(selectedOrder.discount_price || 0)) * 0.95, 0)) }}
                    </span>
                  </p>
                  <p>
                    <b>Số tiền nhận được:</b>
                    <span class="ml-2"
                      v-if="selectedOrder.payout_amount && selectedOrder.payout_status === 'completed'">
                      {{ formatPrice(selectedOrder.payout_amount) }}
                    </span>
                    <span v-else class="text-gray-500 ml-2">---</span>
                  </p>
                  <p>
                    <b>Thời gian chuyển khoản:</b>
                    <span v-if="selectedOrder.transferred_at && selectedOrder.payout_status === 'completed'"
                      class="ml-2">
                      {{ formatDate(selectedOrder.transferred_at) }}
                    </span>
                    <span v-else class="text-gray-500">---</span>
                  </p>
                  <p class="text-xs text-gray-500 mt-2">
                    Lưu ý: Số tiền nhận được là 95% tổng giá trị tiền hàng (đã trừ giảm giá nếu có, không bao gồm phí
                    vận
                    chuyển). Nếu có điều chỉnh khác, admin sẽ ghi chú riêng.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </Teleport>

        <!-- Modal cập nhật trạng thái -->
        <Teleport to="body">
          <div v-if="showUpdateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative border border-gray-100">
              <button @click="closeUpdateModal"
                class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <div class="flex flex-col items-center mb-6">
                <div class="bg-blue-100 rounded-full p-3 mb-2">
                  <svg v-if="orderToUpdate?.status === 'pending'" class="w-8 h-8 text-yellow-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                    <circle cx="12" cy="12" r="10" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'confirmed'" class="w-8 h-8 text-blue-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'processing'" class="w-8 h-8 text-blue-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.75 17L8.5 21m7-4l1.25 4m-7-4h7m-7 0a2.25 2.25 0 01-2.25-2.25V11.5a2.25 2.25 0 012.25-2.25h7A2.25 2.25 0 0117 11.5v3.25A2.25 2.25 0 0114.75 17h-7z" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'shipping'" class="w-8 h-8 text-purple-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 13l2-2m0 0l7-7 7 7M5 11v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'delivered'" class="w-8 h-8 text-green-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'cancelled'" class="w-8 h-8 text-red-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'refunded'" class="w-8 h-8 text-orange-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10h3.5L3 6.5m0 0L6.5 3H3m18 7h-3.5l3.5 3.5m0 0L17 17h3.5M12 3v18" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'failed'" class="w-8 h-8 text-red-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'failed_delivery'" class="w-8 h-8 text-red-500" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <svg v-else-if="orderToUpdate?.status === 'rejected_by_customer'" class="w-8 h-8 text-red-500"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                  </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-1">Cập nhật trạng thái đơn hàng</h2>
                <div class="text-sm text-gray-500">Mã vận đơn: <span class="font-semibold text-gray-700">{{
                  orderToUpdate?.shipping?.tracking_code || 'Chưa có' }}</span></div>
                <div v-if="orderToUpdate?.shipping?.status" class="text-sm text-gray-500 mt-1">
                  Trạng thái GHN: <span
                    :class="statusClass(orderToUpdate.shipping.status) + ' px-2 py-1 rounded-full text-xs font-semibold'">{{
                      statusText(orderToUpdate.shipping.status) }}</span>
                </div>
              </div>
              <div class="mb-5 flex flex-col items-center">
                <div class="mb-2 text-base">Trạng thái hiện tại:</div>
                <span :class="statusClass(orderToUpdate?.status) + ' px-3 py-1 rounded-full text-xs font-semibold'">
                  {{ statusText(orderToUpdate?.status) }}
                </span>
              </div>
              <div class="mb-6">
                <label class="block mb-2 text-gray-700 font-medium">Chọn trạng thái mới:</label>
                <select v-model="newStatus" @change="validateInputs"
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                  <option v-for="status in availableStatuses" :key="status.value" :value="status.value">{{ status.label
                  }}
                  </option>
                </select>
              </div>
              <div v-if="newStatus === 'shipping'" class="mb-6">
                <label class="block mb-2 text-gray-700 font-medium">Mã vận đơn:</label>
                <input v-model="trackingCode" type="text"
                  class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                  :class="{ 'border-red-500': trackingCodeError }" placeholder="Nhập mã vận đơn (6 ký tự)" maxlength="6"
                  @input="validateTrackingCode">
                <p v-if="trackingCodeError" class="text-red-500 text-xs mt-1">{{ trackingCodeError }}</p>
              </div>
              <div v-if="['failed', 'failed_delivery', 'rejected_by_customer'].includes(newStatus)" class="mb-6">
                <label class="block mb-2 text-gray-700 font-medium">Lý do thất bại:</label>
                <input v-model="failureReason" type="text"
                  class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                  :class="{ 'border-red-500': failureReasonError }" placeholder="Nhập lý do thất bại (tối đa 255 ký tự)"
                  maxlength="255" @input="validateFailureReason">
                <p v-if="failureReasonError" class="text-red-500 text-xs mt-1">{{ failureReasonError }}</p>
              </div>
              <div class="flex justify-between gap-2 mt-6">
                <button v-if="orderToUpdate?.status === 'shipping' && orderToUpdate?.shipping?.tracking_code"
                  @click="syncGHNStatus(orderToUpdate)" :disabled="loading"
                  class="px-5 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                  <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>
                  Đồng bộ GHN
                </button>
                <div class="flex gap-2">
                  <button @click="closeUpdateModal"
                    class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Hủy</button>
                  <button @click="confirmUpdateStatus"
                    :disabled="loading || (newStatus === 'shipping' && (trackingCodeError || !trackingCode)) || (['failed', 'failed_delivery', 'rejected_by_customer'].includes(newStatus) && (failureReasonError || !failureReason.trim()))"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                      fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Cập nhật
                  </button>
                </div>
              </div>
              <div v-if="loading"
                class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50 rounded-2xl">
                <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
              </div>
            </div>
          </div>
        </Teleport>

        <!-- Phân trang -->
        <div v-if="orderTotalPages > 1" class="flex justify-center mt-4">
          <button @click="changeOrderPage(orderPage - 1)" :disabled="orderPage === 1"
            class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
          <button v-for="page in orderTotalPages" :key="page" @click="changeOrderPage(page)"
            :class="['px-3 py-1 mx-1 rounded border', orderPage === page ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{
              page }}</button>
          <button @click="changeOrderPage(orderPage + 1)" :disabled="orderPage === orderTotalPages"
            class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
        </div>
      </div>
      <div v-else-if="activeTab === 'payouts'">
        <!-- Bảng đơn hàng đã giao, chờ admin duyệt payout -->
        <div class="bg-white p-6 rounded shadow w-full overflow-x-auto mb-6">
          <h2 class="text-lg font-bold mb-4 text-yellow-700">Đơn hàng đã giao, chờ admin duyệt thanh toán</h2>
          <table class="min-w-full border border-gray-200 rounded">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 border-b text-left">Mã vận đơn</th>
                <th class="px-4 py-2 border-b text-left">Khách hàng</th>
                <th class="px-4 py-2 border-b text-right">Tổng tiền</th>
                <th class="px-4 py-2 border-b text-center">Ngày giao</th>
                <th class="px-4 py-2 border-b text-center">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in deliveredUnpaidOrders" :key="order.id" class="hover:bg-yellow-50 transition">
                <td class="px-4 py-2 border-b">{{ order.shipping?.tracking_code || '-' }}</td>
                <td class="px-4 py-2 border-b">{{ order.user?.name || '-' }}</td>
                <td class="px-4 py-2 border-b text-right">{{ formatPrice(order.final_price) }}</td>
                <td class="px-4 py-2 border-b text-center">
                  {{ order.shipping?.estimated_delivery ? formatDate(order.shipping.estimated_delivery) :
                    (order.updated_at ? formatDate(order.updated_at) : (order.created_at ? formatDate(order.created_at) :
                      '-')) }}
                </td>
                <td class="px-4 py-2 border-b text-center">
                  <span
                    class="inline-block px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-semibold text-sm">Chờ
                    duyệt payout</span>
                </td>
              </tr>
              <tr v-if="deliveredUnpaidOrders.length === 0">
                <td colspan="5" class="text-center text-gray-400 py-4">Không có đơn hàng nào chờ duyệt payout</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Bảng payout đã duyệt -->
        <div class="bg-white p-6 rounded shadow w-full overflow-x-auto">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span>💸</span> Danh sách thanh toán đã được duyệt
          </h2>
          <!-- UI filter payout -->
          <div class="flex flex-wrap gap-2 mb-4 items-end">
            <input v-model="payoutSearch" placeholder="Tìm mã payout hoặc ghi chú" class="border rounded px-2 py-1" />
            <label> Từ: <input type="date" v-model="payoutDateFrom" class="border rounded px-2 py-1" /> </label>
            <label> Đến: <input type="date" v-model="payoutDateTo" class="border rounded px-2 py-1" /> </label>
            <select v-model="payoutSort" class="border rounded px-2 py-1">
              <option value="desc">Mới nhất</option>
              <option value="asc">Cũ nhất</option>
            </select>
            <button @click="applyPayoutFilters" class="px-3 py-1 bg-blue-600 text-white rounded">Lọc</button>
            <button @click="resetPayoutFilters" class="px-3 py-1 bg-gray-200 rounded">Đặt lại</button>
          </div>
          <div v-if="payoutLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
          <div v-else-if="payoutError" class="text-center text-red-500 py-10">{{ payoutError }}</div>
          <div v-else-if="!payoutFilteredData.length" class="text-center text-gray-400 py-10">Không có payout nào</div>
          <div v-else class="mt-4">
            <div style="max-height: 320px; overflow-y: auto;">
              <table class="min-w-full border-collapse border border-gray-300 text-sm">
                <thead>
                  <tr>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">MÃ VẬN ĐƠN</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">SỐ TIỀN</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">NGÀY YÊU CẦU
                    </th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">NGÀY DUYỆT</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">TRẠNG THÁI</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">GHI CHÚ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in payoutPaginatedData" :key="item.id" class="hover:bg-blue-50 transition">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">
                      {{ item.order?.shipping?.tracking_code || '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.amount) }} đ</td>
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
            </div>
            <div v-if="payoutTotalPages > 1" class="flex justify-center mt-4">
              <button @click="payoutPage--" :disabled="payoutPage === 1"
                class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">
                <</button>
                  <button v-for="p in payoutTotalPages" :key="p" @click="payoutPage = p"
                    :class="['px-3 py-1 mx-1 rounded border', payoutPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{
                      p }}</button>
                  <button @click="payoutPage++" :disabled="payoutPage === payoutTotalPages"
                    class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">></button>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="activeTab === 'withdraw'">
        <!-- Nút yêu cầu rút tiền đặt ở trên -->
        <div class="flex justify-end mb-4">
          <button @click="openWithdrawModal" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Yêu cầu rút tiền
          </button>
        </div>
        <!-- Bảng lịch sử rút tiền -->
        <div class="bg-white p-4 rounded shadow w-full overflow-x-auto mb-6">
          <h3 class="text-lg font-bold mb-2 text-blue-700">Lịch sử rút tiền</h3>
          <!-- Thanh filter lịch sử rút tiền đặt ở trên -->
          <div class="flex flex-wrap gap-2 mb-4 items-end">
            <input v-model="withdrawSearch" placeholder="Tìm kiếm theo số tiền" class="border rounded px-2 py-1" />
            <select v-model="withdrawSortDate" class="border rounded px-2 py-1">
              <option value="desc">Mới nhất</option>
              <option value="asc">Cũ nhất</option>
            </select>
            <select v-model="withdrawSortAmount" class="border rounded px-2 py-1">
              <option value="desc">Giá cao → thấp</option>
              <option value="asc">Giá thấp → cao</option>
            </select>
          </div>
          <table class="min-w-full border border-gray-200 rounded text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 border-b text-left">Số tiền</th>
                <th class="px-4 py-2 border-b text-left">Trạng thái</th>
                <th class="px-4 py-2 border-b text-left">Thời gian gửi</th>
                <th class="px-4 py-2 border-b text-left">Thời gian duyệt</th>
                <th class="px-4 py-2 border-b text-left">Ghi chú</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in withdrawHistoryFiltered" :key="item.id" class="hover:bg-blue-50 transition">
                <td class="px-4 py-2 border-b">{{ formatNumber(item.amount) }} đ</td>
                <td class="px-4 py-2 border-b">
                  <span :class="{
                    'text-yellow-600 font-semibold': item.status === 'pending',
                    'text-green-600 font-semibold': item.status === 'completed',
                    'text-red-600 font-semibold': item.status === 'rejected'
                  }">
                    {{ withdrawStatusLabel(item.status) }}
                  </span>
                </td>
                <td class="px-4 py-2 border-b">{{ formatDate(item.created_at) }}</td>
                <td class="px-4 py-2 border-b">{{ item.approved_at ? formatDate(item.approved_at) : '-' }}</td>
                <td class="px-4 py-2 border-b">{{ item.note || '-' }}</td>
              </tr>
              <tr v-if="withdrawHistory.length === 0">
                <td colspan="5" class="text-center text-gray-400 py-4">Chưa có yêu cầu rút tiền nào</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6" :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="notificationType === 'success'" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="notificationType === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button @click="showNotification = false"
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
    <Teleport to="body">
      <div v-if="showWithdrawModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
          <button @click="showWithdrawModal = false"
            class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg">✕</button>
          <h2 class="text-xl font-bold mb-4 text-gray-800">Yêu cầu rút tiền</h2>
          <form @submit.prevent="submitWithdraw">
            <div class="mb-4">
              <div v-if="availableBalance !== null" class="mb-2 text-blue-700 font-semibold">
                Số dư khả dụng: {{ formatNumber(availableBalance) }} đ
              </div>
              <label class="block mb-1 font-medium">Số tiền muốn rút</label>
              <input type="number" v-model.number="withdrawAmount" class="w-full border rounded px-3 py-2"
                :placeholder="'Tối đa ' + formatNumber(availableBalance !== null ? availableBalance : totalApprovedPayout) + ' đ'" />
            </div>
            <div class="mb-4">
              <label class="block mb-1 font-medium">Ghi chú (tuỳ chọn)</label>
              <textarea v-model="withdrawNote" class="w-full border rounded px-3 py-2" rows="2"
                placeholder="Ghi chú cho admin (nếu có)"></textarea>
            </div>
            <div class="mb-4">
              <label class="block mb-1 font-medium">Tên ngân hàng</label>
              <select v-model="withdrawBankName" class="w-full border rounded px-3 py-2">
                <option value="" disabled selected>Chọn ngân hàng</option>

                <optgroup label="Ngân hàng Thương mại Nhà nước">
                  <option value="Agribank">Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam (Agribank)</option>
                </optgroup>

                <optgroup label="Ngân hàng Thương mại Cổ phần">
                  <option value="Vietcombank">Ngân hàng TMCP Ngoại thương Việt Nam (Vietcombank)</option>
                  <option value="VietinBank">Ngân hàng TMCP Công thương Việt Nam (VietinBank)</option>
                  <option value="BIDV">Ngân hàng TMCP Đầu tư và Phát triển Việt Nam (BIDV)</option>
                  <option value="ACB">Ngân hàng TMCP Á Châu (ACB)</option>
                  <option value="ABBANK">Ngân hàng TMCP An Bình (ABBANK)</option>
                  <option value="BVBank">Ngân hàng TMCP Bản Việt (BVBank)</option>
                  <option value="BAOVIET Bank">Ngân hàng TMCP Bảo Việt (BAOVIET Bank)</option>
                  <option value="Bac A Bank">Ngân hàng TMCP Bắc Á (Bac A Bank)</option>
                  <option value="LienVietPostBank">Ngân hàng TMCP Bưu điện Liên Việt (LienVietPostBank)</option>
                  <option value="SeABank">Ngân hàng TMCP Đông Nam Á (SeABank)</option>
                  <option value="MSB">Ngân hàng TMCP Hàng Hải Việt Nam (MSB)</option>
                  <option value="Techcombank">Ngân hàng TMCP Kỹ thương Việt Nam (Techcombank)</option>
                  <option value="MB Bank">Ngân hàng TMCP Quân Đội (MB Bank)</option>
                  <option value="OCB">Ngân hàng TMCP Phương Đông (OCB)</option>
                  <option value="HDBank">Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh (HDBank)</option>
                  <option value="NCB">Ngân hàng TMCP Quốc Dân (NCB)</option>
                  <option value="VIB">Ngân hàng TMCP Quốc tế Việt Nam (VIB)</option>
                  <option value="SCB">Ngân hàng TMCP Sài Gòn (SCB)</option>
                  <option value="SaigonBank">Ngân hàng TMCP Sài Gòn Công Thương (SaigonBank)</option>
                  <option value="SHB">Ngân hàng TMCP Sài Gòn – Hà Nội (SHB)</option>
                  <option value="TPBank">Ngân hàng TMCP Tiên Phong (TPBank)</option>
                  <option value="VPBank">Ngân hàng TMCP Việt Nam Thịnh Vượng (VPBank)</option>
                  <option value="KienlongBank">Ngân hàng TMCP Kiên Long (KienlongBank)</option>
                  <option value="Nam A Bank">Ngân hàng TMCP Nam Á (Nam A Bank)</option>
                  <option value="PG Bank">Ngân hàng TMCP Petrolimex (PG Bank)</option>
                  <option value="PVcomBank">Ngân hàng TMCP Đại chúng Việt Nam (PVcomBank)</option>
                  <option value="VietABank">Ngân hàng TMCP Việt Á (VietABank)</option>
                  <option value="Eximbank">Ngân hàng TMCP Xuất Nhập khẩu Việt Nam (Eximbank)</option>
                  <option value="Vikki Bank">Ngân hàng TMCP Số Vikki (Vikki Bank)</option>
                </optgroup>

                <optgroup label="Ngân hàng 100% vốn nước ngoài">
                  <option value="ANZ Bank">Ngân hàng TNHH MTV ANZ Việt Nam (ANZ Bank)</option>
                  <option value="Citibank">Ngân hàng TNHH MTV Citibank Việt Nam (Citibank)</option>
                  <option value="Deutsche Bank">Ngân hàng TNHH MTV Deutsche Bank Việt Nam</option>
                  <option value="Hong Leong">Ngân hàng TNHH MTV Hong Leong Việt Nam</option>
                  <option value="HSBC">Ngân hàng TNHH MTV HSBC Việt Nam (HSBC)</option>
                  <option value="Shinhan Bank">Ngân hàng TNHH MTV Shinhan Việt Nam (Shinhan Bank)</option>
                  <option value="Standard Chartered">Ngân hàng TNHH MTV Standard Chartered Việt Nam</option>
                  <option value="UOB">Ngân hàng TNHH MTV UOB Việt Nam</option>
                  <option value="Woori Bank">Ngân hàng TNHH MTV Woori Việt Nam</option>
                </optgroup>

                <optgroup label="Ngân hàng Liên doanh">
                  <option value="Indovina">Ngân hàng TNHH Indovina</option>
                  <option value="Việt - Nga">Ngân hàng TNHH MTV Việt - Nga</option>
                </optgroup>

                <optgroup label="Ngân hàng Chính sách">
                  <option value="VBSP">Ngân hàng Chính sách Xã hội Việt Nam (VBSP)</option>
                  <option value="VDB">Ngân hàng Phát triển Việt Nam (VDB)</option>
                </optgroup>

                <optgroup label="Ngân hàng Hợp tác xã">
                  <option value="Co-op Bank">Ngân hàng Hợp tác xã Việt Nam (Co-op Bank)</option>
                </optgroup>

                <optgroup label="Ngân hàng TNHH MTV (Chuyển giao bắt buộc)">
                  <option value="VCBNeo">Ngân hàng TNHH MTV Ngoại thương Công nghệ số (VCBNeo)</option>
                  <option value="MBV">Ngân hàng TNHH MTV Việt Nam Hiện Đại (MBV)</option>
                  <option value="GPBank">Ngân hàng TNHH MTV Dầu khí Toàn cầu (GPBank)</option>
                </optgroup>
              </select>
            </div>
            <div class="mb-4">
              <label class="block mb-1 font-medium">Số tài khoản</label>
              <input v-model="withdrawBankAccount" class="w-full border rounded px-3 py-2" placeholder="Số tài khoản" />
            </div>
            <div class="mb-4">
              <label class="block mb-1 font-medium">Tên chủ tài khoản</label>
              <input v-model="withdrawBankAccountName" class="w-full border rounded px-3 py-2"
                placeholder="Tên chủ tài khoản" />
            </div>
            <div v-if="withdrawError" class="mb-2 text-red-600 text-sm">{{ withdrawError }}</div>
            <div class="flex gap-2 justify-end">
              <button type="button" @click="showWithdrawModal = false"
                class="px-4 py-2 bg-gray-200 rounded">Huỷ</button>
              <button type="submit" :disabled="withdrawLoading"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Xác nhận</button>
            </div>
          </form>
          <div v-if="withdrawLoading"
            class="absolute inset-0 bg-white bg-opacity-60 flex items-center justify-center z-10 rounded-xl">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
          </div>
        </div>
      </div>
    </Teleport>
    <!-- Confirmation Dialog -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>

            <div
              class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div
                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      {{ confirmDialogTitle }}
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        {{ confirmDialogMessage }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="handleConfirmAction">
                  Xác nhận
                </button>
                <button type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="closeConfirmDialog">
                  Hủy
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
    <!-- Invoice Printer Modal -->
    <Teleport to="body">
      <InvoicePrinter v-if="showInvoiceModal" :order-id="orderForInvoice.id" @close="showInvoiceModal = false" />
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { useRuntimeConfig } from '#app';
import { secureFetch } from '@/utils/secureFetch';
import InvoicePrinter from '@/components/shared/InvoicePrinter.vue'; // Giả sử đường dẫn component

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/';
const orders = ref([]);
const selectedOrder = ref(null);
const filters = ref({ status: '', from_date: '', to_date: '', order_id: '' });
const activeDropdown = ref(null);
const showUpdateModal = ref(false);
const orderToUpdate = ref(null);
const newStatus = ref('');
const trackingCode = ref('');
const trackingCodeError = ref('');
const failureReason = ref('');
const failureReasonError = ref('');
const dropdownPosition = ref({ top: '0px', left: '0px', width: '160px' });
const showPayoutList = ref(false);
const payoutLoading = ref(false);
const payoutError = ref('');
const payoutData = ref([]);
const payoutFilteredData = ref([]);
const payoutPage = ref(1);
const payoutPageSize = ref(10);
const payoutTotalPages = computed(() => Math.ceil(payoutFilteredData.value.length / payoutPageSize.value));
const payoutPaginatedData = computed(() => {
  const start = (payoutPage.value - 1) * payoutPageSize.value;
  return payoutFilteredData.value.slice(start, start + payoutPageSize.value);
});
const payoutFilters = ref({ keyword: '', status: '' });
const orderPage = ref(1);
const orderPageSize = ref(10);
const orderTotalPages = ref(1);
// Backend pagination: orders.value is the current page's data
const orderPaginatedData = computed(() => orders.value);
// Change page and fetch orders
function changeOrderPage(page) {
  if (page < 1 || page > orderTotalPages.value) return;
  orderPage.value = page;
  fetchOrders();
}
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const loading = ref(false);
const ordersLoading = ref(false);
const sidebarTab = ref('pending');
const showWithdrawModal = ref(false);
const withdrawAmount = ref(0);
const withdrawNote = ref('');
const withdrawError = ref('');
const withdrawLoading = ref(false);
const withdrawHistory = ref([]);
const activeTab = ref('orders');
const showPayoutMenu = ref(false);
const showWithdrawHistory = ref(false);
const withdrawBankName = ref('');
const withdrawBankAccount = ref('');
const withdrawBankAccountName = ref('');
// Thêm biến lưu số dư khả dụng
const availableBalance = ref(null)

// Thêm biến filter payout
const payoutSearch = ref('');
const payoutDateFrom = ref('');
const payoutDateTo = ref('');
const payoutSort = ref('desc'); // 'desc' = mới nhất, 'asc' = cũ nhất

// Thêm biến filter cho lịch sử rút tiền
const withdrawSearch = ref('');
const withdrawSortDate = ref('desc'); // 'desc' = mới nhất, 'asc' = cũ nhất
const withdrawSortAmount = ref('desc'); // 'desc' = cao->thấp, 'asc' = thấp->cao

// Multi-select functionality
const selectedOrders = ref([]);
const bulkDeleteLoading = ref(false);

// Confirmation dialog variables
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);

// Computed properties for multi-select
const isAllSelected = computed(() => {
  return orderPaginatedData.value.length > 0 && selectedOrders.value.length === orderPaginatedData.value.length;
});

const hasCancelledOrdersSelected = computed(() => {
  return selectedOrders.value.some(orderId => {
    const order = orderPaginatedData.value.find(o => o.id === orderId);
    return order && order.status === 'cancelled';
  });
});

const cancelledOrdersSelectedCount = computed(() => {
  return selectedOrders.value.filter(orderId => {
    const order = orderPaginatedData.value.find(o => o.id === orderId);
    return order && order.status === 'cancelled';
  }).length;
});

// Multi-select functions
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedOrders.value = [];
  } else {
    selectedOrders.value = orderPaginatedData.value.map(order => order.id);
  }
};

const clearSelection = () => {
  selectedOrders.value = [];
};

// Confirmation dialog functions
const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};

const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

const bulkDeleteOrders = async () => {
  const cancelledOrderIds = selectedOrders.value.filter(orderId => {
    const order = orderPaginatedData.value.find(o => o.id === orderId);
    return order && order.status === 'cancelled';
  });

  if (cancelledOrderIds.length === 0) {
    showNotificationMessage('Không có đơn hàng đã hủy nào được chọn!', 'error');
    return;
  }

  showConfirmationDialog(
    'Xác nhận xóa đơn hàng',
    `Bạn có chắc chắn muốn xóa ${cancelledOrderIds.length} đơn hàng đã hủy này không? Hành động này không thể hoàn tác.`,
    async () => {
      try {
        bulkDeleteLoading.value = true;
        const token = localStorage.getItem('access_token');

        const response = await fetch(`${apiBase}/orders/seller/bulk-delete`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
          body: JSON.stringify({
            order_ids: cancelledOrderIds
          }),
        });

        const data = await response.json();

        if (response.ok) {
          showNotificationMessage(`Đã xóa thành công ${cancelledOrderIds.length} đơn hàng đã hủy!`, 'success');
          selectedOrders.value = [];
          await fetchOrders(); // Refresh the list
        } else {
          throw new Error(data.message || `Lỗi ${response.status}: Không thể xóa đơn hàng`);
        }
      } catch (e) {
        console.error('Error in bulkDeleteOrders:', e);
        showNotificationMessage(`Lỗi khi xóa đơn hàng: ${e.message || 'Không thể kết nối đến server'}`, 'error');
      } finally {
        bulkDeleteLoading.value = false;
      }
    }
  );
};

const fetchOrders = async () => {
  try {
    ordersLoading.value = true;
    let token = null;
    if (process.client) {
      token = localStorage.getItem('access_token');
    }
    const params = new URLSearchParams();
    if (filters.value.status) params.append('status', filters.value.status);
    if (filters.value.from_date) params.append('from_date', filters.value.from_date);
    if (filters.value.to_date) params.append('to_date', filters.value.to_date);
    if (filters.value.order_id) params.append('order_id', filters.value.order_id);
    if (trackingCode.value) params.append('tracking_code', trackingCode.value);
    params.append('page', orderPage.value);
    params.append('per_page', orderPageSize.value);
    const url = `${apiBase}/orders/seller?${params.toString()}`;

    const response = await secureFetch(url, {}, ['seller']);

    // Handle the API response structure properly
    if (response && response.data) {
      orders.value = response.data || [];
      // Update pagination metadata from backend
      if (response.meta) {
        orderTotalPages.value = response.meta.last_page || 1;
        orderPage.value = response.meta.current_page || 1;
        orderPageSize.value = response.meta.per_page || 10;
      }
    } else {
      orders.value = [];
    }
  } catch (e) {
    console.error('Error fetching orders:', e);
    orders.value = [];
    showNotificationMessage('Lỗi khi tải danh sách đơn hàng!', 'error');
  } finally {
    ordersLoading.value = false;
  }
};

const resetFilters = () => {
  filters.value = { status: '', from_date: '', to_date: '', order_id: '' };
  orderPage.value = 1; // Reset to first page
  selectedOrders.value = []; // Clear selection when filters are reset
  fetchOrders();
};

const filteredOrders = computed(() => {
  let result = [...orders.value];
  if (filters.value.status) {
    result = result.filter(o => o.status === filters.value.status);
  }
  return result;
});

const formatPrice = (price) => {
  if (!price) return '0 đ';
  if (typeof price === 'string' && price.includes('đ')) return price;
  return Number(price).toLocaleString('vi-VN') + ' đ';
};

const formatDate = (date) => {
  if (!date || date === '0000-00-00 00:00:00') return '-';
  // Nếu là dạng dd/mm/yyyy hh:mm:ss
  if (/^\d{2}\/\d{2}\/\d{4}/.test(date)) {
    const [d, m, yAndTime] = date.split('/');
    let y = '', time = '';
    if (yAndTime) {
      [y, time] = yAndTime.trim().split(' ');
    }
    const [h = '00', min = '00', s = '00'] = (time || '').split(':');
    const jsDate = new Date(`${y}-${m}-${d}T${h}:${min}:${s}`);
    if (isNaN(jsDate.getTime())) return '-';
    return jsDate.toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }
  // Nếu là ISO hoặc dạng khác
  const jsDate = new Date(date);
  if (isNaN(jsDate.getTime())) return '-';
  return jsDate.toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const statusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ xử lý';
    case 'confirmed': return 'Đã xác nhận';
    case 'processing': return 'Đang xử lý';
    case 'shipping': return 'Đang giao';
    case 'delivered': return 'Đã giao';
    case 'cancelled': return 'Đã hủy';
    case 'refunded': return 'Đã hoàn tiền';
    case 'failed': return 'Giao thất bại';
    case 'failed_delivery': return 'Giao không thành công';
    case 'rejected_by_customer': return 'Khách từ chối nhận';
    case 'completed': return 'Đã thanh toán';
    case 'waiting': return 'Chờ xác nhận';
    case 'success': return 'Thành công';
    case 'paid': return 'Đã thanh toán';
    case 'unpaid': return 'Chưa thanh toán';
    case 'ready_to_pick': return 'Chờ GHN lấy hàng';
    case 'picking': return 'GHN đang lấy hàng';
    case 'picked': return 'GHN đã lấy hàng';
    case 'delivering': return 'Đang giao hàng';
    case 'return': return 'Trả hàng';
    case 'returned': return 'Đã trả hàng';
    case 'cancel': return 'Hủy đơn GHN';
    default: return status || 'Không xác định';
  }
};

const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'confirmed': return 'bg-blue-100 text-blue-800';
    case 'processing': return 'bg-blue-100 text-blue-800';
    case 'shipping': return 'bg-purple-100 text-purple-800';
    case 'delivered': return 'bg-green-100 text-green-800';
    case 'cancelled': return 'bg-red-100 text-red-800';
    case 'refunded': return 'bg-orange-100 text-orange-800';
    case 'failed': return 'bg-red-100 text-red-800';
    case 'failed_delivery': return 'bg-red-100 text-red-800';
    case 'rejected_by_customer': return 'bg-red-100 text-red-800';
    case 'ready_to_pick': return 'bg-purple-100 text-purple-800';
    case 'picking': return 'bg-purple-100 text-purple-800';
    case 'picked': return 'bg-purple-100 text-purple-800';
    case 'delivering': return 'bg-purple-100 text-purple-800';
    case 'return': return 'bg-red-100 text-red-800';
    case 'returned': return 'bg-red-100 text-red-800';
    case 'cancel': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

const showOrderDetails = async (order) => {
  let token = null;
  if (process.client) {
    token = localStorage.getItem('access_token');
  }
  try {
    const res = await fetch(`${apiBase}/orders/seller/${order.id}`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    if (!res.ok) throw new Error('Không lấy được chi tiết đơn hàng');
    const data = await res.json();
    selectedOrder.value = data;
    // Bổ sung địa chỉ nếu thiếu tên tỉnh/huyện/xã
    if (
      selectedOrder.value.address &&
      (!selectedOrder.value.address.ward_name || !selectedOrder.value.address.district_name || !selectedOrder.value.address.province_name)
    ) {
      await loadProvinces();
      await loadDistricts(selectedOrder.value.address.province_id);
      await loadWards(selectedOrder.value.address.district_id);
      const province = provinces.value.find(p => p.ProvinceID == selectedOrder.value.address.province_id);
      const district = districts.value.find(d => d.DistrictID == selectedOrder.value.address.district_id);
      const ward = wards.value.find(w => w.WardCode == selectedOrder.value.address.ward_code);
      selectedOrder.value.address.province_name = province?.ProvinceName || '';
      selectedOrder.value.address.district_name = district?.DistrictName || '';
      selectedOrder.value.address.ward_name = ward?.WardName || '';
    }
  } catch (e) {
    showNotificationMessage('Lỗi khi tải chi tiết đơn hàng!', 'error');
    selectedOrder.value = null;
  }
};

const toggleDropdown = (orderId, event) => {
  if (activeDropdown.value === orderId) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = orderId;
    nextTick(() => {
      const button = event.target.closest('button');
      if (button) {
        const rect = button.getBoundingClientRect();
        dropdownPosition.value = {
          top: `${rect.bottom + window.scrollY + 8}px`,
          left: `${rect.right + window.scrollX - 160}px`,
          width: '160px'
        };
      }
    });
  }
};

const closeDropdown = (event) => {
  if (!event.target.closest('.relative') && !event.target.closest('.absolute')) {
    activeDropdown.value = null;
  }
};

const openUpdateStatusModal = (order) => {
  orderToUpdate.value = order;
  newStatus.value = order.status;
  trackingCode.value = order.shipping?.tracking_code || '';
  failureReason.value = order.failure_reason || '';
  trackingCodeError.value = '';
  failureReasonError.value = '';
  showUpdateModal.value = true;
};

const closeUpdateModal = () => {
  showUpdateModal.value = false;
  orderToUpdate.value = null;
  newStatus.value = '';
  trackingCode.value = '';
  failureReason.value = '';
  trackingCodeError.value = '';
  failureReasonError.value = '';
};

const validateTrackingCode = () => {
  if (newStatus.value !== 'shipping') {
    trackingCodeError.value = '';
    return;
  }
  const code = trackingCode.value.trim();
  if (!code) {
    trackingCodeError.value = 'Vui lòng nhập mã vận đơn!';
    return;
  }
  if (!/^[A-Za-z0-9]{6}$/.test(code)) {
    trackingCodeError.value = 'Mã vận đơn phải là 6 ký tự chữ cái hoặc số!';
    return;
  }
  trackingCodeError.value = '';
};

const validateFailureReason = () => {
  if (!['failed', 'failed_delivery', 'rejected_by_customer'].includes(newStatus.value)) {
    failureReasonError.value = '';
    return;
  }
  const reason = failureReason.value.trim();
  if (!reason) {
    failureReasonError.value = 'Vui lòng nhập lý do thất bại!';
    return;
  }
  if (reason.length > 255) {
    failureReasonError.value = 'Lý do thất bại không được vượt quá 255 ký tự!';
    return;
  }
  failureReasonError.value = '';
};

// Thêm hàm validateInputs để gọi validation khi thay đổi trạng thái
const validateInputs = () => {
  validateTrackingCode();
  validateFailureReason();
};

const availableStatuses = computed(() => {
  if (!orderToUpdate.value) return [];
  const allStatuses = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'confirmed', label: 'Đã xác nhận' },
    { value: 'processing', label: 'Đang xử lý' },
    { value: 'shipping', label: 'Đang giao' },
    { value: 'delivered', label: 'Đã giao' },
    { value: 'cancelled', label: 'Đã hủy' },
    { value: 'refunded', label: 'Đã hoàn tiền' },
    { value: 'failed', label: 'Giao thất bại' },
    { value: 'failed_delivery', label: 'Giao không thành công' },
    { value: 'rejected_by_customer', label: 'Khách từ chối nhận' }
  ];
  const currentStatus = orderToUpdate.value.status;
  const restrictedTransitions = {
    delivered: ['pending', 'confirmed', 'processing', 'shipping'],
    cancelled: ['pending', 'confirmed', 'processing', 'shipping', 'delivered'],
    refunded: ['pending', 'confirmed', 'processing', 'shipping', 'delivered'],
    failed: ['pending', 'confirmed', 'processing', 'shipping'],
    failed_delivery: ['pending', 'confirmed', 'processing', 'shipping'],
    rejected_by_customer: ['pending', 'confirmed', 'processing', 'shipping']
  };
  return allStatuses.filter(status => {
    if (status.value === currentStatus) return false;
    if (restrictedTransitions[currentStatus]?.includes(status.value)) return false;
    return true;
  });
});

const confirmUpdateStatus = async () => {
  if (!orderToUpdate.value || !newStatus.value) {
    showNotificationMessage('Đơn hàng hoặc trạng thái không hợp lệ!', 'error');
    return;
  }

  validateTrackingCode();
  validateFailureReason();

  if (newStatus.value === 'shipping' && trackingCodeError.value) {
    showNotificationMessage(trackingCodeError.value, 'error');
    return;
  }
  if (
    ['failed', 'failed_delivery', 'rejected_by_customer'].includes(newStatus.value) &&
    (failureReasonError.value || !failureReason.value?.trim())
  ) {
    showNotificationMessage(failureReasonError.value || 'Vui lòng nhập lý do thất bại!', 'error');
    return;
  }

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    if (!token) {
      throw new Error('Không tìm thấy token xác thực');
    }

    const payload = {
      status: newStatus.value,
    };
    if (
      ['failed', 'failed_delivery', 'rejected_by_customer'].includes(newStatus.value) &&
      failureReason.value?.trim()
    ) {
      payload.failure_reason = failureReason.value.trim();
    }

    if (newStatus.value === 'shipping' && trackingCode.value?.trim()) {
      const trimmedTrackingCode = trackingCode.value.trim();
      if (/^[A-Za-z0-9]{6}$/.test(trimmedTrackingCode)) {
        payload.tracking_code = trimmedTrackingCode;
      } else {
        showNotificationMessage('Mã vận đơn phải gồm 6 ký tự chữ cái hoặc số!', 'error');
        return;
      }
    }

    const response = await fetch(`${apiBase}/orders/seller/${orderToUpdate.value.id}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (response.ok) {
      showUpdateModal.value = false;
      orderToUpdate.value = null;
      newStatus.value = '';
      trackingCode.value = '';
      failureReason.value = '';
      trackingCodeError.value = '';
      failureReasonError.value = '';
      await fetchOrders();
      showNotificationMessage(data.status_message || 'Cập nhật trạng thái đơn hàng thành công!', 'success');
      if (data.warning_email_sent) {
        showNotificationMessage('Email cảnh báo từ chối nhận hàng đã được gửi!', 'success');
      } else if (data.warning_email_error) {
        showNotificationMessage(`Lỗi gửi email cảnh báo: ${data.warning_email_error}`, 'error');
      }
    } else {
      const msg = data.message || data.error || `Lỗi ${response.status}: Không thể cập nhật trạng thái`;
      showNotificationMessage(msg, 'error');
      throw new Error(msg);
    }
  } catch (e) {
    console.error('Error in confirmUpdateStatus:', e);
    showNotificationMessage(`Lỗi khi cập nhật trạng thái đơn hàng: ${e.message || 'Không thể kết nối đến server'}`, 'error');
  } finally {
    loading.value = false;
  }
};

const syncGHNStatus = async (order) => {
  if (!order || !order.shipping || !order.shipping.tracking_code) {
    showNotificationMessage('Thiếu mã vận đơn hoặc thông tin vận chuyển để đồng bộ GHN!', 'error');
    return;
  }
  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/orders/seller/${order.id}/sync-ghn`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        tracking_code: order.shipping.tracking_code
      })
    });

    const data = await response.json();
    if (response.ok && data.success !== false) {
      showNotificationMessage(data.message || 'Đồng bộ trạng thái GHN thành công!', 'success');
      showUpdateModal.value = false;
      orderToUpdate.value = null;
      newStatus.value = '';
      trackingCode.value = '';
      failureReason.value = '';
      trackingCodeError.value = '';
      failureReasonError.value = '';
      await fetchOrders();
    } else {
      throw new Error(data.message || `Lỗi ${response.status}: Không thể đồng bộ GHN`);
    }
  } catch (e) {
    showNotificationMessage(`Lỗi khi đồng bộ GHN: ${e.message || 'Không thể kết nối đến server'}`, 'error');
  } finally {
    loading.value = false;
  }
};

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

const getProductImage = (thumbnail) => {
  if (!thumbnail) return '/images/no-image.png';
  if (thumbnail.startsWith('http://') || thumbnail.startsWith('https://')) return thumbnail;
  return mediaBaseUrl + thumbnail;
};

const payoutStatusLabel = (status) => {
  if (status === 'completed') return 'Đã chuyển khoản';
  if (status === 'pending') return 'Chờ duyệt';
  if (status === 'rejected') return 'Từ chối';
  return status;
};

const payoutStatusClass = (status) => {
  if (status === 'completed') return 'text-green-600 font-bold';
  if (status === 'pending') return 'text-yellow-600 font-bold';
  if (status === 'rejected') return 'text-red-600 font-bold';
  return '';
};

// Cập nhật hàm filter payout
const applyPayoutFilters = () => {
  let arr = [...payoutData.value];
  if (payoutSearch.value) {
    const kw = payoutSearch.value.toLowerCase();
    arr = arr.filter(item =>
      (item.code && item.code.toLowerCase().includes(kw)) ||
      (item.note && item.note.toLowerCase().includes(kw))
    );
  }
  if (payoutDateFrom.value) {
    arr = arr.filter(item => new Date(item.created_at) >= new Date(payoutDateFrom.value));
  }
  if (payoutDateTo.value) {
    arr = arr.filter(item => new Date(item.created_at) <= new Date(payoutDateTo.value + 'T23:59:59'));
  }
  arr = arr.sort((a, b) => {
    const da = new Date(a.created_at), db = new Date(b.created_at);
    return payoutSort.value === 'desc' ? db - da : da - db;
  });
  payoutFilteredData.value = arr;
  payoutPage.value = 1;
};

const resetPayoutFilters = () => {
  payoutSearch.value = '';
  payoutDateFrom.value = '';
  payoutDateTo.value = '';
  payoutSort.value = 'desc';
  payoutFilteredData.value = [...payoutData.value];
  payoutPage.value = 1;
};

const fetchPayoutData = async () => {
  payoutLoading.value = true;
  payoutError.value = '';
  try {
    let token = null;
    if (process.client) {
      token = localStorage.getItem('access_token');
    }
    const res = await fetch(`${apiBase}/seller/payout/list-approved`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    if (!res.ok) {
      const errorData = await res.json();
      throw new Error(errorData.message || `Lỗi ${res.status}: Không lấy được dữ liệu payout`);
    }
    const resData = await res.json();
    payoutData.value = Array.isArray(resData.data) ? resData.data : [];
    payoutFilteredData.value = [...payoutData.value];
  } catch (e) {
    payoutError.value = `Không thể tải dữ liệu payout: ${e.message}`;
    payoutData.value = [];
    payoutFilteredData.value = [];
  } finally {
    payoutLoading.value = false;
  }
};

const formatNumber = (val) => {
  if (typeof val === 'number') return val.toLocaleString('vi-VN', { maximumFractionDigits: 0 });
  if (!isNaN(val) && val !== null && val !== undefined && val !== '') return Number(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 });
  return val || '0';
};

const ordersMap = computed(() => {
  const map = {};
  orders.value.forEach(o => {
    map[o.id] = o;
  });
  return map;
});

const getTrackingCode = (orderId) => {
  const order = ordersMap.value[orderId];
  return order && order.shipping && order.shipping.tracking_code ? order.shipping.tracking_code : '-';
};

const loadProvinces = async () => {
  try {
    const res = await fetch(`${apiBase}/ghn/provinces`);
    const data = await res.json();
    provinces.value = Array.isArray(data.data) ? data.data : [];
  } catch { }
};

const loadDistricts = async (provinceId) => {
  try {
    if (!provinceId) return;
    const res = await fetch(`${apiBase}/ghn/districts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ province_id: provinceId })
    });
    const data = await res.json();
    districts.value = Array.isArray(data.data) ? data.data : [];
  } catch { }
};

const loadWards = async (districtId) => {
  try {
    if (!districtId) return;
    const res = await fetch(`${apiBase}/ghn/wards`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ district_id: districtId })
    });
    const data = await res.json();
    wards.value = Array.isArray(data.data) ? data.data : [];
  } catch { }
};

// Thêm biến lọc đơn hàng đã giao, chưa payout
const deliveredUnpaidOrders = computed(() =>
  orders.value.filter(
    o => o.status === 'delivered' && o.payout_status === 'pending'
  )
)

onMounted(() => {
  fetchOrders();
  fetchPayoutData();
  fetchWithdrawHistory();
  if (process.client) {
    document.addEventListener('click', closeDropdown);
  }
});

watch(payoutFilters, applyPayoutFilters, { deep: true });

definePageMeta({
  layout: 'default-seller'
});

// Sau dòng khai báo payoutPaginatedData
const totalApprovedPayout = computed(() => {
  // Tổng tất cả payout đã duyệt (không phân trang, không filter)
  return payoutData.value.reduce((sum, item) => sum + Number(item.amount || 0), 0);
});

const fetchWithdrawHistory = async () => {
  try {
    let token = null;
    if (process.client) token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBase}/withdraw-requests`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    const data = await res.json();
    withdrawHistory.value = Array.isArray(data) ? data : (data.data || []);
  } catch (e) {
    withdrawHistory.value = [];
  }
};

const submitWithdraw = async () => {
  withdrawError.value = '';
  if (!withdrawAmount.value || withdrawAmount.value < 1) {
    withdrawError.value = 'Vui lòng nhập số tiền muốn rút';
    return;
  }
  if (withdrawAmount.value > totalApprovedPayout.value) {
    withdrawError.value = 'Số tiền rút vượt quá số dư hiện có';
    return;
  }
  withdrawLoading.value = true;
  try {
    let token = null;
    if (process.client) token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBase}/withdraw-requests`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      },
      body: JSON.stringify({
        amount: withdrawAmount.value,
        note: withdrawNote.value,
        bank_name: withdrawBankName.value,
        bank_account: withdrawBankAccount.value,
        bank_account_name: withdrawBankAccountName.value
      })
    });
    const data = await res.json();
    if (res.ok && data.success) {
      showWithdrawModal.value = false;
      withdrawAmount.value = 0;
      withdrawNote.value = '';
      withdrawBankName.value = '';
      withdrawBankAccount.value = '';
      withdrawBankAccountName.value = '';
      await fetchWithdrawHistory();
      showNotificationMessage(data.message || 'Gửi yêu cầu rút tiền thành công!', 'success');
    } else {
      withdrawError.value = data.message || 'Gửi yêu cầu thất bại';
      showNotificationMessage(data.message || 'Gửi yêu cầu thất bại', 'error');
    }
  } catch (e) {
    withdrawError.value = 'Lỗi kết nối server';
  } finally {
    withdrawLoading.value = false;
  }
};

function withdrawStatusLabel(status) {
  if (status === 'pending') return 'Chờ duyệt';
  if (status === 'completed') return 'Đã chuyển khoản';
  if (status === 'rejected') return 'Từ chối';
  return status;
}

// Hàm fetch số dư khả dụng
const fetchAvailableBalance = async () => {
  try {
    let token = null;
    if (process.client) token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBase}/withdraw-available`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    const data = await res.json();
    availableBalance.value = data.available ?? null;
  } catch (e) {
    availableBalance.value = null;
  }
}

// Gọi khi mở modal rút tiền
function openWithdrawModal() {
  showWithdrawModal.value = true;
  showPayoutMenu.value = false;
  fetchAvailableBalance();
}

function toggleWithdrawHistory() {
  showWithdrawHistory.value = !showWithdrawHistory.value;
  showPayoutMenu.value = false;
}

const withdrawHistoryFiltered = computed(() => {
  let arr = [...withdrawHistory.value];
  if (withdrawSearch.value) {
    const kw = withdrawSearch.value.replace(/\D/g, '');
    arr = arr.filter(item => String(item.amount).includes(kw));
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

const receivePayout = async (order) => {
  try {
    const token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBase}/payouts/${order.payout_id}/receive`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await res.json();
    if (data.success) {
      showNotification('Nhận tiền payout thành công!');
      order.payout_status = 'completed';
      order.transferred_at = data.data.transferred_at;
    } else {
      showNotification(data.message || 'Lỗi nhận payout', false);
    }
  } catch (e) {
    showNotification('Lỗi kết nối server', false);
  }
};

const isDelivered = (status) => {
  if (!status && status !== 0) return false;
  const s = String(status).toLowerCase();
  return s === 'delivered' || s === 'đã giao' || s.includes('delivered') || s.includes('đã giao');
};

const showInvoiceModal = ref(false);
const orderForInvoice = ref(null);

const openInvoicePrinter = (order) => {
  orderForInvoice.value = order;
  showInvoiceModal.value = true;
};

</script>

<style scoped>
.relative {
  overflow: visible !important;
}

.dropdown-menu,
.absolute.right-0.mt-2 {
  z-index: 9999;
  min-width: 160px;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  border-radius: 4px;
  overflow: visible !important;
  max-height: none !important;
}
</style>