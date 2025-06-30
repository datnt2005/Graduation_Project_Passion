<template>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý API Keys</title>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="container mx-auto p-6">
    <!-- Header -->
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Quản lý API Keys</h1>
      <p class="text-gray-600">Quản lý và xem API keys của hệ thống bán hàng</p>
      <button class="mt-2 text-red-500 hover:underline">Đăng xuất</button>
    </header>

    <!-- Form tạo API key (chỉ admin) -->
    <div class="mb-8 p-4 bg-white rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">Tạo API Key mới</h2>
      <div class="flex space-x-4">
        <input
          placeholder="Nhập tên API Key"
          class="flex-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Tạo API Key
        </button>
      </div>
    </div>

    <!-- Danh sách API Keys -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4">Danh sách API Keys</h2>
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200">
              <th class="p-3 text-left">Tên</th>
              <th class="p-3 text-left">Key</th>
              <th class="p-3 text-left">Trạng thái</th>
              <th class="p-3 text-left">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-t">
              <td class="p-3">Key API Giao hàng</td>
              <td class="p-3 font-mono text-sm">abc123xyz789</td>
              <td class="p-3 text-green-600">Hoạt động</td>
              <td class="p-3">
                <button class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded mr-2">Khóa</button>
                <button class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded">Xóa</button>
              </td>
            </tr>
            <tr class="border-t">
              <td class="p-3">Key API Vận chuyển</td>
              <td class="p-3 font-mono text-sm">def456uvw000</td>
              <td class="p-3 text-red-600">Đã khóa</td>
              <td class="p-3">
                <button class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded mr-2">Mở khóa</button>
                <button class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded">Xóa</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Danh sách API hệ thống -->
    <div>
      <h2 class="text-xl font-semibold mb-4">Danh sách API hệ thống</h2>
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200">
              <th class="p-3 text-left">Phương thức</th>
              <th class="p-3 text-left">URI</th>
              <th class="p-3 text-left">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-t">
              <td class="p-3">GET</td>
              <td class="p-3">/api/products</td>
              <td class="p-3">Lấy danh sách sản phẩm</td>
            </tr>
            <tr class="border-t">
              <td class="p-3">POST</td>
              <td class="p-3">/api/orders</td>
              <td class="p-3">Tạo đơn hàng mới</td>
            </tr>
            <tr class="border-t">
              <td class="p-3">DELETE</td>
              <td class="p-3">/api/products/:id</td>
              <td class="p-3">Xóa sản phẩm theo ID</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
</template>