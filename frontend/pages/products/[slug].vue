<template>
    <main class="bg-[#f5f7fa] font-sans text-sm text-[#222222]">
        <div class="max-w-[1200px] mx-auto p-6 space-y-6">
            <div class="w-full max-w-6xl">
                <div class="text-sm text-gray-500 rounded ">
                    <nuxt-link to="/">
                        <span class="text-gray-400">Trang chủ</span>
                    </nuxt-link>
                    <span class="mx-1">›</span>
                    <span class="text-black font-medium">{{ product.name }}</span>
                </div>
            </div>
            <!-- Loading State -->
            <div v-if="loading" class="text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải...
            </div>
            <!-- Error Message -->
            <div v-if="error" class="text-red-500 text-center">{{ error }}</div>
            <!-- Main Product Section -->
            <section v-if="!loading && !error" class="bg-white border border-gray-200 rounded-md p-4 md:p-6 mb-8">
                <div v-if="selectedVariant" class="flex flex-col md:flex-row gap-6">
                    <!-- Product Image Gallery -->
                    <ProductImageGallery
                        :images="images"
                        :media-base="mediaBase"
                        :current-index="currentIndex"
                        @update:current-index="currentIndex = $event"
                        @next-image="nextImage"
                        @prev-image="prevImage"
                        @start-auto-slide="startAutoSlide"
                        @pause-auto-slide="pauseAutoSlide"
                        :is-gallery-hovered="isGalleryHovered"
                    />
                    <!-- Product Info -->
                    <ProductInfo
                        :product="product"
                        :seller="seller"
                        :media-base="mediaBase"
                        :selected-variant="selectedVariant"
                        :variant-attributes="variantAttributes"
                        :selected-options="selectedOptions"
                        :quantity="quantity"
                        :is-favorite="isFavorite"
                        :is-variant-fully-selected="isVariantFullySelected"
                        :variants="variants"
                        @toggle-favorite="toggleFavorite"
                        @view-shop="viewShop"
                        @select-option="selectOption"
                        @increase-quantity="increaseQuantity"
                        @decrease-quantity="decreaseQuantity"
                        @validate-selection="onValidateSelection"
                        @add-to-cart="addToCart"
                        @buy-now="buyNow"
                        @update:quantity="quantity = $event"
                        :validation-message="validationMessage"
                        @clear-validation="validationMessage = ''"
                    />
                </div>
                <div v-else class="text-center text-gray-500">
                    Không có biến thể sản phẩm hợp lệ.
                </div>
            </section>
            <!-- Product Description -->
            <ProductDescription
                v-if="!loading && !error"
                :full-description="product.fullDescription"
                :is-collapsed="isCollapsed"
                @toggle-collapse="isCollapsed = !isCollapsed"
            />
            <!-- Phone Number -->
            <PhoneNumber
                v-if="!loading && !error && product.phone !== 'N/A'"
                :phone="product.phone"
            />
            <!-- Related Products -->
            <section v-if="!loading && !error" class="w-full mb-12 py-6 bg-gray-50">
                <h3 class="text-center text-2xl font-bold text-gray-800 mb-6 tracking-wide">
                    Sản Phẩm Liên Quan
                </h3>
                <div v-if="relatedProducts.length"
                    class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 max-w-6xl mx-auto px-4">
                    <RelatedProductItem v-for="item in displayProducts" :key="item.id" :product="item" />
                </div>
                <div v-else class="text-center text-gray-500">Không có sản phẩm liên quan</div>
                <div v-if="relatedProducts.length > 4" class="max-w-6xl mx-auto px-4 mt-6 flex justify-end">
                    <button
                        class="text-sm text-blue-600 cursor-pointer hover:underline hover:text-blue-800 transition-colors duration-200"
                        @click="showAll = !showAll" :aria-expanded="showAll">
                        {{ showAll ? 'Thu gọn' : 'Xem Tất Cả' }}
                    </button>
                </div>
            </section>

            <ProductReviews />
        </div>
    </main>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import RelatedProductItem from '../components/shared/products/RelatedProductItem.vue';
import ProductImageGallery from '../components/shared/products/ProductImageGallery.vue';
import ProductInfo from '../components/shared/products/ProductInfo.vue';
import ProductDescription from '../components/shared/products/ProductDescription.vue';
import ProductReviews from '../components/shared/reviews/ProductReviews.vue';
import PhoneNumber from '../components/shared/products/PhoneNumber.vue';

const config = useRuntimeConfig();
const route = useRoute();
const router = useRouter();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

// API Data
const apiData = ref(null);
const error = ref(null);
const loading = ref(true);

// Product Data
const product = ref({
    id: 0,
    name: '',
    slug: '',
    phone: '',
    rating: 0,
    stars: 0,
    originalPrice: '0.00',
    discountPercent: 0,
    fullDescription: '',
    sold: '0',
    stock: 0
});

// Seller Data
const seller = ref({
    store_name: '',
    avatar: null,
    products_count: 0,
    rating: 0,
    last_active: ''
});

const category = ref({});
const tag = ref({});
// Image gallery data
const images = ref([]);
const currentIndex = ref(0);
const isGalleryHovered = ref(false);

// Dynamic variant attributes
const variantAttributes = ref([]);
const selectedOptions = ref({});
const quantity = ref(1);
const variants = ref([]);
const priceKey = ref(0);

// Related products
const relatedProducts = ref([]);
const showAll = ref(false);
const displayProducts = computed(() => {
    return showAll.value ? relatedProducts.value : relatedProducts.value.slice(0, 4);
});

// Favorites state
const isFavorite = ref(false);

// Validation state
const validationMessage = ref('');

// Computed properties
const isVariantFullySelected = computed(() => {
    if (!variantAttributes.value.length) return true;
    return variantAttributes.value.every(attr => selectedOptions.value[attr.name]);
});

const selectedVariant = computed(() => {
    const defaultVariant = {
        id: null,
        price: product.value.originalPrice || '0.00',
        sale_price: null,
        original_price: product.value.originalPrice || '0.00',
        discount_percent: product.value.discountPercent || 0,
        stock: product.value.stock || 0,
        thumbnail: null
    };

    if (!variants.value?.length) {
        return defaultVariant;
    }

    const selectedKeys = Object.keys(selectedOptions.value);
    if (!selectedKeys.length) {
        return defaultVariant;
    }

    if (selectedKeys.length === variantAttributes.value.length) {
        const variant = variants.value.find(variant =>
            variant.attributes.every(attr =>
                selectedOptions.value[attr.attribute_name] === attr.value
            )
        );

        if (variant) {
            return {
                id: variant.id || null,
                price: String(variant.price || '0.00'),
                sale_price: String(variant.sale_price === 'null' ? null : variant.sale_price || null),
                original_price: String(variant.original_price || variant.price || '0.00'),
                discount_percent: Number(variant.discount_percent || 0),
                stock: Number(variant.stock || 0),
                thumbnail: variant.thumbnail || null
            };
        }
    }

    return defaultVariant;
});

// Methods
function nextImage() {
    if (images.value.length) {
        currentIndex.value = (currentIndex.value + 1) % images.value.length;
    }
}

function prevImage() {
    if (images.value.length) {
        currentIndex.value = (currentIndex.value - 1 + images.value.length) % images.value.length;
    }
}

let intervalId = null;
function startAutoSlide() {
    intervalId = setInterval(() => {
        if (images.value.length > 1 && !isGalleryHovered.value) nextImage();
    }, 4000);
}

function pauseAutoSlide() {
    if (intervalId) clearInterval(intervalId);
}

function isOptionAvailable(attrName, value) {
    const otherSelections = { ...selectedOptions.value };
    delete otherSelections[attrName];

    return variants.value.some(variant =>
        variant.attributes.some(attr => attr.attribute_name === attrName && attr.value === value) &&
        variant.stock > 0 &&
        Object.entries(otherSelections).every(([key, val]) =>
            !val || variant.attributes.some(attr => attr.attribute_name === key && attr.value === val)
        )
    );
}

function validateQuantity() {
    if (quantity.value < 1) {
        quantity.value = 1;
        validationMessage.value = `Số lượng phải từ 1 trở lên.`;
    } else if (selectedVariant.value?.stock && quantity.value > selectedVariant.value.stock) {
        quantity.value = selectedVariant.value.stock;
        validationMessage.value = `Số lượng tối đa là ${selectedVariant.value.stock} sản phẩm.`;
    } else {
        validationMessage.value = '';
    }
}

function findValidVariant(attrName, value) {
    return variants.value.find(variant =>
        variant.stock > 0 &&
        variant.attributes.some(attr => attr.attribute_name === attrName && attr.value === value)
    );
}

function onValidateSelection(callback) {
    const isValid = validateSelection();
    if (typeof callback === 'function') callback(isValid);
}
function formatPrice(price) {
    if (!price || price === 'null' || price === null || price === undefined) {
        return '0';
    }
    const parsedPrice = parseFloat(price);
    if (isNaN(parsedPrice)) {
        return '0';
    }
    return parsedPrice.toLocaleString('vi-VN', { style: 'decimal' });
}

function selectOption(attrName, value) {
    if (selectedOptions.value[attrName] === value) {
        const newSelected = { ...selectedOptions.value };
        delete newSelected[attrName];
        selectedOptions.value = newSelected;
        return;
    }
    if (isOptionAvailable(attrName, value)) {
        selectedOptions.value = { ...selectedOptions.value, [attrName]: value };
        return;
    }
    const validVariant = findValidVariant(attrName, value);
    if (validVariant) {
        selectedOptions.value = {};
        validVariant.attributes.forEach(attr => {
            selectedOptions.value[attr.attribute_name] = attr.value;
        });
    } else {
        alert(`Tùy chọn "${value}" cho "${attrName}" hiện không khả dụng hoặc đã hết hàng.`);
    }
}

function increaseQuantity() {
    if (selectedVariant.value?.stock && quantity.value < selectedVariant.value.stock) {
        quantity.value++;
    }
}

function decreaseQuantity() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}

function validateSelection() {
    const requiredAttrs = variantAttributes.value.map(attr => attr.name);
    const selectedAttrs = Object.keys(selectedOptions.value || {});

    const isValid = requiredAttrs.every(attr => selectedOptions.value[attr]);
    if (!isValid) {
        validationMessage.value = 'Vui lòng chọn Phân loại hàng';
        console.log('Validation failed:', validationMessage.value); // Debug log
        return false;
    }

    if (selectedVariant.value?.stock === 0) {
        validationMessage.value = 'Sản phẩm hiện tại đã hết hàng.';
        return false;
    }
    if (quantity.value > selectedVariant.value?.stock) {
        validationMessage.value = `Số lượng vượt quá số lượng tồn kho. Chỉ còn ${selectedVariant.value.stock} sản phẩm.`;
        quantity.value = selectedVariant.value.stock;
        return false;
    }
    const token = localStorage.getItem('access_token');
    if (!token) {
        validationMessage.value = 'Vui lòng đăng nhập để tiếp tục.';
        router.push('/login');
        return false;
    }
    validationMessage.value = '';
    return true;
}

function toggleFavorite() {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]').map(item => ({
        id: Number(item.id || 0),
        ...item
    }));
    if (isFavorite.value) {
        const index = favorites.findIndex(p => p.id === product.value.id);
        if (index >= 0) {
            favorites.splice(index, 1);
        }
    } else {
        if (!favorites.some(p => p.id === product.value.id)) {
            favorites.push({
                id: product.value.id || 0,
                name: product.value.name || 'Unknown Product',
                price: selectedVariant.value?.sale_price || selectedVariant.value?.price || '0.00',
                image: images.value[0]?.src || ''
            });
        }
    }
    isFavorite.value = !isFavorite.value;
    localStorage.setItem('favorites', JSON.stringify(favorites));
}

async function addToCart() {
    if (!validateSelection()) {
        return; // Halt if validation fails, message is set
    }

    const token = localStorage.getItem('access_token');
    const payload = {
        product_variant_id: selectedVariant.value?.id || null,
        quantity: quantity.value,
        price: selectedVariant.value?.sale_price || selectedVariant.value?.price || '0.00'
    };

    try {
        loading.value = true;
        const res = await fetch(`${apiBase}/cart/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || `Failed to add to cart: ${res.statusText}`);
        }
        alert('Đã thêm sản phẩm vào giỏ hàng.');
        quantity.value = 1;
        validationMessage.value = '';
        emit('clear-validation');
    } catch (err) {
        console.error('Add to cart error:', err);
        validationMessage.value = `${err.message}.`;
    } finally {
        loading.value = false;
    }
}

async function buyNow() {
    if (!validateSelection()) {
        return; // Halt if validation fails, message is set
    }

    const token = localStorage.getItem('access_token');
    const payload = {
        product_id: product.value.id || 0,
        variant_id: selectedVariant.value?.id || null,
        quantity: Number(quantity.value) || 1,
        price: selectedVariant.value?.sale_price || selectedVariant.value?.price || '0.00'
    };

    try {
        loading.value = true;
        const res = await fetch(`${apiBase}/orders/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || `Failed to create order: ${res.statusText}`);
        }
        router.push(`/checkout/${data.order_id}`);
    } catch (err) {
        console.error('Buy now error:', err);
        validationMessage.value = `Error creating order: ${err.message}. Please try again.`;
    } finally {
        loading.value = false;
    }
}

function viewShop() {
    router.push(`/shop/${encodeURIComponent(seller.value.store_name || '')}`);
}

async function fetchProduct() {
    try {
        loading.value = true;
        const slug = route.params.slug;
        const res = await fetch(`${apiBase}/products/slug/${slug}`);
        if (!res.ok) {
            if (res.status === 404) {
                throw new Error('Sản phẩm không tồn tại.');
            }
            throw new Error(`Lỗi khi tải sản phẩm: ${res.statusText}`);
        }
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Lỗi API');
        }

        apiData.value = data;

        product.value.id = data.data?.product?.id || 0;
        product.value.name = data.data?.product?.name || 'Sản phẩm không xác định';
        product.value.slug = data.data?.product?.slug || 'unknown-product';
        product.value.phone = data.data?.product?.phone || 'N/A';
        product.value.rating = Number(data.data?.product?.rating || 0);
        product.value.stars = Math.round(data.data?.product?.stars || 0);
        product.value.originalPrice = String(data.data?.product?.originalPrice || '0.00');
        product.value.discountPercent = Number(data.data?.product?.discountPercent || 0);
        product.value.fullDescription = data.data?.product?.fullDescription || 'No description available.';
        product.value.sold = String(data.data?.product?.sold || '0');
        product.value.stock = Number(data.data?.product?.stock || 0);

        seller.value = {
            store_name: data.data?.product?.seller?.store_name || 'Unknown Seller',
            avatar: data.data?.product?.seller?.avatar || null,
            products_count: Number(data.data?.product?.seller?.products_count || 0),
            rating: Number(data.data?.product?.seller?.rating || 0),
            last_active: data.data?.product?.seller?.last_active || 'Not recently active'
        };
        category.value = {
            name: data.data?.product?.category?.name || 'Unknown Category',
            slug: data.data?.product?.category?.slug || 'unknown-category'
        }
        variants.value = (data.data?.product?.variants || []).map(variant => ({
            id: Number(variant.id || 0),
            thumbnail: variant.thumbnail || null,
            price: String(variant.price || '0.00'),
            sale_price: String(variant.sale_price === 'null' ? null : variant.sale_price || null),
            original_price: String(variant.original_price || variant.price || '0.00'),
            discount_percent: Number(variant.discount_percent || 0),
            attributes: variant.attributes || [],
            stock: Number(variant.stock || 0)
        }));

        const productImages = (data.data?.product?.images || []).map(img => ({
            src: img.src || '/default-product.jpg',
            alt: img.alt || 'Product image',
            type: 'product'
        }));

        const thumbnailMap = new Map();
        variants.value.forEach(variant => {
            if (variant.thumbnail) {
                const key = variant.thumbnail;
                if (!thumbnailMap.has(key)) {
                    const colorAttr = variant.attributes.find(attr => attr.attribute_name === 'Color');
                    thumbnailMap.set(key, {
                        src: variant.thumbnail,
                        alt: colorAttr ? `Variant ${colorAttr.value}` : 'Variant image',
                        type: 'variant',
                        variantId: variant.id
                    });
                }
            }
        });

        images.value = [...productImages, ...thumbnailMap.values()];

        const attributesMap = new Map();
        variants.value.forEach(variant => {
            (variant.attributes || []).forEach(attr => {
                if (!attributesMap.has(attr.attribute_name)) {
                    attributesMap.set(attr.attribute_name, new Map());
                }
                if (!attributesMap.get(attr.attribute_name).has(attr.value)) {
                    attributesMap.get(attr.attribute_name).set(attr.value, {
                        value: attr.value,
                        label: attr.label || attr.value,
                        thumbnail: attr.attribute_name === 'Color' ? variant.thumbnail : null,
                        alt: attr.attribute_name === 'Color' ? `Color ${attr.value}` : null
                    });
                }
            });
        });

        variantAttributes.value = Array.from(attributesMap.entries()).map(([name, valueMap]) => ({
            name,
            options: Array.from(valueMap.values())
        }));

        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]').map(item => ({
            id: Number(item.id || 0),
            ...item
        }));
        isFavorite.value = favorites.some(p => p.id === product.value.id);

        relatedProducts.value = (data.data?.related_products || []).map(item => ({
            id: Number(item.id || 0),
            name: item.name || 'Unknown Product',
            slug: item.slug || 'unknown-product',
            price: String(item.price || '0.00'),
            image: String(item.image || '/default-product.jpg')
        }));

        priceKey.value++;
    } catch (err) {
        console.error('Error fetching product:', err);
        error.value = err.message || 'Unable to load product details. Please try again later.';
    } finally {
        loading.value = false;
    }
}

watch(selectedOptions, (newOptions) => {
    const variant = selectedVariant.value;
    if (variant?.thumbnail) {
        const variantImageIndex = images.value.findIndex(img => img.src === variant.thumbnail && img.type === 'variant');
        if (variantImageIndex >= 0) {
            currentIndex.value = variantImageIndex;
        }
    }
    if (variant?.stock === 0 && isVariantFullySelected.value) {
        validationMessage.value = 'Biến thể này hiện đã hết hàng.';
        const lastSelectedAttr = Object.keys(newOptions).pop();
        if (lastSelectedAttr) {
            const newSelected = { ...selectedOptions.value };
            delete newSelected[lastSelectedAttr];
            selectedOptions.value = newSelected;
        }
    }
    validateQuantity();
}, { deep: true });

onMounted(() => {
    fetchProduct();
    startAutoSlide();
});

onBeforeUnmount(() => {
    pauseAutoSlide();
});
</script>

<style scoped>
.thumbs::-webkit-scrollbar {
    display: none;
}

.scroller {
    -ms-overflow-style: none;
    scrollbar-width: none;
    touch-action: pan-x;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 768px) {
    .flex-col.md\:flex-row {
        flex-direction: column;
    }

    .w-\[480px\] {
        width: 100%;
        height: 300px;
    }

    .thumbs:hover {
        max-width: 100%;
    }
}
</style>