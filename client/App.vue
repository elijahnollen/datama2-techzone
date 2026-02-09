<template>
  <div id="app">
    <Login v-if="!isLoggedIn" @login-success="handleLoginSuccess" />

    <Dashboard v-else @logout="isLoggedIn = false" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Login from './Login.vue';
import Dashboard from './Dashboard.vue';

// State to track if the Admin is authenticated
const isLoggedIn = ref(false);

const handleLoginSuccess = () => {
  isLoggedIn.value = true;
};
</script>

<style>
/* Basic styling to center your app */
#app {
  font-family: Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  margin: 0;
  padding: 0;
}
</style>