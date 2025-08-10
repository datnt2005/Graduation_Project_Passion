<template>
  <div class="bg-[#F8F9FF] text-gray-700">
    <!-- Loading Overlay -->
    <div v-if="isPlacingOrder" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center loading-backdrop">
      <div class="bg-white rounded-2xl shadow-2xl p-10 flex flex-col items-center space-y-6 max-w-md mx-4 relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 opacity-50"></div>
        
        <!-- Animated Shopping Cart Icon -->
        <div class="relative z-10">
          <svg class="w-16 h-16 text-blue-600 animate-float" fill="currentColor" viewBox="0 0 24 24">
            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
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
          <div class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-green-500 rounded-full animate-spin" style="animation-duration: 1.5s"></div>
          <div class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-yellow-500 rounded-full animate-spin" style="animation-duration: 2s"></div>
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
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
        </div>
        
        <!-- Bottom decoration -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-green-500 to-yellow-500"></div>
      </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col lg:flex-row gap-6">
      <main class="flex-1 overflow-y-hidden" :class="{ 'opacity-50 pointer-events-none': isAccountBanned || isPlacingOrder }">
        <!-- Thông báo khi tài khoản bị khóa hoặc không thể dùng COD -->
        <div v-if="isAccountBanned || (!canUseCod && !isAccountBanned && rejectedOrdersCount >= 2)" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <template v-if="isAccountBanned">
            Tài khoản của bạn đã bị khóa do có quá nhiều đơn hàng bị từ chối nhận. Vui lòng liên hệ hỗ trợ để biết thêm chi tiết.
          </template>
          <template v-else>
            Bạn không thể sử dụng phương thức thanh toán COD vì có quá nhiều đơn hàng bị từ chối nhận.
          </template>
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
              <ShippingSelector
                :key="isBuyNow ? 'buy-now' : 'from-cart'"
                :address="selectedAddress"
                :cart-items="displayItems"
                :is-buy-now="isBuyNow"
                @update:shopDiscount="handleShopDiscountUpdate"
                @update:shippingDiscount="handleShippingDiscountUpdate"
                @update:totalShippingFee="handleTotalShippingFeeUpdate"
              />

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
                              : (discount.discount_type === 'shipping_fee'
                                ? `Giảm ${formatPrice(Number(discount.discount_value))} đ`
                                : `Giảm ${formatPrice(discount.discount_value)} đ`)
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
                      <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Mã giảm phí vận chuyển</h3>
                        <div v-if="discountLoading" class="text-gray-500 text-sm italic mt-2">Đang tải mã giảm giá...</div>
                        <div v-else-if="uniqueShippingDiscounts.length" class="space-y-3">
                          <div v-for="discount in uniqueShippingDiscounts" :key="discount.id"
                            class="border border-gray-300 rounded-md p-4 hover:border-blue-500 transition duration-200"
                            :class="{ 'opacity-50': total < discount.min_order_value }">
                            <div class="flex justify-between items-center">
                              <div>
                                <p class="font-semibold text-sm text-gray-800">{{ discount.name }}</p>
                                <p class="text-xs text-gray-600">
                                  Giảm {{ formatPrice(Number(discount.discount_value)) }} đ
                                  <span v-if="discount.min_order_value">
                                    | Đơn tối thiểu {{ formatPrice(discount.min_order_value) }} đ
                                  </span>
                                </p>
                                <p class="text-[11px] text-gray-400 mt-1">HSD: {{ formatDate(discount.end_date) }}</p>
                              </div>
                              <div>
                                <button v-if="selectedDiscounts.some(d => d.id === discount.id)"
                                  @click="removeDiscount(discount.id)" class="text-red-500 text-sm hover:underline">
                                  Bỏ chọn
                                </button>
                                <button v-else @click="applyDiscount(discount)"
                                  :disabled="total < discount.min_order_value"
                                  class="text-blue-600 text-sm hover:underline">
                                  Áp dụng
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div v-else class="text-gray-500 text-sm italic mt-2">Không có mã phù hợp</div>
                      </div>
                      <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Mã giảm giá sản phẩm</h3>
                        <div v-if="discountLoading" class="text-gray-500 text-sm italic mt-2">Đang tải mã giảm giá...</div>
                        <div v-else-if="publicDiscounts.filter(d => d.discount_type !== 'shipping_fee' && d.seller_id === null).length"
                          class="space-y-3">
                          <div v-for="discount in publicDiscounts.filter(d => d.discount_type !== 'shipping_fee' && d.seller_id === null)"
                            :key="discount.id"
                            class="border border-gray-300 rounded-md p-4 hover:border-blue-500 transition duration-200"
                            :class="{ 'opacity-50': total < discount.min_order_value }">
                            <div class="flex justify-between items-center">
                              <div>
                                <p class="font-semibold text-sm text-gray-800">{{ discount.name }}</p>
                                <p class="text-xs text-gray-600">
                                  {{ discount.discount_type === 'percentage'
                                    ? `Giảm ${Math.round(discount.discount_value)}%`
                                    : `Giảm ${formatPrice(Number(discount.discount_value))} đ` }}
                                  <span v-if="discount.min_order_value">
                                    | Đơn tối thiểu {{ formatPrice(discount.min_order_value) }} đ
                                  </span>
                                </p>
                                <p class="text-[11px] text-gray-400 mt-1">HSD: {{ formatDate(discount.end_date) }}</p>
                              </div>
                              <div>
                                <button v-if="selectedDiscounts.some(d => d.id === discount.id)"
                                  @click="removeDiscount(discount.id)" class="text-red-500 text-sm hover:underline">
                                  Bỏ chọn
                                </button>
                                <button v-else @click="applyDiscount(discount)"
                                  :disabled="total < discount.min_order_value"
                                  class="text-blue-600 text-sm hover:underline">
                                  Áp dụng
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div v-else class="text-gray-500 text-sm italic mt-2">Không có mã phù hợp</div>
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
                        {{ item.productVariant.attributes.map(attr => attr.value).join(', ') }}
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
                  <button @click="handlePlaceOrder"
                    class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-4 rounded-lg font-bold text-base hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg hover:shadow-xl"
                    :disabled="!displayItems.length || loading || isAccountBanned || isPlacingOrder">
                    <span v-if="isPlacingOrder" class="flex items-center justify-center">
                      <!-- Animated shopping cart icon -->
                      <svg class="w-5 h-5 mr-2 animate-bounce" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                      </svg>
                      <!-- Loading dots -->
                      <div class="flex space-x-1 mr-2">
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                      </div>
                      Đang xử lý đơn hàng...
                    </span>
                    <span v-else class="flex items-center justify-center">
                      <!-- Shopping cart icon -->
                      <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
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
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { useRoute, useRuntimeConfig } from '#app';
import axios from 'axios';
import Swal from 'sweetalert2';
import SelectedAddress from '~/components/shared/SelectedAddress.vue';
import ShippingSelector from '~/components/shared/ShippingSelector.vue';
import { useCheckout } from '~/composables/useCheckout';
import { useDiscount } from '~/composables/useDiscount';
import { checkoutPerformance, shippingPerformance } from '~/utils/performance';
import { useHead } from '#imports'

useHead({
  title: 'Thanh toán',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const route = useRoute();

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
const orderLoading = ref(false);

const {
  cartItems,
  buyNowItems,
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
  discounts,
  selectedDiscounts,
  discountLoading,
  discountError,
  selectedPaymentMethod,
  fetchPaymentMethods,
  applyDiscount,
  removeDiscount,
  getPaymentMethodLabel,
  placeOrder,
  selectStoreItems,
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
  shopServiceIds,
  getProductDiscountPerShop,
  removeShopDiscount,
  recalculateAllShopDiscounts,
  canUseCod,
  formatPrice
} = useCheckout(shippingRef, selectedShippingMethod, selectedAddress, storeNotes);

const { fetchMyVouchers, fetchDiscounts: fetchPublicDiscounts, fetchSellerDiscounts, discounts: publicDiscounts } = useDiscount();

const displayItems = computed(() => (isBuyNow.value ? buyNowItems.value : cartItems.value));
const displayShopCount = computed(() => displayItems.value.length);
const displayProductCount = computed(() =>
  displayItems.value.reduce((sum, shop) => sum + (shop.items?.reduce((s, i) => s + (i.quantity || 0), 0) || 0), 0)
);

const uniqueShippingDiscounts = computed(() => {
  const seen = new Set();
  return (publicDiscounts.value || []).filter(d => {
    if (d.discount_type === 'shipping_fee' && d.seller_id === null && !seen.has(d.id)) {
      seen.add(d.id);
      return true;
    }
    return false;
  });
});

const handleTotalShippingFeeUpdate = (newTotal) => {
  // Không cần set lại; realShippingFee lấy trực tiếp từ composable.
  console.log('[PARENT] total shipping emitted:', newTotal);
};

const handleShopDiscountUpdate = async (data) => {
  if (!data?.sellerId) return;
  if (data.action === 'remove') {
    removeShopDiscount(data.sellerId);
  } else {
    await updateShopDiscount(data.sellerId, data.discount, data.discountId);
  }
};

const handleShippingDiscountUpdate = (discountData) => {
  console.log('Shipping discount update:', discountData);
  // Nếu muốn ghi ngược vào cart.value.stores thì xử lý ở đây
};

const addNewCard = () => {
  toast('info', 'Chức năng thêm thẻ mới chưa được triển khai');
};

const loadProvinces = async () => {
  try {
    const res = await axios.get(`${apiBase}/ghn/provinces`);
    provinces.value = res.data.data || [];
  } catch {
    toast('error', 'Không thể tải danh sách tỉnh/thành');
  }
};

const loadDistricts = async (province_id) => {
  try {
    const res = await axios.post(`${apiBase}/ghn/districts`, { province_id });
    districts.value = res.data.data || [];
  } catch {
    toast('error', 'Không thể tải danh sách quận/huyện');
  }
};

const loadWards = async (district_id) => {
  try {
    const res = await axios.post(`${apiBase}/ghn/wards`, { district_id });
    wards.value = res.data.data || [];
  } catch {
    toast('error', 'Không thể tải danh sách phường/xã');
  }
};

const updateSelectedAddress = async (newAddress) => {
  selectedAddress.value = newAddress;
  if (newAddress?.province_id && newAddress?.district_id) {
    await loadDistricts(newAddress.province_id);
    await loadWards(newAddress.district_id);
    if (newAddress.ward_code) await loadShippingFees();
  }
};

const loadSelectedAddress = async () => {
  try {
    await loadProvinces();
    const address_id = route.query.address_id;
    const token = localStorage.getItem('access_token');
    if (!token) return toast('error', 'Vui lòng đăng nhập để chọn địa chỉ');

    let res;
    if (address_id) {
      res = await axios.get(`${apiBase}/address/${address_id}`, { headers: { Authorization: `Bearer ${token}` } });
      selectedAddress.value = res.data?.data || null;
    } else {
      res = await axios.get(`${apiBase}/address`, { headers: { Authorization: `Bearer ${token}` } });
      const addresses = res.data?.data || [];
      selectedAddress.value = addresses.find(a => a.is_default === 1) || addresses[0] || null;
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
    }
  } catch (err) {
    console.error('Lỗi khi tải địa chỉ:', err);
    toast('error', 'Không thể tải địa chỉ giao hàng');
  }
};

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return `${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}/${d.getFullYear()}`;
};

const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top',
    icon,
    title,
    width: '350px',
    padding: '10px 20px',
    customClass: { popup: 'text-sm rounded-md shadow-md' },
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
  });
};

const handlePlaceOrder = async () => {
  orderLoading.value = true;
  try {
    await placeOrder();
  } catch (err) {
    console.error('Error placing order:', err);
    toast('error', 'Lỗi khi đặt hàng');
  } finally {
    orderLoading.value = false;
  }
};

watch(error, (val) => val && toast('error', val));
watch(paymentError, (val) => val && toast('error', val));
watch(discountError, (val) => val && toast('error', val));

watch(selectedAddress, async (addr) => {
  if (addr?.district_id && addr?.ward_code) {
    if (window.addressChangeTimeout) clearTimeout(window.addressChangeTimeout);
    window.addressChangeTimeout = setTimeout(async () => {
      await loadShippingFees();
    }, 400);
  }
}, { deep: true });

watch(displayItems, (list) => {
  const hasFee = list.some(s => (s.shipping_fee || 0) > 0);
  if (hasFee) {
    console.log('[PARENT] displayItems got fees:', list.map(s => ({
      seller_id: s.seller_id,
      fee: s.shipping_fee,
      service_id: s.service_id,
    })));
  }
}, { deep: true });

// log để thấy khi phí ship sẵn sàng (đỡ nghi ngờ template)
watch(realShippingFee, (v) => {
  console.log('[PARENT] realShippingFee updated =', v);
});

onMounted(async () => {
  try {
    checkoutPerformance.start();
    console.time('checkout-load');

    const loadPromises = [
      !isBuyNow.value ? selectStoreItems() : Promise.resolve(),
      fetchPaymentMethods(),
      fetchPublicDiscounts(),
      fetchMyVouchers(),
      loadSelectedAddress(),
      checkCodEligibility(),
    ];
    await Promise.all(loadPromises);

    console.timeEnd('checkout-load');
    checkoutPerformance.end();

    const shippingStats = shippingPerformance.getSummary?.();
    if (shippingStats?.totalCalculations > 0) console.log('📊 Shipping Perf:', shippingStats);
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
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

@keyframes shimmer {
  0% { background-position: -200px 0; }
  100% { background-position: calc(200px + 100%) 0; }
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
