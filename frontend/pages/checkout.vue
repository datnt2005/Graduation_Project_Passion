<template>
    <div class="flex min-h-screen bg-gray-100 justify-center py-8">
        <div class="flex bg-white rounded-lg shadow-xl max-w-6xl w-full overflow-hidden">
            <main class="flex-1 p-8 overflow-y-auto">
                <div class="min-h-full max-w-6xl mx-auto">
                    

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-8">
                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center justify-between">
                                    Chọn hình thức giao hàng
                                    <span class="text-sm font-normal text-red-500">-35K</span>
                                </h3>
                                <div class="space-y-4">
                                    <label
                                        class="flex items-center space-x-3 cursor-pointer p-4 rounded-lg border border-gray-300 hover:border-blue-500 transition-colors duration-200">
                                        <input type="radio" name="shipping-method"
                                            class="form-radio text-blue-600 h-5 w-5" checked>
                                        <span class="text-gray-900 font-medium">Giao tiết kiệm</span>
                                        <span class="text-red-500 ml-auto">-35K</span>
                                    </label>
                                    <div class="p-4 rounded-lg border border-blue-500 bg-blue-50">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <input type="radio" name="shipping-method"
                                                class="form-radio text-blue-600 h-5 w-5" disabled>
                                            <span class="text-blue-800 font-medium">Giao tốc độ 2, trước 19h,
                                                02/06</span>
                                            <span class="ml-auto text-green-600 font-semibold">MIỄN PHÍ</span>
                                        </div>
                                        <div class="flex items-start text-sm text-gray-700">
                                            <img src="https://didongmoi.com.vn/upload/images/product/iphone-15-chinh-hang-cu-2.jpg"
                                                alt="Product Image" class="w-16 h-16 rounded mr-3 flex-shrink-0">
                                            <div>
                                                <p class="font-semibold mb-1">Hộp quà Hộp quà gồm giữ nhiệt Lebentang
                                                    LBL3038, cà men dung tích...</p>
                                                <p class="text-gray-600">SL: 1 <span
                                                        class="ml-2 font-bold text-red-500">299.000 đ</span></p>
                                            </div>
                                        </div>
                                    </div>
                                    <label
                                        class="flex items-center space-x-3 cursor-pointer p-4 rounded-lg border border-gray-300 hover:border-blue-500 transition-colors duration-200">
                                        <input type="radio" name="shipping-method"
                                            class="form-radio text-blue-600 h-5 w-5" disabled>
                                        <img src="https://cdn.icon-icons.com/icons2/2699/PNG/512/lebe_express_logo_icon_164391.png"
                                            alt="Lebe Express Logo" class="w-6 h-6 ml-2">
                                        <span class="text-gray-900 font-medium">Được giao bởi Lebentang</span>
                                    </label>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center mb-4">
                                    <input type="checkbox" id="promo-20k"
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <label for="promo-20k" class="ml-2 text-blue-600 font-medium">Đã giảm 20k</label>
                                    <svg class="w-4 h-4 text-blue-600 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <h3 class="text-xl font-bold text-gray-800 mb-4">Chọn hình thức thanh toán</h3>
                                <div class="space-y-4">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-method"
                                            class="form-radio text-blue-600 h-5 w-5" checked>
                                        <span class="text-gray-900">Thanh toán tiền mặt</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-method"
                                            class="form-radio text-blue-600 h-5 w-5">
                                        <img src="https://upload.wikimedia.org/wikipedia/vi/f/f6/Logo_Viettel_Money.png"
                                            alt="Viettel Money" class="h-6 w-auto">
                                        <span class="text-gray-900">Viettel Money</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-method"
                                            class="form-radio text-blue-600 h-5 w-5">
                                        <img src="https://img.mservice.io/image/cc8f4a38dfc6f1a8e100f13c690234a9"
                                            alt="MoMo" class="h-6 w-auto">
                                        <span class="text-gray-900">Ví MoMo</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-method"
                                            class="form-radio text-blue-600 h-5 w-5">
                                        <img src="https://cdn.zalopay.com.vn/zlp/images/logo/logo-square_256px.png"
                                            alt="ZaloPay" class="h-6 w-auto">
                                        <span class="text-gray-900">Ví ZaloPay</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-method"
                                            class="form-radio text-blue-600 h-5 w-5">
                                        <img src="https://static.vnpay.vn/vnpay_logo.svg" alt="VNPAY"
                                            class="h-6 w-auto">
                                        <span class="text-gray-900">VNPAY - Quét mã QR từ ứng dụng ngân hàng</span>
                                    </label>
                                    <div class="flex items-center space-x-3 cursor-not-allowed text-gray-500">
                                        <input type="radio" name="payment-method" class="form-radio h-5 w-5" disabled>
                                        <span class="text-gray-500">Thẻ tín dụng/Ghi nợ</span>
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png"
                                            alt="Visa" class="h-4 w-auto ml-2 opacity-50">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png"
                                            alt="Mastercard" class="h-4 w-auto ml-1 opacity-50">
                                        <button
                                            class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm ml-auto cursor-not-allowed"
                                            disabled>
                                            Thêm thẻ mới
                                        </button>
                                    </div>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <h3 class="text-xl font-bold text-gray-800 mb-4">Ưu đãi thanh toán khác</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Freeship</p>
                                        <p class="text-xs text-gray-600">Thẻ starcard Platinum</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Freeship</p>
                                        <p class="text-xs text-gray-600">Thẻ starcard Standard</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 30K</p>
                                        <p class="text-xs text-gray-600">từ 500K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 20K</p>
                                        <p class="text-xs text-gray-600">từ 200K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 50K</p>
                                        <p class="text-xs text-gray-600">từ 300K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 70K</p>
                                        <p class="text-xs text-gray-600">từ 500K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 150K</p>
                                        <p class="text-xs text-gray-600">từ 1 triệu</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 50K</p>
                                        <p class="text-xs text-gray-600">từ 300K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 70K</p>
                                        <p class="text-xs text-gray-600">từ 500K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col items-center text-center">
                                        <img src="https://img.icons8.com/color/48/000000/discount.png"
                                            alt="Discount Icon" class="w-10 h-10 mb-2">
                                        <p class="font-semibold text-gray-800 mb-1">Giảm 100K</p>
                                        <p class="text-xs text-gray-600">từ 700K</p>
                                        <p class="text-xs text-red-500 font-medium">Không giới hạn</p>
                                        <div class="flex items-center mt-2 text-gray-500">
                                            <img src="https://d1nhio0ox7pgb.cloudfront.net/_img/g_collection_png/standard/512x512/bank_building.png"
                                                alt="Bank" class="w-4 h-4 mr-1">
                                            <span class="text-xs">Ohabank</span>
                                            <svg class="w-3 h-3 ml-auto cursor-pointer" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="payment-bank"
                                            class="form-radio text-blue-600 h-5 w-5">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/ed/Bank_of_America_Logo.svg"
                                            alt="The ATM" class="h-6 w-auto">
                                        <span class="text-gray-900">The ATM</span>
                                    </label>
                                    <p class="text-sm text-gray-600 ml-8">Hỗ trợ Internet Banking</p>
                                </div>
                            </section>
                        </div>

                        <div class="lg:col-span-1 space-y-8">
                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Giao tới</h3>
                                    <a href="#" class="text-blue-600 text-sm font-medium">Thay đổi</a>
                                </div>
                                <div class="space-y-1 text-sm text-gray-700">
                                    <p class="font-semibold">Trần tiến phát - 0969001733</p>
                                    <p>Số nhà 234 Thôn 2, Xã Tâm Thắng, Huyện Cư Jút, Đắk Nông</p>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Khuyến mãi</h3>
                                    <a href="#" class="text-blue-600 text-sm font-medium">Có thể chọn 2</a>
                                    <svg class="w-4 h-4 text-gray-500 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center p-3 border border-gray-300 rounded-lg">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6sF5z5q0V0f7G6J0F4Z0F7G6J0F4Z0F7G6J0F4Z0F7G6J0F4Z0F7G6J0F4Z0F&s"
                                            alt="XTRA Voucher" class="w-8 h-8 rounded-full mr-3">
                                        <span class="text-gray-900 font-medium">Giảm 70K</span>
                                        <button
                                            class="ml-auto px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-md hover:bg-blue-200">Bỏ
                                            chọn</button>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="apply-promo"
                                            class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                        <label for="apply-promo" class="ml-2 text-blue-600 font-medium">Chọn hoặc nhập
                                            mã khác</label>
                                        <svg class="w-4 h-4 text-blue-600 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500">Thứ ngày: Giảm giá cho đơn hàng bằng ASA</p>

                                    <div class="space-y-3 mt-4">
                                        <div class="flex items-center text-gray-400">
                                            <input type="checkbox" disabled
                                                class="form-checkbox h-5 w-5 rounded cursor-not-allowed">
                                            <span class="ml-2">Chưa áp dụng giảm giá</span>
                                            <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 ml-7">Ví dụ: Chưa đủ số lượng kho</p>

                                        <div class="flex items-center text-gray-400">
                                            <input type="checkbox" disabled
                                                class="form-checkbox h-5 w-5 rounded cursor-not-allowed">
                                            <span class="ml-2">Chưa áp dụng giảm giá</span>
                                            <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 ml-7">Ví dụ: Chỉ đủ điều kiện tối thiểu 1000 xu
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center">
                                    <input type="checkbox" id="request-invoice"
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <label for="request-invoice" class="ml-2 text-gray-900 font-medium">Yêu cầu hoá
                                        đơn</label>
                                </div>
                                <p class="text-sm text-gray-500 mt-2 ml-7">Ví dụ: Trading chỉ xuất hoá đơn điện tử</p>
                            </section>

                            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">Đơn hàng</h3>
                                    <a href="#" class="text-blue-600 text-sm font-medium">Thay đổi</a>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <span class="text-gray-900 font-medium mr-2">1 x</span>
                                        <img src="https://didongmoi.com.vn/upload/images/product/iphone-15-chinh-hang-cu-2.jpg"
                                            alt="Product Image" class="w-10 h-10 rounded mr-3 flex-shrink-0">
                                        <div>
                                            <p class="text-gray-900">Hộp quà gồm giữ nhiệt Lebentang LBL3038, cà men
                                                dung tích 700ml, c...</p>
                                        </div>
                                        <span class="ml-auto text-gray-900 font-semibold">299.000 đ</span>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2 text-sm">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Tổng tiền hàng:</span>
                                        <span>429.000đ</span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span>Phí vận chuyển:</span>
                                        <span>36.500đ</span>
                                    </div>
                                    <div class="flex justify-between text-red-600 font-medium">
                                        <span>Giảm giá trực tiếp:</span>
                                        <span>-130.000đ</span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span>Mã khuyến mại từ nhà bán:</span>
                                        <span>-20.000đ</span>
                                    </div>
                                    <div class="flex justify-between text-red-600 font-medium">
                                        <span>Giảm vận chuyển:</span>
                                        <span>-36.500đ</span>
                                    </div>
                                    <div
                                        class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-300 pt-3">
                                        <span>Tổng tiền thanh toán:</span>
                                        <span class="text-red-600">299.000 đ</span>
                                    </div>
                                    <p class="text-xs text-gray-500 italic text-right">
                                        (Giá này đã bao gồm thuế GTGT, phí đóng gói, phí vận chuyển và các chi phí phát
                                        sinh khác)
                                    </p>
                                </div>

                                <div class="mt-6">
                                    <button
                                        class="w-full bg-red-500 text-white py-3 rounded-lg font-bold text-lg hover:bg-red-700 transition duration-200">
                                        Đặt hàng
                                    </button>
                                </div>
                            </section>

                            <div class="flex flex-col space-y-4">
                                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="request-invoice"
                                            class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                        <label for="request-invoice" class="ml-2 text-gray-900 font-medium">Yêu cầu
                                            hoá
                                            đơn</label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2 ml-7">Ví dụ: Trading chỉ xuất hoá đơn điện tử
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
export default {
    name: "checkout"
}
</script>

<style scoped></style>
