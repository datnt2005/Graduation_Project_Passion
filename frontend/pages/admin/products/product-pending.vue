<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Approval History Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Xét duyệt sản phẩm</h1>
        <button @click="openApprovalHistory"
          class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Lịch sử xét duyệt
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <button @click="filterStatus = ''; filterRejected = ''; currentPage = 1" :class="[
            'text-blue-600 hover:underline',
            filterStatus === '' && filterRejected === '' ? 'font-semibold' : ''
          ]">
            Tất cả
          </button>
          <span>({{ totalProducts }})</span>
          <button @click="filterStatus = 'instock'; filterRejected = ''; currentPage = 1" :class="[
            'text-blue-600 hover:underline',
            filterStatus === 'instock' && filterRejected === '' ? 'font-semibold' : ''
          ]">
            Còn hàng
          </button>
          <span>({{ inStockProducts }})</span>
          <button @click="filterRejected = 'rejected'; filterStatus = ''; currentPage = 1" :class="[
            'text-blue-600 hover:underline',
            filterRejected === 'rejected' ? 'font-semibold' : ''
          ]">
            Đã từ chối
          </button>
          <span>({{ rejectedProducts }})</span>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
          <select v-model="sortBy"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="newest">Mới nhất</option>
            <option value="oldest">Cũ nhất</option>
          </select>
          <select v-model="filterCategory"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả danh mục</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <select v-model="filterBrand"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả cửa hàng</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.store_name }}
            </option>
          </select>
          <select v-model="filterTag"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả thẻ</option>
            <option v-for="tag in tags" :key="tag.id" :value="tag.id">
              {{ tag.name }}
            </option>
          </select>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm sản phẩm..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Action Bar -->
      <div class="bg-gray-200 px-4 py-3 flex items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Hành động hàng loạt</option>
          <option value="approve">Phê duyệt</option>
          <option value="reject">Từ chối</option>
        </select>
        <button @click="applyBulkAction" :disabled="!selectedAction || selectedProducts.length === 0 || loading" :class="[
          'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
          (!selectedAction || selectedProducts.length === 0 || loading)
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700'
        ]">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedProducts.length }} sản phẩm được chọn / {{ filteredProducts.length }} sản phẩm
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ảnh</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tên sản phẩm</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Danh mục</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thẻ sản phẩm</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Cửa hàng</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in paginatedProducts" :key="product.id" :class="{ 'bg-gray-50': product.id % 2 === 0 }" class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedProducts" :value="product.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <img v-if="getProductImage(product)" :src="`${mediaBase}` + getProductImage(product)" alt="Product Image" class="w-12 h-12 object-cover rounded" />
              <span v-else>–</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700 hover:underline cursor-pointer" @click="editProduct(product.id)">
              {{ truncateText(product.name, 30) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ product.categories?.length ? product.categories.map(c => c.name).join(', ') : '–' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ product.tags?.length ? product.tags.map(t => t.name).join(', ') : '–' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ formatDate(product.created_at) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ product.seller?.store_name || '–' }}
              <span v-if="product.is_admin_added === 1" class="text-xs text-gray-500">(Admin thêm sản phẩm)</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                'bg-green-100 text-green-700 font-semibold': product.admin_status === 'approved',
                'bg-yellow-100 text-yellow-600 font-semibold': product.admin_status === 'pending',
                'bg-red-100 text-red-600 font-semibold': product.admin_status === 'rejected',
                'bg-gray-100 text-gray-500 font-semibold': product.admin_status === 'trash'
              }">
                {{ getStatusLabel(product.admin_status) }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <button @click="openDetail(product)" class="text-blue-600 hover:underline text-sm font-medium">Xem chi tiết</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Hiển thị
              <select v-model="itemsPerPage" @change="currentPage = 1"
                class="ml-2 inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              trên tổng số {{ filteredProducts.length }} sản phẩm
            </p>
          </div>
          <div class="flex justify-end items-center gap-1 py-4 flex-wrap">
            <button
              @click="currentPage = currentPage - 1"
              :disabled="currentPage === 1"
              class="px-3 py-1 border rounded-md text-sm font-medium bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Trước
            </button>
            <button
              v-if="startPage > 1"
              @click="currentPage = 1"
              class="px-3 py-1 border rounded-md text-sm bg-white hover:bg-gray-100"
            >
              1
            </button>
            <span v-if="startPage > 2" class="px-2 text-gray-500">...</span>
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="currentPage = page"
              :class="[
                'px-3 py-1 border rounded-md text-sm font-medium transition-colors duration-150',
                page === currentPage
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-gray-700 hover:bg-gray-100'
              ]"
            >
              {{ page }}
            </button>
            <span v-if="endPage < totalPages - 1" class="px-2 text-gray-500">...</span>
            <button
              v-if="endPage < totalPages"
              @click="currentPage = totalPages"
              class="px-3 py-1 border rounded-md text-sm bg-white hover:bg-gray-100"
            >
              {{ totalPages }}
            </button>
            <button
              @click="currentPage = currentPage + 1"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 border rounded-md text-sm font-medium bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Sau
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Popup -->
      <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
          leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
          <div v-if="showNotification"
            class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6" :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="notificationType === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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

      <!-- Confirmation Dialog -->
      <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
          enter-to-class="opacity-100" leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100" leave-to-class="opacity-0">
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

      <!-- Product Detail Modal -->
      <div v-if="detailModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 md:p-8 overflow-y-auto max-h-screen relative">
          <div class="flex justify-between items-start mb-4 border-b pb-3">
            <div>
              <h3 class="text-2xl font-bold text-[#1564ff]">Chi tiết sản phẩm</h3>
              <p class="text-gray-500 text-sm mt-1">Xem thông tin & duyệt sản phẩm</p>
            </div>
            <div class="flex flex-col items-end gap-2">
              <button @click="editProduct(currentDetail?.id)"
                class="text-sm font-semibold text-blue-600 hover:underline hover:text-blue-800 transition-colors duration-300">
                Xem & Chỉnh sửa
              </button>
              <button @click="closeDetail"
                class="text-gray-400 hover:text-black text-xl transition-colors duration-200 -mt-1">✕</button>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <img :src="`${mediaBase}` + getProductImage(currentDetail)" alt="Ảnh sản phẩm"
                class="w-full rounded border object-contain max-h-[240px]" />
            </div>
            <div class="flex flex-col gap-2">
              <div><strong>Tên sản phẩm:</strong> {{ currentDetail?.name }}</div>
              <div>
                <strong>Mô tả:</strong>
                <div
                  class="prose max-w-none text-sm text-gray-700 max-h-[200px] overflow-y-auto border border-gray-200 rounded p-2 bg-gray-50"
                  v-html="currentDetail?.description"></div>
              </div>
              <div><strong>Giá:</strong> {{ formatCurrency(currentDetail?.price || getDefaultPrice(currentDetail)) }}</div>
              <div><strong>Danh mục:</strong> {{ currentDetail?.categories?.map(c => c.name).join(', ') || '-' }}</div>
              <div><strong>Nhà bán:</strong> {{ currentDetail?.seller?.store_name || '-' }}</div>
              <div><strong>Trạng thái kho:</strong>
                <span class="inline-block ml-1 px-2 py-0.5 rounded-full text-xs"
                  :class="getStockStatus(currentDetail) === 'instock' ? 'bg-green-100 text-green-700' : 'bg-gray-300 text-gray-700'">
                  {{ getStockStatus(currentDetail) === 'instock' ? 'Còn hàng' : 'Hết hàng' }}
                </span>
              </div>
              <div><strong>Ngày tạo:</strong> {{ formatDate(currentDetail?.created_at) }}</div>
              <div><strong>Trạng thái:</strong>
                <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                  'bg-green-100 text-green-700 font-semibold': currentDetail.admin_status === 'approved',
                  'bg-yellow-100 text-yellow-600 font-semibold': currentDetail.admin_status === 'pending',
                  'bg-red-100 text-red-600 font-semibold': currentDetail.admin_status === 'rejected',
                  'bg-gray-100 text-gray-500 font-semibold': currentDetail.admin_status === 'trash'
                }">
                  {{ getStatusLabel(currentDetail.admin_status) }}
                </span>
              </div>
            </div>
          </div>
          <div v-if="currentDetail?.admin_status === 'pending'" class="mt-6 flex gap-3">
            <button @click="approveProduct(currentDetail.id)"
              class="flex-1 py-2 rounded bg-blue-700 hover:bg-blue-900 text-white font-semibold text-sm transition">
              ✅ Duyệt sản phẩm
            </button>
            <button @click="rejectProduct(currentDetail.id)"
              class="flex-1 py-2 rounded bg-red-500 hover:bg-red-700 text-white font-semibold text-sm transition">
              ❌ Từ chối
            </button>
          </div>
        </div>
      </div>

      <!-- Reason Modal -->
      <div v-if="reasonModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">
          <h3 class="text-lg font-semibold mb-3 text-gray-800">
            {{ pendingAction === 'reject' ? 'Từ chối sản phẩm' : 'Duyệt sản phẩm' }}
          </h3>
          <div v-if="pendingAction === 'reject'">
            <label class="text-sm text-gray-600 mb-1 block">Lý do từ chối</label>
            <textarea v-model="reasonText" rows="4"
              class="w-full border rounded p-2 text-sm focus:outline-blue-500 resize-none"
              placeholder="Nhập lý do từ chối..."></textarea>
          </div>
          <p v-else class="text-sm text-gray-700">Bạn chắc chắn muốn <strong>duyệt</strong> sản phẩm này?</p>
          <div class="flex justify-end mt-5 gap-2">
            <button @click="reasonModal = false"
              class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-26 text-sm">Hủy</button>
            <button @click="submitApproval" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">
              {{ pendingAction === 'reject' ? 'Xác nhận từ chối' : 'Xác nhận duyệt' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Approval History Modal -->
      <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
          enter-to-class="opacity-100" leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100" leave-to-class="opacity-0">
          <div v-if="showApprovalHistory" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeApprovalHistory"></div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
              <div
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">Lịch sử xét duyệt sản phẩm</h3>
                  <div class="mt-4">
                    <table class="min-w-full border-collapse border border-gray-300 text-sm">
                      <thead class="bg-white border-b border-gray-300">
                        <tr>
                          <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">ID</th>
                          <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tên sản phẩm</th>
                          <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Quản trị viên</th>
                          <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
                          <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Lý do</th>
                          <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày xét duyệt</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="history in approvalHistory" :key="history.id" :class="{ 'bg-gray-50': history.id % 2 === 0 }" class="border-b border-gray-300">
                          <td class="border border-gray-300 px-3 py-2 text-left">{{ history.id }}</td>
                          <td class="border border-gray-300 px-3 py-2 text-left">
                            {{ truncateText(history.product_name, 30) }}
                          </td>
                          <td class="border border-gray-300 px-3 py-2 text-left">
                            {{ history.admin_name || '–' }}
                          </td>
                          <td class="border border-gray-300 px-3 py-2 text-left">
                            <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                              'bg-green-100 text-green-700': history.status === 'approved',
                              'bg-red-100 text-red-600': history.status === 'rejected',
                            }">
                              {{ history.status === 'approved' ? 'Đã duyệt' : 'Từ chối' }}
                            </span>
                          </td>
                          <td class="border border-gray-300 px-3 py-2 text-left">
                            {{ history.reason || '–' }}
                          </td>
                          <td class="border border-gray-300 px-3 py-2 text-left">
                            {{ formatDate(history.created_at) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <div v-if="!approvalHistory.length" class="text-center text-gray-500 py-4">
                      Không có lịch sử xét duyệt nào.
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="closeApprovalHistory">
                    Đóng
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { secureFetch } from '@/utils/secureFetch';

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const allProducts = ref([]);
const rejectedProductsList = ref([]);
const selectedProducts = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const selectedAction = ref('');
const filterStatus = ref('');
const filterRejected = ref('');
const sortBy = ref('newest');
const filterCategory = ref('');
const filterBrand = ref('');
const filterTag = ref('');
const categories = ref([]);
const brands = ref([]);
const tags = ref([]);
const loading = ref(false);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;
const currentPage = ref(1);
const itemsPerPage = ref(10);
const pendingProductIds = ref([]);
const reasonModal = ref(false);
const reasonText = ref('');
const pendingAction = ref(null);
const detailModal = ref(false);
const currentDetail = ref(null);
const showApprovalHistory = ref(false);
const approvalHistory = ref([]);

// Calculate product counts on frontend
const totalProducts = computed(() => allProducts.value.length);
const inStockProducts = computed(() => allProducts.value.filter(p => getStockStatus(p) === 'instock').length);
const rejectedProducts = computed(() => rejectedProductsList.value.length);

// Fetch all products and rejected products
const fetchAllData = async () => {
  try {
    loading.value = true;
    const nonRejectedData = await secureFetch(`${apiBase}/approvals?per_page=1000000`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }, ['admin']);
    allProducts.value = nonRejectedData.data?.data || nonRejectedData.data || nonRejectedData || [];

    const rejectedData = await secureFetch(`${apiBase}/approvals/rejected?per_page=1000000`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }, ['admin']);
    rejectedProductsList.value = rejectedData.data?.data || rejectedData.data || rejectedData || [];
  } catch (error) {
    console.error('Error fetching data:', error);
    showNotificationMessage(`Có lỗi xảy ra khi tải dữ liệu: ${error.message}`, 'error');
  } finally {
    loading.value = false;
  }
};

// Fetch categories
const fetchCategories = async () => {
  try {
    const response = await secureFetch(`${apiBase}/categories`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }, ['admin']);
    categories.value = response.data?.data || response.data?.categories || [];
  } catch (error) {
    console.error('Error fetching categories:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải danh mục', 'error');
  }
};

// Fetch brands
const fetchBrands = async () => {
  try {
    const response = await secureFetch(`${apiBase}/sellers/verified`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }, ['admin']);
    brands.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Error fetching brands:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải thương hiệu', 'error');
  }
};

// Fetch tags
const fetchTags = async () => {
  try {
    const response = await secureFetch(`${apiBase}/tags`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }, ['admin']);
    tags.value = response.data?.tags || [];
  } catch (error) {
    console.error('Error fetching tags:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải thẻ', 'error');
  }
};

// Get product image
const getProductImage = (product) => {
  if (product?.product_pic?.[0]?.imagePath) return product.product_pic[0].imagePath;
  if (product?.product_variants?.[0]?.thumbnail) return product.product_variants[0].thumbnail;
  return null;
};

// Get stock status
const getStockStatus = (product) => {
  const totalQuantity = product?.product_variants?.reduce((sum, variant) => sum + (variant.quantity || 0), 0) || 0;
  return totalQuantity > 0 ? 'instock' : 'outofstock';
};

// Get status label
const getStatusLabel = (status) => {
  switch (status) {
    case 'approved':
      return 'Đã duyệt';
    case 'pending':
      return 'Chờ duyệt';
    case 'rejected':
      return 'Từ chối';
    case 'trash':
      return 'Đã xóa';
    default:
      return 'Không xác định';
  }
};

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedProducts.value = paginatedProducts.value.map(p => p.id);
  } else {
    selectedProducts.value = [];
  }
};

// Truncate text
const truncateText = (text, maxLength) => {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text;
};

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedProducts.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một sản phẩm', 'error');
    return;
  }

  const validIds = selectedProducts.value.filter(id => !!id);
  if (!validIds.length) {
    showNotificationMessage('Sản phẩm chọn không hợp lệ.', 'error');
    return;
  }

  pendingProductIds.value = validIds;
  pendingAction.value = selectedAction.value;

  if (selectedAction.value === 'reject') {
    reasonText.value = '';
    reasonModal.value = true;
  } else {
    showConfirmationDialog(
      'Xác nhận duyệt sản phẩm',
      `Bạn có chắc chắn muốn duyệt ${validIds.length} sản phẩm đã chọn?`,
      submitBulkApproval
    );
  }
};

// Edit product
const editProduct = (id) => {
  router.push(`/admin/products/edit-product/${id}`);
};

// Format date
const formatDate = (date) => {
  if (!date) return '–';
  return new Date(date).toLocaleDateString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

// Filtered products
const filteredProducts = computed(() => {
  let result = filterRejected.value === 'rejected' ? [...rejectedProductsList.value] : [...allProducts.value];
  if (filterRejected.value === 'rejected') {
    result = result.filter(product => product.admin_status === 'rejected');
  } else {
    result = result.filter(product => product.admin_status !== 'rejected');
  }
  if (filterStatus.value && filterRejected.value !== 'rejected') {
    result = result.filter(product => getStockStatus(product) === filterStatus.value);
  }
  if (filterCategory.value) {
    result = result.filter(product =>
      product.categories?.some(category => category.id === filterCategory.value)
    );
  }
  if (filterBrand.value) {
    result = result.filter(product =>
      product.seller?.id === filterBrand.value
    );
  }
  if (filterTag.value) {
    result = result.filter(product =>
      product.tags?.some(tag => tag.id === filterTag.value)
    );
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(product =>
      product.name?.toLowerCase().includes(query) ||
      product.slug?.toLowerCase().includes(query) ||
      (product.description && product.description.toLowerCase().includes(query))
    );
  }
  if (sortBy.value === 'newest') {
    result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
  } else if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
  }
  return result;
});

// Pagination logic
const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value));

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredProducts.value.slice(start, end);
});

const maxButtons = 5;

const startPage = computed(() => Math.max(1, currentPage.value - Math.floor(maxButtons / 2)));

const endPage = computed(() => Math.min(totalPages.value, startPage.value + maxButtons - 1));

const visiblePages = computed(() => {
  const pages = [];
  for (let i = startPage.value; i <= endPage.value; i++) {
    pages.push(i);
  }
  return pages;
});

// Show notification
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Close confirm dialog
const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

// Handle confirm action
const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};

// Show confirmation dialog
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

// Open product detail
const openDetail = (product) => {
  currentDetail.value = product;
  detailModal.value = true;
};

// Close product detail
const closeDetail = () => {
  detailModal.value = false;
  currentDetail.value = null;
};

// Approve product
const approveProduct = (id) => {
  pendingAction.value = 'approve';
  pendingProductIds.value = [id];
  reasonText.value = '';
  reasonModal.value = true;
};

// Reject product
const rejectProduct = (id) => {
  pendingAction.value = 'reject';
  pendingProductIds.value = [id];
  reasonText.value = '';
  reasonModal.value = true;
};

// Submit bulk approval
const submitBulkApproval = async () => {
  if (pendingAction.value === 'reject' && !reasonText.value.trim()) {
    showNotificationMessage('Vui lòng nhập lý do từ chối.', 'error');
    return;
  }
  try {
    loading.value = true;
    const responses = await Promise.all(pendingProductIds.value.map(id =>
      secureFetch(`${apiBase}/approvals/${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          admin_status: pendingAction.value === 'approve' ? 'approved' : 'rejected',
          reason: reasonText.value
        })
      }, ['admin'])
    ));
    const failed = responses.some(res => !res.success);
    if (failed) {
      showNotificationMessage('Có lỗi xảy ra khi cập nhật trạng thái một số sản phẩm', 'error');
    } else {
      showNotificationMessage(
        pendingAction.value === 'approve'
          ? 'Duyệt các sản phẩm thành công!'
          : 'Từ chối các sản phẩm thành công!',
        'success'
      );
    }
    selectedProducts.value = [];
    selectAll.value = false;
    selectedAction.value = '';
    reasonModal.value = false;
    await fetchAllData();
  } catch (error) {
    console.error('Error processing bulk action:', error);
    showNotificationMessage('Lỗi khi xử lý hành động hàng loạt', 'error');
  } finally {
    loading.value = false;
  }
};

// Submit approval
const submitApproval = async () => {
  if (!pendingProductIds.value.length && currentDetail.value?.id) {
    pendingProductIds.value = [currentDetail.value.id];
  }
  await submitBulkApproval();
  reasonModal.value = false;
  detailModal.value = false;
};

// Format currency
const formatCurrency = (value) => {
  if (!value) return '–';
  return Number(value).toLocaleString('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0 });
};

// Get default price
const getDefaultPrice = (product) => {
  return product?.product_variants?.[0]?.price || 0;
};

// Fetch approval history
const fetchApprovalHistory = async () => {
  try {
    const data = await secureFetch(`${apiBase}/approvals/history`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }, ['admin']);
    if (!data.success) throw new Error(`HTTP error! status: ${data.status}`);
    approvalHistory.value = data.data || [];
    if (!approvalHistory.value.length) {
      showNotificationMessage('Không có lịch sử xét duyệt nào.', 'info');
    }
  } catch (error) {
    console.error('Error fetching approval history:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải lịch sử xét duyệt', 'error');
  }
};

// Open approval history
const openApprovalHistory = async () => {
  await fetchApprovalHistory();
  showApprovalHistory.value = true;
};

// Close approval history
const closeApprovalHistory = () => {
  showApprovalHistory.value = false;
  approvalHistory.value = [];
};

// Watch filterRejected to switch products
watch(filterRejected, () => {
  currentPage.value = 1;
});

// Lifecycle hooks
onMounted(() => {
  fetchAllData();
  fetchCategories();
  fetchBrands();
  fetchTags();
});

onUnmounted(() => {
  // No dropdown to close, but keep for consistency
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

button {
  position: relative;
  overflow: hidden;
}

button::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  pointer-events: none;
  background-image: radial-gradient(circle, #000 10%, transparent 10.01%);
  background-repeat: no-repeat;
  background-position: 50%;
  transform: scale(10, 10);
  opacity: 0;
  transition: transform .5s, opacity 1s;
}

button:active::after {
  transform: scale(0, 0);
  opacity: .2;
  transition: 0s;
}
</style>