<template>
  <div
    class="container mx-auto mt-6 bg-white shadow rounded-md relative overflow-hidden"
  >
    <div class="border-b border-gray-200 px-4 py-3">
      <div class="text-[#1BA0E2] font-bold text-sm uppercase">
        DANH MỤC NỔI BẬT
      </div>
    </div>
    <!-- Nút điều hướng trái -->
    <button
      v-if="canNavigate"
      @click="scrollLeft"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll left"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-4 text-gray-600"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M12.707 15.707a1 1 0 01-1.414 0L6.586 11l4.707-4.707a1 1 0 111.414 1.414L9.414 11l3.293 3.293a1 1 0 010 1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <!-- Nút điều hướng phải -->
    <button
      v-if="canNavigate"
      @click="scrollRight"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll right"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-4 text-gray-600"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M7.293 4.293a1 1 0 011.414 0L13.414 9l-4.707 4.707a1 1 0 01-1.414-1.414L10.586 9 7.293 5.707a1 1 0 010-1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <!-- Danh sách danh mục -->
    <div
      ref="scrollContainer"
      class="overflow-x-auto scrollbar-hide px-4 py-4 touch-pan-y"
      @mousedown="startDragging"
      @mousemove="dragging"
      @mouseup="stopDragging"
      @mouseleave="stopDragging"
      @touchstart="startDragging"
      @touchmove="dragging"
      @touchend="stopDragging"
    >
      <div class="min-w-[900px] grid grid-cols-8 grid-rows-2 gap-x-8 gap-y-6">
        <NuxtLink
          v-for="(category, index) in categories"
          :key="category.id || index"
          :to="`/shop/${category.slug}`"
          class="flex flex-col items-center text-center text-[12px] text-gray-800 hover:-translate-y-0.5 transition"
        >
          <div
            class="bg-gray-100 rounded-full p-4 mb-3 w-20 h-20 flex items-center justify-center"
          >
            <img
              :src="`${mediaBase}${category.image}`"
              :alt="category.name"
              class="w-16 h-16 object-contain"
              width="64"
              height="64"
            />
          </div>
          <p
            class="text-[12px] text-gray-800 leading-tight font-medium truncate max-w-[100px]"
          >
            {{ category.name }}
          </p>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRuntimeConfig } from "#imports";

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const categories = ref([]);
const scrollContainer = ref(null);

// Drag state
const isDragging = ref(false);
const startX = ref(0);
const scrollLeftStart = ref(0);

// Fetch data from API
const fetchCategories = async () => {
  try {
    const response = await fetch(`${apiBase}/search/trending-categories`);
    const data = await response.json();
    categories.value = data.data.categories || data.data.data || data.data; // Linh hoạt với cấu trúc API
  } catch (err) {
    console.error("Lỗi khi fetch danh mục:", err);
  }
};

// Logic cuộn
const canNavigate = computed(() => {
  return (
    scrollContainer.value &&
    scrollContainer.value.scrollWidth > scrollContainer.value.clientWidth
  );
});

function scrollLeft() {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({
      left: -200,
      behavior: "smooth",
    });
  }
}

function scrollRight() {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({
      left: 200,
      behavior: "smooth",
    });
  }
}

// Drag functionality
function startDragging(event) {
  if (scrollContainer.value) {
    isDragging.value = true;
    startX.value = event.type.includes("touch")
      ? event.touches[0].clientX
      : event.clientX;
    scrollLeftStart.value = scrollContainer.value.scrollLeft;
    scrollContainer.value.style.scrollBehavior = "auto"; // Tắt smooth scroll khi drag
    scrollContainer.value.style.cursor = "grabbing"; // Thay đổi con trỏ khi kéo
  }
}

function dragging(event) {
  if (isDragging.value && scrollContainer.value) {
    event.preventDefault(); // Ngăn chặn hành vi mặc định (như chọn text)
    const currentX = event.type.includes("touch")
      ? event.touches[0].clientX
      : event.clientX;
    const diffX = startX.value - currentX;
    scrollContainer.value.scrollLeft = scrollLeftStart.value + diffX;
  }
}

function stopDragging() {
  if (isDragging.value && scrollContainer.value) {
    isDragging.value = false;
    scrollContainer.value.style.scrollBehavior = "smooth"; // Khôi phục smooth scroll
    scrollContainer.value.style.cursor = "grab"; // Khôi phục con trỏ ban đầu
  }
}

// Fetch data when component mounts
onMounted(() => {
  fetchCategories();
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

.transition {
  transition: all 0.3s ease;
}

/* Ngăn chặn highlight text khi drag */
.touch-pan-y {
  -webkit-user-select: none;
  -ms-user-select: none;
  user-select: none;
  touch-action: pan-y; /* Cho phép cuộn dọc trên touch device, nhưng không ảnh hưởng drag ngang */
  cursor: grab; /* Con trỏ mặc định */
}

/* Thay đổi con trỏ khi hover */
.touch-pan-y:hover {
  cursor: grab;
}
</style>
