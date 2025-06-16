<template>
    <section class="mb-8">
        <h2 class="text-xl font-semibold mb-3">Mô tả chi tiết</h2>
        <div class="text-base text-gray-700 mb-4 leading-relaxed"
             :class="{ 'line-clamp-2 overflow-hidden': isCollapsed }" 
             v-html="fullDescription"></div>
        <button
            class="text-sm text-gray-700 border border-gray-300 rounded-md px-4 py-1.5 hover:bg-gray-100 transition-colors duration-150"
            type="button" @click="handleToggle"
            :aria-expanded="!isCollapsed">
            {{ isCollapsed ? 'Xem thêm' : 'Thu gọn' }}
        </button>
    </section>
</template>

<script setup>
const props = defineProps({
    fullDescription: { type: String, required: true },
    isCollapsed: { type: Boolean, required: true }
});

const emit = defineEmits(['toggle-collapse']);

function handleToggle() {
    emit('toggle-collapse');
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>