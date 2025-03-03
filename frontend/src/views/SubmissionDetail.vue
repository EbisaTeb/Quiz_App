<template>
  <div class="p-4">
    <Toast />
    <h1 class="text-2xl font-bold mb-4">Submission Detail</h1>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <div v-for="(answer, index) in submission.answers" :key="answer.id" class="mb-4">
        <h2 class="text-lg font-semibold">{{ index + 1 }}. {{ answer.question.text }}</h2>
        <p>Your Answer: {{ answer.student_answer }}</p>
        <p>Correct Answer: {{ answer.question.correct_answer }}</p>
        <p>Score: {{ answer.marks_obtained }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import axiosClient from '@/axios';
import { useRoute } from 'vue-router';

export default {
  components: {
    Toast
  },
  setup() {
    const toast = useToast();
    const isLoading = ref(true);
    const submission = ref(null);
    const error = ref(null);
    const route = useRoute();

    const fetchSubmission = async (submissionId) => {
      try {
        const response = await axiosClient.get(`/submissions/${submissionId}`);
        submission.value = response.data;
        isLoading.value = false;
      } catch (err) {
        console.error('Error fetching submission:', err);
        error.value = 'Failed to fetch submission. Please try again later.';
        isLoading.value = false;
      }
    };

    onMounted(() => {
      const submissionId = route.params.id;
      fetchSubmission(submissionId);
    });

    return {
      toast,
      isLoading,
      submission,
      error,
      fetchSubmission
    };
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
