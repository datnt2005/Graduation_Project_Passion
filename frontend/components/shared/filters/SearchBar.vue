<template>
  <div class="relative" ref="wrapper">
    <!-- Tìm kiếm (desktop) -->
    <div class="flex-1 mx-4 hidden sm:flex justify-center w-[600px] relative">
      <form @submit.prevent="handleSearch" class="flex w-full max-w-[500px] border border-gray-200 rounded-full overflow-hidden bg-white shadow-md transition-all duration-300">
        <div class="flex items-center px-4 text-gray-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          type="text"
          v-model="input"
          placeholder="Tìm kiếm sản phẩm..."
          class="flex-grow px-3 py-2.5 text-sm focus:outline-none placeholder-gray-400"
          @focus="handleInputFocus"
          @input="updateSearch"
          @keyup.enter="handleSearch"
        />
        <button type="submit" class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 transition whitespace-nowrap">Tìm kiếm</button>
      </form>

      <!-- Dropdown Gợi ý -->
      <div v-if="showSuggestions" class="absolute z-20 bg-white border border-gray-200 rounded-2xl shadow-lg w-full max-w-[500px] mx-4 mt-[50px] overflow-hidden transition-all duration-200">
        <div v-if="suggestions.top_keywords.length && !loadingSuggestions" class="px-5 py-4 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-800 mb-3">Từ khóa nổi bật</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="keyword in suggestions.top_keywords"
              :key="keyword"
              @click="selectSuggestion(keyword)"
              class="cursor-pointer inline-block px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded-full hover:bg-blue-100 hover:text-blue-800 transition"
            >
              {{ keyword }}
            </span>
          </div>
        </div>

        <div v-if="input.trim() && suggestions.top_products.length" class="px-5 py-4 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-800 mb-2">Gợi ý cho "{{ input }}"</h3>
          <ul class="space-y-2">
            <li
              v-for="product in suggestions.top_products"
              :key="product.id"
              class="flex items-center py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer rounded-lg transition"
              @click="selectProduct(product.id)"
            >
              <img
                v-if="product.image"
                :src="product.image"
                class="w-9 h-9 mr-3 rounded-full object-cover border"
                @error="(e) => (e.target.src = '/default-image.jpg')"
              />
              <span class="truncate">{{ product.name }}</span>
            </li>
          </ul>
        </div>

        <div v-if="suggestions.history.length && !loadingSuggestions" class="px-5 py-4">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-sm font-semibold text-gray-800">Lịch sử tìm kiếm</h3>
            <button class="text-xs text-red-500 hover:text-red-600 font-medium" @click="clearHistory">Xóa tất cả</button>
          </div>
          <ul class="space-y-2">
            <li
              v-for="keyword in suggestions.history"
              :key="keyword"
              class="flex justify-between items-center py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer rounded-lg transition"
              @click="selectSuggestion(keyword)"
            >
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M12 2a10 10 0 100 20 10 10 0 000-20z" />
                </svg>
                {{ keyword }}
              </span>
              <button class="text-gray-400 hover:text-red-500" @click.stop="removeKeyword(keyword)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useSearchStore } from '~/stores/search'
import { useAuthStore } from '~/stores/auth'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { debounce } from 'lodash-es'

const input = ref('')
const showSuggestions = ref(false)
const loadingSuggestions = ref(false)
const suggestions = ref({ history: [], top_keywords: [], top_products: [] })

const searchStore = useSearchStore()
const authStore = useAuthStore()
const router = useRouter()
const wrapper = ref(null)
const apiBase = useRuntimeConfig().public.apiBaseUrl

function getUserId() {
  return authStore.isLoggedIn ? authStore.currentUser?.id : null
}

function generateSessionId() {
  let sessionId = localStorage.getItem('session_id')
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9)
    localStorage.setItem('session_id', sessionId)
  }
  return sessionId
}

const updateSearch = debounce(() => {
  if (input.value.trim() === '') {
    searchStore.updateSearch('')
    showSuggestions.value = false
    return
  }
  searchStore.updateSearch(input.value)
}, 300)

function handleInputFocus() {
  showSuggestions.value = true
  fetchSuggestions()
}

async function fetchSuggestions() {
  loadingSuggestions.value = true
  const sessionId = generateSessionId()

  try {
    const res = await axios.get(`${apiBase}/search/suggestions`, {
      params: {
        user_id: getUserId(),
        session_id: sessionId,
      },
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token') || ''}`,
      },
    })

    suggestions.value = {
      history: res.data.data.history || [],
      top_keywords: (res.data.data.top_keywords || []).slice(0, 5),
      top_products: res.data.data.top_products || [],
    }
  } catch (error) {
    console.error('Lỗi fetchSuggestions:', error.response?.data || error.message)
    suggestions.value = { history: [], top_keywords: [], top_products: [] }
  } finally {
    loadingSuggestions.value = false
  }
}

async function handleSearch() {
  if (input.value.trim() === '') return

  searchStore.updateSearch(input.value)
  const sessionId = generateSessionId()

  try {
    await axios.post(
      `${apiBase}/search/add`,
      {
        keyword: input.value.trim(),
        user_id: getUserId(),
        session_id: sessionId,
      },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('access_token') || ''}`,
        },
      }
    )

    router.push({ path: '/shop/search', query: { search: input.value.trim() } })
    showSuggestions.value = false
  } catch (error) {
    console.error('Lỗi handleSearch:', error.response?.data || error.message)
  }
}

async function removeKeyword(keyword) {
  const sessionId = localStorage.getItem('session_id')

  try {
    await axios.delete(`${apiBase}/search/history`, {
      params: {
        user_id: getUserId(),
        session_id: sessionId,
        keyword,
      },
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token') || ''}`,
      },
    })

    suggestions.value.history = suggestions.value.history.filter(k => k !== keyword)
  } catch (error) {
    console.error('Lỗi removeKeyword:', error.response?.data || error.message)
  }
}

async function clearHistory() {
  const sessionId = localStorage.getItem('session_id')

  try {
    await axios.delete(`${apiBase}/search/history`, {
      params: {
        user_id: getUserId(),
        session_id: sessionId,
      },
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token') || ''}`,
      },
    })

    suggestions.value.history = []
  } catch (error) {
    console.error('Lỗi clearHistory:', error.response?.data || error.message)
  }
}

function selectSuggestion(keyword) {
  input.value = keyword
  handleSearch()
}

function selectProduct(productId) {
  router.push({ path: `/product/${productId}` })
  showSuggestions.value = false
}

function handleClickOutside(event) {
  if (wrapper.value && !wrapper.value.contains(event.target)) {
    showSuggestions.value = false
  }
}

onMounted(async () => {
  generateSessionId()
  document.addEventListener('click', handleClickOutside)

  if (localStorage.getItem('access_token') && !authStore.currentUser) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.error('Lỗi fetchUser:', error.response?.data || error.message)
      localStorage.removeItem('access_token')
    }
  }

  if (getUserId()) {
    searchStore.setUserId(getUserId())
    try {
      await axios.post(`${apiBase}/search/sync-history`, null, {
        params: { session_id: localStorage.getItem('session_id') },
        headers: {
          Authorization: `Bearer ${localStorage.getItem('access_token') || ''}`,
        },
      })
    } catch (error) {
      console.error('Lỗi sync-history:', error.response?.data || error.message)
    }
  }

  // ❌ Không fetchSuggestions ở đây để tránh tự mở dropdown
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(
  () => authStore.isLoggedIn,
  async () => {
    await fetchSuggestions()
  }
)
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
