<template>
  <section v-if="address"
    class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-bold text-gray-800">Giao tới</h3>
      <NuxtLink href="/address" class="text-blue-600 text-sm font-medium">Thay đổi</NuxtLink>
    </div>
    <div class="space-y-1 text-sm text-gray-700">
      <p class="font-semibold">{{ address.name }} - {{ address.phone }}</p>
      <p>
        {{ address.detail }},
        {{ getWardName(address.ward_code, address.district_id) }},
        {{ getDistrictName(address.district_id) }},
        {{ getProvinceName(address.province_id) }}
      </p>
    </div>
  </section>

  <section v-else
    class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-gray-600 italic">
    Bạn chưa có địa chỉ giao hàng.
    <NuxtLink to="/address" class="text-blue-500 underline">Thêm địa chỉ</NuxtLink>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  address: Object,
  provinces: Array,
  districts: Array,
  wards: Array
})

const getProvinceName = (province_id) => {
  const p = props.provinces.find(p => p.ProvinceID == province_id)
  return p ? p.ProvinceName : ''
}

const getDistrictName = (district_id) => {
  const d = props.districts.find(d => d.DistrictID == district_id)
  return d ? d.DistrictName : ''
}

const getWardName = (ward_code, district_id) => {
  const w = props.wards.find(w =>
    w.WardCode == ward_code && w.DistrictID == district_id
  )
  return w ? w.WardName : ''
}
</script>
