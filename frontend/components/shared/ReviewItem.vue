<template>
  <div class="bg-white rounded-md p-4 border text-sm text-gray-800"> <!-- Font nhỏ hơn -->
    <!-- Header -->
    <div class="flex items-start gap-3 mb-3">
      <!-- Avatar -->
      <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">
        {{ review.user.charAt(0).toUpperCase() }}
      </div>

      <!-- Info -->
      <div class="flex-1">
        <p class="font-semibold text-base">{{ review.user }}</p> <!-- Lớn hơn -->
        <p class="text-sm text-gray-500">Đã tham gia {{ review.joined }}</p> <!-- Tăng 5px -->
        <p class="text-sm text-gray-500">Đã viết {{ review.totalReviews }} Đánh giá</p>
      </div>

      <!-- Stars -->
      <div class="ml-auto flex items-center gap-1 text-yellow-400">
        <i v-for="i in review.rating" :key="i" class="fas fa-star"></i>
      </div>
    </div>

    <!-- Title + Badge -->
    <p class="font-semibold text-base mb-1">Cực kì hài lòng</p> <!-- Tăng -->
    <p v-if="review.purchased" class="text-green-600 font-semibold text-sm mb-2 flex items-center gap-1">
      <i class="fas fa-check-circle"></i> Đã mua hàng
    </p>

    <!-- Content -->
    <p class="mb-2 text-base text-gray-700">{{ review.comment }}</p> <!-- Tăng -->
   <div v-if="review.reply" class="mt-3 ml-6 border-l-4 border-[#1BA0E2] pl-4 py-2 bg-blue-50 rounded-md text-sm m-5">
    <div class="flex items-center gap-2 mb-1">
      <i class="fas fa-user-shield text-[#1BA0E2]"></i>
      <span class="font-semibold text-[#1BA0E2]">Passion</span>
      <span class="text-gray-400 text-xs">• Đã phản hồi</span>
    </div>
    <p class="text-gray-700 leading-snug">{{ review.reply }}</p>
  </div>

    <!-- Images -->
    <div class="flex gap-2 overflow-x-auto mb-2">
      <img v-for="(img, index) in review.images" :key="index" :src="img" class="w-20 h-20 object-cover rounded border"
        alt="Ảnh sản phẩm" />
    </div>

    <!-- Metadata -->
    <p class="text-sm text-gray-500 mb-1">Màu: {{ review.color }}</p>
    <p class="text-sm text-gray-500 mb-2">
      Đánh giá vào {{ review.date }} • Đã dùng {{ review.usageTime }}
    </p>

    <!-- Interaction -->
    <div class="flex flex-col gap-2 text-gray-600 text-sm">
      <!-- Like & Reply -->
      <div class="flex items-center gap-4">
        <!-- Like -->
        <button
          class="flex items-center gap-1 transition duration-150 active:scale-95"
          :class="isLiked ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'"
          @click="toggleLike"
        >
          <i class="fas fa-thumbs-up"></i>
          <span>Hữu ích</span>
        </button>

        <!-- Comment -->
        <button class="flex items-center gap-1 hover:text-blue-600 transition" @click="showReplyForm = !showReplyForm">
          <i class="fas fa-comment"></i>
          <span>Bình luận</span>
        </button>

        <!-- Share -->
        <button class="flex items-center gap-1 hover:text-blue-600 transition ml-auto">
          <i class="fas fa-share-alt"></i>
          <span>Chia sẻ</span>
        </button>
      </div>

      <!-- Reply Form -->
      <transition name="fade">
        <div v-if="showReplyForm" class="flex items-start border border-black-500 p-2 rounded-md mt-2">
          <!-- Emoji Icon -->
          <div class="text-2xl mr-2 text-gray-400">
            😊
          </div>

          <!-- Input + Send -->
          <div class="flex-1 relative">
            <input type="text" v-model="replyContent"
              class="w-full border border-blue-500 rounded-full px-4 py-2 pr-10 outline-none"
              placeholder="Viết câu trả lời" />
            <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
              @click="submitReply">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </transition>
    </div>

  </div>
</template>

<script setup>
defineProps(['review']);

const showReplyForm = ref(false);
const replyContent = ref('');

const isLiked = ref(false);
const toggleLike = () => {
  isLiked.value = !isLiked.value;
};

const submitReply = () => {
  // Xuất hành phản hồi
  showReplyForm.value = false;
};
</script>
<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
