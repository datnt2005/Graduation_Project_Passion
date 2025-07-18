<template>
  <div class="max-w-[1200px] mx-auto py-6">
    <div class="bg-white p-4 sm:p-6 shadow min-h-[160px]">
      <!-- Tiêu đề + Countdown -->
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-3 sm:space-y-0"
      >
        <!-- FLASH SALE title -->
        <div
          class="flex items-center text-[#1BA0E2] text-sm sm:text-base font-semibold uppercase tracking-wide"
        >
          <span>FLASH</span>
          <svg
            class="w-4 h-4 sm:w-5 sm:h-5 mx-1 -mt-[2px]"
            fill="#189EFF"
            viewBox="0 0 24 24"
          >
            <path d="M7 2L3 14h5l-1 8 11-12h-5l1-8z" />
          </svg>
          <span>SALE</span>
        </div>

        <!-- Countdown -->
        <div
          class="flex space-x-2 text-white font-bold text-xs sm:text-sm justify-center sm:justify-start"
        >
          <div class="bg-[#189EFF] rounded-md px-2 sm:px-3 py-1 shadow-md">
            <span>{{ hours }}</span>
          </div>
          <div class="bg-[#189EFF] rounded-md px-2 sm:px-3 py-1 shadow-md">
            <span>{{ minutes }}</span>
          </div>
          <div class="bg-[#189EFF] rounded-md px-2 sm:px-3 py-1 shadow-md">
            <span>{{ seconds }}</span>
          </div>
        </div>

        <!-- Xem tất cả -->
        <a
          href="#"
          class="text-[#189EFF] text-xs sm:text-sm font-medium hover:underline text-right sm:text-left"
        >
          Xem tất cả &gt;
        </a>
      </div>

      <!-- Danh sách sản phẩm -->
      <div
        class="flex gap-4 overflow-x-auto pb-2"
        :class="{ 'overflow-x-auto': items.length > 6 }"
      >
        <div
          v-for="(item, index) in items"
          :key="index"
          class="flex-shrink-0 w-[160px]"
        >
          <img
            :src="item.image"
            :alt="'Flash sale item ' + (index + 1)"
            class="w-full h-[160px] object-cover rounded-md"
          />
          <div class="mt-2 text-center">
            <div class="text-[#189EFF] text-sm font-semibold">
              {{ item.price }}
            </div>
            <button
              class="mt-1 bg-gradient-to-r from-[#189EFF] to-[#4CCEFF] rounded-full text-white text-[11px] font-bold px-3 py-1"
            >
              ĐANG BÁN CHẠY
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Đồng hồ đếm ngược
const hours = ref("00");
const minutes = ref("00");
const seconds = ref("00");
const countdownTime = new Date().getTime() + 3600000;

function updateCountdown() {
  const now = new Date().getTime();
  const distance = countdownTime - now;

  if (distance > 0) {
    hours.value = Math.floor((distance / (1000 * 60 * 60)) % 24)
      .toString()
      .padStart(2, "0");
    minutes.value = Math.floor((distance / (1000 * 60)) % 60)
      .toString()
      .padStart(2, "0");
    seconds.value = Math.floor((distance / 1000) % 60)
      .toString()
      .padStart(2, "0");
  } else {
    hours.value = minutes.value = seconds.value = "00";
  }
}

onMounted(() => {
  updateCountdown();
  setInterval(updateCountdown, 1000);
});

// Danh sách sản phẩm mẫu
const items = ref([
  {
    image:
      "https://storage.googleapis.com/a1aa/image/6d8404d1-0ecf-48b0-b72f-a26de82e1dfe.jpg",
    price: "₫ 2.330.000",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/48ce0773-8129-4ef5-5ab0-cefc9ada5909.jpg",
    price: "₫ 948.000",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/7c630f2a-bfa0-4eda-4de8-665a87b0e157.jpg",
    price: "₫ 429.000",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/7060e26e-f3b9-4904-c074-22c9b982620b.jpg",
    price: "₫ 126.650",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/7060e26e-f3b9-4904-c074-22c9b982620b.jpg",
    price: "₫ 126.650",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/7060e26e-f3b9-4904-c074-22c9b982620b.jpg",
    price: "₫ 126.650",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/7060e26e-f3b9-4904-c074-22c9b982620b.jpg",
    price: "₫ 126.650",
  },
  {
    image:
      "https://storage.googleapis.com/a1aa/image/7060e26e-f3b9-4904-c074-22c9b982620b.jpg",
    price: "₫ 126.650",
  },
]);
</script>
