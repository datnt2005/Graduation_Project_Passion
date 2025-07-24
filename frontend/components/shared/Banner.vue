  <template>
    <div class="bg-white w-full select-none">
      <div class="flex flex-wrap justify-center md:justify-between gap-4 mb-6 px-2">
        <!-- Slide bên trái -->
        <div
          class="flex-1 min-w-[280px] max-w-full relative overflow-hidden shadow-md"
          @mousedown="startDrag"
          @mousemove="onDrag"
          @mouseup="endDrag"
          @mouseleave="cancelDrag"
          @touchstart="startDrag"
          @touchmove="onDrag"
          @touchend="endDrag"
        >
          <div
            id="bannerSlides"
            class="flex transition-transform duration-500 ease-in-out"
            :style="{ transform: `translateX(-${realIndex * 100}%)` }"
            ref="sliderRef"
            :class="{ 'transition-none': disableTransition }"
          >
            <!-- Clone ảnh cuối -->
            <div
              v-if="banners.length"
              class="w-full h-[200px] flex-shrink-0 bg-white flex items-center justify-center"
            >
              <img
                :src="banners[banners.length - 1].image_url"
                alt="Clone cuối"
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Ảnh chính -->
            <div
              v-for="(img, i) in banners"
              :key="i"
              class="w-full h-[300px] flex-shrink-0 bg-white flex items-center justify-center"
            >
              <img
                :src="img.image_url"
                :alt="img.title || `Slide ${i + 1}`"
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Clone ảnh đầu -->
            <div
              v-if="banners.length"
              class="w-full h-[300px] flex-shrink-0 bg-white flex items-center justify-center"
            >
              <img
                :src="banners[0].image_url"
                alt="Clone đầu"
                class="w-full h-full object-cover"
              />
            </div>
          </div>

          <!-- Dots -->
          <div class="absolute bottom-3 left-1/2 transform -translate-x-1/2 flex gap-2 z-10">
            <button
              v-for="(img, i) in banners"
              :key="i"
              class="dot w-3 h-3 rounded-full bg-white opacity-70 hover:bg-[#1BA0E2]"
              :class="{ 'bg-[#1BA0E2]': i === index }"
              @click="goToSlide(i)"
            ></button>
          </div>
        </div>

        <!-- Banner nhỏ bên phải -->
        <div class="hidden sm:flex flex-col gap-4 min-w-[250px] max-w-[360px]">
          <img
            src="https://sf-static.upanhlaylink.com/img/image_20250718176342fcca97b9ab168d67eb5c15616e.jpg"
            alt="Banner nhỏ 1"
            class="w-full h-[146px] object-cover shadow-md"
          />
          <img
            src="https://sf-static.upanhlaylink.com/img/image_202507181fb34ba202f43ade448a5eeb1078e9d5.jpg"
            alt="Banner nhỏ 2"
            class="w-full h-[146px] object-cover shadow-md"
          />
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, onMounted, onBeforeUnmount, watch } from "vue";
  import { useRuntimeConfig } from "#app";

  const config = useRuntimeConfig();
  const apiBase = config.public.apiBaseUrl;

  const banners = ref([]);
  const index = ref(0); // index thật
  const realIndex = ref(1); // index thực tế trong list có clone
  const disableTransition = ref(false);

  let timer = null;
  const sliderRef = ref(null);

  // Lấy banner
  async function fetchBanners() {
    try {
      const res = await $fetch(`${apiBase}/banners?status=active&type=banner`);
      banners.value = res.data?.data || [];
      index.value = 0;
      realIndex.value = 1;
    } catch (e) {
      console.error("Lỗi khi lấy banner:", e);
      banners.value = [];
    }
  }

  // Slide
  function goToSlide(i) {
    index.value = i;
    realIndex.value = i + 1;
  }
  function nextSlide() {
    if (!banners.value.length) return;
    index.value = (index.value + 1) % banners.value.length;
    realIndex.value += 1;
  }
  function prevSlide() {
    if (!banners.value.length) return;
    index.value = (index.value - 1 + banners.value.length) % banners.value.length;
    realIndex.value -= 1;
  }

  // Auto reset clone chuyển mượt
  watch(realIndex, (val) => {
    if (val === 0) {
      setTimeout(() => {
        disableTransition.value = true;
        realIndex.value = banners.value.length;
        index.value = banners.value.length - 1;
        setTimeout(() => (disableTransition.value = false), 50);
      }, 500);
    }
    if (val === banners.value.length + 1) {
      setTimeout(() => {
        disableTransition.value = true;
        realIndex.value = 1;
        index.value = 0;
        setTimeout(() => (disableTransition.value = false), 50);
      }, 500);
    }
  });

  // Kéo chuột
  let isDragging = false;
  let startX = 0;
  let currentX = 0;

  function startDrag(e) {
    if (sliderRef.value) {
      isDragging = true;
      startX = e.type === "touchstart" ? e.touches[0].clientX : e.clientX;
      sliderRef.value.style.cursor = "grabbing"; // Thay đổi thành nắm tay khi kéo
      sliderRef.value.style.userSelect = "none"; // Ngăn chọn text
      clearInterval(timer); // Tạm dừng auto slide khi kéo
    }
  }

  function onDrag(e) {
    if (isDragging && sliderRef.value) {
      currentX = e.type === "touchmove" ? e.touches[0].clientX : e.clientX;
      const diff = startX - currentX;
      sliderRef.value.style.transition = "none"; // Tắt transition khi kéo
      sliderRef.value.style.transform = `translateX(-${(realIndex.value * 100 + (diff / sliderRef.value.offsetWidth) * 100)}%)`;
    }
  }

  function endDrag() {
    if (isDragging && sliderRef.value) {
      isDragging = false;
      const diff = currentX - startX;
      if (diff > 50) {
        prevSlide();
      } else if (diff < -50) {
        nextSlide();
      }
      sliderRef.value.style.transition = "transform 0.5s ease-in-out"; // Khôi phục transition
      sliderRef.value.style.cursor = "grab"; // Khôi phục con trỏ grab
      sliderRef.value.style.userSelect = ""; // Khôi phục select
      timer = setInterval(nextSlide, 6000); // Khởi động lại auto slide
    }
  }

  function cancelDrag() {
    if (isDragging && sliderRef.value) {
      isDragging = false;
      sliderRef.value.style.transition = "transform 0.5s ease-in-out"; // Khôi phục transition
      sliderRef.value.style.cursor = "grab"; // Khôi phục con trỏ grab
      sliderRef.value.style.userSelect = ""; // Khôi phục select
      timer = setInterval(nextSlide, 6000); // Khởi động lại auto slide
    }
  }

  // Lifecycle
  onMounted(() => {
    fetchBanners();
    timer = setInterval(nextSlide, 6000);
  });
  onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
  });
  </script>

  <style scoped>
  .transition-none {
    transition: none !important;
  }

  img {
    user-select: none;
    -webkit-user-drag: none;
  }

  /* Con trỏ mặc định khi hover */
  #bannerSlides {
    cursor: grab;
  }
  </style>