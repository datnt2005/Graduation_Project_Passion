<template>
  <aside class="w-64 bg-white shadow-md flex flex-col">
    <div class="flex items-center p-4 border-b">
      <div class="w-10 h-10 rounded-full overflow-hidden mr-3">
        <img src="https://scontent.fsgn2-7.fna.fbcdn.net/v/t39.30808-6/488853217_1403799304327811_4217198369891756285_n.jpg?_nc_cat=108&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeHciyMx1dL8MLFQhCIgylsfLsjRN7AdrpsuyNE3sB2um3iQlFa9SsqBaHB5uBw24uXcaOfsSIYtmOsZuVdYivAC&_nc_ohc=MjCpNL29a28Q7kNvwGK-rkI&_nc_oc=Adl69t3p2kGP4bUs-mBcf2eki-o5rEmpb2AcvTLXr8MPn1JvuC_dLtVX9iU4IKwNQ5G84TZs_4VyYp_-jMmXlzm7&_nc_zt=23&_nc_ht=scontent.fsgn2-7.fna&_nc_gid=BsWXbAqMV2-h8kuJHig4Fw&oh=00_AfLvGZqvCnOkh-YfiDqxSd2yCMcvyyFQuUgixksjgcaAcA&oe=683D14D2" alt="User Avatar" class="w-full h-full object-cover">
      </div>
      <span class="font-semibold text-lg">chubivn805</span>
    </div>
    <nav class="mt-4 flex-grow">
      <NuxtLink to="/users/123/notifications" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2" active-class="bg-blue-50 text-blue-600 font-medium">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        Thông báo
      </NuxtLink>
      <NuxtLink to="/users/123" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2 mt-1" active-class="bg-blue-50 text-blue-600 font-medium">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        Tài khoản của tôi
      </NuxtLink>
      <NuxtLink to="/users/123/orders" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2 mt-1" active-class="bg-blue-50 text-blue-600 font-medium">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        Đơn mua
      </NuxtLink>
      <NuxtLink to="/users/123/favorites" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2 mt-1" active-class="bg-blue-50 text-blue-600 font-medium">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
        Yêu thích
      </NuxtLink>
    </nav>
    <div class="p-4 border-t mt-auto">
         <button class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2 w-full justify-start">
             <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
             Đăng xuất
         </button>
    </div>
  </aside>
</template>

<script setup>
// No script needed for a static sidebar
</script>

<style scoped>
/* Add any specific styles if needed */
</style>