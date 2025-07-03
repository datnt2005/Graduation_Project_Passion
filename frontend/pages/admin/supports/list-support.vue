<template>
  <div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Danh sách hỗ trợ</h1>
    <table class="w-full border rounded shadow text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">ID</th>
          <th class="p-2 border">Tên</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Chủ đề</th>
          <th class="p-2 border">Nội dung</th>
          <th class="p-2 border">Phản hồi</th>
          <th class="p-2 border">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in supports" :key="item.id">
          <td class="p-2 border">{{ item.id }}</td>
          <td class="p-2 border">{{ item.name }}</td>
          <td class="p-2 border">{{ item.email }}</td>
          <td class="p-2 border">{{ item.subject }}</td>
          <td class="p-2 border whitespace-pre-line max-w-xs">{{ item.content }}</td>
          <td class="p-2 border">
            <div v-if="editingId === item.id">
              <textarea v-model="replyContent" rows="2" class="w-full border rounded p-1 text-xs"></textarea>
              <button @click="sendReply(item)" class="bg-blue-500 text-white px-2 py-1 rounded text-xs mt-1">Gửi</button>
              <button @click="cancelEdit" class="text-gray-500 px-2 py-1 text-xs">Hủy</button>
            </div>
            <div v-else>
              <div v-if="item.admin_reply" class="text-green-700 whitespace-pre-line">{{ item.admin_reply }}</div>
              <div v-else class="text-gray-400 italic">Chưa phản hồi</div>
            </div>
          </td>
          <td class="p-2 border">
            <button
              v-if="editingId !== item.id"
              @click="startEdit(item)"
              class="text-blue-500 hover:underline text-xs"
            >Phản hồi</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="message" class="mt-4 text-center text-green-600">{{ message }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: 'default-admin'
})
const supports = ref([])
const editingId = ref(null)
const replyContent = ref('')
const message = ref('')

const fetchSupports = async () => {
  const res = await $fetch('http://localhost:8000/api/supports')
  console.log(res)
  supports.value = Array.isArray(res.data) ? res.data : []
}

const startEdit = (item) => {
  editingId.value = item.id
  replyContent.value = item.admin_reply || ''
}

const cancelEdit = () => {
  editingId.value = null
  replyContent.value = ''
}

const sendReply = async (item) => {
  try {
    await $fetch(`http://localhost:8000/api/supports/${item.id}/reply`, {
      method: 'POST',
      body: { admin_reply: replyContent.value }
    })
    message.value = 'Đã gửi phản hồi và email cho người dùng.'
    editingId.value = null
    replyContent.value = ''
    await fetchSupports()
  } catch (e) {
    message.value = 'Gửi phản hồi thất bại.'
  }
}

onMounted(fetchSupports)
</script>