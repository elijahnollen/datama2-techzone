<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Admin Inventory Control</h1>
      <button @click="logout" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4 text-blue-700">Pending Return Requests</h2>
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b bg-gray-100">
            <th class="p-3">Item ID</th>
            <th class="p-3">Reason</th>
            <th class="p-3">Status</th>
            <th class="p-3">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in returns" :key="item.return_itemID" class="border-b hover:bg-gray-50">
            <td class="p-3">#{{ item.return_itemID }}</td>
            <td class="p-3">{{ item.reason }}</td>
            <td class="p-3">
              <span :class="statusColor(item.return_status)">{{ item.return_status }}</span>
            </td>
            <td class="p-3">
              <button 
                v-if="item.return_status === 'Pending'"
                @click="approveRefund(item.return_itemID)"
                class="bg-green-600 text-white px-3 py-1 rounded text-sm"
              >
                Approve Refund
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const returns = ref([]);
const API_URL = 'https://silver-lamp-v69vvw49gv7gc667q-3000.app.github.dev/api';

const fetchReturns = async () => {
  const res = await fetch(`${API_URL}/returns`);
  returns.value = await res.json();
};

const approveRefund = async (id) => {
  await fetch(`${API_URL}/approve-return`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ returnItemID: id, newStatus: 'Refunded' })
  });
  fetchReturns(); // Refresh table
};

const statusColor = (status) => {
  return status === 'Pending' ? 'text-yellow-600' : 'text-green-600 font-bold';
};

onMounted(fetchReturns);
</script>