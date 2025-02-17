<script setup>
</script>
<template>
    <div class="flex justify-center items-center min-h-screen w-full min-w-screen">
        <div class="flex">
            <div class="text-indigo-600 self-start text-5xl px-8 font-bold border-r pb-8 leading-10">404</div>
            <div class="px-8">
                <h1 class="text-5xl font-bold mb-2"> Page Not Found</h1>
                <p class="text-gray-400 mb-4">Please check the url address bar and try again</p>
            
            <div>
                <router-link :to="{name:'app.dashboard'}" class="  p-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 ">
                Go back to home
                </router-link>
            </div>
        </div>
    </div>
    </div>
</template>