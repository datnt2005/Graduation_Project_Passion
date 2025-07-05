<template>
    <div class="relative" ref="wrapper">
        <!-- Tìm kiếm (mobile) -->
        <div class="flex sm:hidden items-center px-4">
            <button class="text-gray-600" @click="showMobileSearch = !showMobileSearch" aria-label="Toggle search">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            <div v-if="showMobileSearch" class="fixed inset-0 bg-white z-50 p-4">
                <div class="flex items-center justify-between">
                    <form @submit.prevent="handleSearch"
                        class="flex w-full border border-gray-200 rounded-full overflow-hidden bg-white shadow-md">
                        <input type="text" v-model="input" placeholder="Tìm kiếm sản phẩm..."
                            class="flex-grow px-3 py-2.5 text-sm focus:outline-none placeholder-gray-400"
                            @focus="fetchSuggestions" @input="updateSearch" @keyup.enter="handleSearch" />
                        <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition rounded-r-full">
                            Tìm
                        </button>
                    </form>
                    <button class="ml-2 text-gray-600" @click="showMobileSearch = false" aria-label="Close search">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Dropdown gợi ý (mobile) -->
                <div v-if="showSuggestions && showMobileSearch"
                    class="mt-2 bg-white border border-gray-200 rounded-lg shadow-xl w-full">
                    <!-- Loading -->
                    <div v-if="loadingSuggestions" class="px-4 py-3 text-sm text-gray-500 flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Đang tải gợi ý...
                    </div>

                    <!-- Từ khóa phổ biến hệ thống -->
                    <div v-if="suggestions.top_keywords.length && !loadingSuggestions"
                        class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Từ khóa phổ biến</h3>
                        <ul class="mt-2 space-y-1">
                            <li v-for="keyword in suggestions.top_keywords.slice(0, 5)" :key="keyword"
                                class="py-2 px-3 text-sm text-gray-600 hover:bg-gray-50 cursor-pointer rounded-md transition"
                                @click="selectSuggestion(keyword)">
                                <span>{{ keyword }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Lịch sử tìm kiếm cá nhân -->
                    <div v-if="suggestions.history.length && !loadingSuggestions"
                        class="px-4 py-3 border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-gray-700">Lịch sử tìm kiếm</h3>
                            <button class="text-xs text-red-500 hover:text-red-600 font-medium" @click="clearHistory">
                                Xóa tất cả
                            </button>
                        </div>
                        <ul class="mt-2 space-y-1">
                            <li v-for="keyword in suggestions.history" :key="keyword"
                                class="flex justify-between items-center py-2 px-3 text-sm text-gray-600 hover:bg-gray-50 cursor-pointer rounded-md transition"
                                @click="selectSuggestion(keyword)">
                                <span>{{ keyword }}</span>
                                <button class="text-gray-400 hover:text-red-500" @click.stop="removeKeyword(keyword)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Sản phẩm phổ biến -->
                    <div v-if="suggestions.top_products.length && !loadingSuggestions" class="px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-700">Sản phẩm phổ biến</h3>
                        <ul class="mt-2 space-y-1">
                            <li v-for="product in suggestions.top_products" :key="product.id"
                                class="flex items-center py-2 px-3 text-sm text-gray-600 hover:bg-gray-50 cursor-pointer rounded-md transition"
                                @click="selectProduct(product.id)">
                                <img v-if="product.image" :src="product.image" class="w-8 h-8 mr-2 rounded object-cover"
                                    alt="Product image" @error="(e) => (e.target.src = '/default-image.jpg')" />
                                <span class="truncate">{{ product.name }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tìm kiếm (desktop) -->
        <div class="flex-1 mx-4 hidden sm:flex justify-center w-[600px] relative">
            <form @submit.prevent="handleSearch"
                class="flex w-full max-w-[500px] border border-gray-200 rounded-full overflow-hidden bg-white shadow-md transition-all duration-300">
                <div class="flex items-center px-4 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" v-model="input" placeholder="Tìm kiếm sản phẩm..."
                    class="flex-grow px-3 py-2.5 text-sm focus:outline-none placeholder-gray-400"
                    @focus="fetchSuggestions" @input="updateSearch" @keyup.enter="handleSearch" />
                <button type="submit"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 transition whitespace-nowrap">
                    Tìm kiếm
                </button>
            </form>
            <!-- Dropdown gợi ý (desktop) -->
            <div v-if="showSuggestions"
                class="absolute z-20 bg-white border border-gray-200 rounded-2xl shadow-lg w-full max-w-[500px] mx-4 mt-[50px] overflow-hidden transition-all duration-200">
                <!-- Loading -->
                <div v-if="loadingSuggestions" class="px-5 py-4 text-sm text-gray-500 flex items-center">
                    <svg class="animate-spin h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    Đang tải gợi ý...
                </div>

                <!-- Từ khóa phổ biến hệ thống -->
                <div v-if="suggestions.top_keywords.length && !loadingSuggestions"
                    class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3"> Từ khóa phổ biến</h3>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="keyword in suggestions.top_keywords.slice(0, 10)" :key="keyword"
                            @click="selectSuggestion(keyword)"
                            class="cursor-pointer inline-block px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded-full hover:bg-blue-100 hover:text-blue-800 transition">
                            {{ keyword }}
                        </span>
                    </div>
                </div>

                <!-- Lịch sử tìm kiếm -->
                <div v-if="suggestions.history.length && !loadingSuggestions"
                    class="px-5 py-4 border-b border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-sm font-semibold text-gray-800"> Lịch sử tìm kiếm</h3>
                        <button class="text-xs text-red-500 hover:text-red-600 font-medium" @click="clearHistory">
                            Xóa tất cả
                        </button>
                    </div>
                    <ul class="space-y-2">
                        <li v-for="keyword in suggestions.history" :key="keyword"
                            class="flex justify-between items-center py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer rounded-lg transition"
                            @click="selectSuggestion(keyword)">
                            <span>{{ keyword }}</span>
                            <button class="text-gray-400 hover:text-red-500" @click.stop="removeKeyword(keyword)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Sản phẩm phổ biến -->
                <div v-if="suggestions.top_products.length && !loadingSuggestions" class="px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">⭐ Sản phẩm nổi bật</h3>
                    <ul class="space-y-2">
                        <li v-for="product in suggestions.top_products" :key="product.id"
                            class="flex items-center py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer rounded-lg transition"
                            @click="selectProduct(product.id)">
                            <img v-if="product.image" :src="product.image"
                                class="w-9 h-9 mr-3 rounded-full object-cover border" alt="Product image"
                                @error="(e) => (e.target.src = '/default-image.jpg')" />
                            <span class="truncate">{{ product.name }}</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useSearchStore } from '~/stores/search';
import { useAuthStore } from '~/stores/auth';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { debounce } from 'lodash-es';

const input = ref('');
const showSuggestions = ref(false);
const loadingSuggestions = ref(false);
const showMobileSearch = ref(false);
const suggestions = ref({ history: [], top_keywords: [], top_products: [] });
const searchStore = useSearchStore();
const authStore = useAuthStore();
const router = useRouter();
const apiBase = useRuntimeConfig().public.apiBaseUrl;
const wrapper = ref(null);

function generateSessionId() {
    let sessionId = sessionStorage.getItem('session_id');
    if (!sessionId) {
        sessionId = 'sess_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('session_id', sessionId);
    }
    return sessionId;
}

async function fetchSuggestions() {
    showSuggestions.value = true;
    loadingSuggestions.value = true;
    try {
        const response = await axios.get(`${apiBase}/search/suggestions`, {
            params: {
                user_id: authStore.user?.id || null,
                session_id: generateSessionId(),
            },
        });
        suggestions.value = {
            history: response.data.data.history || [],
            top_keywords: (response.data.data.top_keywords || []).slice(0, 5),
            top_products: response.data.data.top_products || [],
        };
        console.log('Suggestions fetched:', suggestions.value);
    } catch (error) {
        console.error('Error fetching suggestions:', error.response?.data || error.message);
        suggestions.value = { history: [], top_keywords: [], top_products: [] };
    } finally {
        loadingSuggestions.value = false;
    }
}

const updateSearch = debounce(() => {
    if (input.value.trim() === '') {
        searchStore.updateSearch('');
        showSuggestions.value = false;
        return;
    }
    searchStore.updateSearch(input.value);
}, 300);

async function handleSearch() {
    if (input.value.trim() === '') return;
    searchStore.updateSearch(input.value);
    try {
        await axios.post(`${apiBase}/search/add`, {
            keyword: input.value.trim(),
            user_id: authStore.user?.id || null,
            session_id: generateSessionId(),
        });
        router.push({ path: '/shop/search', query: { search: input.value.trim() } });
        showSuggestions.value = false;
        showMobileSearch.value = false;
    } catch (error) {
        console.error('Error adding search keyword:', error.response?.data || error.message);
    }
}

function selectSuggestion(keyword) {
    input.value = keyword;
    handleSearch();
}

function selectProduct(productId) {
    router.push({ path: `/product/${productId}` });
    showSuggestions.value = false;
    showMobileSearch.value = false;
}

async function removeKeyword(keyword) {
    console.log('Deleting keyword:', keyword, 'User ID:', authStore.user?.id, 'Session ID:', sessionStorage.getItem('session_id'));
    try {
        await axios.delete(`${apiBase}/search/history`, {
            params: {
                user_id: authStore.user?.id || null,
                session_id: sessionStorage.getItem('session_id'),
                keyword,
            },
        });
        suggestions.value.history = suggestions.value.history.filter((k) => k !== keyword);
    } catch (error) {
        console.error('Error removing keyword:', error.response?.data || error.message);
    }
}

async function clearHistory() {
    console.log('Clearing history for User ID:', authStore.user?.id, 'Session ID:', sessionStorage.getItem('session_id'));
    try {
        await axios.delete(`${apiBase}/search/history`, {
            params: {
                user_id: authStore.user?.id || null,
                session_id: sessionStorage.getItem('session_id'),
            },
        });
        suggestions.value.history = [];
    } catch (error) {
        console.error('Error clearing history:', error.response?.data || error.message);
    }
}

function handleClickOutside(event) {
    if (wrapper.value && !wrapper.value.contains(event.target)) {
        showSuggestions.value = false;
        showMobileSearch.value = false;
    }
}

onMounted(() => {
    generateSessionId();
    document.addEventListener('click', handleClickOutside);
    if (authStore.user?.id) {
        searchStore.setUserId(authStore.user.id);
        axios.post(`${apiBase}/search/sync-history`, null, {
            params: { session_id: sessionStorage.getItem('session_id') },
            headers: { Authorization: `Bearer ${localStorage.getItem('access_token') || ''}` },
        }).then(() => {
            console.log('History synced for user:', authStore.user.id);
            fetchSuggestions();
        }).catch((error) => {
            console.error('Error syncing history:', error.response?.data || error.message);
        });
    } else {
        fetchSuggestions();
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.bg-blue-100 {
    background-color: #e0f2fe;
}

.text-blue-600 {
    color: #2563eb;
}

.bg-blue-200:hover {
    background-color: #bfdbfe;
}
</style>