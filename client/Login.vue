<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
    <div class="p-8 bg-white shadow-md rounded-lg w-96">
      <h1 class="text-2xl font-bold mb-6 text-center text-blue-600">TechZone Admin Access</h1>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Email Address</label>
        <input 
          v-model="email" 
          type="email" 
          placeholder="admin@techzone.com" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm"
        />
      </div>

      <button 
        @click="handleLogin" 
        class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition"
      >
        Sign In
      </button>

      <p v-if="message" :class="messageClass" class="mt-4 text-center text-sm font-semibold">
        {{ message }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const email = ref('');
const message = ref('');
const messageClass = ref('');

const handleLogin = async () => {
  try {
    const response = await fetch('https://silver-lamp-v69vvw49gv7gc667q-3000.app.github.dev/api/admin-check', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value })
    });

    const data = await response.json();

    if (data.isAdmin) {
      message.value = `Welcome back, ${data.role}! Access Granted.`;
      messageClass.value = 'text-green-600';
      // Redirect logic for your Vue Router would go here
    } else {
      message.value = "Access Denied: You do not have Admin privileges.";
      messageClass.value = 'text-red-600';
    }
  } catch (error) {
    message.value = "Connection Error: Is the server running?";
    messageClass.value = 'text-red-600';
  }
};
</script>