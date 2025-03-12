<!-- src/views/Login.vue -->
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import GuestLayout from '@/components/GuestLayout.vue';
import { validateEmail, validatePassword } from '@/utils/validators';

const router = useRouter();
const authStore = useAuthStore();

const formData = ref({
  email: '',
  password: '',
  remember: false
});

const errors = ref({
  email: [],
  password: [],
  general: ''
});

const loading = ref(false);

const validateForm = () => {
  let isValid = true;
  errors.value = { email: [], password: [], general: '' };

  // Email validation
  const emailError = validateEmail(formData.value.email);
  if (emailError) {
    errors.value.email.push(emailError);
    isValid = false;
  }

  // Password validation
  const passwordError = validatePassword(formData.value.password);
  if (passwordError) {
    errors.value.password.push(passwordError);
    isValid = false;
  }

  return isValid;
};

const handleLogin = async () => {
  if (!validateForm()) return;

  loading.value = true;
  errors.value.general = '';

  try {
    await authStore.login({
      email: formData.value.email,
      password: formData.value.password,
      remember: formData.value.remember
    });

    if (authStore.isAuthenticated) {
      router.push({ name: 'app.home' });
    }
  } catch (error) {
    console.error('Login error:', error);
    errors.value.general = error.message || 'Login failed. Please try again.';
    
    // Handle backend validation errors
    if (error.response?.data?.errors) {
      for (const [field, messages] of Object.entries(error.response.data.errors)) {
        errors.value[field] = messages;
      }
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <GuestLayout title="Sign in to your account">
    <form @submit.prevent="handleLogin" class="space-y-6">
      <!-- Error Message -->
      <div 
        v-if="errors.general"
        class="p-3 bg-red-100 text-red-700 rounded-md"
      >
        {{ errors.general }}
      </div>

      <!-- Email Input -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
          Email address
        </label>
        <div class="mt-1">
          <input
            id="email"
            v-model="formData.email"
            type="email"
            autocomplete="email"
            required
            :class="[
              'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none sm:text-sm',
              errors.email.length ? 'border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'
            ]"
          >
        </div>
        <div 
          v-for="(error, index) in errors.email"
          :key="`email-error-${index}`"
          class="text-red-500 text-sm mt-1"
        >
          {{ error }}
        </div>
      </div>

      <!-- Password Input -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
          Password
        </label>
        <div class="mt-1">
          <input
            id="password"
            v-model="formData.password"
            type="password"
            autocomplete="current-password"
            required
            :class="[
              'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none sm:text-sm',
              errors.password.length ? 'border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'
            ]"
          >
        </div>
        <div 
          v-for="(error, index) in errors.password"
          :key="`password-error-${index}`"
          class="text-red-500 text-sm mt-1"
        >
          {{ error }}
        </div>
      </div>

      <!-- Remember Me & Forgot Password -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input
            id="remember-me"
            v-model="formData.remember"
            type="checkbox"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          >
          <label for="remember-me" class="ml-2 block text-sm text-gray-900">
            Remember me
          </label>
        </div>

        <div class="text-sm">
          <router-link
            :to="{ name: 'requestPassword' }"
            class="font-medium text-indigo-600 hover:text-indigo-500"
          >
            Forgot your password?
          </router-link>
        </div>
      </div>

      <!-- Submit Button -->
      <div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!loading">Sign in</span>
          <span v-else>
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
        </button>
      </div>

      <!-- Signup Link -->
      <div class="text-center text-sm">
        <RouterLink
          :to="{ name: 'signup' }"
          href="#"
          class="font-medium text-indigo-600 hover:text-indigo-500"
        >
          Don't have an account? Sign up
        </RouterLink>
      </div>
    </form>
  </GuestLayout>
</template>