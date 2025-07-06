<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đơn hàng của shop</h1>
      </div>
      <!-- Nút chuyển đổi -->
      <div class="flex gap-2 mb-4 px-4 pt-4">
        <button
          @click="showPayoutList = false"
          :class="['px-4 py-2 rounded', !showPayoutList ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
        >Đơn hàng</button>
        <button
          @click="showPayoutList = true"
          :class="['px-4 py-2 rounded', showPayoutList ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
        >Thanh toán đã duyệt</button>
      </div>
      <div v-if="!showPayoutList">
      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ orders.length }})</span>
        </div>
        <div class="flex gap-2">
          <select v-model="filters.status" class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="processing">Đang xử lý</option>
            <option value="shipped">Đang giao</option>
            <option value="delivered">Đã giao</option>
            <option value="cancelled">Đã hủy</option>
          </select>
          <input type="date" v-model="filters.from_date" class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Từ ngày">
          <input type="date" v-model="filters.to_date" class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Đến ngày">
          <input type="text" v-model="filters.order_id" placeholder="Mã đơn hàng" class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="ml-auto flex gap-2">
          <button @click="resetFilters" class="px-4 py-2 border rounded-md bg-white hover:bg-gray-50">Đặt lại</button>
          <button @click="fetchOrders" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Tìm kiếm</button>
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Mã vận đơn</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Khách hàng</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tổng tiền</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phương thức thanh toán</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
          </tr>
        </thead>
        <tbody>
            <tr v-for="order in orderPaginatedData" :key="order.id" class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">{{ order.shipping?.tracking_code || 'Chưa có' }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ order.user?.name }}<br>
              <span class="text-xs">{{ order.user?.email }}</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div>
                {{ formatPrice(order.final_price) }}
              </div>
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
              <span :class="statusClass(order.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ statusText(order.status) }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">{{ formatDate(order.created_at) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div class="relative inline-block text-left">
                <button @click="(e) => toggleDropdown(order.id, e)" class="inline-flex items-center text-gray-600 hover:text-gray-800 focus:outline-none">
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Dropdown Portal -->
      <Teleport to="body">
        <Transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div v-for="order in filteredOrders" :key="order.id" v-show="activeDropdown === order.id"
              class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
              :style="dropdownPosition">
              <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <button @click="showOrderDetails(order); activeDropdown = null"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Xem chi tiết</button>
                <button @click="openUpdateStatusModal(order); activeDropdown = null"
                  class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Cập nhật trạng thái</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Modal xem chi tiết đơn hàng -->
      <Teleport to="body">
        <div v-if="selectedOrder" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-start overflow-y-auto py-8">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 relative">
            <!-- Nút đóng -->
            <button @click="selectedOrder = null" class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg">
              ✕
            </button>
            <!-- Step bar trạng thái đơn hàng -->
            <div class="flex items-center justify-center gap-4 mb-6">
              <!-- Chờ xử lý -->
              <div class="flex flex-col items-center">
                <svg class="w-7 h-7" :class="selectedOrder.status === 'pending' ? 'text-blue-600' : (['processing','shipped','delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400')" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2a2 2 0 002 2h2a2 2 0 002-2V3"/>
                </svg>
                <span class="text-xs mt-1" :class="selectedOrder.status === 'pending' ? 'text-blue-600 font-semibold' : (['processing','shipped','delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400')">Chờ xử lý</span>
              </div>
              <div class="h-1 w-8 bg-gray-300 rounded"></div>
              <!-- Đã xử lý -->
              <div class="flex flex-col items-center">
                <svg class="w-7 h-7" :class="['processing','shipped','delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L8.5 21m7-4l1.25 4m-7-4h7m-7 0a2.25 2.25 0 01-2.25-2.25V11.5a2.25 2.25 0 012.25-2.25h7A2.25 2.25 0 0117 11.5v3.25A2.25 2.25 0 0114.75 17h-7z"/>
                </svg>
                <span class="text-xs mt-1" :class="['processing','shipped','delivered'].includes(selectedOrder.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đã xử lý</span>
              </div>
              <div class="h-1 w-8 bg-gray-300 rounded"></div>
              <!-- Đang giao -->
              <div class="flex flex-col items-center">
                <svg class="w-7 h-7" :class="['shipped','delivered'].includes(selectedOrder.status) ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-2m0 0l7-7 7 7M5 11v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                </svg>
                <span class="text-xs mt-1" :class="['shipped','delivered'].includes(selectedOrder.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đang giao</span>
              </div>
              <div class="h-1 w-8 bg-gray-300 rounded"></div>
              <!-- Đã giao -->
              <div class="flex flex-col items-center">
                <svg class="w-7 h-7" :class="selectedOrder.status === 'delivered' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs mt-1" :class="selectedOrder.status === 'delivered' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đã giao</span>
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
                <p class="flex gap-1 pb-2">
                  <span class="min-w-[90px] text-gray-500">Tổng tiền:</span>
                  <span class="text-black">{{ formatPrice(selectedOrder.final_price) }}</span>
                </p>
                <p v-if="selectedOrder.shipping && selectedOrder.shipping.shipping_fee > 0" class="flex gap-1 pb-2 text-xs text-gray-500">
                  <span class="min-w-[90px]">Phí vận chuyển:</span>
                  <span>{{ formatPrice(selectedOrder.shipping.shipping_fee) }}</span>
                </p>
                <p v-if="selectedOrder.discount_price > 0" class="flex gap-1 pb-2 text-xs text-gray-500">
                  <span class="min-w-[90px]">Mã giảm giá đã dùng:</span>
                  <span>{{ formatPrice(selectedOrder.discount_price) }}</span>
                </p>
              </div>
              <!-- Box 2: Thông tin khách hàng -->
              <div class="flex-1 border border-gray-200 rounded-lg p-4 flex flex-col space-y-2 text-sm text-gray-700">
                <div class="flex items-center gap-2 text-gray-500">
                  <span class="font-medium text-gray-900">Thông tin khách hàng</span>
                </div>
                <div class="flex items-center gap-2">
                  <!-- User SVG -->
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 17.578A6 6 0 006 21h12a6 6 0 00-6.768-3.422z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                  <span class="text-black">{{ selectedOrder.user?.name || '-' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <!-- Mail SVG -->
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12l-4-4-4 4m8 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6"/></svg>
                  <span class="text-black">{{ selectedOrder.user?.email || '-' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <!-- Phone SVG -->
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 10a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2zm8-5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V10zm0 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                  <span class="text-black">{{ selectedOrder.address?.phone || '-' }}</span>
                </div>
                <div class="flex items-start gap-2">
                  <!-- MapPin SVG -->
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 11v10"/></svg>
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
              <div v-for="item in selectedOrder.order_items || []" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-start justify-between p-4 border-b last:border-0">
                <div class="flex gap-3">
                  <img
                    :src="getProductImage(item.product?.thumbnail)"
                    :alt="item.product?.name || 'Ảnh sản phẩm'"
                    class="w-12 h-12 object-cover rounded-md border"
                    width="60"
                    @error="(e) => { e.target.src = '/images/no-image.png' }"
                  />
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
              <div v-if="selectedOrder.payments.length > 1 || (selectedOrder.payments.length === 1 && selectedOrder.payments[0].amount != selectedOrder.final_price)" class="px-4 pt-2 pb-0 text-xs text-gray-500">
                Lưu ý: Số tiền từng lần thanh toán có thể chưa bao gồm phí vận chuyển hoặc giảm giá. Số tiền thực tế cần đối soát là <b>Tổng tiền đơn hàng</b> phía trên.
              </div>
              <div v-for="payment in selectedOrder.payments" :key="payment.created_at" class="px-4 py-3 text-sm text-gray-700 space-y-1">
                <p>Phương thức: <span class="text-black">{{ payment.method || '-' }}</span></p>
                <p>Số tiền: <span class="text-black">{{ formatPrice(payment.amount) }}</span></p>
                <p>
                  Trạng thái:
                  <span
                    :class="{
                      'text-green-600 font-semibold': payment.status === 'completed',
                      'text-yellow-600 font-semibold': ['pending','waiting'].includes(payment.status),
                      'text-red-600 font-semibold': ['failed','cancelled'].includes(payment.status),
                      'text-gray-500': !['completed','pending','waiting','failed','cancelled'].includes(payment.status)
                    }"
                  >
                    {{ statusText(payment.status) }}
                  </span>
                </p>
              </div>
            </div>
            <!-- Thông tin payout -->
            <div class="border border-gray-200 rounded-lg mt-4">
              <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán cho shop</div>
              <div class="px-4 py-3 text-sm text-gray-700">
                <p>
                  <b>Trạng thái thanh toán:</b>
                  <span v-if="selectedOrder.payout_status === 'completed'" class="text-green-600 font-semibold">Đã chuyển khoản</span>
                  <span v-else-if="selectedOrder.payout_status === 'pending'" class="text-yellow-600 font-semibold">Chưa thanh toán</span>
                  <span v-else-if="selectedOrder.payout_status === 'failed'" class="text-red-600 font-semibold">Thanh toán thất bại</span>
                  <span v-else class="text-gray-500">Chưa thanh toán</span>
                </p>
                  <p>
                    <b>Tổng tiền hàng:</b>
                    <span>{{ formatPrice(selectedOrder.final_price) }}</span>
                  </p>
                  <p v-if="selectedOrder.shipping && selectedOrder.shipping.shipping_fee > 0">
                    <b>Phí vận chuyển:</b>
                    <span>{{ formatPrice(selectedOrder.shipping.shipping_fee) }}</span>
                  </p>
                  <p v-if="selectedOrder.discount_price > 0">
                    <b>Giảm giá:</b>
                    <span>{{ formatPrice(selectedOrder.discount_price) }}</span>
                  </p>
                  <p>
                    <b>Chiết khấu admin (5%):</b>
                    <span>
                      {{ formatPrice(Math.max((Number(selectedOrder.final_price || 0) - Number(selectedOrder.shipping?.shipping_fee || 0)) * 0.05, 0)) }}
                    </span>
                  </p>
                <p>
                  <b>Ước tính số tiền nhận được:</b>
                    <span>
                      {{ formatPrice(Math.max((Number(selectedOrder.final_price || 0) - Number(selectedOrder.shipping?.shipping_fee || 0)) * 0.95, 0)) }}
                    </span>
                </p>
                <p>
                  <b>Số tiền nhận được:</b>
                  <span v-if="selectedOrder.payout_amount && selectedOrder.payout_status === 'completed'">
                    {{ formatPrice(selectedOrder.payout_amount) }}
                  </span>
                  <span v-else class="text-gray-500">---</span>
                </p>
                <p>
                  <b>Thời gian chuyển khoản:</b>
                  <span v-if="selectedOrder.transferred_at && selectedOrder.payout_status === 'completed'">
                    {{ formatDate(selectedOrder.transferred_at) }}
                  </span>
                  <span v-else class="text-gray-500">---</span>
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Lưu ý: Số tiền nhận được là 95% tổng giá trị đơn hàng (bao gồm phí vận chuyển, đã trừ chiết khấu 5% cho admin và giảm giá nếu có). Nếu có điều chỉnh khác, admin sẽ ghi chú riêng.
                </p>
                </div>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Modal cập nhật trạng thái -->
      <div v-if="showUpdateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative border border-gray-100">
          <button @click="closeUpdateModal" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition-colors">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
          <div class="flex flex-col items-center mb-6">
            <div class="bg-blue-100 rounded-full p-3 mb-2">
              <svg v-if="orderToUpdate?.status === 'pending'" class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
              <svg v-else-if="orderToUpdate?.status === 'processing'" class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L8.5 21m7-4l1.25 4m-7-4h7m-7 0a2.25 2.25 0 01-2.25-2.25V11.5a2.25-2.25 0 012.25-2.25h7A2.25 2.25 0 0117 11.5v3.25A2.25 2.25 0 0114.75 17h-7z"/></svg>
              <svg v-else-if="orderToUpdate?.status === 'shipped'" class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-2m0 0l7-7 7 7M5 11v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
              <svg v-else-if="orderToUpdate?.status === 'delivered'" class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <svg v-else-if="orderToUpdate?.status === 'cancelled'" class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-1">Cập nhật trạng thái đơn hàng</h2>
            <div class="text-sm text-gray-500">Mã vận đơn: <span class="font-semibold text-gray-700">{{ orderToUpdate?.shipping?.tracking_code || 'Chưa có' }}</span></div>
          </div>
          <div class="mb-5 flex flex-col items-center">
            <div class="mb-2 text-base">Trạng thái hiện tại:</div>
            <span :class="statusClass(orderToUpdate?.status) + ' px-3 py-1 rounded-full text-xs font-semibold'">
              {{ statusText(orderToUpdate?.status) }}
            </span>
          </div>
          <div class="mb-6">
            <label class="block mb-2 text-gray-700 font-medium">Chọn trạng thái mới:</label>
            <select v-model="newStatus" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
              <option v-for="status in availableStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <button @click="closeUpdateModal" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Hủy</button>
              <button @click="confirmUpdateStatus" :disabled="loading" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
                <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Cập nhật
              </button>
            </div>
            <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50 rounded-2xl">
              <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Phân trang -->
        <div v-if="orderTotalPages > 1" class="flex justify-center mt-4">
          <button @click="orderPage--" :disabled="orderPage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
          <button v-for="p in orderTotalPages" :key="p" @click="orderPage = p" :class="['px-3 py-1 mx-1 rounded border', orderPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{ p }}</button>
          <button @click="orderPage++" :disabled="orderPage === orderTotalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
        </div>
      </div>
      <div v-else>
        <!-- Bảng payout đã duyệt -->
        <div class="bg-white p-6 rounded shadow w-full overflow-x-auto">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span>💸</span> Danh sách thanh toán đã được duyệt
          </h2>
          <div class="flex flex-wrap gap-3 mb-4">
            <input v-model="payoutFilters.keyword" type="text" placeholder="Tìm theo mã payout hoặc ghi chú"
              class="border p-2 rounded flex-1 min-w-[180px] placeholder-gray-400">
            <select v-model="payoutFilters.status" class="border p-2 rounded flex-1 min-w-[140px]">
              <option value="">Tất cả trạng thái</option>
              <option value="completed">Đã chuyển khoản</option>
              <option value="pending">Chờ duyệt</option>
              <option value="rejected">Từ chối</option>
            </select>
            <button @click="applyPayoutFilters" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Lọc</button>
            <button @click="resetPayoutFilters" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</button>
          </div>
          <div v-if="payoutLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
          <div v-else-if="payoutError" class="text-center text-red-500 py-10">{{ payoutError }}</div>
          <div v-else-if="!payoutFilteredData.length" class="text-center text-gray-400 py-10">Không có payout nào</div>
          <div v-else class="mt-4">
            <table class="w-full table-auto divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">MÃ PAYOUT</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">SỐ TIỀN</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">NGÀY YÊU CẦU</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">NGÀY DUYỆT</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">TRẠNG THÁI</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">GHI CHÚ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in payoutPaginatedData" :key="item.id" class="hover:bg-blue-50 transition">
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">
                    {{ getTrackingCode(item.order_id) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.amount) }} đ</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.created_at) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.transferred_at) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span :class="payoutStatusClass(item.status)">{{ payoutStatusLabel(item.status) }}</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.note }}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="payoutTotalPages > 1" class="flex justify-center mt-4">
              <button @click="payoutPage--" :disabled="payoutPage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
              <button v-for="p in payoutTotalPages" :key="p" @click="payoutPage = p" :class="['px-3 py-1 mx-1 rounded border', payoutPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{ p }}</button>
              <button @click="payoutPage++" :disabled="payoutPage === payoutTotalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6" :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="notificationType === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="notificationType === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button @click="showNotification = false" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
const router = useRouter();
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
const orderTotalPages = computed(() => Math.ceil(filteredOrders.value.length / orderPageSize.value));
const orderPaginatedData = computed(() => {
  const start = (orderPage.value - 1) * orderPageSize.value;
  return filteredOrders.value.slice(start, start + orderPageSize.value);
});
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const loading = ref(false);

const fetchOrders = async () => {
  try {
    let token = null;
    if (process.client) {
      token = localStorage.getItem('access_token');
    }
    // Build query string từ filters
    const params = new URLSearchParams();
    if (filters.value.status) params.append('status', filters.value.status);
    if (filters.value.from_date) params.append('from_date', filters.value.from_date);
    if (filters.value.to_date) params.append('to_date', filters.value.to_date);
    if (filters.value.order_id) params.append('order_id', filters.value.order_id);

    const response = await fetch(`${apiBase}/orders/seller?${params.toString()}`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    const data = await response.json();
    orders.value = data.data || [];
  } catch (e) {
    orders.value = [];
  }
};

const resetFilters = () => {
  filters.value = { status: '', from_date: '', to_date: '', order_id: '' };
  fetchOrders();
};

const filteredOrders = computed(() => {
  let result = [...orders.value];
  if (filters.value.status) {
    result = result.filter(o => o.status === filters.value.status);
  }
  // Có thể thêm lọc ngày và mã đơn hàng nếu muốn
  return result;
});

const formatPrice = (price) => {
  if (!price) return '0 đ';
  if (typeof price === 'string' && price.includes('đ')) return price;
  return Number(price).toLocaleString('vi-VN') + ' đ';
};
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
const statusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ xác nhận';
    case 'processing': return 'Đang xử lý';
    case 'shipped': return 'Đang giao';
    case 'delivered': return 'Đã giao';
    case 'cancelled': return 'Đã hủy';
    case 'completed': return 'Đã thanh toán';
    case 'waiting': return 'Chờ xác nhận';
    case 'failed': return 'Thất bại';
    case 'refunded': return 'Đã hoàn tiền';
    case 'success': return 'Thành công';
    case 'paid': return 'Đã thanh toán';
    case 'unpaid': return 'Chưa thanh toán';
    default: return status;
  }
};
const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'processing': return 'bg-blue-100 text-blue-800';
    case 'shipped': return 'bg-purple-100 text-purple-800';
    case 'delivered': return 'bg-green-100 text-green-800';
    case 'cancelled': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
const showOrderDetails = async (order) => {
  selectedOrder.value = order;
  // Nếu đã có đủ tên địa phương thì không cần fetch lại
  if (
    selectedOrder.value.address &&
    (!selectedOrder.value.address.ward_name || !selectedOrder.value.address.district_name || !selectedOrder.value.address.province_name)
  ) {
    await loadProvinces();
    await loadDistricts(selectedOrder.value.address.province_id);
    await loadWards(selectedOrder.value.address.district_id);
    // Gán tên địa phương
    const province = provinces.value.find(p => p.ProvinceID == selectedOrder.value.address.province_id)
    const district = districts.value.find(d => d.DistrictID == selectedOrder.value.address.district_id)
    const ward = wards.value.find(w => w.WardCode == selectedOrder.value.address.ward_code)
    selectedOrder.value.address.province_name = province?.ProvinceName || ''
    selectedOrder.value.address.district_name = district?.DistrictName || ''
    selectedOrder.value.address.ward_name = ward?.WardName || ''
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
  showUpdateModal.value = true;
};
const closeUpdateModal = () => {
  showUpdateModal.value = false;
  orderToUpdate.value = null;
  newStatus.value = '';
};

const availableStatuses = computed(() => {
  if (!orderToUpdate.value) return [];
  const transitions = {
    pending: ['processing', 'cancelled'],
    processing: ['shipped', 'cancelled'],
    shipped: ['delivered', 'cancelled'],
    delivered: [],
    cancelled: []
  };
  return (transitions[orderToUpdate.value.status] || []).map(status => ({
    value: status,
    label: statusText(status)
  }));
});

const confirmUpdateStatus = async () => {
  if (!orderToUpdate.value || !newStatus.value) return;
  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/orders/seller/${orderToUpdate.value.id}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ status: newStatus.value })
    });
    if (response.ok) {
      showUpdateModal.value = false;
      orderToUpdate.value = null;
      newStatus.value = '';
      await fetchOrders();
      showNotificationMessage('Cập nhật trạng thái đơn hàng thành công!', 'success');
    }
  } catch (e) {
    showNotificationMessage('Có lỗi khi cập nhật trạng thái đơn hàng!', 'error');
  } finally {
    loading.value = false;
  }
};

function showNotificationMessage(message, type = 'success') {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
}

function getProductImage(thumbnail) {
  if (!thumbnail) return '/images/no-image.png';
  if (thumbnail.startsWith('http://') || thumbnail.startsWith('https://')) return thumbnail;
  return mediaBaseUrl + thumbnail;
}

const payoutStatusText = (status) => {
  const statusText = {
    pending: 'Chờ xử lý',
    completed: 'Đã chuyển khoản',
    failed: 'Thất bại'
  };
  return statusText[status] || status;
};

const payoutStatusLabel = (status) => {
  if (status === 'completed') return 'Đã chuyển khoản'
  if (status === 'pending') return 'Chờ duyệt'
  if (status === 'rejected') return 'Từ chối'
  return status
}

const payoutStatusClass = (status) => {
  if (status === 'completed') return 'text-green-600 font-bold'
  if (status === 'pending') return 'text-yellow-600 font-bold'
  if (status === 'rejected') return 'text-red-600 font-bold'
  return ''
}

const applyPayoutFilters = () => {
  let arr = [...payoutData.value]
  if (payoutFilters.value.keyword) {
    const kw = payoutFilters.value.keyword.toLowerCase()
    arr = arr.filter(item => (item.code && item.code.toLowerCase().includes(kw)) || (item.note && item.note.toLowerCase().includes(kw)))
  }
  if (payoutFilters.value.status) {
    arr = arr.filter(item => item.status === payoutFilters.value.status)
  }
  payoutFilteredData.value = arr
  payoutPage.value = 1
}

const resetPayoutFilters = () => {
  payoutFilters.value = { keyword: '', status: '' }
  payoutFilteredData.value = [...payoutData.value]
  payoutPage.value = 1
}

async function fetchPayoutData() {
  payoutLoading.value = true
  payoutError.value = ''
  try {
    let token = null;
    if (process.client) {
      token = localStorage.getItem('access_token');
    }
    const res = await fetch(`${apiBase}/payout/list-approved`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    if (!res.ok) throw new Error('Không lấy được dữ liệu payout')
    const resData = await res.json()
    payoutData.value = Array.isArray(resData) ? resData : (resData.data || [])
    payoutFilteredData.value = [...payoutData.value]
  } catch (e) {
    payoutError.value = 'Không thể tải dữ liệu payout!'
    payoutData.value = []
    payoutFilteredData.value = []
  } finally {
    payoutLoading.value = false
  }
}

function formatNumber(val) {
  if (typeof val === 'number') return val.toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  if (!isNaN(val) && val !== null && val !== undefined && val !== '') return Number(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  return val || '0'
}

const ordersMap = computed(() => {
  const map = {};
  orders.value.forEach(o => {
    map[o.id] = o;
  });
  return map;
});

function getTrackingCode(orderId) {
  const order = ordersMap.value[orderId];
  return order && order.shipping && order.shipping.tracking_code ? order.shipping.tracking_code : '-';
}

async function loadProvinces() {
  try {
    const res = await fetch(`${apiBase}/ghn/provinces`)
    const data = await res.json()
    provinces.value = Array.isArray(data.data) ? data.data : []
  } catch {}
}
async function loadDistricts(provinceId) {
  try {
    if (!provinceId) return
    const res = await fetch(`${apiBase}/ghn/districts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ province_id: provinceId })
    })
    const data = await res.json()
    districts.value = Array.isArray(data.data) ? data.data : []
  } catch {}
}
async function loadWards(districtId) {
  try {
    if (!districtId) return
    const res = await fetch(`${apiBase}/ghn/wards`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ district_id: districtId })
    })
    const data = await res.json()
    wards.value = Array.isArray(data.data) ? data.data : []
  } catch {}
}

onMounted(() => {
  fetchOrders();
  fetchPayoutData();
  if (process.client) {
    document.addEventListener('click', closeDropdown);
  }
});

watch(payoutFilters, applyPayoutFilters, { deep: true })

definePageMeta({
layout: 'default-seller'
});
</script>

<style scoped>
/* Sửa lỗi dropdown làm xuất hiện thanh kéo */
.relative {
  overflow: visible !important;
}

/* Đảm bảo dropdown menu hiển thị trên cùng và không bị cắt, không có thanh kéo */
.dropdown-menu, .absolute.right-0.mt-2 {
  z-index: 9999;
  min-width: 160px;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  border-radius: 4px;
  overflow: visible !important;
  max-height: none !important;
}
</style>