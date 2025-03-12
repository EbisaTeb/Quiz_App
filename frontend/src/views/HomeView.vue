<script setup>
import { useAuthStore } from '@/stores/auth';
import Button from 'primevue/button';
import Card from 'primevue/card';
import {useRouter } from 'vue-router';


const router = useRouter();
const authStore = useAuthStore();
const userRoles = authStore.user?.roles.map(role => role.name) || [];
const isAdmin = userRoles.includes('admin');
const isTeacher = userRoles.includes('teacher');
const isStudent = userRoles.includes('student');
</script>

<template>
  <div class="container mx-auto p-4">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold mb-3">Welcome to the Quiz App!</h1>
      <!-- <div class="flex justify-center gap-2">
        <Tag v-for="(role, index) in userRoles" :key="index" 
             :value="role" severity="info" class="capitalize" />
      </div> -->
    </div>

    <div class="grid grid-nogutter justify-content-center gap-5">
      <!-- Admin Dashboard -->
      <Card v-if="isAdmin" class="w-full md:w-30rem transition-all hover:shadow-5">
        <template #title>
          <div class="flex align-items-center gap-3">
            <i class="pi pi-shield text-2xl text-blue-500"></i>
            <span class="text-2xl font-medium">Administrator</span>
          </div>
        </template>
        <template #content>
          <ul class="list-none p-0 m-0 mb-4">
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Manage users and permissions
            </li>
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Assign subjects and classes
            </li>
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Monitor system analytics
            </li>
          </ul>
            <Button label="Access Dashboard" icon="pi pi-arrow-right" 
                class="w-full" severity="secondary" @click="router.push({ name: 'app.dashboard' })" />
        </template>
      </Card>

      <!-- Teacher Dashboard -->
      <Card v-if="isTeacher" class="w-full md:w-30rem transition-all hover:shadow-5">
        <template #title>
          <div class="flex align-items-center gap-3">
            <i class="pi pi-book text-2xl text-orange-500"></i>
            <span class="text-2xl font-medium">Educator Hub</span>
          </div>
        </template>
        <template #content>
          <ul class="list-none p-0 m-0 mb-4">
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Create interactive quizzes
            </li>
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Track student progress
            </li>
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Generate performance reports
            </li>
          </ul>
            <Button label="Manage Quizzes" icon="pi pi-arrow-right" 
                class="w-full" severity="secondary" @click="router.push({ name: 'app.quiz' })" /> 
          </template>
      </Card>

      <!-- Student Dashboard -->
      <Card v-if="isStudent" class="w-full md:w-30rem transition-all hover:shadow-5">
        <template #title>
          <div class="flex align-items-center gap-3">
            <i class="pi pi-users text-2xl text-purple-500"></i>
            <span class="text-2xl font-medium">Learning Portal</span>
          </div>
        </template>
        <template #content>
          <ul class="list-none p-0 m-0 mb-4">
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Attempt assigned quizzes
            </li>
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Submit timed assessments
            </li>
            <li class="flex align-items-center py-3">
              <i class="pi pi-check-circle text-green-500 mr-3"></i>
              Review results & feedback
            </li>
          </ul>
            <Button label="View Active Quizzes" icon="pi pi-arrow-right" 
              class="w-full" severity="secondary" @click="router.push({ name: 'app.activequizzes' })" />
        </template>
      </Card>
    </div>
  </div>
</template>

<style scoped>
.container {
  max-width: 1400px;
}

.pi {
  font-size: 1.2rem;
}

.transition-all {
  transition: all 0.3s ease;
}
</style>