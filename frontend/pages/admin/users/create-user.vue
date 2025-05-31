<template>
  <div class="p-4 sm:p-6 md:p-8 max-w-4xl mx-auto">
    <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-800">Thêm người dùng</h1>

    <form @submit.prevent="handleSubmit" class="space-y-5 bg-white p-4 sm:p-6 md:p-8 rounded-lg shadow">
      <!-- Grid responsive -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Name -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Họ và tên</label>
          <input v-model="form.name" type="text" required class="input" placeholder="Nhập họ tên người dùng" />
        </div>

        <!-- Email -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Email</label>
          <input v-model="form.email" type="email" required class="input" placeholder="example@email.com" />
        </div>

        <!-- Phone -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Số điện thoại</label>
          <input v-model="form.phone" type="text" class="input" placeholder="SĐT (tùy chọn)" />
        </div>

        <!-- Password -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Mật khẩu</label>
          <input v-model="form.password" type="password" required class="input" placeholder="Tối thiểu 6 ký tự" />
        </div>
      </div>

      <!-- Avatar Upload -->
      <div>
        <label class="block font-medium mb-1 text-gray-700">Ảnh đại diện</label>
        <input type="file" @change="handleFileUpload" accept="image/*" class="input" />
        <div v-if="avatarPreview" class="mt-3">
          <img :src="avatarPreview" alt="Avatar Preview" class="w-24 h-24 rounded-full object-cover border" />
        </div>
      </div>

      <!-- Role and Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <!-- Role -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Vai trò</label>
          <select v-model="form.role" class="input" required>
            <option value="user">User</option>
            <option value="seller">Seller</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <!-- Status -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Trạng thái</label>
          <select v-model="form.status" class="input" required>
            <option value="active">Hoạt động</option>
            <option value="inactive">Không hoạt động</option>
            <option value="banned">Bị khóa</option>
          </select>
        </div>
      </div>

      <!-- Is Verified -->
      <div class="flex items-center space-x-2">
        <input v-model="form.is_verified" type="checkbox" id="is_verified" class="accent-blue-600" />
        <label for="is_verified" class="font-medium text-gray-700">Email đã xác minh</label>
      </div>

      <!-- Submit Button -->
      <div class="pt-3">
        <button
          type="submit"
          class="w-full sm:w-auto bg-blue-600 text-white font-medium px-5 py-2.5 rounded-lg hover:bg-blue-700 transition duration-200"
        >
          Tạo người dùng
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const form = reactive({
  name: '',
  email: '',
  phone: '',
  avatar: '',
  password: '',
  role: 'user',
  status: 'active',
  is_verified: false,
});

const selectedFile = ref(null);
const avatarPreview = ref(null);

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('image/')) {
    selectedFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
    form.avatar = file.name; // Tạm set tên file
  }
};

const handleSubmit = () => {
  console.log('Submitted form:', form);
  alert('Thêm người dùng thành công!');
};


definePageMeta({
  layout: 'default-admin'
});
</script>

<style scoped>
.input {
  @apply w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style>





