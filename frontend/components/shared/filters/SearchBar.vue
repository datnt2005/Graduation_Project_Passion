    <template>
        <!-- Tìm kiếm (desktop) -->
        <div class="flex-1 mx-4 hidden  sm:flex justify-center">
            <form @submit.prevent="handleSearch"
                class="flex w-full max-w-[500px] border border-gray-300 rounded-full overflow-hidden bg-white shadow-sm">
                <!-- Icon tìm kiếm -->
                <div class="flex items-center px-3 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Ô nhập -->
                <input type="text" v-model="input" placeholder="Tìm kiếm sản phẩm..."
                    class="flex-grow px-2 py-2 text-sm focus:outline-none" />

                <!-- Nút tìm -->
                <button type="submit"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 transition whitespace-nowrap">
                    Tìm kiếm
                </button>
            </form>
        </div>

        <!-- Tìm kiếm (mobile) -->
        <div class="px-4 pb-3 sm:hidden">
            <div class="relative">
                <input type="text" v-model="input" @input="updateSearch" placeholder="Tìm kiếm"
                    class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-full focus:outline-none" />
                <button
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#1BA0E2] p-2 rounded-full hover:bg-blue-600"
                    @click="handleSearch">
                    <!-- Icon kính lúp SVG nhỏ gọn -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                    </svg>
                </button>
            </div>
        </div>
    </template>

<script setup>
import { ref } from 'vue';
import { useSearchStore } from '~/stores/search';
import { useRouter } from 'vue-router';

const input = ref('');
const searchStore = useSearchStore();
const router = useRouter();

function handleSearch() {
    if (input.value.trim() === '') return;
    searchStore.updateSearch(input.value);
    router.push({ path: '/shop/search', query: { search: input.value.trim() } });
}

function updateSearch() {
    if (input.value.trim() === '') {
        searchStore.updateSearch('');
        return;
    }
    searchStore.updateSearch(input.value);
}
</script>