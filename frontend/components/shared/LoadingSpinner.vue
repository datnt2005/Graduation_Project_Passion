<template>
  <div class="fixed inset-0 z-[9999] bg-white bg-opacity-80 flex items-center justify-center">
    <div class="flex flex-col items-center space-y-4 animate-fade-in">
      <svg class="w-12 h-12 animate-spin text-[#1BA0E2]" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
      </svg>
      <p class="text-[#1BA0E2] font-semibold">Đang tải trang...</p>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in {
  0% { opacity: 0; transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
  animation: fade-in 0.3s ease-out forwards;
}
</style>
