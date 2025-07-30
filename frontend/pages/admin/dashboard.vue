<template>
  <!-- Biểu đồ doanh thu chiết khấu admin -->
  <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">📈 Biểu Đồ Doanh Thu Chiết Khấu Admin</h2>
        <p class="text-gray-600">Theo dõi doanh thu chiết khấu theo thời gian</p>
      </div>
      <div class="mt-4 lg:mt-0 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Lọc theo:</label>
          <select
            v-model="chartType"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
          >
            <option value="day">Ngày</option>
            <option value="month">Tháng</option>
            <option value="year">Năm</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Kiểu biểu đồ:</label>
          <select
            v-model="chartTypeMode"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
          >
            <option value="bar">Cột</option>
            <option value="line">Đường</option>
            <option value="pie">Tròn</option>
          </select>
        </div>
      </div>
  </div>

    <div class="h-[400px] min-w-[600px] bg-gradient-to-br from-gray-50 to-white rounded-lg p-4">
      <div v-if="chartLoading" class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <div class="text-gray-500 font-medium">Đang tải biểu đồ...</div>
        </div>
      </div>
      <div v-else-if="chartError" class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="text-red-500 text-lg mb-4">⚠️</div>
          <div class="text-red-500 font-medium mb-4">{{ chartError }}</div>
          <button 
            @click="fetchPayoutChartData(chartType)"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Thử lại
          </button>
        </div>
      </div>
      <component v-else :is="chartComponent" :data="combinedChartData" :options="combinedChartOptions" />
    </div>
  </div>

  <!-- Tổng quan hệ thống -->
  <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">📊 Tổng Quan Hệ Thống</h2>
    
    <div v-if="systemOverviewLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
      <div v-for="i in 5" :key="i" class="bg-gray-200 animate-pulse rounded-xl p-6 h-32"></div>
    </div>

    <div v-else-if="systemOverviewError" class="text-center py-8">
      <div class="text-red-500">{{ systemOverviewError }}</div>
      <button 
        @click="fetchSystemOverview"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Thử lại
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
      <!-- Thống kê người dùng -->
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 rounded-lg shadow-md transform hover:scale-102 transition-transform duration-200">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-xs opacity-90 font-medium">Tổng người dùng</p>
            <p class="text-2xl font-bold mt-1">{{ systemOverview?.users?.total || 0 }}</p>
            <p class="text-xs opacity-75">{{ systemOverview?.users?.active || 0 }} đang hoạt động</p>
          </div>
          <div class="text-2xl opacity-80 ml-2">👥</div>
        </div>
      </div>

      <!-- Thống kê đơn hàng -->
      <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 rounded-lg shadow-md transform hover:scale-102 transition-transform duration-200">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-xs opacity-90 font-medium">Tổng đơn hàng</p>
            <p class="text-2xl font-bold mt-1">{{ systemOverview?.orders?.total || 0 }}</p>
            <p class="text-xs opacity-75">{{ systemOverview?.orders?.delivered || 0 }} đã giao</p>
          </div>
          <div class="text-2xl opacity-80 ml-2">📦</div>
        </div>
      </div>

      <!-- Thống kê doanh thu -->
      <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 rounded-lg shadow-md transform hover:scale-102 transition-transform duration-200">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-xs opacity-90 font-medium">Tổng doanh thu</p>
            <p class="text-xl font-bold mt-1">{{ formatCurrency(systemOverview?.orders?.total_revenue || 0) }}</p>
            <p class="text-xs opacity-75">{{ formatCurrency(systemOverview?.orders?.total_discount || 0) }} giảm giá</p>
          </div>
          <div class="text-2xl opacity-80 ml-2">💰</div>
        </div>
      </div>

      <!-- Thống kê seller -->
      <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-4 rounded-lg shadow-md transform hover:scale-102 transition-transform duration-200">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-xs opacity-90 font-medium">Tổng người bán hàng</p>
            <p class="text-2xl font-bold mt-1">{{ systemOverview?.sellers?.total || 0 }}</p>
            <p class="text-xs opacity-75">{{ systemOverview?.sellers?.verified || 0 }} đã xác thực</p>
          </div>
          <div class="text-2xl opacity-80 ml-2">🏪</div>
        </div>
      </div>

      <!-- Thống kê chiết khấu admin -->
      <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-4 rounded-lg shadow-md transform hover:scale-102 transition-transform duration-200">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-xs opacity-90 font-medium">Chiết khấu admin</p>
            <p class="text-xl font-bold mt-1">{{ formatNumber(adminCommission) }} đ</p>
            <p class="text-xs opacity-75">Tổng thu nhập</p>
          </div>
          <div class="text-2xl opacity-80 ml-2">💳</div>
        </div>
      </div>
    </div>

    <!-- Chi tiết thống kê -->
    <div v-if="systemOverviewLoading" class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="bg-gray-200 animate-pulse rounded-xl p-6 h-48"></div>
    </div>
    
    <div v-else class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Thống kê đơn hàng chi tiết -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl shadow-md border border-gray-200">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
          <span class="text-xl">📋</span>
          Trạng thái đơn hàng
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Chờ xử lý:</span>
            <span class="font-bold text-yellow-600">{{ systemOverview?.orders?.pending || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Đang xử lý:</span>
            <span class="font-bold text-blue-600">{{ systemOverview?.orders?.processing || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Đang giao:</span>
            <span class="font-bold text-purple-600">{{ systemOverview?.orders?.shipped || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Đã giao:</span>
            <span class="font-bold text-green-600">{{ systemOverview?.orders?.delivered || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Đã hủy:</span>
            <span class="font-bold text-red-600">{{ systemOverview?.orders?.cancelled || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Thống kê người dùng chi tiết -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl shadow-md border border-gray-200">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
          <span class="text-xl">👤</span>
          Phân loại người dùng
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Khách hàng:</span>
            <span class="font-bold text-blue-600">{{ systemOverview?.users?.customers || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Người bán:</span>
            <span class="font-bold text-green-600">{{ systemOverview?.users?.sellers || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Quản trị viên:</span>
            <span class="font-bold text-purple-600">{{ systemOverview?.users?.admins || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Đã xác thực:</span>
            <span class="font-bold text-green-600">{{ systemOverview?.users?.verified || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Thống kê sản phẩm -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl shadow-md border border-gray-200">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
          <span class="text-xl">🛍️</span>
          Sản phẩm
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Tổng sản phẩm:</span>
            <span class="font-bold text-blue-600">{{ systemOverview?.products?.total || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Đang bán:</span>
            <span class="font-bold text-green-600">{{ systemOverview?.products?.active || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Chờ duyệt:</span>
            <span class="font-bold text-yellow-600">{{ systemOverview?.products?.pending || 0 }}</span>
          </div>
          <div class="flex justify-between items-center p-2 bg-white rounded-lg">
            <span class="text-sm text-gray-600">Bị từ chối:</span>
            <span class="font-bold text-red-600">{{ systemOverview?.products?.rejected || 0 }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Danh sách đơn hàng gần đây -->
  <div class="bg-white p-6 rounded shadow mb-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-800">📦 Đơn Hàng Gần Đây</h2>
      <button 
        @click="showAllOrders = !showAllOrders"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
      >
        {{ showAllOrders ? 'Thu gọn' : 'Xem tất cả' }}
      </button>
    </div>

    <div v-if="ordersLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <div class="mt-2 text-gray-500">Đang tải...</div>
    </div>

    <div v-else-if="ordersError" class="text-center py-8">
      <div class="text-red-500">{{ ordersError }}</div>
      <button 
        @click="fetchOrders"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Thử lại
      </button>
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="w-full table-auto">
          <thead>
                         <tr class="bg-gray-50">
               <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Mã vận đơn</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Khách hàng</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Tổng tiền</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Trạng thái</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="order in displayOrders" 
              :key="order.id" 
              class="border-b hover:bg-gray-50 transition"
            >
                             <td class="px-4 py-3">
                 <a
            v-if="order.shipping?.tracking_code"
            :href="`/admin/orders/list-order?tracking_code=${order.shipping.tracking_code}`"
            @click.prevent="goToOrderWithTracking(order.shipping.tracking_code)"
            class="font-semibold text-blue-600 underline hover:text-orange-600 cursor-pointer transition"
          >
            {{ order.shipping.tracking_code }}
          </a>
          <span v-else class="text-gray-400">Chưa có</span>
               </td>
              <td class="px-4 py-3">
                <div>
                  <div class="font-medium">{{ order.user?.name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">{{ order.user?.email || 'N/A' }}</div>
                </div>
              </td>
              <td class="px-4 py-3 font-semibold text-green-600">
                {{ formatCurrency(order.final_price) }}
              </td>
              <td class="px-4 py-3">
                <span :class="getOrderStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ getOrderStatusLabel(order.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">
                {{ formatDate(order.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Phân trang cho đơn hàng -->
      <div v-if="showAllOrders && ordersMeta.last_page > 1" class="mt-4 flex justify-center gap-2">
        <button
          v-for="page in ordersMeta.last_page"
          :key="page"
          @click="changeOrdersPage(page)"
          :class="[
            'px-3 py-1 rounded text-sm',
            page === ordersMeta.current_page
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>

  <!-- Danh sách người dùng đang hoạt động -->
  <div class="bg-white p-6 rounded shadow mb-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-800">👥 Người Dùng Đang Hoạt Động</h2>
      <button 
        @click="showAllUsers = !showAllUsers"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
      >
        {{ showAllUsers ? 'Thu gọn' : 'Xem tất cả' }}
      </button>
    </div>

    <div v-if="usersLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <div class="mt-2 text-gray-500">Đang tải...</div>
    </div>

    <div v-else-if="usersError" class="text-center py-8">
      <div class="text-red-500">{{ usersError }}</div>
      <button 
        @click="fetchUsers"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Thử lại
      </button>
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Tên</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Email</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Vai trò</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Đơn hàng</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Tổng chi tiêu</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Ngày tham gia</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="user in displayUsers" 
              :key="user.id" 
              class="border-b hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-medium text-gray-600">
                      {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </span>
                  </div>
                  <div>
                    <div class="font-medium">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.phone || 'Chưa có số điện thoại' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">{{ user.email || 'Chưa có email' }}</td>
              <td class="px-4 py-3">
                <span :class="getRoleClass(user.role)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ getRoleLabel(user.role) }}
                </span>
                <div v-if="user.seller" class="text-xs text-gray-500 mt-1">
                  {{ user.seller.store_name || 'Chưa có tên shop' }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-medium">{{ user.total_orders }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-green-600">
                {{ formatCurrency(user.total_spent) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">
                {{ formatDate(user.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Phân trang cho người dùng -->
      <div v-if="showAllUsers && usersMeta.last_page > 1" class="mt-4 flex justify-center gap-2">
        <button
          v-for="page in usersMeta.last_page"
          :key="page"
          @click="changeUsersPage(page)"
          :class="[
            'px-3 py-1 rounded text-sm',
            page === usersMeta.current_page
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>

  

  <!-- Bộ lọc shop - Giao diện hiện đại -->
  <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">🏪 Quản Lý Shop</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
          <span class="w-5 h-5 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </span>
          Chọn shop:
        </label>
        <select 
          v-model="selectedSellerId" 
          @change="onSellerChange" 
          class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
        >
          <option value="">Tất cả shop</option>
          <option v-for="seller in filteredSellers" :key="seller.id" :value="seller.id">
            {{ seller.store_name || seller.user?.name || 'Shop #' + seller.id }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
          <span class="w-5 h-5 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          Tìm kiếm:
        </label>
        <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tên shop, email..."
            class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 pl-10 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors bg-white"
          @input="debounceSearch"
        />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
          <span class="w-5 h-5 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
            </svg>
          </span>
          Sắp xếp theo:
        </label>
        <select
          v-model="sortBy"
          class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors bg-white"
          @change="fetchSellers"
        >
          <option value="">Mặc định</option>
          <option value="revenue_desc">Doanh thu cao → thấp</option>
          <option value="revenue_asc">Doanh thu thấp → cao</option>
          <option value="orders_desc">Đơn hàng nhiều → ít</option>
          <option value="orders_asc">Đơn hàng ít → nhiều</option>
        </select>
      </div>
    </div>

    <!-- Danh sách shop -->
    <div class="mt-6 border-t pt-4">
      <div v-if="sellersLoading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <div class="mt-2 text-gray-500">Đang tải...</div>
      </div>

      <div v-else-if="sellersError" class="text-center py-8">
        <div class="text-red-500">{{ sellersError }}</div>
        <button 
          @click="fetchSellers"
          class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Thử lại
        </button>
      </div>

      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="seller in paginatedSellers"
            :key="seller.id"
            :class="[
              'p-4 rounded-lg border transition cursor-pointer',
              selectedSellerId === seller.id
                ? 'border-blue-500 bg-blue-50 shadow-md'
                : 'border-gray-200 hover:border-blue-300 hover:shadow'
            ]"
            @click="selectSeller(seller.id)"
          >
            <div class="font-semibold text-blue-600 mb-1">{{ seller.store_name }}</div>
            <div class="text-sm text-gray-500 mb-3">{{ seller.user.email }}</div>
            <div class="flex gap-8">
              <div>
                <div class="text-gray-500 text-sm">Đơn hàng</div>
                <div class="font-semibold">{{ seller.total_orders }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-sm">Doanh thu</div>
                <div class="font-semibold text-green-600">{{ formatCurrency(seller.total_revenue) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="totalShopPages > 1" class="flex justify-center mt-4 gap-2">
          <button
            :disabled="currentShopPage === 1"
            @click="changeShopPage(currentShopPage - 1)"
            class="px-3 py-1 rounded border bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
          >
            Trước
          </button>
          <button
            v-for="page in totalShopPages"
            :key="page"
            @click="changeShopPage(page)"
            :class="[
              'px-3 py-1 rounded border',
              page === currentShopPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'
            ]"
          >
            {{ page }}
          </button>
          <button
            :disabled="currentShopPage === totalShopPages"
            @click="changeShopPage(currentShopPage + 1)"
            class="px-3 py-1 rounded border bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
          >
            Sau
          </button>
        </div>

        <div v-if="!sellers.length" class="text-center py-8">
          <div class="text-gray-500">Không tìm thấy shop nào</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Thống kê doanh thu shop -->
  <div v-if="selectedSellerId" class="bg-white p-4 rounded shadow mt-6 w-full max-w-4xl mx-auto">
    <h3 class="text-lg font-semibold text-blue-600 mb-4">Thống kê doanh thu shop</h3>
    
    <div v-if="shopStatsLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <div class="mt-2 text-gray-500">Đang tải...</div>
    </div>

    <div v-else-if="shopStatsError" class="text-center py-8">
      <div class="text-red-500">{{ shopStatsError }}</div>
      <button 
        @click="fetchShopStats"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Thử lại
      </button>
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-6">
      <!-- Cột 1 -->
      <div>
        <div class="text-gray-600 mb-1">Tổng đơn hàng</div>
        <div class="text-2xl font-bold">{{ shopStats?.total_orders || 0 }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Đơn đã bán</div>
        <div class="text-2xl font-bold">{{ shopStats?.sold_orders || 0 }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Tổng doanh thu</div>
        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(shopStats?.total_revenue || 0) }}</div>
      </div>

      <!-- Cột 2 -->
      <div>
        <div class="text-gray-600 mb-1">Tổng vốn đã bán</div>
        <div class="text-2xl font-bold">{{ formatCurrency(shopStats?.total_cost || 0) }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Tổng lợi nhuận</div>
        <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(shopStats?.total_profit || 0) }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Tổng lỗ</div>
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(shopStats?.total_loss || 0) }}</div>
      </div>
    </div>
  </div>



  <!-- Bảng danh sách các đơn hàng đã được thanh toán (payout completed) -->
  <div class="bg-white p-6 rounded shadow mb-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-800">💸 Đơn Hàng Đã Thanh Toán Gần Đây</h2>
      <button 
        @click="showAllPayouts = !showAllPayouts"
        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
      >
        {{ showAllPayouts ? 'Thu gọn' : 'Xem tất cả' }}
      </button>
      </div>

    <div v-if="payoutListLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
      <div class="mt-2 text-gray-500">Đang tải...</div>
    </div>

    <div v-else-if="payoutListError" class="text-center py-8">
      <div class="text-red-500">{{ payoutListError }}</div>
      <button 
        @click="fetchPayoutList"
        class="mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
      >
        Thử lại
      </button>
    </div>

    <div v-else-if="!payoutList.length" class="text-center py-8">
      <div class="text-gray-500">Không có đơn hàng đã thanh toán</div>
  </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="w-full table-auto">
        <thead>
            <tr class="bg-gray-50">
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">MÃ VẬN ĐƠN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">NGƯỜI BÁN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">SỐ TIỀN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">NGÀY CHUYỂN KHOẢN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">GHI CHÚ</th>
          </tr>
        </thead>
        <tbody>
            <tr 
              v-for="item in displayPayouts" 
              :key="item.id" 
              class="border-b hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3">
                <a
                  v-if="getTrackingCode(item) && getTrackingCode(item) !== '-'"
                  :href="`/admin/orders/list-order?tracking_code=${getTrackingCode(item)}`"
                @click.prevent="goToOrderWithTracking(getTrackingCode(item))"
                  class="font-semibold text-blue-600 underline hover:text-orange-600 cursor-pointer transition"
              >
                {{ getTrackingCode(item) }}
              </a>
                <span v-else class="text-gray-400">Chưa có</span>
            </td>
              <td class="px-4 py-3">
                <div class="font-medium">{{ item.seller?.store_name || item.seller?.user?.name || 'N/A' }}</div>
            </td>
              <td class="px-4 py-3 font-semibold text-green-600">
                {{ formatCurrency(item.amount) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">
                {{ formatDate(item.transferred_at) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate" :title="item.note || '-'">
                {{ item.note || '-' }}
              </td>
          </tr>
        </tbody>
      </table>
        </div>

      <!-- Phân trang cho payout -->
      <div v-if="showAllPayouts && payoutMeta.last_page > 1" class="mt-4 flex justify-center gap-2">
          <button
            v-for="page in payoutMeta.last_page"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 rounded text-sm',
              page === payoutMeta.current_page
              ? 'bg-green-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >
            {{ page }}
          </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { Bar, Line, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale } from 'chart.js'
import { secureFetch } from '@/utils/secureFetch' 
import { useRouter } from 'vue-router'
ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl
const router = useRouter()

// Shop selector state
const sellers = ref([])
const sellersLoading = ref(false)
const sellersError = ref('')
const selectedSellerId = ref('')
const searchQuery = ref('')
const sortBy = ref('')
const selectedSellerStats = ref(null)

// Computed
const filteredSellers = computed(() => {
  if (!searchQuery.value) return sellers.value;
  const search = searchQuery.value.toLowerCase();
  return sellers.value.filter(seller => {
    return seller.store_name?.toLowerCase().includes(search) ||
           seller.user?.email?.toLowerCase().includes(search) ||
           seller.user?.name?.toLowerCase().includes(search);
  });
});

// Debounce search
let searchTimeout;
let chartTimeout;
function debounceSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchSellers();
  }, 300);
}

function debounceChartUpdate() {
  clearTimeout(chartTimeout);
  chartTimeout = setTimeout(() => {
    fetchPayoutChartData(chartType.value);
  }, 500);
}

async function fetchSellers() {
  sellersLoading.value = true;
  sellersError.value = '';
  try {
    let url = `${apiBaseUrl}/admin/sellers?`;
    if (searchQuery.value) {
      url += `search=${encodeURIComponent(searchQuery.value)}&`;
    }
    if (sortBy.value) {
      url += `sort=${sortBy.value}&`;
    }
    const data = await secureFetch(url, {}, ['admin']);
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được danh sách seller');
    }
    sellers.value = data.data;
  } catch (error) {
    console.error('Error fetching sellers:', error);
    sellersError.value = error.message || 'Không thể tải danh sách seller!';
    sellers.value = [];
  } finally {
    sellersLoading.value = false;
  }
}

function selectSeller(id) {
  selectedSellerId.value = id === selectedSellerId.value ? '' : id;
  if (selectedSellerId.value) {
    selectedSellerStats.value = sellers.value.find(s => s.id === selectedSellerId.value);
  } else {
    selectedSellerStats.value = null;
  }
  onSellerChange();
}

const shopStats = ref(null)
const shopStatsLoading = ref(false)
const shopStatsError = ref('')

async function fetchShopStats() {
  if (!selectedSellerId.value) {
    shopStats.value = null
    return
  }
  shopStatsLoading.value = true
  shopStatsError.value = ''
  try {
    const token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBaseUrl}/dashboard/stats?seller_id=${selectedSellerId.value}`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    })
    if (!res.ok) throw new Error('Không lấy được thống kê shop')
    const data = await res.json()
    shopStats.value = data
  } catch (e) {
    shopStatsError.value = 'Không thể tải thống kê shop!'
    shopStats.value = null
  } finally {
    shopStatsLoading.value = false
  }
}

function onSellerChange() {
  fetchAdminCommission()
  fetchPayoutChartData(chartType.value)
  fetchPayoutList()
  fetchShopStats()
}

// Tổng chiết khấu admin
const adminCommission = ref(0)
const adminCommissionLoading = ref(false)
const adminCommissionError = ref('')

// Dữ liệu payout completed để vẽ biểu đồ chiết khấu
const payoutChartData = ref([])
const chartType = ref('month')
const chartLoading = ref(false)
const chartError = ref('')

const payoutList = ref([])
const payoutListLoading = ref(false)
const payoutListError = ref('')
const payoutMeta = ref({})
const currentPage = ref(1)
const recentPayouts = computed(() => {
  return payoutList.value
    .filter(p => p.status === 'completed')
    .sort((a, b) => parseVNDate(b.transferred_at || 0) - parseVNDate(a.transferred_at || 0))
    .slice(0, 10)
})

// Map order_id -> tracking_code từ danh sách orders
const orderMap = ref({})
async function fetchOrderMap() {
  try {
    const token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBaseUrl}/orders?per_page=1000`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    })
    const data = await res.json()
    if (Array.isArray(data.data)) {
      const map = {}
      data.data.forEach(o => {
        map[o.id] = o.shipping && o.shipping.tracking_code ? o.shipping.tracking_code : '-'
      })
      orderMap.value = map
    }
  } catch {}
}

async function fetchAdminCommission() {
  adminCommissionLoading.value = true
  adminCommissionError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/payouts/stats`
    if (selectedSellerId.value) url += `?seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được thống kê payout')
    }
    adminCommission.value = data.data.total_commission
  } catch (error) {
    console.error('Error fetching admin commission:', error)
    adminCommissionError.value = error.message || 'Không thể tải dữ liệu chiết khấu!'
  } finally {
    adminCommissionLoading.value = false
  }
}

async function fetchPayoutChartData(type = 'month') {
  chartLoading.value = true
  chartError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/payouts/chart?type=${type}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu payout')
    }
    payoutChartData.value = data.data
  } catch (error) {
    console.error('Error fetching payout chart data:', error)
    chartError.value = error.message || 'Không thể tải dữ liệu biểu đồ chiết khấu!'
    payoutChartData.value = { labels: [], data: [] }
  } finally {
    chartLoading.value = false
  }
}

function parseVNDate(dateStr) {
  // Hỗ trợ dd/mm/yyyy hh:mm:ss
  if (!dateStr) return null;
  if (/^\d{2}\/\d{2}\/\d{4}/.test(dateStr)) {
    const [d, m, yAndTime] = dateStr.split('/');
    let y = '', time = '';
    if (yAndTime) [y, time] = yAndTime.trim().split(' ');
    const [h = '00', min = '00', s = '00'] = (time || '').split(':');
    return new Date(`${y}-${m}-${d}T${h}:${min}:${s}`);
  }
  // ISO hoặc yyyy-mm-dd
  return new Date(dateStr);
}

function formatDate(dateStr) {
  const date = parseVNDate(dateStr);
  if (!date || isNaN(date.getTime())) return '-';
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getTrackingCode(payout) {
  // Ưu tiên lấy từ orderMap nếu có
  if (payout.order_id && orderMap.value[payout.order_id]) return orderMap.value[payout.order_id]
  if (payout.tracking_code) return payout.tracking_code
  if (payout.order && payout.order.shipping && payout.order.shipping.tracking_code) return payout.order.shipping.tracking_code
  return '-'
}

async function fetchPayoutList(page = 1) {
  payoutListLoading.value = true
  payoutListError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/payouts/approved?page=${page}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu payout')
    }
    payoutList.value = data.data
    payoutMeta.value = data.meta
  } catch (error) {
    console.error('Error fetching payout list:', error)
    payoutListError.value = error.message || 'Không thể tải danh sách payout!'
    payoutList.value = []
    payoutMeta.value = {}
  } finally {
    payoutListLoading.value = false
  }
}

function changePage(page) {
  if (page !== currentPage.value) {
    currentPage.value = page
    fetchPayoutList(page)
  }
}

const shopsPerPage = 4
const currentShopPage = ref(1)
const totalShopPages = computed(() => Math.ceil(filteredSellers.value.length / shopsPerPage))
const paginatedSellers = computed(() => {
  const start = (currentShopPage.value - 1) * shopsPerPage
  return filteredSellers.value.slice(start, start + shopsPerPage)
})
function changeShopPage(page) {
  if (page >= 1 && page <= totalShopPages.value) {
    currentShopPage.value = page
  }
}

// Tổng quan hệ thống
const systemOverview = ref(null)
const systemOverviewLoading = ref(true) // Bắt đầu với loading = true
const systemOverviewError = ref('')

async function fetchSystemOverview() {
  systemOverviewLoading.value = true
  systemOverviewError.value = ''
  try {
    const data = await secureFetch(`${apiBaseUrl}/dashboard/system-overview`, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được tổng quan hệ thống')
    }
    systemOverview.value = data.data
  } catch (error) {
    console.error('Error fetching system overview:', error)
    systemOverviewError.value = error.message || 'Không thể tải tổng quan hệ thống!'
    systemOverview.value = null
  } finally {
    systemOverviewLoading.value = false
  }
}

// Danh sách đơn hàng gần đây
const orders = ref([])
const ordersLoading = ref(true) // Bắt đầu với loading = true
const ordersError = ref('')
const ordersMeta = ref({})
const showAllOrders = ref(false)
const currentOrdersPage = ref(1)

async function fetchOrders(page = 1) {
  ordersLoading.value = true
  ordersError.value = ''
  try {
    let url = `${apiBaseUrl}/dashboard/orders-stats?page=${page}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    if (showAllOrders.value) url += `&per_page=15` // Xem tất cả đơn hàng
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu đơn hàng')
    }
    orders.value = data.data
    ordersMeta.value = data.meta
  } catch (error) {
    console.error('Error fetching orders:', error)
    ordersError.value = error.message || 'Không thể tải danh sách đơn hàng!'
    orders.value = []
    ordersMeta.value = {}
  } finally {
    ordersLoading.value = false
  }
}

const displayOrders = computed(() => {
  if (showAllOrders.value) {
    return orders.value
  }
  return orders.value.slice(0, 5) // Chỉ hiển thị 5 đơn hàng gần nhất
})

function changeOrdersPage(page) {
  if (page >= 1 && page <= ordersMeta.value.last_page) {
    currentOrdersPage.value = page
    fetchOrders(page)
  }
}

// Danh sách người dùng đang hoạt động
const users = ref([])
const usersLoading = ref(true) // Bắt đầu với loading = true
const usersError = ref('')
const usersMeta = ref({})
const showAllUsers = ref(false)
const currentUsersPage = ref(1)
const showAllPayouts = ref(false)

async function fetchUsers(page = 1) {
  usersLoading.value = true
  usersError.value = ''
  try {
    let url = `${apiBaseUrl}/dashboard/users-stats?page=${page}`
    if (showAllUsers.value) url += `&per_page=15` // Xem tất cả người dùng
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu người dùng')
    }
    users.value = data.data
    usersMeta.value = data.meta
  } catch (error) {
    console.error('Error fetching users:', error)
    usersError.value = error.message || 'Không thể tải danh sách người dùng!'
    users.value = []
    usersMeta.value = {}
  } finally {
    usersLoading.value = false
  }
}

const displayUsers = computed(() => {
  if (showAllUsers.value) {
    return users.value
  }
  return users.value.slice(0, 5) // Chỉ hiển thị 5 người dùng gần nhất
})

const displayPayouts = computed(() => {
  if (showAllPayouts.value) {
    return payoutList.value
  }
  return payoutList.value.slice(0, 5) // Chỉ hiển thị 5 payout gần nhất
})

function changeUsersPage(page) {
  if (page >= 1 && page <= usersMeta.value.last_page) {
    currentUsersPage.value = page
    fetchUsers(page)
  }
}

onMounted(async () => {
  // Load dữ liệu theo thứ tự ưu tiên
  try {
    // 1. Load dữ liệu tổng quan trước (quan trọng nhất)
    await Promise.all([
      fetchSystemOverview(),
  fetchAdminCommission()
    ])
    
    // 2. Load dữ liệu biểu đồ và danh sách
    await Promise.all([
      fetchPayoutChartData(chartType.value),
      fetchPayoutList(),
      fetchOrders(),
      fetchUsers()
    ])
    
    // 3. Load dữ liệu shop (ít quan trọng hơn)
    await Promise.all([
      fetchSellers(),
  fetchOrderMap()
    ])
    
    // 4. Load shop stats nếu có seller được chọn
    if (selectedSellerId.value) {
  fetchShopStats()
    }
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
})

const chartTypeMode = ref('bar')
const chartComponent = computed(() => {
  if (chartTypeMode.value === 'bar') return Bar
  if (chartTypeMode.value === 'line') return Line
  if (chartTypeMode.value === 'pie') return Pie
  return Bar
})

const combinedChartData = computed(() => {
  return {
    labels: payoutChartData.value?.labels || [],
    datasets: [
      {
        label: 'Doanh thu chiết khấu admin',
        data: payoutChartData.value?.data || [],
        backgroundColor: chartTypeMode.value === 'pie' 
          ? ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe', '#eff6ff', '#1e40af', '#1d4ed8']
          : chartTypeMode.value === 'bar'
          ? 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)'
          : '#3b82f6',
        borderColor: chartTypeMode.value === 'line' ? '#3b82f6' : undefined,
        borderWidth: chartTypeMode.value === 'line' ? 3 : undefined,
        borderRadius: chartTypeMode.value === 'bar' ? 8 : undefined,
        barThickness: chartTypeMode.value === 'bar' ? 40 : undefined,
        fill: chartTypeMode.value === 'line' ? {
          target: 'origin',
          above: 'rgba(59, 130, 246, 0.1)'
        } : undefined,
        tension: chartTypeMode.value === 'line' ? 0.4 : undefined,
      }
    ]
  }
})

const combinedChartOptions = computed(() => {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: chartTypeMode.value === 'pie' ? 'right' : 'top',
        labels: { 
          padding: 20,
          font: {
            size: 12,
            weight: 'bold'
          },
          usePointStyle: true
        }
      },
      tooltip: {
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        titleColor: 'white',
        bodyColor: 'white',
        borderColor: '#3b82f6',
        borderWidth: 1,
        cornerRadius: 8,
        callbacks: {
          label: (context) => {
            const value = context.parsed.y || context.parsed;
            return `${context.dataset.label}: ${formatCurrency(value)}`;
          }
        }
      },
      title: {
        display: false
      }
    },
    scales: chartTypeMode.value === 'pie' ? undefined : {
      y: {
        beginAtZero: true,
        grid: {
          color: 'rgba(0, 0, 0, 0.1)',
          drawBorder: false
        },
        ticks: {
          callback: (value) => formatCurrency(value),
          font: {
            size: 11
          },
          padding: 8
        },
        title: {
          display: true,
          text: 'VND',
          font: {
            size: 12,
            weight: 'bold'
          }
        }
      },
      x: {
        grid: {
          color: 'rgba(0, 0, 0, 0.1)',
          drawBorder: false
        },
        ticks: {
          font: {
            size: 11
          },
          padding: 8
        },
        title: {
          display: true,
          text: chartType.value === 'day' ? 'Ngày' : chartType.value === 'month' ? 'Tháng' : 'Năm',
          font: {
            size: 12,
            weight: 'bold'
          }
        }
      }
    },
    elements: {
      point: {
        radius: 6,
        hoverRadius: 8,
        backgroundColor: '#3b82f6',
        borderColor: 'white',
        borderWidth: 2
      },
      line: {
        tension: 0.4
      }
    }
  }
})

// Format tiền tệ
function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value);
}

// Watch chartType để load lại dữ liệu khi thay đổi
watch(chartType, (val) => {
  debounceChartUpdate();
});

// Watch chartTypeMode để cập nhật lại biểu đồ
watch(chartTypeMode, () => {
  // Chart.js sẽ tự động cập nhật khi data hoặc options thay đổi
});



function formatNumber(val) {
  if (typeof val === 'number') return val.toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  if (!isNaN(val) && val !== null && val !== undefined && val !== '') return Number(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  return val || '0'
}

function goToOrderWithTracking(trackingCode) {
  if (!trackingCode || trackingCode === '-') return;
  router.push({ path: '/admin/orders/list-order', query: { tracking_code: trackingCode } })
}

function payoutStatusClass(status) {
  if (status === 'completed') return 'text-green-600 font-bold';
  if (status === 'pending') return 'text-yellow-600 font-bold';
  if (status === 'rejected') return 'text-red-600 font-bold';
  return '';
}

function payoutStatusLabel(status) {
  if (status === 'completed') return 'Đã chuyển khoản';
  if (status === 'pending') return 'Chờ duyệt';
  if (status === 'rejected') return 'Từ chối';
  return status;
}

function getOrderStatusClass(status) {
  if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
  if (status === 'processing') return 'bg-blue-100 text-blue-800';
  if (status === 'shipped') return 'bg-purple-100 text-purple-800';
  if (status === 'delivered') return 'bg-green-100 text-green-800';
  if (status === 'cancelled') return 'bg-red-100 text-red-800';
  return 'bg-gray-100 text-gray-800';
}

function getOrderStatusLabel(status) {
  if (status === 'pending') return 'Chờ xử lý';
  if (status === 'processing') return 'Đang xử lý';
  if (status === 'shipped') return 'Đang giao';
  if (status === 'delivered') return 'Đã giao';
  if (status === 'cancelled') return 'Đã hủy';
  return status;
}

function getRoleClass(role) {
  if (role === 'admin') return 'bg-purple-100 text-purple-800';
  if (role === 'seller') return 'bg-green-100 text-green-800';
  if (role === 'user') return 'bg-blue-100 text-blue-800';
  return 'bg-gray-100 text-gray-800';
}

function getRoleLabel(role) {
  if (role === 'admin') return 'Quản trị viên';
  if (role === 'seller') return 'Người bán';
  if (role === 'user') return 'Khách hàng';
  return role;
}

definePageMeta({
  layout: 'default-admin'
})
</script>

<style scoped>
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