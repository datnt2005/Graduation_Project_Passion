<template>
    <div class="bg-white text-gray-800">
         <Header />
         <!-- nội dung  -->
            <NuxtPage />

         <Footer />   


    </div>
</template>
<script setup>
 import Header from '~/components/shared/Header.vue';
 import Footer from '~/components/shared/Footer.vue';

</script>
