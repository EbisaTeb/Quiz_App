<template>
  <div class="p-4">
    <Toast />
    <h1 class="text-2xl font-bold mb-4">My Submissions</h1>
    <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
    <div v-else-if="error" class="text-red-500">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <DataTable :value="submissions" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" responsiveLayout="scroll">
        <Column field="quiz.title" header="Quiz Title" sortable />
        <Column field="score" header="Score" sortable />
        <Column field="created_at" header="Submission Date" sortable />
        <Column header="Actions">
          <template #body="slotProps">
            <Button label="View" icon="pi pi-eye" @click="viewSubmission(slotProps.data.id)" />
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import axiosClient from '@/axios';
import { useRouter } from 'vue-router';

export default {
  components: {
    Toast,
    DataTable,
    Column,
    Button
  },
  setup() {
    const toast = useToast();
    const isLoading = ref(true);
    const submissions = ref([]);
    const error = ref(null);
    const router = useRouter();

    const fetchSubmissions = async () => {
      try {
        const response = await axiosClient.get('/submissions');
        submissions.value = response.data;
        isLoading.value = false;
      } catch (err) {
        console.error('Error fetching submissions:', err);
        error.value = 'Failed to fetch submissions. Please try again later.';
        isLoading.value = false;
      }
    };

    const viewSubmission = (submissionId) => {
      // Navigate to the submission detail view
      router.push({ name: 'app.submissionDetail', params: { id: submissionId } });
    };

    onMounted(() => {
      fetchSubmissions();
    });

    return {
      toast,
      isLoading,
      submissions,
      error,
      fetchSubmissions,
      viewSubmission
    };
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
