<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axiosClient from '@/axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import ProgressSpinner from 'primevue/progressspinner';
import Button from 'primevue/button';
import { useRouter } from 'vue-router';

interface Quiz {
  id: number;
  title: string;
}

interface StudentScore {
  id: number;
  student_id: number;
  score: string;
  student: {
    id: number;
    name: string;
  };
}

const route = useRoute();
const quizzes = ref<Quiz[]>([]);
const selectedQuiz = ref<Quiz | null>(null);
const studentScores = ref<StudentScore[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);
const searchQuery = ref('');
const router = useRouter();

const fetchQuizzes = async () => {
  try {
    const response = await axiosClient.get('/admin/quizzes');
    quizzes.value = response.data;
  } catch (err) {
    error.value = 'Failed to load quizzes';
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

const fetchStudentScores = async (quizId: number) => {
  try {
    isLoading.value = true;
    const response = await axiosClient.get(`/admin/quizzes/${quizId}/student-scores`);
    studentScores.value = response.data;
  } catch (err) {
    error.value = 'Failed to load student scores';
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

const viewSubmission = (submissionId: number) => {
  router.push({ name: 'app.submissiondetail', params: { id: submissionId } });
};

const filteredScores = computed(() => {
  if (!searchQuery.value) {
    return studentScores.value;
  }
  return studentScores.value.filter(score =>
    score.student.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

watch(selectedQuiz, (newQuiz) => {
  if (newQuiz) {
    fetchStudentScores(newQuiz.id);
  }
});

onMounted(() => {
  fetchQuizzes();
});
</script>

<template>
  <div class="p-4">
    <div v-if="isLoading" class="flex justify-center items-center h-screen">
      <ProgressSpinner />
    </div>

    <div v-else-if="error" class="p-4 bg-red-50 text-red-600 rounded">
      {{ error }}
    </div>

    <div v-else class="space-y-4">
      <h1 class="text-2xl font-bold mb-4">Student Scores</h1>
      <div class="flex justify-between mb-4">
        <Dropdown v-model="selectedQuiz" :options="quizzes" optionLabel="title" placeholder="Select a Quiz" />
        <InputText v-model="searchQuery" placeholder="Search by student name" />
      </div>
      <DataTable :value="filteredScores" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" responsiveLayout="scroll">
        <Column field="student.name" header="Student Name" sortable />
        <Column field="score" header="Score" sortable />
        <Column header="Actions">
          <template #body="slotProps">
            <Button label="View" icon="pi pi-eye" @click="viewSubmission(slotProps.data.id)" />
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
