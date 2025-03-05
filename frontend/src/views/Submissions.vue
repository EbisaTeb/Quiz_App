<script setup>
import { ref, onMounted } from 'vue';
import axiosClient from '@/axios';
import { useRouter } from 'vue-router';
import Button from 'primevue/button';

const router = useRouter();
const submissions = ref([]);
const isLoading = ref(true);
const error = ref(null);

const fetchSubmissions = async () => {
  try {
    const response = await axiosClient.get('/submissions');
    submissions.value = response.data;
  } catch (err) {
    console.error('Error fetching submissions:', err);
    error.value = 'Failed to fetch submissions. Please try again later.';
  } finally {
    isLoading.value = false;
  }
};

const viewSubmission = (submissionId) => {
  router.push({ name: 'app.submissiondetail', params: { id: submissionId } });
};

onMounted(() => {
  fetchSubmissions();
});
</script>

<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Submissions</h1>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <div v-for="submission in submissions" :key="submission.id" class="mb-4 p-4 bg-white rounded shadow">
        <h2 class="text-lg font-semibold">{{ submission.quiz.title }}</h2>
        <p><strong>Score:</strong> {{ submission.score }}</p>
        <Button label="View" icon="pi pi-eye" @click="viewSubmission(submission.id)" class="p-button-info mt-2" />
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add your styles here */
</style>
