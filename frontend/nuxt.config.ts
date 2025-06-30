export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  css: ['@/assets/css/tailwind.css',
     '@fortawesome/fontawesome-free/css/all.min.css',
      'vue-slider-component/theme/default.css'
  ],
   modules: ['@pinia/nuxt'],
  plugins: ['~/plugins/fontawesome'],
  devtools: { enabled: false },
   postcss: {
    plugins: {
       tailwindcss: {},
      autoprefixer: {},
    },
  },
   runtimeConfig: {
    public: {
      apiBaseUrl: process.env.API_BASE_URL ,
      mediaBaseUrl: process.env.MEDIA_BASE_URL,
    }
  }
});

 