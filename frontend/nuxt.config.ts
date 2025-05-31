// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',
  css: ['@/assets/css/tailwind.css'],
  plugins: ['~/plugins/fontawesome'],
  devtools: { enabled: true },
   postcss: {
    plugins: {
       tailwindcss: {},
      autoprefixer: {},
    },
  },
});

