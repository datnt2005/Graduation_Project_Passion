<template>
  <teleport to="body">
    <div
      v-if="open && banner?.type === 'popup'"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50"
      @click.self="onBackdrop"
    >
      <div
        class="relative transition duration-300 ease-out transform"
        :class="showAnim ? 'scale-100 opacity-100' : 'scale-95 opacity-0'"
        role="dialog"
        aria-modal="true"
        :aria-label="banner?.title || 'Khuyến mãi'"
      >
        <div style="display: flex; flex-direction: column; align-items: flex-end;">
          <!-- Close Icon -->
          <button
            @click="close"
            class="bg-white rounded-full shadow-md p-1 cursor-pointer transition-all duration-200 hover:bg-gray-100"
            style="width: 32px; height: 32px; opacity: 1;"
            aria-label="Đóng"
          >
            <i class="fas fa-times text-gray-600 text-lg "></i>
          </button>

          <!-- Clickable Image -->
          <a
            v-if="banner?.link"
            :href="banner.link"
            target="_blank"
            rel="noopener"
            class="webpimg-container cursor-pointer"
            style="margin-top: 12px; height: 100%; opacity: 1;"
          >
            <img
              :src="banner.image_url"
              :alt="banner.title || 'Banner'"
              class="transition-all duration-300"
              style="width: 382px; height: auto;"
              @load="loaded = true"
            />
          </a>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  banner: { type: Object, default: null },          // {image_url, title, link, type:'popup'}
  autoOnHome: { type: Boolean, default: true },     // tự mở ở trang chủ
  cooldownMinutes: { type: Number, default: 5 },    // lặp lại sau N phút
  storageKey: { type: String, default: 'popup_home_banner_v1' }, // key localStorage
  closeOnBackdrop: { type: Boolean, default: true },
  escToClose: { type: Boolean, default: true },
  autoCloseMs: { type: Number, default: 1000000 },     // tự đóng sau X ms (0 = tắt)
})

const emit = defineEmits(['opened', 'closed'])

const route = useRoute()
const open = ref(false)
const showAnim = ref(false)
const loaded = ref(false)
let autoCloseTimer = null

function lockScroll(lock) { document.documentElement.style.overflow = lock ? 'hidden' : '' }

function nextAllowedAt() { return Number(localStorage.getItem(props.storageKey) || 0) }
function allowedNow()    { return Date.now() > nextAllowedAt() }
function setCooldown()   { localStorage.setItem(props.storageKey, String(Date.now() + props.cooldownMinutes * 60 * 1000)) }

function startAutoClose() {
  if (!props.autoCloseMs) return
  clearTimeout(autoCloseTimer)
  autoCloseTimer = setTimeout(() => close(), props.autoCloseMs)
}

function openNow() {
  if (!props.banner) return
  if (!allowedNow()) return
  open.value = true
  requestAnimationFrame(() => { showAnim.value = true })
  lockScroll(true)
  emit('opened')
  startAutoClose()
}

function close() {
  showAnim.value = false
  setTimeout(() => {
    open.value = false
    lockScroll(false)
    setCooldown()
    emit('closed')
  }, 160)
}

function onBackdrop() { if (props.closeOnBackdrop) close() }
function onKey(e) { if (props.escToClose && e.key === 'Escape') close() }
function isHome(path) { return path === '/' || path === '' || path === '/index' }

onMounted(() => {
  window.addEventListener('keydown', onKey)
  if (props.autoOnHome && isHome(route.path)) openNow()
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKey)
  lockScroll(false)
  clearTimeout(autoCloseTimer)
})

watch(() => route.path, (p) => {
  if (props.autoOnHome && isHome(p)) openNow()
})

watch(() => props.banner, (b) => {
  if (b && props.autoOnHome && isHome(route.path)) openNow()
})
</script>

<style scoped>
/* Tiki-inspired dynamic styling */
.webpimg-container img {
  background: transparent ; /* Warm gradient like Tiki banners */
  padding: 6px;
  border-radius: 12px;
}

</style>