export default defineNuxtConfig({
  ssr: false,
  compatibilityDate: '2025-05-15',

  app: {
    // ✅ Tắt hiệu ứng loading mặc định (Nuxt logo đen)
    pageTransition: false,
    layoutTransition: false,
   
  },

  css: [
    '@/assets/css/tailwind.css',
    '@fortawesome/fontawesome-free/css/all.min.css',
    'vue-slider-component/theme/default.css'
  ],

  modules: [
    '@pinia/nuxt',
    '@nuxt/devtools' 
  ],

  plugins: ['~/plugins/fontawesome'],

  devtools: { enabled: false }, // ✅ bật devtools tại đây

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
