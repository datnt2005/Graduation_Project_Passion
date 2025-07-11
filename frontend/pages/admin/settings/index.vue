<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen flex">
    <!-- Sidebar -->
    <aside
      class="w-64 bg-white border-r border-gray-200 flex-shrink-0 hidden md:block"
    >
      <nav class="py-6 px-4 space-y-2">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Cài đặt hệ thống</h2>
        <ul class="space-y-1">
          <li>
            <router-link
              to="/admin/settings"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path === '/admin/settings'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa-solid fa-house"></i> Trang cài đặt chính
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-paymentMethod"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-paymentMethod')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-credit-card mr-2"></i> Quản lý phương thức thanh
              toán
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-address"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-address')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-map-marker-alt mr-2"></i> Quản lý địa chỉ
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-shipping"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-shipping')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-shipping-fast mr-2"></i> Quản lý vận chuyển
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-other"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-other')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-cogs mr-2"></i> Cài đặt khác
            </router-link>
          </li>
        </ul>
      </nav>
    </aside>
    <div class="max-w-4xl mx-auto p-6">
      <h2 class="text-2xl font-bold mb-4">Cài đặt hệ thống</h2>

      <div class="mb-4 flex gap-4">
        <button
          @click="downloadBackup"
          class="bg-green-600 text-white px-4 py-2 rounded"
        >
          📦 Tải về sao lưu
        </button>
        <input
          type="file"
          @change="uploadRestore"
          accept=".json"
          class="border p-2"
        />
      </div>

      <form @submit.prevent="updateSettings">
        <div
          v-for="(group, groupName) in settings"
          :key="groupName"
          class="mb-8"
        >
          <h3 class="text-lg font-semibold mb-2 text-blue-600 uppercase">
            {{ groupName || "Khác" }}
          </h3>

          <div v-for="setting in group" :key="setting.key" class="mb-4">
            <label class="block font-semibold mb-1">
              {{ setting.description || setting.key }}
            </label>

            <input
              v-if="setting.type === 'text'"
              v-model="setting.value"
              type="text"
              class="border p-2 w-full rounded"
            />

            <textarea
              v-else-if="setting.type === 'textarea'"
              v-model="setting.value"
              class="border p-2 w-full rounded"
            ></textarea>

            <template v-else-if="setting.type === 'file'">
              <input type="file" @change="uploadFile($event, setting)" />
              <div v-if="setting.value">
                <img :src="getFileUrl(setting.value)" class="h-16" />
              </div>
            </template>

            <input
              v-else
              v-model="setting.value"
              type="text"
              class="border p-2 w-full rounded"
            />
          </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
          Lưu cài đặt
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useToast } from "~/composables/useToast";
const { toast } = useToast();
const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;

const settings = ref({});

onMounted(async () => {
  const { data, error } = await useFetch(`${API}/settings`);

  if (error.value) {
    toast("error", "Không thể tải dữ liệu cài đặt.");
    return;
  }

  if (
    data.value &&
    typeof data.value === "object" &&
    !Array.isArray(data.value)
  ) {
    settings.value = data.value;
  } else {
    toast("error", "⚠️ API không trả về object như mong đợi:", data.value);
  }
});

const updateSettings = async () => {
  const flatSettings = Object.values(settings.value)
    .flat()
    .map(({ key, value }) => ({ key, value }));

  try {
    await $fetch(`${API}/settings`, {
      method: "PUT",
      body: flatSettings,
    });
    toast("success", "✅ Đã lưu thành công!");

    // Refresh data after successful save
    const { data, error } = await useFetch(`${API}/settings`);
    if (error.value) {
      toast("error", "❌ Lỗi khi lấy lại dữ liệu:", error.value);
      return;
    }
    if (
      data.value &&
      typeof data.value === "object" &&
      !Array.isArray(data.value)
    ) {
      settings.value = data.value;
    } else {
      toast("error", "⚠️ API không trả về object như mong đợi:", data.value);
    }
  } catch (err) {
    toast("error", "❌ Lưu thất bại:", err);
    toast("error", `❌ Lưu thất bại: ${err.message}`);
  }
};

const handleFileUpload = async (e, setting) => {
  const file = e.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);
  formData.append("key", setting.key);

  try {
    const result = await $fetch(`${API}/settings/upload`, {
      method: "POST",
      body: formData,
    });
    if (result && result.path) {
      setting.value = result.path;
    } else {
      toast("error", "⚠️ Không nhận được đường dẫn từ API upload:", result);
    }
  } catch (err) {
    toast("error", "❌ Upload file thất bại:", err);
  }
};

const getFileUrl = (path) => {
  const base =
    config.public.r2BaseUrl ||
    config.public.mediaBaseUrl ||
    "http://localhost:8000/storage";
  return path ? `${base.replace(/\/$/, "")}/${path}` : "/default-logo.png";
};

const downloadBackup = async () => {
  try {
    const res = await $fetch(`${API}/settings/backup`);
    const blob = new Blob([JSON.stringify(res, null, 2)], {
      type: "application/json",
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "settings-backup.json";
    link.click();
  } catch (err) {
    toast("error", "❌ Lỗi sao lưu:", err);
  }
};

const uploadRestore = async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);

  try {
    await $fetch(`${API}/settings/restore`, {
      method: "POST",
      body: formData,
    });
    toast("success", "✅ Đã phục hồi cài đặt! Trang sẽ tải lại...");
    location.reload();
  } catch (err) {
    toast("error", "❌ Phục hồi thất bại:", err);
  }
};

definePageMeta({
  layout: "default-admin",
});
</script>
