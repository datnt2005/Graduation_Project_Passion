export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',

  // CSS global
  css: ['@/assets/css/tailwind.css'],

  // Plugins
  plugins: ['~/plugins/fontawesome'],

  // Devtools bật trong chế độ phát triển
  devtools: { enabled: true },

  // Cấu hình PostCSS
  postcss: {
    plugins: {
      tailwindcss: {},   // OK
      autoprefixer: {},  // OK
    },
  },
  runtimeConfig: {
  public: {
    apiBaseUrl: process.env.API_BASE_URL || 'http://localhost:8000/api',
  }
}

});
 
