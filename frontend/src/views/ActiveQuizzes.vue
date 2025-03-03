<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axiosClient from '@/axios';
import Button from 'primevue/button';

const router = useRouter();
const activeQuizzes = ref([]);
const isLoading = ref(true);
const error = ref(null);

const fetchActiveQuizzes = async () => {
  try {
    const response = await axiosClient.get('/student/active-quizzes');
    activeQuizzes.value = response.data;
  } catch (err) {
    console.error('Error fetching active quizzes:', err);
    error.value = 'Failed to fetch active quizzes. Please try again later.';
  } finally {
    isLoading.value = false;
  }
};

const startQuiz = (quizId) => {
  router.push({ name: 'app.quizsubmission', params: { id: quizId } });
};

onMounted(() => {
  fetchActiveQuizzes();
});
</script>

<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Active Quizzes</h1>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <div v-for="quiz in activeQuizzes" :key="quiz.id" class="mb-4 p-4 bg-white rounded shadow">
        <h2 class="text-lg font-semibold">{{ quiz.title }}</h2>
        <p><strong>Subject:</strong> {{ quiz.subject.name }}</p>
        <p><strong>Time Limit:</strong> {{ quiz.time_limit }} minutes</p>
        <p><strong>Start Time:</strong> {{ new Date(quiz.start_time).toLocaleString() }}</p>
        <p><strong>End Time:</strong> {{ new Date(quiz.end_time).toLocaleString() }}</p>
        <Button label="Start" icon="pi pi-play" @click="startQuiz(quiz.id)" class="p-button-success mt-2" />
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add your styles here */
</style>
