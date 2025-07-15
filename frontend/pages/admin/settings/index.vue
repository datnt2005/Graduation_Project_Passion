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
    <!-- Main Content -->
    <main class="flex-1 p-8">
      <h2 class="text-3xl font-bold mb-6 text-blue-700">
        <i class="fa-solid fa-wrench"></i> Cài đặt hệ thống
      </h2>

      <div class="mb-6 flex flex-wrap gap-4">
        <button
          @click="downloadBackup"
          class="bg-blue-600 hover:bg-blue-700 transition text-white px-5 py-2 rounded shadow"
        >
          <i class="fa-solid fa-file"></i> Tải về sao lưu
        </button>
        <input
          type="file"
          @change="uploadRestore"
          accept=".json"
          class="border p-2 rounded bg-white"
        />
      </div>

      <form @submit.prevent="updateSettings">
        <div
          v-for="(group, groupName) in settings"
          :key="groupName"
          class="mb-10"
        >
          <h3
            class="text-xl font-semibold mb-4 text-blue-500 uppercase border-b pb-1"
          >
            {{ groupName || "Khác" }}
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="setting in group" :key="setting.key">
              <label class="block font-medium text-sm mb-1 text-gray-800">
                {{ setting.description || setting.key }}
              </label>

              <!-- Text input -->
              <input
                v-if="setting.type === 'text'"
                v-model="setting.value"
                type="text"
                class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
              />

              <!-- Textarea -->
              <textarea
                v-else-if="setting.type === 'textarea'"
                v-model="setting.value"
                class="border border-gray-300 p-2 w-full rounded h-32 resize-none focus:outline-none focus:ring-2 focus:ring-blue-400"
              ></textarea>

              <!-- File upload -->
              <template v-else-if="setting.type === 'file'">
                <input
                  type="file"
                  @change="handleFileUpload($event, setting)"
                  class="mb-2"
                />
                <div v-if="setting.value">
                  <img
                    :src="getFileUrl(setting.value)"
                    class="h-16 rounded shadow border"
                  />
                </div>
              </template>

              <!-- Fallback input -->
              <input
                v-else
                v-model="setting.value"
                type="text"
                class="border p-2 w-full rounded"
              />
            </div>
          </div>
        </div>

        <button
          type="submit"
          class="mt-4 bg-blue-600 hover:bg-blue-700 transition text-white px-6 py-3 rounded shadow-md"
        >
          Lưu cài đặt
        </button>
      </form>
    </main>
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
  const fetchSettings = async (retries = 3) => {
    for (let i = 0; i < retries; i++) {
      console.log(`Attempt ${i + 1} to fetch settings...`);
      const { data, error } = await useFetch(`${API}/settings`, {
        query: { t: Date.now() },
        headers: { "Cache-Control": "no-cache" },
      });

      console.log("API Response:", data.value, "Error:", error.value);

      if (error.value) {
        if (i === retries - 1) {
          toast(
            "error",
            "Không thể tải dữ liệu cài đặt: " + error.value.message
          );
          return;
        }
        await new Promise((resolve) => setTimeout(resolve, 1000)); // Đợi 1 giây
        continue;
      }

      if (
        data.value &&
        typeof data.value === "object" &&
        !Array.isArray(data.value)
      ) {
        settings.value = data.value;
        return;
      } else {
        if (i === retries - 1) {
          toast(
            "error",
            "⚠️ API không trả về object như mong đợi:",
            data.value
          );
        }
      }
    }
  };

  await fetchSettings();
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
