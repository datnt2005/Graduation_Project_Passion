<template>
    <div  class="bg-[#F8F9FF] text-gray-700">
  <div class="max-w-[1200px] mx-auto px-4 py-6 flex flex-col lg:flex-row gap-6">
    
            <main class="flex-1 p-8 overflow-y-hidden">
                 <div class="w-full max-w-6xl mb-4">
                 <div class="text-sm text-gray-500  px-4 py-2 rounded ">
                   <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
                   <span class="mx-1">›</span>
                   <span class="text-black font-medium">Giỏ hàng</span>
                 </div>
               </div>
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

                <div class="min-h-full max-w-6xl mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 space-y-2">
                            <!-- Loading state -->
                            <div v-if="loading" class="flex justify-center items-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>
                            <!-- Trong checkout.vue -->
                            <ShippingSelector ref="shippingRef" :address="selectedAddress"
                                v-model:selectedMethod="selectedShippingMethod" :cart-items="cartItems" />

                           <section class="bg-white rounded-[4px] p-5">
                            <h3 class="text-gray-800 font-semibold text-base mb-2">Chọn hình thức thanh toán</h3>

                            <!-- Loading state -->
                            <div v-if="paymentLoading" class="flex justify-center items-center py-4">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            </div>

                            <!-- Payment methods -->
                            <form v-else class="space-y-6 text-xs text-gray-700 max-w-md">
                            <label v-for="method in paymentMethods" :key="method.id" class="cursor-pointer"
                                    :class="method.name === 'VNPAY' || method.name === 'CREDIT' ? 'flex flex-col gap-1' : 'flex items-center gap-3'">
                                <div class="flex items-center gap-3">
                                <input class="w-4 h-4 text-blue-600 border-blue-600 focus:ring-blue-500 accent-blue-600" 
                                        type="radio" 
                                        name="payment" 
                                        :value="method.name" 
                                        
                                        v-model="selectedPaymentMethod" />
                                
                                <!-- Payment method icons and labels -->
                                <template v-if="method.name === 'COD'">
                                    <i class="fas fa-hand-holding-usd text-[#2A5DB0] text-lg"></i>
                                    <span>Thanh toán tiền mặt</span>
                                </template>
                                <template v-else-if="method.name === 'VIETTEL'">
                                    <img src="https://storage.googleapis.com/a1aa/image/b3807c5a-0b76-4704-69fb-c3ef0c4d99ab.jpg" 
                                        alt="Viettel Money" 
                                        class="w-5 h-5 object-contain" />
                                    <span>Viettel Money</span>
                                </template>
                                <template v-else-if="method.name === 'MOMO'">
                                    <img src="https://storage.googleapis.com/a1aa/image/6db00e7b-8953-4dc4-51f8-3fe0805858d1.jpg" 
                                        alt="Momo" 
                                        class="w-5 h-5 object-contain" />
                                    <span>Ví Momo</span>
                                </template>
                                <template v-else-if="method.name === 'ZALOPAY'">
                                    <img src="https://storage.googleapis.com/a1aa/image/dc336404-6ee8-4fa2-4836-316782a96c00.jpg" 
                                        alt="ZaloPay" 
                                        class="w-5 h-5 object-contain" />
                                    <span>Ví ZaloPay</span>
                                </template>
                                <template v-else-if="method.name === 'VNPAY'">
                                    <img src="https://storage.googleapis.com/a1aa/image/f9093db3-1943-4ac8-c243-b844b9d32c13.jpg" 
                                        alt="VNPAY" 
                                        class="w-5 h-5 object-contain" />
                                    <span>VNPAY</span>
                                </template>
                                <template v-else-if="method.name === 'CREDIT'">
                                    <i class="fas fa-credit-card text-[#2A5DB0] text-lg"></i>
                                    <span>Thẻ tín dụng/ Ghi nợ</span>
                                    <div class="flex gap-1 ml-2">
                                    <img src="https://storage.googleapis.com/a1aa/image/76558095-7f7c-4cd9-ec5d-947d743be711.jpg" 
                                        alt="Tiki" 
                                        class="w-5 h-[12px] object-contain" />
                                    <img src="https://storage.googleapis.com/a1aa/image/c6b52119-c8ce-4e24-831c-180cafb12671.jpg" 
                                        alt="Visa" 
                                        class="w-5 h-[12px] object-contain" />
                                    <img src="https://storage.googleapis.com/a1aa/image/11785e4a-1bd0-4af1-eeee-90375d5f3565.jpg" 
                                        alt="Mastercard" 
                                        class="w-5 h-[12px] object-contain" />
                                    <img src="https://storage.googleapis.com/a1aa/image/1641f402-65a9-4577-8362-46dd9d84b719.jpg" 
                                        alt="JCB" 
                                        class="w-5 h-[12px] object-contain" />
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

                                <!-- Additional info for VNPAY -->
                                <template v-if="method.name === 'VNPAY'">
                                <span class="text-xs text-gray-400 ml-9">Quét Mã QR từ ứng dụng ngân hàng</span>
                                </template>

                                <!-- Additional info for CREDIT -->
                                <template v-if="method.name === 'CREDIT'">
                                <button @click="addNewCard" 
                                        class="ml-9 mt-1 text-[#2A7FDF] border border-[#2A7FDF] rounded px-3 py-1 text-xs font-medium hover:bg-[#E6F0FF]" 
                                        type="button">
                                    + Thêm thẻ mới
                                </button>
                                </template>
                            </label>

                            <!-- Card promotions (shown when CREDIT is selected) -->
                            <div v-if="selectedPaymentMethod === 'CREDIT'" 
                                class="ml-9 mt-4 bg-[#F0F4FF] border border-[#D2E3FC] rounded p-3 grid grid-cols-3 gap-3 text-xs text-gray-700 max-w-[600px]">
                                <div v-for="promo in cardPromotions" :key="promo.id" 
                                    class="border border-[#D2E3FC] rounded p-2 flex flex-col justify-between cursor-pointer hover:shadow-md"
                                    @click="selectPromotion(promo)">
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

                            <section class="bg-white p-6 rounded-[4px] ">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xl font-bold text-gray-800">Ưu đãi thanh toán</h3>
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-600">Chọn để áp dụng</span>
                                        <i class="fas fa-info-circle text-gray-400 ml-2"></i>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template v-for="promotion in promotions" :key="promotion.id">
                                        <div
                                            class="bg-gradient-to-br from-white to-gray-50 p-4 rounded-lg  hover:border-blue-300 hover:shadow-md transition-all duration-300 group">
                                            <div class="flex items-start space-x-4">
                                                <!-- Icon and badge -->
                                                <div class="relative">
                                                    <div
                                                        class="w-12 h-12 flex items-center justify-center bg-blue-50 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                                        <img :src="promotion.icon" :alt="promotion.name"
                                                            class="w-8 h-8">
                                                    </div>
                                                    <div v-if="promotion.badge" :class="getBadgeClass(promotion.badge)"
                                                        class="absolute -top-2 -right-2 px-2 py-1 text-xs text-white rounded-full">
                                                        {{ promotion.badge }}
                                                    </div>
                                                </div>

                                                <!-- Content -->
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between">
                                                        <div>
                                                            <h4 class="font-bold text-gray-800">{{ promotion.name }}
                                                            </h4>
                                                            <p class="text-sm text-gray-600">{{ promotion.description }}
                                                            </p>
                                                        </div>
                                                        <button v-if="!promotion.selected"
                                                            @click="selectPromotion(promotion)"
                                                            class="px-3 py-1 text-sm bg-blue-50 text-blue-600 font-medium rounded hover:bg-blue-100 transition-colors">
                                                            Chọn
                                                        </button>
                                                        <span v-else class="text-green-500">
                                                            <i class="fas fa-check-circle"></i>
                                                        </span>
                                                    </div>

                                                    <!-- Details -->
                                                    <div class="mt-2 flex items-center text-xs text-gray-500 space-x-4">
                                                        <span v-if="promotion.limit">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            {{ promotion.limit }}
                                                        </span>
                                                        <span v-if="promotion.expiry">
                                                            <i class="fas fa-calendar-alt mr-1"></i>
                                                            HSD: {{ formatDate(promotion.expiry) }}
                                                        </span>
                                                    </div>

                                                    <!-- Bank info -->
                                                    <div class="mt-2 flex items-center">
                                                        <img :src="promotion.bankIcon" :alt="promotion.bank"
                                                            class="w-4 h-4 mr-1">
                                                        <span class="text-xs text-gray-600">{{ promotion.bank }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        </div>

                        <div class="lg:col-span-1 space-y-2">
                            <SelectedAddress :address="selectedAddress" :provinces="provinces" :districts="districts"
                                :wards="wards" />
                                <!-- // giảm giá  -->
                            <section class="bg-white p-6 rounded-[4px] shadow-sm">
                              <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                <span class="text-gray-800 font-semibold text-base">Khuyến mãi</span>
                                <span class="text-[13px] text-gray-600 ml-2">(Đã chọn {{ selectedDiscounts.length }}/2)</span>
                                </div>

                                <!-- Danh sách mã giảm giá đã chọn -->
                                <div
                                v-if="selectedDiscounts.length"
                                class="bg-gray-50 border border-dashed border-gray-300 rounded-md p-3 space-y-2"
                                >
                                <div
                                    v-for="discount in selectedDiscounts"
                                    :key="discount.id"
                                    class="flex items-center justify-between bg-white border border-gray-200 rounded px-3 py-2"
                                >
                                    <div>
                                    <p class="text-sm font-semibold text-green-600">{{ discount.name }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ discount.discount_type === 'percentage'
                                        ? `Giảm ${discount.discount_value}%`
                                        : `Giảm ${formatPrice(discount.discount_value)} đ` }}
                                        <span v-if="discount.min_order_value">
                                        (Đơn tối thiểu {{ formatPrice(discount.min_order_value) }})
                                        </span>
                                    </p>
                                    </div>
                                    <button
                                    @click="removeDiscount(discount.id)"
                                    class="text-sm text-red-500 hover:underline"
                                    >
                                    Xóa
                                    </button>
                                </div>
                                </div>
                            </div>

                            <!-- Gợi ý -->
                            <span class="text-[14px] text-gray-500 ml-4 self-start whitespace-nowrap">Có thể chọn 2</span>
                            </div>


                                <!-- Nút mở modal -->
                                <button
                                    @click="showDiscountModal = true"
                                    class="flex items-center gap-2 text-[#2A7FDF] text-[14px] hover:underline"
                                    type="button"
                                >
                                    <i class="fas fa-ticket-alt"></i>
                                    Chọn hoặc nhập mã khác
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>

                                <!-- Modal mã giảm giá -->
                                <div
                                v-if="showDiscountModal"
                                class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center"
                                @click.self="showDiscountModal = false"
                                >
                                <div class="bg-white rounded-[8px] shadow-xl w-full max-w-2xl p-6 relative">
                                    <!-- Header -->
                                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                                    <h2 class="text-lg font-semibold text-gray-800">Chọn mã giảm giá</h2>
                                    <button @click="showDiscountModal = false" class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-times text-base"></i>
                                    </button>
                                    </div>

                                    <!-- Nhập mã thủ công -->
                                    <div class="mb-6">
                                    <label class="block text-sm text-gray-700 mb-1">Nhập mã giảm giá</label>
                                    <div class="flex gap-2">
                                        <input
                                        v-model="manualCode"
                                        type="text"
                                        placeholder="Nhập mã..."
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        />
                                        <button
                                        @click="applyManualDiscount"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-md"
                                        >
                                        Áp dụng
                                        </button>
                                    </div>
                                    </div>

                                    <!-- Danh sách mã giảm giá -->
                                    <div class="space-y-6 max-h-[450px] overflow-y-auto">
                                    <!-- Mã giảm phí vận chuyển -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 mb-2">Mã giảm phí vận chuyển</h3>
                                        <div
                                        v-if="discounts.filter(d => d.discount_type === 'shipping_fee').length"
                                        class="space-y-3"
                                        >
                                        <div
                                            v-for="discount in discounts.filter(d => d.discount_type === 'shipping_fee')"
                                            :key="discount.id"
                                            class="border border-gray-300 rounded-md p-4 hover:border-blue-500 transition duration-200"
                                            :class="{ 'opacity-50': cartTotal < discount.min_order_value }"
                                        >
                                            <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-semibold text-sm text-gray-800">{{ discount.name }}</p>
                                                <p class="text-xs text-gray-600">
                                                Giảm {{ formatPrice(discount.discount_value) }}đ
                                                <span v-if="discount.min_order_value">
                                                    | Đơn tối thiểu {{ formatPrice(discount.min_order_value) }}đ
                                                </span>
                                                </p>
                                                <p class="text-[11px] text-gray-400 mt-1">HSD: {{ formatDate(discount.end_date) }}</p>
                                            </div>
                                            <div>
                                                <button
                                                v-if="selectedDiscounts.some(d => d.id === discount.id)"
                                                @click="removeDiscount(discount.id)"
                                                class="text-red-500 text-sm hover:underline"
                                                >
                                                Bỏ chọn
                                                </button>
                                                <button
                                                v-else
                                                @click="applyDiscount(discount)"
                                                :disabled="cartTotal < discount.min_order_value"
                                                class="text-blue-600 text-sm hover:underline"
                                                >
                                                Áp dụng
                                                </button>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div v-else class="text-gray-500 text-sm italic mt-2">Không có mã phù hợp</div>
                                    </div>

                                    <!-- Mã giảm giá sản phẩm -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 mb-2">Mã giảm giá sản phẩm</h3>
                                        <div
                                        v-if="discounts.filter(d => d.discount_type !== 'shipping_fee').length"
                                        class="space-y-3"
                                        >
                                        <div
                                            v-for="discount in discounts.filter(d => d.discount_type !== 'shipping_fee')"
                                            :key="discount.id"
                                            class="border border-gray-300 rounded-md p-4 hover:border-blue-500 transition duration-200"
                                            :class="{ 'opacity-50': cartTotal < discount.min_order_value }"
                                        >
                                            <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-semibold text-sm text-gray-800">{{ discount.name }}</p>
                                                <p class="text-xs text-gray-600">
                                                {{ discount.discount_type === 'percentage'
                                                    ? `Giảm ${discount.discount_value}%`
                                                    : `Giảm ${formatPrice(discount.discount_value)}đ` }}
                                                <span v-if="discount.min_order_value">
                                                    | Đơn tối thiểu {{ formatPrice(discount.min_order_value) }}đ
                                                </span>
                                                </p>
                                                <p class="text-[11px] text-gray-400 mt-1">HSD: {{ formatDate(discount.end_date) }}</p>
                                            </div>
                                            <div>
                                                <button
                                                v-if="selectedDiscounts.some(d => d.id === discount.id)"
                                                @click="removeDiscount(discount.id)"
                                                class="text-red-500 text-sm hover:underline"
                                                >
                                                Bỏ chọn
                                                </button>
                                                <button
                                                v-else
                                                @click="applyDiscount(discount)"
                                                :disabled="cartTotal < discount.min_order_value"
                                                class="text-blue-600 text-sm hover:underline"
                                                >
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

                               <section class="bg-white rounded-lg p-4 text-xs text-gray-700 border border-[#E6E8F0]">
                                <label class="flex items-start gap-2 cursor-pointer">
                                <input class="w-4 h-4 text-blue-600 border-gray-300 rounded" type="checkbox"/>
                                <div class="flex flex-col">
                                <span class="font-semibold text-gray-800">
                                    Yêu cầu hoá đơn
                                </span>
                                <span class="text-gray-400 text-[11px]">
                                    Passion Trading chỉ xuất hoá đơn điện tử
                                </span>
                                </div>
                                </label>
                            </section>
                           <section class="bg-white rounded-lg p-5 text-sm text-gray-700 border border-gray-200 space-y-4">
                            <!-- Tiêu đề -->
                            <div class="flex justify-between items-center">
                                <h3 class="text-base font-semibold text-gray-900">Thông tin đơn hàng</h3>
                                <NuxtLink to="/cart" class="text-blue-600 text-sm font-medium hover:underline">Thay đổi</NuxtLink>
                            </div>
                            <!-- Nội dung đơn hàng -->
                            <div class="space-y-3">
                                <!-- Sản phẩm -->
                                <div class="text-sm">
                                1 sản phẩm.
                                <NuxtLink to="/cart" class="text-blue-600 hover:underline">Xem chi tiết</NuxtLink>
                                </div>
                                <hr>

                                <!-- Tổng tiền hàng -->
                                <div class="flex justify-between">
                                <span class="text-[14px]">Tổng tiền hàng</span>
                                <span class="text-[14px] text-gray-800">{{ formattedCartTotal }}</span>
                                </div>

                                <!-- Phí vận chuyển -->
                                <div class="flex justify-between">
                                <span class="text-[14px]">Phí vận chuyển</span>
                                <span class="text-[14px] text-gray-800">{{ formattedShippingFee }}</span>
                                </div>
                                 <!-- Giảm giá phí ship -->
                                <div class="flex justify-between">
                                <span class="text-[14px]">Giảm giá phí ship</span>
                                <span class="text-green-600">- {{ formatPrice(getShippingDiscount(cartTotal)) }} đ</span>
                                </div>
                                <!-- Giảm giá -->
                                <div class="flex justify-between">
                                <span class="text-[14px]">Giảm giá</span>
                                <span class="text-green-600">- {{ formatPrice(calculateDiscount(cartTotal)) }} đ</span>
                                </div>

                                <div class="flex justify-between">
                                  
                                <span class="text-[14px]">Phí vận chuyển sau giảm:</span>
                                 <span class="text-green-600">{{ formatPrice(finalShippingFee) }} đ</span>
                                </div>
                             

                                <!-- Tổng tiền thanh toán -->
                                <div class="flex justify-between pt-3 border-t border-gray-200 text-base font-semibold">
                                <span class="text-[14px]">Tổng thanh toán</span>
                                <span class="text-[15px] text-lg">{{ formattedFinalTotal }}</span>
                                </div>

                                <!-- Ghi chú -->
                                <p class="text-xs text-gray-500 italic text-right mt-1 leading-snug">
                                (Giá đã bao gồm thuế GTGT, phí đóng gói, phí vận chuyển và chi phí phát sinh khác)
                                </p>
                            </div>
                            <!-- Nút đặt hàng -->
                            <div class="pt-2">
                                <button
                                @click="placeOrder"
                                class="w-full bg-red-500 text-white py-3 rounded-md font-bold text-base hover:bg-red-600 transition"
                                :disabled="!cartItems.length || loading"
                                >
                                Đặt hàng
                                </button>
                            </div>
                            </section>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Thêm Notification Popup -->
    <ClientOnly>
        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-100" leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95">
                <div v-if="showNotification"
                    class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl  p-4 flex items-center space-x-3 z-50">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useRuntimeConfig } from '#app'
import axios from 'axios'
import Swal from 'sweetalert2'

import SelectedAddress from '../components/shared/SelectedAddress.vue'
import ShippingSelector from '../components/shared/ShippingSelector.vue'
import { useCheckout } from '~/composables/useCheckout'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBaseUrl = config.public.mediaBaseUrl
const route = useRoute()

const shippingRef = ref(null)
const selectedShippingMethod = ref(null)

const manualCode = ref('')

// Địa chỉ
const selectedAddress = ref(null)
const provinces = ref([])
const districts = ref([])
const wards = ref([])


// Giả sử bạn đã có selectedShippingMethod, shippingRef, getShippingDiscount, cartTotal...
const formattedShippingFee = computed(() => {
  const rawFee = shippingRef.value?.fees?.[selectedShippingMethod.value] || '0đ';
  const baseFee = parsePrice(rawFee);
  return formatPrice(baseFee);
}); 



const applyManualDiscount = () => {
  const code = manualCode.value.trim().toUpperCase()
  
  if (!code) {
    toast('warning', 'Vui lòng nhập mã giảm giá')
    return
  }

  // Tìm trong danh sách mã đã có
  const discount = discounts.value.find(d => d.code.toUpperCase() === code)

  if (!discount) {
    toast('error', 'Không tìm thấy mã giảm giá này')
    return
  }

  // Kiểm tra điều kiện đơn hàng tối thiểu
  if (cartTotal.value < discount.min_order_value) {
    toast('error', `Đơn hàng chưa đủ điều kiện (${formatPrice(discount.min_order_value)}) để dùng mã này`)
    return
  }

  // Gọi lại hàm applyDiscount gốc
  applyDiscount(discount)
   manualCode.value = ''
   showDiscountModal.value = false
}

const loadProvinces = async () => {
    const res = await axios.get(`${apiBase}/ghn/provinces`)
    provinces.value = res.data.data
}

const showDiscountModal = ref(false)

const loadDistricts = async (province_id) => {
    const res = await axios.post(`${apiBase}/ghn/districts`, { province_id })
    districts.value = res.data.data || []
}

const loadWards = async (district_id) => {
    const res = await axios.post(`${apiBase}/ghn/wards`, { district_id })
    wards.value = res.data.data || []
}

const loadSelectedAddress = async () => {
    await loadProvinces()
    const address_id = route.query.address_id

    if (address_id) {
        const res = await axios.get(`${apiBase}/address/${address_id}`)
        selectedAddress.value = res.data.data
    } else {
        const userId = 4
        const res = await axios.get(`${apiBase}/address?user_id=${userId}`)
        selectedAddress.value = res.data.data.find(addr => addr.is_default == 1)
    }

    if (selectedAddress.value) {
        await loadDistricts(selectedAddress.value.province_id)
        await loadWards(selectedAddress.value.district_id)
    }
}

// Checkout logic
const {
    cartItems,
    cartTotal,
    loading,
    error,
    paymentMethods,
    paymentLoading,
    paymentError,
    discounts,
    selectedDiscounts,
    discountLoading,
    discountError,
    fetchCart,
    fetchPaymentMethods,
    fetchDiscounts,
    applyDiscount,
    removeDiscount,
    calculateDiscount,
    getShippingDiscount,
    selectedPaymentMethod,
    showNotification,
    notificationMessage,
    showSuccessNotification,
    formatPrice,
    parsePrice,
    finalShippingFee,
    finalTotal,
    formattedFinalTotal,
    formattedCartTotal,
    getPaymentMethodLabel,
    placeOrder
} = useCheckout(config, shippingRef, selectedShippingMethod, selectedAddress)
console.log('[Template] getShippingDiscount: ', getShippingDiscount)
// Ưu đãi thủ công
const promotions = ref([])

const selectPromotion = (promotion) => {
    const selectedCount = promotions.value.filter(p => p.selected).length
    if (selectedCount >= 1 && !promotion.selected) {
        alert('Chỉ được chọn tối đa 1 ưu đãi')
        return
    }
    promotion.selected = !promotion.selected
}
const getBadgeClass = (badge) => {
    const classes = {
        'Hot': 'bg-red-500',
        'New': 'bg-green-500',
        'Best': 'bg-yellow-500',
        'VIP': 'bg-purple-500'
    }
    return classes[badge] || 'bg-gray-500'
}
const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return `${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}/${d.getFullYear()}`
}
// Toast
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
        didOpen: (toastEl) => {
            toastEl.addEventListener('mouseenter', () => Swal.stopTimer())
            toastEl.addEventListener('mouseleave', () => Swal.resumeTimer())
        }
    })
}
// Watch error
watch(error, (val) => { if (val) toast('error', val) })
watch(paymentError, (val) => { if (val) toast('error', val) })
watch(discountError, (val) => { if (val) toast('error', val) })
// Load all onMounted
onMounted(async () => {
    try {
        await Promise.all([
            fetchCart(),
            fetchPaymentMethods(),
            fetchDiscounts(),
            loadSelectedAddress()
        ])
    } catch (err) {
        console.error('Error during checkout load:', err)
        toast('error', 'Lỗi khi tải dữ liệu thanh toán')
    }
    await nextTick()
})
</script>

<style scoped>
.form-radio {
    @apply text-blue-600 focus:ring-blue-500;
}
</style>