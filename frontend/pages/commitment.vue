<template>
  <div
    class="bg-[#0050C8] font-sans text-[#333] text-[15px] md:text-[16px] leading-relaxed pb-8"
  >
    <div class="max-w-[1200px] mx-auto bg-white rounded-[10px] overflow-hidden">
      <!-- Top banner -->
      <div class="relative bg-[#0050C8] rounded-b-[20px] py-8 px-4 md:py-12">
        <img
          alt="Scooter"
          class="absolute left-4 top-4 w-[100px] md:w-[120px] h-auto object-contain"
          src="https://storage.googleapis.com/a1aa/image/f19f93e6-8f3f-4912-a871-aab7815b30eb.jpg"
        />
        <div class="flex flex-col items-center gap-4">
          <div
            class="flex items-center bg-[#FF3B30] rounded-[10px] px-6 py-3 text-white font-semibold text-lg md:text-xl"
          >
            <i class="fas fa-box-open mr-2 text-[24px]"></i> 30 ngày đổi trả
          </div>
          <div
            class="bg-[#2E9FFF] rounded-[10px] px-6 py-3 text-white text-center text-lg md:text-xl font-semibold leading-snug"
          >
            Hoàn ngay
            <span class="text-[28px] font-extrabold">200%</span><br />
            nếu hàng giả
          </div>
        </div>
        <img
          alt="Arrows"
          class="absolute right-4 bottom-4 w-[100px] md:w-[120px] h-auto object-contain"
          src="https://storage.googleapis.com/a1aa/image/ea9e85c5-60cb-4f4e-daf5-7fe2c8af6b47.jpg"
        />
      </div>

      <!-- Mini buttons -->
      <div class="flex justify-center flex-wrap gap-3 mt-5 px-4">
        <button
          class="border border-[#2E9FFF] rounded-full px-4 py-2 text-[#2E9FFF] font-medium text-sm md:text-base"
        >
          30 ngày đổi trả
        </button>
        <button
          class="border border-[#2E9FFF] rounded-full px-4 py-2 text-[#2E9FFF] font-medium text-sm md:text-base"
        >
          Có ý kiến kỹ thuật
        </button>
        <button
          class="border border-[#2E9FFF] rounded-full px-4 py-2 text-[#2E9FFF] font-medium text-sm md:text-base"
        >
          Hoàn 200% nếu hàng giả
        </button>
        <button
          class="border border-[#2E9FFF] rounded-full px-4 py-2 text-[#2E9FFF] font-medium text-sm md:text-base"
        >
          Thao tác Online nhanh chóng
        </button>
      </div>

      <!-- Section: 30 NGÀY ĐỔI TRẢ -->
      <section class="px-4 pb-8 pt-6">
        <h2
          class="bg-[#FCD535] text-center font-bold text-lg md:text-xl rounded-[6px] py-3"
        >
          30 NGÀY ĐỔI TRẢ
        </h2>
        <p class="mt-5 text-base md:text-lg leading-[1.6]">
          Trong vòng 30 ngày kể từ ngày nhận hàng, nếu không hài lòng với sản
          phẩm, bạn luôn có thể yêu cầu đổi trả sản phẩm mà
          <span class="font-bold text-[#FF3B30]">KHÔNG</span> mất thêm chi phí.
        </p>

        <!-- Grid Icon -->
        <div
          class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 text-center text-sm md:text-base font-semibold text-[#333]"
        >
          <div
            class="bg-[#F5F9FF] rounded-xl py-5 px-2 flex flex-col items-center"
          >
            <img
              src="https://storage.googleapis.com/a1aa/image/49d6dfd5-4948-47b5-7542-04b8ea72f413.jpg"
              alt="Đổi ý"
              class="w-20 h-20 object-contain mb-3"
            />
            Đổi ý
          </div>
          <div
            class="bg-[#F5F9FF] rounded-xl py-5 px-2 flex flex-col items-center"
          >
            <img
              src="https://storage.googleapis.com/a1aa/image/13a63a74-3067-4fb5-54ce-3537b9f11314.jpg"
              alt="Lỗi kỹ thuật"
              class="w-20 h-20 object-contain mb-3"
            />
            Lỗi kỹ thuật
          </div>
          <div
            class="bg-[#F5F9FF] rounded-xl py-5 px-2 flex flex-col items-center"
          >
            <img
              src="https://storage.googleapis.com/a1aa/image/7c536166-7cae-4afb-e4b4-8c8eda49c70c.jpg"
              alt="Bể vỡ"
              class="w-20 h-20 object-contain mb-3"
            />
            Bể vỡ
          </div>
          <div
            class="bg-[#F5F9FF] rounded-xl py-5 px-2 flex flex-col items-center"
          >
            <img
              src="https://storage.googleapis.com/a1aa/image/5eda6ac6-4160-4eb5-6cf8-f82349830759.jpg"
              alt="Giao sai"
              class="w-20 h-20 object-contain mb-3"
            />
            Giao sai
          </div>
          <div
            class="bg-[#F5F9FF] rounded-xl py-5 px-2 flex flex-col items-center"
          >
            <img
              src="https://storage.googleapis.com/a1aa/image/bf85bffd-2e67-4aeb-ce0f-5918fe6dbe2b.jpg"
              alt="Giao thiếu"
              class="w-20 h-20 object-contain mb-3"
            />
            Giao thiếu
          </div>
          <div
            class="bg-[#F5F9FF] rounded-xl py-5 px-2 flex flex-col items-center"
          >
            <img
              src="https://storage.googleapis.com/a1aa/image/58462906-1251-4343-5d48-a2004c14b72f.jpg"
              alt="Hết hạn"
              class="w-20 h-20 object-contain mb-3"
            />
            Hết hạn sử dụng
          </div>
        </div>
      </section>
      <!-- Section: ĐỔI Ý MIỄN PHÍ -->
      <div class="px-4 pb-6">
        <div
          class="bg-[#FCD535] text-center font-bold text-[16px] leading-[20px] rounded-[6px] py-2"
        >
          ĐỔI Ý MIỄN PHÍ
        </div>
        <div
          class="bg-[#D9F0FF] rounded-[10px] mt-4 p-4 text-[14px] text-[#0066CC] font-semibold"
        >
          Nhận không đúng ý
          <br />
          <span class="font-bold"> Trả hàng miễn phí </span>
          <br />
          <span class="text-[10px] font-normal text-[#666]">
            PASSION hỗ trợ TRẢ HÀNG MIỄN PHÍ nếu bạn có ý
          </span>
        </div>
        <div class="mt-4 space-y-3">
          <div
            class="bg-[#2E9FFF] rounded-[10px] text-white font-semibold text-[14px] px-4 py-3 flex items-center"
          >
            <div
              class="flex-shrink-0 w-8 h-8 rounded-full bg-white text-[#2E9FFF] font-extrabold text-xl flex items-center justify-center mr-4 select-none"
            >
              1
            </div>
            Sản phẩm còn nguyên tem/ seal, chưa qua sử dụng/ lắp đặt.
          </div>
          <div
            class="bg-[#2E9FFF] rounded-[10px] text-white font-semibold text-[14px] px-4 py-3 flex items-center"
          >
            <div
              class="flex-shrink-0 w-8 h-8 rounded-full bg-white text-[#2E9FFF] font-extrabold text-xl flex items-center justify-center mr-4 select-none"
            >
              2
            </div>
            Không mua bằng hình thức trả góp
          </div>
        </div>
      </div>
      <!-- Section: Product categories not eligible -->
      <div
        class="px-4 pb-10 text-[14px] md:text-[16px] text-[#333] leading-relaxed"
      >
        <p>
          Và sản phẩm
          <span class="font-bold text-[#FF3B30]">KHÔNG</span>
          nằm trong danh mục sau:
        </p>

        <div class="mt-4 space-y-5">
          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Thời trang nam/ nữ
            </div>
            <p>Đồ lót, phụ kiện đồ lót, đồ bơi</p>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Đồ chơi - Mẹ & Bé
            </div>
            <p>Đồ lót, Đồ bơi</p>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Làm đẹp - Sức khỏe
            </div>
            <p>Nước hoa</p>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Đồng hồ và Trang sức
            </div>
            <p>Trang sức</p>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Sách
            </div>
            <ul class="list-disc list-inside space-y-1">
              <li>Đào, tạp chí, các ấn phẩm định kỳ.</li>
              <li>Băng đĩa CD/DVD.</li>
              <li>Sản phẩm đã bọc bookcare.</li>
            </ul>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Nhà cửa - Đời sống
            </div>
            <p>Hoa tươi, cây cảnh</p>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Voucher - Dịch vụ
            </div>
            <p>Tất cả sản phẩm trong danh mục Voucher - Dịch vụ</p>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              NGON
            </div>
            <ul class="list-disc list-inside space-y-1">
              <li>
                Các sản phẩm tươi sống và đông lạnh (trái cây, rau củ quả, thịt,
                trứng, cá, thủy hải sản...)
              </li>
              <li>Các sản phẩm thuộc sữa - trứng (sữa, bơ, phô mai...)</li>
              <li>Sản phẩm có thời gian sử dụng dưới 3 tháng</li>
            </ul>
          </div>

          <div class="border border-[#FCD535] rounded-[8px] p-4">
            <div
              class="inline-block bg-[#2E9FFF] text-white text-sm font-semibold rounded-[6px] px-3 py-1 mb-2"
            >
              Khác
            </div>
            <ul class="list-disc list-inside space-y-1">
              <li>Sản phẩm thuộc Ô tô - Xe máy - Xe đạp</li>
              <li>Sản phẩm giao từ nước ngoài</li>
              <li>
                Sản phẩm quà tặng kèm hoặc mua kèm giảm thêm (chỉ hỗ trợ trả
                hàng khi trả kèm với sản phẩm chính trong đơn hàng).
              </li>
              <li>Sản phẩm có thông tin "Không áp dụng đổi trả do nhu cầu"</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Section: HOÀN 200% NẾU HÀNG GIẢ -->
      <div class="px-4 pb-6">
        <div
          class="bg-[#FCD535] text-center font-bold text-[16px] leading-[20px] rounded-[6px] py-2"
        >
          HOÀN 200% NẾU HÀNG GIẢ
        </div>
        <div class="mt-4 flex flex-col items-center space-y-4">
          <img
            alt="Blue 3D cartoon character smiling and pointing"
            class="w-[120px] h-[120px] object-contain"
            height="120"
            src="https://storage.googleapis.com/a1aa/image/f03414c9-7848-4e72-699b-854b0287cf8f.jpg"
            width="120"
          />
          <div
            class="text-[#0066CC] font-bold text-[16px] text-center leading-tight"
          >
            Chọn PASSION
            <br />
            Mua hàng thật, dễ thật
          </div>
          <ul
            class="text-[14px] text-[#333] list-disc list-inside max-w-[320px]"
          >
            <li>
              PASSION cam kết tất cả sản phẩm là hàng chính hãng đã ĐĂNG KÝ BẢO
              HÀNH TOÀN SẢN.
            </li>
            <li>
              Trong trường hợp nghi ngờ sản phẩm đã mua là hàng giả, bạn hãy
              thực hiện kiểm tra theo các hướng dẫn.
            </li>
          </ul>
        </div>
        <div class="mt-6 flex flex-col sm:flex-row gap-4 justify-center">
          <div
            class="bg-[#2E9FFF] text-white rounded-[10px] px-6 py-6 text-center flex-1"
          >
            <div class="font-bold text-[18px] leading-none mb-1">
              Thu hồi xử lý
              <br />
              <span class="text-[24px] font-extrabold">
                trả hàng miễn phí
              </span>
            </div>
          </div>
          <div
            class="bg-[#FCD535] text-[#333] rounded-[10px] px-6 py-6 text-center flex-1"
          >
            <div class="font-bold text-[18px] leading-none mb-1">
              Hoàn
              <br />
              <span class="text-[36px] font-extrabold"> 200% </span>
              <br />
              giá trị sản phẩm
            </div>
          </div>
        </div>
        <p class="mt-2 text-[14px] text-center text-[#666]">
          (*) PASSION sẽ hoàn tiền theo hình thức thanh toán ban đầu, 100% số
          hoàn qua PASSION Thẻ tín dụng trong 24h.
        </p>
      </div>
      <!-- Section: THAO TÁC ONLINE NHANH CHÓNG -->
      <div class="px-4 pb-8">
        <div
          class="bg-[#FCD535] text-center font-bold text-[16px] leading-[20px] rounded-[6px] py-2"
        >
          THAO TÁC ONLINE NHANH CHÓNG
        </div>
        <div class="mt-4 space-y-6 text-[12px] text-[#333]">
          <!-- Step 1 -->
          <div>
            <div class="font-bold text-[14px] mb-1">
              1. Chọn "Yêu Cầu Đổi - Trả"
            </div>
            <img
              alt="Screenshot of mobile app screen showing step 1: Chọn Yêu Cầu Đổi - Trả"
              class="w-full max-w-[200px] mx-auto rounded-[10px] border border-[#ccc]"
              height="400"
              src="https://storage.googleapis.com/a1aa/image/2746cfe7-e37c-4214-715b-7e264b8fa62f.jpg"
              width="200"
            />
          </div>
          <!-- Step 2 -->
          <div>
            <div class="font-bold text-[14px] mb-1">
              2. Chọn lý do cần đổi trả
            </div>
            <img
              alt="Screenshot of mobile app screen showing step 2: Chọn lý do cần đổi trả"
              class="w-full max-w-[200px] mx-auto rounded-[10px] border border-[#ccc]"
              height="400"
              src="https://storage.googleapis.com/a1aa/image/f5699546-2c63-4a9f-9998-191ac938c223.jpg"
              width="200"
            />
          </div>
          <!-- Step 3 -->
          <div>
            <div class="font-bold text-[14px] mb-1">
              3. Bổ sung hình ảnh và giải pháp
            </div>
            <img
              alt="Screenshot of mobile app screen showing step 3: Bổ sung hình ảnh và giải pháp"
              class="w-full max-w-[200px] mx-auto rounded-[10px] border border-[#ccc]"
              height="400"
              src="https://storage.googleapis.com/a1aa/image/b3df5fad-a1ce-4f8b-a01a-fa3afb17c88b.jpg"
              width="200"
            />
          </div>
          <!-- Step 4 -->
          <div>
            <div class="font-bold text-[14px] mb-1">
              4. Xác nhận và gửi yêu cầu
            </div>
            <img
              alt="Screenshot of mobile app screen showing step 4: Xác nhận và gửi yêu cầu"
              class="w-full max-w-[200px] mx-auto rounded-[10px] border border-[#ccc]"
              height="400"
              src="https://storage.googleapis.com/a1aa/image/f78ff367-e72b-4bc7-ec3b-912228ff453d.jpg"
              width="200"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
