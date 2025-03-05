<template>
  <div class="p-4">
    <div v-if="isLoading" class="flex justify-center items-center h-screen">
      <ProgressSpinner />
    </div>

    <div v-else-if="submission" class="space-y-6">
      <!-- Quiz Header -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-2xl font-bold">{{ submission.quiz.title }}</h1>
          <div class="text-xl font-semibold text-primary">
            Score: {{ actualScore.toFixed(2) }}/{{ totalPossibleMarks }}
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4 text-gray-600">
          <div>Submitted: {{ formatDate(submission.created_at) }}</div>
          <div>Time Limit: {{ submission.quiz.time_limit }} minutes</div>
        </div>
      </div>

      <!-- Questions List -->
      <div v-for="(question, index) in submission.quiz.questions" 
           :key="question.id" 
           class="bg-white p-6 rounded-lg shadow">
        <template v-if="getAnswerForQuestion(question.id)">
          <!-- Question Header -->
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center gap-2">
              <span class="text-xl font-semibold">Q{{ index + 1 }}:</span>
              <div v-html="question.content" class="text-lg"></div>
            </div>
            <Tag :severity="getAnswerForQuestion(question.id).is_correct ? 'success' : 'danger'" 
                 :value="`${getAnswerForQuestion(question.id).marks_obtained}/${question.marks} marks`"/>
          </div>

          <!-- Answer Display -->
          <div class="space-y-4">
            <!-- Short Answer/MCQ -->
            <div v-if="['short_answer', 'mcq'].includes(question.type)" class="space-y-2">
              <div class="p-3 bg-gray-50 rounded">
                <label class="text-sm font-medium text-gray-500">Your Answer:</label>
                <p class="mt-1">{{ getAnswerForQuestion(question.id).student_answer }}</p>
              </div>
              <div class="p-3 bg-green-50 rounded">
                <label class="text-sm font-medium text-green-600">Correct Answer:</label>
                <p class="mt-1">{{ question.correct_answer }}</p>
              </div>
            </div>

            <!-- Matching Questions -->
            <div v-if="question.type === 'matching'" class="space-y-4">
              <DataTable :value="getMatchingPairs(getAnswerForQuestion(question.id))" 
                        class="p-datatable-sm" 
                        showGridlines>
                <Column field="left" header="Item" style="width: 35%"></Column>
                <Column field="studentAnswer" header="Your Match">
                  <template #body="slotProps">
                    <span :class="{
                      'text-green-500': slotProps.data.isCorrect, 
                      'text-red-500': !slotProps.data.isCorrect
                    }">
                      {{ slotProps.data.studentAnswer }}
                    </span>
                  </template>
                </Column>
                <Column field="correctAnswer" header="Correct Match" style="width: 35%"></Column>
              </DataTable>
            </div>
          </div>
        </template>

        <!-- Unanswered Question -->
        <div v-else class="text-red-500">
          <span class="text-xl font-semibold">Q{{ index + 1 }}:</span>
          <span class="ml-2">No answer submitted for this question</span>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="p-4 bg-red-50 text-red-600 rounded">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axiosClient from '@/axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';

interface Submission {
  id: number;
  quiz: {
    title: string;
    time_limit: number;
    questions: Array<{
      id: number;
      type: string;
      content: string;
      marks: number;
      correct_answer: string;
    }>;
  };
  answers: Array<{
    id: number;
    question_id: number;
    student_answer: any;
    is_correct: boolean;
    marks_obtained: string;
  }>;
  created_at: string;
  score: string;
}

const route = useRoute();
const submission = ref<Submission | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

const totalPossibleMarks = computed(() => 
  submission.value?.quiz.questions.reduce((sum, q) => sum + q.marks, 0) || 0
);

const actualScore = computed(() =>
  submission.value?.answers.reduce((sum, a) => sum + parseFloat(a.marks_obtained), 0) || 0
);

const getAnswerForQuestion = (questionId: number) => {
  return submission.value?.answers.find(a => a.question_id === questionId);
};

const getMatchingPairs = (answer: any) => {
  try {
    const correctPairs = JSON.parse(answer.question.correct_answer);
    const studentAnswers = answer.student_answer;
    const options = Object.values(correctPairs);
    
    return Object.entries(studentAnswers).map(([leftItem, studentKey]) => {
      const optionIndex = (studentKey as string).charCodeAt(0) - 97;
      return {
        left: leftItem,
        studentAnswer: options[optionIndex] || 'Invalid selection',
        correctAnswer: correctPairs[leftItem],
        isCorrect: options[optionIndex] === correctPairs[leftItem]
      };
    });
  } catch (e) {
    console.error('Error parsing matching pairs:', e);
    return [];
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const fetchSubmission = async () => {
  try {
    const response = await axiosClient.get(`/submissions/${route.params.id}`);
    submission.value = response.data;
  } catch (err) {
    error.value = 'Failed to load submission details';
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchSubmission);
</script>

<style scoped>
/* Add custom styles if needed */
</style>