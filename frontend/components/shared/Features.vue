<template>
  <div
    class="text-base text-gray-800 px-6 py-3 flex justify-center rounded-lg shadow-sm bg-white"
  >
    <!-- Desktop -->
    <div
      class="hidden md:flex flex-wrap justify-center gap-4 items-center text-xs sm:text-sm md:text-sm lg:text-base"
    >
      <NuxtLink
        to="/commitment"
        class="pl-2 border-l border-gray-300 flex items-center gap-1 max-w-[150px] truncate whitespace-nowrap hover:underline"
      >
        Cam kết
        <i
          class="fa-solid fa-check text-[#1BA9FF] w-4 h-4 text-xs inline-block"
        ></i>
        100% hàng thật
      </NuxtLink>

      <NuxtLink
        to="/commitment"
        class="pl-2 border-l border-gray-300 flex items-center gap-2 whitespace-nowrap hover:underline"
      >
        <i
          class="fa-solid fa-truck-fast text-[#1BA9FF] w-5 h-5 text-base inline-block"
        ></i>
        Freeship mọi đơn
      </NuxtLink>

      <NuxtLink
        to="/commitment"
        class="pl-2 border-l border-gray-300 flex items-center gap-2 whitespace-nowrap hover:underline"
      >
        <i
          class="fa-solid fa-right-left text-[#1BA9FF] w-5 h-5 text-base inline-block"
        ></i>
        Hoàn 200% nếu hàng giả
      </NuxtLink>

      <NuxtLink
        to="/commitment"
        class="pl-2 border-l border-gray-300 flex items-center gap-2 whitespace-nowrap hover:underline"
      >
        <i
          class="fa-solid fa-rotate-left text-[#1BA9FF] w-5 h-5 text-base inline-block"
        ></i>
        30 ngày đổi trả
      </NuxtLink>

      <NuxtLink
        to="/commitment"
        class="pl-2 border-l border-gray-300 flex items-center gap-2 whitespace-nowrap hover:underline"
      >
        <i
          class="fa-solid fa-bolt-lightning text-[#1BA9FF] w-5 h-5 text-base inline-block"
        ></i>
        Giao hàng nhanh 2h
      </NuxtLink>

      <NuxtLink
        to="/commitment"
        class="pl-2 border-l border-gray-300 flex items-center gap-2 whitespace-nowrap hover:underline"
      >
        <i
          class="fa-solid fa-money-bill text-[#1BA9FF] w-5 h-5 text-base inline-block"
        ></i>
        Giá siêu rẻ
      </NuxtLink>
    </div>

    <!-- Mobile -->
    <div
      class="md:hidden text-center min-h-[28px] flex items-center justify-center relative w-full"
    >
      <transition name="fade" mode="out-in">
        <NuxtLink
          v-if="currentIndex === 0"
          to="/commitment"
          key="0"
          class="pl-2 border-l border-gray-300 flex items-center gap-2 absolute left-1/2 -translate-x-1/2 hover:underline"
        >
          Cam kết
          <i
            class="fa-solid fa-check text-[#1BA9FF] w-5 h-5 text-base inline-block"
          ></i>
          100% hàng thật
        </NuxtLink>

        <NuxtLink
          v-else-if="currentIndex === 2"
          to="/commitment"
          key="2"
          class="pl-2 border-l border-gray-300 flex items-center gap-2 absolute left-1/2 -translate-x-1/2 hover:underline"
        >
          <i
            class="fa-solid fa-truck-fast text-[#1BA9FF] w-5 h-5 text-base inline-block"
          ></i>
          Freeship mọi đơn
        </NuxtLink>

        <NuxtLink
          v-else-if="currentIndex === 3"
          to="/commitment"
          key="3"
          class="pl-2 border-l border-gray-300 flex items-center gap-2 absolute left-1/2 -translate-x-1/2 hover:underline"
        >
          <i
            class="fa-solid fa-right-left text-[#1BA9FF] w-5 h-5 text-base inline-block"
          ></i>
          Hoàn 200% nếu hàng giả
        </NuxtLink>

        <NuxtLink
          v-else-if="currentIndex === 4"
          to="/commitment"
          key="4"
          class="pl-2 border-l border-gray-300 flex items-center gap-2 absolute left-1/2 -translate-x-1/2 hover:underline"
        >
          <i
            class="fa-solid fa-rotate-left text-[#1BA9FF] w-5 h-5 text-base inline-block"
          ></i>
          30 ngày đổi trả
        </NuxtLink>

        <NuxtLink
          v-else-if="currentIndex === 5"
          to="/commitment"
          key="5"
          class="pl-2 border-l border-gray-300 flex items-center gap-2 absolute left-1/2 -translate-x-1/2 hover:underline"
        >
          <i
            class="fa-solid fa-bolt-lightning text-[#1BA9FF] w-5 h-5 text-base inline-block"
          ></i>
          Giao hàng nhanh 2h
        </NuxtLink>

        <NuxtLink
          v-else
          to="/commitment"
          key="6"
          class="pl-2 border-l border-gray-300 flex items-center gap-2 absolute left-1/2 -translate-x-1/2 hover:underline"
        >
          <i
            class="fa-solid fa-money-bill text-[#1BA9FF] w-5 h-5 text-base inline-block"
          ></i>
          Giá siêu rẻ
        </NuxtLink>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const currentIndex = ref(0);
let intervalId = null;

onMounted(() => {
  intervalId = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % 7;
  }, 2500);
});

onUnmounted(() => {
  clearInterval(intervalId);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
