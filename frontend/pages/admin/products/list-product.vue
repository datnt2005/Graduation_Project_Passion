<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý sản phẩm</h1>
        <button @click="router.push('/admin/products/create-product')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Thêm sản phẩm
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <button @click="filterStatus = ''; filterTrash = ''; fetchProducts()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === '' && filterTrash === '' ? 'font-semibold' : ''
          ]">
            Tất cả
          </button>
          <span>({{ totalProducts }})</span>
          <button @click="filterStatus = 'instock'; filterTrash = ''; fetchProducts()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === 'instock' && filterTrash === '' ? 'font-semibold' : ''
          ]">
            Còn hàng
          </button>
          <span>({{ inStockProducts }})</span>
          <button @click="filterTrash = 'trash'; filterStatus = ''; fetchProducts()" :class="[
            'text-blue-600 hover:underline',
            filterTrash === 'trash' ? 'font-semibold' : ''
          ]">
            Thùng rác
          </button>
          <span>({{ trashProducts }})</span>
          <button @click="openApprovalHistory" :class="[
            'text-blue-600 hover:underline',
          ]">
            Lịch sử duyệt
          </button>

        </div>
        <div class="flex flex-wrap gap-2 items-center">
          <!-- Sort by Date -->
          <select v-model="sortBy"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="newest">Mới nhất</option>
            <option value="oldest">Cũ nhất</option>
          </select>
          <!-- Category Filter -->
          <select v-model="filterCategory"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả danh mục</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <!-- Brand Filter -->
          <select v-model="filterBrand"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả cửa hàng</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.store_name }}
            </option>
          </select>
          <!-- Tag Filter -->
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
      <div
        class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Hành động hàng loạt</option>
          <option value="active" v-if="filterTrash !== 'trash'">Kích hoạt</option>
          <option value="inactive" v-if="filterTrash !== 'trash'">Vô hiệu hóa</option>
          <option value="trash" v-if="filterTrash !== 'trash'">Thêm vào thùng rác</option>
          <option value="restore" v-if="filterTrash === 'trash'">Khôi phục</option>
          <option value="delete" v-if="filterTrash === 'trash'">Xóa</option>
        </select>
        <button @click="applyBulkAction" :disabled="!selectedAction || selectedProducts.length === 0 || loading" :class="[
          'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
          (!selectedAction || selectedProducts.length === 0 || loading)
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700'
        ]">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <select v-model="filterStatus" v-if="filterTrash !== 'trash'"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Tất cả trạng thái</option>
          <option value="instock">Còn hàng</option>
          <option value="outofstock">Hết hàng</option>
        </select>
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
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Ảnh
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Tên sản phẩm
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Tồn kho
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Danh mục
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thẻ sản phẩm
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Ngày tạo
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Cửa hàng
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Trạng thái
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in filteredProducts" :key="product.id" :class="{ 'bg-gray-50': product.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedProducts" :value="product.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <img v-if="getProductImage(product)" :src="`${mediaBase}` + getProductImage(product)" alt="Product Image"
                class="w-12 h-12 object-cover rounded" />
              <span v-else>–</span>
            </td>
            <td
              class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700 hover:underline cursor-pointer"
              @click="editProduct(product.id)">
              {{ truncateText(product.name, 30) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <span :class="getStockStatus(product) === 'instock' ? 'text-green-600' : 'text-red-600'">
                {{ getStockStatus(product) === 'instock' ? 'Còn hàng' : 'Hết hàng' }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{product.categories?.length ? product.categories.map(c => c.name).join(', ') : '–'}}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{product.tags?.length ? product.tags.map(t => t.name).join(', ') : '–'}}
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
              <div class="relative inline-block text-left">
                <button @click="toggleDropdown(product.id)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchProducts" />

  <!-- Dropdown Portal -->
  <Teleport to="body">
    <Transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
      <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
        <div v-for="product in products" :key="product.id" v-show="activeDropdown === product.id"
          class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
          :style="dropdownPosition">
          <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
            <button v-if="product.status !== 'trash'" @click="editProduct(product.id)"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
              role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Sửa
            </button>
            <button v-if="product.status !== 'trash'" @click="moveToTrash(product)"
              class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
              role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Thêm vào thùng rác
            </button>
            <button v-if="product.status === 'trash'" @click="restoreProduct(product)"
              class="flex items-center w-full px-4 py-2 text-sm text-green-600 hover:bg-green-50 transition-colors duration-150"
              role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Khôi phục
            </button>
            <button v-if="product.status === 'trash'" @click="confirmDelete(product)"
              class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
              role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Xóa vĩnh viễn
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

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

  <!-- Approval History Modal -->
<Teleport to="body">
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="showApprovalHistory" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeApprovalHistory"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
        <div
          class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
        >
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
                  <tr
                    v-for="history in approvalHistory"
                    :key="history.id"
                    :class="{ 'bg-gray-50': history.id % 2 === 0 }"
                    class="border-b border-gray-300"
                  >
                    <td class="border border-gray-300 px-3 py-2 text-left">{{ history.id }}</td>
                    <td class="border border-gray-300 px-3 py-2 text-left">
                      {{ truncateText(history.product_name, 30) }}

                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-left">
                      {{ history.admin_name || '–' }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-left">
                      <span
                        class="inline-block px-3 py-1 text-xs rounded-full font-medium"
                        :class="{
                          'bg-green-100 text-green-700': history.status === 'approved',
                          'bg-red-100 text-red-600': history.status === 'rejected',
                        }"
                      >
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
            <button
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              @click="closeApprovalHistory"
            >
              Đóng
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import Pagination from '~/components/Pagination.vue';


definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const products = ref([]);
const selectedProducts = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const selectedAction = ref('');
const filterStatus = ref('');
const filterTrash = ref('');
const sortBy = ref('newest');
const filterCategory = ref('');
const filterBrand = ref('');
const filterTag = ref('');
const categories = ref([]);
const brands = ref([]);
const tags = ref([]);
const totalProducts = ref(0);
const inStockProducts = ref(0);
const trashProducts = ref(0);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' });
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
const lastPage = ref(1);
const perPage = 10;

// Fetch product counts (total, instock, trash)
const fetchProductCounts = async () => {
  try {
    // Fetch all products to get total count
    const productsResponse = await fetch(`${apiBase}/products`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    const productsData = await productsResponse.json();
    const allProducts = productsData.data?.data || productsData.data || [];
    totalProducts.value = productsData.data?.total || allProducts.length || 0;
    inStockProducts.value = allProducts.filter(p => getStockStatus(p) === 'instock').length;

    // Fetch trashed products to get trash count
    const trashResponse = await fetch(`${apiBase}/products/trash`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    const trashData = await trashResponse.json();
    const trashProductsList = trashData.data?.data || trashData.data || [];
    trashProducts.value = trashData.data?.total || trashProductsList.length || 0;
  } catch (error) {
    console.error('Error fetching product counts:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải số lượng sản phẩm', 'error');
  }
};

// Fetch products from API
const fetchProducts = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;
    const endpoint = filterTrash.value === 'trash'
      ? `${apiBase}/products/trash?page=${page}&per_page=${perPage}`
      : `${apiBase}/products?page=${page}&per_page=${perPage}`;
    const response = await fetch(endpoint, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    const data = await response.json();
    products.value = data.data?.data || data.data || data || [];
    lastPage.value = data.data?.last_page || 1;
    currentPage.value = data.data?.current_page || page;
    if (!products.value.length) {
      showNotificationMessage(filterTrash.value === 'trash' ? 'Không có sản phẩm nào trong thùng rác' : 'Không có sản phẩm nào');
    }
    await fetchProductCounts();
  } catch (error) {
    console.error('Error fetching products:', error);
    showNotificationMessage(`Có lỗi xảy ra khi tải sản phẩm: ${error.message}`, 'error');
    products.value = [];
  } finally {
    loading.value = false;
  }
};

// Fetch categories
const fetchCategories = async () => {
  try {
    const response = await fetch(`${apiBase}/categories`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    const data = await response.json();
    categories.value = data.data.data || data.categories || [];
  } catch (error) {
    console.error('Error fetching categories:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải danh mục', 'error');
  }
};

// Fetch brands
const fetchBrands = async () => {
  try {
    const response = await fetch(`${apiBase}/sellers/verified`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    const data = await response.json();
    brands.value = data.data || [];

  } catch (error) {
    console.error('Error fetching brands:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải thương hiệu', 'error');
  }
};

// Fetch tags
const fetchTags = async () => {
  try {
    const response = await fetch(`${apiBase}/tags`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    const data = await response.json();
    tags.value = data.data.tags || [];
    console.log('tags', tags.value);

  } catch (error) {
    console.error('Error fetching tags:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải thẻ', 'error');
  }
};

// Get product image
const getProductImage = (product) => {
  if (product.product_pic?.[0]?.imagePath) {
    return product.product_pic[0].imagePath;
  }
  if (product.product_variants?.[0]?.thumbnail) {
    return product.product_variants[0].thumbnail;
  }
  return null;
};

// Get stock status
const getStockStatus = (product) => {
  const totalQuantity = product.product_variants?.reduce((sum, variant) => {
    return sum + (variant.quantity || 0);
  }, 0) || 0;
  return totalQuantity > 0 ? 'instock' : 'outofstock';
};

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
    selectedProducts.value = filteredProducts.value.map(p => p.id);
  } else {
    selectedProducts.value = [];
  }
};
function truncateText(text, maxLength) {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text;
}

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedProducts.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một sản phẩm', 'error');
    return;
  }

  if (selectedAction.value === 'delete') {
    showConfirmationDialog(
      'Xác nhận xóa vĩnh viễn',
      `Bạn có chắc chắn muốn xóa vĩnh viễn ${selectedProducts.value.length} sản phẩm đã chọn?`,
      async () => {
        try {
          loading.value = true;
          const deletePromises = selectedProducts.value.map(id =>
            fetch(`${apiBase}/products/${id}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json'

              }
            })
          );

          const responses = await Promise.all(deletePromises);
          const failed = responses.some(res => !res.ok);
          if (failed) {
            showNotificationMessage('Có lỗi xảy ra khi xóa một số sản phẩm', 'error');
          } else {
            showNotificationMessage('Xóa vĩnh viễn các sản phẩm thành công!', 'success');
          }
          selectedProducts.value = [];
          selectAll.value = false;
          selectedAction.value = '';
          await fetchProducts();
        } catch (error) {
          console.error('Error deleting products:', error);
          showNotificationMessage('Có lỗi xảy ra khi xóa sản phẩm', 'error');
        } finally {
          loading.value = false;
        }
      }
    );
  } else if (['active', 'inactive', 'trash', 'restore'].includes(selectedAction.value)) {
    try {
      loading.value = true;
      const status = selectedAction.value === 'active' ? 'active' :
        selectedAction.value === 'inactive' ? 'inactive' :
          selectedAction.value === 'trash' ? 'trash' : 'active';
      const token = localStorage.getItem('access_token');
      const updatePromises = selectedProducts.value.map(id =>
        fetch(`${apiBase}/products/change-status/${id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({ status })
        })
      );

      const responses = await Promise.all(updatePromises);
      const failed = responses.some(res => !res.ok);
      if (failed) {
        showNotificationMessage('Có lỗi xảy ra khi cập nhật trạng thái một số sản phẩm', 'error');
      } else {
        showNotificationMessage(
          selectedAction.value === 'trash' ? 'Đã chuyển các sản phẩm vào thùng rác!' :
            selectedAction.value === 'restore' ? 'Khôi phục các sản phẩm thành công!' :
              'Cập nhật trạng thái thành công!', 'success'
        );
      }
      selectedProducts.value = [];
      selectAll.value = false;
      selectedAction.value = '';
      await fetchProducts();
    } catch (error) {
      console.error('Error updating status:', error);
      showNotificationMessage('Có lỗi xảy ra khi cập nhật trạng thái', 'error');
    } finally {
      loading.value = false;
    }
  }
};

// Edit product
const editProduct = (id) => {
  router.push(`/admin/products/edit-product/${id}`);
};

// Move to trash
const moveToTrash = async (product) => {
  const token = localStorage.getItem('access_token');
  showConfirmationDialog(
    'Xác nhận chuyển vào thùng rác',
    `Bạn có chắc chắn muốn chuyển sản phẩm "${product.name}" vào thùng rác?`,

    async () => {
      try {
        const response = await fetch(`${apiBase}/products/change-status/${product.id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({ status: 'trash' })
        });

        if (response.ok) {
          showNotificationMessage('Đã chuyển sản phẩm vào thùng rác!', 'success');
          await fetchProducts();
        } else {
          const data = await response.json();
          showNotificationMessage(data.message || 'Có lỗi xảy ra khi chuyển vào thùng rác', 'error');
        }
      } catch (error) {
        console.error('Error moving to trash:', error);
        showNotificationMessage('Có lỗi xảy ra khi chuyển vào thùng rác', 'error');
      }
    }
  );
};

// Restore product
const restoreProduct = async (product) => {
  const token = localStorage.getItem('access_token');
  showConfirmationDialog(
    'Xác nhận khôi phục',
    `Bạn có chắc chắn muốn khôi phục sản phẩm "${product.name}"?`,
    async () => {
      try {
        const response = await fetch(`${apiBase}/products/change-status/${product.id}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({ status: 'active' })
        });

        if (response.ok) {
          showNotificationMessage('Khôi phục sản phẩm thành công!', 'success');
          await fetchProducts();
        } else {
          const data = await response.json();
          showNotificationMessage(data.message || 'Có lỗi xảy ra khi khôi phục sản phẩm', 'error');
        }
      } catch (error) {
        console.error('Error restoring product:', error);
        showNotificationMessage('Có lỗi xảy ra khi khôi phục sản phẩm', 'error');
      }
    }
  );
};

// Delete product
const confirmDelete = async (product) => {
  showConfirmationDialog(
    'Xác nhận xóa vĩnh viễn',
    `Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm "${product.name}" không?`,
    async () => {
      try {
        const token = localStorage.getItem('access_token');
        const response = await fetch(`${apiBase}/products/${product.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        });

        if (response.ok) {
          showNotificationMessage('Xóa vĩnh viễn sản phẩm thành công!', 'success');
          await fetchProducts();
        } else {
          const data = await response.json();
          showNotificationMessage(data.message || 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
        }
      } catch (error) {
        console.error('Error deleting product:', error);
        showNotificationMessage('Có lỗi xảy ra khi xóa sản phẩm', 'error');
      }
    }
  );
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

// Toggle dropdown
const toggleDropdown = (id) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = id;
    nextTick(() => {
      const button = event.target.closest('button');
      if (button) {
        const rect = button.getBoundingClientRect();
        dropdownPosition.value = {
          top: `${rect.bottom + window.scrollY + 8}px`,
          left: `${rect.right + window.scrollX - 192}px`,
          width: '192px'
        };
      }
    });
  }
};

// Close dropdown
const closeDropdown = (event) => {
  if (!event.target.closest('.relative') && !event.target.closest('.absolute')) {
    activeDropdown.value = null;
  }
};

// Filtered products
const filteredProducts = computed(() => {
  let result = [...products.value];

  // Filter by trash status
  if (filterTrash.value === 'trash') {
    result = result.filter(product => product.status === 'trash');
  } else {
    result = result.filter(product => product.status !== 'trash');
  }

  // Filter by stock status (only when not in trash view)
  if (filterStatus.value && filterTrash.value !== 'trash') {
    result = result.filter(product => getStockStatus(product) === filterStatus.value);
  }

  // Filter by category
  if (filterCategory.value) {
    result = result.filter(product =>
      product.categories?.some(category => category.id === filterCategory.value)
    );
  }

  // Filter by brand
  if (filterBrand.value) {
    result = result.filter(product =>
      product.seller?.id === filterBrand.value
    );
  }

  // Filter by tag
  if (filterTag.value) {
    result = result.filter(product =>
      product.tags?.some(tag => tag.id === filterTag.value)
    );
  }

  // Search by name, slug, or description
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(product =>
      product.name?.toLowerCase().includes(query) ||
      product.slug?.toLowerCase().includes(query) ||
      (product.description && product.description.toLowerCase().includes(query))
    );
  }

  // Sort by date
  if (sortBy.value === 'newest') {
    result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
  } else if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
  }

  return result;
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

const showApprovalHistory = ref(false);
const approvalHistory = ref([]);

// Fetch approval history từ API
const fetchApprovalHistory = async () => {
  try {
    const response = await fetch(`${apiBase}/approvals/history`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
      },
    });
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    const data = await response.json();
    approvalHistory.value = data.data || [];

    if (!approvalHistory.value.length) {
      showNotificationMessage('Không có lịch sử xét duyệt nào.', 'info');
    }
  } catch (error) {
    console.error('Error fetching approval history:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải lịch sử xét duyệt', 'error');
  }
};

const openApprovalHistory = async () => {
  await fetchApprovalHistory();
  showApprovalHistory.value = true;
};

const closeApprovalHistory = () => {
  showApprovalHistory.value = false;
  approvalHistory.value = [];
};
// Lifecycle hooks
onMounted(() => {
  fetchProducts();
  fetchCategories();
  fetchBrands();
  fetchTags();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
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