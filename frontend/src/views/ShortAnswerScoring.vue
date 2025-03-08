<script lang="ts">
import { ref, onMounted, watch } from 'vue';
import Dropdown from 'primevue/dropdown';
import axiosClient from '@/axios'; // Use configured axios instance
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

// const toast = useToast();
// const isLoading = ref(true);
// const isSubmitting = ref(false);

interface Quiz {
  id: number;
  title: string;
}

interface Answer {
  id: number;
  question_id: number;
  student_answer: string;
  marks_obtained: number;
  correct_answer: string;
  max_marks: number;
}

interface Submission {
  id: number;
  student_name: string;
  answers: Answer[];
}

export default {
  components: { 
    Dropdown,
    DataTable,
    Column,
    InputNumber,
    Button
  },
  setup() {
    const quizzes = ref<Quiz[]>([]);
    const selectedQuiz = ref<number|null>(null);
    const submissions = ref<Submission[]>([]);

    const fetchQuizzes = async () => {
      try {
        const { data } = await axiosClient.get('/teacher/quizzes');
        quizzes.value = data;
      } catch (error) {
        console.error('Quiz fetch error:', error);
      }
    };

    const fetchSubmissions = async () => {
      if (!selectedQuiz.value) return;
      
      try {
        const { data } = await axiosClient.get(`/quiz/${selectedQuiz.value}/short-answer-submissions`);
        submissions.value = data;
      } catch (error) {
        console.error('Submission fetch error:', error);
      }
    };

    const updateScore = async (submissionId: number, questionId: number, score: number) => {
      try {
        await axiosClient.post(`/submission/${submissionId}/question/${questionId}/score`, { score });
        await fetchSubmissions(); // Refresh data
      } catch (error) {
        console.error('Score update error:', error);
      }
    };

    onMounted(fetchQuizzes);

    watch(selectedQuiz, fetchSubmissions);

    return { quizzes, selectedQuiz, submissions, fetchSubmissions, updateScore };
  }
};
</script>

<template>
  <div class="p-4">
    <!-- <Toast /> -->
    <h2 class="text-2xl font-bold mb-4">Select a Quiz</h2>
    <Dropdown 
      v-model="selectedQuiz" 
      :options="quizzes" 
      option-label="title" 
      option-value="id" 
      placeholder="Select Quiz"
      @change="fetchSubmissions"
    />

    <div v-if="submissions.length" class="mt-6">
      <h2 class="text-xl font-semibold mb-4">Short Answer Submissions</h2>
      <div v-if="isLoading" class="flex justify-center items-center">
      <i class="pi pi-spin pi-spinner text-4xl"></i>
    </div>
      <DataTable :value="submissions">
        <Column field="student_name" header="Student">
          <template #body="{ data }">
            {{ data.student_name || 'Unknown' }}
          </template>
        </Column>
        
        <Column header="Answers">
          <template #body="{ data }">
            <div v-for="answer in data.answers" :key="answer.id" class="mb-4 p-4 bg-gray-50 rounded">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="font-medium">Student Answer:</p>
                  <p>{{ answer.student_answer }}</p>
                </div>
                <div>
                  <p class="font-medium">Correct Answer:</p>
                  <p>{{ answer.correct_answer }}</p>
                </div>
              </div>
              
              <div class="mt-4 flex gap-4 items-center">
                <InputNumber 
                  v-model="answer.marks_obtained" 
                  :min="0" 
                  :max="answer.max_marks"
                  class="w-32"
                />
                <Button 
                  label="Update Score" 
                  icon="pi pi-check" 
                  @click="updateScore(data.id, answer.question_id, answer.marks_obtained)"
                />
              </div>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<style>
/* Add any custom styles here */
</style>