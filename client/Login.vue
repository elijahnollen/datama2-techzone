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
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500"
          @keyup.enter="handleLogin"
        />
      </div>

      <button 
        @click="handleLogin" 
        class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition font-bold"
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
import { useRouter } from 'vue-router'; // Import if you have a router set up

const router = useRouter();
const email = ref('');
const message = ref('');
const messageClass = ref('');

const handleLogin = async () => {
  if (!email.value) {
    message.value = "Please enter an email address.";
    messageClass.value = 'text-red-600';
    return;
  }

  try {
    // UPDATED URL: Match your active Codespaces backend
    const backendUrl = 'https://probable-rotary-phone-97r99wvr6vpwh79r4-3000.app.github.dev';
    
    const response = await fetch(`${backendUrl}/api/admin-check`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value })
    });

    const data = await response.json();

    if (data.isAdmin) {
      // Forensic Audit: Server-side validation succeeded
      message.value = `Access Granted! Welcome, ${data.name}.`;
      messageClass.value = 'text-green-600';
      
      // Navigate to your admin dashboard after a short delay
      // router.push('/admin-dashboard'); 
    } else {
      message.value = data.message || "Access Denied: Invalid Admin credentials.";
      messageClass.value = 'text-red-600';
    }
  } catch (error) {
    console.error("Login connection error:", error);
    message.value = "Server is offline. Please check your backend terminal.";
    messageClass.value = 'text-red-600';
  }
};
</script>