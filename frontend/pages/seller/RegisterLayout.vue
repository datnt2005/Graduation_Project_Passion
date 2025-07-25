<!-- RegisterLayout.vue -->
<template>
        <div class="grid grid-cols- md:grid-cols-2 min-h-screen bg-white">
            <div class="hidden md:flex items-center justify-cente">
                <img src="/images/SellerCenter2.png" alt="Register visual"/>
            </div>   
            <div class="flex flex-col justify-center p-8 max-w-md w-full mx-auto">
                <slot />
            </div>
        </div>
</template>
