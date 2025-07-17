<template>
  <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Cập nhật sản phẩm</h1>
  <div class="px-6 pb-4">
    <nuxt-link to="/admin/products/list-product" class="text-gray-600 hover:underline text-sm">
      Danh sách sản phẩm
    </nuxt-link>
    <span class="text-gray-600 text-sm"> / Cập nhật sản phẩm</span>
  </div>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <nav class="w-64 bg-white border-r border-gray-200">
      <ul class="py-1">
        <li>
          <button @click="activeTab = 'general'" :class="[
            'flex items-center w-full px-4 py-2 text-sm transition-colors',
            activeTab === 'general' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Thông tin chung
          </button>
        </li>
        <li>
          <button @click="activeTab = 'variants'" :class="[
            'flex items-center w-full px-4 py-2 text-sm transition-colors',
            activeTab === 'variants' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Biến thể
          </button>
        </li>
        <li>
          <button @click="activeTab = 'inventory'" :class="[
            'flex items-center w-full px-4 py-2 text-sm transition-colors',
            activeTab === 'inventory' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Tồn kho
          </button>
        </li>
      </ul>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-6 bg-gray-100">
      <div class="max-w-[1200px] mx-auto">
        <form @submit.prevent="updateProduct">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
            <section class="space-y-4">
              <!-- Form Content -->
              <div class="space-y-2">
                <!-- Product Name -->
                <label for="product-name" class="block text-sm text-gray-700 mb-1">Tên sản phẩm</label>
                <input id="product-name" v-model="formData.name" type="text"
                  class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nhập tên sản phẩm" />
                <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>

                <!-- Slug -->
                <label for="product-slug" class="block text-sm text-gray-700 mb-1">Đường dẫn (Slug)</label>
                <input id="product-slug" v-model="formData.slug" type="text"
                  class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nhập đường dẫn (tùy chọn)" />
                <span v-if="errors.slug" class="text-red-500 text-xs mt-1">{{ errors.slug }}</span>

                <!-- Description -->
                <label for="description" class="block text-sm text-gray-700 mb-1">Mô tả</label>
                <Editor v-model="formData.description" api-key="rlas5j7eqa6dogiwnt1ld8iilzj3q074o4rw75lsxcygu1zd" :init="{
                  height: 300,
                  menubar: false,
                  plugins: 'lists link image preview code help table',
                  toolbar: 'undo redo | formatselect | bold italic underline | alignjustify alignleft aligncenter alignright | bullist numlist | removeformat | preview | link image | code | h1 h2 h3 h4 h5 h6',
                }" />
                <span v-if="errors.description" class="text-red-500 text-xs mt-1">{{ errors.description }}</span>

                <!-- Tabbed Content -->
                <div class="bg-white rounded border border-gray-300 shadow-sm mt-4">
                  <header
                    class="flex items-center justify-between px-3 py-2 border-b border-gray-300 text-gray-700 font-semibold text-sm">
                    <span>Chi tiết sản phẩm</span>
                  </header>
                  <div class="flex-1 p-4 text-xs text-gray-700 space-y-3">
                    <!-- General Tab -->
                    <div v-if="activeTab === 'general'">
                      <!-- Status -->
                      <div class="flex flex-col md:flex-row md:items-center md:space-x-2">
                        <label for="status" class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">
                          Trạng thái
                        </label>
                        <select id="status" v-model="formData.status"
                          class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                          <option value="active">Hoạt động</option>
                          <option value="inactive">Không hoạt động</option>
                          <option value="trash">Thùng rác</option>
                        </select>
                        <span v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</span>
                      </div>
                    </div>

                    <!-- Variants Tab -->
                    <div v-if="activeTab === 'variants'">
                      <div v-if="apiErrors.attributes" class="text-red-500 text-xs mb-2">
                        {{ apiErrors.attributes }}
                      </div>
                      <div v-else-if="!attributes.length" class="text-gray-500 text-xs mb-2">
                        Không có thuộc tính nào để hiển thị.
                      </div>
                      <div class="space-y-4">
                        <div class="flex justify-between items-center mb-2">
                          <h3 class="font-semibold">Thuộc tính</h3>
                          <button type="button" class="text-blue-700 underline text-xs"
                            @click="showAddAttributeModal = true">
                            Thêm thuộc tính mới
                          </button>
                        </div>
                        <span v-if="errors.variants" class="text-red-500 text-xs mt-1 block">
                          {{ errors.variants }}
                        </span>
                        <div v-for="(variant, index) in formData.variants" :key="index" class="border p-4 rounded">
                          <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold">Biến thể {{ index + 1 }}</h3>
                            <button v-if="formData.variants.length > 1" @click="removeVariant(index)"
                              class="text-red-500 hover:text-red-700 text-xs">
                              Xóa
                            </button>
                          </div>
                          <!-- Variant Price -->
                          <div class="flex flex-col md:flex-row md:items-center md:space-x-2 mb-2">
                            <label class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">Giá</label>
                            <input v-model.number="variant.price" type="number" min="0" step="0.01"
                              class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                            <span v-if="errors[`variants.${index}.price`]" class="text-red-500 text-xs mt-1">{{
                              errors[`variants.${index}.price`]
                            }}</span>
                          </div>
                          <!-- Sale Price -->
                          <div class="flex flex-col md:flex-row md:items-center md:space-x-2 mb-2">
                            <label class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">Giá khuyến mãi</label>
                            <input v-model.number="variant.sale_price" type="number" min="0" step="0.01"
                              class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                            <span v-if="errors[`variants.${index}.sale_price`]" class="text-red-500 text-xs mt-1">{{
                              errors[`variants.${index}.sale_price`]
                            }}</span>
                          </div>
                          <!-- Cost Price -->
                          <div class="flex flex-col md:flex-row md:items-center md:space-x-2 mb-2">
                            <label class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">Giá vốn</label>
                            <input v-model.number="variant.cost_price" type="number" min="0" step="0.01"
                              class="w-full md:w-60 rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                            <span v-if="errors[`variants.${index}.cost_price`]" class="text-red-500 text-xs mt-1">{{
                              errors[`variants.${index}.cost_price`]
                            }}</span>
                          </div>
                          <!-- Thumbnail -->
                          <div class="flex flex-col md:flex-row md:items-center md:space-x-2 mb-2">
                            <label class="w-full md:w-40 mb-1 md:mb-0 font-normal text-gray-700">Thumbnail</label>
                            <div class="flex items-center space-x-2">
                              <input type="file" :ref="`variantThumbnail${index}`" accept="image/*" class="hidden"
                                @change="handleVariantThumbnailUpload($event, index)" />
                              <button type="button" class="text-blue-700 underline text-xs"
                                @click="$refs[`variantThumbnail${index}`][0].click()">
                                {{ variant.thumbnail ? 'Thay đổi' : 'Chọn' }} hình ảnh
                              </button>
                              <button v-if="variant.thumbnail" type="button"
                                class="text-red-500 hover:text-red-700 text-xs"
                                @click="variant.thumbnail = null; variant.thumbnailFile = null">
                                Xóa
                              </button>
                              <img v-if="variant.thumbnail" :src="variant.thumbnail" alt="Thumbnail"
                                class="w-12 h-12 object-cover rounded" />
                            </div>
                            <span v-if="errors[`variants.${index}.thumbnail`]" class="text-red-500 text-xs mt-1">{{
                              errors[`variants.${index}.thumbnail`]
                            }}</span>
                          </div>
                          <!-- Attributes -->
                          <div class="mb-2">
                            <label class="block mb-1 font-normal text-gray-700">Thuộc tính</label>
                            <div v-for="(attr, attrIndex) in variant.attributes" :key="attrIndex"
                              class="flex space-x-2 mb-2">
                              <select v-model="attr.attribute_id"
                                class="w-full md:w-40 rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                @change="attr.value_id = ''">
                                <option value="">Chọn thuộc tính</option>
                                <option v-for="attribute in attributes" :value="attribute.id">{{ attribute.name }}
                                </option>
                              </select>
                              <select v-model="attr.value_id"
                                class="w-full md:w-40 rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                :disabled="!attr.attribute_id">
                                <option value="">Chọn giá trị</option>
                                <option v-for="value in getAttributeValues(attr.attribute_id)" :value="value.id">
                                  {{ value.name }}
                                </option>
                              </select>
                              <button v-if="variant.attributes.length > 1" type="button"
                                class="text-red-500 hover:text-red-700 text-xs"
                                @click="variant.attributes.splice(attrIndex, 1)">
                                Xóa
                              </button>
                            </div>
                            <button type="button" class="text-blue-700 underline text-xs"
                              @click="variant.attributes.push({ attribute_id: '', value_id: '' })">
                              Thêm thuộc tính
                            </button>
                            <span v-if="errors[`variants.${index}.attributes`]"
                              class="text-red-500 text-xs mt-1 block">{{
                                errors[`variants.${index}.attributes`]
                              }}</span>
                          </div>
                        </div>
                        <button type="button" class="text-blue-700 underline text-xs" @click="addVariant">
                          Thêm biến thể
                        </button>
                      </div>
                    </div>

                    <!-- Inventory Tab -->
                    <div v-if="activeTab === 'inventory'">
                      <div v-for="(variant, index) in formData.variants" :key="index" class="border p-4 rounded mb-4">
                        <h3 class="font-semibold mb-2">Biến thể {{ index + 1 }}</h3>
                        <span v-if="errors[`variants.${index}.inventory`]" class="text-red-500 text-xs mb-2 block">{{
                          errors[`variants.${index}.inventory`]
                        }}</span>
                        <div v-for="(inv, invIndex) in variant.inventory" :key="invIndex" class="space-y-2 mb-4">
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Số lượng -->
                            <div class="flex-1">
                              <label class="block mb-1 font-normal text-gray-700">Số lượng</label>
                              <input v-model.number="inv.quantity" type="number" min="0"
                                class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                              <span v-if="errors[`variants.${index}.inventory.${invIndex}.quantity`]"
                                class="text-red-500 text-xs mt-1">{{
                                  errors[`variants.${index}.inventory.${invIndex}.quantity`]
                                }}</span>
                            </div>
                            <!-- Vị trí -->
                            <div class="flex-1">
                              <label class="block mb-1 font-normal text-gray-700">Vị trí</label>
                              <input v-model="inv.location" type="text"
                                class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                              <span v-if="errors[`variants.${index}.inventory.${invIndex}.location`]"
                                class="text-red-500 text-xs mt-1">{{
                                  errors[`variants.${index}.inventory.${invIndex}.location`]
                                }}</span>
                            </div>
                            <!-- Mã lô -->
                            <div class="flex-1">
                              <label class="block mb-1 font-normal text-gray-700">Mã lô</label>
                              <input v-model="inv.batch_number" type="text"
                                class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                              <span v-if="errors[`variants.${index}.inventory.${invIndex}.batch_number`]"
                                class="text-red-500 text-xs mt-1">{{
                                  errors[`variants.${index}.inventory.${invIndex}.batch_number`]
                                }}</span>
                            </div>
                            <!-- Nguồn nhập -->
                            <div class="flex-1">
                              <label class="block mb-1 font-normal text-gray-700">Nguồn nhập</label>
                              <input v-model="inv.import_source" type="text"
                                class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                              <span v-if="errors[`variants.${index}.inventory.${invIndex}.import_source`]"
                                class="text-red-500 text-xs mt-1">{{
                                  errors[`variants.${index}.inventory.${invIndex}.import_source`]
                                }}</span>
                            </div>
                            <!-- Ghi chú -->
                            <div class="flex-1 md:col-span-2">
                              <label class="block mb-1 font-normal text-gray-700">Ghi chú</label>
                              <input v-model="inv.note" type="text"
                                class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                              <span v-if="errors[`variants.${index}.inventory.${invIndex}.note`]"
                                class="text-red-500 text-xs mt-1">{{
                                  errors[`variants.${index}.inventory.${invIndex}.note`]
                                }}</span>
                            </div>
                          </div>
                          <button v-if="variant.inventory.length > 1" type="button"
                            class="text-red-500 hover:text-red-700 text-xs mt-2"
                            @click="variant.inventory.splice(invIndex, 1)">
                            Xóa
                          </button>
                        </div>
                        <button type="button" class="text-blue-700 underline text-xs"
                          @click="variant.inventory.push({ quantity: 0, location: '', batch_number: '', import_source: '', note: '' })">
                          Thêm kho
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Sidebar -->
            <div class="w-full lg:w-80 space-y-4 text-xs text-gray-700 font-normal">
              <!-- Product Image -->
              <section class="border border-gray-300 rounded-md shadow-sm bg-white">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1 px-4 py-3">
                  <h2 class="font-semibold">Hình ảnh sản phẩm</h2>
                </header>
                <div class="p-4 space-y-3">
                  <!-- Drag & Drop + Click Upload Box -->
                  <div
                    class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                    @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
                    <input ref="fileInput" id="product-image" type="file" accept="image/*" class="hidden" multiple
                      @change="handleImageUpload" />
                    <div class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span>
                      </p>
                    </div>
                  </div>
                  <div v-if="formData.images.length" class="grid grid-cols-2 gap-2 mt-2">
                    <div v-for="(img, index) in formData.images" :key="index" class="relative">
                      <img :src="img.url" alt="Hình ảnh sản phẩm" class="w-full h-20 object-cover rounded" />
                      <button type="button"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                        @click="removeProductImage(index, img.id)">
                        ×
                      </button>
                    </div>
                  </div>
                  <span v-if="errors.images" class="text-red-500 text-xs mt-1 block">{{ errors.images }}</span>
                </div>
              </section>
              <button type="submit"
                class="bg-blue-700 text-white rounded px-4 py-2 text-sm font-semibold hover:bg-blue-800 transition-colors w-full"
                :disabled="loading" aria-label="Cập nhật sản phẩm">
                {{ loading ? 'Đang xử lý...' : 'Cập nhật sản phẩm' }}
              </button>
              <!-- Product Categories -->
              <section class="border border-gray-300 rounded-md shadow-sm bg-white">
                <header
                  class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
                  @click="togglePanel('categories')" :aria-expanded="panels.categories"
                  aria-label="Toggle Product categories panel">
                  <span>Danh mục sản phẩm</span>
                  <i class="fas" :class="panels.categories ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </header>
                <div v-if="panels.categories" class="p-4 text-xs">
                  <div v-if="apiErrors.categories" class="text-red-500 text-xs mb-2">
                    {{ apiErrors.categories }}
                  </div>
                  <div v-else-if="!categories.length" class="text-gray-500 text-xs mb-2">
                    Không có danh mục nào để hiển thị.
                  </div>
                  <div v-else class="relative mb-3">
                    <input v-model="categorySearch" type="text" placeholder="Tìm danh mục..."
                      class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      @focus="activeDropdown = 'categories'" />
                    <div v-if="activeDropdown === 'categories' && filteredCategories.length > 0"
                      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-48 overflow-y-auto">
                      <div v-for="category in filteredCategories" :key="category.id"
                        class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer flex items-center"
                        @click="toggleCategory(category)">
                        <input type="checkbox" :checked="formData.categories.includes(category.id)" class="mr-2 w-4 h-4"
                          @click.stop />
                        <span class="text-xs">{{ category.name }}</span>
                      </div>
                    </div>
                    <div v-else-if="activeDropdown === 'categories' && !filteredCategories.length"
                      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg p-2 text-xs text-gray-500">
                      Không tìm thấy danh mục
                    </div>
                  </div>
                  <div v-if="formData.categories.length" class="flex flex-wrap gap-1.5">
                    <div v-for="categoryId in formData.categories" :key="categoryId"
                      class="bg-gray-100 px-1.5 py-0.5 rounded flex items-center gap-1">
                      <span class="text-xs">
                        {{ categories.find(c => c.id === categoryId)?.name || 'Danh mục không xác định' }}
                      </span>
                      <button @click="toggleCategory(categories.find(c => c.id === categoryId))"
                        class="text-gray-500 hover:text-gray-700 text-xs">
                        ×
                      </button>
                    </div>
                  </div>
                  <span v-if="errors.categories" class="text-red-500 text-xs mt-1 block">{{ errors.categories }}</span>
                </div>
              </section>

              <!-- Product Tags -->
              <section class="border border-gray-300 rounded-md shadow-sm bg-white">
                <header
                  class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
                  @click="togglePanel('tags')" :aria-expanded="panels.tags" aria-label="Toggle Product tags panel">
                  <span>Thẻ sản phẩm</span>
                  <i class="fas" :class="panels.tags ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </header>
                <div v-if="panels.tags" class="p-4 text-xs">
                  <div v-if="apiErrors.tags" class="text-red-500 text-xs mb-2">
                    {{ apiErrors.tags }}
                  </div>
                  <div v-else-if="!tags.length" class="text-gray-500 text-xs mb-2">
                    Không có thẻ nào để hiển thị.
                  </div>
                  <div v-else class="relative mb-3">
                    <input v-model="tagSearch" type="text" placeholder="Tìm thẻ..."
                      class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      @focus="activeDropdown = 'tags'" />
                    <div v-if="activeDropdown === 'tags' && filteredTags.length > 0"
                      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-48 overflow-y-auto">
                      <div v-for="tag in filteredTags" :key="tag.id"
                        class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer flex items-center" @click="toggleTag(tag)">
                        <input type="checkbox" :checked="formData.tags.includes(tag.id)" class="mr-2 w-4 h-4"
                          @click.stop />
                        <span class="text-xs">{{ tag.name }}</span>
                      </div>
                    </div>
                    <div v-else-if="activeDropdown === 'tags' && !filteredTags.length"
                      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg p-2 text-xs text-gray-500">
                      Không tìm thấy thẻ
                    </div>
                  </div>
                  <div v-if="formData.tags.length" class="flex flex-wrap gap-1.5">
                    <div v-for="tagId in formData.tags" :key="tagId"
                      class="bg-gray-100 px-1.5 py-0.5 rounded flex items-center gap-1">
                      <span class="text-xs">{{ tags.find(t => t.id === tagId)?.name || 'Thẻ không xác định' }}</span>
                      <button @click="toggleTag(tags.find(t => t.id === tagId))"
                        class="text-gray-500 hover:text-gray-700 text-xs">
                        ×
                      </button>
                    </div>
                  </div>
                  <span v-if="errors.tags" class="text-red-500 text-xs mt-1 block">{{ errors.tags }}</span>
                </div>
              </section>

              <!-- Product Sellers -->
              <section class="border border-gray-300 rounded-md shadow-sm bg-white">
                <header
                  class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
                  @click="togglePanel('sellers')" :aria-expanded="panels.sellers"
                  aria-label="Toggle Product sellers panel">
                  <span>Người bán</span>
                  <i class="fas" :class="panels.sellers ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </header>
                <div v-if="panels.sellers" class="p-4 text-xs">
                  <div v-if="apiErrors.sellers" class="text-red-500 text-xs mb-2">
                    {{ apiErrors.sellers }}
                  </div>
                  <div v-else-if="!sellers.length" class="text-gray-500 text-xs mb-2">
                    Không có người bán nào để hiển thị.
                  </div>
                  <div v-else class="relative mb-3">
                    <select v-model="formData.seller_id"
                      class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      @change="selectSeller">
                      <option value="">Chọn người bán</option>
                      <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
                        {{ seller.store_name }} (#{{ seller.id }})
                      </option>
                    </select>
                    <span v-if="errors.seller_id" class="text-red-500 text-xs mt-1 block">{{ errors.seller_id }}</span>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </form>
      </div>
    </main>

    <!-- Add Attribute Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showAddAttributeModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
          @click="closeAddAttributeModal">
          <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.stop>
            <h2 class="text-lg font-semibold mb-4">Thêm thuộc tính mới</h2>
            <form @submit.prevent="createAttribute">
              <!-- Attribute Name -->
              <div class="mb-4">
                <label for="attribute-name" class="block text-sm text-gray-700 mb-1">Tên thuộc tính</label>
                <input id="attribute-name" v-model="newAttribute.name" type="text"
                  class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nhập tên thuộc tính (VD: Màu sắc)" />
                <span v-if="newAttributeErrors.name" class="text-red-500 text-xs mt-1">{{ newAttributeErrors.name
                }}</span>
              </div>
              <!-- Attribute Values -->
              <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">Giá trị thuộc tính</label>
                <div v-for="(value, index) in newAttribute.values" :key="index"
                  class="flex items-center space-x-2 mb-2">
                  <input v-model="newAttribute.values[index]" type="text"
                    class="flex-1 rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nhập giá trị (VD: Đỏ)" />
                  <button v-if="newAttribute.values.length > 1" type="button"
                    class="text-red-500 hover:text-red-700 text-xs" @click="newAttribute.values.splice(index, 1)">
                    Xóa
                  </button>
                </div>
                <button type="button" class="text-blue-700 underline text-xs" @click="newAttribute.values.push('')">
                  Thêm giá trị
                </button>
                <span v-if="newAttributeErrors.values" class="text-red-500 text-xs mt-1 block">{{
                  newAttributeErrors.values }}</span>
              </div>
              <!-- Actions -->
              <div class="flex justify-end space-x-2">
                <button type="button" class="bg-gray-200 text-gray-700 rounded px-4 py-2 text-sm hover:bg-gray-300"
                  @click="closeAddAttributeModal">
                  Hủy
                </button>
                <button type="submit" class="bg-blue-700 text-white rounded px-4 py-2 text-sm hover:bg-blue-800"
                  :disabled="loading">
                  {{ loading ? 'Đang lưu...' : 'Lưu' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 rounded-lg shadow-xl border p-4 flex items-center space-x-3 z-50"
          :class="notificationType === 'success' ? 'bg-white border-gray-200' : 'bg-red-50 border-red-200'">
          <div class="flex-shrink-0">
            <svg v-if="notificationType === 'success'" class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg"
              fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium" :class="notificationType === 'success' ? 'text-gray-900' : 'text-red-900'">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronUp, faChevronDown } from '@fortawesome/free-solid-svg-icons';
import Editor from '@tinymce/tinymce-vue';

library.add(faChevronUp, faChevronDown);

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const route = useRoute();
const activeTab = ref('general');
const loading = ref(false);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const activeDropdown = ref(null);
const categorySearch = ref('');
const tagSearch = ref('');
const errors = reactive({});
const apiErrors = reactive({
  products: null,
  categories: null,
  tags: null,
  attributes: null,
  sellers: null
});
const showAddAttributeModal = ref(false);
const fileInput = ref(null);
const removedImages = ref([]);
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;
const newAttribute = reactive({
  name: '',
  values: ['']
});
const newAttributeErrors = reactive({});

const formData = reactive({
  id: '',
  name: '',
  slug: '',
  description: '',
  status: 'active',
  seller_id: '',
  categories: [],
  tags: [],
  variants: [
    {
      id: null,
      price: 0,
      sale_price: null,
      cost_price: 0,
      attributes: [{ attribute_id: '', value_id: '' }],
      inventory: [{ id: null, quantity: 0, location: '', batch_number: '', import_source: '', note: '' }],
      thumbnail: null,
      thumbnailFile: null
    }
  ],
  images: []
});

const panels = ref({
  categories: true,
  tags: true,
  sellers: true
});

const categories = ref([]);
const tags = ref([]);
const attributes = ref([]);
const sellers = ref([]);

// Extract array from various API response formats
const extractArray = (data, key) => {
  if (Array.isArray(data)) return data;
  if (data[key] && Array.isArray(data[key])) return data[key];
  if (data.data && Array.isArray(data.data)) return data.data;
  if (data.data && data.data[key] && Array.isArray(data.data[key])) return data.data[key];
  return [];
};

// Fetch product data by ID
const fetchProduct = async () => {
  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/products/${route.params.id}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    });
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    const data = await response.json();
    const product = data.data || data.product || data;

    if (!product.id) {
      throw new Error('Invalid product data');
    }

    // Populate formData
    formData.id = product.id;
    formData.name = product.name || '';
    formData.slug = product.slug || '';
    formData.description = product.description || '';
    formData.status = product.status || 'active';
    formData.seller_id = product.seller_id || '';
    formData.categories = product.categories?.map(c => c.id) || [];
    formData.tags = product.tags?.map(t => t.id) || [];
    formData.images = product.product_pic?.length
      ? product.product_pic.map(img => ({
          id: img.id,
          url: img.imagePath ? `${mediaBase}${img.imagePath}` : img.url,
          file: null
        }))
      : [];
    formData.variants = product.product_variants?.length
      ? product.product_variants.map(variant => ({
          id: variant.id,
          price: parseFloat(variant.price) || 0,
          sale_price: variant.sale_price !== null ? parseFloat(variant.sale_price) : null,
          cost_price: parseFloat(variant.cost_price) || 0,
          thumbnail: variant.thumbnail ? `${mediaBase}${variant.thumbnail}` : null,
          thumbnailFile: null,
          attributes: variant.attributes?.map(attr => ({
            attribute_id: attr.id,
            value_id: attr.pivot?.value_id || attr.value_id
          })) || [{ attribute_id: '', value_id: '' }],
          inventory: variant.inventories?.map(inv => ({
            id: inv.id,
            quantity: parseInt(inv.quantity) || 0,
            location: inv.location || '',
            batch_number: inv.batch_number || '',
            import_source: inv.import_source || '',
            note: inv.note || ''
          })) || [{ id: null, quantity: 0, location: '', batch_number: '', import_source: '', note: '' }]
        }))
      : [
          {
            id: null,
            price: 0,
            sale_price: null,
            cost_price: 0,
            thumbnail: null,
            thumbnailFile: null,
            attributes: [{ attribute_id: '', value_id: '' }],
            inventory: [{ id: null, quantity: 0, location: '', batch_number: '', import_source: '', note: '' }]
          }
        ];
  } catch (error) {
    console.error('Error fetching product:', error);
    showNotificationMessage('Không thể tải sản phẩm.', 'error');
    router.push('/admin/products/list-product');
  } finally {
    loading.value = false;
  }
};

// Fetch data with error handling
const fetchCategories = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/categories`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    const data = await response.json();
    const categoryArray = extractArray(data, 'data') || extractArray(data, 'categories');
    if (categoryArray.length) {
      categories.value = categoryArray.map(item => ({
        id: item.id,
        name: item.name || item.title || 'Không có tên'
      }));
      apiErrors.categories = null;
    } else {
      throw new Error('Unexpected response format for categories');
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
    apiErrors.categories = 'Không thể tải danh sách danh mục.';
  }
};

const fetchTags = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/tags`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    const data = await response.json();
    const tagArray = extractArray(data, 'tags');
    if (tagArray.length) {
      tags.value = tagArray.map(item => ({
        id: item.id,
        name: item.name || item.title || 'Không có tên'
      }));
      apiErrors.tags = null;
    } else {
      throw new Error('Unexpected response format for tags');
    }
  } catch (error) {
    console.error('Error fetching tags:', error);
    apiErrors.tags = 'Không thể tải danh sách thẻ.';
  }
};

const fetchAttributes = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/attributes`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    const data = await response.json();
    const attributeArray = extractArray(data, 'data') || extractArray(data, 'attributes');
    if (attributeArray.length) {
      attributes.value = attributeArray.map(attr => ({
        id: attr.id,
        name: attr.name || attr.title || 'Không có tên',
        values: Array.isArray(attr.values)
          ? attr.values.map(val => ({
              id: val.id,
              name: val.value || val.name || 'Không có giá trị'
            }))
          : []
      }));
      apiErrors.attributes = null;
    } else {
      throw new Error('Unexpected response format for attributes');
    }
  } catch (error) {
    console.error('Error fetching attributes:', error);
    apiErrors.attributes = 'Không thể tải danh sách thuộc tính.';
  }
};

const fetchSellers = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/sellers/verified`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    const data = await response.json();
    const sellerArray = extractArray(data, 'sellers');
    if (sellerArray.length) {
      sellers.value = sellerArray.map(item => ({
        id: item.id,
        store_name: item.store_name || item.title || 'Không có tên'
      }));
      sellers.value.unshift({ id: 'passion', store_name: 'Passion (Admin)' });
      apiErrors.sellers = null;
    } else {
      throw new Error('Unexpected response format for sellers');
    }
  } catch (error) {
    console.error('Error fetching sellers:', error);
    apiErrors.sellers = 'Không thể tải danh sách người bán.';
  }
};

// Create new attribute
const createAttribute = async () => {
  Object.keys(newAttributeErrors).forEach(key => delete newAttributeErrors[key]);
  let isValid = true;

  if (!newAttribute.name.trim()) {
    newAttributeErrors.name = 'Tên thuộc tính là bắt buộc.';
    isValid = false;
  } else if (newAttribute.name.length > 255) {
    newAttributeErrors.name = 'Tên thuộc tính không được vượt quá 255 ký tự.';
    isValid = false;
  }

  const trimmedValues = newAttribute.values.map(v => v.trim()).filter(v => v);
  if (!trimmedValues.length) {
    newAttributeErrors.values = 'Phải có ít nhất một giá trị hợp lệ.';
    isValid = false;
  } else if (trimmedValues.some(v => v.length > 255)) {
    newAttributeErrors.values = 'Giá trị không được vượt quá 255 ký tự.';
    isValid = false;
  } else if (new Set(trimmedValues).size !== trimmedValues.length) {
    newAttributeErrors.values = 'Các giá trị không được trùng lặp.';
    isValid = false;
  }

  if (!isValid) return;

  const attributeData = {
    name: newAttribute.name.trim(),
    values: trimmedValues.map(value => ({ value }))
  };

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/attributes`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify(attributeData)
    });

    const data = await response.json();
    if (response.ok && data.success) {
      const newAttr = data.data || data;
      attributes.value.push({
        id: newAttr.id,
        name: newAttr.name,
        values: Array.isArray(newAttr.values)
          ? newAttr.values.map(val => ({
              id: val.id,
              name: val.value
            }))
          : []
      });
      showNotificationMessage('Tạo thuộc tính thành công!', 'success');
      closeAddAttributeModal();
    } else {
      showNotificationMessage(data.message || 'Có lỗi khi tạo thuộc tính.', 'error');
      if (data.errors) {
        Object.entries(data.errors).forEach(([key, value]) => {
          newAttributeErrors[key] = Array.isArray(value) ? value[0] : value;
        });
      }
    }
  } catch (error) {
    console.error('Error creating attribute:', error);
    showNotificationMessage('Có lỗi kết nối khi tạo thuộc tính.', 'error');
  } finally {
    loading.value = false;
  }
};

const closeAddAttributeModal = () => {
  showAddAttributeModal.value = false;
  newAttribute.name = '';
  newAttribute.values = [''];
  Object.keys(newAttributeErrors).forEach(key => delete newAttributeErrors[key]);
};

// Computed properties
const filteredCategories = computed(() => {
  if (!categorySearch.value) return categories.value;
  const searchTerm = removeVietnameseTones(categorySearch.value.toLowerCase());
  return categories.value.filter(category =>
    removeVietnameseTones(category.name.toLowerCase()).includes(searchTerm)
  );
});

const filteredTags = computed(() => {
  if (!tagSearch.value) return tags.value;
  const searchTerm = removeVietnameseTones(tagSearch.value.toLowerCase());
  return tags.value.filter(tag =>
    removeVietnameseTones(tag.name.toLowerCase()).includes(searchTerm)
  );
});

const getAttributeValues = (attributeId) => {
  const attribute = attributes.value.find(a => a.id === attributeId);
  return attribute ? attribute.values : [];
};

// Vietnamese tone removal
const removeVietnameseTones = (str) => {
  str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
  str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
  str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
  str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
  str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
  str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
  str = str.replace(/đ/g, "d");
  str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
  str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ẽ/g, "E");
  str = str.replace(/Ì|Í|Ị|Ĩ|Í/g, "I");
  str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
  str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
  str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
  str = str.replace(/Đ/g, "D");
  return str;
};

// Image handling
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files);
  processFiles(files);
};

const handleDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  processFiles(files);
};

const processFiles = (files) => {
  files.forEach((file) => {
    if (['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp'].includes(file.type) && file.size <= 4048 * 1024) {
      const url = URL.createObjectURL(file);
      formData.images.push({ file, url, id: '' });
      delete errors.images;
    } else {
      errors.images = 'Hình ảnh không hợp lệ hoặc vượt quá 4MB.';
    }
  });
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const removeProductImage = (index, id) => {
  if (id) {
    removedImages.value.push(id);
  }
  formData.images.splice(index, 1);
  if (!formData.images.length) delete errors.images;
};

const handleVariantThumbnailUpload = (event, index) => {
  const file = event.target.files[0];
  if (file && ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp'].includes(file.type) && file.size <= 4048 * 1024) {
    formData.variants[index].thumbnail = URL.createObjectURL(file);
    formData.variants[index].thumbnailFile = file;
    delete errors[`variants.${index}.thumbnail`];
  } else {
    errors[`variants.${index}.thumbnail`] = 'Thumbnail không hợp lệ hoặc vượt quá 4MB.';
  }
};

// Variant management
const addVariant = () => {
  formData.variants.push({
    id: null,
    price: 0,
    sale_price: null,
    cost_price: 0,
    attributes: [{ attribute_id: '', value_id: '' }],
    inventory: [{ id: null, quantity: 0, location: '', batch_number: '', import_source: '', note: '' }],
    thumbnail: null,
    thumbnailFile: null
  });
};

const removeVariant = (index) => {
  formData.variants.splice(index, 1);
  Object.keys(errors).forEach(key => {
    if (key.startsWith(`variants.${index}.`)) delete errors[key];
  });
};

// Category and tag selection
const toggleCategory = (category) => {
  if (!category) return;
  const index = formData.categories.indexOf(category.id);
  if (index === -1) {
    formData.categories.push(category.id);
  } else {
    formData.categories.splice(index, 1);
  }
};

const toggleTag = (tag) => {
  if (!tag) return;
  const index = formData.tags.indexOf(tag.id);
  if (index === -1) {
    formData.tags.push(tag.id);
  } else {
    formData.tags.splice(index, 1);
  }
};

const selectSeller = (event) => {
  formData.seller_id = event.target.value;
  if (!formData.seller_id) errors.seller_id = 'Vui lòng chọn người bán.';
  else delete errors.seller_id;
};

// Form validation
const validateFormData = () => {
  Object.keys(errors).forEach(key => delete errors[key]);
  let isValid = true;

  // Validate product name
  if (!formData.name.trim()) {
    errors.name = 'Tên sản phẩm là bắt buộc.';
    isValid = false;
  } else if (formData.name.length > 255) {
    errors.name = 'Tên sản phẩm không được vượt quá 255 ký tự.';
    isValid = false;
  }

  // Validate slug
  if (formData.slug && formData.slug.length > 255) {
    errors.slug = 'Slug không được vượt quá 255 ký tự.';
    isValid = false;
  } else if (formData.slug && !/^[a-zA-Z0-9-]+$/.test(formData.slug)) {
    errors.slug = 'Slug chỉ được chứa chữ cái, số và dấu gạch ngang.';
    isValid = false;
  }

  // Validate seller
  if (!formData.seller_id) {
    errors.seller_id = 'Vui lòng chọn người bán.';
    isValid = false;
  }

  // Validate status
  if (!['active', 'inactive', 'trash'].includes(formData.status)) {
    errors.status = 'Trạng thái không hợp lệ.';
    isValid = false;
  }

  // Validate variants
  formData.variants.forEach((variant, index) => {
    // Validate attributes
    const attrIds = variant.attributes.map(attr => attr.attribute_id).filter(Boolean);
    const duplicateAttr = attrIds.length !== new Set(attrIds).size;
    if (duplicateAttr) {
      errors[`variants.${index}.attributes`] = 'Không được chọn trùng thuộc tính trong một biến thể.';
      isValid = false;
    }

    // Validate prices
    if (!Number.isFinite(variant.price) || variant.price < 0) {
      errors[`variants.${index}.price`] = 'Giá phải là số dương.';
      isValid = false;
    }
    if (variant.sale_price !== null && (!Number.isFinite(variant.sale_price) || variant.sale_price < 0)) {
      errors[`variants.${index}.sale_price`] = 'Giá khuyến mãi phải là số dương hoặc bằng 0.';
      isValid = false;
    } else if (variant.sale_price !== null && variant.sale_price >= variant.price) {
      errors[`variants.${index}.sale_price`] = 'Giá khuyến mãi phải nhỏ hơn giá gốc.';
      isValid = false;
    }
    if (!Number.isFinite(variant.cost_price) || variant.cost_price < 0) {
      errors[`variants.${index}.cost_price`] = 'Giá vốn phải là số dương hoặc bằng 0.';
      isValid = false;
    }

    // Validate inventory
    const inventoryCombinations = variant.inventory
      .map(inv => `${inv.location || ''}|${inv.batch_number || ''}`)
      .filter(comb => comb !== '|');
    const uniqueCombinations = new Set(inventoryCombinations);
    if (inventoryCombinations.length !== uniqueCombinations.size) {
      errors[`variants.${index}.inventory`] = 'Tổ hợp vị trí và mã lô không được trùng lặp trong cùng một biến thể.';
      isValid = false;
    }

    variant.inventory.forEach((inv, invIndex) => {
      if (!Number.isFinite(inv.quantity) || inv.quantity < 0) {
        errors[`variants.${index}.inventory.${invIndex}.quantity`] = 'Số lượng phải là số nguyên không âm.';
        isValid = false;
      }
      if (inv.location && inv.location.length > 255) {
        errors[`variants.${index}.inventory.${invIndex}.location`] = 'Vị trí không được vượt quá 255 ký tự.';
        isValid = false;
      }
      if (inv.batch_number && inv.batch_number.length > 255) {
        errors[`variants.${index}.inventory.${invIndex}.batch_number`] = 'Mã lô không được vượt quá 255 ký tự.';
        isValid = false;
      }
      if (inv.import_source && inv.import_source.length > 255) {
        errors[`variants.${index}.inventory.${invIndex}.import_source`] = 'Nguồn nhập không được vượt quá 255 ký tự.';
        isValid = false;
      }
      if (inv.note && inv.note.length > 255) {
        errors[`variants.${index}.inventory.${invIndex}.note`] = 'Ghi chú không được vượt quá 255 ký tự.';
        isValid = false;
      }
    });
  });

  // Validate unique attribute combinations across variants
  const attributeSets = formData.variants.map((variant, index) => ({
    index,
    attributes: variant.attributes
      .filter(attr => attr.attribute_id && attr.value_id)
      .sort((a, b) => a.attribute_id - b.attribute_id)
      .map(attr => `${attr.attribute_id}:${attr.value_id}`)
      .join(',')
  }));
  const duplicates = attributeSets.reduce((acc, curr, i, arr) => {
    if (curr.attributes && arr.some((other, j) => i !== j && other.attributes === curr.attributes)) {
      acc.push(curr.index);
    }
    return acc;
  }, []);
  if (duplicates.length) {
    errors.variants = `Các biến thể tại vị trí ${duplicates.map(i => i + 1).join(', ')} có thuộc tính trùng nhau. Vui lòng sửa đổi các thuộc tính để đảm bảo mỗi biến thể là duy nhất.`;
    isValid = false;
  }

  return isValid;
};

// Form submission
const updateProduct = async () => {
  if (!validateFormData()) {
    showNotificationMessage('Vui lòng kiểm tra lại dữ liệu.', 'error');
    if (Object.keys(errors).some(key => key.includes('inventory'))) {
      activeTab.value = 'inventory'; // Switch to inventory tab if there are inventory errors
    } else if (Object.keys(errors).some(key => key.includes('variants'))) {
      activeTab.value = 'variants'; // Switch to variants tab if there are variant errors
    }
    return;
  }

  const formDataToSend = new FormData();
  formDataToSend.append('name', formData.name.trim());
  if (formData.slug) formDataToSend.append('slug', formData.slug.trim());
  formDataToSend.append('description', formData.description.trim());
  formDataToSend.append('status', formData.status);
  formDataToSend.append('sellers', formData.seller_id); // Match backend 'sellers' field

  formData.categories.forEach(categoryId => {
    formDataToSend.append('categories[]', categoryId);
  });

  formData.tags.forEach(tagId => {
    formDataToSend.append('tags[]', tagId);
  });

  const validVariants = formData.variants.filter(variant =>
    variant.price !== null && Number.isFinite(variant.price) &&
    variant.cost_price !== null && Number.isFinite(variant.cost_price)
  );

  validVariants.forEach((variant, index) => {
    if (variant.id) {
      formDataToSend.append(`variants[${index}][id]`, variant.id);
    }
    formDataToSend.append(`variants[${index}][price]`, variant.price.toFixed(2));
    if (variant.sale_price !== null && Number.isFinite(variant.sale_price)) {
      formDataToSend.append(`variants[${index}][sale_price]`, variant.sale_price.toFixed(2));
    }
    formDataToSend.append(`variants[${index}][cost_price]`, variant.cost_price.toFixed(2));

    const validAttributes = variant.attributes.filter(attr => attr.attribute_id && attr.value_id);
    if (validAttributes.length > 0) {
      validAttributes.forEach((attr, i) => {
        formDataToSend.append(`variants[${index}][attributes][${i}][attribute_id]`, attr.attribute_id);
        formDataToSend.append(`variants[${index}][attributes][${i}][value_id]`, attr.value_id);
      });
    }

    const validInventory = variant.inventory.filter(inv => Number.isFinite(inv.quantity) && inv.quantity >= 0);
    validInventory.forEach((inv, i) => {
      if (inv.id) {
        formDataToSend.append(`variants[${index}][inventory][${i}][id]`, inv.id);
      }
      formDataToSend.append(`variants[${index}][inventory][${i}][quantity]`, inv.quantity);
      if (inv.location && inv.location.trim()) {
        formDataToSend.append(`variants[${index}][inventory][${i}][location]`, inv.location.trim());
      }
      if (inv.batch_number && inv.batch_number.trim()) {
        formDataToSend.append(`variants[${index}][inventory][${i}][batch_number]`, inv.batch_number.trim());
      }
      if (inv.import_source && inv.import_source.trim()) {
        formDataToSend.append(`variants[${index}][inventory][${i}][import_source]`, inv.import_source.trim());
      }
      if (inv.note && inv.note.trim()) {
        formDataToSend.append(`variants[${index}][inventory][${i}][note]`, inv.note.trim());
      }
    });

    if (variant.thumbnailFile) {
      formDataToSend.append(`variants[${index}][thumbnail]`, variant.thumbnailFile);
    }
  });

  formData.images.forEach((img, index) => {
    if (img.file) {
      formDataToSend.append(`images[${index}]`, img.file);
    }
  });

  removedImages.value.forEach(imageId => {
    formDataToSend.append('removed_images[]', imageId);
  });

  formDataToSend.append('_method', 'PUT');

  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    const response = await fetch(`${apiBase}/products/${formData.id}`, {
      method: 'POST',
      body: formDataToSend,
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    });
    const data = await response.json();

    if (response.ok && data.success) {
      showNotificationMessage('Cập nhật sản phẩm thành công!', 'success');
      setTimeout(() => router.push('/admin/products/list-product'), 1500);
    } else {
      if (data.errors) {
        Object.entries(data.errors).forEach(([key, value]) => {
          errors[key] = Array.isArray(value) ? value[0] : value;
        });
      }
      showNotificationMessage(data.message || 'Có lỗi xảy ra khi cập nhật sản phẩm.', 'error');
      if (Object.keys(errors).some(key => key.includes('inventory'))) {
        activeTab.value = 'inventory'; // Switch to inventory tab on error
      } else if (Object.keys(errors).some(key => key.includes('variants'))) {
        activeTab.value = 'variants'; // Switch to variants tab on error
      }
      if (data.errors && Object.keys(data.errors).some(key => key.startsWith('removed_images'))) {
        removedImages.value = [];
        fetchProduct();
      }
    }
  } catch (error) {
    console.error('Error updating product:', error);
    showNotificationMessage('Có lỗi kết nối khi cập nhật sản phẩm.', 'error');
    removedImages.value = [];
    fetchProduct();
  } finally {
    loading.value = false;
  }
};

// Panel toggle
const togglePanel = (panel) => {
  panels.value[panel] = !panels.value[panel];
};

// Dropdown handling
const closeDropdowns = (event) => {
  if (!event.target.closest('.relative')) {
    activeDropdown.value = null;
  }
};

// Notification
const showNotificationMessage = (message, type) => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 5000);
};

// Lifecycle hooks
onMounted(async () => {
  if (!route.params.id) {
    showNotificationMessage('Không tìm thấy ID sản phẩm.', 'error');
    router.push('/admin/products/list-product');
    return;
  }
  formData.id = route.params.id;
  await Promise.allSettled([
    fetchProduct(),
    fetchCategories(),
    fetchTags(),
    fetchAttributes(),
    fetchSellers()
  ]);
  document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
});
</script>

<style scoped>
.scrollbar-height {
  -webkit-overflow-scrolling: touch;
  overflow-y: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>