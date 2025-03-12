<script setup>
import { Squares2X2Icon, UserIcon, AcademicCapIcon, BookOpenIcon, ClipboardDocumentListIcon, UserGroupIcon, ClipboardIcon, ClipboardDocumentCheckIcon, HomeIcon, CubeIcon, UserPlusIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Extract user roles as an array
const userRoles = authStore.user?.roles.map(role => role.name) || [];
// const userId = authStore.user?.id;

// Role-based visibility
const isAdmin = userRoles.includes('admin');
const isTeacher = userRoles.includes('teacher');
const isStudent = userRoles.includes('student');
</script>

<template>
  <div class="w-[200px] shadow bg-white py-4 px-2 transition-all h-full flex flex-col">
    <div class="flex items-center justify-center py-1 px-2 rounded mb-2 mr-20 flex-shrink-0">
      <span class="text-xl text-black font-serif">
        {{ userRoles.join(', ') }}
      </span>
    </div>
    <div class="overflow-y-auto flex-grow scrollbar-hide">
      <!-- Admin-Specific Links -->
      <template v-if="isAdmin">
        <!-- Dashboard (Available to Admin) -->
        <router-link :to="{ name: 'app.dashboard' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
        <span class="mr-2 text-black-300"><Squares2X2Icon  class="w-5" /></span>
        <span class="text-xl">Dashboard</span>
      </router-link>
        <router-link :to="{ name: 'app.usermanage' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><UserGroupIcon  class="w-5" /></span>
          <span class="text-xl">Users</span>
        </router-link>
        
        <router-link :to="{ name: 'app.class' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><AcademicCapIcon class="w-5" /></span>
          <span class="text-xl">Class</span>
        </router-link>

        <router-link :to="{ name: 'app.subject' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><BookOpenIcon class="w-5" /></span>
          <span class="text-xl">Subject</span>
        </router-link>
        <router-link :to="{ name: 'app.addstudent' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><UserPlusIcon  class="w-5" /></span>
          <span class="text-xl">Student</span>
        </router-link>

        <router-link :to="{ name: 'app.addteacher' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
            <span class="mr-2 text-black-300"><UserPlusIcon  class="w-5" /></span>
          <span class="text-xl">Teacher</span>
        </router-link>
        <router-link :to="{ name: 'app.adminquizmanagement' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentListIcon class="w-5" /></span>
          <span class="text-xl">Quiz</span>
        </router-link>
        <router-link :to="{ name: 'app.admin_see_studentscore' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xl">Student Result</span>
        </router-link>
     
  
      </template>

      <!-- Teacher-Specific Links -->
      <template v-if="isTeacher">
        <router-link :to="{ name: 'app.home' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><HomeIcon class="w-5" /></span>
          <span class="text-xl">Home</span>
        </router-link>
        <router-link :to="{ name: 'app.quiz' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentListIcon class="w-5" /></span>
          <span class="text-xl">Quizzes</span>
        </router-link>
        
        <router-link :to="{ name: 'app.question' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardIcon class="w-5" /></span>
          <span class="text-xl">Questions</span>
        </router-link>
        
        <router-link :to="{ name: 'app.shortanswerscoring' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xl">Short Answer</span>
        </router-link>
        <router-link :to="{ name: 'app.teacher_see_studentscore' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xl">Student Result</span>
        </router-link>

      </template>

      <!-- Student-Specific Links -->
      <template v-if="isStudent">
        <router-link :to="{ name: 'app.home' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><HomeIcon class="w-5" /></span>
          <span class="text-xl">Home</span>
        </router-link>
        <router-link :to="{ name: 'app.activequizzes' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardIcon class="w-5" /></span>
          <span class="text-xl">Take Quizzes</span>
        </router-link>
        <router-link :to="{ name: 'app.submissionslist' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xl">Result</span>
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
