<template>
    <head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
 <body class="bg-white text-black font-sans">
  <!-- Progress bar -->
  <div class="flex justify-center items-center space-x-8 py-6 max-w-5xl mx-auto">
   <div class="flex flex-col items-center text-green-600">
    <i class="fas fa-shopping-cart text-3xl">
    </i>
    <span class="mt-1 text-sm font-semibold">
     Giỏ hàng
    </span>
   </div>
   <div class="flex items-center space-x-2 text-gray-400">
    <div class="h-[1px] w-20 bg-gray-400">
    </div>
    <i class="fas fa-arrow-right text-gray-400">
    </i>
    <div class="h-[1px] w-20 bg-gray-400">
    </div>
   </div>
   <div class="flex flex-col items-center text-blue-600">
    <i class="fas fa-credit-card text-3xl">
    </i>
    <span class="mt-1 text-sm font-semibold">
     Thanh toán
    </span>
   </div>
   <div class="flex items-center space-x-2 text-gray-400">
    <div class="h-[1px] w-20 bg-gray-400">
    </div>
    <i class="fas fa-arrow-right text-gray-400">
    </i>
    <div class="h-[1px] w-20 bg-gray-400">
    </div>
   </div>
   <div class="flex flex-col items-center text-blue-500">
    <i class="fas fa-check-circle text-3xl">
    </i>
    <span class="mt-1 text-sm font-semibold">
     Thành công
    </span>
   </div>
  </div>
  <!-- Title -->
  <h2 class="text-center font-semibold text-base mb-6">
   GIỎ HÀNG CỦA BẠN
  </h2>
  <!-- Table header -->
  <div class="max-w-5xl mx-auto border-t border-b border-gray-300 py-3 grid grid-cols-[40px_1.5fr_1fr_1fr_1fr_40px] items-center font-semibold text-sm">
   <input class="w-5 h-5" type="checkbox"/>
   <div>
    Sản phẩm
   </div>
   <div class="text-center">
    Đơn giá
   </div>
   <div class="text-center">
    Số lượng
   </div>
   <div class="text-center">
    Tổng tiền
   </div>
   <div>
   </div>
  </div>
  <!-- Cart items -->
  <div class="max-w-5xl mx-auto border-b border-gray-300 divide-y divide-gray-300">
   <!-- Item 1 -->
   <div class="grid grid-cols-[40px_1.5fr_1fr_1fr_1fr_40px] items-center py-4">
    <input class="w-5 h-5" type="checkbox"/>
    <div class="flex items-center space-x-4">
     <img alt="Black leather women's handbag on white background" class="w-14 h-14 object-contain" height="60" src="https://storage.googleapis.com/a1aa/image/a76b33ee-6893-4642-f5bb-af9c961c6a8d.jpg" width="60"/>
     <div class="text-xs">
      <div class="font-normal text-sm">
       Gucci Women's
      </div>
      <div class="text-gray-400 text-[9px] leading-tight">
       Free returns are available for the shipping address you chose.
      </div>
     </div>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center items-center space-x-2">
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      −
     </button>
     <div class="border border-gray-400 rounded-full w-10 h-6 flex justify-center items-center text-sm font-normal">
      2
     </div>
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      +
     </button>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center">
     <i class="fas fa-trash-alt cursor-pointer">
     </i>
    </div>
   </div>
   <!-- Item 2 -->
   <div class="grid grid-cols-[40px_1.5fr_1fr_1fr_1fr_40px] items-center py-4">
    <input class="w-5 h-5" type="checkbox"/>
    <div class="flex items-center space-x-4">
     <img alt="Black sunglasses with red temples on white background" class="w-14 h-14 object-contain" height="60" src="https://storage.googleapis.com/a1aa/image/968f1067-8193-4b0f-0aa2-17ae1d5fd6a9.jpg" width="60"/>
     <div class="text-xs">
      <div class="font-normal text-sm">
       Gucci Women's
      </div>
      <div class="text-gray-400 text-[9px] leading-tight">
       Free returns are available for the shipping address you chose.
      </div>
     </div>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center items-center space-x-2">
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      −
     </button>
     <div class="border border-gray-400 rounded-full w-10 h-6 flex justify-center items-center text-sm font-normal">
      2
     </div>
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      +
     </button>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center">
     <i class="fas fa-trash-alt cursor-pointer">
     </i>
    </div>
   </div>
   <!-- Item 3 -->
   <div class="grid grid-cols-[40px_1.5fr_1fr_1fr_1fr_40px] items-center py-4">
    <input class="w-5 h-5" type="checkbox"/>
    <div class="flex items-center space-x-4">
     <img alt="Brown sunglasses on white background" class="w-14 h-14 object-contain" height="60" src="https://storage.googleapis.com/a1aa/image/b95b2273-e00a-4f23-fd6c-2009406308dd.jpg" width="60"/>
     <div class="text-xs">
      <div class="font-normal text-sm">
       Gucci Women's
      </div>
      <div class="text-gray-400 text-[9px] leading-tight">
       Free returns are available for the shipping address you chose.
      </div>
     </div>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center items-center space-x-2">
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      −
     </button>
     <div class="border border-gray-400 rounded-full w-10 h-6 flex justify-center items-center text-sm font-normal">
      2
     </div>
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      +
     </button>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center">
     <i class="fas fa-trash-alt cursor-pointer">
     </i>
    </div>
   </div>
   <!-- Item 4 -->
   <div class="grid grid-cols-[40px_1.5fr_1fr_1fr_1fr_40px] items-center py-4">
    <input class="w-5 h-5" type="checkbox"/>
    <div class="flex items-center space-x-4">
     <img alt="Black and brown sunglasses stacked on white background" class="w-14 h-14 object-contain" height="60" src="https://storage.googleapis.com/a1aa/image/457a7e24-1bdb-4b47-cfa5-4400a9a565d9.jpg" width="60"/>
     <div class="text-xs">
      <div class="font-normal text-sm">
       Gucci Women's
      </div>
      <div class="text-gray-400 text-[9px] leading-tight">
       Free returns are available for the shipping address you chose.
      </div>
     </div>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center items-center space-x-2">
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      −
     </button>
     <div class="border border-gray-400 rounded-full w-10 h-6 flex justify-center items-center text-sm font-normal">
      2
     </div>
     <button class="border border-gray-400 rounded-full w-6 h-6 flex justify-center items-center text-lg font-light select-none">
      +
     </button>
    </div>
    <div class="text-center text-sm font-normal">
     $195.99
    </div>
    <div class="flex justify-center">
     <i class="fas fa-trash-alt cursor-pointer">
     </i>
    </div>
   </div>
  </div>
  <!-- Total and button -->
  <div class="max-w-5xl mx-auto flex justify-between items-center py-6 border-b border-gray-300 px-2">
   <div class="text-sm font-normal">
    TỔNG TIỀN: $1998.000
   </div>
   <div class="flex items-center space-x-4">
    
    <button class="bg-blue-400 text-white text-sm px-5 py-2 rounded">
     Thanh toán
    </button>
   </div>
  </div>
  <!-- Related products title -->
  <div class="max-w-5xl mx-auto mt-6 mb-2 px-2 text-sm font-normal">
   SẢN PHẨM KÈM THEO
  </div>
  <!-- Related products grid -->
  <div class="max-w-5xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-1 px-2">
   <!-- 12 product cards -->
   <div class="border border-gray-200">
    <img alt="Black sunglasses with thick frame on white background" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/8c2929bc-a286-42a5-7cfa-31d0f9a0d45d.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Brown sunglasses on white background" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/b95b2273-e00a-4f23-fd6c-2009406308dd.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="City street with neon signs at night" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/f578e254-e963-4c53-cdaa-a37ddeb2b9e7.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Starry night sky over desert hills" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/1d2fb625-f84e-4017-c9e8-173a11cefdba.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Green leafy plant in white pot on white background" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/bdbab9ea-01ac-4536-74da-1d161e11937f.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Green tree with round foliage on white background" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/7f9cb2b7-62e5-473f-c40f-28a401b71d7f.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Portrait of a woman with long hair wearing a yellow top" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/f4ff4bca-e358-49c6-d145-ecaff71e226e.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Portrait of a man wearing a black polo shirt" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/11075c34-98bd-4d92-d217-185eacca0c8f.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Folded clothes on a dark table" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/efb30fa6-bb9d-49f7-76b4-f2a70b3f06f7.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="White t-shirt on a mannequin torso" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/6b9c829f-d1d5-4b15-ec00-895d079583c6.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Brown shoes and folded blue clothes on white background" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/a9d6f539-c2e7-4e80-54ed-523ada586709.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
   <div class="border border-gray-200">
    <img alt="Green clothes hanging on wooden hangers" class="w-full object-cover" height="100" src="https://storage.googleapis.com/a1aa/image/cc369b35-d00e-4a5f-a0e0-952e21bbb0ba.jpg" width="150"/>
    <div class="text-[10px] px-1 pt-1 leading-tight">
     Kính việt nam, tại việt nam
    </div>
    <div class="text-[10px] px-1 pb-1 flex justify-between items-center">
     <span>
      $19.000
     </span>
     <span class="text-yellow-400 text-xs">
      ★★★★★
     </span>
    </div>
   </div>
  </div>
 </body>
</template>