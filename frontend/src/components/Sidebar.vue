<script setup>
import { Squares2X2Icon, UserIcon, AcademicCapIcon, BookOpenIcon, ClipboardDocumentListIcon, UserGroupIcon, ClipboardIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';
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
      <span class="text-md text-black font-serif">
        {{ userRoles.join(', ') }}
      </span>
    </div>
    <div class="overflow-y-auto flex-grow scrollbar-hide">
      <!-- Dashboard (Available to All) -->
      <router-link :to="{ name: 'app.dashboard' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
        <span class="mr-2 text-black-300"><Squares2X2Icon  class="w-5" /></span>
        <span class="text-xs">Dashboard</span>
      </router-link>

      <!-- Admin-Specific Links -->
      <template v-if="isAdmin">
        <router-link :to="{ name: 'app.usermanage' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><UserGroupIcon  class="w-5" /></span>
          <span class="text-xs">Users</span>
        </router-link>
        
        <router-link :to="{ name: 'app.class' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><AcademicCapIcon class="w-5" /></span>
          <span class="text-xs">Class</span>
        </router-link>

        <router-link :to="{ name: 'app.subject' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><BookOpenIcon class="w-5" /></span>
          <span class="text-xs">Subject</span>
        </router-link>
        <router-link :to="{ name: 'app.addstudent' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><UserIcon class="w-5" /></span>
          <span class="text-xs">Student</span>
        </router-link>

        <router-link :to="{ name: 'app.addteacher' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><UserIcon class="w-5" /></span>
          <span class="text-xs">Teachers</span>
        </router-link>
        <router-link :to="{ name: 'app.adminquizmanagement' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentListIcon class="w-5" /></span>
          <span class="text-xs">Quiz</span>
        </router-link>
        <router-link :to="{ name: 'app.admin_see_studentscore' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentListIcon class="w-5" /></span>
          <span class="text-xs">See Student Result</span>
        </router-link>
     
  
      </template>

      <!-- Teacher-Specific Links -->
      <template v-if="isTeacher">
        <router-link :to="{ name: 'app.quiz' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentListIcon class="w-5" /></span>
          <span class="text-xs">Manage Quizzes</span>
        </router-link>
        
        <router-link :to="{ name: 'app.question' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xs">Manage Questions</span>
        </router-link>
        
        <router-link :to="{ name: 'app.shortanswerscoring' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xs">Short Answer Scoring</span>
        </router-link>
        <router-link :to="{ name: 'app.teacher_see_studentscore' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentListIcon class="w-5" /></span>
          <span class="text-xs">See Student Result</span>
        </router-link>
        
        <!-- <router-link :to="{ name: 'app.autograde' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <i class="pi pi-file mr-2 w-5 text-black-300"></i>
          <span class="text-xs">Auto Grade</span>
        </router-link> -->
      </template>

      <!-- Student-Specific Links -->
      <template v-if="isStudent">
        <router-link :to="{ name: 'app.activequizzes' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardIcon class="w-5" /></span>
          <span class="text-xs">Take Quizzes</span>
        </router-link>
        <router-link :to="{ name: 'app.submissionslist' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xs">Result</span>
        </router-link>
        <!-- <router-link :to="{ name: 'app.submissions' }" class="flex items-center py-1 px-2 rounded transition-colors hover:bg-black/30 mb-2">
          <span class="mr-2 text-black-300"><ClipboardDocumentCheckIcon class="w-5" /></span>
          <span class="text-xs">Submissions</span>
        </router-link> -->
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
