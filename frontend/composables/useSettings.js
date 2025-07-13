export const useSettings = () => {
  const settings = useState("settings", () => ({}));
  const config = useRuntimeConfig();
  const API = config.public.apiBaseUrl;

  const fetchSettings = async () => {
    try {
      const res = await $fetch(`${API}/settings`);
      if (res && typeof res === "object" && !Array.isArray(res)) {
        settings.value = res;
      } else {
        console.warn("⚠️ Response settings không hợp lệ:", res);
      }
    } catch (err) {
      console.error("❌ Lỗi lấy settings:", err);
    }
  };

  return { settings, fetchSettings };
};
