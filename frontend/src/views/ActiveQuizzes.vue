<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/axios';
import Button from 'primevue/button';
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';
const router = useRouter();
const activeQuizzes = ref([]);
const isLoading = ref(true);
const error = ref(null);
const startingQuiz = ref(null);

const fetchActiveQuizzes = async () => {
  try {
    const response = await axiosClient.get('/student/active-quizzes');
    activeQuizzes.value = response.data;
  } catch (err) {
    console.error('Error fetching active quizzes:', err);
  } finally {
    isLoading.value = false;
  }
};

const startQuiz = async (quizId) => {
  try {

    router.push({ name: 'app.quizsubmission', params: { id: quizId } });
  } catch (err) {
    error.value = 'You have already completed this quiz.';
  } 
};

onMounted(() => {
  fetchActiveQuizzes();
});
</script>

<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Active Quizzes</h1>
    
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center">
      <ProgressSpinner />
    </div>
    <!-- show error -->
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <!-- Content -->
    <div v-else>
      <!-- No Quizzes -->
      <div v-if="activeQuizzes.length === 0" class="text-center text-gray-500 p-4 border border-gray-300 rounded-lg shadow-sm">
        <i class="pi pi-info-circle text-4xl mb-2"></i>
        <p class="text-lg">There are no quizzes available for you at this time.</p>
      </div>

      <!-- Quizzes List -->
      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="quiz in activeQuizzes" :key="quiz.id">
          <Card>
            <template #title>
              <h2 class="text-lg font-semibold">{{ quiz.title }}</h2>
            </template>
            <template #content>
              <p><strong>Subject:</strong> {{ quiz.subject?.name || 'N/A' }}</p>
              <p><strong>Time Limit:</strong> {{ quiz.time_limit }} minutes</p>
              <p><strong>Start Time:</strong> {{ new Date(quiz.start_time).toLocaleString() }}</p>
              <p><strong>End Time:</strong> {{ new Date(quiz.end_time).toLocaleString() }}</p>
            </template>
            <template #footer>
              <Button 
                label="Start" 
                icon="pi pi-play" 
                :loading="startingQuiz === quiz.id"
                :disabled="startingQuiz !== null"
                @click="startQuiz(quiz.id)" 
                class="p-button-success mt-2 w-full" 
              />
            </template>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>