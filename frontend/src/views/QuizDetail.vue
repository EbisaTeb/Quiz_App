<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosClient from '@/axios';
import Button from 'primevue/button';
const route = useRoute();
const router = useRouter();
const quizId = route.params.id;
const quiz = ref(null);
const isLoading = ref(true);

onMounted(async () => {
  try {
    const response = await axiosClient.get(`/quizzes/${quizId}`);
    quiz.value = response.data;
  } catch (error) {
    console.error('Failed to fetch quiz details', error);
  } finally {
    isLoading.value = false;
  }
});

const goBack = () => {
  router.back();
};
</script>

<template>
  <div class="max-w-4xl mx-auto p-6 bg-gray-100 rounded-lg shadow-md">
    <div class="sticky top-0 bg-gray-100 flex justify-between items-center mb-6 p-4 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold">Quiz Details</h2>
      <Button 
        @click="goBack" 
        icon="pi pi-arrow-left" 
        class="p-button-text"
        tooltip="Go back to the previous page" 
        tooltipOptions="{ position: 'top' }"
      >
        Back
      </Button>
    </div>
    <div v-if="isLoading" class="text-center">Loading...</div>
    <div v-else>
      <h3 class="text-xl font-semibold mb-4">{{ quiz.title }}</h3>
      <p><strong>Subject:</strong> {{ quiz.subject.name }}</p>
      <p><strong>Time Limit:</strong> {{ quiz.time_limit }} minutes</p>
      <p><strong>Start Time:</strong> {{ new Date(quiz.start_time).toLocaleString() }}</p>
      <p><strong>End Time:</strong> {{ new Date(quiz.end_time).toLocaleString() }}</p>
      <h4 class="text-lg font-semibold mt-6 mb-4">Questions:</h4>
      <div v-for="(question, index) in quiz.questions" :key="question.id" class="mb-4 p-4 bg-white rounded shadow">
        <p class="font-medium"><strong>{{ index + 1 }}.</strong> {{ question.content }} <strong>({{ question.marks }} marks)</strong></p>
        <div v-if="question.type === 'mcq'">
          <ul class="list-disc pl-5">
            <li v-for="option in question.options" :key="option.id">
              {{ option.content }} <span v-if="option.is_correct" class="text-green-500">(Correct)</span>
            </li>
          </ul>
        </div>
        <div v-else-if="question.type === 'matching'">
          <ul class="list-disc pl-5">
            <li v-for="pair in question.matching_pairs" :key="pair.id">
              {{ pair.left_value }} - {{ pair.right_value }}
            </li>
          </ul>
        </div>
        <div v-else-if="question.type === 'short_answer'">
          <p><strong>Correct Answer:</strong> {{ question.correct_answer }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
