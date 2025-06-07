export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  css: ['@/assets/css/tailwind.css',
     '@fortawesome/fontawesome-free/css/all.min.css'
  ],
   modules: ['@pinia/nuxt'],
  plugins: ['~/plugins/fontawesome'],
  devtools: { enabled: true },
   postcss: {
    plugins: {
       tailwindcss: {},
      autoprefixer: {},
    },
  },
   runtimeConfig: {
    public: {
      apiBaseUrl: process.env.API_BASE_URL || 'http://localhost:8000/api'
    }
  }
});

 