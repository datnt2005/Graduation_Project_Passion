<template>
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-logo">
          <img src="/" alt="Logo" width="60" height="60" />
        </div>
        <h2>Đăng kí</h2>
        <form @submit.prevent="onRegister">
          <input v-model="email" type="email" placeholder="Nhập email" required />
          <input v-model="username" type="text" placeholder="Tên đăng nhập của bạn" required />
          <input v-model="password" type="password" placeholder="Nhập mật khẩu" required />
          <input v-model="confirmPassword" type="password" placeholder="Nhập lại mật khẩu" required />
          <button class="btn-primary" type="submit">Đăng kí</button>
        </form>
        <div class="auth-link" style="justify-content: flex-start;">
          <NuxtLink to="/auth/login">Bạn đã có tài khoản? Đăng nhập</NuxtLink>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  const email = ref('')
  const username = ref('')
  const password = ref('')
  const confirmPassword = ref('')
  
  function onRegister() {
    if (password.value !== confirmPassword.value) {
      alert('Mật khẩu không khớp!')
      return
    }
    alert('Chưa kết nối backend. Email: ' + email.value)
  }
  </script>
  
  <style scoped>
  .auth-container {
    min-height: 100vh;
    background: linear-gradient(to bottom, #e0f2ff 0%, #7fd1fa 100%);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .auth-card {
    background: #fff;
    padding: 2rem 2.5rem;
    border-radius: 18px;
    box-shadow: 0 2px 12px #0001;
    width: 340px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .auth-logo {
    margin-bottom: 10px;
  }
  h2 {
    margin: 0 0 1rem 0;
  }
  .auth-social {
    display: flex;
    gap: 10px;
    margin-bottom: 0.5rem;
  }
  .btn-social {
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    background: #f1f1f1;
    transition: background 0.2s;
  }
  .btn-social.google { color: #ea4335; }
  .btn-social.facebook { color: #1877f3; }
  .divider {
    width: 100%;
    text-align: center;
    margin: 1rem 0;
    color: #aaa;
    position: relative;
  }
  .divider span {
    background: #fff;
    padding: 0 10px;
    position: relative;
    z-index: 1;
  }
  .divider:before {
    content: "";
    position: absolute;
    left: 0; right: 0; top: 50%;
    border-top: 1px solid #eee;
    z-index: 0;
  }
  form {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 10px;
  }
  input {
    padding: 10px;
    border-radius: 7px;
    border: 1px solid #ddd;
    font-size: 15px;
  }
  .btn-primary {
    background: #29a6ff;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 10px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
  }
  .auth-link {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-top: 12px;
    font-size: 14px;
  }
  .auth-link a { color: #2997ff; text-decoration: none; }
  .auth-link a:hover { text-decoration: underline; }
  </style>