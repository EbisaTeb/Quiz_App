<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/axios';
import GuestLayout from '../components/GuestLayout.vue';

const router = useRouter();

// Define reactive form data
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const errors = ref({});

const signup = async () => {
  errors.value = {}; // Clear previous errors

  try {
    await axiosClient.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });

    // Redirect to login page after successful registration
    router.push({
      name: 'login',
      query: { registered: 'true' },
    });
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors; // Store validation errors
    }
  }
};
</script>

<template>
  <GuestLayout title="Sign Up to your account">
    <form class="space-y-3" @submit.prevent="signup" method="POST">
      <div>
        <div class="mt-2">
          <input v-model="name" type="text" name="name" id="name" required placeholder="Full Name"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600" />
          <p v-if="errors.name" class="error">{{ errors.name[0] }}</p>
        </div>
      </div>

      <div>
        <div class="mt-2">
          <input v-model="email" type="email" name="email" id="email" required placeholder="Email address"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600" />
          <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>
        </div>
      </div>

      <div>
        <div class="mt-2">
          <input v-model="password" type="password" name="password" id="password" required placeholder="Password"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600" />
          <p v-if="errors.password" class="error">{{ errors.password[0] }}</p>
        </div>
      </div>

      <div>
        <div class="mt-2">
          <input v-model="password_confirmation" type="password" name="password_confirmation" id="password_confirmation"
            required placeholder="Confirm Password"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600" />
        </div>
      </div>

      <div>
        <button type="submit"
          class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
          Sign Up
        </button>
      </div>

      <div class="flex flex-row mx-auto items-center justify-center gap-2">
        <p>Already have an account?</p>
        <div class="flex text-sm">
          <RouterLink :to="{ name: 'login' }" class="font-semibold text-indigo-600 hover:text-indigo-500">Login</RouterLink>
        </div>
      </div>
    </form>
  </GuestLayout>
</template>
