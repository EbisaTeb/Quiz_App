<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosClient from '@/axios';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();
const quizId = route.params.id;
const quiz = ref(null);
const isLoading = ref(true);
const isSubmitting = ref(false);
const answers = ref({});
const error = ref(null);

onMounted(async () => {
  try {
    if (authStore.isAuthenticated && authStore.user.roles.includes('student')) {
      const response = await axiosClient.get(`/student/quizzes/${quizId}`);
      quiz.value = response.data;
    } else {
      error.value = 'You need to be logged in as a student to access this quiz.';
    }
  } catch (err) {
    console.error('Error fetching quiz:', err);
    if (err.response && err.response.status === 403) {
      error.value = 'You do not have permission to access this quiz.';
    } else if (err.response && err.response.status === 404) {
      error.value = 'Quiz not found.';
    } else {
      error.value = 'Failed to fetch quiz. Please try again later.';
    }
  } finally {
    isLoading.value = false;
  }
});

const submitQuiz = async () => {
  isSubmitting.value = true;
  try {
    const response = await axiosClient.post(`/quizzes/${quiz.value.id}/submit`, {
      quiz_id: quiz.value.id,
      answers: answers.value
    });
    toast.add({ severity: 'success', summary: 'Success', detail: 'Quiz submitted successfully', life: 3000 });
  } catch (err) {
    console.error('Error submitting quiz:', err);
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to submit quiz', life: 5000 });
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.back();
};
</script>

<template>
  <div class="p-4">
    <Toast />
    <div class="sticky top-0 bg-gray-100 flex justify-between items-center mb-6 p-4 rounded-lg shadow-md">
      <h1 class="text-2xl font-bold">Quiz Submission</h1>
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
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <form @submit.prevent="submitQuiz">
        <div v-for="(question, index) in quiz.questions" :key="question.id" class="mb-4">
          <h2 class="text-lg font-semibold">{{ index + 1 }}. {{ question.text }}</h2>
          <div v-if="question.type === 'mcq'">
            <div v-for="option in question.options" :key="option" class="flex items-center mb-2">
              <input type="radio" :name="'question-' + question.id" :value="option" v-model="answers[question.id]" class="mr-2" />
              <label>{{ option }}</label>
            </div>
          </div>
          <div v-else-if="question.type === 'short_answer'">
            <input type="text" v-model="answers[question.id]" class="p-inputtext p-component w-full" />
          </div>
          <!-- Add more question types as needed -->
        </div>
        <Button label="Submit" icon="pi pi-check" type="submit" class="p-button-success" :loading="isSubmitting" />
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Add your styles here */
</style>
