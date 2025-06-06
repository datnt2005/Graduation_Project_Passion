export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  css: ['@/assets/css/tailwind.css'],
  modules: ['@pinia/nuxt'],
  plugins: ['~/plugins/fontawesome'],
  devtools: { enabled: true },
   postcss: {
    plugins: {
       tailwindcss: {},
      autoprefixer: {},
    },
  },
});

