
<template>
 <div class="p-4 sm:p-6 max-w-7xl mx-auto">
  <div class="flex flex-col lg:flex-row gap-6">
    
    <!-- Cột nội dung chính -->
    <div class="flex-1">
      <h1 class="mb-4 text-lg sm:text-xl font-semibold text-gray-900">Thêm sản phẩm mới</h1>
      <!-- Notification -->
   

      <!-- Product Name and Tabs -->
      <!-- <div class="border border-gray-300 rounded-md shadow-sm">
        <div class="flex border-b border-gray-300 bg-gray-100 text-gray-700 text-sm font-semibold select-none">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="selectedTab = tab.value"
            :class="[
              'px-4 py-2 border-r border-gray-300 transition-colors',
              selectedTab === tab.value ? 'bg-white text-gray-900' : 'text-gray-600 hover:bg-gray-200',
              tab.disabled ? 'text-gray-400 cursor-not-allowed' : ''
            ]"
            :disabled="tab.disabled"
            :aria-disabled="tab.disabled ? 'true' : 'false'"
            :aria-label="`Chọn tab ${tab.label}`"
          >
            {{ tab.label }}
          </button>
        </div>
        <input
          v-model="productName"
          type="text"
          placeholder="Tên sản phẩm"
          class="w-full border-none px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
          aria-label="Tên sản phẩm"
        />
      </div> -->

      <!-- Product Description -->
      <div class="mt-6 border border-gray-300 rounded-md shadow-sm">
        <div class="px-4 py-3 text-sm font-semibold text-gray-700 border-b border-gray-300">Mô tả sản phẩm</div>
        <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-b border-gray-300 text-xs text-gray-700">
          <button
            class="flex items-center gap-1 border border-gray-300 rounded px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 transition-colors"
            @click="addMedia"
            aria-label="Thêm media"
          >
            <font-awesome-icon icon="image" /> Thêm Media
          </button>
          <span class="text-xs cursor-help select-none" title="Trợ giúp về mô tả sản phẩm">?</span>
          <input type="file" ref="mediaInput" class="hidden" accept="image/*" @change="handleMediaUpload" />
        </div>
        <div class="px-4 py-3">
          <div class="flex flex-wrap gap-2 mb-3 text-xs text-gray-700">
            <select class="border border-gray-300 rounded px-2 py-1 text-xs text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
              <option>Định dạng</option>
            </select>
            <select class="border border-gray-300 rounded px-2 py-1 text-xs text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
              <option>Đoạn văn</option>
            </select>
            <button class="font-bold px-2 hover:bg-gray-100 rounded" aria-label="Định dạng in đậm">B</button>
            <button class="italic px-2 hover:bg-gray-100 rounded" aria-label="Định dạng nghiêng">I</button>
            <button class="px-2 hover:bg-gray-100 rounded" aria-label="Danh sách dấu đầu dòng">•</button>
            <button class="px-2 hover:bg-gray-100 rounded" aria-label="Danh sách số">1.</button>
            <button class="px-2 hover:bg-gray-100 rounded" aria-label="Trích dẫn">“</button>
            <button class="px-2 hover:bg-gray-100 rounded" aria-label="Căn lề">≡</button>
            <button class="px-2 hover:bg-gray-100 rounded" aria-label="Liên kết"><font-awesome-icon icon="link" /></button>
            <button class="px-2 hover:bg-gray-100 rounded" aria-label="Shortcodes">Shortcodes ▼</button>
          </div>
          <textarea
            v-model="productDescription"
            aria-label="Mô tả sản phẩm"
            class="w-full h-48 sm:h-64 border border-gray-300 rounded resize-y p-3 text-sm text-gray-900 font-sans focus:outline-none focus:ring-2 focus:ring-blue-600"
            spellcheck="false"
            @input="updateWordCount"
          ></textarea>
          <div class="text-xs text-gray-500 mt-2">Số từ: {{ wordCount }}</div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
     <div class="w-full lg:w-80 space-y-4 text-xs text-gray-700 font-normal">
      <!-- Publish Panel -->
      <section class="border border-gray-300 rounded-md shadow-sm bg-white">
        <header
          class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
          @click="togglePanel('publish')"
          :aria-expanded="panels.publish"
          aria-label="Toggle Publish panel"
        >
          <span>Thêm sản phẩm</span>
          <font-awesome-icon :icon="panels.publish ? 'chevron-up' : 'chevron-down'" />
        </header>
        <div v-if="panels.publish" class="p-4 space-y-3">
          <button
            class="border border-gray-300 rounded px-3 py-2 text-xs text-blue-700 hover:bg-gray-100 transition-colors w-full text-left"
            @click="saveDraft"
            aria-label="Lưu nháp"
          >
            Lưu nháp
          </button>
          <button
            class="border border-gray-300 rounded px-3 py-2 text-xs text-gray-700 hover:bg-gray-100 transition-colors w-full text-left"
            @click="previewProduct"
            aria-label="Xem trước"
          >
            Xem trước
          </button>
          <div>
            <span><i class="fa-solid fa-key"></i> Trạng thái: </span>
            <span class="font-semibold">{{ productStatus }}</span>
            <a href="#" class="underline text-blue-700 hover:text-blue-900" @click.prevent="editStatus">Chỉnh sửa</a>
          </div>
          <div>
            <span><i class="fa-solid fa-eye"></i> Hiển thị: </span>
            <span class="font-semibold">{{ visibility }}</span>
            <a href="#" class="underline text-blue-700 hover:text-blue-900" @click.prevent="editVisibility">Chỉnh sửa</a>
          </div>
          <div>
            <span><i class="fa-solid fa-calendar-days"></i> Xuất bản ngay </span>
            <a href="#" class="underline text-blue-700 hover:text-blue-900" @click.prevent="editPublishDate">Chỉnh sửa</a>
          </div>
          <div>
            <a href="#" class="underline text-blue-700 hover:text-blue-900">Phân tích SEO:</a> Chưa khả dụng
          </div>
          <div>
            <a href="#" class="underline text-blue-700 hover:text-blue-900">Phân tích khả năng đọc:</a> Chưa khả dụng
          </div>
          <div>
            Hiển thị danh mục: Shop và kết quả tìm kiếm
            <a href="#" class="underline text-blue-700 hover:text-blue-900" @click.prevent="editCatalogVisibility">Chỉnh sửa</a>
          </div>
          <div>
            <a href="#" class="underline text-blue-700 hover:text-blue-900" @click.prevent="copyToDraft">Sao chép thành nháp mới</a>
          </div>
          <button
            class="bg-blue-700 text-white rounded px-4 py-2 text-sm font-semibold hover:bg-blue-800 transition-colors w-full"
            @click="publishProduct"
            aria-label="Xuất bản sản phẩm"
          >
            Thêm sản phẩm
          </button>
        </div>
      </section>

      <!-- Product Image -->
      <section class="border border-gray-300 rounded-md shadow-sm bg-white">
        <header
          class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
          @click="togglePanel('image')"
          :aria-expanded="panels.image"
          aria-label="Toggle Product image panel"
        >
          <span>Hình ảnh sản phẩm</span>
          <font-awesome-icon :icon="panels.image ? 'chevron-up' : 'chevron-down'" />
        </header>
        <div v-if="panels.image" class="p-4 text-xs">
          <a
            href="#"
            class="underline text-blue-700 hover:text-blue-900"
            @click.prevent="$refs.productImageInput.click()"
            aria-label="Chọn hình ảnh sản phẩm"
          >
            Chọn hình ảnh sản phẩm
          </a>
          <span class="cursor-help select-none ml-2" title="Trợ giúp về hình ảnh sản phẩm">?</span>
          <input type="file" ref="productImageInput" class="hidden" accept="image/*" @change="handleProductImageUpload" />
          <img v-if="productImage" :src="productImage" alt="Hình ảnh sản phẩm" class="mt-2 w-full h-32 object-cover rounded" />
        </div>
      </section>

      <!-- Product Gallery -->
      <section class="border border-gray-300 rounded-md shadow-sm bg-white">
        <header
          class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
          @click="togglePanel('gallery')"
          :aria-expanded="panels.gallery"
          aria-label="Toggle Product gallery panel"
        >
          <span>Thư viện sản phẩm</span>
          <font-awesome-icon :icon="panels.gallery ? 'chevron-up' : 'chevron-down'" />
        </header>
        <div v-if="panels.gallery" class="p-4 text-xs">
          <a
            href="#"
            class="underline text-blue-700 hover:text-blue-900"
            @click.prevent="$refs.galleryInput.click()"
            aria-label="Thêm hình ảnh thư viện"
          >
            Thêm hình ảnh thư viện
          </a>
          <span class="cursor-help select-none ml-2" title="Trợ giúp về thư viện sản phẩm">?</span>
          <input type="file" ref="galleryInput" class="hidden" accept="image/*" multiple @change="handleGalleryUpload" />
          <div v-if="galleryImages.length" class="grid grid-cols-2 gap-2 mt-2">
            <img v-for="(img, index) in galleryImages" :key="index" :src="img" alt="Hình ảnh thư viện" class="w-full h-20 object-cover rounded" />
          </div>
        </div>
      </section>

      <!-- Product Categories -->
      <section class="border border-gray-300 rounded-md shadow-sm bg-white">
        <header
          class="flex justify-between items-center px-4 py-3 border-b border-gray-300 font-semibold cursor-pointer select-none"
          @click="togglePanel('categories')"
          :aria-expanded="panels.categories"
          aria-label="Toggle Product categories panel"
        >
          <span>Danh mục sản phẩm</span>
          <font-awesome-icon :icon="panels.categories ? 'chevron-up' : 'chevron-down'" />
        </header>
        <div v-if="panels.categories" class="p-4 text-xs">
          <div class="mb-3 border-b border-gray-300 pb-2">
            <button
              :class="['mr-2', categoryTab === 'all' ? 'text-blue-700 underline' : 'text-gray-600']"
              @click="categoryTab = 'all'"
              aria-label="Xem tất cả danh mục"
            >
              Tất cả danh mục
            </button>
            <button
              :class="[categoryTab === 'mostUsed' ? 'text-blue-700 underline' : 'text-gray-600']"
              @click="categoryTab = 'mostUsed'"
              aria-label="Xem danh mục thường dùng"
            >
              Thường dùng
            </button>
          </div>
          <form class="space-y-2">
            <label v-for="category in filteredCategories" :key="category.id" class="flex items-center gap-2">
              <input type="checkbox" v-model="selectedCategories" :value="category.id" />
              {{ category.name }}
            </label>
          </form>
        </div>
      </section>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  layout: 'default-admin'
})

// Notification
const showNotification = ref(true)
const updatePlugins = () => {
  console.log('Updating plugins...')
  // Thực tế: Gọi API cập nhật plugin
}
const dismissNotification = () => {
  showNotification.value = false
}

// Tabs
// const tabs = [
//   { label: 'Editor', value: 'editor', disabled: false },
//   { label: 'UX', value: 'ux', disabled: true },
//   { label: 'Builder', value: 'builder', disabled: false }
// ]
const selectedTab = ref('editor')

// Product data
const productName = ref('')
const productDescription = ref('')
const focusKeyphrase = ref('')
const wordCount = ref(0)
const productStatus = ref('Draft')
const visibility = ref('Public')
const productImage = ref('')
const galleryImages = ref([])
const selectedCategories = ref([])
const categoryTab = ref('all')

// Panel toggle
const panels = ref({
  yoast: true,
  publish: true,
  image: true,
  gallery: true,
  categories: true
})
const togglePanel = (panel) => {
  panels.value[panel] = !panels.value[panel]
}

// Word count
const updateWordCount = () => {
  const words = productDescription.value.trim().split(/\s+/).filter(word => word.length > 0)
  wordCount.value = words.length
}

// Media upload
const mediaInput = ref(null)
const productImageInput = ref(null)
const galleryInput = ref(null)

const addMedia = () => {
  mediaInput.value.click()
}
const handleMediaUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    console.log('Media uploaded:', file)
    // Thực tế: Upload file lên server và lưu URL
  }
}
const handleProductImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    productImage.value = URL.createObjectURL(file)
  }
}
const handleGalleryUpload = (event) => {
  const files = Array.from(event.target.files)
  galleryImages.value = files.map(file => URL.createObjectURL(file))
}

// Publish actions
const saveDraft = () => {
  console.log('Saving draft:', { productName: productName.value, productDescription: productDescription.value })
  // Thực tế: Gọi API lưu nháp
}
const previewProduct = () => {
  console.log('Previewing product...')
  // Thực tế: Mở preview trong tab mới
}
const publishProduct = () => {
  console.log('Publishing product:', {
    productName: productName.value,
    productDescription: productDescription.value,
    focusKeyphrase: focusKeyphrase.value,
    productImage: productImage.value,
    galleryImages: galleryImages.value,
    selectedCategories: selectedCategories.value
  })
  // Thực tế: Gọi API xuất bản
}
const editStatus = () => {
  console.log('Editing status...')
  // Thực tế: Mở modal chỉnh sửa trạng thái
}
const editVisibility = () => {
  console.log('Editing visibility...')
  // Thực tế: Mở modal chỉnh sửa hiển thị
}
const editPublishDate = () => {
  console.log('Editing publish date...')
  // Thực tế: Mở modal chọn ngày
}
const editCatalogVisibility = () => {
  console.log('Editing catalog visibility...')
  // Thực tế: Mở modal chỉnh sửa
}
const copyToDraft = () => {
  console.log('Copying to new draft...')
  // Thực tế: Tạo bản sao nháp
}

// Categories
const categories = ref([
  { id: 1, name: 'Đồ ở nhà' },
  { id: 2, name: 'Quần áo nam' },
  { id: 3, name: 'Quần áo nữ' },
  { id: 4, name: 'Quần áo trẻ em' }
])
const filteredCategories = computed(() => {
  if (categoryTab.value === 'all') return categories.value
  // Giả lập danh mục thường dùng
  return categories.value.slice(0, 2)
})

// Fetch categories (simulate API)
const fetchCategories = async () => {
  try {
    // Thay bằng API thực tế
    // const response = await fetch('/api/categories')
    // categories.value = await response.json()
    await new Promise(resolve => setTimeout(resolve, 1000))
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
/* Responsive styles */
@media (max-width: 640px) {
  .sm\:fixed {
    position: static;
    width: 100%;
  }
  .sm\:w-80 {
    width: 100%;
  }
  .text-xs {
    font-size: 0.875rem;
  }
  button, input, select, textarea {
    padding: 0.5rem;
  }
  textarea {
    height: 12rem;
  }
}
</style>
```