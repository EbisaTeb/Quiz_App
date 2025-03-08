<script lang="ts">
import { ref, onMounted, watch } from 'vue';
import Dropdown from 'primevue/dropdown';
import axiosClient from '@/axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import { z } from 'zod';

interface Quiz {
  id: number;
  title: string;
}

interface Answer {
  id: number;
  question_id: number;
  question: string;
  student_answer: string;
  marks_obtained: number;
  correct_answer: string;
  max_marks: number;
  error?: string;
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
    Button,
    ProgressSpinner,
    Toast
  },
  setup() {
    const toast = useToast();
    const selectedQuiz = ref<number | null>(null);
    const quizzes = ref<Quiz[]>([]);
    const submissions = ref<Submission[]>([]);
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const submittingQuestionId = ref<number | null>(null);

    const fetchQuizzes = async () => {
      try {
        const { data } = await axiosClient.get('/teacher/quizzes');
        quizzes.value = data;
      } catch (error) {
        console.error('Quiz fetch error:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load quizzes', life: 3000 });
      }
    };

    const fetchSubmissions = async () => {
      if (!selectedQuiz.value) return;
      
      try {
        isLoading.value = true;
        const { data } = await axiosClient.get(`/quiz/${selectedQuiz.value}/short-answer-submissions`);
        submissions.value = data;
      } catch (error) {
        console.error('Submission fetch error:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load submissions', life: 3000 });
      } finally {
        isLoading.value = false;
      }
    };

    const validateScore = (value: number, answer: Answer) => {
      const scoreSchema = z.number()
        .min(0, "Score cannot be negative")
        .max(answer.max_marks, `Score cannot exceed ${answer.max_marks}`);
      
      try {
        scoreSchema.parse(value);
        answer.error = undefined;
      } catch (error) {
        if (error instanceof z.ZodError) {
          answer.error = error.errors[0].message;
        } else {
          answer.error = 'Invalid score value';
        }
      }
    };

    const updateScore = async (submissionId: number, questionId: number, score: number, maxMarks: number) => {
      try {
        isSubmitting.value = true;
        submittingQuestionId.value = questionId;
        
        await axiosClient.post(`/submission/${submissionId}/question/${questionId}/score`, { score });
        
        // Update local state
        const submission = submissions.value.find(s => s.id === submissionId);
        const answer = submission?.answers.find(a => a.question_id === questionId);
        if (answer) {
          answer.marks_obtained = score;
          answer.error = undefined;
        }

        toast.add({ severity: 'success', summary: 'Success', detail: 'Score updated', life: 3000 });
      } catch (error) {
        console.error('Score update error:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update score', life: 3000 });
      } finally {
        isSubmitting.value = false;
        submittingQuestionId.value = null;
      }
    };

    onMounted(fetchQuizzes);

    watch(selectedQuiz, fetchSubmissions);

    return { 
      quizzes,
      selectedQuiz,
      submissions,
      isLoading,
      isSubmitting,
      submittingQuestionId,
      validateScore,
      updateScore
    };
  }
};
</script>

<template>
  <div class="p-4">
    <Toast />
    <h2 class="text-2xl font-bold mb-4">Select a Quiz</h2>
    <Dropdown 
      v-model="selectedQuiz" 
      :options="quizzes" 
      option-label="title" 
      option-value="id" 
      placeholder="Select Quiz"
      class="w-full md:w-96"
    />

    <div v-if="isLoading" class="flex justify-center items-center h-32">
      <ProgressSpinner />
    </div>

    <div v-if="submissions.length && !isLoading" class="mt-6">
      <h2 class="text-xl font-semibold mb-4">Short Answer Submissions</h2>
      <DataTable :value="submissions" class="p-datatable-sm">
        <Column field="student_name" header="Student">
          <template #body="{ data }">
            <div class="font-medium">{{ data.student_name || 'Unknown' }}</div>
          </template>
        </Column>
        
        <Column header="Answers">
          <template #body="{ data }">
            <div v-for="(answer, index) in data.answers" :key="answer.id" class="mb-4 p-4 bg-gray-50 rounded-lg">
              <div class="mb-2">
                <p class="font-medium text-gray-700">Q{{ index + 1 }}: {{ answer.question }}<strong>({{ answer.max_marks }} marks)</strong>  </p>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="bg-white p-3 rounded">
                  <p class="text-sm font-medium text-gray-500 mb-1">Student Answer:</p>
                  <p class="text-gray-800">{{ answer.student_answer }}</p>
                </div>
                <div class="bg-white p-3 rounded">
                  <p class="text-sm font-medium text-gray-500 mb-1">Correct Answer:</p>
                  <p class="text-gray-800">{{ answer.correct_answer }}</p>
                </div>
              </div>
              
                <div class="flex flex-col gap-2">
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <InputNumber 
                    v-model="answer.marks_obtained" 
                    :min="0" 
                    :max="answer.max_marks"
                    :class="{ 'p-invalid': answer.error }"
                    class="w-full md:w-32"
                    showButtons
                    buttonLayout="horizontal"
                    incrementButtonIcon="pi pi-plus"
                    decrementButtonIcon="pi pi-minus"
                    @update:modelValue="(value) => validateScore(value, answer)"
                    />
                  <!-- <span class="text-gray-500">/ {{ answer.max_marks }}</span> -->
                </div>
                
                <small v-if="answer.error" class="p-error">{{ answer.error }}</small>

                <Button 
                  label="Update Score" 
                  icon="pi pi-check" 
                  class="w-fit"
                  :loading="isSubmitting && submittingQuestionId === answer.question_id"
                  :disabled="!!answer.error || isSubmitting"
                  @click="updateScore(data.id, answer.question_id, answer.marks_obtained, answer.max_marks)"
                />
              </div>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
