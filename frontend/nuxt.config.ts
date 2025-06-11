export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  css: ['@/assets/css/tailwind.css',
     '@fortawesome/fontawesome-free/css/all.min.css'
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
      apiBaseUrl: process.env.API_BASE_URL || 'http://localhost:8000/api',
      mediaBaseUrl: process.env.MEDIA_BASE_URL || 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/',
    }
  }
});

 