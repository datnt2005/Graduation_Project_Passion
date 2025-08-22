<template>
  <div class="bg-[#F8F9FF] text-gray-700">
    <!-- Loading Overlay -->
    <div v-if="isPlacingOrder"
      class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center loading-backdrop">
      <div
        class="bg-white rounded-2xl shadow-2xl p-10 flex flex-col items-center space-y-6 max-w-md mx-4 relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 opacity-50"></div>
        <!-- Animated Shopping Cart Icon -->
        <div class="relative z-10">
          <svg class="w-16 h-16 text-blue-600 animate-float" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
          </svg>
          <!-- Animated dots -->
          <div class="absolute -top-2 -right-2 flex space-x-1">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
          </div>
        </div>
        <!-- Enhanced Loading Spinner -->
        <div class="relative z-10">
          <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
          <div
            class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-green-500 rounded-full animate-spin"
            style="animation-duration: 1.5s"></div>
          <div
            class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-yellow-500 rounded-full animate-spin"
            style="animation-duration: 2s"></div>
        </div>
        <div class="text-center space-y-3 z-10">
          <h3 class="text-xl font-bold text-gray-800">Đang xử lý đơn hàng</h3>
          <p class="text-sm text-gray-600 leading-relaxed">
            Vui lòng chờ trong giây lát, chúng tôi đang chuẩn bị đơn hàng của bạn...
          </p>
          <!-- Enhanced Progress indicators -->
          <div class="flex justify-center space-x-4 mt-6">
            <div class="flex items-center space-x-2">
              <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse"></div>
              <span class="text-xs text-gray-600 font-medium">Kiểm tra tồn kho</span>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-3 h-3 bg-green-600 rounded-full animate-pulse" style="animation-delay: 0.5s"></div>
              <span class="text-xs text-gray-600 font-medium">Tính toán phí</span>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-3 h-3 bg-yellow-600 rounded-full animate-pulse" style="animation-delay: 1s"></div>
              <span class="text-xs text-gray-600 font-medium">Tạo đơn hàng</span>
            </div>
          </div>
        </div>
        <!-- Decorative elements -->
        <div class="absolute top-4 right-4 z-10">
          <svg class="w-6 h-6 text-blue-200" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
          </svg>
        </div>
        <!-- Bottom decoration -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-green-500 to-yellow-500">
        </div>
      </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col lg:flex-row gap-6">
      <main class="flex-1 overflow-y-hidden"
        :class="{ 'opacity-50 pointer-events-none': isAccountBanned || isPlacingOrder || isAdminOrSeller || hasBannedSellers }">
        <!-- Thông báo khi tài khoản bị khóa hoặc không thể dùng COD -->
        <div v-if="isAccountBanned || (!canUseCod && !isAccountBanned && rejectedOrdersCount >= 2)"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <template v-if="isAccountBanned">
            Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận. Vui lòng liên hệ hỗ trợ để biết thêm
            chi tiết.
          </template>
          <template v-else>
            Bạn không thể sử dụng phương thức thanh toán COD vì có quá nhiều đơn hàng bị từ chối nhận.
          </template>
        </div>

        <!-- Thông báo khi user là admin hoặc seller -->
        <div v-if="isAdminOrSeller" 
          class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
          <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <div>
              <strong>Không thể đặt hàng:</strong> Tài khoản {{ userRole === 'admin' ? 'Admin' : 'Seller' }} không thể thực hiện đặt hàng. 
              Vui lòng sử dụng tài khoản khách hàng để tiếp tục.
            </div>
          </div>
        </div>

        <!-- Thông báo khi có sellers bị cấm -->
        <div v-if="hasBannedSellers" 
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <div class="flex items-center">
            <i class="fas fa-ban mr-2"></i>
            <div>
              <strong>Không thể đặt hàng:</strong> Các cửa hàng sau đã bị cấm:
              <ul class="mt-2 ml-4 list-disc">
                <li v-for="seller in bannedSellers" :key="seller.seller_id" class="text-sm">
                  <strong>{{ seller.store_name }}</strong>
                  <span v-if="seller.ban_reason"> - {{ seller.ban_reason }}</span>
                </li>
              </ul>
              <p class="text-sm mt-2">Vui lòng xóa các sản phẩm của cửa hàng bị cấm khỏi giỏ hàng để tiếp tục.</p>
            </div>
          </div>
        </div>
        <!-- Breadcrumb -->
        <div class="w-full max-w-7xl mb-4">
          <div class="text-sm text-gray-500 px-4 py-2 rounded">
            <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
            <span class="mx-1">›</span>
            <span class="text-black font-medium">Thanh toán</span>
          </div>
        </div>
        <!-- Header -->
        <section class="bg-white px-6 py-4 border-b border-gray-200 space-y-4 mb-2">
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">Thanh toán</h2>
              <p class="text-sm text-gray-500 mt-1">Vui lòng kiểm tra thông tin trước khi hoàn tất đơn hàng</p>
            </div>
            <div class="text-right hidden md:block">
              <span class="text-sm text-gray-600">Thời Gian Giao Hàng:</span>
              <span class="ml-2 text-blue-600 font-medium">Từ 3 đến 7 ngày</span>
            </div>
          </div>
        </section>
        <div class="min-h-full max-w-7xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-2">
              <!-- Loading state -->
              <div v-if="loading" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
              <!-- Shipping Selector -->
              <ShippingSelector :key="isBuyNow ? 'buy-now' : 'from-cart'" :address="selectedAddress"
                :cart-items="displayItems" :is-buy-now="isBuyNow" :shipping-discounts="uniqueShippingDiscounts"
                @update:shippingFee="updateShippingFee" @update:shopDiscount="handleShopDiscountUpdate"
                @update:totalShippingFee="handleTotalShippingFeeUpdate"
                @update:shippingDiscount="handleShippingDiscountUpdate" />
              <!-- Payment Methods -->
              <section class="bg-white rounded-[4px] p-5">
                <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức thanh toán</h3>
                <div v-if="paymentLoading" class="flex justify-center items-center py-4">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                </div>
                <form v-else class="space-y-6 text-xs text-gray-700 max-w-md">
                  <label v-for="method in paymentMethods" :key="method.id" class="cursor-pointer"
                    :class="method.name === 'VNPAY' || method.name === 'CREDIT' ? 'flex flex-col gap-1' : 'flex items-center gap-3'">
                    <div class="flex items-center gap-3">
                      <input class="w-4 h-4 text-blue-600 border-blue-600 focus:ring-blue-500 accent-blue-600"
                        type="radio" name="payment" :value="method.name" v-model="selectedPaymentMethod" />
                      <template v-if="method.name === 'COD'">
                        <i class="fas fa-hand-holding-usd text-[#2A5DB0] text-lg"></i>
                        <span>Thanh toán tiền mặt</span>
                      </template>
                      <template v-else-if="method.name === 'VIETTEL'">
                        <img src="https://storage.googleapis.com/a1aa/image/b3807c5a-0b76-4704-69fb-c3ef0c4d99ab.jpg"
                          alt="Viettel Money" class="w-5 h-5 object-contain" />
                        <span>Viettel Money</span>
                      </template>
                      <template v-else-if="method.name === 'MOMO'">
                        <img src="https://storage.googleapis.com/a1aa/image/6db00e7b-8953-4dc4-51f8-3fe0805858d1.jpg"
                          alt="Momo" class="w-5 h-5 object-contain" />
                        <span>Ví Momo</span>
                      </template>
                      <template v-else-if="method.name === 'ZALOPAY'">
                        <img src="https://storage.googleapis.com/a1aa/image/dc336404-6ee8-4fa2-4836-316782a96c00.jpg"
                          alt="ZaloPay" class="w-5 h-5 object-contain" />
                        <span>Ví ZaloPay</span>
                      </template>
                      <template v-else-if="method.name === 'VNPAY'">
                        <img src="https://storage.googleapis.com/a1aa/image/f9093db3-1943-4ac8-c243-b844b9d32c13.jpg"
                          alt="VNPAY" class="w-5 h-5 object-contain" />
                        <span>VNPAY</span>
                      </template>
                      <template v-else-if="method.name === 'CREDIT'">
                        <i class="fas fa-credit-card text-[#2A5DB0] text-lg"></i>
                        <span>Thẻ tín dụng/ Ghi nợ</span>
                        <div class="flex gap-1 ml-2">
                          <img src="https://storage.googleapis.com/a1aa/image/76558095-7f7c-4cd9-ec5d-947d743be711.jpg"
                            alt="Tiki" class="w-5 h-[12px] object-contain" />
                          <img src="https://storage.googleapis.com/a1aa/image/c6b52119-c8ce-4e24-831c-180cafb12671.jpg"
                            alt="Visa" class="w-5 h-[12px] object-contain" />
                          <img src="https://storage.googleapis.com/a1aa/image/11785e4a-1bd0-4af1-eeee-90375d5f3565.jpg"
                            alt="Mastercard" class="w-5 h-[12px] object-contain" />
                          <img src="https://storage.googleapis.com/a1aa/image/1641f402-65a9-4577-8362-46dd9d84b719.jpg"
                            alt="JCB" class="w-5 h-[12px] object-contain" />
                        </div>
                      </template>
                      <template v-else-if="method.name === 'ATM'">
                        <i class="fas fa-credit-card text-[#2A5DB0] text-lg"></i>
                        <div class="flex flex-col text-xs text-gray-500">
                          <span>Thẻ ATM</span>
                          <span class="text-[11px]">Hỗ trợ Internet Banking</span>
                        </div>
                      </template>
                      <template v-else>
                        <span>{{ getPaymentMethodLabel(method.name) }}</span>
                      </template>
                    </div>
                    <template v-if="method.name === 'VNPAY'">
                      <span class="text-xs text-gray-400 ml-9">Quét Mã QR từ ứng dụng ngân hàng</span>
                    </template>
                    <template v-if="method.name === 'CREDIT'">
                      <button @click="addNewCard"
                        class="ml-9 mt-1 text-[#2A7FDF] border border-[#2A7FDF] rounded px-3 py-1 text-xs font-medium hover:bg-[#E6F0FF]"
                        type="button">
                        + Thêm thẻ mới
                      </button>
                    </template>
                  </label>
                  <div v-if="selectedPaymentMethod === 'CREDIT'"
                    class="ml-9 mt-4 bg-[#F0F4FF] border border-[#D2E3FC] rounded p-3 grid grid-cols-3 gap-3 text-xs text-gray-700 max-w-[600px]">
                    <div v-for="promo in cardPromotions" :key="promo.id"
                      class="border border-[#D2E3FC] rounded p-2 flex flex-col justify-between cursor-pointer hover:shadow-md"
                      @click="selectCardPromotion(promo)">
                      <div class="flex justify-between items-center mb-1">
                        <div class="font-semibold text-[#2A5DB0]">{{ promo.name }}</div>
                        <img :src="promo.bankIcon" :alt="promo.bank" class="w-5 h-5 object-contain" />
                      </div>
                      <div class="text-[10px] text-gray-400">{{ promo.description }}</div>
                      <div class="text-[10px] text-[#E67E22] italic">{{ promo.limit }}</div>
                    </div>
                  </div>
                </form>
              </section>
            </div>
            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-2">
              <!-- Address Selector -->
              <SelectedAddress :address="selectedAddress" :provinces="provinces" :districts="districts" :wards="wards"
                @update:address="updateSelectedAddress" />
              <!-- Discounts -->
              <section class="bg-white p-6 rounded-[4px] shadow-sm">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <div class="flex items-center mb-2">
                      <span class="text-gray-800 font-semibold text-base">Khuyến mãi</span>
                      <span class="text-[13px] text-gray-600 ml-2">(Đã chọn {{ selectedDiscounts.length }})</span>
                    </div>
                    <div v-if="selectedDiscounts.length"
                      class="bg-gray-50 border border-dashed border-gray-300 rounded-md p-3 space-y-2">
                      <div v-for="discount in selectedDiscounts" :key="discount.id"
                        class="relative bg-white border border-gray-200 rounded px-3 py-2">
                        <button @click="removeDiscount(discount.id)"
                          class="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 shadow-md transition">
                          ×
                        </button>
                        <div>
                          <p class="text-sm font-semibold text-green-600">{{ discount.name }}</p>
                          <p class="text-xs text-gray-600">
                            {{ discount.discount_type === 'percentage'
                              ? `Giảm ${Math.round(discount.discount_value)}%`
                              : discount.discount_type === 'fixed'
                                ? `Giảm ${formatPrice(Number(discount.discount_value))} đ`
                                : (discount.discount_type === 'shipping_fee'
                                  ? `Giảm ${formatPrice(Number(discount.discount_value))} đ phí vận chuyển`
                                  : `Giảm ${formatPrice(Number(discount.discount_value))} đ`)
                            }}
                            <span v-if="discount.min_order_value">
                              (Đơn tối thiểu {{ formatPrice(discount.min_order_value) }} đ)
                            </span>
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <button @click="showDiscountModal = true"
                  class="flex items-center gap-2 text-[#2A7FDF] text-[14px] hover:underline" type="button">
                  <i class="fas fa-ticket-alt"></i>
                  Chọn hoặc nhập mã khác
                  <i class="fas fa-chevron-right text-[10px]"></i>
                </button>
                <!-- Discount Modal -->
                <div v-if="showDiscountModal"
                  class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center"
                  @click.self="showDiscountModal = false">
                  <div class="bg-white rounded-[8px] shadow-xl w-full max-w-2xl p-6 relative">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                      <h3 class="text-lg font-semibold text-gray-800">Chọn mã giảm giá</h3>
                      <button @click="showDiscountModal = false" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-base"></i>
                      </button>
                    </div>
                    <div class="mb-6">
                      <label class="block text-sm text-gray-700 mb-1">Nhập mã giảm giá</label>
                      <div class="flex gap-2">
                        <input v-model="manualCode" type="text" placeholder="Nhập mã..."
                          class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <button @click="applyManualDiscount"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-md">
                          Áp dụng
                        </button>
                      </div>
                      <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Bạn có thể nhập mã giảm giá của admin hoặc mã giảm giá của từng shop
                      </p>
                    </div>
                    <div class="space-y-6 max-h-[450px] overflow-y-auto">
                      <!-- Nhóm 1: Mã giảm phí vận chuyển -->
                      <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Mã giảm phí vận chuyển</h3>
                        <div v-if="discountLoading" class="text-gray-500 text-sm italic mt-2">Đang tải mã giảm giá...
                        </div>

                        <div v-else-if="uniqueShippingDiscounts.length" class="space-y-3">
                          <div v-for="discount in uniqueShippingDiscounts" :key="discount.id"
                            class="border border-gray-300 rounded-md p-4 hover:border-blue-500 transition duration-200"
                            :class="{ 'opacity-50': total < (discount.min_order_value || 0) || isDiscountExpired(discount) }">
                            <div class="flex justify-between items-center">
                              <div>
                                <p class="font-semibold text-sm text-gray-800">
                                  {{ discount.name }}
                                  <span v-if="discount.seller_id"> (Shop: {{ getShopName(discount.seller_id) }})</span>
                                </p>
                                <p class="text-xs text-gray-600">
                                  Giảm {{ formatPrice(Number(discount.discount_value)) }} đ phí vận chuyển
                                  <span v-if="discount.min_order_value">
                                    | Đơn tối thiểu {{ formatPrice(discount.min_order_value) }} đ
                                  </span>
                                </p>
                                <p class="text-[11px] text-gray-400 mt-1">HSD: {{ formatDate(discount.end_date) }}</p>
                                <p v-if="isDiscountExpired(discount)" class="text-[11px] text-red-500 mt-1">Mã đã hết
                                  hạn</p>
                                <p v-else-if="total < (discount.min_order_value || 0)"
                                  class="text-[11px] text-red-500 mt-1">
                                  Đơn hàng chưa đủ {{ formatPrice(discount.min_order_value) }} đ
                                </p>
                              </div>

                              <div>
                                <button v-if="selectedDiscounts.some(d => d.id === discount.id)"
                                  @click="removeDiscount(discount.id)" class="text-red-500 text-sm hover:underline">
                                  Bỏ chọn
                                </button>
                                <button v-else @click="applyDiscount(discount)"
                                  :disabled="total < (discount.min_order_value || 0) || isDiscountExpired(discount)"
                                  class="text-blue-600 text-sm hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
                                  Áp dụng
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div v-else class="text-gray-500 text-sm italic mt-2">Không có mã giảm phí vận chuyển phù hợp
                        </div>
                      </div>

                      <!-- Nhóm 2: Mã giảm giá (gộp phần trăm + cố định) -->
                      <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Mã giảm giá (phần trăm & cố định)</h3>
                        <div v-if="discountLoading" class="text-gray-500 text-sm italic mt-2">Đang tải mã giảm giá...
                        </div>

                        <div v-else-if="[...uniquePercentageDiscounts, ...uniqueFixedDiscounts].length"
                          class="space-y-3">
                          <div v-for="discount in [...uniquePercentageDiscounts, ...uniqueFixedDiscounts]"
                            :key="discount.id"
                            class="border border-gray-300 rounded-md p-4 hover:border-blue-500 transition duration-200"
                            :class="{ 'opacity-50': total < (discount.min_order_value || 0) || isDiscountExpired(discount) }">
                            <div class="flex justify-between items-center">
                              <div>
                                <p class="font-semibold text-sm text-gray-800">
                                  {{ discount.name }}
                                  <span v-if="discount.seller_id"> (Shop: {{ getShopName(discount.seller_id) }})</span>
                                </p>

                                <!-- Dòng mô tả: nếu là item trong uniquePercentageDiscounts thì hiển thị %, còn lại hiển thị số tiền -->
                                <p class="text-xs text-gray-600">
                                  <template v-if="uniquePercentageDiscounts.some(p => p.id === discount.id)">
                                    Giảm {{ Math.round(discount.discount_value) }}%
                                  </template>
                                  <template v-else>
                                    Giảm {{ formatPrice(Number(discount.discount_value)) }} đ
                                  </template>

                                  <span v-if="discount.min_order_value">
                                    | Đơn tối thiểu {{ formatPrice(discount.min_order_value) }} đ
                                  </span>
                                </p>

                                <p class="text-[11px] text-gray-400 mt-1">HSD: {{ formatDate(discount.end_date) }}</p>
                                <p v-if="isDiscountExpired(discount)" class="text-[11px] text-red-500 mt-1">Mã đã hết
                                  hạn</p>
                                <p v-else-if="total < (discount.min_order_value || 0)"
                                  class="text-[11px] text-red-500 mt-1">
                                  Đơn hàng chưa đủ {{ formatPrice(discount.min_order_value) }} đ
                                </p>
                              </div>

                              <div>
                                <button v-if="selectedDiscounts.some(d => d.id === discount.id)"
                                  @click="removeDiscount(discount.id)" class="text-red-500 text-sm hover:underline">
                                  Bỏ chọn
                                </button>
                                <button v-else @click="applyDiscount(discount)"
                                  :disabled="total < (discount.min_order_value || 0) || isDiscountExpired(discount)"
                                  class="text-blue-600 text-sm hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
                                  Áp dụng
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div v-else class="text-gray-500 text-sm italic mt-2">Không có mã giảm giá phù hợp</div>
                      </div>
                    </div>

                  </div>
                </div>
              </section>
              <!-- Order Summary -->
              <section class="bg-white rounded-lg p-5 text-sm text-gray-700 border border-gray-200 space-y-4">
                <div class="flex justify-between items-center">
                  <h3 class="text-base font-semibold text-gray-900">Thông tin đơn hàng</h3>
                  <NuxtLink to="/cart" class="text-blue-600 text-sm font-medium hover:underline">Thay đổi</NuxtLink>
                </div>
                <div class="flex items-center justify-between">
                  <div class="text-sm">
                    {{ displayProductCount }} sản phẩm từ {{ displayShopCount }} cửa hàng
                  </div>
                  <div class="cursor-pointer" @click="isOrderDetailsOpen = !isOrderDetailsOpen">
                    <span class="ml-2 transform transition-transform" :class="{ 'rotate-180': isOrderDetailsOpen }">
                      <i class="fas fa-chevron-down"></i>
                    </span>
                  </div>
                </div>
                <div v-if="isOrderDetailsOpen" class="space-y-3">
                  <hr />
                  <div v-for="store in displayItems" :key="store.seller_id" class="bg-white rounded shadow p-4 mb-4">
                    <div class="font-semibold text-gray-800 mb-2">{{ store.store_name }}</div>
                    <div v-for="item in store.items" :key="item.id"
                      class="flex items-center py-2 border-b last:border-b-0">
                      <span class="text-xs text-gray-500 w-12 text-center">{{ item.quantity }} x</span>
                      <span v-if="item.productVariant?.attributes" class="text-xs text-gray-500 w-16 text-center">
                        {{item.productVariant.attributes.map(attr => attr.value).join(', ')}}
                      </span>
                      <span class="flex-1 font-semibold text-sm truncate">{{ item.product?.name }}</span>
                      <span class="font-semibold w-24 text-right">{{ formatPrice(item.sale_price) }} đ</span>
                    </div>
                  </div>
                </div>
                <hr />
                <div class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-[14px]">Tổng tiền hàng</span>
                    <span class="text-[14px] text-gray-800">{{ formattedTotal }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-[14px]">Tổng phí vận chuyển</span>
                    <span class="text-[14px] text-gray-800">{{ formatPrice(realShippingFee) }} đ</span>
                  </div>
                  <div v-for="store in displayItems" :key="store.seller_id" class="flex justify-between">
                    <span class="text-[14px]">Giảm giá {{ store.store_name || store.seller_id }}</span>
                    <span class="text-green-600">- {{ formatPrice(store.discount || 0) }} đ</span>
                  </div>
                  <div class="flex justify-between pt-3 border-t border-gray-200 text-base font-semibold">
                    <span class="text-[14px]">Tổng thanh toán</span>
                    <span class="text-[15px] text-lg">{{ formatPrice(realFinalTotal) }} đ</span>
                  </div>
                  <p class="text-xs text-gray-500 italic text-right mt-1 leading-snug">
                    (Giá đã bao gồm thuế GTGT, phí đóng gói, phí vận chuyển và chi phí phát sinh khác)
                  </p>
                </div>
                <div class="pt-2">
                  <!-- Thông báo cho admin/seller -->
                  <div v-if="isAdminOrSeller" class="text-center text-sm text-yellow-600 mb-3 p-2 bg-yellow-50 rounded border border-yellow-200">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tài khoản {{ userRole === 'admin' ? 'Admin' : 'Seller' }} không thể đặt hàng
                  </div>

                  <!-- Thông báo cho banned sellers -->
                  <div v-if="hasBannedSellers" class="text-center text-sm text-red-600 mb-3 p-2 bg-red-50 rounded border border-red-200">
                    <i class="fas fa-ban mr-1"></i>
                    Có {{ bannedSellers.length }} cửa hàng bị cấm - Không thể đặt hàng
                  </div>
                  
                  <button @click="handlePlaceOrder"
                    class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-4 rounded-lg font-bold text-base hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg hover:shadow-xl"
                    :disabled="!displayItems.length || loading || isAccountBanned || isPlacingOrder || isAdminOrSeller || hasBannedSellers">
                    <span v-if="isPlacingOrder" class="flex items-center justify-center">
                      <!-- Animated shopping cart icon -->
                      <svg class="w-5 h-5 mr-2 animate-bounce" fill="currentColor" viewBox="0 0 24 24">
                        <path
                          d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                      </svg>
                      <!-- Loading dots -->
                      <div class="flex space-x-1 mr-2">
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse" style="animation-delay: 0.2s">
                        </div>
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse" style="animation-delay: 0.4s">
                        </div>
                      </div>
                      Đang xử lý đơn hàng...
                    </span>
                    <span v-else-if="isAdminOrSeller" class="flex items-center justify-center">
                      <!-- Lock icon -->
                      <i class="fas fa-lock mr-2"></i>
                      Không thể đặt hàng
                    </span>
                    <span v-else-if="hasBannedSellers" class="flex items-center justify-center">
                      <!-- Ban icon -->
                      <i class="fas fa-ban mr-2"></i>
                      Có cửa hàng bị cấm
                    </span>
                    <span v-else class="flex items-center justify-center">
                      <!-- Shopping cart icon -->
                      <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path
                          d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                      </svg>
                      Đặt hàng ngay
                    </span>
                  </button>
                </div>
              </section>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed, reactive } from 'vue';
import { useRoute, useRuntimeConfig } from '#app';
import axios from 'axios';
import Swal from 'sweetalert2';
import SelectedAddress from '~/components/shared/SelectedAddress.vue';
import ShippingSelector from '~/components/shared/ShippingSelector.vue';
import { useCheckout } from '~/composables/useCheckout';
import { useDiscount } from '~/composables/useDiscount';
import { checkoutPerformance } from '~/utils/performance';
import { useToast } from '~/composables/useToast';
import { useHead } from '#imports'

useHead({
  title: 'Thanh toán ',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl;
const route = useRoute();
const { toast } = useToast();

const shippingRef = ref(null);
const selectedShippingMethod = ref(null);
const selectedAddress = ref(null);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const manualCode = ref('');
const showDiscountModal = ref(false);
const storeNotes = ref({});
const isOrderDetailsOpen = ref(false);
const shippingFees = ref({});
const orderLoading = ref(false);
const userRole = ref(null);
const isAdminOrSeller = ref(false);
const sellerStatuses = ref({});
const bannedSellers = ref([]);
const hasBannedSellers = ref(false);
const displayItems = computed(() => (isBuyNow.value ? buyNowItems.value : cartItems.value));
const displayProductCount = computed(() =>
  displayItems.value.reduce((sum, shop) => sum + (shop.items?.reduce((s, i) => s + (i.quantity || 0), 0) || 0), 0)
);
const displayShopCount = computed(() => cartItems.value.length);
const cardPromotions = ref([
  {
    id: 1,
    name: 'Visa 10% Off',
    description: 'Giảm 10% tối đa 100,000đ',
    bank: 'Visa',
    bankIcon: 'https://storage.googleapis.com/a1aa/image/c6b52119-c8ce-4e24-831c-180cafb12671.jpg',
    limit: '1 lần/người',
  },
  {
    id: 2,
    name: 'Mastercard 50K Off',
    description: 'Giảm 50,000đ cho đơn trên 500,000đ',
    bank: 'Mastercard',
    bankIcon: 'https://storage.googleapis.com/a1aa/image/11785e4a-1bd0-4af1-eeee-90375d5f3565.jpg',
    limit: 'Hạn sử dụng 30 ngày',
  },
]);

const {
  cartItems,
  cart,
  total,
  formattedTotal,
  realShippingFee,
  realFinalTotal,
  loading,
  error,
  paymentMethods,
  paymentLoading,
  paymentError,
  buyNowItems,
  discounts,
  selectedDiscounts,
  discountLoading,
  discountError,
  selectedPaymentMethod,
  fetchPaymentMethods,
  fetchDiscounts,
  applyDiscount,
  removeDiscount,
  calculateDiscount,
  getShippingDiscount,
  formatPrice,
  getPaymentMethodLabel,
  placeOrder,
  selectStoreItems,
  removeOrderedItems,
  isPlacingOrder,
  isBuyNow,
  buyNowData,
  updateShopDiscount,
  getShopDiscount,
  isAccountBanned,
  rejectedOrdersCount,
  checkCodEligibility,
  loadShippingFees,
  fetchDefaultAddress,
  calculateShippingFee,
  shopServiceIds,
  getShippingDiscountPerShop,
  getProductDiscountPerShop,
  totalShippingDiscount,
  removeShopDiscount,
  recalculateAllShopDiscounts,
  canUseCod,
  getUserInfo,
  checkAllSellersStatus
} = useCheckout(shippingRef, selectedShippingMethod, selectedAddress, storeNotes);

const { fetchMyVouchers, fetchDiscounts: fetchPublicDiscounts, fetchSellerDiscounts, discounts: publicDiscounts, checkMultipleDiscounts } = useDiscount();

// Ensure publicDiscounts is reactive
publicDiscounts.value = reactive(publicDiscounts.value || []);

// Computed unique discounts for each type to avoid duplication and ensure proper display
const uniqueShippingDiscounts = computed(() => {
  const seen = new Set();
  const filtered = publicDiscounts.value
    .filter(d => d.discount_type === 'shipping_fee' && !d.seller_id && !seen.has(d.id || `${d.code}-${d.name}-${d.discount_type}`) && seen.add(d.id || `${d.code}-${d.name}-${d.discount_type}`))
    .map(d => ({ ...d, admin: true }));
  return filtered;
});

const uniquePercentageDiscounts = computed(() => {
  const seen = new Set();
  const filtered = publicDiscounts.value
    .filter(d => d.discount_type === 'percentage' && !d.seller_id && !seen.has(d.id || `${d.code}-${d.name}-${d.discount_type}`) && seen.add(d.id || `${d.code}-${d.name}-${d.discount_type}`))
    .map(d => ({ ...d, admin: true }));
  return filtered;
});

const uniqueFixedDiscounts = computed(() => {
  const seen = new Set();
  const filtered = publicDiscounts.value
    .filter(d => d.discount_type === 'fixed' && !d.seller_id && !seen.has(d.id || `${d.code}-${d.name}-${d.discount_type}`) && seen.add(d.id || `${d.code}-${d.name}-${d.discount_type}`))
    .map(d => ({ ...d, admin: true }));
  return filtered;
});

// Hàm lấy tên shop từ seller_id
const getShopName = (sellerId) => {
  const shop = cartItems.value.find(s => s.seller_id === sellerId);
  return shop ? shop.store_name : 'Unknown';
};

// Hàm kiểm tra mã hết hạn
const isDiscountExpired = (discount) => {
  if (!discount || !discount.end_date) return true;
  const endDate = new Date(discount.end_date);
  return endDate < new Date();
};

// Hàm định dạng ngày hiển thị
const formatDate = (date) => {
  if (!date) return 'N/A';
  const d = new Date(date);
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
};

const updateShippingFee = ({ sellerId, fee }) => {
  if (cart.value && cart.value.stores) {
    const store = cart.value.stores.find(s => s.seller_id === sellerId);
    if (store) {
      store.shipping_fee = fee;
    }
  }
};

const handleTotalShippingFeeUpdate = (newTotal) => {
  console.log(`Updated totalShippingFee: ${newTotal}`);
};

const handleShopDiscountUpdate = async (data) => {
  if (data && data.sellerId) {
    if (data.action === 'remove') {
      await removeShopDiscount(data.sellerId);
      toast('success', 'Đã xóa mã giảm giá');
    } else {
      const success = await updateShopDiscount(data.sellerId, data.discount, data.discountId);
    }
  }
};

const handleShippingDiscountUpdate = (discountData) => {
  if (discountData.sellerId) {
    if (cart.value && cart.value.stores) {
      const store = cart.value.stores.find(s => s.seller_id === discountData.sellerId);
      if (store) {
        store.shipping_discount = discountData.shippingDiscount || 0;
      }
    }
  }
};

const applyManualDiscount = async () => {
  const code = manualCode.value.trim().toUpperCase();
  if (!code) {
    toast('warning', 'Vui lòng nhập mã giảm giá');
    return;
  }

  let discount = publicDiscounts.value.find((d) => d.code?.toUpperCase() === code && !d.seller_id && ['shipping_fee', 'percentage', 'fixed'].includes(d.discount_type));

  if (!discount) {
    toast('error', 'Không tìm thấy mã giảm giá này');
    return;
  }

  if (isDiscountExpired(discount)) {
    toast('error', 'Mã giảm giá đã hết hạn');
    return;
  }

  if (discount.min_order_value && total.value < discount.min_order_value) {
    toast('error', `Đơn hàng chưa đủ điều kiện (${formatPrice(discount.min_order_value)} đ) để dùng mã này`);
    return;
  }

  if (selectedDiscounts.value.length >= 2) {
    toast('error', 'Bạn chỉ có thể áp dụng tối đa 2 mã giảm giá');
    return;
  }

  // Kiểm tra xem có thể áp dụng cùng lúc với discount hiện tại không
  const currentDiscounts = selectedDiscounts.value.map(d => d.id);
  const testDiscounts = [...currentDiscounts, discount.id];

  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const checkResult = await checkMultipleDiscounts(testDiscounts, user.id, total.value, realShippingFee.value);

    if (checkResult.success) {
      await applyDiscount(discount);

      // Xử lý theo loại discount
      if (discount.discount_type === 'shipping_fee') {
        // Shipping discount được xử lý tự động bởi backend
        toast('success', `Đã áp dụng mã giảm phí vận chuyển ${discount.name}`);
      } else if (!discount.seller_id && (discount.discount_type === 'percentage' || discount.discount_type === 'fixed')) {
        // Product discount - chia đều cho các shop
        const shopCount = cartItems.value.length;
        const perShopDiscount = getProductDiscountPerShop(total.value, shopCount);
        if (perShopDiscount > 0) {
          for (const shop of cartItems.value) {
            await updateShopDiscount(shop.seller_id, perShopDiscount, discount.id);
          }
          toast('success', `Đã áp dụng mã giảm giá ${discount.name} cho tất cả cửa hàng`);
        } else {
          toast('error', 'Không thể phân bổ mã giảm giá do tổng tiền hàng hoặc số lượng shop không hợp lệ');
        }
      } else if (discount.seller_id) {
        // Shop-specific discount
        const shop = cartItems.value.find(s => s.seller_id === discount.seller_id);
        if (shop) {
          const shopDiscountAmount = discount.discount_type === 'percentage'
            ? shop.store_total * discount.discount_value / 100
            : discount.discount_value;
          if (shopDiscountAmount > shop.store_total) {
            toast('error', `Giá trị giảm giá không hợp lệ cho ${shop.store_name}`);
            return;
          }
          const success = await updateShopDiscount(shop.seller_id, shopDiscountAmount, discount.id);
          if (success) {
            toast('success', `Đã áp dụng mã giảm giá cho ${shop.store_name}`);
          } else {
            toast('error', `Không thể áp dụng mã giảm giá cho ${shop.store_name}`);
          }
        }
      }
    } else {
      toast('error', checkResult.message || 'Không thể áp dụng mã giảm giá này');
    }
  } catch (error) {
    console.error('Error checking multiple discounts:', error);
    toast('error', 'Lỗi khi kiểm tra mã giảm giá');
  }

  manualCode.value = '';
  showDiscountModal.value = false;
};

const selectCardPromotion = async (promo) => {
  const discount = discounts.value.find((d) => d.name === promo.name && !d.seller_id && ['percentage', 'fixed'].includes(d.discount_type));
  if (!discount) {
    toast('error', 'Ưu đãi không khả dụng');
    return;
  }
  if (isDiscountExpired(discount)) {
    toast('error', 'Ưu đãi đã hết hạn');
    return;
  }
  if (discount.min_order_value && total.value < discount.min_order_value) {
    toast('error', `Đơn hàng chưa đủ điều kiện (${formatPrice(discount.min_order_value)} đ) để dùng mã này`);
    return;
  }
  if (selectedDiscounts.value.length >= 2) {
    toast('error', 'Bạn chỉ có thể áp dụng tối đa 2 mã giảm giá');
    return;
  }

  // Kiểm tra xem có thể áp dụng cùng lúc với discount hiện tại không
  const currentDiscounts = selectedDiscounts.value.map(d => d.id);
  const testDiscounts = [...currentDiscounts, discount.id];

  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const checkResult = await checkMultipleDiscounts(testDiscounts, user.id, total.value, realShippingFee.value);

    if (checkResult.success) {
      await applyDiscount(discount);

      if (!discount.seller_id && (discount.discount_type === 'percentage' || discount.discount_type === 'fixed')) {
        const shopCount = cartItems.value.length;
        const perShopDiscount = getProductDiscountPerShop(total.value, shopCount);
        if (perShopDiscount > 0) {
          for (const shop of cartItems.value) {
            await updateShopDiscount(shop.seller_id, perShopDiscount, discount.id);
          }
          toast('success', `Đã áp dụng ưu đãi ${discount.name} cho tất cả cửa hàng`);
        }
      }
    } else {
      toast('error', checkResult.message || 'Không thể áp dụng ưu đãi này');
    }
  } catch (error) {
    console.error('Error checking multiple discounts:', error);
    toast('error', 'Lỗi khi kiểm tra ưu đãi');
  }
};

const addNewCard = () => {
  toast('info', 'Chức năng thêm thẻ mới chưa được triển khai');
};

const loadProvinces = async () => {
  try {
    const res = await axios.get(`${apiBase}/ghn/provinces`);
    provinces.value = res.data.data || [];
  } catch (err) {
    console.error('Error loading provinces:', err);
    toast('error', 'Không thể tải danh sách tỉnh/thành');
  }
};

const loadDistricts = async (province_id) => {
  try {
    const res = await axios.post(`${apiBase}/ghn/districts`, { province_id });
    districts.value = res.data.data || [];
  } catch (err) {
    console.error('Error loading districts:', err);
    toast('error', 'Không thể tải danh sách quận/huyện');
  }
};

const loadWards = async (district_id) => {
  try {
    const res = await axios.post(`${apiBase}/ghn/wards`, { district_id });
    wards.value = res.data.data || [];
  } catch (err) {
    console.error('Error loading wards:', err);
    toast('error', 'Không thể tải danh sách phường/xã');
  }
};

const updateSelectedAddress = async (newAddress) => {
  selectedAddress.value = newAddress;
  if (newAddress && newAddress.province_id && newAddress.district_id) {
    await loadDistricts(newAddress.province_id);
    await loadWards(newAddress.district_id);
    await loadShippingFees();
  }
};

const checkUserRole = async () => {
  try {
    const userData = await getUserInfo();
    userRole.value = userData.role;
    isAdminOrSeller.value = userData.role === 'admin' || userData.role === 'seller';
    
    if (isAdminOrSeller.value) {
      toast('warning', 'Tài khoản admin và seller không thể đặt hàng. Vui lòng sử dụng tài khoản khách hàng.');
    }
  } catch (err) {
    console.error('Error checking user role:', err);
    // Không hiển thị toast error ở đây vì có thể user chưa đăng nhập
  }
};

const checkSellersStatus = async () => {
  try {
    const { sellerStatuses: statuses, bannedSellers: banned } = await checkAllSellersStatus();
    sellerStatuses.value = statuses;
    bannedSellers.value = banned;
    hasBannedSellers.value = banned.length > 0;
    
    if (hasBannedSellers.value) {
      const bannedStoreNames = banned.map(s => s.store_name).join(', ');
      toast('warning', `Các cửa hàng sau đã bị cấm: ${bannedStoreNames}. Không thể đặt hàng.`);
    }
  } catch (err) {
    console.error('Error checking sellers status:', err);
    // Không hiển thị toast error ở đây
  }
};

const loadSelectedAddress = async () => {
  try {
    await loadProvinces();
    const address_id = route.query.address_id;
    const token = localStorage.getItem('access_token');

    if (!token) {
      toast('error', 'Vui lòng đăng nhập để chọn địa chỉ');
      return;
    }

    let res;
    if (address_id) {
      res = await axios.get(`${apiBase}/address/${address_id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      selectedAddress.value = res.data?.data || null;
    } else {
      res = await axios.get(`${apiBase}/address`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const addresses = res.data?.data || [];
      selectedAddress.value = addresses.find(addr => addr.is_default === 1) || addresses[0] || null;
    }

    if (selectedAddress.value) {
      selectedAddress.value = {
        id: selectedAddress.value.id,
        user_id: selectedAddress.value.user_id,
        name: selectedAddress.value.name,
        phone: selectedAddress.value.phone,
        province_id: selectedAddress.value.province_id,
        district_id: selectedAddress.value.district_id,
        ward_code: selectedAddress.value.ward_code,
        detail: selectedAddress.value.detail,
        is_default: selectedAddress.value.is_default,
        address_type: selectedAddress.value.address_type,
      };
      await loadDistricts(selectedAddress.value.province_id);
      await loadWards(selectedAddress.value.district_id);
      await loadShippingFees();
    } else {
      console.warn('No suitable shipping address found');
    }
  } catch (err) {
    console.error('Error loading address:', err);
    toast('error', 'Không thể tải địa chỉ giao hàng');
  }
};



// Handle place order with loading state
const handlePlaceOrder = async () => {
  // Kiểm tra role trước khi đặt hàng
  if (isAdminOrSeller.value) {
    toast('warning', 'Tài khoản admin và seller không thể đặt hàng. Vui lòng sử dụng tài khoản khách hàng.');
    return;
  }

  // Kiểm tra sellers bị cấm trước khi đặt hàng
  if (hasBannedSellers.value) {
    const bannedStoreNames = bannedSellers.value.map(s => s.store_name).join(', ');
    toast('warning', `Không thể đặt hàng vì các cửa hàng sau đã bị cấm: ${bannedStoreNames}`);
    return;
  }

  orderLoading.value = true;
  try {
    await placeOrder();
    toast('success', 'Đặt hàng thành công!');
  } catch (err) {
    console.error('Error placing order:', err);
    toast('error', 'Lỗi khi đặt hàng: ' + (err.message || 'Vui lòng thử lại'));
  } finally {
    orderLoading.value = false;
  }
};

watch(error, (val) => {
  if (val) toast('error', val);
});
watch(paymentError, (val) => {
  if (val) toast('error', val);
});
watch(discountError, (val) => {
  if (val) toast('error', val);
});

watch(selectedAddress, async (newAddress) => {
  if (newAddress && newAddress.district_id && newAddress.ward_code) {
    if (window.addressChangeTimeout) {
      clearTimeout(window.addressChangeTimeout);
    }
    window.addressChangeTimeout = setTimeout(async () => {
      await loadShippingFees();
    }, 500);
  }
}, { deep: true });

watch(cartItems, (newVal) => {
  const hasShippingFeeChanges = newVal.some(s => s.shipping_fee > 0);
  if (hasShippingFeeChanges) {
    console.log('cartItems updated with shipping fees:', newVal.map(s => ({
      seller_id: s.seller_id,
      shipping_fee: s.shipping_fee,
      service_id: s.service_id
    })));
  }
}, { deep: true });

watch(selectedShippingMethod, (newVal) => {
  if (newVal) {
    console.log('Selected shipping method in checkout.vue:', newVal);
  }
});

// Lắng nghe sự kiện khi admin discount bị huỷ hoặc được áp dụng
onMounted(() => {
  const handleAdminDiscountRemoved = (event) => {
    const { discountId, discount } = event.detail;
    recalculateAllShopDiscounts();
  };

  const handleAdminDiscountApplied = (event) => {
    const { discountId, discount } = event.detail;
    recalculateAllShopDiscounts();
  };

  window.addEventListener('adminDiscountRemoved', handleAdminDiscountRemoved);
  window.addEventListener('adminDiscountApplied', handleAdminDiscountApplied);

  // Cleanup khi component unmount
  onUnmounted(() => {
    window.removeEventListener('adminDiscountRemoved', handleAdminDiscountRemoved);
    window.removeEventListener('adminDiscountApplied', handleAdminDiscountApplied);
  });
});

onMounted(async () => {
  try {
    checkoutPerformance.start();
    console.time('checkout-load');
    await selectStoreItems();
    await fetchPaymentMethods();

    // Kiểm tra role của user
    await checkUserRole();

    // Kiểm tra trạng thái sellers
    await checkSellersStatus();

    discountLoading.value = true;
    // Chỉ lấy voucher đã lưu của user, sau đó lọc ra voucher ADMIN để hiển thị
    await fetchMyVouchers().catch(err => {
      console.error('Error fetching my vouchers:', err);
    });

    const seen = new Set();
    publicDiscounts.value = (publicDiscounts.value || []).filter(d => {
      const key = d.id || `${d.code}-${d.name}-${d.discount_type}`;
      return !seen.has(key) && seen.add(key) && !d.seller_id && ['shipping_fee', 'percentage', 'fixed'].includes(d.discount_type);
    });

    discountLoading.value = false;

    await loadSelectedAddress();
    await checkCodEligibility();

    checkoutPerformance.markMilestone('Data loaded');
    console.timeEnd('checkout-load');
    checkoutPerformance.end();
  } catch (err) {
    console.error('Error during checkout load:', err);
    toast('error', 'Lỗi khi tải dữ liệu thanh toán');
  }
});
</script>

<style scoped>
.form-radio {
  @apply text-blue-600 focus:ring-blue-500;
}

/* Custom animations for loading overlay */
@keyframes float {

  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-10px);
  }
}

@keyframes shimmer {
  0% {
    background-position: -200px 0;
  }

  100% {
    background-position: calc(200px + 100%) 0;
  }
}

.animate-float {
  animation: float 2s ease-in-out infinite;
}

.animate-shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200px 100%;
  animation: shimmer 1.5s infinite;
}

/* Enhanced button hover effects */
button:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Loading overlay backdrop blur */
.loading-backdrop {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
</style>