<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
    <div class="p-8 bg-white shadow-md rounded-lg w-96">
      <h1 class="text-2xl font-bold mb-6 text-center text-blue-600">TechZone Admin Access</h1>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Email Address</label>
        <input 
          v-model="email" 
          type="email" 
          :disabled="isLoading"
          placeholder="admin@techzone.com" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100"
          @keyup.enter="handleLogin"
        />
      </div>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input 
          v-model="password" 
          type="password" 
          :disabled="isLoading"
          placeholder="••••••••" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100"
          @keyup.enter="handleLogin"
        />
      </div>

      <button 
        @click="handleLogin" 
        :disabled="isLoading"
        class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition font-bold flex items-center justify-center disabled:bg-blue-300"
      >
        <span v-if="isLoading">Verifying Credentials...</span>
        <span v-else>Sign In</span>
      </button>

      <p v-if="message" :class="messageClass" class="mt-4 text-center text-sm font-semibold">
        {{ message }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue';

const emit = defineEmits(['login-success']);

const email = ref('');
const password = ref('');
const isLoading = ref(false); // New state to track the loading status
const message = ref('');
const messageClass = ref('');

const handleLogin = async () => {
  // 1. Basic Validation
  if (!email.value || !password.value) {
    message.value = "Email and Password are required.";
    messageClass.value = 'text-red-600';
    return;
  }

  // 2. Start Loading
  isLoading.value = true;
  message.value = "Authenticating with secure server...";
  messageClass.value = 'text-blue-600';

  try {
    const backendUrl = 'https://probable-rotary-phone-97r99wvr6vpwh79r4-3000.app.github.dev';
    
    const response = await fetch(`${backendUrl}/api/admin-check`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        email: email.value, 
        password: password.value 
      })
    });

    const data = await response.json();

    if (data.isAdmin) {
      message.value = `Access Granted! Welcome, ${data.name}.`;
      messageClass.value = 'text-green-600';
      
      // Delay slightly so the user can see the success message
      setTimeout(() => {
        emit('login-success');
      }, 1000);
    } else {
      message.value = data.message || "Access Denied: Invalid credentials.";
      messageClass.value = 'text-red-600';
    }
  } catch (error) {
    console.error("Login connection error:", error);
    message.value = "Server error. Check your backend terminal.";
    messageClass.value = 'text-red-600';
  } finally {
    // 3. Stop Loading regardless of success or failure
    isLoading.value = false;
  }
};
</script>