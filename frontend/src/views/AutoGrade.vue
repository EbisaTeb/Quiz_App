<template>
  <div class="p-4">
    <Toast />
    <h1 class="text-2xl font-bold mb-4">Auto Grade</h1>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <form @submit.prevent="autoGrade">
        <div v-for="(question, index) in gradedAnswers" :key="question.id" class="mb-4">
          <h2 class="text-lg font-semibold">{{ index + 1 }}. {{ question.text }}</h2>
          <p>User Answer: {{ question.user_answer }}</p>
          <p>Correct Answer: {{ question.correct_answer }}</p>
          <p>Score: {{ question.score }}</p>
        </div>
        <Button label="Auto Grade" icon="pi pi-check" type="submit" class="p-button-success" :loading="isSubmitting" />
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import axiosClient from '@/axios';

export default {
  components: {
    Toast,
    Button
  },
  setup() {
    const toast = useToast();
    const isLoading = ref(true);
    const isSubmitting = ref(false);
    const gradedAnswers = ref([]);
    const error = ref(null);

    const autoGrade = async () => {
      isSubmitting.value = true;
      try {
        const response = await axiosClient.post('/quizzes/auto-grade', {
          // Pass necessary data for auto-grading
        });
        gradedAnswers.value = response.data.graded_answers;
        toast.add({ severity: 'success', summary: 'Success', detail: 'Auto grading completed successfully', life: 3000 });
      } catch (err) {
        console.error('Error during auto grading:', err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to complete auto grading', life: 5000 });
      } finally {
        isSubmitting.value = false;
      }
    };

    return {
      toast,
      isLoading,
      isSubmitting,
      gradedAnswers,
      error,
      autoGrade
    };
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
