<template>
  <div class="bg-gray-50 py-3">
    <div class="max-w-6xl mx-auto px-2">
      <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-2 sm:mb-3">
        Danh mục sản phẩm
      </h2>

      <div
        class="grid grid-cols-6 gap-x-2 gap-y-1 text-[11px] sm:text-[12px] md:text-sm text-gray-700"
      >
        <div v-for="(category, index) in categoryGroups" :key="index">
          <h3 class="font-semibold text-gray-900 mb-1 text-[12px] sm:text-sm">
            {{ category.title }}
          </h3>
          <ul class="space-y-[2px]">
            <li
              v-for="(item, idx) in category.items"
              :key="idx"
              class="truncate"
            >
              <NuxtLink
                :to="`/shop/${item.slug}`"
                class="hover:underline hover:text-blue-500 transition-all duration-150 relative after:content-['|'] after:mx-1 after:text-gray-300"
                :class="{
                  'after:content-none': idx === category.items.length - 1,
                }"
              >
                {{ item.name }}
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const categoryGroups = [
  {
    title: "Thời trang",
    items: [
      { name: "Áo thun", slug: "ao-thun" },
      { name: "Quần jeans", slug: "quan-jeans" },
      { name: "Đầm nữ", slug: "dam-nu" },
    ],
  },
  {
    title: "Điện tử",
    items: [
      { name: "Điện thoại", slug: "dien-thoai" },
      { name: "Laptop", slug: "laptop" },
      { name: "Tai nghe", slug: "tai-nghe" },
    ],
  },
  {
    title: "Nhà cửa",
    items: [
      { name: "Đèn ngủ", slug: "den-ngu" },
      { name: "Rèm cửa", slug: "rem-cua" },
      { name: "Đồ nhà bếp", slug: "do-nha-bep" },
    ],
  },
  {
    title: "Làm đẹp",
    items: [
      { name: "Son môi", slug: "son-moi" },
      { name: "Kem dưỡng", slug: "kem-duong" },
      { name: "Sữa rửa mặt", slug: "sua-rua-mat" },
    ],
  },
  {
    title: "Thể thao",
    items: [
      { name: "Giày thể thao", slug: "giay-the-thao" },
      { name: "Balo", slug: "balo" },
      { name: "Đồng hồ", slug: "dong-ho" },
    ],
  },
  {
    title: "Khác",
    items: [
      { name: "Sách", slug: "sach" },
      { name: "Đồ chơi", slug: "do-choi" },
      { name: "Quà tặng", slug: "qua-tang" },
    ],
  },
  {
    title: "Nội thất",
    items: [
      { name: "Ghế sofa", slug: "ghe-sofa" },
      { name: "Bàn trà", slug: "ban-tra" },
    ],
  },
  {
    title: "Đồ gia dụng",
    items: [
      { name: "Máy hút bụi", slug: "may-hut-bui" },
      { name: "Nồi chiên không dầu", slug: "noi-chien-khong-dau" },
    ],
  },
  {
    title: "Mẹ và bé",
    items: [
      { name: "Bỉm tã", slug: "bim-ta" },
      { name: "Sữa bột", slug: "sua-bot" },
    ],
  },
  {
    title: "Văn phòng phẩm",
    items: [
      { name: "Bút viết", slug: "but-viet" },
      { name: "Sổ tay", slug: "so-tay" },
    ],
  },
  {
    title: "Thiết bị nhà bếp",
    items: [
      { name: "Máy xay sinh tố", slug: "may-xay-sinh-to" },
      { name: "Lò vi sóng", slug: "lo-vi-song" },
    ],
  },
  {
    title: "Dụng cụ sửa chữa",
    items: [
      { name: "Máy khoan", slug: "may-khoan" },
      { name: "Bộ tua vít", slug: "bo-tua-vit" },
    ],
  },
  {
    title: "Ô tô & Xe máy",
    items: [
      { name: "Dầu nhớt", slug: "dau-nhot" },
      { name: "Phụ kiện xe", slug: "phu-kien-xe" },
    ],
  },
  {
    title: "Phụ kiện điện thoại",
    items: [
      { name: "Ốp lưng", slug: "op-lung" },
      { name: "Cáp sạc", slug: "cap-sac" },
    ],
  },
  {
    title: "Phụ kiện máy tính",
    items: [
      { name: "Chuột máy tính", slug: "chuot" },
      { name: "Bàn phím", slug: "ban-phim" },
    ],
  },
  {
    title: "Thú cưng",
    items: [
      { name: "Thức ăn cho chó", slug: "thuc-an-cho-cho" },
      { name: "Đồ chơi cho mèo", slug: "do-choi-cho-meo" },
    ],
  },
  {
    title: "Du lịch & Dã ngoại",
    items: [
      { name: "Lều cắm trại", slug: "leu-cam-trai" },
      { name: "Túi ngủ", slug: "tui-ngu" },
    ],
  },
  {
    title: "Trang sức",
    items: [
      { name: "Dây chuyền", slug: "day-chuyen" },
      { name: "Nhẫn", slug: "nhan" },
    ],
  },
  {
    title: "Sức khỏe",
    items: [
      { name: "Thực phẩm chức năng", slug: "thuc-pham-chuc-nang" },
      { name: "Máy đo huyết áp", slug: "may-do-huyet-ap" },
    ],
  },
  {
    title: "Nhạc cụ",
    items: [
      { name: "Guitar", slug: "guitar" },
      { name: "Piano điện", slug: "piano-dien" },
    ],
  },
  {
    title: "Thiết bị an ninh",
    items: [
      { name: "Camera giám sát", slug: "camera-giam-sat" },
      { name: "Chuông cửa", slug: "chuong-cua" },
    ],
  },
  {
    title: "Máy móc công nghiệp",
    items: [
      { name: "Máy nén khí", slug: "may-nen-khi" },
      { name: "Máy hàn", slug: "may-han" },
    ],
  },
  {
    title: "Dụng cụ thể hình",
    items: [
      { name: "Tạ tay", slug: "ta-tay" },
      { name: "Thảm tập yoga", slug: "tham-tap-yoga" },
    ],
  },
  {
    title: "Dụng cụ làm vườn",
    items: [
      { name: "Kéo tỉa cây", slug: "keo-tia-cay" },
      { name: "Bình tưới nước", slug: "binh-tuoi-nuoc" },
    ],
  },
  {
    title: "Chăm sóc cá nhân",
    items: [
      { name: "Máy cạo râu", slug: "may-cao-rau" },
      { name: "Bàn chải điện", slug: "ban-chai-dien" },
    ],
  },
  {
    title: "Thực phẩm khô",
    items: [
      { name: "Hạt điều", slug: "hat-dieu" },
      { name: "Mì ăn liền", slug: "mi-an-lien" },
    ],
  },
  {
    title: "Đồ uống",
    items: [
      { name: "Trà", slug: "tra" },
      { name: "Cà phê", slug: "ca-phe" },
    ],
  },
  {
    title: "Đèn trang trí",
    items: [
      { name: "Đèn LED dây", slug: "den-led-day" },
      { name: "Đèn thả trần", slug: "den-tha-tran" },
    ],
  },
  {
    title: "Giày dép",
    items: [
      { name: "Giày cao gót", slug: "giay-cao-got" },
      { name: "Sandal", slug: "sandal" },
    ],
  },
  {
    title: "Túi xách",
    items: [
      { name: "Túi xách nữ", slug: "tui-xach-nu" },
      { name: "Túi đeo chéo", slug: "tui-deo-cheo" },
    ],
  },
  {
    title: "Đồ cosplay",
    items: [
      { name: "Trang phục anime", slug: "trang-phuc-anime" },
      { name: "Phụ kiện hóa trang", slug: "phu-kien-hoa-trang" },
    ],
  },
  {
    title: "Thời trang nam",
    items: [
      { name: "Áo sơ mi", slug: "ao-so-mi" },
      { name: "Áo vest", slug: "ao-vest" },
    ],
  },
  {
    title: "Thời trang nữ",
    items: [
      { name: "Chân váy", slug: "chan-vay" },
      { name: "Áo kiểu", slug: "ao-kieu" },
    ],
  },
];
</script>
