<template>
    <div class="bg-white text-gray-800">
         <Header />
         <!-- nội dung  -->
            <NuxtPage />
            <Notification />
          <!-- footer -->  
         <Footer />   


    </div>
</template>
<script setup>
 import Header from '~/components/shared/layouts/Header.vue';
 import Footer from '~/components/shared/layouts/Footer.vue';
 import Notification from '~/components/Notification.vue';
</script>
