<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axiosClient from '@/axios';
import Button from 'primevue/button';

const route = useRoute();
const submissionId = route.params.id;
const submission = ref(null);
const isLoading = ref(true);
const error = ref(null);

const fetchSubmission = async () => {
  try {
    const response = await axiosClient.get(`/submissions/${submissionId}`);
    submission.value = response.data;
  } catch (err) {
    console.error('Error fetching submission:', err);
    error.value = 'Failed to fetch submission. Please try again later.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchSubmission();
});
</script>

<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Submission Details</h1>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <div class="mb-4 p-4 bg-white rounded shadow">
        <h2 class="text-lg font-semibold">{{ submission.quiz.title }}</h2>
        <p><strong>Score:</strong> {{ submission.score }}</p>
        <div v-for="answer in submission.answers" :key="answer.id" class="mb-4">
          <h3 class="font-semibold">{{ answer.question.content }}</h3>
          <p><strong>Your Answer:</strong> {{ answer.student_answer }}</p>
          <p><strong>Correct Answer:</strong> {{ answer.question.correct_answer }}</p>
          <p><strong>Marks Obtained:</strong> {{ answer.marks_obtained }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add your styles here */
</style>
