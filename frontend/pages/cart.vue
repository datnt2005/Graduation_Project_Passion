<template>
  <div class="bg-[#f8f9fc] font-sans text-[14px] text-[#222222]">
    <div
      class="max-w-[1200px] mx-auto px-4 py-4 flex flex-col md:flex-row md:space-x-4"
    >
    <!-- Left main content -->
    <div class="flex-1">
        <!-- Breadcrumb -->
               <div class="w-full max-w-6xl mb-4">
                 <div class="text-sm text-gray-500  px-4 py-2 rounded ">
                   <span class="text-gray-400">Trang chủ</span>
                   <span class="mx-1">›</span>
                   <span class="text-black font-medium">Giỏ hàng</span>
                 </div>
               </div>
        <h2 class="font-normal text-[22px] mb-3">GIỎ HÀNG</h2>
        <!-- Header row -->
        <div
          class="flex items-center bg-[#f8f9fc] text-[#999999] text-[13px] font-normal rounded-md px-4 py-2 mb-2 select-none"
        >
          <label class="flex items-center space-x-2 w-[280px]">
            <input
              class="w-4 h-4 border border-[#d9d9d9] rounded-sm bg-[#1BA0E2] checked:bg-[#1BA0E2] checked:border-transparent"
              type="checkbox"
            />
            <span class="text-[18px]">Tất cả (2 sản phẩm)</span>
          </label>
          <div class="flex-1"></div>
          <div class="w-[90px] text-right text-[16px]">Đơn giá</div>
          <div class="w-[90px] text-center text-[16px]">Số lượng</div>
          <div class="w-[90px] text-right text-[16px]">Thành tiền</div>
          <div class="w-[40px] text-center">
            <i class="fas fa-trash-alt"></i>
          </div>
        </div>
        <!-- Shop 1 -->
        <div class="mb-4 border border-[#f8b77b] rounded-md">
          <div
            class="flex items-center space-x-2 px-4 py-2 border-b border-[#f8b77b] bg-[#ffe0b3]"
          >
            <label
              class="flex items-center space-x-2 text-[13px] font-normal flex-1"
            >
              <input
                checked
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm text-[#007aff]"
                type="checkbox"
              />
              <i class="fas fa-store-alt text-[#666666]"></i>
              <span>AHABOOKS</span>
              <i class="fas fa-chevron-right text-[#666666] text-xs"></i>
            </label>
            <a
              class="text-[#007aff] text-[13px] font-normal hover:underline"
              href="#"
              >Chọn sản phẩm</a
            >
          </div>
          <!-- Product item -->
          <div class="flex items-center px-4 py-3 space-x-4">
            <label class="flex items-center space-x-2 w-[280px]">
              <input
                checked
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm text-[#007aff]"
                type="checkbox"
              />
              <img
                alt="Book cover of Đắc Nhân Tâm (Khổ Lớn) with red background and white text"
                class="w-[60px] h-[60px] object-cover border border-[#d9d9d9] rounded-sm"
                height="60"
                src="https://storage.googleapis.com/a1aa/image/a4db177f-9c15-40cd-7dbe-6c36ed86a753.jpg"
                width="60"
              />
              <div class="flex flex-col text-[13px] font-normal leading-tight">
                <span class="text-[#222222] font-semibold"
                  >Đắc Nhân Tâm (Khổ Lớn)</span
                >
                <div
                  class="flex items-center space-x-1 text-[#999999] text-[12px]"
                >
                  <i class="fas fa-truck"></i>
                  <span>Giao thứ 7, 07/06</span>
                </div>
                <span class="text-[#999999] text-[12px]"
                  >Có thể bọc bằng Bookcare</span
                >
              </div>
            </label>
            <div class="w-[90px] text-right font-semibold text-[14px]">
              86.000₫
            </div>
            <div class="w-[90px] flex justify-center items-center space-x-1">
              <button
                aria-label="Decrease quantity"
                class="border border-[#d9d9d9] rounded-l-sm w-6 h-6 text-[14px] font-semibold text-[#222222]"
              >
                −
              </button>
              <input
                class="w-10 h-6 border-t border-b border-[#d9d9d9] text-center text-[14px] font-semibold text-[#222222] outline-none"
                min="1"
                type="number"
                value="1"
              />
              <button
                aria-label="Increase quantity"
                class="border border-[#d9d9d9] rounded-r-sm w-6 h-6 text-[14px] font-semibold text-[#222222]"
              >
                +
              </button>
            </div>
            <div
              class="w-[90px] text-right text-[#ff3b30] font-semibold text-[14px]"
            >
              86.000₫
            </div>
            <div class="w-[40px] text-center text-[#999999] cursor-pointer">
              <i class="fas fa-trash-alt"></i>
            </div>
          </div>
        </div>
        <!-- Shop 2 (repeated product for demonstration) -->
        <div class="mb-4 border border-[#f8b77b] rounded-md">
          <div
            class="flex items-center space-x-2 px-4 py-2 border-b border-[#f8b77b] bg-[#ffe0b3]"
          >
            <label
              class="flex items-center space-x-2 text-[13px] font-normal flex-1"
            >
              <input
                checked
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm text-[#007aff]"
                type="checkbox"
              />
              <i class="fas fa-store-alt text-[#666666]"></i>
              <span>AHABOOKS</span>
              <i class="fas fa-chevron-right text-[#666666] text-xs"></i>
            </label>
            <a
              class="text-[#007aff] text-[13px] font-normal hover:underline"
              href="#"
              >Chọn sản phẩm</a
            >
          </div>
          <div class="flex items-center px-4 py-3 space-x-4">
            <label class="flex items-center space-x-2 w-[280px]">
              <input
                checked
                class="w-4 h-4 border border-[#d9d9d9] rounded-sm text-[#007aff]"
                type="checkbox"
              />
              <img
                alt="Book cover of Đắc Nhân Tâm (Khổ Lớn) with red background and white text"
                class="w-[60px] h-[60px] object-cover border border-[#d9d9d9] rounded-sm"
                height="60"
                src="https://storage.googleapis.com/a1aa/image/a4db177f-9c15-40cd-7dbe-6c36ed86a753.jpg"
                width="60"
              />
              <div class="flex flex-col text-[13px] font-normal leading-tight">
                <span class="text-[#222222] font-semibold"
                  >Đắc Nhân Tâm (Khổ Lớn)</span
                >
                <div
                  class="flex items-center space-x-1 text-[#999999] text-[12px]"
                >
                  <i class="fas fa-truck"></i>
                  <span>Giao thứ 7, 07/06</span>
                </div>
                <span class="text-[#999999] text-[12px]"
                  >Có thể bọc bằng Bookcare</span
                >
              </div>
            </label>
            <div class="w-[90px] text-right font-semibold text-[14px]">
              86.000₫
            </div>
            <div class="w-[90px] flex justify-center items-center space-x-1">
              <button
                aria-label="Decrease quantity"
                class="border border-[#d9d9d9] rounded-l-sm w-6 h-6 text-[14px] font-semibold text-[#222222]"
              >
                −
              </button>
              <input
                class="w-10 h-6 border-t border-b border-[#d9d9d9] text-center text-[14px] font-semibold text-[#222222] outline-none"
                min="1"
                type="number"
                value="1"
              />
              <button
                aria-label="Increase quantity"
                class="border border-[#d9d9d9] rounded-r-sm w-6 h-6 text-[14px] font-semibold text-[#222222]"
              >
                +
              </button>
            </div>
            <div
              class="w-[90px] text-right text-[#ff3b30] font-semibold text-[14px]"
            >
              86.000₫
            </div>
            <div class="w-[40px] text-center text-[#999999] cursor-pointer">
              <i class="fas fa-trash-alt"></i>
            </div>
          </div>
        </div>
        <!-- Shop 2 promo -->
        <div
          class="flex items-center space-x-2 px-4 py-2 text-[#007aff] text-[13px] font-normal cursor-pointer select-none"
        >
          <i class="fas fa-ticket-alt"></i>
          <span>Thêm mã khuyến mãi của Shop</span>
          <i class="fas fa-chevron-right ml-auto"></i>
        </div>
        <!-- Shop 2 freeship -->
        <div
          class="flex items-center space-x-2 px-4 py-2 border border-[#f0f0f0] rounded-md text-[14px] font-semibold text-[#222222]"
        >
          <i class="fas fa-shipping-fast text-[#2f9e44]"></i>
          <span>Freeship 10k đơn từ 45k, Freeship 25k đơn từ 100k</span>
          <i
            class="fas fa-info-circle text-[#999999] text-[13px] ml-1 cursor-pointer"
            title="Thông tin freeship"
          ></i>
        </div>
      </div>
      <!-- Right sidebar -->
      <div class="w-full md:w-[320px] mt-6 md:mt-0 space-y-4">
        <!-- Delivery info -->
        <div class="bg-white p-4 rounded-md">
          <div class="flex justify-between">
            <span class="font-semibold">Giao tới</span>
            <button
              @click="showSelector = true"
              class="text-blue-600 hover:underline"
            >
              Thay đổi
            </button>
          </div>
         <p class="font-bold">{{ selectedAddress?.name }} - {{ selectedAddress?.phone }}</p>
          <p>{{ selectedAddress?.street }}</p>
          <p>{{ selectedAddress?.full }}</p>
        </div>
        <!-- Address Selector Modal -->
        <AddressSelector
          v-if="showSelector"
          :addresses="addresses"
          @close="showSelector = false"
          @update="setDefaultAddress"
        />
        <!-- Tiki Khuyến Mãi -->
        <div class="bg-white rounded-md p-4 text-[13px] text-[#222222]">
          <div class="flex justify-between items-center mb-3">
            <span class="font-semibold">Tiki Khuyến Mãi</span>
            <span
              class="text-[#999999] text-[12px] font-normal flex items-center space-x-1"
            >
              <span>Có thể chọn 2</span>
              <i class="fas fa-info-circle"></i>
            </span>
          </div>
          <div
            class="flex items-center border border-[#007aff] rounded-md p-2 space-x-3 cursor-pointer select-none"
          >
            <img
              alt="Green icon with white truck and text FreeShip"
              class="w-10 h-10 rounded border border-[#007aff]"
              height="40"
              src="https://storage.googleapis.com/a1aa/image/33c63651-d7bb-4343-ef1f-1ddb7b15dd76.jpg"
              width="40"
            />
            <div class="flex-1 text-[14px] font-semibold text-[#222222]">
              Giảm 10K
            </div>
            <i
              class="fas fa-info-circle text-[#007aff] text-[14px] cursor-pointer"
              title="Thông tin khuyến mãi"
            ></i>
            <button
              class="bg-[#007aff] text-white text-[14px] font-semibold rounded px-4 py-1"
            >
              Bỏ Chọn
            </button>
          </div>
          <a
            class="flex items-center space-x-1 text-[#007aff] text-[18px] font-normal mt-3 hover:underline"
            href="#"
          >
            <i class="fas fa-ticket-alt"></i>
            <span>Mua thêm để freeship 25k cho đơn hàng</span>
            <i class="fas fa-chevron-right"></i>
          </a>
        </div>
        <!-- Total price box -->
        <div
          class="bg-white rounded-md p-4 text-[13px] text-[#222222] border border-[#f0f0f0]"
        >
          <div class="flex justify-between items-center mb-2">
            <span class="text-[#999999]">Tổng tiền hàng</span>
            <span class="font-semibold">172.000₫</span>
          </div>
          <div
            class="flex justify-between items-center mb-1 border-b border-[#f0f0f0] pb-2"
          >
            <span class="font-semibold text-[15px]">Tổng tiền thanh toán</span>
            <span class="font-semibold text-[#ff3b30] text-[18px]"
              >162.000₫</span
            >
          </div>
          <div class="text-[11px] text-[#999999] mb-4">
            (Đã bao gồm VAT nếu có)
          </div>
          <!-- <button
            class="w-full bg-[#ff3b30] text-white font-semibold text-[16px] rounded-md py-3"
          >
            Mua Hàng (2)
          </button> -->
          <NuxtLink
            to="/checkout"
            class="block w-full bg-[#ff3b30] text-white font-semibold text-[16px] rounded-md py-3 text-center"
          >
            Mua Hàng (2)
          </NuxtLink>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import AddressSelector from "../components/shared/AddressSelector.vue";

// Define reactive addresses array
const addresses = ref([
  {
    id: 1,
    name: "Châu Bùi văn",
    phone: "0336841037",
    type: "Nhà",
    street: "Thôn 5 xã iapior huyện chưprong",
    full: "Tỉnh Gialai, Xã Ia Pơr, Huyện Chư Prông, Gia Lai",
    isDefault: true,
  },
  {
    id: 2,
    name: "Châu Bùi văn",
    phone: "0336841037",
    type: "Công ty",
    street: "123 Lê Duẩn, phường Trà Bá",
    full: "Thành phố Pleiku, Gia Lai",
    isDefault: false,
  },
]);

// Reactive variable for selected address
const selectedAddress = ref(
  addresses.value.find((a) => a.isDefault) || addresses.value[0]
);

// Reactive variable to toggle AddressSelector modal
const showSelector = ref(false);

// Function to handle address selection
const setDefaultAddress = (id) => {
  addresses.value.forEach((address) => {
    address.isDefault = address.id === id;
  });

  selectedAddress.value = addresses.value.find((a) => a.id === id);

  showSelector.value = false;
};
</script>

<style>
/* Custom scrollbar for quantity input */
input::-webkit-inner-spin-button,
input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
span {
  font-size: 16px;
}
</style>
