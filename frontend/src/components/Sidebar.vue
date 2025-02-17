<script setup>
import { HomeIcon,UserIcon, ListBulletIcon,ChartBarIcon } from '@heroicons/vue/24/solid'
import { useAuthStore } from '@/stores/auth' 
const authStore = useAuthStore();
</script>
<template>

  <div class="w-[200px] bg-indigo-700 py-4 px-2 transition-all">
  <div class="flex items-center justify-center py-1 px-2 rounded  mb-2 mr-20">
  
  <span class="text-md text-white font-serif">
    {{ authStore.user?.roles.map(role => role.name).join() }}
    </span>
  </div>
  <router-link :to="{name: 'app.dashboard'}" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <span class="mr-2 text-gray-300">
    <HomeIcon class="w-5"/>
  </span>
  <span class="text-xs">
    Dashboard
    </span>
  </router-link>
  <!-- <router-link :to="{name: 'app.products'}"  class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <span class="mr-2 text-gray-300">
    <ListBulletIcon class="w-5"/>
  </span>
  <span class="text-xs">
   Products
    </span>
  </router-link> -->
  <router-link  :to="{name: 'app.usermanage'}" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <span class="mr-2 text-gray-300">
    <UserIcon class="w-5"/>
  </span>
  <span class="text-xs">
    Users
    </span>
  </router-link>

  <!-- <AddCourseAndsubject/> -->
  <router-link :to="{name: 'app.class'}" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <i class=" pi pi-plus mr-2 w-5 text-gray-300">
  </i>
  <span class="text-xs">
    Class
    </span>
  </router-link>
  <!-- <AddCourseAndsubject/> -->
  <router-link :to="{name: 'app.subject'}" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <i class=" pi pi-plus mr-2 w-5 text-gray-300">
  </i>
  <span class="text-xs">
    Subject
    </span>
  </router-link>
  <router-link :to="{name: 'app.addstudent'}" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <i class=" pi pi-plus mr-2 w-5 text-gray-300">
  </i>
  <span class="text-xs">
    StudentManagement
    </span>
  </router-link>
  <router-link :to="{name: 'app.addteacher'}" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
    <i class=" pi pi-plus mr-2 w-5 text-gray-300">
  </i>
  <span class="text-xs">
    TeacherManagement
    </span>
  </router-link>
  </div>
</template>