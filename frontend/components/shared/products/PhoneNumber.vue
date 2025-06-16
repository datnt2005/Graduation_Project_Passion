<template>
    <section class="mb-8 text-sm text-gray-700">
        <p class="mb-2 font-semibold">
            Nhấn để hiển thị số: <a :href="`tel:${phone}`"
                class="font-bold text-blue-600 hover:underline">{{ phone }}</a>
        </p>
    </section>
</template>

<script setup>
defineProps({
    phone: { type: String, required: true }
});
</script>