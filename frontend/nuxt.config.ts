export default defineNuxtConfig({
  ssr: false,
  compatibilityDate: '2025-05-15',

  css: [
    '@/assets/css/tailwind.css',
    '@fortawesome/fontawesome-free/css/all.min.css',
    'vue-slider-component/theme/default.css'
  ],

  modules: [
    '@pinia/nuxt',
    '@nuxt/devtools' // ⬅ nếu bạn muốn khai báo rõ ràng (tự động nếu đã cài)
  ],

  plugins: ['~/plugins/fontawesome'],

  devtools: { enabled: true }, // ✅ bật devtools tại đây

  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.API_BASE_URL,
      mediaBaseUrl: process.env.MEDIA_BASE_URL,
    }
  },
  vue: {
    compilerOptions: {
      isCustomElement: (tag) => tag === 'emoji-picker',
    },
  },
  vite: {
    server: {
      hmr: false, // Tắt HMR
    },
  },
});
