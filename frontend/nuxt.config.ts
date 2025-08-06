export default defineNuxtConfig({
  ssr: false,
  compatibilityDate: '2025-05-15',

  app: {
    pageTransition: false,
    layoutTransition: false,
    head: {
      title: 'Passion – Đam mê kết nối, giá tốt không thôi. Mua bán tiện lợi, mỗi ngày thêm vui!',
      titleTemplate: '%s - Passion',
      meta: [
        { name: 'description', content: 'Passion là sàn thương mại điện tử kết nối đam mê mua sắm – giá tốt, giao nhanh, sản phẩm đa dạng, dịch vụ tận tâm.' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ],
      link: [
        {
          rel: 'icon',
          type: 'image/png',
          sizes: '512x512',
          href: '/logo.png'
        }
      ]
    }
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

  plugins: ['~/plugins/fontawesome', '~/plugins/router-loading'],

  devtools: { enabled: false },

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
      hmr: false,
    },
  },
  
});
