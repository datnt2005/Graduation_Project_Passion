<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="max-w-7xl mx-auto md:pt-6 md:pb-6 flex flex-col md:flex-row gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-4 sm:p-6">
        <div class="min-h-full">
          <h1 class="text-2xl sm:text-3xl text-center font-extrabold text-gray-900 mb-4">
            Sổ địa chỉ giao hàng
          </h1>

          <!-- Nút thêm địa chỉ -->
          <div class="bg-white rounded-lg shadow-sm border border-dashed border-gray-400 p-3 sm:p-4 mb-6">
            <NuxtLink
              to="/users/add_address"
              class="flex items-center justify-center w-full py-2 px-3 text-blue-600 hover:text-blue-700 transition-colors duration-200"
              aria-label="Thêm địa chỉ mới"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                  clip-rule="evenodd"
                />
              </svg>
              Thêm địa chỉ mới
            </NuxtLink>
          </div>

          <!-- Danh sách địa chỉ -->
          <div v-if="loading">
            <div
              class="bg-white rounded-lg shadow-sm border p-4 mb-4 animate-pulse h-24"
              v-for="i in 3"
              :key="i"
            />
          </div>

          <div v-else>
            <div
              v-for="address in addresses"
              :key="address.id"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4"
            >
              <div class="flex justify-between items-start mb-2">
                <h2 class="text-lg font-semibold text-gray-900">
                  {{ address.name }}
                  <span
                    v-if="address.is_default"
                    class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"
                  >
                    Mặc định
                  </span>
                </h2>
                <div class="flex gap-4 text-sm font-medium">
                  <NuxtLink
                    :to="{ path: '/users/add_address', query: { ...address } }"
                    class="text-blue-600 hover:text-blue-800"
                    :aria-label="`Chỉnh sửa địa chỉ ${address.name}`"
                  >
                    Chỉnh sửa
                  </NuxtLink>
                  <button
                    class="text-red-500 hover:text-red-700"
                    @click="deleteAddress(address.id)"
                    :aria-label="`Xoá địa chỉ ${address.name}`"
                  >
                    Xoá
                  </button>
                </div>
              </div>
              <p class="text-gray-700 text-sm mb-1">
                Địa chỉ: {{ resolved[address.id] || 'Đang tải...' }}
              </p>
              <p class="text-gray-700 text-sm">Số điện thoại: {{ address.phone }}</p>
            </div>

            <div v-if="addresses.length === 0" class="text-center text-gray-600 mt-8">
              Bạn chưa có địa chỉ nào. Hãy thêm một địa chỉ mới!
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRuntimeConfig, useHead } from '#app';
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue';
import { useAuthHeaders } from '~/composables/useAuthHeaders';
import { useToast } from '~/composables/useToast';
import Swal from 'sweetalert2';
import axios from 'axios';

useHead({
  title: 'Sổ địa chỉ | Tài khoản của bạn',
  meta: [
    { name: 'description', content: 'Trang quản lý địa chỉ giao hàng của bạn trên hệ thống.' },
    { name: 'robots', content: 'index, follow' },
    { property: 'og:title', content: 'Sổ địa chỉ giao hàng' },
    { property: 'og:description', content: 'Xem và chỉnh sửa địa chỉ giao hàng của bạn.' },
  ],
});

const { showSuccess, showError } = useToast();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const addresses = ref([]);
const loading = ref(true);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const resolved = reactive({});

const loadProvinces = async () => {
  const key = 'ghn_provinces';
  localStorage.removeItem(key); // Clear cache to ensure fresh data
  console.log('Cleared ghn_provinces cache');

  try {
    console.log('Calling API /api/ghn/provinces');
    const res = await axios.get(`${apiBase}/ghn/provinces`);
    console.log('Response from /api/ghn/provinces:', res.data);

    provinces.value = Array.isArray(res.data.data) ? res.data.data : [];
    if (provinces.value.length === 0) {
      console.warn('API /api/ghn/provinces returned empty array:', res.data);
      provinces.value = [
        { ProvinceID: 202, ProvinceName: 'TP. Hồ Chí Minh' },
        { ProvinceID: 210, ProvinceName: 'Đắk Lắk' },
        { ProvinceID: 253, ProvinceName: 'Bạc Liêu' },
        { ProvinceID: 266, ProvinceName: 'Sơn La' },
      ];
      console.log('Using default provinces:', provinces.value);
    } else {
      console.log('Provinces from API:', provinces.value);
    }
    localStorage.setItem(key, JSON.stringify({ data: provinces.value, timestamp: Date.now() }));
  } catch (e) {
    console.error('Error calling /api/ghn/provinces:', e);
    provinces.value = [
      { ProvinceID: 202, ProvinceName: 'TP. Hồ Chí Minh' },
      { ProvinceID: 210, ProvinceName: 'Đắk Lắk' },
      { ProvinceID: 253, ProvinceName: 'Bạc Liêu' },
      { ProvinceID: 266, ProvinceName: 'Sơn La' },
    ];
    console.log('Using default provinces due to API error:', provinces.value);
    localStorage.setItem(key, JSON.stringify({ data: provinces.value, timestamp: Date.now() }));
  }
};

const loadDistrictsAppend = async (provinceId) => {
  const key = `ghn_districts_${provinceId}`;
  const cache = localStorage.getItem(key);
  let data = [];

  if (cache) {
    try {
      const parsed = JSON.parse(cache);
      if (parsed.data && Array.isArray(parsed.data)) {
        data = parsed.data;
        console.log(`Loaded districts from cache for province ${provinceId}:`, data);
      } else {
        console.warn(`Data in localStorage at ${key} is not an array:`, parsed);
      }
    } catch (e) {
      console.error(`Error parsing localStorage at ${key}:`, e);
    }
  } else {
    try {
      const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId });
      data = Array.isArray(res.data.data) ? res.data.data : [];
      localStorage.setItem(key, JSON.stringify({ data, timestamp: Date.now() }));
      console.log(`Saved districts to localStorage for province ${provinceId}:`, data);
    } catch (e) {
      console.error(`Error fetching districts for province ${provinceId}:`, e);
      data = [];
    }
  }

  const ids = data.map((d) => d.DistrictID);
  if (!districts.value.some((d) => ids.includes(d.DistrictID))) {
    districts.value.push(...data);
  }
};

const loadWardsAppend = async (districtId) => {
  const key = `ghn_wards_${districtId}`;
  const cache = localStorage.getItem(key);
  let data = [];

  if (cache) {
    try {
      const parsed = JSON.parse(cache);
      if (parsed.data && Array.isArray(parsed.data)) {
        data = parsed.data;
        console.log(`Loaded wards from cache for district ${districtId}:`, data);
      } else {
        console.warn(`Data in localStorage at ${key} is not an array:`, parsed);
      }
    } catch (e) {
      console.error(`Error parsing localStorage at ${key}:`, e);
    }
  } else {
    try {
      const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId });
      data = Array.isArray(res.data.data) ? res.data.data : [];
      localStorage.setItem(key, JSON.stringify({ data, timestamp: Date.now() }));
      console.log(`Saved wards to localStorage for district ${districtId}:`, data);
    } catch (e) {
      console.error(`Error fetching wards for district ${districtId}:`, e);
      data = [];
    }
  }

  const codes = data.map((w) => w.WardCode);
  if (!wards.value.some((w) => codes.includes(w.WardCode))) {
    wards.value.push(...data);
  }
};

const provinceMap = computed(() => {
  if (!Array.isArray(provinces.value)) {
    console.warn('provinces.value is not an array:', provinces.value);
    return new Map();
  }
  const map = new Map(provinces.value.map((p) => [Number(p.ProvinceID), p.ProvinceName]));
  console.log('provinceMap:', Object.fromEntries(map));
  return map;
});

const districtMap = computed(() =>
  new Map(Array.isArray(districts.value) ? districts.value.map((d) => [d.DistrictID, d.DistrictName]) : [])
);
const wardMap = computed(() =>
  new Map(Array.isArray(wards.value) ? wards.value.map((w) => [`${w.WardCode}-${w.DistrictID}`, w.WardName]) : [])
);

const getProvinceName = (id) => {
  if (!id) {
    console.warn('Invalid province_id:', id);
    return 'Không xác định';
  }
  const name = provinceMap.value.get(Number(id));
  if (!name) {
    console.warn(`Province name not found for province_id: ${id}`);
    return 'Không xác định';
  }
  return name;
};

const getDistrictName = (id) => (id ? districtMap.value.get(id) || 'Không xác định' : 'Không xác định');
const getWardName = (code, did) => (code && did ? wardMap.value.get(`${code}-${did}`) || 'Không xác định' : 'Không xác định');

const resolveAddressText = async (address) => {
  try {
    console.log('Processing address:', address);
    await loadDistrictsAppend(address.province_id);
    await loadWardsAppend(address.district_id);

    const ward = getWardName(address.ward_code, address.district_id);
    const district = getDistrictName(address.district_id);
    const province = getProvinceName(address.province_id);
    const addressText = `${address.detail}, ${ward}, ${district}, ${province}`;
    console.log('Resolved address:', addressText);
    return addressText;
  } catch (e) {
    console.error('Error resolving address:', e, 'for address:', address);
    return `${address.detail}, Không xác định`;
  }
};

const loadAddresses = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`${apiBase}/address`, useAuthHeaders());
    addresses.value = Array.isArray(res.data.data) ? res.data.data : [];

    for (const addr of addresses.value) {
      if (!addr.province_id || !addr.district_id || !addr.ward_code) {
        console.warn('Invalid address:', addr);
        resolved[addr.id] = `${addr.detail}, Không xác định`;
        continue;
      }
      resolveAddressText(addr).then((text) => {
        resolved[addr.id] = text;
      }).catch((e) => {
        resolved[addr.id] = `${addr.detail}, Không xác định`;
      });
    }
  } catch (e) {
    showError('Không thể tải địa chỉ');
  } finally {
    loading.value = false;
  }
};

const deleteAddress = async (id) => {
  const confirm = await Swal.fire({
    title: 'Xoá địa chỉ?',
    text: 'Bạn có chắc chắn muốn xoá địa chỉ này?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xoá',
    cancelButtonText: 'Huỷ',
  });

  if (!confirm.isConfirmed) return;

  try {
    await axios.delete(`${apiBase}/address/${id}`, useAuthHeaders());
    showSuccess('Đã xoá thành công');
    await loadAddresses();
  } catch (e) {
    showError('Không thể xoá địa chỉ');
  }
};

onMounted(async () => {
  await loadProvinces();
  await loadAddresses();
});
</script>

<style scoped>
@media (max-width: 640px) {
  button,
  a {
    font-size: 0.75rem;
    padding: 0.5rem 1rem;
  }
}
</style>