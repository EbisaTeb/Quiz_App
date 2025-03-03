<script setup>
import { HomeIcon, UserIcon, ListBulletIcon, ChartBarIcon } from '@heroicons/vue/24/solid';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Extract user roles as an array
const userRoles = authStore.user?.roles.map(role => role.name) || [];
const userId = authStore.user?.id;

// Role-based visibility
const isAdmin = userRoles.includes('admin');
const isTeacher = userRoles.includes('teacher');
const isStudent = userRoles.includes('student');
</script>

<template>
  <div class="w-[200px] shadow bg-white py-4 px-2 transition-all h-full flex flex-col">
    <div class="flex items-center justify-center py-1 px-2 rounded mb-2 mr-20 flex-shrink-0">
      <span class="text-md text-black font-serif">
        {{ userRoles.join(', ') }}
      </span>
    </div>
    <div class="overflow-y-auto flex-grow scrollbar-hide">
      <!-- Dashboard (Available to All) -->
      <router-link :to="{ name: 'app.dashboard' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
        <span class="mr-2 text-black-300"><HomeIcon class="w-5" /></span>
        <span class="text-xs">Dashboard</span>
      </router-link>

      <!-- Admin-Specific Links -->
      <template v-if="isAdmin">
        <router-link :to="{ name: 'app.usermanage' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><UserIcon class="w-5" /></span>
          <span class="text-xs">Users</span>
        </router-link>
        
        <router-link :to="{ name: 'app.class' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-plus mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Class</span>
        </router-link>

        <router-link :to="{ name: 'app.subject' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-plus mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Subject</span>
        </router-link>
        <router-link :to="{ name: 'app.addstudent' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-plus mr-2 w-5 black-gray-300"></i>
          <span class="text-xs">Student</span>
        </router-link>

        <router-link :to="{ name: 'app.addteacher' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-plus mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Teachers</span>
        </router-link>
        <router-link :to="{ name: 'app.adminquizmanagement' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-plus mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Quiz</span>
        </router-link>
      </template>

      <!-- Teacher-Specific Links -->
      <template v-if="isTeacher">
        <router-link :to="{ name: 'app.quiz' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-file mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Manage Quizzes</span>
        </router-link>
        
        <router-link :to="{ name: 'app.question' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-file mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Manage Questions</span>
        </router-link>
        
        <router-link :to="{ name: 'app.autograde' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-file mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Auto Grade</span>
        </router-link>
      </template>

      <!-- Student-Specific Links -->
      <template v-if="isStudent">
        <router-link :to="{ name: 'app.quizsubmission', params: { id: userId } }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-file mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Take Quizzes</span>
        </router-link>
        
        <router-link :to="{ name: 'app.submissions' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-file mr-2 w-5 text-black-300"></i>
          <span class="text-xs">My Submissions</span>
        </router-link>
      </template>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
</style>
