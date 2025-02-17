<script setup>
import GuestLayout from '../components/GuestLayout.vue';
</script>
<template>
    <GuestLayout title="Request new password">
    
        <form class="space-y-4" action="#" method="POST">
          <h1 class="mb-6 text-center text-3xl font-extrabold"> </h1>
          <div>
           
            <div class="mt-2">
              <input type="email" name="email" id="email" autocomplete="email" required="" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="Email address" />
            </div>
          </div>
          <div class="flex items-center justify-between">
              <div class="text-sm">
                <router-link :to="{name:'login'}" class="font-semibold text-indigo-600 hover:text-indigo-500">Remember your password</router-link>
              </div>
            </div>
        
          <div>
            <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Submit</button>
          </div>
        </form>
    </GuestLayout>
  </template>
  