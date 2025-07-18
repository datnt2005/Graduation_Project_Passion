<template>
  <section class="bg-[#f5f7fa] font-sans text-[#1a1a1a] min-h-screen">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
      <!-- Sidebar -->
      <aside class="w-full md:w-auto md:min-w-[250px]">
        <SidebarProfile class="w-full border border-gray-200 rounded-md bg-white" />
      </aside>

      <!-- Main content -->
      <main class="flex-1 w-full">
        <!-- Title -->
        <header class="text-center mb-6" v-once>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Đơn hàng của tôi</h1>
          <p class="text-sm text-gray-500">Theo dõi và quản lý đơn hàng bạn đã đặt</p>
        </header>

        <!-- Filter & Search -->
        <div class="mb-4 flex flex-wrap gap-2 items-center justify-center">
          <button @click="sortType = 'newest'"
            :class="['px-4 py-2 rounded-full border text-sm', sortType === 'newest' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50']"
            aria-label="Sắp xếp đơn hàng theo mới nhất">
            Mới nhất
          </button>
          <button @click="sortType = 'recent'"
            :class="['px-4 py-2 rounded-full border text-sm', sortType === 'recent' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50']"
            aria-label="Sắp xếp đơn hàng theo gần đây">
            Gần đây
          </button>
          <button @click="sortType = 'oldest'"
            :class="['px-4 py-2 rounded-full border text-sm', sortType === 'oldest' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50']"
            aria-label="Sắp xếp đơn hàng theo cũ nhất">
            Cũ nhất
          </button>
          <input @input="debouncedSearch($event.target.value)" type="text" placeholder="Tìm theo mã vận đơn"
            class="ml-4 px-3 py-2 border rounded-md text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            style="min-width:180px;" aria-label="Tìm kiếm đơn hàng theo mã vận đơn" />
          <button @click="refreshData"
            class="px-4 py-2 rounded-full border text-sm bg-white text-gray-700 border-gray-300 hover:bg-blue-50"
            aria-label="Làm mới dữ liệu">
            <i class="fas fa-sync-alt"></i> Làm mới
          </button>
        </div>

        <!-- Tabs -->
        <nav class="mb-6 flex flex-wrap justify-center gap-2">
          <button v-for="tab in tabs" :key="tab.value" @click="selectedTab = tab.value" :class="[
            'px-4 py-2 text-sm rounded-full border flex items-center gap-1 transition',
            selectedTab === tab.value ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50'
          ]" :aria-label="`Xem ${tab.label}`">
            {{ tab.label }}
            <span v-if="tab.count !== undefined"
              class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-blue-600 bg-blue-100 rounded-full">
              {{ tab.count }}
            </span>
          </button>
        </nav>

        <!-- Loading Skeleton for Order Table -->
        <div v-if="isLoading" class="bg-white rounded-md shadow border border-gray-200 overflow-hidden animate-pulse">
          <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase">
              <tr>
                <th class="px-4 py-3 text-left">STT</th>
                <th class="px-4 py-3 text-left">Mã vận đơn</th>
                <th class="px-4 py-3 text-left">Khách hàng</th>
                <th class="px-4 py-3 text-left">SĐT</th>
                <th class="px-4 py-3 text-left">Trạng thái</th>
                <th class="px-4 py-3 text-left">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="i in 4" :key="i" class="border-t">
                <td class="px-4 py-3">
                  <div class="w-5 h-4 bg-gray-200 rounded"></div>
                </td>
                <td class="px-4 py-3">
                  <div class="w-20 h-4 bg-gray-200 rounded"></div>
                </td>
                <td class="px-4 py-3">
                  <div class="w-40 h-4 bg-gray-200 rounded"></div>
                </td>
                <td class="px-4 py-3">
                  <div class="w-24 h-4 bg-gray-200 rounded"></div>
                </td>
                <td class="px-4 py-3">
                  <div class="w-24 h-6 bg-gray-200 rounded-full"></div>
                </td>
                <td class="px-4 py-3">
                  <div class="w-16 h-4 bg-gray-200 rounded"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Orders list -->
        <div v-else>
          <!-- Refund list -->
          <article v-if="selectedTab === 'refunds'"
            class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <div class="p-4 text-sm text-gray-700">
              <h2 class="text-lg font-semibold mb-4">Yêu cầu hoàn tiền</h2>
              <div v-if="isLoadingRefunds" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
              <div v-else-if="refundError" class="text-center text-red-500 py-10">{{ refundError }}</div>
              <div v-else-if="!refunds.length" class="text-center text-gray-400 py-10">Không có yêu cầu hoàn tiền nào
              </div>
              <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase text-left">
                  <tr>
                    <th class="px-4 py-3">Mã hoàn tiền</th>
                    <th class="px-4 py-3">Mã đơn hàng</th>
                    <th class="px-4 py-3">Mã vận đơn</th>
                    <th class="px-4 py-3">Số tiền</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Lý do</th>
                    <th class="px-4 py-3">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="refund in refunds" :key="refund.id" class="hover:bg-gray-50 border-t">
                    <td class="px-4 py-3">{{ refund.id }}</td>
                    <td class="px-4 py-3">{{ refund.order_id }}</td>
                    <td class="px-4 py-3">{{ refund.order?.shipping?.tracking_code || 'Chưa có mã vận đơn' }}</td>
                    <td class="px-4 py-3">{{ formatPrice(refund.amount * 1000) }}</td>
                    <td class="px-4 py-3">
                      <span :class="refundStatusClass(refund.status)"
                        class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                        {{ refundStatusText(refund.status) }}
                      </span>
                    </td>
                    <td class="px-4 py-3">{{ refund.reason }}</td>
                    <td class="px-4 py-3">{{ formatDate(refund.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <!-- Desktop table for orders -->
          <div v-if="filteredOrders.length > 0 && selectedTab !== 'refunds'"
            class="hidden md:block bg-white rounded-lg shadow border border-gray-200 overflow-visible ">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase text-left">
                <tr>
                  <th class="px-4 py-3">STT</th>
                  <th class="px-4 py-3">Mã vận đơn</th>
                  <th class="px-4 py-3">Khách hàng</th>
                  <th class="px-4 py-3">SĐT</th>
                  <th class="px-4 py-3">Trạng thái</th>
                  <th class="px-4 py-3 text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(order, index) in paginatedOrders" :key="order.id" class="hover:bg-gray-50 border-t">
                  <td class="px-4 py-3">{{ index + 1 }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ order.shipping?.tracking_code || '-' }}</td>
                  <td class="px-4 py-3 text-gray-800">{{ order.user?.name || '---' }}</td>
                  <td class="px-4 py-3">{{ order.address?.phone || '-' }}</td>
                  <td class="px-4 py-3">
                    <span :class="statusClass(order.status)"
                      class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                      {{ statusText(order.status) }}
                    </span>
                  </td>

                  <td class="px-4 py-3 text-center">
                    <div class="relative inline-block text-left data-dropdown">
                      <button @click="toggleDropdown(order.id)"
                        class="text-xs px-3 py-1 border rounded hover:bg-gray-100">
                        Tuỳ chọn <i class="fas fa-chevron-down ml-1 text-xs"></i>
                      </button>

                      <div v-show="openDropdownId === order.id"
                        class="origin-top-right absolute right-0 mt-1 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1 text-sm text-gray-700">
                          <button @click="viewOrder(order.id)" class="w-full px-4 py-2 hover:bg-gray-50 text-left">
                            <i class="fas fa-eye mr-1"></i> Chi tiết
                          </button>
                          <button v-if="order.can_delete" @click="confirmCancel(order.id)"
                            class="w-full px-4 py-2 hover:bg-gray-50 text-left text-red-500">
                            <i class="fas fa-times-circle mr-1"></i> Hủy
                          </button>
                          <button v-if="order.status === 'cancelled'" @click="reorderToCart(order)"
                            class="w-full px-4 py-2 hover:bg-gray-50 text-left text-orange-500">
                            <i class="fas fa-undo-alt mr-1"></i> Mua lại
                          </button>
                          <button v-if="order.status === 'delivered'" @click="returnOrder(order)"
                            class="w-full px-4 py-2 hover:bg-gray-50 text-left text-orange-500">
                            <i class="fas fa-undo-alt mr-1"></i> Trả hàng
                          </button>
                          <button v-if="order.status === 'delivered'" @click="printOrder(order.id)"
                            class="w-full px-4 py-2 hover:bg-gray-50 text-left">
                            <i class="fas fa-print mr-1"></i> In hóa đơn
                          </button>
                          <button v-if="order.status === 'delivered'" @click="downloadPDF(order.id)"
                            class="w-full px-4 py-2 hover:bg-gray-50 text-left text-blue-600">
                            <i class="fas fa-file-pdf mr-1"></i> Tải PDF
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>


                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile card view for orders -->
          <div v-if="filteredOrders.length > 0 && selectedTab !== 'refunds'" class="md:hidden space-y-4">
            <article v-for="(order, index) in paginatedOrders" :key="order.id"
              class="bg-white rounded-lg shadow border border-gray-200 p-4">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500 font-medium">Mã vận đơn: {{ order.shipping?.tracking_code || '-'
                  }}</span>
                <span :class="statusClass(order.status)"
                  class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                  {{ statusText(order.status) }}
                </span>
              </div>
              <div class="text-sm text-gray-800 space-y-1">
                <div><strong>Khách:</strong> {{ order.user?.name || '-' }}</div>
                <div><strong>Điện thoại:</strong> {{ order.address?.phone || '-' }}</div>
                <div>
                  <strong>Tổng tiền: </strong>
                  <span class="text-green-600 font-medium">{{ formatPrice(parseFloat(order.final_price)) }}</span>
                </div>
              </div>
              <div class="mt-3 flex flex-wrap gap-2">
                <button class="text-xs px-3 py-1 text-blue-600 hover:bg-blue-50 flex items-center gap-1"
                  @click="viewOrder(order.id)" aria-label="Xem chi tiết đơn hàng">
                  <i class="fas fa-eye"></i> Chi tiết
                </button>
                <button v-if="order.can_delete"
                  class="text-xs px-3 py-1 text-red-500 hover:bg-red-50 flex items-center gap-1"
                  @click="confirmCancel(order.id)" aria-label="Hủy đơn hàng">
                  <i class="fas fa-times-circle"></i> Hủy
                </button>
                <button v-if="order.status === 'cancelled'" @click="reorderToCart(order)"
                  class="text-xs px-3 py-1 text-orange-600 hover:bg-orange-50 flex items-center gap-1"
                  aria-label="Mua lại đơn hàng">
                  <i class="fas fa-undo-alt"></i> Mua lại
                </button>
                <div v-if="order.status === 'delivered'" class="flex gap-2">
                  <button @click="printOrder(order.id)"
                    class="text-xs px-3 py-1 text-gray-600 hover:bg-gray-50 flex items-center gap-1"
                    aria-label="In hóa đơn">
                    <i class="fas fa-print"></i> In hóa đơn
                  </button>
                  <button @click="downloadPDF(order.id)"
                    class="text-xs px-3 py-1 text-blue-600 hover:bg-blue-50 flex items-center gap-1"
                    aria-label="Tải hóa đơn PDF">
                    <i class="fas fa-file-pdf"></i> Tải PDF
                  </button>
                </div>
              </div>
            </article>
          </div>

          <!-- Pagination -->
          <div v-if="filteredOrders.length > 0 && selectedTab !== 'refunds'" class="mt-4 flex justify-center gap-2">
            <button @click="page.value -= 1" :disabled="page.value === 1"
              class="px-4 py-2 border rounded-md disabled:opacity-50" aria-label="Trang trước">Trang trước</button>
            <span class="px-4 py-2 text-sm">Trang {{ page.value }} / {{ totalPages }}</span>
            <button @click="page.value += 1" :disabled="page.value === totalPages"
              class="px-4 py-2 border rounded-md disabled:opacity-50" aria-label="Trang sau">Trang sau</button>
          </div>

          <!-- No orders -->
          <div v-if="filteredOrders.length === 0 && selectedTab !== 'refunds'" class="text-center text-gray-600 mt-10">
            Không có đơn hàng nào phù hợp.</div>
        </div>

        <!-- Order details modal -->
        <Teleport to="body">
          <transition name="fade" mode="out-in">
            <div v-if="isDetailOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center"
              aria-modal="true" role="dialog">
              <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-auto p-6 relative">
                <!-- Thanh tiến trình trạng thái đơn hàng -->
                <div class="flex items-center justify-center gap-4 mb-6">
                  <div class="flex flex-col items-center">
                    <i class="fas fa-clipboard-list text-2xl"
                      :class="selectedOrder?.status === 'pending' ? 'text-blue-600' : 'text-gray-400'"
                      aria-hidden="true"></i>
                    <span class="text-xs mt-1"
                      :class="selectedOrder?.status === 'pending' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Chờ
                      xử lý</span>
                  </div>
                  <div class="h-1 w-8 bg-gray-300 rounded"></div>
                  <div class="flex flex-col items-center">
                    <i class="fas fa-cogs text-2xl"
                      :class="['processing', 'shipped', 'delivered'].includes(selectedOrder?.status) ? 'text-blue-600' : 'text-gray-400'"
                      aria-hidden="true"></i>
                    <span class="text-xs mt-1"
                      :class="['processing', 'shipped', 'delivered'].includes(selectedOrder?.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đã
                      xử lý</span>
                  </div>
                  <div class="h-1 w-8 bg-gray-300 rounded"></div>
                  <div class="flex flex-col items-center">
                    <i class="fas fa-shipping-fast text-2xl"
                      :class="['shipped', 'delivered'].includes(selectedOrder?.status) ? 'text-blue-600' : 'text-gray-400'"
                      aria-hidden="true"></i>
                    <span class="text-xs mt-1"
                      :class="['shipped', 'delivered'].includes(selectedOrder?.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đang
                      giao</span>
                  </div>
                  <div class="h-1 w-8 bg-gray-300 rounded"></div>
                  <div class="flex flex-col items-center">
                    <i class="fas fa-check-circle text-2xl"
                      :class="selectedOrder?.status === 'delivered' ? 'text-green-600' : 'text-gray-400'"
                      aria-hidden="true"></i>
                    <span class="text-xs mt-1"
                      :class="selectedOrder?.status === 'delivered' ? 'text-green-600 font-semibold' : 'text-gray-400'">Đã
                      giao</span>
                  </div>
                </div>
                <!-- Nút đóng -->
                <button @click="isDetailOpen = false"
                  class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg"
                  aria-label="Đóng modal chi tiết đơn hàng">✕</button>
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
                      <span class="text-black">{{ selectedOrder?.shipping?.tracking_code || '-' }}</span>
                    </p>
                    <p class="flex gap-1 pb-2">
                      <span class="min-w-[90px] text-gray-500">Ngày đặt:</span>
                      <span class="text-black">{{ formatDate(selectedOrder?.created_at) }}</span>
                    </p>
                    <p class="flex gap-1 pb-2">
                      <span class="min-w-[90px] text-gray-500">Trạng thái:</span>
                      <span :class="statusClass(selectedOrder?.status)" class="text-xs px-2 py-1 rounded-full">
                        {{ statusText(selectedOrder?.status) }}
                      </span>
                    </p>
                    <p class="flex gap-1 pb-2">
                      <span class="min-w-[90px] text-gray-500">Tổng tiền:</span>
                      <span class="text-black">{{ formatPrice(parseFloat(selectedOrder?.final_price)) }}</span>
                    </p>
                  </div>
                  <!-- Box 2: Thông tin khách hàng -->
                  <div
                    class="flex-1 border border-gray-200 rounded-lg p-4 flex flex-col space-y-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2 text-gray-500">
                      <span class="font-medium text-gray-900">Thông tin khách hàng</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <i class="fas fa-user text-gray-400 w-4 h-4" aria-hidden="true"></i>
                      <span class="text-black">{{ selectedOrder?.user?.name || '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <i class="fas fa-envelope text-gray-400 w-4 h-4" aria-hidden="true"></i>
                      <span class="text-black">{{ selectedOrder?.user?.email || '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <i class="fas fa-phone-alt text-gray-400 w-4 h-4" aria-hidden="true"></i>
                      <span class="text-black">{{ selectedOrder?.address?.phone || '-' }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                      <i class="fas fa-map-marker-alt text-gray-400 w-4 h-4" aria-hidden="true"></i>
                      <span class="text-black">
                        {{ selectedOrder?.address?.detail || '-' }},
                        {{ selectedOrder?.address?.ward_name || '-' }},
                        {{ selectedOrder?.address?.district_name || '-' }},
                        {{ selectedOrder?.address?.province_name || '-' }}
                      </span>
                    </div>
                  </div>
                </div>
                <!-- Danh sách sản phẩm -->
                <div class="border border-gray-200 rounded-lg mb-6">
                  <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Sản phẩm đã đặt</div>
                  <div v-for="item in selectedOrder?.order_items || []"
                    :key="item.product?.id + '-' + (item.variant?.id || '')"
                    class="flex items-start justify-between p-4 border-b last:border-0">
                    <div class="flex gap-3">
                      <img :src="getProductImage(item.product?.thumbnail)" :alt="item.product?.name || 'Ảnh sản phẩm'"
                        class="w-12 h-12 object-cover rounded-md border" width="60" loading="lazy" />
                      <div class="space-y-1">
                        <p class="text-gray-800">{{ item.product?.name || '-' }}</p>
                        <p class="text-xs text-gray-500" v-if="item.variant">Phân loại: {{ item.variant.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatPrice(item.price) }} × {{ item.quantity || 0 }}</p>
                      </div>
                    </div>
                    <div class="text-right text-gray-900 font-semibold whitespace-nowrap">{{ formatPrice(item.total) }}
                    </div>
                  </div>
                </div>
                <!-- Thông tin thanh toán -->
                <div v-if="selectedOrder?.payments?.length" class="border border-gray-200 rounded-lg mb-6">
                  <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800 cursor-pointer"
                    @click="showPayments = !showPayments">
                    Thông tin thanh toán
                    <i :class="showPayments ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" aria-hidden="true"></i>
                  </div>
                  <div v-if="showPayments">
                    <div
                      v-if="selectedOrder.payments.length > 1 || (selectedOrder.payments.length === 1 && selectedOrder.payments[0].amount != selectedOrder.final_price)"
                      class="px-4 pt-2 pb-0 text-xs text-gray-500">
                      Lưu ý: Số tiền từng lần thanh toán có thể chưa bao gồm phí vận chuyển hoặc giảm giá. Số tiền thực
                      tế cần đối soát là <b>Tổng tiền đơn hàng</b> phía trên.
                    </div>
                    <div v-for="payment in selectedOrder.payments" :key="payment.created_at"
                      class="px-4 py-3 text-sm text-gray-700 space-y-1">
                      <p>Phương thức: <span class="text-black">{{ payment.method || '-' }}</span></p>
                      <p>Số tiền: <span class="text-black">{{ formatPrice(payment.amount) }}</span></p>
                      <p v-if="payment && payment.status">Trạng thái: <span class="text-black">{{
                        statusText(payment.status) }}</span></p>
                    </div>
                  </div>
                </div>
                <!-- Xử lý hoàn tiền -->
                <div v-if="['failed', 'cancelled', 'returned'].includes(selectedOrder.status) && !effectiveRefund"
                  class="border border-gray-200 rounded-lg mb-6">
                  <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Xử lý hoàn tiền</div>
                  <div class="px-4 py-3 text-sm text-gray-700">
                    <p><b>Lý do hiện tại:</b> {{ selectedOrder.note || 'Chưa có ghi chú' }}</p>
                    <div class="mt-2">
                      <label class="block mb-1" for="refund-amount">Số tiền hoàn (VND):</label>
                      <input v-model.number="refundAmount" id="refund-amount" type="number" min="0"
                        :max="maxRefundAmount" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
                        :disabled="true" :placeholder="`Số tiền hoàn tự động: ${formatPrice(maxRefundAmount * 1000)}`"
                        aria-label="Số tiền hoàn tự động" aria-required="true">
                      <label class="block mb-1 mt-2" for="refund-reason">Lý do hoàn tiền:</label>
                      <textarea v-model="refundReason" id="refund-reason" class="w-full border rounded px-3 py-2"
                        placeholder="Nhập lý do hoàn tiền" aria-label="Nhập lý do hoàn tiền"
                        aria-required="true"></textarea>
                      <button @click="requestRefund(selectedOrder)"
                        class="mt-2 px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700"
                        :disabled="!refundReason" aria-label="Gửi yêu cầu hoàn tiền">Gửi yêu cầu hoàn tiền</button>
                    </div>
                  </div>
                </div>
                <div v-if="effectiveRefund" class="border border-gray-200 rounded-lg mb-6">
                  <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin hoàn tiền</div>
                  <div class="px-4 py-3 text-sm text-gray-700">
                    <p><b>Mã hoàn tiền:</b> {{ effectiveRefund.id || '-' }}</p>
                    <p><b>Số tiền hoàn:</b> {{ formatPrice(effectiveRefund.amount * 1000) }}</p>
                    <p><b>Trạng thái:</b>
                      <span :class="refundStatusClass(effectiveRefund.status)">{{
                        refundStatusText(effectiveRefund.status) }}</span>
                    </p>
                    <p><b>Lý do:</b> {{ effectiveRefund.reason || '-' }}</p>
                    <p><b>Thời gian tạo:</b> {{ formatDate(effectiveRefund.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </Teleport>
      </main>
    </div>
  </section>

  <ReturnModal v-if="isReturnModalOpen" :order="selectedReturnOrder" @close="isReturnModalOpen = false" />

</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue';
import Swal from 'sweetalert2/dist/sweetalert2.min.js';
import 'sweetalert2/dist/sweetalert2.min.css';
import { useCartStore } from '~/stores/cart';
import { useRouter, useHead } from '#app';
import { debounce } from 'lodash';
import { secureFetch } from '@/utils/secureFetch' 


// State
const orders = ref([]);
const isLoading = ref(true);
const selectedTab = ref('all');
const user = ref(null);
const selectedOrder = ref(null);
const isDetailOpen = ref(false);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const cartStore = useCartStore();
const router = useRouter();
const config = useRuntimeConfig();
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/';
const apiBase = config.public.apiBaseUrl;
const sortType = ref('newest');
const searchTracking = ref('');
const refunds = ref([]);
const isLoadingRefunds = ref(false);
const refundError = ref('');
const refundAmount = ref(0);
const refundReason = ref('');
const showPayments = ref(false);
const page = ref(1);
const perPage = 10;
const isReturnModalOpen = ref(false)
const selectedReturnOrder = ref(null)
//  dropdown
const openDropdownId = ref(null)

function toggleDropdown(id) {
  openDropdownId.value = openDropdownId.value === id ? null : id
}



const returnReason = ref('')

function returnOrder(order) {
  selectedReturnOrder.value = order
  isReturnModalOpen.value = true
}

// Tabs configuration
const tabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ xử lý', value: 'pending', count: 0 },
  { label: 'Đang xử lý', value: 'processing', count: 0 },
  { label: 'Đã gửi hàng', value: 'shipped', count: 0 },
  { label: 'Đã giao hàng', value: 'delivered', count: 0 },
  { label: 'Đã huỷ', value: 'cancelled', count: 0 },
  { label: 'Yêu cầu hoàn tiền', value: 'refunds', count: 0 }
]);

// Status mapping
const statusText = (status) => ({
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  shipping: 'Đang giao hàng',
  shipped: 'Đã gửi hàng',
  delivered: 'Đã giao hàng',
  cancelled: 'Đã huỷ',
  completed: 'Đã thanh toán',
  failed: 'Thất bại',
  refunded: 'Đã hoàn tiền',
  returned: 'Đã trả hàng',
  success: 'Thành công',
  paid: 'Đã thanh toán',
  unpaid: 'Chưa thanh toán',
  waiting: 'Đang chờ',
  error: 'Lỗi',
})[status] || (status ? status : 'Không xác định');

const statusClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-700',
  processing: 'bg-indigo-100 text-indigo-700',
  shipped: 'bg-blue-100 text-blue-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-orange-100 text-orange-700',
  returned: 'bg-orange-100 text-orange-700',
  failed: 'bg-red-100 text-red-700'
})[status] || 'bg-gray-100 text-gray-700';

const refundStatusText = (status) => ({
  pending: 'Chờ xử lý',
  approved: 'Đã duyệt',
  rejected: 'Đã từ chối'
})[status] || (status ? status : 'Không xác định');

const refundStatusClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800'
})[status] || 'bg-gray-100 text-gray-800';

// Utility functions
const formatPrice = (price) => {
  const number = typeof price === 'string' ? parseFloat(price) : price;
  if (isNaN(number)) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(number);
};

const formatDate = (date) => {
  if (!date) return '-';
  try {
    let parsedDate;
    if (typeof date === 'string' && date.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
      const [day, month, year, time] = date.split(/[\s\/:]+/);
      parsedDate = new Date(`${year}-${month}-${day}T${time}:00.000Z`);
    } else {
      parsedDate = new Date(date);
    }
    if (isNaN(parsedDate)) return '-';
    return parsedDate.toLocaleString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch {
    return '-';
  }
};

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
      toastEl.addEventListener('mouseenter', () => Swal.stopTimer());
      toastEl.addEventListener('mouseleave', () => Swal.resumeTimer());
    }
  });
};

const getProductImage = (thumbnail) => {
  if (!thumbnail) return '/images/no-image.png';
  if (thumbnail.startsWith('http://') || thumbnail.startsWith('https://')) return `${thumbnail}?w=100&q=80`;
  return `${mediaBaseUrl}${thumbnail}?w=100&q=80`;
};

const maxRefundAmount = computed(() => {
  if (!selectedOrder.value) return 0;
  const finalPrice = parseFloat(selectedOrder.value.final_price || 0) / 1000; // Chia cho 1000 để khớp đơn vị
  const shippingFee = parseFloat(selectedOrder.value.shipping?.shipping_fee || 0) / 1000;
  return Math.max(finalPrice - shippingFee, 0);
});

const effectiveRefund = computed(() => {
  if (selectedOrder.value?.refund) return selectedOrder.value.refund;
  return refunds.value.find(refund => refund.order_id === selectedOrder.value?.id) || null;
});

const debouncedSearch = debounce((value) => {
  searchTracking.value = value;
  page.value = 1; // Reset về trang 1 khi tìm kiếm
}, 300);

// API calls
const fetchOrders = async (forceRefresh = false) => {
  const cacheKey = `user_orders_page_${page.value}`;
  if (!forceRefresh) {
    const cache = localStorage.getItem(cacheKey);
    if (cache) {
      const cachedData = JSON.parse(cache);
      if (cachedData.expiry > Date.now()) {
        orders.value = cachedData.orders;
        user.value = cachedData.user;
        updateTabCounts();
        isLoading.value = false;
        return;
      }
    }
  }
  isLoading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Chưa đăng nhập');

    const [userRes, ordersRes] = await Promise.all([
      axios.get(`${apiBase}/me`, { headers: { Authorization: `Bearer ${token}` } }),
      axios.get(`${apiBase}/user/orders?page=${page.value}&per_page=${perPage}`, { headers: { Authorization: `Bearer ${token}` } })
    ]);

    console.log('fetchOrders API response:', { user: userRes.data, orders: ordersRes.data });

    user.value = userRes.data;
    orders.value = Array.isArray(ordersRes.data.data) ? ordersRes.data.data.map(order => ({
      ...order,
      refund: order.refund || null
    })) : [];
    localStorage.setItem(cacheKey, JSON.stringify({
      orders: orders.value,
      user: user.value,
      expiry: Date.now() + 1000 * 60 * 5 // Cache 5 phút
    }));
    updateTabCounts();
  } catch (err) {
    console.error('fetchOrders error:', err);
    toast('error', 'Không thể tải đơn hàng!');
  } finally {
    isLoading.value = false;
  }
};

const fetchRefunds = async (forceRefresh = false) => {
  const cacheKey = 'user_refunds';
  if (!forceRefresh) {
    const cache = localStorage.getItem(cacheKey);
    if (cache) {
      const cachedData = JSON.parse(cache);
      if (cachedData.expiry > Date.now()) {
        refunds.value = cachedData.refunds;
        updateTabCounts();
        isLoadingRefunds.value = false;
        return;
      }
    }
  }
  isLoadingRefunds.value = true;
  refundError.value = '';
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Chưa đăng nhập');

    const { data } = await axios.get(`${apiBase}/refunds`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    console.log('fetchRefunds API response:', data);

    refunds.value = Array.isArray(data.data) ? data.data.map(refund => ({
      ...refund,
      amount: Number(refund.amount) || 0,
      created_at: refund.created_at || null,
      order: refund.order || null
    })) : [];
    if (!refunds.value.length) {
      refundError.value = 'Không có yêu cầu hoàn tiền nào.';
    } else {
      for (const refund of refunds.value) {
        if (!refund.order) {
          try {
            const orderResponse = await axios.get(`${apiBase}/user/orders/${refund.order_id}`, {
              headers: { Authorization: `Bearer ${token}` }
            });
            refund.order = orderResponse.data.data || null;
          } catch (err) {
            refund.order = null;
          }
        }
      }
    }
    localStorage.setItem(cacheKey, JSON.stringify({
      refunds: refunds.value,
      expiry: Date.now() + 1000 * 60 * 5 // Cache 5 phút
    }));
    updateTabCounts();
  } catch (error) {
    console.error('fetchRefunds error:', error);
    refundError.value = error.response?.data?.message || 'Lỗi khi tải danh sách hoàn tiền';
    refunds.value = [];
  } finally {
    isLoadingRefunds.value = false;
  }
};

const viewOrder = async (id) => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      router.push('/login');
      throw new Error('Chưa đăng nhập');
    }

    const { data } = await axios.get(`${apiBase}/user/orders/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    console.log('viewOrder API response:', data);

    let orderData = data.success !== undefined && data.data ? data.data : data;
    if (!orderData || !orderData.id) {
      throw new Error(data?.message || 'Phản hồi API không chứa dữ liệu đơn hàng');
    }

    selectedOrder.value = {
      ...orderData,
      refund: orderData.refund || null,
      address: orderData.address || {},
      user: orderData.user || {},
      order_items: Array.isArray(orderData.order_items) ? orderData.order_items : [],
      payments: Array.isArray(orderData.payments) ? orderData.payments : [],
      shipping: orderData.shipping || null
    };

    console.log('selectedOrder.value:', selectedOrder.value);

    if (!selectedOrder.value.refund) {
      const refund = refunds.value.find(r => r.order_id === id);
      if (refund) {
        selectedOrder.value.refund = refund;
      }
    }

    if (!selectedOrder.value.refund && ['failed', 'cancelled', 'returned'].includes(selectedOrder.value.status)) {
      refundAmount.value = maxRefundAmount.value;
      toast('info', `Số tiền hoàn đã được tự động điền: ${formatPrice(refundAmount.value * 1000)}`);
    } else {
      refundAmount.value = 0;
    }

    isDetailOpen.value = true;

    if (!provinces.value.length || !districts.value.length || !wards.value.length) {
      await Promise.all([loadProvinces(), loadDistricts(), loadWards()]);
    }
    const province = provinces.value.find(p => p.ProvinceID == selectedOrder.value.address?.province_id);
    const district = districts.value.find(d => d.DistrictID == selectedOrder.value.address?.district_id);
    const ward = wards.value.find(w => w.WardCode == selectedOrder.value.address?.ward_code);
    selectedOrder.value.address = {
      ...selectedOrder.value.address,
      province_name: province?.ProvinceName || '',
      district_name: district?.DistrictName || '',
      ward_name: ward?.WardName || ''
    };

    const cacheKey = `user_orders_page_${page.value}`;
    localStorage.removeItem(cacheKey);
    localStorage.setItem(cacheKey, JSON.stringify({
      orders: orders.value.map(o => o.id === id ? selectedOrder.value : o),
      user: user.value,
      expiry: Date.now() + 1000 * 60 * 5 // Cache 5 phút
    }));
  } catch (err) {
    console.error('viewOrder error:', err, {
      status: err.response?.status,
      data: err.response?.data
    });
    let message = err.message || 'Không thể tải thông tin đơn hàng!';
    if (err.response?.status === 401) {
      localStorage.removeItem('access_token');
      router.push('/login');
      message = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại!';
    } else if (err.response?.status === 404) {
      message = 'Không tìm thấy đơn hàng!';
    } else if (err.response?.data?.message) {
      message = err.response.data.message;
    }
    toast('error', message);
  }
};

const requestRefund = async (order) => {
  if (!refundReason.value) {
    toast('error', 'Vui lòng nhập lý do hoàn tiền!');
    return;
  }

  if (refundAmount.value > maxRefundAmount.value) {
    toast('error', `Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value * 1000)}!`);
    return;
  }

  const result = await Swal.fire({
    title: 'Xác nhận yêu cầu hoàn tiền',
    text: `Bạn có chắc chắn muốn yêu cầu hoàn ${formatPrice(refundAmount.value * 1000)} cho đơn hàng ${order.shipping?.tracking_code || order.id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Gửi yêu cầu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#f97316',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Chưa đăng nhập');

    console.log('Sending refund request:', {
      order_id: order.id,
      final_price: selectedOrder.value.final_price,
      shipping_fee: selectedOrder.value.shipping?.shipping_fee || 0,
      maxRefundAmount: maxRefundAmount.value,
      refundAmount: refundAmount.value
    });

    const cacheKey = `user_orders_page_${page.value}`;
    localStorage.removeItem(cacheKey);
    localStorage.removeItem('user_refunds');

    const response = await axios.post(`${apiBase}/orders/${order.id}/refund`, {
      reason: refundReason.value,
      amount: Number(refundAmount.value),
      status: 'pending'
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (response.data.success) {
      toast('success', response.data.message || 'Yêu cầu hoàn tiền đã được gửi!');
      await Promise.all([fetchOrders(true), fetchRefunds(true)]);
      if (selectedOrder.value?.id === order.id) {
        await viewOrder(order.id);
      }
      refundAmount.value = 0;
      refundReason.value = '';
      isDetailOpen.value = true;
    } else {
      throw new Error(response.data.message || 'Lỗi khi gửi yêu cầu hoàn tiền');
    }
  } catch (error) {
    console.error('requestRefund error:', error);
    let message = error.response?.data?.message || error.message;
    if (message.includes('Đơn hàng này đã có yêu cầu hoàn tiền')) {
      message = 'Đơn hàng này đã có yêu cầu hoàn tiền đang chờ xử lý!';
      await Promise.all([fetchOrders(true), fetchRefunds(true)]);
      if (selectedOrder.value?.id === order.id) {
        await viewOrder(order.id);
      }
    } else if (message.includes('Số tiền hoàn không được vượt quá')) {
      message = `Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value * 1000)}!`;
    } else if (error.response?.status === 404) {
      message = 'Không tìm thấy đơn hàng hoặc dịch vụ hoàn tiền!';
    } else if (error.response?.status === 403) {
      message = 'Bạn không có quyền yêu cầu hoàn tiền. Vui lòng đăng nhập lại!';
      localStorage.removeItem('access_token');
      router.push('/login');
    }
    toast('error', message);
  }
};

const reorderToCart = async (order) => {
  const token = localStorage.getItem('access_token');
  const item = order.order_items[0];

  if (!item?.product?.id || !item?.quantity) {
    toast('error', 'Không thể thêm sản phẩm vào giỏ hàng!');
    return;
  }

  const availableStock = item.variant?.stock ?? item.product?.stock ?? 0;
  if (item.quantity > availableStock) {
    toast('error', 'Hết hàng, vui lòng quay lại sau!');
    return;
  }

  try {
    await axios.post(`${apiBase}/cart/add`, {
      product_id: item.product.id,
      product_variant_id: item.variant?.id || undefined,
      quantity: item.quantity,
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });

    router.push('/cart');
  } catch (err) {
    toast('error', 'Không thể thêm sản phẩm vào giỏ hàng!');
  }
};

const loadProvinces = async () => {
  try {
    const res = await axios.get(`${apiBase}/ghn/provinces`);
    provinces.value = Array.isArray(res.data.data) ? res.data.data : [];
  } catch (err) {
    toast('error', 'Lỗi khi tải danh sách tỉnh thành!');
  }
};

const loadDistricts = async () => {
  try {
    const provinceIds = [...new Set(orders.value.map(o => o.address?.province_id).filter(id => id))];
    for (const provinceId of provinceIds) {
      const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId });
      if (Array.isArray(res.data.data)) {
        districts.value.push(...res.data.data.filter(d => !districts.value.some(existing => existing.DistrictID === d.DistrictID)));
      }
    }
  } catch (err) {
    toast('error', 'Lỗi khi tải danh sách quận huyện!');
  }
};

const loadWards = async () => {
  try {
    const districtIds = [...new Set(orders.value.map(o => o.address?.district_id).filter(id => id))];
    for (const districtId of districtIds) {
      const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId });
      if (Array.isArray(res.data.data)) {
        wards.value.push(...res.data.data.filter(w => !wards.value.some(existing => existing.WardCode === w.WardCode)));
      }
    }
  } catch (err) {
    toast('error', 'Lỗi khi tải danh sách phường xã!');
  }
};

const updateTabCounts = () => {
  const counts = { all: orders.value.length, refunds: refunds.value.length };
  orders.value.forEach(o => {
    counts[o.status] = (counts[o.status] || 0) + 1;
  });
  tabs.value = tabs.value.map(tab => ({
    ...tab,
    count: counts[tab.value] || 0
  }));
};

const confirmCancel = (orderId) => {
  Swal.fire({
    icon: 'error',
    title: 'Xác nhận hủy',
    text: 'Bạn có chắc chắn muốn hủy đơn hàng này?',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#e53e3e',
    cancelButtonColor: '#fff',
    customClass: {
      popup: 'rounded-md shadow-sm',
      title: 'text-base font-semibold',
      htmlContainer: 'text-sm',
      confirmButton: 'text-sm px-4 py-2 bg-red-600 text-white',
      cancelButton: 'text-sm px-4 py-2 bg-white text-gray-700 border border-gray-300'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      cancelOrder(orderId);
    }
  });
};

const cancelOrder = async (id) => {
  try {
    const token = localStorage.getItem('access_token');
    await axios.post(`${apiBase}/user/orders/${id}/cancel`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    toast('success', 'Đơn hàng đã được hủy!');
    const cacheKey = `user_orders_page_${page.value}`;
    localStorage.removeItem(cacheKey);
    localStorage.removeItem('user_refunds');
    await fetchOrders(true);
  } catch (err) {
    console.error('cancelOrder error:', err);
    toast('error', 'Không thể hủy đơn hàng!');
  }
};

const refreshData = async () => {
  const cacheKey = `user_orders_page_${page.value}`;
  localStorage.removeItem(cacheKey);
  localStorage.removeItem('user_refunds');
  await Promise.all([fetchOrders(true), fetchRefunds(true)]);
  toast('success', 'Dữ liệu đã được làm mới!');
};

const printOrder = (orderId) => {
  window.open(`/users/print-order/${orderId}`, '_blank');
};

const downloadPDF = async (orderId) => {
  try {
    const token = localStorage.getItem('access_token');
    const res = await axios.get(`${apiBase}/user/orders/${orderId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    const data = res.data.success ? res.data.data : res.data;
    const { default: html2pdf } = await import('html2pdf.js');
    const content = `
      <div style="font-family:sans-serif; font-size:13px;">
        <h2 style="text-align:center">HÓA ĐƠN MUA HÀNG - #ORD${String(data.id).padStart(3, '0')}</h2>
        <p><strong>Khách hàng:</strong> ${data.user.name}</p>
        <p><strong>Email:</strong> ${data.user.email}</p>
        <p><strong>Địa chỉ:</strong> ${data.address.detail}</p>
        <p><strong>SĐT:</strong> ${data.address.phone}</p>
        <hr />
        <table style="width:100%; border-collapse: collapse;" border="1">
          <thead><tr>
            <th style="padding:4px;">Sản phẩm</th>
            <th style="padding:4px;">SL</th>
            <th style="padding:4px;">Đơn giá</th>
            <th style="padding:4px;">Thành tiền</th>
          </tr></thead>
          <tbody>
            ${data.order_items.map(item => `
              <tr>
                <td style="padding:4px;">${item.product.name}${item.variant ? ` (${item.variant.name})` : ''}</td>
                <td style="padding:4px; text-align:center;">${item.quantity}</td>
                <td style="padding:4px; text-align:right;">${formatPrice(item.price)}</td>
                <td style="padding:4px; text-align:right;">${formatPrice(item.total)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
        <p style="text-align:right; font-weight:bold; margin-top:10px;">Tổng cộng: ${formatPrice(data.final_price)}</p>
      </div>
    `;
    const opt = {
      margin: 0.5,
      filename: `HoaDon_${data.user.name.replace(/\s+/g, '_')}_ORD${String(data.id).padStart(3, '0')}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().from(content).set(opt).save();
  } catch (err) {
    console.error('downloadPDF error:', err);
    toast('error', 'Không thể tải hóa đơn PDF!');
  }
};

// Computed
const filteredOrders = computed(() => {
  let result = orders.value;
  if (selectedTab.value !== 'all') {
    result = result.filter(o => o.status === selectedTab.value);
  }
  if (searchTracking.value.trim()) {
    result = result.filter(o => (o.shipping?.tracking_code || '').toLowerCase().includes(searchTracking.value.trim().toLowerCase()));
  }
  if (sortType.value === 'newest') {
    return [...result].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } else if (sortType.value === 'oldest') {
    return [...result].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
  } else if (sortType.value === 'recent') {
    return [...result].sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at));
  }
  return result;
});

const totalPages = computed(() => Math.ceil(filteredOrders.value.length / perPage));
const paginatedOrders = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredOrders.value.slice(start, start + perPage);
});

// Lifecycle and watchers
onMounted(async () => {
  useHead({
    title: `Đơn hàng của tôi | ${user.value?.name || 'Quản lý đơn hàng'}`,
    meta: [
      { name: 'description', content: `Theo dõi và quản lý ${orders.value.length} đơn hàng của bạn, yêu cầu hoàn tiền và tải hóa đơn.` },
      { name: 'robots', content: 'noindex, nofollow' },
      { property: 'og:title', content: `Đơn hàng của ${user.value?.name || 'Khách hàng'}` },
      { property: 'og:description', content: `Xem chi tiết ${orders.value.length} đơn hàng, yêu cầu hoàn tiền và tải hóa đơn PDF.` },
      { property: 'og:type', content: 'website' }
    ]
  });
  await Promise.all([fetchOrders(true), fetchRefunds(true)]);

});

watch(selectedTab, async () => {
  await Promise.all([fetchOrders(true), fetchRefunds(true)]);
});

watch(page, async () => {
  await fetchOrders(true);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>